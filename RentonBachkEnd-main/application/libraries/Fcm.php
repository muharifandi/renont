<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * FCM HTTP v1 client (migrated from the legacy FCM/GCM HTTP API, shut down
 * by Google in June 2024). Public method names/signatures are kept
 * identical to the old library on purpose -- every call site across the
 * app (17 of them) just does setRecepients()/addRecepient() -> setData()
 * -> setNotification() -> send() and never inspects the return value or
 * ->status/->messagesStatuses, so none of those call sites need to change.
 *
 * Auth: HTTP v1 requires an OAuth2 Bearer token obtained via a Google
 * service-account JSON key (no more simple server key). This library signs
 * its own JWT and exchanges it for an access token (cached to a temp file,
 * reused until near expiry) using only curl/openssl -- no Composer/Google
 * client library, consistent with the rest of this codebase.
 */
class FCM {
    protected $serviceAccountPath = '';
    protected $projectId = '';
    protected $clientEmail = '';
    protected $privateKey = '';
    protected $payload = array();
    protected $message = '';
    public $status = array();
    public $messagesStatuses = array();

    public function __construct()
    {
        $ci =& get_instance();
        $ci->load->config('fcm', true);
        $this->serviceAccountPath = $ci->config->item('fcm_service_account_path', 'fcm');

        if ($this->serviceAccountPath && is_file($this->serviceAccountPath)) {
            $sa = json_decode(file_get_contents($this->serviceAccountPath), true);
            if (is_array($sa)) {
                $this->projectId = $sa['project_id'] ?? '';
                $this->clientEmail = $sa['client_email'] ?? '';
                $this->privateKey = $sa['private_key'] ?? '';
            }
        }

        if (!$this->projectId || !$this->clientEmail || !$this->privateKey) {
            // Deliberately NOT show_error()/exit here (unlike the old library):
            // push notifications are a side-effect of core flows like booking
            // creation and chat -- those must keep working even if FCM isn't
            // configured yet. send() below just no-ops with a logged error.
            log_message('error', 'FCM: service account not configured or unreadable at "'.$this->serviceAccountPath.'" -- push notifications are disabled until this is fixed.');
        }
    }

    /** Kept only so existing call sites (`$this->fcm->setApiKey(...)`) don't fatal-error; FCM v1 has no per-call API key. */
    public function setApiKey($key) {}

    public function setTtl($ttl = '')
    {
        if (!$ttl) {
            unset($this->payload['time_to_live']);
        } else {
            $this->payload['time_to_live'] = $ttl;
        }
    }

    public function setMessage($message = '')
    {
        $this->message = $message;
        $this->payload['data']['message'] = $message;
    }

    public function setData($data = array())
    {
        $this->payload['data'] = $data;
        if ($this->message) {
            $this->payload['data']['message'] = $this->message;
        }
    }

    public function setNotification($data = array())
    {
        $this->payload['notification'] = $data;
        if ($this->message) {
            $this->payload['notification']['message'] = $this->message;
        }
    }

    public function setGroup($group = '')
    {
        if (!$group) {
            unset($this->payload['collapse_key']);
        } else {
            $this->payload['collapse_key'] = $group;
        }
    }

    public function addRecepient($registrationId)
    {
        $this->payload['registration_ids'][] = $registrationId;
    }

    public function setRecepients($registrationIds)
    {
        $this->payload['registration_ids'] = $registrationIds;
    }

    public function clearRecepients()
    {
        $this->payload['registration_ids'] = array();
    }

    /**
     * Sends to every recipient token. FCM v1 has no multicast endpoint (unlike
     * the legacy API's `registration_ids`), so this loops one HTTP request per
     * token -- fine at this app's scale, but a broadcast to a very large
     * audience (e.g. News::send_notification to "all customers") would be
     * worth moving to topic messaging if that list ever grows large.
     */
    public function send()
    {
        $tokens = array_values(array_unique($this->payload['registration_ids'] ?? array()));
        sort($tokens);

        if (empty($tokens)) {
            $this->status = array('error' => 1, 'message' => 'No recipients');
            $this->messagesStatuses = array();
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            $this->status = array('error' => 1, 'message' => 'Could not obtain FCM OAuth2 access token (service account not configured?)');
            $this->messagesStatuses = array();
            return false;
        }

        $message = $this->buildMessage();
        $this->messagesStatuses = array();
        $errorCount = 0;

        foreach ($tokens as $key => $token) {
            $message['token'] = $token;
            $result = $this->sendOne($message, $accessToken, $token);
            $this->messagesStatuses[$key] = $result;
            if ($result['error']) {
                $errorCount++;
            }
        }

        $total = count($tokens);
        $this->status = array(
            'error' => $errorCount > 0 ? 1 : 0,
            'message' => $errorCount === 0
                ? 'All messages were sent successfully'
                : ($total - $errorCount).' of '.$total.' messages were sent successfully',
        );

        return $errorCount === 0;
    }

    /** Builds the v1 `message` object (minus `token`, added per-recipient in send()). */
    protected function buildMessage()
    {
        $message = array();

        if (!empty($this->payload['notification'])) {
            $notif = $this->payload['notification'];
            $message['notification'] = array(
                'title' => (string) ($notif['title'] ?? ''),
                'body' => (string) ($notif['text'] ?? ($notif['body'] ?? ($notif['message'] ?? ''))),
            );

            // The legacy payload smuggled these as top-level notification keys;
            // v1 has real, separate fields for them under `android.notification`.
            $androidNotification = array();
            if (!empty($notif['android_channel_id'])) {
                $androidNotification['channel_id'] = (string) $notif['android_channel_id'];
            }
            if (!empty($notif['sound'])) {
                $androidNotification['sound'] = (string) $notif['sound'];
            }
            if ($androidNotification) {
                $message['android']['notification'] = $androidNotification;
            }
        }

        if (!empty($this->payload['data'])) {
            // FCM v1 requires every `data` value to be a string.
            $message['data'] = array_map(function ($value) {
                if (is_string($value)) {
                    return $value;
                }
                return is_scalar($value) ? (string) $value : json_encode($value);
            }, $this->payload['data']);
        }

        if (!empty($this->payload['collapse_key'])) {
            $message['android']['collapse_key'] = (string) $this->payload['collapse_key'];
        }
        if (!empty($this->payload['time_to_live'])) {
            $message['android']['ttl'] = ((int) $this->payload['time_to_live']).'s';
        }

        return $message;
    }

    protected function sendOne($message, $accessToken, $token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://fcm.googleapis.com/v1/projects/'.$this->projectId.'/messages:send',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$accessToken,
            ),
            CURLOPT_POSTFIELDS => json_encode(array('message' => $message)),
        ));
        $responseBody = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            return array('error' => 1, 'regid' => $token, 'message' => 'cURL error: '.$curlError, 'message_id' => '');
        }

        $decoded = json_decode($responseBody, true);

        if ($httpCode === 200 && !empty($decoded['name'])) {
            return array('error' => 0, 'regid' => $token, 'message' => 'Message was sent successfully', 'message_id' => $decoded['name']);
        }

        $errorStatus = $decoded['error']['status'] ?? ('HTTP_'.$httpCode);
        $errorMessage = $decoded['error']['message'] ?? ('Unexpected response: '.substr((string) $responseBody, 0, 200));

        return array('error' => 1, 'regid' => $token, 'message' => $errorStatus.': '.$errorMessage, 'message_id' => '');
    }

    /**
     * Returns a valid OAuth2 access token, reusing a cached one (file-based --
     * survives across requests/PHP-FPM workers, unlike a static property)
     * until it's within 60s of expiring.
     */
    protected function getAccessToken()
    {
        if (!$this->projectId || !$this->clientEmail || !$this->privateKey) {
            return null;
        }

        $cacheFile = sys_get_temp_dir().'/renton_fcm_token_'.md5($this->clientEmail).'.json';
        if (is_file($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached) && !empty($cached['access_token']) && !empty($cached['expires_at']) && $cached['expires_at'] > (time() + 60)) {
                return $cached['access_token'];
            }
        }

        $jwt = $this->buildSignedJwt();
        if (!$jwt) {
            return null;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://oauth2.googleapis.com/token',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            )),
        ));
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $decoded = json_decode($response, true);
        if ($httpCode !== 200 || empty($decoded['access_token'])) {
            log_message('error', 'FCM: OAuth2 token exchange failed (HTTP '.$httpCode.'): '.$response);
            return null;
        }

        file_put_contents($cacheFile, json_encode(array(
            'access_token' => $decoded['access_token'],
            'expires_at' => time() + (int) ($decoded['expires_in'] ?? 3600),
        )));
        @chmod($cacheFile, 0600);

        return $decoded['access_token'];
    }

    protected function buildSignedJwt()
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));
        $claims = $this->base64UrlEncode(json_encode(array(
            'iss' => $this->clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        )));

        $signingInput = $header.'.'.$claims;
        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $this->privateKey, 'sha256WithRSAEncryption');
        if (!$signed) {
            log_message('error', 'FCM: failed to sign JWT with service account private key.');
            return null;
        }

        return $signingInput.'.'.$this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

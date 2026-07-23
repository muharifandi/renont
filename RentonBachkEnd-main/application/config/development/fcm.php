<?php
/*
|--------------------------------------------------------------------------
| Firebase Cloud Messaging (FCM) -- HTTP v1 API
|--------------------------------------------------------------------------
| The legacy FCM/GCM HTTP API (simple server key) was shut down by Google
| in June 2024. HTTP v1 requires a service-account JSON key instead --
| generate one at Firebase Console > Project Settings > Service accounts >
| Generate new private key, then point this path at the downloaded file
| (see application/libraries/Fcm.php for how it's used).
|
| Defaults to a single shared file at the project root
| (RentonBachkEnd-main/.fcm-service-account.json) unless overridden per
| environment via FCM_SERVICE_ACCOUNT_PATH in .env.{environment}.
*/
$config['fcm_service_account_path'] = getenv('FCM_SERVICE_ACCOUNT_PATH') ?: dirname(dirname(dirname(__DIR__))).'/.fcm-service-account.json';
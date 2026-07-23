<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared secret every request to the `api` module (mobile app) must send
 * via the `X-App-Secret` header -- see Api_Base_Controller. This is a
 * client/app-level gate, separate from the per-account `key` header used
 * by require_auth(). It filters out generic tools (curl/Postman/scanners)
 * that don't know this value; it does NOT stop an attacker who decompiles
 * the APK, since any secret baked into a mobile app can be extracted.
 */
$config['app_secret_key'] = getenv('APP_SECRET_KEY');

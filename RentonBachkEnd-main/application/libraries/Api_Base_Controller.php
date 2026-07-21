<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Base for every controller in the `api` module (the one the Android app
 * talks to). Adds a mandatory `X-App-Secret` header check on top of
 * REST_Base_Controller's per-account `key` auth: requests must present the
 * shared app secret (application/config/{env}/app_secret.php) before any
 * controller method runs, account-authenticated or not.
 *
 * This is a client/app-level gate, not user authentication -- it exists to
 * stop generic tools (curl/Postman/scanners) hitting the API directly.
 * It does not stop a determined attacker who decompiles the Android APK,
 * since any secret baked into a mobile app can be extracted from it; for
 * that level of assurance, Google Play Integrity API would be needed instead.
 */
class Api_Base_Controller extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->_require_app_secret();
	}

	private function _require_app_secret()
	{
		$this->config->load('app_secret');
		$expected = (string) $this->config->item('app_secret_key');
		$provided = (string) $this->input->get_request_header('X-App-Secret', TRUE);

		if (empty($expected) || empty($provided) || !hash_equals($expected, $provided)) {
			$this->forbidden('Akses ditolak');
			exit;
		}
	}
}

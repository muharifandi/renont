<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/Api_Base_Controller.php';

/**
 * Public utility endpoints (no auth) used before/during registration.
 */
class Basic extends Api_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Basic_m');
	}

	public function index_get()
	{
		$a = date('Y-m-d', strtotime('monday this week'));
		$b = date('Y-m-d', strtotime('sunday this week'));
		$c = date('Y-m-d', strtotime('now'));

		$this->ok([
			'week_start' => $a,
			'week_end' => $b,
			'today' => $c,
		]);
	}

	public function application_status_get()
	{
		$this->ok([
			'maintenance' => $this->Basic_m->get_config('maintenance')->value,
			'maintenance_message' => $this->Basic_m->get_config('maintenance_message')->value,
			'android_app_version_code' => $this->Basic_m->get_config('android_app_version_code')->value,
			'android_app_version_name' => $this->Basic_m->get_config('android_app_version_name')->value,
			'android_app_update_link' => $this->Basic_m->get_config('android_app_update_link')->value,
		]);
	}

	/** GET api/basic/check_email?email=... */
	public function check_email_get()
	{
		$email = $this->get('email');
		if (empty($email)) {
			return $this->validation_error(['email' => 'wajib diisi']);
		}

		$result = $this->Basic_m->check_email($email);

		$this->ok([
			'available' => !$result,
		], $result ? 'Email telah terdaftar' : 'Email dapat digunakan');
	}

	/** GET api/basic/check_phone?phone=... */
	public function check_phone_get()
	{
		$phone = $this->get('phone');
		if (empty($phone)) {
			return $this->validation_error(['phone' => 'wajib diisi']);
		}

		$result = $this->Basic_m->check_phone($phone);

		$this->ok([
			'available' => !$result,
		], $result ? 'Nomor telah terdaftar' : 'Nomor dapat digunakan');
	}

	/** GET api/basic/check_agent?id=... */
	public function check_agent_get()
	{
		$id = $this->get('id');
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$result = $this->Basic_m->check_agent($id);

		if (!$result) {
			return $this->not_found('Agen tidak ditemukan');
		}

		$this->ok(['valid' => true, 'name' => $result->first_name.' '.$result->last_name]);
	}

	/** GET api/basic/regencies?province=... */
	public function regencies_get()
	{
		$province = $this->get('province');
		$result = $this->Basic_m->get_regencies($province);

		$this->ok(['regencies' => $result ?: []]);
	}

	/** GET api/basic/active_regencies */
	public function active_regencies_get()
	{
		$this->ok(['regencies' => $this->Basic_m->get_active_regencies()]);
	}
}

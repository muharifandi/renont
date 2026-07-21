<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/Api_Base_Controller.php';

/**
 * Customer account, profile, and wallet resource.
 */
class Customer extends Api_Base_Controller
{
	/**
	 * Per-IP rate limit on login, on top of Ion Auth's own per-identity lockout
	 * (track_login_attempts) -- rest_limits_method is 'IP_ADDRESS' for this
	 * module, see application/modules/api/config/rest.php.
	 */
	protected $methods = [
		'login_post' => ['limit' => 10, 'time' => 300],
	];

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Customer API — RentOn');
	}

	/** POST api/customer — register a new customer account (multipart) */
	public function index_post()
	{
		$this->load->library('ion_auth');

		$email = $this->post('email');
		$password = $this->post('password');
		$first_name = $this->post('first_name');
		$last_name = $this->post('last_name');
		$phone = $this->post('phone');

		if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
			return $this->validation_error([
				'email' => 'wajib diisi', 'password' => 'wajib diisi',
				'first_name' => 'wajib diisi', 'last_name' => 'wajib diisi',
			]);
		}

		$referal_id = null;
		$referal = $this->post('referal');
		if (!empty($referal)) {
			$referal_id = $this->Basic_m->get_account_id_by_referal_code($referal);
		}

		$additional_data = ['first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone];
		$id = $this->ion_auth->register($email, $password, $email, $additional_data, [5]);

		if (!$id) {
			return $this->fail('Gagal Registrasi! Cek kembali form registrasi.', 422, $this->ion_auth->errors());
		}

		$img_profile = $this->_handle_upload('img_profile', FCPATH.'data/customers/profile');
		$img_identity = $this->_handle_upload('img_identity', FCPATH.'data/customers/files/identity');

		$this->Customer_m->register($id, [
			'account_id' => $id,
			'identity_number' => $this->post('identity_number'),
			'img_profile' => $img_profile,
			'referal_id' => $referal_id,
		]);
		$this->Customer_m->insert_customer_file(['account_id' => $id, 'img_identity' => $img_identity]);

		$key = $this->_generate_key();
		$this->_insert_key($key, ['account_id' => $id, 'level' => 1, 'ignore_limits' => 1]);

		$this->created(['account_id' => (int) $id, 'key' => $key], 'Berhasil Registrasi');
	}

	private function _handle_upload($field, $path)
	{
		if (empty($_FILES[$field]['name'])) {
			return null;
		}
		$this->load->helper('image_manipulation');

		$config = [
			'upload_path' => $path,
			'allowed_types' => 'jpg|jpeg|png',
			'max_size' => '5120',
			'overwrite' => false,
		];
		$this->load->library('upload', $config, $field);
		if ($this->{$field}->do_upload($field)) {
			$file_name = $this->{$field}->data('file_name');
			thumb_image($path.'/'.$file_name, $path.'/thumb_rentone_'.$file_name, 250);
			return resize_image($path.'/'.$file_name, $path.'/rentone_'.$file_name, 600, 1, TRUE);
		}
		return null;
	}

	/** POST api/customer/login body: {email, password} */
	public function login_post()
	{
		$this->load->library('ion_auth');

		$email = $this->post('email');
		$password = $this->post('password');

		if (empty($email) || empty($password)) {
			return $this->validation_error(['email' => 'wajib diisi', 'password' => 'wajib diisi']);
		}

		if (!$this->ion_auth->login($email, $password)) {
			return $this->fail('Gagal Login. Cek kembali email dan password.', 401);
		}

		$user = $this->ion_auth->user()->row();
		$this->db->select('group_id');
		$this->db->where('account_id', $user->id);
		$groups = array_map(function ($g) { return (int) $g->group_id; }, $this->db->get('accounts_groups')->result());

		if (!in_array(self::GROUP_CUSTOMER, $groups, true)) {
			return $this->fail('Akun ini tidak bisa login di sini! Gunakan akun lain atau buat baru.', 403);
		}
		if ((int) $user->active !== 1) {
			return $this->fail('Akun belum aktif atau masih dalam proses review', 403);
		}

		$this->db->where('account_id', $user->id);
		$this->db->where('level >', 0);
		$this->db->group_start()->where('date_expires IS NULL')->or_where('date_expires >', time())->group_end();
		$existing = $this->db->get(config_item('rest_keys_table'))->row();
		if ($existing) {
			$key = $existing->{config_item('rest_key_column')};
			$this->db->where('id', $existing->id);
			$this->db->update(config_item('rest_keys_table'), ['date_expires' => time() + (30 * 24 * 60 * 60)]);
		} else {
			$key = $this->Customer_m->get_key($user->id);
		}

		$this->ok(['account_id' => (int) $user->id, 'key' => $key], 'Login berhasil');
	}

	public function detail_get()
	{
		$account = $this->require_auth();
		$this->ok([
			'customer' => $this->Customer_m->detail($account->id),
			'balance' => $this->Customer_m->balance($account->id),
			'bank_total' => $this->Customer_m->bank_total($account->id),
		]);
	}

	public function banks_get()
	{
		$account = $this->require_auth();
		$this->ok(['banks' => $this->Customer_m->banks($account->id)]);
	}

	public function banks_post()
	{
		$account = $this->require_auth();

		$bank_id = $this->post('bank_id');
		$name = $this->post('name');
		$bank_number = $this->post('bank_number');
		$id = $this->post('id');

		if (empty($bank_id) || empty($name) || empty($bank_number)) {
			return $this->validation_error(['bank_id' => 'wajib diisi', 'name' => 'wajib diisi', 'bank_number' => 'wajib diisi']);
		}

		$data = ['bank_id' => $bank_id, 'name' => $name, 'bank_number' => $bank_number];

		if (!empty($id)) {
			$this->Customer_m->update_bank($account->id, $id, $data);
			$this->ok(null, 'Berhasil mengubah rekening bank');
		} else {
			$new_id = $this->Customer_m->add_bank($account->id, $data);
			$this->created(['id' => (int) $new_id], 'Berhasil menambahkan rekening bank');
		}
	}

	public function bank_delete($id = null)
	{
		$account = $this->require_auth();
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$bank = $this->Customer_m->bank_detail($id, $account->id);
		if (!$bank) {
			return $this->not_found('Rekening tidak ditemukan');
		}
		$this->Customer_m->delete_bank($id, $account->id);
		$this->ok(null, 'Berhasil menghapus rekening bank');
	}

	public function bank_get($id = null)
	{
		$account = $this->require_auth();
		if (!empty($id)) {
			$bank = $this->Customer_m->bank_detail($id, $account->id);
			if (!$bank) return $this->not_found('Rekening tidak ditemukan');
			return $this->ok(['bank' => $bank]);
		}
		$this->ok(['banks' => $this->Basic_m->get_banks()]);
	}

	/** POST api/customer/profile_image (multipart) */
	public function profile_image_post()
	{
		$account = $this->require_auth();

		$img_profile = $this->_handle_upload('img_profile', FCPATH.'data/customers/profile');
		if (!$img_profile) {
			return $this->fail('Gagal mengunggah foto profil', 422);
		}

		$this->Customer_m->update_profile_image($account->id, $img_profile);
		$this->ok(['img_profile' => $img_profile], 'Berhasil mengubah foto profil');
	}

	public function name_put()
	{
		$account = $this->require_auth();
		$first_name = $this->put('first_name');
		$last_name = $this->put('last_name');

		if (empty($first_name) || empty($last_name)) {
			return $this->validation_error(['first_name' => 'wajib diisi', 'last_name' => 'wajib diisi']);
		}

		$this->Customer_m->change_name($account->id, ['first_name' => $first_name, 'last_name' => $last_name]);
		$this->ok(null, 'Berhasil mengubah nama');
	}

	public function password_put()
	{
		$account = $this->require_auth();
		$this->load->library('ion_auth');

		$old = $this->put('old_password');
		$new = $this->put('new_password');

		if (empty($old) || empty($new)) {
			return $this->validation_error(['old_password' => 'wajib diisi', 'new_password' => 'wajib diisi']);
		}

		if ($this->ion_auth->change_password($account->email, $old, $new)) {
			$this->ok(null, 'Berhasil mengubah kata sandi');
		} else {
			$this->fail('Gagal mengubah kata sandi. Periksa kembali kata sandi lama', 400);
		}
	}

	/** GET api/customer/home -- header key optional */
	public function home_get()
	{
		$this->load->model('RentVehicle_m');
		$this->load->model('News_m');

		$account_id = null;
		$key = $this->input->get_request_header('key', TRUE);
		if (!empty($key)) {
			$key_row = $this->get_detail_key($key);
			$account_id = $key_row ? $key_row->account_id : null;
		}

		$data = [
			'vehicles_recomendation' => $this->RentVehicle_m->vehicles_recomendation($account_id),
			'promote_vehicles_recomendation' => $this->RentVehicle_m->promote_vehicles_recomendation($account_id),
			'news_preview' => $this->News_m->list_preview(),
		];

		if ($account_id) {
			$data['balance'] = $this->Customer_m->balance($account_id);
			$data['referal_code'] = $this->Customer_m->referal_code($account_id);
		}

		$this->ok($data);
	}

	/** GET api/customer/activate?id=..&code=.. */
	public function activate_get()
	{
		$this->load->library('ion_auth');
		$id = $this->get('id');
		$code = $this->get('code');

		if ($this->ion_auth->activate($id, $code)) {
			$this->ok(null, 'Akun berhasil diaktifkan');
		} else {
			$this->fail('Gagal mengaktifkan akun', 400, $this->ion_auth->errors());
		}
	}

	public function status_get()
	{
		$account = $this->require_auth();
		$this->load->model('Partner_m');
		$this->load->model('Chat_m');

		$partner_status = $this->Partner_m->get_status($account->id);

		$data = [
			'partner_chat_unread' => $this->Chat_m->partner_chatroom_unread($account->id),
			'customer_chat_unread' => $this->Chat_m->customer_chatroom_unread($account->id),
			'customer_status' => $this->Customer_m->get_status($account->id),
			'partner_status' => $partner_status,
			'maintenance' => $this->Basic_m->get_config('maintenance')->value,
			'android_app_version_code' => $this->Basic_m->get_config('android_app_version_code')->value,
			'android_app_version_name' => $this->Basic_m->get_config('android_app_version_name')->value,
		];

		if ($partner_status == 1) {
			$data['partner_features'] = $this->Partner_m->features($account->id);
		}

		$this->ok($data);
	}

	public function balance_get()
	{
		$account = $this->require_auth();
		$this->ok(['balance' => $this->Customer_m->balance($account->id)->balance]);
	}

	public function topup_config_get()
	{
		$this->ok([
			'topup_minimum' => $this->Basic_m->get_config_value('topup_minimum'),
			'banks' => $this->Basic_m->get_company_banks(),
		]);
	}

	/** POST api/customer/topups body: {value, company_bank_id} */
	public function topups_post()
	{
		$account = $this->require_auth();

		$value = $this->post('value');
		$company_bank_id = $this->post('company_bank_id');

		if (empty($value) || empty($company_bank_id)) {
			return $this->validation_error(['value' => 'wajib diisi', 'company_bank_id' => 'wajib diisi']);
		}

		$value_with_code = $this->Customer_m->get_unique_value_topup($value);
		$id = $this->Customer_m->add_request_topup([
			'account_id' => $account->id,
			'company_bank_id' => $company_bank_id,
			'value' => $value,
			'value_with_code' => $value_with_code,
			'status' => 1,
		]);

		$this->created(['id' => (int) $id, 'value_with_code' => $value_with_code], 'Berhasil mengirimkan permintaan topup');
	}

	public function topups_get($id = null)
	{
		$account = $this->require_auth();

		if (!empty($id)) {
			$detail = $this->Customer_m->topup_detail($id, $account->id);
			if (!$detail) return $this->not_found('Data topup tidak ditemukan');
			return $this->ok(['detail' => $detail]);
		}

		$page = (int) ($this->get('page') ?: 1);
		$limit = min((int) ($this->get('limit') ?: 10), 50);
		$this->ok(['topups' => $this->Customer_m->list_topup($account->id, ['page' => $page, 'limit' => $limit])]);
	}

	/** POST api/customer/topup_proof/{id} (multipart: img_proof) */
	public function topup_proof_post($id = null)
	{
		$account = $this->require_auth();

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$topup = $this->Customer_m->topup_detail($id, $account->id);
		if (!$topup) {
			return $this->not_found('Data topup tidak ditemukan');
		}

		$img_proof = $this->_handle_upload('img_proof', FCPATH.'data/customers/topup');
		if (!$img_proof) {
			return $this->fail('Gagal mengunggah bukti pembayaran', 422);
		}

		$this->Customer_m->update_topup($id, ['status' => 2, 'img_proof' => $img_proof], $account->id);
		$this->_notify_admins_topup($id);

		$this->ok(null, 'Berhasil mengirim bukti verifikasi topup');
	}

	private function _notify_admins_topup($topup_id)
	{
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));
		$this->fcm->setData(['data_type' => 'customer_topup', 'id' => $topup_id]);
		$this->fcm->setNotification(['title' => 'Pengisian Dana', 'text' => 'Verifikasi topup #'.$topup_id.' menunggu diproses']);
		$this->fcm->send();
	}

	public function withdraws_get($id = null)
	{
		$account = $this->require_auth();
		$page = (int) ($this->get('page') ?: 1);
		$limit = min((int) ($this->get('limit') ?: 10), 50);
		$this->ok(['withdraws' => $this->Customer_m->list_withdraw($account->id, ['page' => $page, 'limit' => $limit])]);
	}

	public function withdraw_config_get()
	{
		$account = $this->require_auth();
		$this->ok([
			'withdraw_minimum' => $this->Basic_m->get_config_value('withdraw_minimum'),
			'banks' => $this->Customer_m->banks($account->id),
		]);
	}

	/** POST api/customer/withdraws body: {value, account_bank_id} */
	public function withdraws_post()
	{
		$account = $this->require_auth();

		$value = $this->post('value');
		$account_bank_id = $this->post('account_bank_id');

		if (empty($value) || empty($account_bank_id)) {
			return $this->validation_error(['value' => 'wajib diisi', 'account_bank_id' => 'wajib diisi']);
		}

		$balance = $this->Customer_m->balance($account->id)->balance;
		if ($balance < $value) {
			return $this->fail('Saldo anda kurang dari permintaan pencairan', 402);
		}

		$id = $this->Customer_m->add_request_withdraw([
			'account_id' => $account->id, 'account_bank_id' => $account_bank_id, 'value' => $value, 'status' => 1,
		]);

		$this->created(['id' => (int) $id], 'Berhasil mengirimkan permintaan withdraw');
	}

	public function point_get()
	{
		$account = $this->require_auth();
		$this->ok(['point' => $this->Customer_m->balance($account->id)->point]);
	}

	public function point_exchange_config_get()
	{
		$this->ok([
			'exchange_point_minimum' => $this->Basic_m->get_config_value('exchange_point_minimum'),
			'rate_point_to_balance' => $this->Basic_m->get_config_value('rate_point_to_balance'),
		]);
	}

	/** POST api/customer/point/exchange body: {point} */
	public function point_exchange_post()
	{
		$account = $this->require_auth();
		$point = $this->post('point');

		if (empty($point)) {
			return $this->validation_error(['point' => 'wajib diisi']);
		}

		$current = $this->Customer_m->balance($account->id)->point;
		if ($current < $point) {
			return $this->fail('Poin anda kurang dari permintaan penukaran', 402);
		}

		$rate = (float) $this->Basic_m->get_config_value('rate_point_to_balance');

		$this->db->trans_start();
		$this->Customer_m->exchange_point_to_balance([
			'account_id' => $account->id, 'point_credit' => $point, 'description' => 'Penukaran Poin ke Saldo',
		], $rate);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->fail('Gagal menukar poin, silakan coba lagi', 500);
		}

		$this->ok(null, 'Berhasil menukar poin ke saldo');
	}

	public function point_transactions_get()
	{
		$account = $this->require_auth();
		$page = (int) ($this->get('page') ?: 1);
		$limit = min((int) ($this->get('limit') ?: 10), 50);
		$this->ok(['transaction_point' => $this->Customer_m->list_transaction_point($account->id, ['page' => $page, 'limit' => $limit])]);
	}

	public function location_put()
	{
		$account = $this->require_auth();
		$latitude = $this->put('latitude');
		$longitude = $this->put('longitude');

		if (empty($latitude) || empty($longitude)) {
			return $this->validation_error(['latitude' => 'wajib diisi', 'longitude' => 'wajib diisi']);
		}

		$this->Customer_m->update_customer_location($account->id, ['latitude' => $latitude, 'longitude' => $longitude]);
		$this->ok(null, 'Berhasil memperbaharui lokasi terakhir');
	}

	public function push_token_put()
	{
		$account = $this->require_auth();
		$token = $this->put('token');

		if (empty($token)) {
			return $this->validation_error(['token' => 'wajib diisi']);
		}

		$this->Customer_m->update_token($account->id, ['token' => $token]);
		$this->ok(null, 'Berhasil memperbaharui token');
	}
}

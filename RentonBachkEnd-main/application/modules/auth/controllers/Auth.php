<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Unified, stateless authentication endpoint for every non-mobile role
 * (admin, supervisor, staff, reader, agent). Replaces the old session-based
 * application/controllers/Auth.php: login here issues an API key (same
 * mechanism already used by customer/partner in the `api` module) instead of
 * setting a PHP session, so the whole backend authenticates the same way.
 *
 * Group ids (table `groups`): 1=admin, 2=supervisor, 3=staff, 4=partner,
 * 5=user(customer), 6=reader, 7=agent.
 */
class Auth extends REST_Base_Controller
{
	/** Login here is for every non-mobile role: admin panel staff + agent. */
	const STAFF_GROUP_IDS = [self::GROUP_ADMIN, self::GROUP_SUPERVISOR, self::GROUP_STAFF, self::GROUP_READER, self::GROUP_AGENT];

	public function __construct()
	{
		parent::__construct();
		$this->load->library('ion_auth');
		$this->load->model('Ion_auth_model');
	}

	public function index_get()
	{
		$this->ok(null, 'Auth API — RentOn');
	}

	/**
	 * POST auth/login
	 * body: { "email": "...", "password": "..." }
	 */
	public function login_post()
	{
		$identity = $this->post('email');
		$password = $this->post('password');

		if (empty($identity) || empty($password)) {
			return $this->validation_error(['email' => 'wajib diisi', 'password' => 'wajib diisi']);
		}

		if (!$this->Ion_auth_model->login($identity, $password)) {
			return $this->fail('Email atau kata sandi salah', 401);
		}

		$this->db->where('email', $identity);
		$account = $this->db->get('accounts')->row();

		if (!$account) {
			return $this->fail('Email atau kata sandi salah', 401);
		}

		if ((int) $account->active !== 1) {
			return $this->fail('Akun belum aktif', 403);
		}

		$this->db->select('group_id');
		$this->db->where('account_id', $account->id);
		$groups = $this->db->get('accounts_groups')->result();
		$group_ids = array_map(function ($g) { return (int) $g->group_id; }, $groups);

		if (empty(array_intersect(self::STAFF_GROUP_IDS, $group_ids))) {
			return $this->fail('Akun ini tidak memiliki akses ke panel admin/agent', 403);
		}

		// Reuse any existing active key instead of piling up a new row every login.
		$this->db->where('account_id', $account->id);
		$this->db->where('level >', 0);
		$existing_key = $this->db->get(config_item('rest_keys_table'))->row();

		if ($existing_key) {
			$key = $existing_key->{config_item('rest_key_column')};
		} else {
			$key = $this->_generate_key();
			$this->_insert_key($key, [
				'account_id'     => $account->id,
				'level'          => in_array(1, $group_ids, TRUE) ? 10 : 5,
				'ignore_limits'  => 1,
				'is_private_key' => 0,
			]);
		}

		$this->ok([
			'account_id' => (int) $account->id,
			'first_name' => $account->first_name,
			'last_name'  => $account->last_name,
			'email'      => $account->email,
			'groups'     => $group_ids,
			'key'        => $key,
		], 'Login berhasil');
	}

	/**
	 * POST auth/logout
	 * header: key
	 * Revokes the key used for this request (sets level to 0) so it can no
	 * longer authenticate anything -- the stateless equivalent of destroying
	 * a session.
	 */
	public function logout_post()
	{
		$account = $this->require_auth();

		$key = $this->input->get_request_header('key', TRUE);
		$this->db->where(config_item('rest_key_column'), $key);
		$this->db->set('level', 0);
		$this->db->update(config_item('rest_keys_table'));

		$this->ok(null, 'Berhasil logout');
	}

	/**
	 * POST auth/change_password
	 * header: key
	 * body: { "old_password": "...", "new_password": "..." }
	 */
	public function change_password_post()
	{
		$account = $this->require_auth();

		$old = $this->post('old_password');
		$new = $this->post('new_password');

		if (empty($old) || empty($new)) {
			return $this->validation_error(['old_password' => 'wajib diisi', 'new_password' => 'wajib diisi']);
		}

		if ($this->ion_auth->change_password($account->email, $old, $new)) {
			$this->ok(null, 'Berhasil mengubah kata sandi');
		} else {
			$this->fail('Gagal mengubah kata sandi. Periksa kembali kata sandi lama', 400);
		}
	}

	/**
	 * POST auth/forgot_password
	 * body: { "email": "..." }
	 * Sends the same reset-password email the old web flow used; the emailed
	 * link should point at a small standalone reset-password page (outside
	 * this API) that calls reset_password_post below.
	 */
	public function forgot_password_post()
	{
		$identity = $this->post('email');

		if (empty($identity)) {
			return $this->validation_error(['email' => 'wajib diisi']);
		}

		$this->db->where('email', $identity);
		$user = $this->db->get('accounts')->row();

		if (!$user) {
			// Do not reveal whether the email exists.
			return $this->ok(null, 'Jika email terdaftar, tautan reset kata sandi telah dikirim');
		}

		// forgotten_password() generates the reset code AND sends the email
		// itself (same CI email + template config the old web flow used) --
		// no need to build/send a second email here.
		$this->ion_auth->forgotten_password($identity);

		$this->ok(null, 'Jika email terdaftar, tautan reset kata sandi telah dikirim');
	}

	/**
	 * POST auth/reset_password
	 * body: { "code": "...", "new_password": "..." }
	 */
	public function reset_password_post()
	{
		$code = $this->post('code');
		$new  = $this->post('new_password');

		if (empty($code) || empty($new)) {
			return $this->validation_error(['code' => 'wajib diisi', 'new_password' => 'wajib diisi']);
		}

		$user = $this->ion_auth->forgotten_password_check($code);

		if (!$user) {
			return $this->fail('Kode reset tidak valid atau sudah kedaluwarsa', 400);
		}

		$identity_column = $this->config->item('identity', 'ion_auth');
		if ($this->ion_auth->reset_password($user->{$identity_column}, $new)) {
			$this->ok(null, 'Berhasil mengubah kata sandi');
		} else {
			$this->fail('Gagal mengubah kata sandi', 400);
		}
	}
}

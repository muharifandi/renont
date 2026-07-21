<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Agent's own profile, bank accounts, push-notification token and region lookup.
 *
 * GET    agent/config/profile               -> own profile detail
 * PUT    agent/config/profile                -> update own profile (multipart)
 * GET    agent/config/bank                   -> list own bank accounts (query: page, limit, search)
 * GET    agent/config/bank/{id}              -> bank account detail
 * POST   agent/config/bank                   -> add a bank account
 * PUT    agent/config/bank/{id}               -> edit a bank account
 * DELETE agent/config/bank/{id}               -> delete a bank account
 * PUT    agent/config/password                -> change own password
 * GET    agent/config/notification_count      -> admin-style notification badge counts
 * GET    agent/config/regency_select          -> typeahead lookup (query: search, page)
 * PUT    agent/config/push_token              -> update this agent's device push token
 */
class Config extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('agent/Config_m');
	}

	/**
	 * Read a field from either the parsed PUT body or the raw $_POST superglobal.
	 * Multipart bodies (file uploads) can't be parsed by CI's raw-input-stream PUT
	 * parser, so a PUT-with-files request is expected to arrive as a real POST with
	 * `_method=PUT` (the REST library's method-override) -- in that case $_POST is
	 * what's actually populated, hence the fallback.
	 */
	private function _field($key)
	{
		$value = $this->put($key);
		return ($value === null || $value === '') ? $this->post($key) : $value;
	}

	/** Uploads $_FILES[$field] (if present) into $path, generating a thumbnail + resized copy.
	 *  Falls back to $existing_filename when no new file was sent. */
	private function _handle_upload($field, $path, $existing_filename = null)
	{
		if (empty($_FILES[$field]['name'])) {
			return $existing_filename ?: null;
		}

		$config = [
			'upload_path' => $path,
			'allowed_types' => 'jpg|jpeg|png',
			'max_size' => '20480',
			'overwrite' => false,
		];
		$this->load->library('upload', $config, $field);

		if ($this->{$field}->do_upload($field)) {
			$file_name = $this->{$field}->data('file_name');
			$this->load->helper('image_manipulation');
			thumb_image($path.'/'.$file_name, $path.'/thumb_rentone_'.$file_name, 250);
			return resize_image($path.'/'.$file_name, $path.'/rentone_'.$file_name, 600, 1, TRUE);
		}
		return null;
	}

	public function index_get()
	{
		$this->ok(null, 'Agent Config API — RentOn');
	}

	/** GET agent/config/profile */
	public function profile_get()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);
		$this->ok(['agent' => $this->Config_m->get_profile_detail($account->id)]);
	}

	/**
	 * PUT agent/config/profile multipart body:
	 * {first_name,last_name,phone,regencies_id,identity_number,address,password,password_confirm,
	 *  img_profile,img_identity (files)}
	 */
	public function profile_put()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);
		$this->load->library('ion_auth');

		$user = $this->ion_auth->user($account->id)->row();

		$first_name = $this->_field('first_name');
		$last_name = $this->_field('last_name');
		$phone = $this->_field('phone');
		$regencies_id = $this->_field('regencies_id');
		$identity_number = $this->_field('identity_number');
		$address = $this->_field('address');
		$password = $this->_field('password');
		$password_confirm = $this->_field('password_confirm');

		$errors = [];
		if (empty($first_name)) $errors['first_name'] = 'wajib diisi';
		if (empty($last_name)) $errors['last_name'] = 'wajib diisi';
		if (empty($regencies_id)) $errors['regencies_id'] = 'wajib diisi';
		if (empty($identity_number)) $errors['identity_number'] = 'wajib diisi';

		if (!empty($password)) {
			$min_length = $this->config->item('min_password_length', 'ion_auth');
			if (strlen($password) < $min_length) {
				$errors['password'] = 'Panjang minimal '.$min_length.' karakter';
			} elseif ($password !== $password_confirm) {
				$errors['password_confirm'] = 'Tidak sama dengan password';
			}
		}

		if (!empty($errors)) {
			return $this->validation_error($errors);
		}

		$img_profile = $this->_handle_upload('img_profile', FCPATH.'data/agents/profile', $this->_field('img_profile_filename'));
		$img_identity = $this->_handle_upload('img_identity', FCPATH.'data/agents/files/identity', $this->_field('img_identity_filename'));

		$update_data = ['first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone];
		if (!empty($password)) $update_data['password'] = $password;

		if (!$this->ion_auth->update($user->id, $update_data)) {
			return $this->fail('Gagal memperbaharui profil', 400, $this->ion_auth->errors());
		}

		$agent_data = [
			'identity_number' => $identity_number,
			'regencies_id' => $regencies_id,
			'address' => $address,
		];
		if ($img_profile) $agent_data['img_profile'] = $img_profile;
		$this->Config_m->update_profile($account->id, $agent_data);

		$agent_file = [];
		if ($img_identity) $agent_file['img_identity'] = $img_identity;
		$this->Config_m->update_profile_file($account->id, $agent_file);

		$this->ok(null, 'Berhasil memperbaharui profil');
	}

	/** GET agent/config/bank?page=&limit=&search=  OR  GET agent/config/bank/{id} */
	public function bank_get($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		if (!empty($id)) {
			$bank = $this->Config_m->get_bank($account->id, $id);
			if (!$bank) {
				return $this->not_found('Bank tidak ditemukan');
			}
			return $this->ok($bank, 'Berhasil mengambil Bank');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Config_m->get_list_bank($account->id, $param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Config_m->get_total_list_bank_filtered($account->id, $param),
				'total_unfiltered' => (int) $this->Config_m->get_total_list_bank_unfiltered($account->id, $param),
				// master list of available bank institutions -- dropdown data folded in
				// from the old list_bank() page-view, per the no-more-views conversion.
				'bank_options' => $this->Config_m->get_all_bank(),
			]
		);
	}

	/** POST agent/config/bank body: {bank_id, name, bank_number} */
	public function bank_post()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		$bank_id = $this->post('bank_id');
		$name = $this->post('name');
		$bank_number = $this->post('bank_number');

		if (empty($bank_id) || empty($name) || empty($bank_number)) {
			return $this->validation_error(['bank_id' => 'wajib diisi', 'name' => 'wajib diisi', 'bank_number' => 'wajib diisi']);
		}

		$this->Config_m->add_bank($account->id, ['bank_id' => $bank_id, 'name' => $name, 'bank_number' => $bank_number]);
		$this->created(null, 'Berhasil menambahkan Bank');
	}

	/** PUT agent/config/bank/{id} body: {bank_id, name, bank_number} */
	public function bank_put($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$bank_id = $this->put('bank_id');
		$name = $this->put('name');
		$bank_number = $this->put('bank_number');

		if (empty($bank_id) || empty($name) || empty($bank_number)) {
			return $this->validation_error(['bank_id' => 'wajib diisi', 'name' => 'wajib diisi', 'bank_number' => 'wajib diisi']);
		}

		$this->Config_m->edit_bank($account->id, $id, ['bank_id' => $bank_id, 'name' => $name, 'bank_number' => $bank_number]);
		$this->ok(null, 'Berhasil mengubah Bank');
	}

	/** DELETE agent/config/bank/{id} -- was a plain, CSRF-less GET; moved to DELETE. */
	public function bank_delete($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->Config_m->delete_bank($account->id, $id);
		$this->ok(null, 'Berhasil menghapus Bank');
	}

	/** PUT agent/config/password body: {old, new, new_confirm} -- changes the AGENT's OWN password. */
	public function password_put()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);
		$this->load->library('ion_auth');

		$old = $this->put('old');
		$new = $this->put('new');
		$new_confirm = $this->put('new_confirm');

		$errors = [];
		if (empty($old)) $errors['old'] = 'wajib diisi';

		if (empty($new)) {
			$errors['new'] = 'wajib diisi';
		} else {
			$min_length = $this->config->item('min_password_length', 'ion_auth');
			if (strlen($new) < $min_length) {
				$errors['new'] = 'Panjang minimal '.$min_length.' karakter';
			} elseif ($new !== $new_confirm) {
				$errors['new'] = 'Tidak sama dengan konfirmasi password baru';
			}
		}

		if (empty($new_confirm)) $errors['new_confirm'] = 'wajib diisi';

		if (!empty($errors)) {
			return $this->validation_error($errors);
		}

		if ($this->ion_auth->change_password($account->email, $old, $new)) {
			$this->ok(null, 'Berhasil mengubah kata sandi');
		} else {
			$this->fail('Gagal mengubah kata sandi. Periksa kembali kata sandi lama', 400, $this->ion_auth->errors());
		}
	}

	/**
	 * PUT agent/config/push_token body: {token}
	 *
	 * Was save_admin_token($id,$token) -- a plain, CSRF-less GET that also called a
	 * model method (update_admin_token) that doesn't exist on agent/Config_m (only
	 * update_agent_token() does) -- guaranteed fatal error in the original. Fixed to
	 * call the real method and to only ever touch the caller's own account.
	 */
	public function push_token_put()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		$token = $this->put('token');
		if (empty($token)) {
			return $this->validation_error(['token' => 'wajib diisi']);
		}

		$this->Config_m->update_agent_token($account->id, $token);
		$this->ok(null, 'Berhasil memperbaharui token');
	}

	/**
	 * GET agent/config/notification_count
	 *
	 * Mirrors the platform-wide notification badge (verification queues, withdraw/topup
	 * requests, reward claims). The original loaded `Partner_m`/`Customer_m`/`PartnerReward_m`
	 * with no module prefix -- inside the agent module that resolves to agent/models/Partner_m.php
	 * (no *_unprocessed_count() methods there) for Partner_m, and to nothing at all for
	 * Customer_m/PartnerReward_m (agent module has no such models) -- every call would have
	 * fatally errored. Fixed to explicitly load the admin module's models, which is what
	 * actually defines these counters.
	 */
	public function notification_count_get()
	{
		$this->require_auth_group([self::GROUP_AGENT]);

		$this->load->model('admin/Partner_m', 'AdminPartner_m');
		$this->load->model('admin/Customer_m', 'AdminCustomer_m');
		$this->load->model('admin/Partnerreward_m', 'AdminPartnerReward_m');

		$partner_verification = $this->AdminPartner_m->partner_unprocessed_count();
		$partner_feature = $this->AdminPartner_m->partner_feature_unprocessed_count();
		$withdraw_request = $this->AdminCustomer_m->withdraw_unprocessed_count();
		$topup_request = $this->AdminCustomer_m->topup_unprocessed_count();
		$partner_claim_reward = $this->AdminPartnerReward_m->reward_unprocessed_count();

		$request = $withdraw_request + $topup_request + $partner_claim_reward;

		$this->ok([
			'notification' => 0,
			'partner' => (int) $partner_verification,
			'partner_verification' => (int) $partner_verification,
			'request' => (int) $request,
			'withdraw_request' => (int) $withdraw_request,
			'topup_request' => (int) $topup_request,
			'partner_claim_reward' => (int) $partner_claim_reward,
			'feature_request' => (int) $partner_feature,
			'support_request' => 0,
			'chat' => 0,
		]);
	}

	/** GET agent/config/regency_select?search=&page= -- typeahead lookup */
	public function regency_select_get()
	{
		$this->require_auth_group([self::GROUP_AGENT]);

		$search = $this->get('search');
		$page = (int) ($this->get('page') ?: 0);

		$param = ['search' => $search, 'limit' => ['start' => $page * 30, 'length' => 30]];

		$this->ok([
			'items' => $this->Config_m->get_list_regencies($param),
			'total_count' => (int) $this->Config_m->get_total_list_regencies_filtered($param),
		]);
	}
}

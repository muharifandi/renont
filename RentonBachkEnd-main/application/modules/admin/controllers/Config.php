<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Global settings, bank/feature/company-bank master data, and admin user
 * management (admin backoffice).
 *
 * GET  admin/config/settings                    -> global key/value config map
 * PUT  admin/config/settings                    -> update settings (body: any subset of keys)
 *
 * GET/POST admin/config/bank[/{id}]             -> bank CRUD (+ PUT/DELETE with {id})
 * GET/POST admin/config/feature[/{id}]          -> feature CRUD (+ PUT/DELETE with {id})
 * GET/POST admin/config/bank_company[/{id}]     -> company bank CRUD (+ PUT/DELETE with {id})
 *
 * GET  admin/config/admins                      -> list admin/staff users
 * POST admin/config/admins                      -> create admin/staff user
 * PUT  admin/config/admin/{id}                  -> update admin/staff user (incl. group membership)
 * PUT  admin/config/admin_password/{id}         -> change another user's password
 * PUT  admin/config/admin_activate/{id}         -> activate
 * PUT  admin/config/admin_deactivate/{id}       -> deactivate
 * DELETE admin/config/admin/{id}                -> delete
 *
 * PUT  admin/config/push_token                  -> save the caller's device push token
 * GET  admin/config/notification_count          -> badge counts for the admin panel
 * GET  admin/config/regency_select               -> typeahead lookup
 * GET  admin/config/bank_company_select          -> typeahead lookup
 */
class Config extends REST_Base_Controller
{
	const CONFIG_KEYS = [
		'admin_fee_use_percentage', 'admin_fee', 'referal_point_reward_partner', 'referal_point_reward_customer',
		'transaction_point_reward_partner', 'transaction_point_reward_customer', 'exchange_point_minimum',
		'rate_point_to_balance', 'topup_minimum', 'withdraw_minimum', 'distance_recomendation_rentvehicle',
		'distance_max_rentvehicle', 'maintenance', 'maintenance_message', 'android_app_version_code',
		'android_app_version_name', 'android_app_update_link', 'promote_price_per_day_rent_vehicle',
		'promote_max_rent_vehicle', 'promote_info_rent_vehicle', 'report_title', 'report_description',
	];

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Config_m');
	}

	// ---------------------------------------------------------------------
	// Global settings
	// ---------------------------------------------------------------------

	public function settings_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['config' => $this->Config_m->get_config(self::CONFIG_KEYS)]);
	}

	/** PUT admin/config/settings body: any subset of the config keys above */
	public function settings_put()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$post = array_intersect_key($this->input->post() ?: [], array_flip(self::CONFIG_KEYS));
		if (empty($post)) {
			return $this->validation_error(['*' => 'sertakan minimal satu pengaturan untuk diubah']);
		}

		$this->Config_m->set_config($post);
		$this->ok(null, 'Berhasil mengubah pengaturan');
	}

	// ---------------------------------------------------------------------
	// Bank / Feature / Company bank -- 3 identical CRUD groups
	// ---------------------------------------------------------------------

	private function _paginated_list($list_fn, $total_filtered_fn, $total_unfiltered_fn)
	{
		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Config_m->$list_fn($param),
			'Berhasil',
			['page' => $page, 'limit' => $limit, 'total' => (int) $this->Config_m->$total_filtered_fn($param), 'total_unfiltered' => (int) $this->Config_m->$total_unfiltered_fn($param)]
		);
	}

	public function bank_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!empty($id)) {
			$bank = $this->Config_m->get_bank($id);
			if (!$bank) return $this->not_found('Bank tidak ditemukan');
			return $this->ok(['data' => $bank]);
		}
		$this->_paginated_list('get_list_bank', 'get_total_list_bank_filtered', 'get_total_list_bank_unfiltered');
	}

	public function bank_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}
		$param = ['name' => $name, 'code' => $this->post('code')];
		if ($this->post('icon') != '') $param['icon'] = $this->post('icon');
		$this->Config_m->add_bank($param);
		$this->created(null, 'Berhasil menambahkan Bank');
	}

	public function bank_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);
		$param = ['name' => $this->put('name'), 'code' => $this->put('code')];
		if ($this->put('icon') != '') $param['icon'] = $this->put('icon');
		$this->Config_m->edit_bank($id, $param);
		$this->ok(null, 'Berhasil mengubah Bank');
	}

	public function bank_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);
		$this->Config_m->delete_bank($id);
		$this->ok(null, 'Berhasil menghapus Bank');
	}

	public function feature_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!empty($id)) {
			$feature = $this->Config_m->get_feature($id);
			if (!$feature) return $this->not_found('Layanan tidak ditemukan');
			return $this->ok(['data' => $feature]);
		}
		$this->_paginated_list('get_list_feature', 'get_total_list_feature_filtered', 'get_total_list_feature_unfiltered');
	}

	public function feature_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}
		$param = ['id' => $this->post('id'), 'name' => $name];
		if ($this->post('icon') != '') $param['icon'] = $this->post('icon');
		$this->Config_m->add_feature($param);
		$this->created(null, 'Berhasil menambahkan Layanan');
	}

	public function feature_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);
		$param = ['id' => $this->put('id'), 'name' => $this->put('name')];
		if ($this->put('icon') != '') $param['icon'] = $this->put('icon');
		$this->Config_m->edit_feature($id, $param);
		$this->ok(null, 'Berhasil mengubah Layanan');
	}

	public function feature_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);
		$this->Config_m->delete_feature($id);
		$this->ok(null, 'Berhasil menghapus Layanan');
	}

	public function bank_company_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!empty($id)) {
			$bank = $this->Config_m->get_bank_company($id);
			if (!$bank) return $this->not_found('Bank tidak ditemukan');
			return $this->ok(['data' => $bank]);
		}
		$this->_paginated_list('get_list_bank_company', 'get_total_list_bank_company_filtered', 'get_total_list_bank_company_unfiltered');
	}

	public function bank_company_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$bank_id = $this->post('bank_id');
		if (empty($bank_id)) {
			return $this->validation_error(['bank_id' => 'wajib diisi']);
		}
		$this->Config_m->add_bank_company(['bank_id' => $bank_id, 'name' => $this->post('name'), 'bank_number' => $this->post('bank_number')]);
		$this->created(null, 'Berhasil menambahkan Bank');
	}

	public function bank_company_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);
		$this->Config_m->edit_bank_company($id, ['bank_id' => $this->put('bank_id'), 'name' => $this->put('name'), 'bank_number' => $this->put('bank_number')]);
		$this->ok(null, 'Berhasil mengubah Bank');
	}

	public function bank_company_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);
		$this->Config_m->delete_bank_company($id);
		$this->ok(null, 'Berhasil menghapus Bank');
	}

	// ---------------------------------------------------------------------
	// Admin/staff user management (Ion Auth)
	// ---------------------------------------------------------------------

	public function admins_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['users' => $this->Config_m->get_admins(self::STAFF_GROUP_IDS)]);
	}

	/** POST admin/config/admins body: {first_name, last_name, identity?, email, phone, company, password, password_confirm} */
	public function admins_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->library('ion_auth');
		$this->load->library('form_validation');
		$this->lang->load('auth');

		$tables = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');

		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
		if ($identity_column !== 'email') {
			$this->form_validation->set_rules('identity', 'Identity', 'trim|required|is_unique['.$tables['users'].'.'.$identity_column.']');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		} else {
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique['.$tables['users'].'.email]');
		}
		$this->form_validation->set_rules('password', 'Password', 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');

		if ($this->form_validation->run() !== TRUE) {
			return $this->validation_error(['*' => validation_errors()]);
		}

		$email = strtolower($this->post('email'));
		$identity = ($identity_column === 'email') ? $email : $this->post('identity');
		$password = $this->post('password');
		$additional_data = [
			'first_name' => $this->post('first_name'),
			'last_name' => $this->post('last_name'),
			'company' => $this->post('company'),
			'phone' => $this->post('phone'),
		];

		$id = $this->ion_auth->register($identity, $password, $email, $additional_data, ['1']);
		if ($id) {
			$this->created(['id' => (int) $id], 'Berhasil menambahkan user admin');
		} else {
			$this->fail(implode(' ', (array) $this->ion_auth->errors()), 422);
		}
	}

	/** PUT admin/config/admin/{id} body: {first_name, last_name, phone, company, password?, password_confirm?, groups[]?} */
	public function admin_put($id = null)
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->library('ion_auth');
		$this->load->library('form_validation');

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$user = $this->ion_auth->user($id)->row();
		if (!$user) {
			return $this->not_found('User tidak ditemukan');
		}

		$this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
		$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');

		$password = $this->put('password');
		if ($password) {
			$this->form_validation->set_rules('password', 'Password', 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');
		}

		if ($this->form_validation->run() !== TRUE) {
			return $this->validation_error(['*' => validation_errors()]);
		}

		$data = [
			'first_name' => $this->put('first_name'),
			'last_name' => $this->put('last_name'),
			'company' => $this->put('company'),
			'phone' => $this->put('phone'),
		];
		if ($password) {
			$data['password'] = $password;
		}

		if (in_array(self::GROUP_ADMIN, $this->auth_group_ids, true)) {
			$this->ion_auth->remove_from_group('', $id);
			$group_ids = $this->put('groups');
			if (!empty($group_ids)) {
				foreach ($group_ids as $grp) {
					$this->ion_auth->add_to_group($grp, $id);
				}
			}
		}

		if ($this->ion_auth->update($user->id, $data)) {
			$this->ok(null, 'Berhasil mengubah user admin');
		} else {
			$this->fail(implode(' ', (array) $this->ion_auth->errors()), 422);
		}
	}

	/** PUT admin/config/admin_password/{id} body: {new, new_confirm} -- staff resetting another user's password */
	public function admin_password_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->library('ion_auth');

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$user = $this->ion_auth->user($id)->row();
		if (!$user) {
			return $this->not_found('User tidak ditemukan');
		}

		$new = $this->put('new');
		$new_confirm = $this->put('new_confirm');
		$min_length = (int) $this->config->item('min_password_length', 'ion_auth');

		if (empty($new) || strlen($new) < $min_length || $new !== $new_confirm) {
			return $this->validation_error(['new' => 'wajib diisi, minimal '.$min_length.' karakter, dan sama dengan new_confirm']);
		}

		// reset_password() (not change_password()) is used deliberately here: this is a staff
		// member resetting ANOTHER user's password, so there is no "old password" to verify --
		// change_password() would always fail since it requires the old password to match.
		if ($this->ion_auth->reset_password($user->email, $new)) {
			$this->ok(null, 'Berhasil mengubah kata sandi');
		} else {
			$this->fail(implode(' ', (array) $this->ion_auth->errors()), 400);
		}
	}

	public function admin_activate_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->library('ion_auth');
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);

		if ($this->ion_auth->activate($id)) {
			$this->ok(null, 'Berhasil mengaktifkan user');
		} else {
			$this->fail(implode(' ', (array) $this->ion_auth->errors()), 400);
		}
	}

	public function admin_deactivate_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->library('ion_auth');
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);

		if ($this->ion_auth->deactivate($id)) {
			$this->ok(null, 'Berhasil menonaktifkan user');
		} else {
			$this->fail(implode(' ', (array) $this->ion_auth->errors()), 400);
		}
	}

	public function admin_delete($id = null)
	{
		$this->require_auth_group([self::GROUP_ADMIN]);
		$this->load->library('ion_auth');
		if (empty($id)) return $this->validation_error(['id' => 'wajib diisi']);

		if ($this->ion_auth->delete_user($id)) {
			$this->ok(null, 'Berhasil menghapus user');
		} else {
			$this->fail(implode(' ', (array) $this->ion_auth->errors()), 400);
		}
	}

	// ---------------------------------------------------------------------
	// Misc
	// ---------------------------------------------------------------------

	/** PUT admin/config/push_token body: {token} */
	public function push_token_put()
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		$token = $this->put('token');
		if (empty($token)) {
			return $this->validation_error(['token' => 'wajib diisi']);
		}
		$this->Config_m->update_admin_token($account->id, $token);
		$this->ok(null, 'Token diperbaharui');
	}

	public function notification_count_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->model('Partner_m');
		$this->load->model('Customer_m');
		$this->load->model('PartnerReward_m');
		$this->load->model('Agent_m');

		$partner_verification = $this->Partner_m->partner_unprocessed_count();
		$partner_feature = $this->Partner_m->partner_feature_unprocessed_count();
		$withdraw_request = $this->Customer_m->withdraw_unprocessed_count();
		$agent_withdraw_request = $this->Agent_m->withdraw_unprocessed_count();
		$topup_request = $this->Customer_m->topup_unprocessed_count();
		$partner_claim_reward = $this->PartnerReward_m->reward_unprocessed_count();

		$this->ok([
			'notification' => 0,
			'partner' => $partner_verification,
			'partner_verification' => $partner_verification,
			'request' => $withdraw_request + $topup_request + $partner_claim_reward,
			'withdraw_request' => $withdraw_request,
			'agent_withdraw_request' => $agent_withdraw_request,
			'topup_request' => $topup_request,
			'partner_claim_reward' => $partner_claim_reward,
			'feature_request' => $partner_feature,
			'support_request' => 0,
			'chat' => 0,
		]);
	}

	public function regency_select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];
		$this->ok(['items' => $this->Config_m->get_list_regencies($param), 'total_count' => (int) $this->Config_m->get_total_list_regencies_filtered($param)]);
	}

	public function bank_company_select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];
		$this->ok(['items' => $this->Config_m->get_list_bank_company($param), 'total_count' => (int) $this->Config_m->get_total_list_bank_company_filtered($param)]);
	}
}

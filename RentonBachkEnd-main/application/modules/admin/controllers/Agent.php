<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Agent (Marketing) resource (admin backoffice).
 *
 * GET    admin/agent                          -> list (query: page, limit, search)
 * GET    admin/agent/{id}                      -> detail
 * GET    admin/agent/form_options               -> dropdown data (active status)
 * POST   admin/agent                            -> create (multipart: profile/identity files + ion_auth register)
 * POST   admin/agent/{id}                        -> update (multipart: profile/identity files + ion_auth update)
 *        (uses POST rather than PUT for both create and update so file uploads keep working --
 *        PHP never populates $_FILES for PUT requests, regardless of content type)
 * PUT    admin/agent/status/{id}                -> change active status (body: status_id)
 * DELETE admin/agent/{id}                        -> delete
 * GET    admin/agent/select                       -> typeahead lookup (query: search, page)
 * GET    admin/agent/commision                    -> commission tier list (query: page, limit, search)
 * GET    admin/agent/commision/{id}                -> commission tier detail
 * POST   admin/agent/commision                     -> add commission tier
 * PUT    admin/agent/commision/{id}                -> edit commission tier
 * DELETE admin/agent/commision/{id}                -> delete commission tier
 * GET    admin/agent/withdraw                       -> withdraw request list (query: page, limit, search)
 * GET    admin/agent/withdraw_form_options          -> dropdown data (withdraw status)
 * PUT    admin/agent/withdraw_status/{id}            -> change withdraw status (body: status_id, description)
 * GET    admin/agent/transaction                     -> transaction history list (query: page, limit, search)
 * POST   admin/agent/pair_partner                     -> pair a partner account with an agent account (body: agent_email, partner_email)
 */
class Agent extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Agent_m');
		$this->load->helper('image_manipulation');
	}

	/** GET admin/agent?page=&limit=&search=  |  GET admin/agent/{id} */
	public function index_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->Agent_m->detail($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail);
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Agent_m->get_list($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Agent_m->get_total_list_filtered($param),
				'total_unfiltered' => (int) $this->Agent_m->get_total_list_unfiltered($param),
			]
		);
	}

	/** GET admin/agent/form_options -- dropdown options for the list/status form */
	public function form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['active_status' => $this->Agent_m->get_active_status()]);
	}

	/**
	 * POST admin/agent            (multipart) -> create agent account
	 * POST admin/agent/{id}       (multipart) -> update agent account
	 */
	public function index_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			return $this->_update_agent($id);
		}
		return $this->_create_agent();
	}

	private function _handle_agent_upload($field, $path)
	{
		if (empty($_FILES[$field]['name'])) {
			$filename_field = $this->post($field.'_filename');
			return $filename_field ?: null;
		}

		$config['upload_path'] = $path;
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size'] = '20480';
		$config['overwrite'] = false;
		$this->load->library('upload', $config, $field.'upload');

		$uploader = $field.'upload';
		if ($this->{$uploader}->do_upload($field)) {
			$file_name = $this->{$uploader}->data('file_name');
			thumb_image($path.'/'.$file_name, $path.'/thumb_rentone_'.$file_name, 250);
			return resize_image($path.'/'.$file_name, $path.'/rentone_'.$file_name, 600, 1, TRUE);
		}

		return null;
	}

	private function _create_agent()
	{
		$this->load->model('Config_m');
		$this->lang->load('auth');
		$this->load->library('form_validation');
		$tables = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');

		$img_profile = $this->_handle_agent_upload('img_profile', FCPATH.'data/agents/profile');
		$img_identity = $this->_handle_agent_upload('img_identity', FCPATH.'data/agents/files/identity');

		$this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'trim|required');
		if ($identity_column !== 'email') {
			$this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'trim|required|is_unique['.$tables['users'].'.'.$identity_column.']');
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email');
		} else {
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email|is_unique['.$tables['users'].'.email]');
		}
		$this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');
		$this->form_validation->set_rules('regencies_id', 'Lokasi Wajib Diisi', 'required');
		$this->form_validation->set_rules('identity_number', 'No Identitas Wajib Diisi', 'required');

		if ($this->form_validation->run() !== TRUE) {
			return $this->validation_error($this->form_validation->error_array());
		}

		$email = strtolower($this->post('email'));
		$identity = ($identity_column === 'email') ? $email : $this->post('identity');
		$password = $this->post('password');

		$additional_data = [
			'first_name' => $this->post('first_name'),
			'last_name' => $this->post('last_name'),
			'phone' => $this->post('phone'),
		];

		$use_email = $this->config->item('email_activation', 'ion_auth');
		$result = $this->ion_auth->register($identity, $password, $email, $additional_data, array('7'));
		$id = $use_email ? (is_array($result) ? $result['id'] : null) : $result;

		if (!$id) {
			return $this->fail('Gagal menambahkan Marketing', 422, $this->ion_auth->errors());
		}

		$agent_data = array(
			'account_id' => $id,
			'identity_number' => $this->post('identity_number'),
			'regencies_id' => $this->post('regencies_id'),
			'address' => $this->post('address'),
		);
		if ($img_profile) {
			$agent_data['img_profile'] = $img_profile;
		}
		$this->Agent_m->register($agent_data);

		$agent_file = array('account_id' => $id);
		if ($img_identity) {
			$agent_file['img_identity'] = $img_identity;
		}
		$this->Agent_m->insert_agent_file($agent_file);

		$this->created(['account_id' => (int) $id], 'Berhasil menambahkan Marketing');
	}

	private function _update_agent($id)
	{
		$this->load->model('Config_m');
		$this->lang->load('auth');
		$this->load->library('form_validation');

		$user = $this->ion_auth->user($id)->row();
		if (!$user) {
			return $this->not_found();
		}

		$img_profile = $this->_handle_agent_upload('img_profile', FCPATH.'data/agents/profile');
		$img_identity = $this->_handle_agent_upload('img_identity', FCPATH.'data/agents/files/identity');

		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('regencies_id', 'Lokasi Wajib Diisi', 'required');
		$this->form_validation->set_rules('identity_number', 'No Identitas Wajib Diisi', 'required');

		if ($this->post('password')) {
			$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
		}

		if ($this->form_validation->run() !== TRUE) {
			return $this->validation_error($this->form_validation->error_array());
		}

		$data = [
			'first_name' => $this->post('first_name'),
			'last_name' => $this->post('last_name'),
			'phone' => $this->post('phone'),
		];
		if ($this->post('password')) {
			$data['password'] = $this->post('password');
		}

		if (!$this->ion_auth->update($user->id, $data)) {
			return $this->fail('Gagal mengubah Marketing', 422, $this->ion_auth->errors());
		}

		$agent_data = array(
			'identity_number' => $this->post('identity_number'),
			'regencies_id' => $this->post('regencies_id'),
			'address' => $this->post('address'),
		);
		if ($img_profile) {
			$agent_data['img_profile'] = $img_profile;
		}
		$this->Agent_m->update($id, $agent_data);

		$agent_file = array();
		if ($img_identity) {
			$agent_file['img_identity'] = $img_identity;
		}
		$this->Agent_m->update_agent_file($id, $agent_file);

		$this->ok(null, 'Berhasil mengubah Marketing');
	}

	/** PUT admin/agent/status/{id} body: {status_id} */
	public function status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Agent_m->update_active_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** DELETE admin/agent/{id} */
	public function index_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Agent_m->delete($id);
		$this->ion_auth->remove_from_group(self::GROUP_AGENT, $id);
		$this->ok(null, $id.' Dihapus');
	}

	/** GET admin/agent/select?search=&page= -- typeahead lookup */
	public function select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];

		$this->ok([
			'items' => $this->Agent_m->get_list($param),
			'total_count' => (int) $this->Agent_m->get_total_list_filtered($param),
		]);
	}

	/** GET admin/agent/commision?page=&limit=&search=  |  GET admin/agent/commision/{id} */
	public function commision_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->Agent_m->get_commision($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Komisi');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Agent_m->get_list_commision($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Agent_m->get_total_list_commision_filtered($param),
				'total_unfiltered' => (int) $this->Agent_m->get_total_list_commision_unfiltered($param),
			]
		);
	}

	/** POST admin/agent/commision body: {title, description, min_target, max_target, percentage} */
	public function commision_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$param = array(
			'title' => $this->post('title'),
			'description' => $this->post('description'),
			'min_target' => $this->post('min_target'),
			'max_target' => $this->post('max_target'),
			'percentage' => $this->post('percentage'),
		);
		$this->Agent_m->add_commision($param);
		$this->created(null, 'Berhasil menambahkan Komisi');
	}

	/** PUT admin/agent/commision/{id} body: {title, description, min_target, max_target, percentage} */
	public function commision_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = array(
			'title' => $this->put('title'),
			'description' => $this->put('description'),
			'min_target' => $this->put('min_target'),
			'max_target' => $this->put('max_target'),
			'percentage' => $this->put('percentage'),
		);
		$this->Agent_m->edit_commision($id, $param);
		$this->ok(null, 'Berhasil mengubah Komisi');
	}

	/** DELETE admin/agent/commision/{id} */
	public function commision_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Agent_m->delete_commision($id);
		$this->ok(null, 'Berhasil menghapus Komisi');
	}

	/** GET admin/agent/withdraw?page=&limit=&search= */
	public function withdraw_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Agent_m->get_list_withdraw($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Agent_m->get_total_list_withdraw_filtered($param),
				'total_unfiltered' => (int) $this->Agent_m->get_total_list_withdraw_unfiltered($param),
			]
		);
	}

	/** GET admin/agent/withdraw_form_options -- dropdown options for the withdraw list */
	public function withdraw_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['withdraw_status' => $this->Agent_m->get_withdraw_status()]);
	}

	/** PUT admin/agent/withdraw_status/{id} body: {status_id, description} */
	public function withdraw_status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$status_id = $this->put('status_id');
		$description = $this->put('description');
		$this->Agent_m->update_withdraw_status($id, $status_id, $description);

		$detail = $this->Agent_m->withdraw_detail($id);
		$status = $this->Agent_m->get_withdraw_status($status_id);

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		//kirim ke agent
		$this->fcm->addRecepient($this->Agent_m->get_token($detail->account_id));
		$data_payload = array(
			'data_type' => 'customer_withdraw',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Pencairan Dana", "text" => 'Permintaan #'.$id.' '.$status->name, 'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification

		$this->ok(null, $id.' Status diubah');
	}

	/** GET admin/agent/transaction?page=&limit=&search= */
	public function transaction_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Agent_m->get_list_transaction($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Agent_m->get_total_list_transaction_filtered($param),
				'total_unfiltered' => (int) $this->Agent_m->get_total_list_transaction_unfiltered($param),
			]
		);
	}

	/** POST admin/agent/pair_partner body: {agent_email, partner_email} */
	public function pair_partner_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$agent_email = $this->post('agent_email');
		$partner_email = $this->post('partner_email');

		if (empty($agent_email) || empty($partner_email)) {
			return $this->validation_error(['agent_email' => 'wajib diisi', 'partner_email' => 'wajib diisi']);
		}

		$identity_column = $this->config->item('identity', 'ion_auth');

		$agent = $this->ion_auth->where($identity_column, $agent_email)->users()->row();
		$is_agent = false;
		if ($agent) {
			foreach ($this->ion_auth->get_users_groups($agent->id)->result() as $val) {
				if ($val->id == self::GROUP_AGENT) {
					$is_agent = true;
				}
			}
		}

		$partner = $this->ion_auth->where($identity_column, $partner_email)->users()->row();
		$is_partner = false;
		if ($partner) {
			foreach ($this->ion_auth->get_users_groups($partner->id)->result() as $val) {
				if ($val->id == self::GROUP_PARTNER) {
					$is_partner = true;
				}
			}
		}

		if (!$is_agent || !$is_partner) {
			$errors = [];
			if (!$is_agent) {
				$errors['agent_email'] = 'Email Marketing yang dimasukan salah';
			}
			if (!$is_partner) {
				$errors['partner_email'] = 'Email Mitra yang dimasukan salah';
			}
			return $this->validation_error($errors);
		}

		$partner_agent_id = $this->Agent_m->get_partner_agent($partner->id);
		if ($partner_agent_id == $agent->id) {
			return $this->fail('Mitra sudah disandingkan dengan Marketing ini');
		}
		if ($partner_agent_id != null) {
			$partner_agent = $this->ion_auth->user($partner_agent_id)->row();
			return $this->fail('Mitra sudah disandingkan dengan Marketing '.$partner_agent->first_name.' '.$partner_agent->last_name);
		}

		$this->Agent_m->pair_partner($partner->id, $agent->id);
		$this->ok(null, 'Berhasil mensandingkan Marketing '.$agent->first_name.' '.$agent->last_name.' dengan Mitra '.$partner->first_name.' '.$partner->last_name);
	}
}

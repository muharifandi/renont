<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Partner resource (admin backoffice).
 *
 * GET    admin/partner                              -> list (query: page, limit, search)
 * GET    admin/partner/form_options                  -> dropdown data (active status)
 * PUT    admin/partner/status/{id}                   -> change active status (body: status_id)
 * DELETE admin/partner/{id}                          -> delete
 * GET    admin/partner/select                         -> typeahead lookup (query: search, page)
 * GET    admin/partner/register_request               -> registration request list (query: page, limit, search)
 * GET    admin/partner/register_request/{id}          -> registration request detail
 * POST   admin/partner/accept_request/{id}            -> accept registration request
 * POST   admin/partner/reject_request/{id}            -> reject registration request
 * GET    admin/partner/feature_request                 -> feature request list (query: page, limit, search)
 * GET    admin/partner/feature_request_form_options    -> dropdown data (feature status)
 * PUT    admin/partner/feature_request_status/{id}     -> change feature request status (body: status_id)
 */
class Partner extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Partner_m');
	}

	/** GET admin/partner?page=&limit=&search= */
	public function index_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			return $this->not_found();
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Partner_m->get_list($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Partner_m->get_total_list_filtered($param),
				'total_unfiltered' => (int) $this->Partner_m->get_total_list_unfiltered($param),
			]
		);
	}

	/** GET admin/partner/form_options -- dropdown options for the list/status form */
	public function form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['active_status' => $this->Partner_m->get_active_status()]);
	}

	/** PUT admin/partner/status/{id} body: {status_id} */
	public function status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Partner_m->update_active_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** DELETE admin/partner/{id} */
	public function index_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Partner_m->delete($id);
		$this->ion_auth->remove_from_group(self::GROUP_PARTNER, $id);
		$this->ok(null, $id.' Dihapus');
	}

	/** GET admin/partner/select?search=&page= -- typeahead lookup */
	public function select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];

		$this->ok([
			'items' => $this->Partner_m->get_list($param),
			'total_count' => (int) $this->Partner_m->get_total_list_filtered($param),
		]);
	}

	/** GET admin/partner/register_request?page=&limit=&search=  |  GET admin/partner/register_request/{id} */
	public function register_request_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->Partner_m->request_detail($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail);
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Partner_m->get_list_register_request($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Partner_m->get_total_list_register_request_filtered($param),
				'total_unfiltered' => (int) $this->Partner_m->get_total_list_register_request_unfiltered($param),
			]
		);
	}

	/** POST admin/partner/accept_request/{id} */
	public function accept_request_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('Customer_m');
		$this->load->model('api/Basic_m');

		$detail = $this->Partner_m->request_detail($id);

		if ($detail) {
			$this->Partner_m->accept_request($id);
			if ($detail->referal_id) {
				$data = array(
					'account_id' => $detail->referal_id,
					'target_id' => $id,
					'point_debit' => $this->Basic_m->get_config_value('referal_point_reward_partner'),
					'description' => 'Poin Referal',
				);
				$this->Basic_m->insert_point_reward($data);
			}
		}

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($id));
		$data_payload = array(
			'data_type' => 'partner_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Registrasi Mitra", "text" => "Permintaan menjadi Mitra diterima.", 'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification

		$this->ok(null, 'Berhasil menerima permintaan registrasi');
	}

	/** POST admin/partner/reject_request/{id} */
	public function reject_request_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('Customer_m');
		$this->Partner_m->reject_request($id);

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($id));
		$data_payload = array(
			'data_type' => 'partner_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Registrasi Mitra", "text" => "Permintaan menjadi Mitra ditolak. Lengkapi persyaratan sesuai ketentuan dan lakukan registrasi ulang", 'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification

		$this->ok(null, 'Berhasil menolak permintaan registrasi');
	}

	/** GET admin/partner/feature_request?page=&limit=&search= */
	public function feature_request_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Partner_m->get_list_feature_request($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Partner_m->get_total_list_feature_request_filtered($param),
				'total_unfiltered' => (int) $this->Partner_m->get_total_list_feature_request_unfiltered($param),
			]
		);
	}

	/** GET admin/partner/feature_request_form_options -- dropdown options for the feature request list */
	public function feature_request_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['feature_status' => $this->Partner_m->get_feature_status()]);
	}

	/** PUT admin/partner/feature_request_status/{id} body: {status_id} */
	public function feature_request_status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Partner_m->update_feature_request_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}
}

<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Customer resource (admin backoffice).
 *
 * GET    admin/customer                     -> list (query: page, limit, search)
 * GET    admin/customer/{id}                -> detail
 * GET    admin/customer/form_options        -> dropdown data (active status) for the list/status form
 * PUT    admin/customer/status/{id}         -> change active status (body: status_id)
 * POST   admin/customer/accept_request/{id} -> accept registration request
 * POST   admin/customer/reject_request/{id} -> reject registration request
 * GET    admin/customer/topup               -> topup request list (query: page, limit, search)
 * GET    admin/customer/topup_form_options  -> dropdown data (topup status)
 * PUT    admin/customer/topup_status/{id}   -> change topup status (body: status_id)
 * GET    admin/customer/withdraw             -> withdraw request list (query: page, limit, search)
 * GET    admin/customer/withdraw_form_options -> dropdown data (withdraw status)
 * PUT    admin/customer/withdraw_status/{id} -> change withdraw status (body: status_id, description)
 * GET    admin/customer/select                -> typeahead lookup (query: search, page)
 */
class Customer extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Customer_m');
	}

	/** GET admin/customer?page=&limit=&search=  |  GET admin/customer/{id} */
	public function index_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->Customer_m->detail($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail);
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Customer_m->get_list($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Customer_m->get_total_list_filtered($param),
				'total_unfiltered' => (int) $this->Customer_m->get_total_list_unfiltered($param),
			]
		);
	}

	/** GET admin/customer/form_options -- dropdown options for the list/status form */
	public function form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['active_status' => $this->Customer_m->get_active_status()]);
	}

	/**
	 * Shared logic for accept/reject/status-change: update active status + push FCM notification.
	 * Not an endpoint by itself -- called from status_put(), accept_request_post(), reject_request_post().
	 */
	private function _set_active_status($id, $status_id)
	{
		$this->Customer_m->update_active_status($id, $status_id);
		$status = $this->Customer_m->get_active_status($status_id);

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($id));
		$data_payload = array(
			'data_type' => 'customer_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Registrasi Pelanggan", "text" => "Status akun pelanggan ".$status->name.". Hubungi admin untuk keterangan lebih lanjut", 'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
	}

	/** PUT admin/customer/status/{id} body: {status_id} */
	public function status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->_set_active_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** POST admin/customer/accept_request/{id} */
	public function accept_request_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('api/Basic_m');

		$detail = $this->Customer_m->detail($id);

		if (!$detail) {
			return $this->not_found();
		}

		$this->_set_active_status($id, 1);
		if ($detail->referal_id) {
			$data = array(
				'account_id' => $detail->referal_id,
				'target_id' => $id,
				'point_debit' => $this->Basic_m->get_config_value('referal_point_reward_customer'),
				'description' => 'Poin Referal',
			);
			$this->Basic_m->insert_point_reward($data);
		}

		$this->ok(null, 'Berhasil menerima permintaan registrasi');
	}

	/** POST admin/customer/reject_request/{id} */
	public function reject_request_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->_set_active_status($id, 2);
		$this->ok(null, 'Berhasil menolak permintaan registrasi');
	}

	/** GET admin/customer/topup?page=&limit=&search= */
	public function topup_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Customer_m->get_list_topup($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Customer_m->get_total_list_topup_filtered($param),
				'total_unfiltered' => (int) $this->Customer_m->get_total_list_topup_unfiltered($param),
			]
		);
	}

	/** GET admin/customer/topup_form_options -- dropdown options for the topup list */
	public function topup_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['topup_status' => $this->Customer_m->get_topup_status()]);
	}

	/** PUT admin/customer/topup_status/{id} body: {status_id} */
	public function topup_status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$status_id = $this->put('status_id');
		$this->Customer_m->update_topup_status($id, $status_id);

		$detail = $this->Customer_m->topup_detail($id);
		$status = $this->Customer_m->get_topup_status($status_id);

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($detail->account_id));
		$data_payload = array(
			'data_type' => 'customer_withdraw',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Pengisian Dana", "text" => 'Permintaan #'.$id.' '.$status->name, 'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification

		$this->ok(null, $id.' Status diubah');
	}

	/** GET admin/customer/withdraw?page=&limit=&search= */
	public function withdraw_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Customer_m->get_list_withdraw($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Customer_m->get_total_list_withdraw_filtered($param),
				'total_unfiltered' => (int) $this->Customer_m->get_total_list_withdraw_unfiltered($param),
			]
		);
	}

	/** GET admin/customer/withdraw_form_options -- dropdown options for the withdraw list */
	public function withdraw_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['withdraw_status' => $this->Customer_m->get_withdraw_status()]);
	}

	/** PUT admin/customer/withdraw_status/{id} body: {status_id, description} */
	public function withdraw_status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$status_id = $this->put('status_id');
		$description = $this->put('description');
		$this->Customer_m->update_withdraw_status($id, $status_id, $description);

		$detail = $this->Customer_m->withdraw_detail($id);
		$status = $this->Customer_m->get_withdraw_status($status_id);

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($detail->account_id));
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

	/** GET admin/customer/select?search=&page= -- typeahead lookup */
	public function select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];

		$this->ok([
			'items' => $this->Customer_m->get_list_account($param),
			'total_count' => (int) $this->Customer_m->get_total_list_account_filtered($param),
		]);
	}
}

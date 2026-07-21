<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Voucher resource (admin backoffice).
 *
 * GET    admin/voucher              -> list (query: page, limit, search)
 * POST   admin/voucher               -> create
 * DELETE admin/voucher/{id}          -> delete
 * PUT    admin/voucher/status/{id}   -> change status
 * GET    admin/voucher/form_options  -> dropdown data for create/edit form
 * GET    admin/voucher/select        -> typeahead lookup (query: search, page)
 */
class Voucher extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Voucher_m');
	}

	/** GET admin/voucher?page=&limit=&search= */
	public function index_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Voucher_m->get_list($param),
			'Berhasil',
			['page' => $page, 'limit' => $limit, 'total' => (int) $this->Voucher_m->get_total_list_filtered($param), 'total_unfiltered' => (int) $this->Voucher_m->get_total_list_unfiltered($param)]
		);
	}

	/** POST admin/voucher body: {code, user_type, voucher_type, value, description, use_expire, start_date, end_date, use_quota, quota, feature} */
	public function index_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$code = $this->post('code');
		if (empty($code)) {
			return $this->validation_error(['code' => 'wajib diisi']);
		}

		$feature = $this->post('feature');

		$this->Voucher_m->add_voucher([
			'code' => $code,
			'user_type' => $this->post('user_type'),
			'voucher_type' => $this->post('voucher_type'),
			'value' => $this->post('value'),
			'description' => $this->post('description'),
			'use_expire' => $this->post('use_expire'),
			'start_date' => date('Y-m-d', strtotime($this->post('start_date'))),
			'end_date' => date('Y-m-d', strtotime($this->post('end_date'))),
			'use_quota' => $this->post('use_quota'),
			'quota' => $this->post('quota'),
			'feature_id' => ($feature == -1) ? null : $feature,
		]);

		$this->created(null, 'Berhasil menambahkan voucher');
	}

	/** DELETE admin/voucher/{id} */
	public function index_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Voucher_m->delete($id);
		$this->ok(null, $id.' Dihapus');
	}

	/** PUT admin/voucher/status/{id} body: {status_id} */
	public function status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->Voucher_m->update_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** GET admin/voucher/form_options -- dropdown options for the create/edit form */
	public function form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->model('Base_m');

		$this->ok([
			'status' => $this->Base_m->get_status(),
			'voucher_type' => $this->Voucher_m->get_voucher_type(),
			'user_type' => $this->Base_m->get_user_type_filtered(),
			'feature' => $this->Base_m->get_feature(),
		]);
	}

	/** GET admin/voucher/select?search=&page= -- typeahead lookup */
	public function select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];

		$this->ok([
			'items' => $this->Voucher_m->get_list($param),
			'total_count' => (int) $this->Voucher_m->get_total_list_filtered($param),
		]);
	}
}

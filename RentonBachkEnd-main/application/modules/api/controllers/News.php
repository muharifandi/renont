<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * In-app news / promo content resource.
 *
 * GET api/news          -> list (query: page, limit; header key required)
 * GET api/news/{id}      -> detail (public, no key required)
 */
class News extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('News_m');
	}

	public function index_get($id = null)
	{
		if (!empty($id)) {
			$this->load->model('Basic_m');
			$detail = $this->News_m->detail($id);
			if (!$detail) {
				return $this->not_found('Berita tidak ditemukan');
			}
			return $this->ok([
				'detail' => $detail,
				'voucher' => $this->Basic_m->get_voucher($detail->voucher_id),
			]);
		}

		$account = $this->require_auth();
		$this->load->model('Partner_m');

		$partner_valid = $this->Partner_m->check_account_valid($account->id);
		$param = [
			'page' => (int) ($this->get('page') ?: 1),
			'limit' => min((int) ($this->get('limit') ?: 10), 50),
			'partner_valid' => $partner_valid,
		];

		$this->ok(['news' => $this->News_m->list($param) ?: []]);
	}
}

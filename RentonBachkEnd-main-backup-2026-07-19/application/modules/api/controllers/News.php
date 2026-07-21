<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class News extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('News_m');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Basic",200);
	}
	
	public function list_post()
	{
		$this->load->model('Partner_m');
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$partner_valid = $this->Partner_m->check_account_valid($account_id);
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
			'partner_valid' => $partner_valid,
		);
		$result = $this->News_m->list($param);
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'news' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'news' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function detail_post()
	{
		$this->load->model('Basic_m');
		$detail = $this->News_m->detail($this->post('id'));
		
		$voucher = $this->Basic_m->get_voucher($detail->voucher_id);
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'detail' => $detail,
			'voucher' => $voucher,
		);
		$this->response($response,200);
	}
	
}
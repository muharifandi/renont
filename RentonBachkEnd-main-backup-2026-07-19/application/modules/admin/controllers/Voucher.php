<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher extends AdminController
{
	public $data = [];

	public function __construct()
	{
		parent::__construct();
		
		
		$this->load->model('admin/Voucher_m');
		
	}
	
	public function index()
	{
		redirect('admin/dashboard', 'refresh');
	}
	
	public function list()
	{
		$this->load->model('Base_m');
	
		$list_status = $this->Base_m->get_status();
		$user_type = $this->Base_m->get_user_type_filtered();
		$feature = $this->Base_m->get_feature();
		$list_type_voucher = $this->Voucher_m->get_voucher_type();
		$result = array(
			'list_status' => $list_status, 
			'list_type_voucher' => $list_type_voucher, 
			'user_type' => $user_type, 
			'feature' => $feature, 
		);
		$this->show('voucher_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Voucher_m->get_list($param),
			"recordsTotal" => $this->Voucher_m->get_total_list_unfiltered($param),
			"recordsFiltered" => $this->Voucher_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function change_status($id)
	{
		$status_id = $this->input->post('status_id');
		$this->Voucher_m->update_status($id,$status_id);
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function add_voucher()
	{
		$param = array(
			'code' => $this->input->post('code'),
			'user_type' => $this->input->post('user_type'),
			'voucher_type' => $this->input->post('voucher_type'),
			'value' => $this->input->post('value'),
			'description' => $this->input->post('description'),
			'use_expire' => $this->input->post('use_expire'),
			'start_date' => date("Y-m-d", strtotime($this->input->post('start_date'))),
			'end_date' => date("Y-m-d", strtotime($this->input->post('end_date'))),
			'use_quota' => $this->input->post('use_quota'),
			'quota' => $this->input->post('quota'),
		);
		
		if($this->input->post('feature') == -1)
			$param['feature_id'] = null;
		else
			$param['feature_id'] = $this->input->post('feature');
		
		$this->Voucher_m->add_voucher($param);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menambahkan voucher',
		);
		echo json_encode($result);
	}
	
	public function delete($id)
	{
		$this->Voucher_m->delete($id);
		$result = array(
			'status' => true,
			'message' => $id.' Dihapus',
		);
		echo json_encode($result);
	}
	
	public function get_voucher_select()
	{
		$search = $this->input->get('search');
		$page = $this->input->get('page');
		$current_page = 0;
		
		if($page)
		{
			$current_page = $page;
		}
			
			
		$param = array(
			'search' => $search,
			'limit' => array('start'=>$current_page * 30,'length'=>30),
		);
		
		$result = array(
			'items' => $this->Voucher_m->get_list($param),
			"total_count" => $this->Voucher_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	
}
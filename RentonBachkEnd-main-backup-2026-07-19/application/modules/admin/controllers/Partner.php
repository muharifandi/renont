<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Partner extends AdminController
{
	public $data = [];

	public function __construct()
	{
		parent::__construct();
		
		
		$this->load->model('admin/Partner_m');
		
	}
	
	public function get_partners_select()
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
			'items' => $this->Partner_m->get_list($param),
			"total_count" => $this->Partner_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function list_register_request()
	{
		$this->show('partner_request_list',$data,TRUE);
	}
	
	public function get_list_register_request()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Partner_m->get_list_register_request($param),
			"recordsTotal" => $this->Partner_m->get_total_list_register_request_unfiltered($param),
			"recordsFiltered" => $this->Partner_m->get_total_list_register_request_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function request_detail($user_id)
	{
		$result = array(
			'detail' => $this->Partner_m->request_detail($user_id),
		);
		$this->show('partner_request_detail',$result,TRUE);
	}
	
	public function accept_request($id)
	{
		$this->load->model('Customer_m');
		$this->load->model('api/Basic_m');
		
		$detail = $this->Partner_m->request_detail($id);

		if($detail)
		{
			$this->Partner_m->accept_request($id);
			if($detail->referal_id)
			{
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
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($id));
		$data_payload = array(
			'data_type' => 'partner_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Registrasi Mitra", "text" => "Permintaan menjadi Mitra diterima.",'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
		
		redirect('admin/partner/list_register_request', 'refresh');
	}
	
	public function reject_request($id)
	{
		$this->load->model('Customer_m');
		$this->Partner_m->reject_request($id);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($id));
		$data_payload = array(
			'data_type' => 'partner_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Registrasi Mitra", "text" => "Permintaan menjadi Mitra ditolak. Lengkapi persyaratan sesuai ketentuan dan lakukan registrasi ulang",'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
		redirect('admin/partner/list_register_request', 'refresh');
	}
	
	public function list()
	{
		$list_active_status = $this->Partner_m->get_active_status();
		$result = array(
			'list_active_status' => $list_active_status, 
		);
		$this->show('partner_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Partner_m->get_list($param),
			"recordsTotal" => $this->Partner_m->get_total_list_unfiltered($param),
			"recordsFiltered" => $this->Partner_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function change_status($id)
	{
		$status_id = $this->input->post('status_id');
		$this->Partner_m->update_active_status($id,$status_id);
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function delete($id)
	{
		$this->Partner_m->delete($id);
		$this->ion_auth->remove_from_group(4, $id);
		$result = array(
			'status' => true,
			'message' => $id.' Dihapus',
		);
		echo json_encode($result);
	}
	
	public function list_feature_request()
	{
		$list_feature_status = $this->Partner_m->get_feature_status();
		$result = array(
			'list_feature_status' => $list_feature_status, 
		);
		$this->show('partner_feature_request_list',$result,TRUE);
	}
	
	public function get_list_feature_request()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Partner_m->get_list_feature_request($param),
			"recordsTotal" => $this->Partner_m->get_total_list_feature_request_unfiltered($param),
			"recordsFiltered" => $this->Partner_m->get_total_list_feature_request_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function verification_feature_request($id)
	{
		$status_id = $this->input->post('status_id');
		$this->Partner_m->update_feature_request_status($id,$status_id);
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
}
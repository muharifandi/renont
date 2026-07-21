 <?php defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends AdminController
{
	public $data = [];

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Customer_m');
		
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function list()
	{
		$list_active_status = $this->Customer_m->get_active_status();
		$result = array(
			'list_active_status' => $list_active_status, 
		);
		$this->show('customer_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Customer_m->get_list($param),
			"recordsTotal" => $this->Customer_m->get_total_list_unfiltered($param),
			"recordsFiltered" => $this->Customer_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function detail($id)
	{
		$detail = $this->Customer_m->detail($id);
		$result = array(
			'detail' => $detail, 
		);
		$this->show('customer_detail',$result,TRUE);
	}
	
	public function accept_request($id)
	{
		
		$this->load->model('api/Basic_m');
		
		$detail = $this->Customer_m->detail($id);

		if($detail)
		{
			$this->change_status($id,1);
			if($detail->referal_id)
			{
				$data = array(
					'account_id' => $detail->referal_id,
					'target_id' => $id,
					'point_debit' => $this->Basic_m->get_config_value('referal_point_reward_customer'),
					'description' => 'Poin Referal',
				);
				$status = $this->Basic_m->insert_point_reward($data);
			}
		}
		
		redirect('admin/customer/list', 'refresh');
	}
	
	public function reject_request($id)
	{
		$this->change_status($id,2);
		redirect('admin/customer/list', 'refresh');		
	}
	
	public function change_status($id,$status_id = null)
	{
		
		if($status_id == null)
			$status_id = $this->input->post('status_id');
		
		$this->Customer_m->update_active_status($id,$status_id);
		$status = $this->Customer_m->get_active_status($status_id);
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($id));
		$data_payload = array(
			'data_type' => 'customer_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Registrasi Pelanggan", "text" => "Status akun pelanggan ".$status->name.". Hubungi admin untuk keterangan lebih lanjut",'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		
		//if($status_id == null)
			echo json_encode($result);
	}
	
	public function list_topup()
	{
		$list_topup_status = $this->Customer_m->get_topup_status();
		$result = array(
			'list_topup_status' => $list_topup_status, 
		);
		$this->show('topup_list',$result,TRUE);
	}
	
	public function get_list_topup()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Customer_m->get_list_topup($param),
			"recordsTotal" => $this->Customer_m->get_total_list_topup_unfiltered($param),
			"recordsFiltered" => $this->Customer_m->get_total_list_topup_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function verification_topup($id)
	{
		$status_id = $this->input->post('status_id');
		$this->Customer_m->update_topup_status($id,$status_id);
		
		$detail = $this->Customer_m->topup_detail($id);
		$status = $this->Customer_m->get_topup_status($status_id);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($detail->account_id));
		$data_payload = array(
			'data_type' => 'customer_withdraw',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Pengisian Dana", "text" => 'Permintaan #'.$id.' '.$status->name ,'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function list_withdraw()
	{
		$list_withdraw_status = $this->Customer_m->get_withdraw_status();
		$result = array(
			'list_withdraw_status' => $list_withdraw_status, 
		);
		$this->show('withdraw_list',$result,TRUE);
	}
	
	public function get_list_withdraw()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Customer_m->get_list_withdraw($param),
			"recordsTotal" => $this->Customer_m->get_total_list_withdraw_unfiltered($param),
			"recordsFiltered" => $this->Customer_m->get_total_list_withdraw_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function verification_withdraw($id)
	{
		$status_id = $this->input->post('status_id');
		$description = $this->input->post('description');
		$this->Customer_m->update_withdraw_status($id,$status_id,$description);
		
		$detail = $this->Customer_m->withdraw_detail($id);
		$status = $this->Customer_m->get_withdraw_status($status_id);
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($detail->account_id));
		$data_payload = array(
			'data_type' => 'customer_withdraw',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Pencairan Dana", "text" => 'Permintaan #'.$id.' '.$status->name ,'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function get_accounts_select()
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
			'items' => $this->Customer_m->get_list_account($param),
			"total_count" => $this->Customer_m->get_total_list_account_filtered($param),
		);
		echo json_encode($result);
	}
	
	
}
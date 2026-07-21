<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PartnerReward extends AdminController
{
	public $data = [];

	public function __construct()
	{
		parent::__construct();
		
		
		$this->load->model('PartnerReward_m');
		
	}
	
	public function index()
	{
		redirect('admin/dashboard', 'refresh');
	}
	
	public function list()
	{
		$this->load->model('Base_m');
	
		$list_status = $this->Base_m->get_status();
		
		$feature = $this->Base_m->get_feature();
		$list_type_reward = $this->PartnerReward_m->get_reward_type();
		$list_scope_reward = $this->PartnerReward_m->get_reward_scope();
		$result = array(
			'list_status' => $list_status, 
			'list_type_reward' => $list_type_reward, 
			'list_scope_reward' => $list_scope_reward, 
			'feature' => $feature, 
		);
		$this->show('partner_reward_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->PartnerReward_m->get_list($param),
			"recordsTotal" => $this->PartnerReward_m->get_total_list_unfiltered($param),
			"recordsFiltered" => $this->PartnerReward_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function change_status($id)
	{
		$status_id = $this->input->post('status_id');
		$this->PartnerReward_m->update_status($id,$status_id);
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	
	public function add_reward()
	{
		$img_filename = null;
		$upload_log = null;
		if($_FILES["img"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/rewards';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config);
		
			if ($this->upload->do_upload('img')) {
				$img_filename = $this->upload->data("file_name");
			}else
				$upload_log .= "profile:".$this->upload->display_errors()."\n";
		}
		
		$param = array(
			'title' => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'img' => $img_filename,
			'feature_id' => $this->input->post('feature_id'),
			'reward_scope' => $this->input->post('reward_scope'),
			'reward_type' => $this->input->post('reward_type'),
			'target' => $this->input->post('target'),
			'point_reward' => $this->input->post('point_reward'),
			'status' => 0,
		);
		
		$this->PartnerReward_m->add_reward($param);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menambahkan hadiah',
			'log' => $upload_log
		);
		echo json_encode($result);
	}
	
	public function delete($id)
	{
		$this->PartnerReward_m->delete($id);
		$result = array(
			'status' => true,
			'message' => $id.' Dihapus',
		);
		echo json_encode($result);
	}
	
	public function list_claim()
	{
		$this->load->model('Base_m');
	
		$this->show('partner_claim_reward_list',$result,TRUE);
	}
	
	public function get_list_claim()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->PartnerReward_m->get_list_claim_reward($param),
			"recordsTotal" => $this->PartnerReward_m->get_total_list_claim_reward_unfiltered($param),
			"recordsFiltered" => $this->PartnerReward_m->get_total_list_claim_reward_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function process($id)
	{
		$this->load->model('Customer_m');
		$data = array(
			'processed' => 1
		);
		$this->PartnerReward_m->update_history_reward($id,$data);
		$detail = $this->PartnerReward_m->history_reward($id);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($detail->account_id));
		$data_payload = array(
			'data_type' => 'partner_claim_reward',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Klaim Hadiah", "text" => 'Permintaan Klaim Hadiah sedang diproses','android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		
		$result = array(
			'status' => true,
			'message' => $id.' Berhasil diubah',
		);
		echo json_encode($result);
	}
	
}
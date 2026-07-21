<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class PartnerReward extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('PartnerReward_m');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Basic",200);
	}
	
	public function list_scope_post()
	{
		$list_scope = $this->PartnerReward_m->list_scope();
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'data' => $list_scope,
		);
		$this->response($response,200);
		
	}
	
	public function detail_post()
	{
		$this->load->model('Partner_m');
		$this->load->model('PartnerRent_m');
		
		$reward_scope = $this->post('reward_scope');
		
		if($reward_scope == null)
		{
			$list_scope = $this->PartnerReward_m->list_scope();
			
			if(count($list_scope) > 0)
				$reward_scope = $list_scope[0]->id;
		}
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$feature = $this->Partner_m->list_feature($account_id);
		
		$reward_scope_detail = $this->PartnerReward_m->reward_scope_detail($reward_scope);
		$start_date = date("Y-m-d", strtotime($reward_scope_detail->start));
		$end_date = date("Y-m-d", strtotime($reward_scope_detail->end));
		
		$data = array();
		foreach($feature as $val)
		{
			$detail = new stdClass();
			$detail->feature_name = $val->name;
			$detail->transaction_success = $this->PartnerRent_m->count_transaction_success($account_id,$start_date,$end_date);
			$detail->rewards = $this->PartnerReward_m->reward_aquired($account_id,$val->id,$reward_scope,$start_date,$end_date);
			
			$data[] = $detail;
		}
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'data' => $data,
		);
		$this->response($response,200);
		
	}
	
	public function claim_item_reward_post()
	{
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$reward_id = $this->post('reward_id');
		$this->PartnerReward_m->claim_reward($reward_id);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
        $this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke mitra
		$this->fcm->addRecepient($this->Customer_m->get_token($account_id));
		$data_payload = array(
			'data_type' => 'partner_reward_claim',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Klaim Hadiah", "text" => "Klaim Hadiah Sedang diproses",'android_channel_id' => 4, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		
		//kirim ke semua admin
		$this->fcm->clearRecepients();
		$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
		$data_payload = array(
			'data_type' => 'partner_reward_claim',
			'id' => $id,
			'link_action' => base_url().'admin/partnerReward/list_claim'
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Permintaan Klaim Hadiah", "body" => "ID #".$partner_reward_id,'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		
		$response = array(
			'status' => true,
			'message' => "Berhasil Klaim Hadiah",
		);
		$this->response($response,200);
	}
}
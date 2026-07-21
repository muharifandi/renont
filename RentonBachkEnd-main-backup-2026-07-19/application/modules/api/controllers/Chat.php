<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class Chat extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('Chat_m');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Basic",200);
	}
	
	public function list_chatroom_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$is_partner = $this->post('is_partner');
		
		$list_chat;
		if($is_partner == 1)
			$list_chat = $this->Chat_m->list_chat_partner($account_id);
		else
			$list_chat = $this->Chat_m->list_chat_customer($account_id);
		
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'list' => $list_chat,
		);
		$this->response($response,200);
	}
	
	public function list_chat_post()
	{
		$chatroom_id = $this->post('chatroom_id');
		$partner_account_id = $this->post('partner_account_id');
		$customer_account_id = $this->post('customer_account_id');
		
		if($chatroom_id == null){
				$chatroom_id = $this->Chat_m->create_chatroom_unavailable($partner_account_id,$customer_account_id);
		}
		
		$param = array(
			'limit' => $this->post('limit'),
			'page' => $this->post('page'),
			'chatroom_id' => $chatroom_id,
		);
		$chats = $this->Chat_m->list_chat($param);
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'chats' => $chats,
			'chatroom_id' => $chatroom_id,
		);
		$this->response($response,200);
	}
	
	public function send_message_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$chatroom_id = $this->post('chatroom_id');
		$target_account_id = $this->post('account_id');
		
		if($chatroom_id == null){
			if($this->post('user_type') == 4)
				$chatroom_id = $this->Chat_m->create_chatroom_unavailable($account_id,$target_account_id);
			else if($this->post('user_type') == 5)
				$chatroom_id = $this->Chat_m->create_chatroom_unavailable($target_account_id,$account_id);
		}
		
		
		$data = array(
			'chatroom_id' => $chatroom_id,
			'account_id' => $account_id,
			'user_type' => $this->post('user_type'),
			'message' => $this->post('message'),
			'attachment_type' => $this->post('attachment_type'),
			'attachment' => $this->post('attachment'),
		);
		
		$chat_id = $this->Chat_m->add_chat($data);
		$chat = $this->Chat_m->get_chat($chat_id);
		
		$chatroom = $this->Chat_m->get_chatroom($chatroom_id);
		
		$token = null;
		$title = "";
		$img;
		$to_partner;
		if($account_id == $chatroom->customer_account_id)
		{
			$this->load->model('Customer_m');
			
			$customer_detail = $this->Customer_m->detail($chatroom->customer_account_id);
			$title = $customer_detail->first_name." ".$customer_detail->last_name;
			
			$token = $this->Chat_m->get_account_token($chatroom->partner_account_id);
			$to_partner = '1';
			$img = base_url().'data/customers/profile/'.$customer_detail->img_profile;
		}else if($account_id == $chatroom->partner_account_id)
		{
			$this->load->model('Partner_m');
			
			$partner_detail = $this->Partner_m->detail($chatroom->partner_account_id);
			$title = $partner_detail->company_name;
			
			$token = $this->Chat_m->get_account_token($chatroom->customer_account_id);
			$to_partner = '0';
			$img = base_url().'data/partners/profile/'.$partner_detail->img_profile;
		}
		
		$receiver = array(
			$token
		);
		
		$data_payload = array(
			'data_type' => 'chat',
			'name'=> $title,
			'image'=> $img,
			'to_partner' => $to_partner,
			'chatroom_id' => $chat->chatroom_id,
			'user_type' => $chat->user_type,
			'account_id' => $chat->account_id,
			'attachment_type' => $chat->attachment_type,
			'attachment' => $chat->attachment,
			'message' => $chat->message,
			'date_added' => $chat->date_added,
		);
		
		$this->load->library('Fcm');
		$this->load->config('fcm');
        $this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		$this->fcm->setRecepients($receiver);
        $this->fcm->setData($data_payload);
		
		$notif = array("title" => $title, "text" => $chat->message,'android_channel_id' => 1, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $status = $this->fcm->send();
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'chat' => $chat,
		);
		$this->response($response,200);
	}
	
	public function read_message_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$chatroom_id = $this->post('chatroom_id');
		
		$chatroom = $this->Chat_m->get_chatroom($chatroom_id);
		
		if($account_id == $chatroom->customer_account_id)
		{
			$this->Chat_m->read_message($chatroom_id,$chatroom->partner_account_id);
		}else if($account_id == $chatroom->partner_account_id)
		{
			$this->Chat_m->read_message($chatroom_id,$chatroom->customer_account_id);
		}
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
		);
		$this->response($response,200);
	}
}
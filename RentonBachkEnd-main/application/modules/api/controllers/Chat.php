<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Customer <-> Partner chat resource.
 */
class Chat extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Chat_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Chat API — RentOn');
	}

	/** GET api/chat/chatrooms?is_partner=0|1 */
	public function chatrooms_get()
	{
		$account = $this->require_auth();
		$is_partner = $this->get('is_partner');

		$list = ($is_partner == 1)
			? $this->Chat_m->list_chat_partner($account->id)
			: $this->Chat_m->list_chat_customer($account->id);

		$this->ok(['list' => $list]);
	}

	/**
	 * GET api/chat/messages?chatroom_id=..&partner_account_id=..&customer_account_id=..&page=..&limit=..
	 * header: key (required) -- previously reachable without auth, fixed here.
	 */
	public function messages_get()
	{
		$this->require_auth();

		$chatroom_id = $this->get('chatroom_id');
		$partner_account_id = $this->get('partner_account_id');
		$customer_account_id = $this->get('customer_account_id');

		if (empty($chatroom_id)) {
			if (empty($partner_account_id) || empty($customer_account_id)) {
				return $this->validation_error(['chatroom_id' => 'atau partner_account_id + customer_account_id wajib diisi']);
			}
			$chatroom_id = $this->Chat_m->create_chatroom_unavailable($partner_account_id, $customer_account_id);
		}

		$param = [
			'limit' => min((int) ($this->get('limit') ?: 20), 100),
			'page' => (int) ($this->get('page') ?: 1),
			'chatroom_id' => $chatroom_id,
		];

		$this->ok(['chats' => $this->Chat_m->list_chat($param), 'chatroom_id' => (int) $chatroom_id]);
	}

	/**
	 * POST api/chat/messages
	 * body: { chatroom_id?, account_id (target), user_type, message, attachment_type?, attachment? }
	 */
	public function messages_post()
	{
		$account = $this->require_auth();

		$target_account_id = $this->post('account_id');
		$user_type = $this->post('user_type');
		$message = $this->post('message');
		$chatroom_id = $this->post('chatroom_id');

		if (empty($user_type) || empty($message)) {
			return $this->validation_error(['user_type' => 'wajib diisi', 'message' => 'wajib diisi']);
		}

		if (empty($chatroom_id)) {
			if ($user_type == 4) {
				$chatroom_id = $this->Chat_m->create_chatroom_unavailable($account->id, $target_account_id);
			} else if ($user_type == 5) {
				$chatroom_id = $this->Chat_m->create_chatroom_unavailable($target_account_id, $account->id);
			} else {
				return $this->validation_error(['chatroom_id' => 'tidak dapat dibuat otomatis, sertakan chatroom_id']);
			}
		}

		$chat_id = $this->Chat_m->add_chat([
			'chatroom_id' => $chatroom_id,
			'account_id' => $account->id,
			'user_type' => $user_type,
			'message' => $message,
			'attachment_type' => $this->post('attachment_type'),
			'attachment' => $this->post('attachment'),
		]);

		$chat = $this->Chat_m->get_chat($chat_id);
		$this->_notify_counterpart($account->id, $chatroom_id, $chat);

		$this->created(['chat' => $chat], 'Berhasil mengirim pesan');
	}

	private function _notify_counterpart($account_id, $chatroom_id, $chat)
	{
		$chatroom = $this->Chat_m->get_chatroom($chatroom_id);

		$token = null;
		$title = '';
		$img = null;
		$to_partner = null;

		if ($account_id == $chatroom->customer_account_id) {
			$this->load->model('Customer_m');
			$customer_detail = $this->Customer_m->detail($chatroom->customer_account_id);
			$title = $customer_detail->first_name.' '.$customer_detail->last_name;
			$token = $this->Chat_m->get_account_token($chatroom->partner_account_id);
			$to_partner = '1';
			$img = base_url().'data/customers/profile/'.$customer_detail->img_profile;
		} else if ($account_id == $chatroom->partner_account_id) {
			$this->load->model('Partner_m');
			$partner_detail = $this->Partner_m->detail($chatroom->partner_account_id);
			$title = $partner_detail->company_name;
			$token = $this->Chat_m->get_account_token($chatroom->customer_account_id);
			$to_partner = '0';
			$img = base_url().'data/partners/profile/'.$partner_detail->img_profile;
		}

		if (!$token) {
			return;
		}

		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));
		$this->fcm->setRecepients([$token]);
		$this->fcm->setData([
			'data_type' => 'chat', 'name' => $title, 'image' => $img, 'to_partner' => $to_partner,
			'chatroom_id' => $chat->chatroom_id, 'user_type' => $chat->user_type, 'account_id' => $chat->account_id,
			'attachment_type' => $chat->attachment_type, 'attachment' => $chat->attachment,
			'message' => $chat->message, 'date_added' => $chat->date_added,
		]);
		$this->fcm->setNotification(['title' => $title, 'text' => $chat->message, 'android_channel_id' => 1, 'sound' => 'default']);
		$this->fcm->send();
	}

	/** PUT api/chat/messages_read body: {chatroom_id} */
	public function messages_read_put()
	{
		$account = $this->require_auth();
		$chatroom_id = $this->put('chatroom_id');

		if (empty($chatroom_id)) {
			return $this->validation_error(['chatroom_id' => 'wajib diisi']);
		}

		$chatroom = $this->Chat_m->get_chatroom($chatroom_id);
		if (!$chatroom) {
			return $this->not_found('Chatroom tidak ditemukan');
		}

		if ($account->id == $chatroom->customer_account_id) {
			$this->Chat_m->read_message($chatroom_id, $chatroom->partner_account_id);
		} else if ($account->id == $chatroom->partner_account_id) {
			$this->Chat_m->read_message($chatroom_id, $chatroom->customer_account_id);
		} else {
			return $this->forbidden();
		}

		$this->ok(null, 'Berhasil menandai pesan terbaca');
	}
}

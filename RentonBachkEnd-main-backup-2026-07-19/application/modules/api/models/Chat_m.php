<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_m extends MY_Model {

	function create_chatroom_unavailable($partner_account_id,$customer_account_id)
	{
		$this->db->where('partner_account_id',$partner_account_id);
		$this->db->where('customer_account_id',$customer_account_id);
		$result = $this->db->get('chatroom')->row();
		
		if($result == null){
			$data = array(
				'partner_account_id' => $partner_account_id,
				'customer_account_id' => $customer_account_id,
			);
			$this->db->insert('chatroom',$data);
			return $this->db->insert_id();
		}
		else
			return $result->id;
	}
	
	function list_chat($param)
	{
		$this->db->order_by('date_added','DESC');
		$this->db->where('chatroom_id',$param['chatroom_id']);
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		
		return $this->db->get('chat_message')->result();
	}
	
	function add_chat($data)
	{
		$this->db->insert('chat_message',$data);
		
		return $this->db->insert_id();
	}
	
	function get_chat($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('chat_message')->row();
	}
	
	function get_chatroom($chatroom_id)
	{
		$this->db->where('id',$chatroom_id);
		return $this->db->get('chatroom')->row();
	}
	
	function get_account_token($account_id)
	{
		$this->db->where('id',$account_id);
		return $this->db->get('accounts')->row()->token;
	}
	
	function list_chat_partner($account_id)
	{
		$this->db->group_by('chatroom.id');
		$this->db->order_by('last_chat.date_added','DESC');
		$this->db->where('chatroom.partner_account_id',$account_id);
		$this->db->where('chat.size != 0');
		$this->db->join('(
			SELECT chatroom_id,count(id) as size from chat_message GROUP BY chatroom_id
			) chat','chat.chatroom_id = chatroom.id','left');
		$this->db->join('chat_message last_chat','last_chat.id = (SELECT id FROM chat_message WHERE chat_message.chatroom_id = chatroom.id ORDER BY date_added DESC LIMIT 1)','left');
			
		$this->db->join('chat_message unread_chat','unread_chat.chatroom_id = chatroom.id AND unread_chat.account_id = chatroom.customer_account_id','left');
		
		$this->db->join('accounts','accounts.id = chatroom.customer_account_id','left');
		$this->db->join('customers','customers.account_id = chatroom.customer_account_id','left');
		$this->db->select('chatroom.id, CONCAT(accounts.first_name," ",accounts.last_name) as name, customers.img_profile, last_chat.message, last_chat.date_added ,SUM(unread_chat.unread) as unread');
		return $this->db->get('chatroom')->result();
	}
	
	function list_chat_customer($account_id)
	{
		$this->db->group_by('chatroom.id');
		$this->db->order_by('last_chat.date_added','DESC');
		$this->db->where('chatroom.customer_account_id',$account_id);
		$this->db->where('chat.size != 0');
		$this->db->join('(
			SELECT chatroom_id,count(id) as size from chat_message GROUP BY chatroom_id
			) chat','chat.chatroom_id = chatroom.id','left');
		$this->db->join('chat_message last_chat','last_chat.id = (SELECT id FROM chat_message WHERE chat_message.chatroom_id = chatroom.id ORDER BY date_added DESC LIMIT 1)','left');
			
		$this->db->join('chat_message unread_chat','unread_chat.chatroom_id = chatroom.id AND unread_chat.account_id = chatroom.partner_account_id','left');
		
		$this->db->join('partners','partners.account_id = chatroom.partner_account_id','left');
		
		$this->db->select('chatroom.id, partners.company_name as name, partners.img_profile, last_chat.message, last_chat.date_added 	,SUM(unread_chat.unread) as unread');
		return $this->db->get('chatroom')->result();
	}
	
	function read_message($chatroom_id,$account_id)
	{
		$this->db->where('chatroom_id',$chatroom_id);
		$this->db->where('account_id',$account_id);
		
		$this->db->set('unread',0);
		$this->db->update('chat_message');
	}
	
	function partner_chatroom_unread($account_id)
	{
		$query = $this->db->query(
			'SELECT COUNT(c.id) as total_unread FROM (SELECT chatroom.id
			FROM chatroom 
			LEFT JOIN 
			(
				SELECT chatroom_id,count(id) as size from chat_message
				GROUP BY chatroom_id
			) chat 
			ON chat.chatroom_id = chatroom.id
			LEFT JOIN chat_message unread_chat
			ON unread_chat.chatroom_id = chatroom.id AND unread_chat.account_id = chatroom.customer_account_id
			WHERE
			partner_account_id = '.$account_id.'
			AND
			chat.size != 0
			GROUP BY chatroom.id
			HAVING SUM(unread_chat.unread) > 0 ) c');
		return $query->row();
	}
	
	function customer_chatroom_unread($account_id)
	{
		$query = $this->db->query(
			'SELECT COUNT(c.id) as total_unread FROM (SELECT chatroom.id
			FROM chatroom 
			LEFT JOIN 
			(
				SELECT chatroom_id,count(id) as size from chat_message
				GROUP BY chatroom_id
			) chat 
			ON chat.chatroom_id = chatroom.id
			LEFT JOIN chat_message unread_chat
			ON unread_chat.chatroom_id = chatroom.id AND unread_chat.account_id = chatroom.partner_account_id
			WHERE
			customer_account_id = '.$account_id.'
			AND
			chat.size != 0
			GROUP BY chatroom.id
			HAVING SUM(unread_chat.unread) > 0 ) c');
		return $query->row();
	}
	
}
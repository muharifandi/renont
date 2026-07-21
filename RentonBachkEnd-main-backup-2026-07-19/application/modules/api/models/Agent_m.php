<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agent_m extends MY_Model {

	function get_list_commision()
	{
		$this->db->order_by('min_target','asc');
		
		$this->db->select("agents_commision.*");
		return $this->db->get('agents_commision')->result();
	}
	
	function add_history_transaction($data)
	{
		$this->db->insert('history_agent_transaction',$data);
	}
	
	function increase_balance($id,$value)
	{
		$this->db->where('account_id',$id);
		$this->db->set('balance','balance + '.$value,false);
		$this->db->update('agents_balance');
	}
}
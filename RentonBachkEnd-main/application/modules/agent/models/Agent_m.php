<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agent_m extends MY_Model {
	
	function get_agent_balance($agent_id)
	{
		$this->db->where('agents.account_id',$agent_id);
		$this->db->join('agents_balance','agents_balance.account_id = agents.account_id','left');
		return  $this->db->get('agents')->row();
	}
	
	function get_list_withdraw($agent_id,$param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->where('agent_withdraw.account_id',$agent_id);
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like('agent_withdraw_status.name',$param['search'],'both');
		$this->db->group_end();
		$this->db->order_by('agent_withdraw.date_added','DESC');
		$this->db->join('accounts','accounts.id = agent_withdraw.account_id','left');
		$this->db->join('agents_bank','agents_bank.id = agent_withdraw.account_bank_id','left');
		$this->db->join('bank','bank.id = agents_bank.bank_id','left');
		$this->db->join('agent_withdraw_status','agent_withdraw_status.id = agent_withdraw.status','left');
		$this->db->select("agent_withdraw.id, CONCAT('Rp. ', FORMAT(agent_withdraw.value, 0),',-') as value, agent_withdraw.status, agent_withdraw.date_added, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, bank.name as bank_name, bank.code as bank_code, bank.icon, agents_bank.bank_number, agents_bank.name, agent_withdraw_status.name as status_name");
		return $this->db->get('agent_withdraw')->result();
	}
	
	function get_total_list_withdraw_filtered($agent_id,$param)
	{
		$this->db->where('agent_withdraw.account_id',$agent_id);
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like('agent_withdraw_status.name',$param['search'],'both');
		$this->db->group_end();
		$this->db->join('accounts','accounts.id = agent_withdraw.account_id','left');
		$this->db->join('agent_withdraw_status','agent_withdraw_status.id = agent_withdraw.status','left');
		$this->db->select("count(agent_withdraw.id) as total");
		return $this->db->get('agent_withdraw')->result()[0]->total;
	}
	
	function get_total_list_withdraw_unfiltered($agent_id,$param)
	{
		$this->db->where('agent_withdraw.account_id',$agent_id);
		$this->db->select("count(agent_withdraw.id) as total");
		return $this->db->get('agent_withdraw')->result()[0]->total;
	}
	
	function withdraw_detail($agent_id,$id)
	{
		$this->db->where('agent_withdraw.account_id',$agent_id);
		$this->db->where('id',$id);
		return $this->db->get('agent_withdraw')->row();
	}
	
	function request_withdraw($param)
	{
		$this->db->insert('agent_withdraw',$param);
		return $this->db->insert_id();
	}
	
	
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_m extends MY_Model {
	
	function get_total_partner_transaction($agent_id, $start,$end)
	{
		$this->db->where('FROM_UNIXTIME(created_on) >= "'.$start.'"');
		$this->db->where('FROM_UNIXTIME(created_on) <= "'.$end.'"');
		$this->db->where('partners.agent_id',$agent_id);
		$this->db->where('partners.status',0);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select('count(*) as total');
		return $this->db->get('accounts')->row()->total;
	}
	
	function get_total_commission_transaction($agent_id, $start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->where('history_agent_transaction.account_id',$agent_id);
		$this->db->select('count(*) as total');
		return $this->db->get('history_agent_transaction')->row()->total;
	}
	
	function get_total_partner_register($agent_id, $start,$end)
	{
		$this->db->where('FROM_UNIXTIME(created_on) >= "'.$start.'"');
		$this->db->where('FROM_UNIXTIME(created_on) <= "'.$end.'"');
		$this->db->where('partners.agent_id',$agent_id);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select('count(*) as total');
		return $this->db->get('accounts')->row()->total;
	}
	
	function get_total_partner($agent_id)
	{
		$this->db->where('partners.agent_id',$agent_id);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select('count(*) as total');
		return $this->db->get('accounts')->row()->total;
	}
	
	function get_total_revenue($agent_id, $start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->where('history_agent_transaction.account_id',$agent_id);
		$this->db->select('IFNULL(sum(value),0) as total');
		return $this->db->get('history_agent_transaction')->row()->total;
	}
	
}
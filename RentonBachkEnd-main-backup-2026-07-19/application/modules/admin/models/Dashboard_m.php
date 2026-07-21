<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_m extends MY_Model {
	
	function get_total_register($start,$end)
	{
		$this->db->where('FROM_UNIXTIME(created_on) >= "'.$start.'"');
		$this->db->where('FROM_UNIXTIME(created_on) <= "'.$end.'"');
		$this->db->select('count(*) as total');
		return $this->db->get('accounts')->row()->total;
	}
	
	function get_total_partner($start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->select('count(*) as total');
		return $this->db->get('partners')->row()->total;
	}
	
	function get_total_transaction_rent_vehicle($start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->select('count(*) as total');
		return $this->db->get('transaction_rent_vehicle')->row()->total;
	}
	
	function get_total_claim_reward($start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->select('count(*) as total');
		return $this->db->get('partner_rewards')->row()->total;
	}
	
	function get_total_admin_fee_transaction_rent_vehicle($start,$end)
	{
		$this->db->where('DATE(date_modified) >= "'.$start.'"');
		$this->db->where('DATE(date_modified) <= "'.$end.'"');
		$this->db->where('status',8);
		$this->db->select('sum(admin_fee) as total');
		return $this->db->get('transaction_rent_vehicle')->row()->total;
	}
	
	function get_total_income_promote_transaction_rent_vehicle($start,$end)
	{
		$this->db->where('DATE(date_modified) >= "'.$start.'"');
		$this->db->where('DATE(date_modified) <= "'.$end.'"');
		$this->db->group_start();
		$this->db->where('status',2);
		$this->db->or_where('status',3);
		$this->db->group_end();
		$this->db->select('(sum(total_payment) - sum(canceled_total_return)) as total');
		return $this->db->get('promote_rent_vehicle')->row()->total;
	}
	
	function get_total_agent_commission($start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->select('sum(value) as total');
		return $this->db->get('history_agent_transaction')->row()->total;
	}
	
	function get_total_topup($start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->where('status',2);
		$this->db->select('count(*) as total');
		return $this->db->get('customer_topup')->row()->total;
	}
	
	function get_total_withdraw($start,$end)
	{
		$this->db->where('DATE(date_added) >= "'.$start.'"');
		$this->db->where('DATE(date_added) <= "'.$end.'"');
		$this->db->where('status',1);
		$this->db->select('count(*) as total');
		return $this->db->get('customer_withdraw')->row()->total;
	}
}
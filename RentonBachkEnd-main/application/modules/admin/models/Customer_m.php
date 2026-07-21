<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_m extends MY_Model {
	
	function get_list($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("active_status.name",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('accounts_groups.group_id','5');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('customers','customers.account_id = accounts.id','left');
		$this->db->join('accounts_balance','accounts_balance.account_id = accounts.id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, accounts.email,accounts.phone, DATE_FORMAT(FROM_UNIXTIME(accounts.created_on), '%d %M %Y') as member_since,customers.img_profile,accounts_balance.balance,accounts_balance.point, accounts.active as status, active_status.name as status_name");
		return $this->db->get('accounts')->result();
	}
	
	function get_total_list_filtered($param)
	{
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("active_status.name",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('accounts_groups.group_id','5');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($param)
	{
		$this->db->where('accounts_groups.group_id','5');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function detail($id)
	{
		$this->db->where('accounts.id',$id);
		$this->db->join('customers_file','customers_file.account_id = accounts.id','left');
		$this->db->join('customers','customers.account_id = accounts.id','left');
		
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname,accounts.phone,customers.identity_number, accounts.email, DATE_FORMAT(FROM_UNIXTIME(accounts.created_on), '%d %M %Y') as member_since,customers.img_profile, customers_file.img_identity, customers.referal_id");
		return $this->db->get('accounts')->row();
	}
	
	function get_active_status($id = null)
	{
		if($id == null)
			return $this->db->get('active_status')->result();
		else
		{
			$this->db->where('id',$id);
			return $this->db->get('active_status')->row();
		}
	}
	
	
	function update_active_status($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('active',$status);
		$this->db->update('accounts');
	}
	
	function get_all_token()
	{
		$this->db->where('accounts.active',1);
		$this->db->select("accounts.token");
		$result = $this->db->get('accounts')->result();
		
		$tokens = array();
		foreach($result as $val)
		{
			$tokens[] = $val->token;
		}
		return $tokens;
	}
	
	function get_token($account_id)
	{
		$this->db->where('id',$account_id);
		
		return $this->db->get('accounts')->row()->token;
	}
	
	function get_list_topup($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like('customer_topup_status.name',$param['search'],'both');
		$this->db->order_by('customer_topup.date_added','DESC');
		$this->db->join('accounts','accounts.id = customer_topup.account_id','left');
		$this->db->join('company_bank','company_bank.id = customer_topup.company_bank_id','left');
		$this->db->join('bank','bank.id = company_bank.bank_id','left');
		$this->db->join('customer_topup_status','customer_topup_status.id = customer_topup.status','left');
		$this->db->select("customer_topup.id, CONCAT('Rp. ', FORMAT(customer_topup.value, 0),',-') as value, CONCAT('Rp. ', FORMAT(customer_topup.value_with_code, 0),',-') as value_with_code,customer_topup.img_proof, customer_topup.status,customer_topup.date_added, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, bank.name as bank_name, bank.code as bank_code, bank.icon, company_bank.bank_number, company_bank.name, customer_topup_status.name as status_name");
		return $this->db->get('customer_topup')->result();
	}
	
	function get_total_list_topup_filtered($param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->join('accounts','accounts.id = customer_topup.account_id','left');
		$this->db->select("count(customer_topup.id) as total");
		return $this->db->get('customer_topup')->result()[0]->total;
	}
	
	function get_total_list_topup_unfiltered($param)
	{
		$this->db->select("count(customer_topup.id) as total");
		return $this->db->get('customer_topup')->result()[0]->total;
	}
	
	function topup_detail($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('customer_topup')->row();
	}
	
	function get_topup_status($id = null)
	{
		if($id == null)
			return $this->db->get('customer_topup_status')->result();
		else
		{
			$this->db->where('id',$id);
			return $this->db->get('customer_topup_status')->row();
		}
	}
	
	function update_topup_status($id,$status)
	{
		$this->db->where('id',$id);
		$topup = $this->db->get('customer_topup')->row();
		
		if($status == 3 && $topup->processed == 0)
		{
			$this->db->where('account_id',$topup->account_id);
			$this->db->set('balance','balance + '.$topup->value_with_code,false);
			$this->db->update('accounts_balance');
			
			$this->db->set('processed',1);
		}
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('customer_topup');
	}
	
	function get_list_withdraw($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like('customer_withdraw_status.name',$param['search'],'both');
		$this->db->order_by('customer_withdraw.date_added','DESC');
		$this->db->join('accounts','accounts.id = customer_withdraw.account_id','left');
		$this->db->join('accounts_bank','accounts_bank.id = customer_withdraw.account_bank_id','left');
		$this->db->join('bank','bank.id = accounts_bank.bank_id','left');
		$this->db->join('customer_withdraw_status','customer_withdraw_status.id = customer_withdraw.status','left');
		$this->db->select("customer_withdraw.id, CONCAT('Rp. ', FORMAT(customer_withdraw.value, 0),',-') as value, customer_withdraw.status, customer_withdraw.date_added, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, bank.name as bank_name, bank.code as bank_code, bank.icon, accounts_bank.bank_number, accounts_bank.name, customer_withdraw_status.name as status_name");
		return $this->db->get('customer_withdraw')->result();
	}
	
	function get_total_list_withdraw_filtered($param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->join('accounts','accounts.id = customer_withdraw.account_id','left');
		$this->db->select("count(customer_withdraw.id) as total");
		return $this->db->get('customer_withdraw')->result()[0]->total;
	}
	
	function get_total_list_withdraw_unfiltered($param)
	{
		$this->db->select("count(customer_withdraw.id) as total");
		return $this->db->get('customer_withdraw')->result()[0]->total;
	}
	
	function withdraw_detail($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('customer_withdraw')->row();
	}
	
	function get_withdraw_status($id = null)
	{
		if($id == null)
			return $this->db->get('customer_withdraw_status')->result();
		else
		{
			$this->db->where('id',$id);
			return $this->db->get('customer_withdraw_status')->row();
		}
	}
	
	
	function update_withdraw_status($id,$status,$description)
	{
		$this->db->where('id',$id);
		$withdraw = $this->db->get('customer_withdraw')->row();
		$this->db->where('account_id',$withdraw->account_id);
		$balance = $this->db->get('accounts_balance')->row();
		if($status == 2 && $withdraw->processed == 0)
		{
			if($balance->balance >= $withdraw->value)
			{
				$this->db->where('account_id',$withdraw->account_id);
				$this->db->set('balance','balance - '.$withdraw->value,false);
				$this->db->update('accounts_balance');
				
				$this->db->set('processed',1);
				$this->db->set('status',$status);
			}else
			{
				$this->db->set('processed',1);
				$this->db->set('status',3);
			}
		}
		$this->db->where('id',$id);
		$this->db->set('description',$description);
		
		$this->db->update('customer_withdraw');
	}
	
	function withdraw_unprocessed_count()
	{
		$this->db->where('status',1);
		$this->db->select('count(id) count');
		return $this->db->get('customer_withdraw')->row()->count;
	}
	
	function topup_unprocessed_count()
	{
		$this->db->where('status',2);
		$this->db->select('count(id) count');
		return $this->db->get('customer_topup')->row()->count;
	}
	
	function get_list_account($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("active_status.name",$param['search'],'both');
		$this->db->or_like("partners.company_name",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('accounts_groups.group_id','5');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('customers','customers.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->join('accounts_balance','accounts_balance.account_id = accounts.id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, accounts.email,accounts.phone, DATE_FORMAT(FROM_UNIXTIME(accounts.created_on), '%Y-%M-%d') as member_since, partners.account_id as partner_account_id, IFNULL(partners.img_profile,customers.img_profile) as img_profile, partners.company_name, accounts_balance.balance,accounts_balance.point, accounts.active as status, active_status.name as status_name");
		return $this->db->get('accounts')->result();
	}
	
	function get_total_list_account_filtered($param)
	{
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("active_status.name",$param['search'],'both');
		$this->db->or_like("partners.company_name",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('accounts_groups.group_id','5');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_total_list_account_unfiltered($param)
	{
		$this->db->where('accounts_groups.group_id','5');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Basic_m extends MY_Model {

	function check_email($email)
	{
		$this->db->where('email',$email);
		return $this->db->get('accounts')->row();
	}
	
	function check_agent($id)
	{
		$this->db->where('account_id',$id);
		$this->db->join('accounts','accounts.id = agents.account_id','left');
		$this->db->select('accounts.*');
		return $this->db->get('agents')->row();
	}
	
	
	function check_phone($phone)
	{
		$this->db->where('phone',$phone);
		
		return $this->db->get('accounts')->row();
	}
	
	function get_regencies($regency)
	{
		$this->db->like('name',$regency,'both');
		
		return $this->db->get('regencies')->result();
	}
	
	function get_regency($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('regencies')->row();
	}
	
	function get_active_regencies()
	{
		$this->db->group_by('regencies.name');
		$this->db->where('rent_vehicles_item.status',1);
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id');
		$this->db->join('regencies','regencies.id = partners.regencies_id');
		$this->db->select('regencies.id,regencies.name');
		return $this->db->get('rent_vehicles_item')->result();
	}
	
	function get_account_id_by_referal_code($referal_code)
	{
		$this->db->where("CONCAT(UPPER(SUBSTRING(REPLACE(accounts.first_name,' ',''),1,5)),accounts.id) ='".strtoupper($referal_code)."'");
		
		$result = $this->db->get('accounts')->row();
		if($result)
			return $result->id;
		else
			return null;
	}
	
	function insert_point_reward($data)
	{
		$count = 0;
		if($data['target_id'] != null || $data['transaction_id'] != null)
		{
			$this->db->where('account_id',$data['account_id']);
			
			if($data['target_id'] != null)
				$this->db->where('target_id',$data['target_id']);
			
			if($data['transaction_id'] != null)
				$this->db->where('transaction_id',$data['transaction_id']);
			
			$count = $this->db->get('transaction_point')->num_rows();
		}
		
		if($count > 0)
		{
			return false;
		}else
		{
			$point_reward = $data['point_debit'];
			
			if($point_reward)
			{
				$this->db->insert('transaction_point',$data);
				
				$this->db->where('account_id',$data['account_id']);
				$this->db->set('point',"point+$point_reward",FALSE);
				$this->db->update('accounts_balance');
				
				return true;
			}else
				return false;
		}
	}
	
	function get_config_value($name)
	{
		$this->db->where('name',$name);
		$result = $this->db->get('config')->row();
		
		if($result)
			return $result->value;
		else
			return null;
	}
	
	function get_banks()
	{
		return $this->db->get('bank')->result();
	}
	
	function get_company_banks()
	{
		$this->db->join('bank','bank.id = company_bank.bank_id');
		$this->db->select('company_bank.*,bank.name as bank_name, bank.code, bank.icon');
		return $this->db->get('company_bank')->result();
	}
	
	function get_config($key)
	{
		$this->db->where('name',$key);
		return $this->db->get('config')->row();
	}
	
	function get_voucher_by_code($param)
	{
		$this->db->where('status',1); //1
		$this->db->where('voucher_type',$param['voucher_type']); //2
		$this->db->where('user_type',$param['user_type']);//5
		$this->db->where('code',$param['code']);
		
		if($param['feature_id'] != null)
			$this->db->where('feature_id',$param['feature_id']);
		return $this->db->get('vouchers')->row();
	}
	
	function get_voucher($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('vouchers')->row();
	}
	
	function is_voucher_used($account_id,$voucher_id)
	{
		$this->db->where('account_id',$account_id);
		$this->db->where('voucher_id',$voucher_id);
		
		if($this->db->get('transaction_rent_vehicle')->num_rows() > 0)
			return true;
		
		return false;
	}
	
	function decrease_voucher_quota($voucher_id)
	{
		$this->db->where('id',$voucher_id);
		$voucher = $this->db->get('vouchers')->row();
		
		if($voucher->quota > 0)
		{
			$this->db->where('id',$voucher_id);
			$this->db->set('quota','quota - 1',false);
			$this->db->update('vouchers');
			
			return true;
		}
		
		return false;
		
	}
	
	function get_all_admin_token()
	{
		$this->db->where('accounts_groups.group_id',1);
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$result = $this->db->get('accounts')->result();
		
		$data = array();
		foreach($result as $val)
		{
			$data[] = $val->token;
		}
		
		return $data;
	}
	
}
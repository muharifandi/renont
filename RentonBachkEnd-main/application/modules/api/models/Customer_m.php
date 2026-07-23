<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_m extends MY_Model {
	
	function register($data)
	{
		$this->db->insert('customers',$data);
		
		$this->db->set('account_id',$data['account_id']);
		$this->db->insert('accounts_balance');
		$this->db->set('account_id',$data['account_id']);
		$this->db->insert('customers_location');
		$this->db->set('account_id',$data['account_id']);
	}
	
	function insert_customer_file($data)
	{
		$this->db->insert('customers_file',$data);
	}
	
	function check_account_valid($id)
	{
		$this->db->where('group_id',5);
		$this->db->where('account_id',$id);
		$row = $this->db->get('accounts_groups')->row();
		if($row)
			return true;
		else
			return false;
	}
	
	function check_account_active($id)
	{
		$this->db->where('id',$id);
		$row = $this->db->get('accounts')->row();
		if($row->active == 1)
			return true;
		else
			return false;
	}
	
	function detail($id)
	{
		$status = $this->check_account_valid($id);
		
		if($status)
		{
			$this->db->where('accounts.id',$id);
			
			$this->db->select('accounts.id, accounts.email, accounts.first_name,accounts.last_name, DATE_FORMAT(FROM_UNIXTIME(accounts.created_on), "%d %M %Y") as member_since,customers.img_profile, customers.referal_id');
			$this->db->join('customers','customers.account_id = accounts.id','left');
			return $this->db->get('accounts')->row();
		}else
			return null;
	}
	
	function change_name($account_id,$data)
	{
		$this->db->where('id',$account_id);
		$this->db->update('accounts',$data);
	}
	
	function update_profile_image($account_id, $img_filename)
	{
		$data = array(
			'img_profile' => $img_filename,
		);
		$this->db->where('account_id',$account_id);
		$this->db->update('customers',$data);
	}
	
	function banks($id)
	{
		$this->db->where('account_id',$id);
		$this->db->join('bank','bank.id = accounts_bank.bank_id','left');
		$this->db->select('accounts_bank.*,bank.name as bank_name,bank.code,bank.icon');
		return $this->db->get('accounts_bank')->result();
	}
	
	function bank_detail($id,$account_id = null)
	{
		$this->db->where('accounts_bank.id',$id);
		if ($account_id !== null) {
			$this->db->where('accounts_bank.account_id',$account_id);
		}
		$this->db->join('bank','bank.id = accounts_bank.bank_id','left');
		$this->db->select('accounts_bank.*,bank.name as bank_name,bank.code,bank.icon');
		return $this->db->get('accounts_bank')->row();
	}
	
	function add_bank($account_id,$data)
	{
		$this->db->set('account_id',$account_id);
		$status = $this->db->insert('accounts_bank',$data);
		if($status)
			return $this->db->insert_id();
		else
			return false;
	}
	
	function update_bank($item_id,$data)
	{
		$this->db->where('id',$item_id);
		$status = $this->db->update('accounts_bank',$data);
		return $item_id;
	}
	
	function delete_bank($id,$account_id = null)
	{
		$this->db->where('id',$id);
		if ($account_id !== null) {
			$this->db->where('account_id',$account_id);
		}
		$this->db->delete('accounts_bank');
	}
	
	function bank_total($id)
	{
		$this->db->where('account_id',$id);
		$this->db->select('COUNT(account_id) as bank_total');
		return $this->db->get('accounts_bank')->row()->bank_total;
	}
	
	
	function balance($id)
	{
		$this->db->where('account_id',$id);
		$this->db->select('balance, point');
		return $this->db->get('accounts_balance')->row();
	}
	
	function decrease_balance($id,$value)
	{
		$this->db->where('account_id',$id);
		$this->db->set('balance','balance - '.$value,false);
		$this->db->update('accounts_balance');
	}
	
	function increase_balance($id,$value)
	{
		$this->db->where('account_id',$id);
		$this->db->set('balance','balance + '.$value,false);
		$this->db->update('accounts_balance');
	}
	
	function referal_code($id)
	{
		$this->db->where('accounts.id',$id);
		$this->db->select("accounts.id,CONCAT(UPPER(SUBSTRING(REPLACE(accounts.first_name,' ',''),1,5)),accounts.id) as referal_code");
		
		return $this->db->get('accounts')->row()->referal_code;
	}
	
	function get_key($id)
	{
		$this->db->where('account_id',$id);
		return $this->db->get('keys')->row()->key;
	}
	
	function get_status($id)
	{
		$this->db->where('id',$id);
		$result = $this->db->get('accounts')->row();
		
		if($result)
			return $result->active;
		else
			return -1;
	}
	function get_unique_value_topup($value)
	{
		$value_with_code = 0;
		$found_unique = false;
		do{
			$code = rand(100,300);
			$this->db->where('value_with_code',$value+$code);
			$this->db->where('DATE(`date_added`) = CURDATE()');
			$row = $this->db->get('customer_topup')->row();
			
			if($row == null){
				$found_unique = true;
				$value_with_code = $value+$code;
			}
		}while($found_unique == false);
		
		return $value_with_code;
	}
	
	function add_request_topup($data)
	{
		$this->db->insert('customer_topup',$data);
		
		return $this->db->insert_id();
	}

	function update_topup($id,$data,$account_id = null)
	{
		$this->db->where('id',$id);
		if ($account_id !== null) {
			$this->db->where('account_id',$account_id);
		}
		$this->db->update('customer_topup',$data);
	}

	function topup_detail($id,$account_id = null)
	{
		$this->db->where('customer_topup.id',$id);
		if ($account_id !== null) {
			$this->db->where('customer_topup.account_id',$account_id);
		}
		$this->db->join('company_bank','company_bank.id = customer_topup.company_bank_id','left');
		$this->db->join('bank','bank.id = company_bank.bank_id','left');
		$this->db->join('customer_topup_status','customer_topup_status.id = customer_topup.status','left');
		$this->db->select('customer_topup.*, bank.name as bank_name, bank.code as bank_code, bank.icon, company_bank.bank_number, company_bank.name,customer_topup_status.name as status_name');
		return $this->db->get('customer_topup')->row();
	}
	
	function list_topup($account_id,$param)
	{
		$this->db->where('customer_topup.account_id',$account_id);
		$this->db->order_by('date_added','DESC');
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->join('company_bank','company_bank.id = customer_topup.company_bank_id','left');
		$this->db->join('bank','bank.id = company_bank.bank_id','left');
		$this->db->join('customer_topup_status','customer_topup_status.id = customer_topup.status','left');
		$this->db->select('customer_topup.*, bank.name as bank_name, bank.code as bank_code, bank.icon, company_bank.bank_number, company_bank.name,customer_topup_status.name as status_name');
		return $this->db->get('customer_topup')->result();
	}
	
	function list_withdraw($account_id,$param)
	{
		$this->db->where('customer_withdraw.account_id',$account_id);
		$this->db->order_by('date_added','DESC');
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->join('accounts_bank','accounts_bank.id = customer_withdraw.account_bank_id','left');
		$this->db->join('bank','bank.id = accounts_bank.bank_id','left');
		$this->db->join('customer_withdraw_status','customer_withdraw_status.id = customer_withdraw.status','left');
		$this->db->select('customer_withdraw.*, bank.name as bank_name, bank.code as bank_code, bank.icon, accounts_bank.bank_number, accounts_bank.name,customer_withdraw_status.name as status_name');
		return $this->db->get('customer_withdraw')->result();
	}
	
	function add_request_withdraw($data)
	{
		$this->db->where('account_id',$data['account_id']);
		$this->db->set('balance','balance - '.$data['value'],false);
		$this->db->update('accounts_balance');
		$this->db->insert('customer_withdraw',$data);
		
		return $this->db->insert_id();
	}
	
	function exchange_point_to_balance($data,$rate)
	{
		$this->db->where('account_id',$data['account_id']);
		$this->db->set('balance','balance + '.($data['point_credit'] * $rate),false);
		$this->db->set('point','point - '.$data['point_credit'],false);
		$this->db->update('accounts_balance');
		$this->db->insert('transaction_point',$data);
		
		return $this->db->insert_id();
	}
	
	function list_transaction_point($account_id,$param)
	{
		$this->db->where('account_id',$account_id);
		$this->db->order_by('date_added','DESC');
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		return $this->db->get('transaction_point')->result();
	}
	
	function update_customer_location($account_id,$data)
	{
		$this->db->where('account_id',$account_id);
		$this->db->update('customers_location',$data);
	}
	
	function customer_info($id)
	{
		$this->db->where('accounts.id',$id);
			
		$this->db->select('accounts.id,accounts.first_name,accounts.last_name, accounts.phone,customers.identity_number ,customers.img_profile, customers_file.img_identity');
		$this->db->join('customers_file','customers_file.account_id = accounts.id','left');
		$this->db->join('customers','customers.account_id = accounts.id','left');
		return $this->db->get('accounts')->row();
	}
	
	function update_token($account_id,$data)
	{
		$this->db->where('id',$account_id);
		$this->db->update('accounts',$data);
	}
	
	function get_token($account_id)
	{
		$this->db->where('id',$account_id);
		
		return $this->db->get('accounts')->row()->token;
	}
}
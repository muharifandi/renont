<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Config_m extends MY_Model {
	
	function get_all_bank()
	{
		return $this->db->get('bank')->result();
	}

	/**
	 * Accounts belonging to any of $staff_group_ids, with a comma-separated `groups`
	 * column of their (staff-only) group names -- single JOIN query, replaces the old
	 * N+1 loop (1 query per account) that used to live in Config::admins_get().
	 */
	function get_admins($staff_group_ids)
	{
		$this->db->select('accounts.*, accounts.id as user_id, GROUP_CONCAT(groups.name) as groups', false);
		$this->db->join('accounts_groups', 'accounts_groups.account_id = accounts.id');
		$this->db->join('groups', 'groups.id = accounts_groups.group_id');
		$this->db->where_in('accounts_groups.group_id', $staff_group_ids);
		$this->db->group_by('accounts.id');
		return $this->db->get('accounts')->result();
	}
	
	function get_list_bank($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("name",$param['search'],'both');
		return $this->db->get('bank')->result();
	}
	
	function get_total_list_bank_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(bank.id) as total");
		return $this->db->get('bank')->result()[0]->total;
	}
	
	function get_total_list_bank_unfiltered($param)
	{
		$this->db->select("count(bank.id) as total");
		return $this->db->get('bank')->result()[0]->total;
	}
	
	function add_bank($param)
	{
		$this->db->insert('bank',$param);
	}
	
	function edit_bank($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('bank',$param);
	}
	
	function get_bank($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('bank')->row();
	}
	
	function delete_bank($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('bank');
	}
	
	function get_list_feature($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("name",$param['search'],'both');
		return $this->db->get('feature')->result();
	}
	
	function get_total_list_feature_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(feature.id) as total");
		return $this->db->get('feature')->result()[0]->total;
	}
	
	function get_total_list_feature_unfiltered($param)
	{
		$this->db->select("count(feature.id) as total");
		return $this->db->get('feature')->result()[0]->total;
	}
	
	function add_feature($param)
	{
		$this->db->insert('feature',$param);
	}
	
	function edit_feature($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('feature',$param);
	}
	
	function get_feature($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('feature')->row();
	}
	
	function delete_feature($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('feature');
	}
	
	function get_list_bank_company($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("company_bank.name",$param['search'],'both');
		$this->db->or_like("company_bank.bank_number",$param['search'],'both');
		$this->db->or_like("bank.name",$param['search'],'both');
		
		$this->db->join('bank','bank.id = company_bank.bank_id','left');
		$this->db->select('company_bank.id, bank.name as bank_name,bank.code, bank.icon, company_bank.bank_number, company_bank.name');
		return $this->db->get('company_bank')->result();
	}
	
	function get_total_list_bank_company_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(company_bank.id) as total");
		return $this->db->get('company_bank')->result()[0]->total;
	}
	
	function get_total_list_bank_company_unfiltered($param)
	{
		$this->db->select("count(company_bank.id) as total");
		return $this->db->get('company_bank')->result()[0]->total;
	}
	
	function add_bank_company($param)
	{
		$this->db->insert('company_bank',$param);
	}
	
	function edit_bank_company($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('company_bank',$param);
	}
	
	function get_bank_company($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('company_bank')->row();
	}
	
	function delete_bank_company($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('company_bank');
	}
	
	function get_config($array_name)
	{
		for($i = 0; $i < sizeof($array_name);$i++)
		{
			if($i == 0)
				$this->db->where('name',$array_name[$i]);
			else
				$this->db->or_where('name',$array_name[$i]);
		}
		$_config = $this->db->get('config')->result();
		
		$result = array();
		foreach($_config as $val)
		{
			$result[$val->name] = $val->value;
		}
		return $result;
	}
	
	function set_config($array_data)
	{
		$array = array();
		foreach($array_data as $key=>$val)
		{
			$array[] = array(
				'name' => $key,
				'value' => $val,
			);
		}
		$this->db->update_batch('config', $array, 'name');
	}
	
	function update_admin_token($id,$token)
	{
		$this->db->where('id',$id);
		$this->db->set('token',$token);
		$this->db->update('accounts');
	}
	
	function get_list_regencies($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("regencies.name",$param['search'],'both');
		$this->db->join('provinces','provinces.id = regencies.province_id','left');
		$this->db->select('regencies.id, regencies.name,  provinces.name as province');
		return $this->db->get('regencies')->result();
	}
	
	function get_total_list_regencies_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(regencies.id) as total");
		return $this->db->get('regencies')->result()[0]->total;
	}
	
	function get_regencies_by_id($id)
	{
		$this->db->where("regencies.id",$id);	
		return $this->db->get('regencies')->row();
	}
}
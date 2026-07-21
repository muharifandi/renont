<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Config_m extends MY_Model {
	
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
	
	function get_profile_detail($id)
	{
		$this->db->where('accounts.id',$id);
		$this->db->where('accounts_groups.group_id','7');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('agents','agents.account_id = accounts.id','left');
		$this->db->join('agents_file','agents_file.account_id = accounts.id','left');
		$this->db->join('agents_balance','agents_balance.account_id = accounts.id','left');
		$this->db->join('(
			SELECT agent_id, count(agent_id) as total from partners GROUP BY agent_id
			) p','p.agent_id = accounts.id','left');
		$this->db->join('regencies','regencies.id = agents.regencies_id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->select("accounts.id, accounts.first_name, accounts.last_name, accounts.email, accounts.phone,agents.address, agents_balance.balance, agents.regencies_id, regencies.name as regencies_name,agents.img_profile,agents.identity_number,agents_file.img_identity, accounts.active");
		return $this->db->get('accounts')->row();
	}
	
	function update_profile($id, $data)
	{
		$this->db->where('account_id',$id);
		$this->db->set($data);
		$this->db->update('agents');
	}
	
	function update_profile_file($id, $data)
	{
		$this->db->where('account_id',$id);
		$this->db->set($data);
		$this->db->update('agents_file');
	}
	
	function get_all_bank()
	{
		return $this->db->get('bank')->result();
	}
	
	function get_list_bank($account_id,$param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->group_start();
		$this->db->like("agents_bank.name",$param['search'],'both');
		$this->db->or_like("agents_bank.bank_number",$param['search'],'both');
		$this->db->or_like("bank.name",$param['search'],'both');
		$this->db->group_end();
		$this->db->join('bank','bank.id = agents_bank.bank_id','left');
		$this->db->select('agents_bank.id, bank.name as bank_name,bank.code, bank.icon, agents_bank.bank_number, agents_bank.name');
		return $this->db->get('agents_bank')->result();
	}
	
	function get_total_list_bank_filtered($account_id,$param)
	{
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->group_start();
		$this->db->like("agents_bank.name",$param['search'],'both');
		$this->db->or_like("agents_bank.bank_number",$param['search'],'both');
		$this->db->or_like("bank.name",$param['search'],'both');
		$this->db->group_end();
		$this->db->join('bank','bank.id = agents_bank.bank_id','left');
		$this->db->select("count(agents_bank.id) as total");
		return $this->db->get('agents_bank')->result()[0]->total;
	}
	
	function get_total_list_bank_unfiltered($account_id,$param)
	{
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->select("count(agents_bank.id) as total");
		return $this->db->get('agents_bank')->result()[0]->total;
	}
	
	function add_bank($account_id,$param)
	{
		$this->db->set('account_id',$account_id);
		$this->db->insert('agents_bank',$param);
	}
	
	function edit_bank($account_id,$id,$param)
	{
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->where('id',$id);
		$this->db->update('agents_bank',$param);
	}
	
	function get_bank($account_id,$id)
	{
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->where('id',$id);
		return $this->db->get('agents_bank')->row();
	}
	
	function delete_bank($account_id,$id)
	{
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->where('id',$id);
		$this->db->delete('agents_bank');
	}
	
	function get_all_agent_bank($account_id)
	{
		$this->db->where('agents_bank.account_id',$account_id);
		$this->db->join('bank','bank.id = agents_bank.bank_id','left');
		$this->db->select('agents_bank.id, bank.name as bank_name,bank.code, bank.icon, agents_bank.bank_number, agents_bank.name');
		return $this->db->get('agents_bank')->result();
	}
	
	function update_agent_token($id,$token)
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
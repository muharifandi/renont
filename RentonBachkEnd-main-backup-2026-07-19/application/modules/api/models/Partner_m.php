<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partner_m extends MY_Model {
	
	function register($data)
	{
		$this->db->insert('partners',$data);
		$this->db->set('account_id',$data['account_id']);
		$this->db->set('status','1');
		$this->db->set('feature_id','1');
		$this->db->insert('partners_features');
		$this->db->set('account_id',$data['account_id']);
		$this->db->insert('partners_config');
	}
	
	function insert_partner_file($data)
	{
		$this->db->insert('partners_file',$data);
	}
	
	function delete($account_id)
	{
		$this->db->where('account_id',$account_id);
		$this->db->delete('partners');
		
		$this->db->where('account_id',$account_id);
		$this->db->where('group_id',4);
		$this->db->delete('accounts_groups');
	}
	function check_account_valid($id)
	{
		$this->db->where('account_id',$id);
		$row = $this->db->get('accounts_groups')->row();
		if($row)
		{
			if($row->group_id == 4)
				return true;
			else
				return false;
		}else
			return false;
		
	}
	
	function detail($id)
	{
		$status = $this->check_account_valid($id);
		
		if($status)
		{
			$this->db->where('partners.account_id',$id);
			
			$this->db->select('partners.*, regencies.name as regencies_name, ownerships.name as ownership_name');
			$this->db->join('regencies','regencies.id = partners.regencies_id','left');
			$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
			return $this->db->get('partners')->row();
		}else
			return null;
	}
	
	function get_key($id)
	{
		$this->db->where('account_id',$id);
		return $this->db->get('keys')->row()->key;
	}
	
	function get_status($id)
	{
		$this->db->where('account_id',$id);
		$result = $this->db->get('partners')->row();
		
		if($result)
			return $result->status;
		else
			return -1;
	}
	
	function features($id)
	{
		$this->db->where('account_id',$id);
		
		$this->db->select('partners_features.*,feature.name, feature_status.name as status_name, feature.icon');
		$this->db->join('feature','feature.id = partners_features.feature_id','left');
		$this->db->join('feature_status','feature_status.id = partners_features.status','left');
		
		return $this->db->get('partners_features')->result();
	}
	
	function list_feature()
	{
		return $this->db->get('feature')->result();
	}
	
	function list_feature_pair($id)
	{
		//$this->db->where('partners_features.account_id',$id);
		
		$this->db->select('partners_features.status, feature.id as feature_id,feature.name, feature_status.name as status_name, feature.icon');
		$this->db->join('partners_features','feature.id = partners_features.feature_id AND partners_features.account_id = '.$id ,'left');
		$this->db->join('feature_status','feature_status.id = partners_features.status','left');
		return $this->db->get('feature')->result();
	}
	
	function update_profile_image($account_id, $img_filename)
	{
		$data = array(
			'img_profile' => $img_filename,
		);
		$this->db->where('account_id',$account_id);
		$this->db->update('partners',$data);
	}
	
	function update_partner($account_id,$data)
	{
		$this->db->where('account_id',$account_id);
		$this->db->update('partners',$data);
	}
	
	function request_feature($account_id,$data)
	{
		$this->db->where('account_id',$account_id);
		$this->db->where('feature_id',$data['feature_id']);
		$pf = $this->db->get('partners_features')->row();
		
		if($pf == null)
		{
			$data['account_id'] = $account_id;
			$this->db->insert('partners_features',$data);
			return true;
		}else
		{
			if($pf->status == 2 || $pf->status == 3)
				return false;
			else
			{
				$this->db->where('account_id',$account_id);
				$this->db->where('feature_id',$data['feature_id']);
				$this->db->update('partners_features',$data);
				return true;
			}
		}
	}
	
	function partner_info($id)
	{
		$this->db->where('partners.account_id',$id);
			
		$this->db->select('partners.id,partners.account_id,partners.company_name,partners.img_profile,partners.description, regencies.name as regencies_name, ownerships.name as ownership_name,partners.address, partners.latitude, partners.longitude');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
		return $this->db->get('partners')->row();
	}
}
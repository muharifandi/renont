<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partner_m extends MY_Model {

	function get_list_register_request($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->where('partners.status',0);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname,accounts.email,accounts.phone, partners.img_profile, ownerships.name as ownership, partners.company_name, regencies.name as regencies");
		return $this->db->get('accounts')->result();
	}
	
	function get_total_list_register_request_filtered($param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->where('partners.status',0);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_total_list_register_request_unfiltered($param)
	{
		$this->db->where('partners.status',0);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function request_detail($id)
	{
		$this->db->where('accounts.id',$id);
		$this->db->where('partners.status',0);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->join('partners_file','partners_file.account_id = accounts.id','left');
		$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->select("accounts.id,partners.referal_id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, accounts.email,accounts.phone, partners.img_profile, accounts.phone,partners.ownership_id, ownerships.name as ownership, partners.company_name, partners.description, regencies.name as regencies, partners.address, partners.latitude, partners.longitude, partners.tax_number, partners_file.img_identity, partners_file.img_driver_licence, partners_file.img_bussiness_licence, partners_file.img_bussiness_registration");
		return $this->db->get('accounts')->row();
	}
	
	function accept_request($id)
	{
		$this->db->where('account_id',$id);
		$this->db->set('status',1);
		$this->db->update('partners');
	}
	
	function reject_request($id)
	{
		$this->db->where('account_id',$id);
		$this->db->set('status',2);
		$this->db->update('partners');
	}
	
	function get_list($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("partners.company_name",$param['search'],'both');
		$this->db->group_end();
		//$this->db->where('partners.status',1);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->join('active_status','active_status.id = partners.status','left');
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname,accounts.email,accounts.phone, partners.img_profile, ownerships.name as ownership, partners.company_name, regencies.name as regencies, partners.status, active_status.name as status_name");
		return $this->db->get('accounts')->result();
	}
	
	function get_total_list_filtered($param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		//$this->db->where('partners.status',1);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($param)
	{
		//$this->db->where('partners.status',1);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_active_status()
	{
		return $this->db->get('active_status')->result();
	}
	
	function update_active_status($id,$status)
	{
		$this->db->where('account_id',$id);
		$this->db->set('status',$status);
		$this->db->update('partners');
	}
	
	function get_all_token()
	{
		$this->db->where('partners.status',1);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		
		$this->db->select("accounts.token");
		
		$result = $this->db->get('accounts')->result();
		
		$tokens = array();
		foreach($result as $val)
		{
			$tokens[] = $val->token;
		}
		return $tokens;
	}
	
	function delete($id)
	{
		$this->db->where('account_id',$id);
		$this->db->delete('partners');
	}
	
	function get_list_feature_request($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->order_by('partners_features.date_added','DESC');
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like("feature_status.name",$param['search'],'both');
		$this->db->join('partners','partners.account_id = partners_features.account_id','left');
		$this->db->join('feature','feature.id = partners_features.feature_id','left');
		$this->db->join('feature_status','feature_status.id = partners_features.status','left');
		
		$this->db->select('partners_features.*, partners.company_name, partners.img_profile,feature.name as feature_name, feature_status.name as status_name');
		return $this->db->get('partners_features')->result();
	}
	
	function get_total_list_feature_request_filtered($param)
	{
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->join('partners','partners.account_id = partners_features.account_id','left');
		$this->db->select("count(partners_features.id) as total");
		return $this->db->get('partners_features')->result()[0]->total;
	}
	
	function get_total_list_feature_request_unfiltered($param)
	{
		$this->db->select("count(partners_features.id) as total");
		return $this->db->get('partners_features')->result()[0]->total;
	}
	
	function get_feature_status()
	{
		return $this->db->get('feature_status')->result();
	}
	
	function update_feature_request_status($id,$status)
	{	
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('partners_features');
	}
	
	function partner_unprocessed_count()
	{
		$this->db->where('status',0);
		$this->db->select('count(id) count');
		return $this->db->get('partners')->row()->count;
	}
	
	function partner_feature_unprocessed_count()
	{
		$this->db->where('status',0);
		$this->db->select('count(id) count');
		return $this->db->get('partners_features')->row()->count;
	}
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Voucher_m extends MY_Model {
	
	function get_list($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("code",$param['search'],'both');
		$this->db->or_like("vouchers.description",$param['search'],'both');
		$this->db->join('groups','groups.id = vouchers.user_type','left');
		$this->db->join('status','status.id = vouchers.status','left');
		$this->db->join('voucher_type','voucher_type.id = vouchers.voucher_type','left');
		$this->db->join('feature','feature.id = vouchers.feature_id','left');
		$this->db->select('vouchers.id, code, feature.name as feature, groups.description as user_type, voucher_type.name as voucher_type,value, vouchers.description, use_expire, DATE_FORMAT(start_date, "%d %M %Y") as start_date, DATE_FORMAT(end_date, "%d %M %Y") as end_date, use_quota, quota,status.id as status_id, status.name as status, date_modified');
		return $this->db->get('vouchers')->result();
	}
	
	function get_total_list_filtered($param)
	{
		$this->db->like("code",$param['search'],'both');
		$this->db->select("count(vouchers.id) as total");
		return $this->db->get('vouchers')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($param)
	{
		$this->db->select("count(vouchers.id) as total");
		return $this->db->get('vouchers')->result()[0]->total;
	}
	
	
	function get_voucher_type()
	{
		return $this->db->get('voucher_type')->result();
	}
	
	function update_status($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('vouchers');
	}
	
	function add_voucher($param)
	{
		$this->db->insert('vouchers',$param);
	}
	
	function delete($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('vouchers');
	}
	
}
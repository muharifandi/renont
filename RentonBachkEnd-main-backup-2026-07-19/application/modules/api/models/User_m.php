<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_m extends MY_Model {

	function check_email($email)
	{
		$this->db->where('email',$email);
		return $this->db->get('users')->row();
	}
	
	function check_phone($phone)
	{
		$this->db->where('phone',$phone);
		
		return $this->db->get('users')->row();
	}
	
	function get_regencies($regency)
	{
		$this->db->like('name',$regency,'both');
		
		return $this->db->get('regencies')->result();
	}
	
	function register($data)
	{
		$this->db->insert('partners',$data);
	}
	
	function insert_partner_file($data)
	{
		$this->db->insert('partners_file',$data);
	}
	
	function check_account_valid($id)
	{
		$this->db->where('user_id',$id);
		$row = $this->db->get('users_groups')->row();
		if($row)
		{
			if($row->group_id == 4)
				return true;
			else
				return false;
		}else
			return false;
		
	}
	
	function get_user_key($id)
	{
		$this->db->where('user_id',$id);
		return $this->db->get('keys')->row()->key;
	}
}
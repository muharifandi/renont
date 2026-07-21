<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Base_m extends MY_Model {
	
	function get_status()
	{
		return $this->db->get('status')->result();
	}
	
	function get_user_type_filtered()
	{
		$this->db->where('id != 1');
		$this->db->where('id != 2');
		$this->db->where('id != 3');
		
		return $this->db->get('groups')->result();
	}
	
	function get_feature()
	{
		return $this->db->get('feature')->result();
	}
	
	
	
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CustomerRent_m extends MY_Model {

	function list_transaction($account_id,$param)
	{
		$this->db->order_by('date_modified','DESC');
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->where('transaction_rent_vehicle.account_id',$account_id);
		if($param['status'] != null && $param['status'] !=-1)
		{
			$this->db->where('transaction_rent_vehicle.status',$param['status']);
		}
		$this->db->group_by('transaction_rent_vehicle.id');
		$this->db->join('transaction_rent_vehicle_status','transaction_rent_vehicle_status.id = transaction_rent_vehicle.status','left');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = transaction_rent_vehicle.item_id','left');
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->select('transaction_rent_vehicle.id,transaction_rent_vehicle.price_package_name,transaction_rent_vehicle.start_date, transaction_rent_vehicle.end_date,transaction_rent_vehicle.total_payment,transaction_rent_vehicle.date_modified,rent_vehicles_item.title as vehicle_title,rent_vehicles_item_images.img as img, transaction_rent_vehicle_status.name as status_name');
		return $this->db->get('transaction_rent_vehicle')->result();
	}
	
	function transaction_detail($id)
	{
		$this->db->where('transaction_rent_vehicle.id',$id);
		$this->db->join('transaction_rent_vehicle_status','transaction_rent_vehicle_status.id = transaction_rent_vehicle.status','left');
		$this->db->select('transaction_rent_vehicle.*, transaction_rent_vehicle_status.name as status_name');
		return $this->db->get('transaction_rent_vehicle')->row();
	}
	
	function is_review_submit($transaction_id, $account_id)
	{
		$this->db->where('account_id',$account_id);
		$this->db->where('transaction_id',$transaction_id);
		$result = $this->db->get('review_vehicle')->num_rows();
		
		if($result > 0)
			return true;
		else
			return false;
	}
	
	function post_review($data)
	{
		$this->db->insert('review_vehicle',$data);
	}
	
}
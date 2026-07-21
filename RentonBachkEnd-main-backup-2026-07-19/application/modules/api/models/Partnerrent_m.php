<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PartnerRent_m extends MY_Model {

	function get_functional_type()
	{
		$this->db->order_by("name", "asc");
		return $this->db->get('functional_type')->result();
	}
	
	function get_vehicle_type($functional_type)
	{
		$this->db->order_by("name", "asc");
		$this->db->where('functional_type',$functional_type);
		return $this->db->get('vehicle_type')->result();
	}
	
	function get_brand($functional_type)
	{
		$this->db->order_by("name", "asc");
		$this->db->where('functional_type',$functional_type);
		return $this->db->get('brand')->result();
	}
	
	function get_vehicle_model($brand_id)
	{
		$this->db->order_by("name", "asc");
		$this->db->where('brand_id',$brand_id);
		return $this->db->get('vehicle_model')->result();
	}
	
	function get_color()
	{
		$this->db->order_by("name", "asc");
		return $this->db->get('color')->result();
	}
	
	function get_transmition_type($functional_type)
	{
		$this->db->order_by("name", "asc");
		$this->db->where('functional_type',$functional_type);
		return $this->db->get('transmition_type')->result();
	}
	
	function get_driven_type($functional_type)
	{
		$this->db->order_by("name", "asc");
		$this->db->where('functional_type',$functional_type);
		return $this->db->get('driven_type')->result();
	}
	
	function get_fuel()
	{
		$this->db->order_by("name", "asc");
		return $this->db->get('fuel')->result();
	}
	
	function add_vehicle($account_id,$data)
	{
		$this->db->set('account_id',$account_id);
		$status = $this->db->insert('rent_vehicles_item',$data);
		if($status)
			return $this->db->insert_id();
		else
			return false;
	}
	
	function update_vehicle($item_id,$data)
	{
		$this->db->where('id',$item_id);
		$status = $this->db->update('rent_vehicles_item',$data);
		return $item_id;
	}
	
	function add_vehicle_photo($item_id,$img)
	{
		$this->db->set('item_id',$item_id);
		$this->db->set('img',$img);
		$this->db->insert('rent_vehicles_item_images');
	}
	
	
	function list_vehicle($account_id,$param)
	{
		if($param['status'] != null)
			$this->db->where('rent_vehicles_item.status',$param['status']);
		
		if($param['min_passenger'] != null)
			$this->db->where('max_passenger >= '.$param['min_passenger']);
		
		if($param['max_passenger'] != null)
			$this->db->where('max_passenger <= '.$param['max_passenger']);
		
		if($param['min_price'] != null)
			$this->db->where('price >= '.$param['min_price']);
		
		if($param['max_price'] != null)
			$this->db->where('price <= '.$param['max_price']);
		
		if($param['sort'] != null)
		{
			switch($param['sort'])
			{
				case 0 : 
					$this->db->order_by('rent_vehicles_item.date_modified',"DESC");break;
				case 1 :
					$this->db->order_by('rent_vehicles_item.title',"ASC");break;
				case 2 :
					$this->db->order_by('rent_vehicles_item.title',"DESC");break;
				case 3 :
					$this->db->order_by('rent_vehicles_item.price',"DESC");break;
				case 4 :
					$this->db->order_by('rent_vehicles_item.price',"ASC");break;
				default :
					$this->db->order_by('rent_vehicles_item.date_modified',"DESC");break;
			}
		}
		
		if( ($param['limit'] != null) && ($param['page'] != null) )
			$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		
		if($param['vehicle_functional_type_selected'] != null)
		    $this->db->where_in('rent_vehicles_item.functional_type',$param['vehicle_functional_type_selected']);
		
		$this->db->group_by('rent_vehicles_item.id');
		$this->db->where('account_id',$account_id);
		$this->db->where('status != -1');
		
		$this->db->select('rent_vehicles_item.id, rent_vehicles_item.title, rent_vehicles_item.functional_type,
		vehicle_type.name as vehicle_type_name, 
		rent_vehicles_item.with_driver, rent_vehicles_item.max_passenger,color.name as color_name,color.value as color_value,rent_vehicles_item.price, rent_vehicles_item.price_with_driver_basic, rent_vehicles_item.price_with_driver_full, rent_vehicles_item_images.img as img');
		
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('color','color.id = rent_vehicles_item.color_id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		return $this->db->get('rent_vehicles_item')->result();
	}
	
	function list_vehicle_min_max_value($account_id)
	{
		$this->db->where('account_id',$account_id);
		$this->db->select('LEAST(MIN(price),MIN(price_with_driver_basic),MIN(price_with_driver_full)) as price_min, GREATEST(MAX(price),MAX(price_with_driver_basic),MAX(price_with_driver_full)) as price_max');
		return $this->db->get('rent_vehicles_item')->row();
	}
	
	function vehicle_detail($id)
	{
		$this->db->where('rent_vehicles_item.id',$id);
		$this->db->where('rent_vehicles_item.status != -1');
		$this->db->select('rent_vehicles_item.*,vehicle_type.name as vehicle_type_name, brand.name as brand_name, vehicle_model.name as vehicle_model_name, color.name as color_name,color.value as color_value,driven_type.name as driven_type_name, transmition_type.name as transmition_type_name,fuel.name as fuel_type_name, status.name as status_name');
		$this->db->select('vehicle_type.icon as vehicle_type_icon, brand.icon as brand_icon, driven_type.icon as driven_type_icon, transmition_type.icon as transmition_type_icon, fuel.icon as fuel_type_icon');
		
		$this->db->join('status','status.id = rent_vehicles_item.status','left');
		$this->db->join('fuel','fuel.id = rent_vehicles_item.fuel_type','left');
		$this->db->join('transmition_type','transmition_type.id = rent_vehicles_item.transmition_type','left');
		$this->db->join('driven_type','driven_type.id = rent_vehicles_item.driven_type','left');
		$this->db->join('color','color.id = rent_vehicles_item.color_id','left');
		$this->db->join('brand','brand.id = rent_vehicles_item.brand_id','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('vehicle_model','vehicle_model.id = rent_vehicles_item.vehicle_model','left');
		return $this->db->get('rent_vehicles_item')->row();
	}
	
	function vehicle_photos($id)
	{
		$this->db->where('item_id',$id);
		return $this->db->get('rent_vehicles_item_images')->result();
	}
	
	function delete_vehicle_photo($id)
	{
		$this->db->where('id',$id);
		$img_filename = $this->db->get('rent_vehicles_item_images')->row()->img;
		
		$this->db->where('id',$id);
		$this->db->delete('rent_vehicles_item_images');
		
		return $img_filename;
	}
	
	function delete_vehicle($id)
	{	
		$this->db->where('id',$id);
		$this->db->set('status',-1);
		$this->db->update('rent_vehicles_item');
		
	}
	
	function config($account_id)
	{
		$this->db->where('account_id',$account_id);
		$this->db->select('force_with_driver,force_with_driver,max_day_cod,force_disable_delivery, delivery_fee, force_disable_pickoff, pickoff_fee, overtime_fee');
		return $this->db->get('partners_config')->row();
	}
	
	function update_config($account_id,$data){
		$this->db->where('account_id',$account_id);
		$this->db->update('partners_config',$data);
	}
	
	function list_transaction($account_id,$param)
	{
		$this->db->order_by('date_modified','DESC');
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->where('rent_vehicles_item.account_id',$account_id);
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
	
	function count_transaction_success($account_id,$start_date,$end_date)
	{
		$this->db->where('rent_vehicles_item.account_id',$account_id);
		
		$this->db->where("DATE(transaction_rent_vehicle.date_modified) >='".$start_date."'");
		$this->db->where("DATE(transaction_rent_vehicle.date_modified) <='".$end_date."'");
		
		$this->db->where('transaction_rent_vehicle.status',8);
		
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = transaction_rent_vehicle.item_id','left');
		$this->db->select('count(transaction_rent_vehicle.id) as total');
		return $this->db->get('transaction_rent_vehicle')->row()->total;
	}
	
	function is_review_submit($transaction_id, $account_id)
	{
		$this->db->where('account_id',$account_id);
		$this->db->where('transaction_id',$transaction_id);
		$result = $this->db->get('review_customer')->num_rows();
		
		if($result > 0)
			return true;
		else
			return false;
	}
	
	function post_review($data)
	{
		$this->db->insert('review_customer',$data);
	}
	
	function count_vehicle_transaction_success($item_id)
	{
		$this->db->where('transaction_rent_vehicle.item_id',$item_id);
		$this->db->where('transaction_rent_vehicle.status',8);
		$this->db->select('count(transaction_rent_vehicle.id) as total');
		return $this->db->get('transaction_rent_vehicle')->row()->total;
	}
	
	function list_promote_vehicle($account_id,$param)
	{
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		$this->db->group_by('promote_rent_vehicle.id');
		$this->db->order_by('promote_rent_vehicle.date_added','DESC');
		$this->db->where('promote_rent_vehicle.account_id',$account_id);
		
		$this->db->select('promote_rent_vehicle.*, rent_vehicles_item.title, rent_vehicles_item_images.img as img,promote_rent_vehicle_status.name as status_name');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('promote_rent_vehicle_status','promote_rent_vehicle_status.id = promote_rent_vehicle.status','left');
		
		return $this->db->get('promote_rent_vehicle')->result();
	}
	
	function promote_detail($id)
	{
		$this->db->where('promote_rent_vehicle.id',$id);
		$this->db->select('promote_rent_vehicle.*, rent_vehicles_item.title, rent_vehicles_item_images.img as img,promote_rent_vehicle_status.name as status_name');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('promote_rent_vehicle_status','promote_rent_vehicle_status.id = promote_rent_vehicle.status','left');
		return $this->db->get('promote_rent_vehicle')->row();
	}
	
	function add_promote($account_id,$data)
	{
		$this->db->set('account_id',$account_id);
		$status = $this->db->insert('promote_rent_vehicle',$data);
		if($status)
			return $this->db->insert_id();
		else
			return false;
	}
	
	function update_promote($id,$data)
	{
		$this->db->where('id',$id);
		return $this->db->update('promote_rent_vehicle',$data);
	}
	
	function update_status_promote($id,$status_id)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status_id);
		$this->db->update('promote_rent_vehicle');
	}
	
}
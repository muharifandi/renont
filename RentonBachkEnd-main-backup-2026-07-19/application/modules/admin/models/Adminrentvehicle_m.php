<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminRentVehicle_m extends MY_Model {

/** functional Type **/
	function get_list_functional_type($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("name",$param['search'],'both');
		return $this->db->get('functional_type')->result();
	}
	
	function get_total_list_functional_type_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(functional_type.id) as total");
		return $this->db->get('functional_type')->result()[0]->total;
	}
	
	function get_total_list_functional_type_unfiltered($param)
	{
		$this->db->select("count(functional_type.id) as total");
		return $this->db->get('functional_type')->result()[0]->total;
	}
	
	function add_functional_type($param)
	{
		$this->db->insert('functional_type',$param);
	}
	
	function edit_functional_type($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('functional_type',$param);
	}
	
	function get_functional_type($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('functional_type')->row();
	}
	
	function delete_functional_type($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('functional_type');
	}

/** Brand **/	
	function get_list_brand($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("brand.name",$param['search'],'both');
		$this->db->join('functional_type','functional_type.id = brand.functional_type','left');
		$this->db->select('brand.*, functional_type.name as functional_type_name');
		return $this->db->get('brand')->result();
	}
	
	function get_total_list_brand_filtered($param)
	{
		$this->db->like("brand.name",$param['search'],'both');
		$this->db->select("count(brand.id) as total");
		return $this->db->get('brand')->result()[0]->total;
	}
	
	function get_total_list_brand_unfiltered($param)
	{
		$this->db->select("count(brand.id) as total");
		return $this->db->get('brand')->result()[0]->total;
	}
	
	function get_input_brand_parameter()
	{
		$result = array();
		$result['functional_type'] = $this->db->get('functional_type')->result();
		
		return $result;
	}
	
	function add_brand($param)
	{
		$this->db->insert('brand',$param);
	}
	
	function edit_brand($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('brand',$param);
	}
	
	function get_brand($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('brand')->row();
	}
	
	function delete_brand($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('brand');
	}
	
/** Vehicle Model **/
	function get_list_vehicle_model($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("vehicle_model.name",$param['search'],'both');
		$this->db->join('brand','brand.id = vehicle_model.brand_id','left');
		$this->db->select('vehicle_model.*, brand.name as brand_name');
		return $this->db->get('vehicle_model')->result();
	}
	
	function get_total_list_vehicle_model_filtered($param)
	{
		$this->db->like("vehicle_model.name",$param['search'],'both');
		$this->db->select("count(vehicle_model.id) as total");
		return $this->db->get('vehicle_model')->result()[0]->total;
	}
	
	function get_total_list_vehicle_model_unfiltered($param)
	{
		$this->db->select("count(vehicle_model.id) as total");
		return $this->db->get('vehicle_model')->result()[0]->total;
	}
	
	function get_input_vehicle_model_parameter()
	{
		$result = array();
		
		$this->db->order_by('functional_type.name ASC, brand.name ASC');
		$this->db->join('functional_type','functional_type.id = brand.functional_type','left');
		$this->db->select('brand.*, functional_type.name as functional_type_name');
		$result['brand'] = $this->db->get('brand')->result();
		
		return $result;
	}
	
	function add_vehicle_model($param)
	{
		$this->db->insert('vehicle_model',$param);
	}
	
	function edit_vehicle_model($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('vehicle_model',$param);
	}
	
	function get_vehicle_model($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('vehicle_model')->row();
	}
	
	function delete_vehicle_model($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('vehicle_model');
	}
	
/** Vehicle Type **/
	function get_list_vehicle_type($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("vehicle_type.name",$param['search'],'both');
		$this->db->join('functional_type','functional_type.id = vehicle_type.functional_type','left');
		$this->db->select('vehicle_type.*, functional_type.name as functional_type_name');
		return $this->db->get('vehicle_type')->result();
	}
	
	function get_total_list_vehicle_type_filtered($param)
	{
		$this->db->like("vehicle_type.name",$param['search'],'both');
		$this->db->select("count(vehicle_type.id) as total");
		return $this->db->get('vehicle_type')->result()[0]->total;
	}
	
	function get_total_list_vehicle_type_unfiltered($param)
	{
		$this->db->select("count(vehicle_type.id) as total");
		return $this->db->get('vehicle_type')->result()[0]->total;
	}
	
	function get_input_vehicle_type_parameter()
	{
		$result = array();
		$result['functional_type'] = $this->db->get('functional_type')->result();
		
		return $result;
	}
	
	function add_vehicle_type($param)
	{
		$this->db->insert('vehicle_type',$param);
	}
	
	function edit_vehicle_type($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('vehicle_type',$param);
	}
	
	function get_vehicle_type($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('vehicle_type')->row();
	}
	
	function delete_vehicle_type($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('vehicle_type');
	}
	
/** Color **/
	function get_list_color($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("name",$param['search'],'both');
		return $this->db->get('color')->result();
	}
	
	function get_total_list_color_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(color.id) as total");
		return $this->db->get('color')->result()[0]->total;
	}
	
	function get_total_list_color_unfiltered($param)
	{
		$this->db->select("count(color.id) as total");
		return $this->db->get('color')->result()[0]->total;
	}
	
	function add_color($param)
	{
		$this->db->insert('color',$param);
	}
	
	function edit_color($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('color',$param);
	}
	
	function get_color($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('color')->row();
	}
	
	function delete_color($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('color');
	}
	
/** Transmition Type **/
	function get_list_transmition_type($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("transmition_type.name",$param['search'],'both');
		$this->db->join('functional_type','functional_type.id = transmition_type.functional_type','left');
		$this->db->select('transmition_type.*, functional_type.name as functional_type_name');
		return $this->db->get('transmition_type')->result();
	}
	
	function get_total_list_transmition_type_filtered($param)
	{
		$this->db->like("transmition_type.name",$param['search'],'both');
		$this->db->select("count(transmition_type.id) as total");
		return $this->db->get('transmition_type')->result()[0]->total;
	}
	
	function get_total_list_transmition_type_unfiltered($param)
	{
		$this->db->select("count(transmition_type.id) as total");
		return $this->db->get('transmition_type')->result()[0]->total;
	}
	
	function get_input_transmition_type_parameter()
	{
		$result = array();
		$result['functional_type'] = $this->db->get('functional_type')->result();
		
		return $result;
	}
	
	function add_transmition_type($param)
	{
		$this->db->insert('transmition_type',$param);
	}
	
	function edit_transmition_type($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('transmition_type',$param);
	}
	
	function get_transmition_type($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('transmition_type')->row();
	}
	
	function delete_transmition_type($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('transmition_type');
	}
	
/** Driven Type **/
	function get_list_driven_type($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("driven_type.name",$param['search'],'both');
		$this->db->join('functional_type','functional_type.id = driven_type.functional_type','left');
		$this->db->select('driven_type.*, functional_type.name as functional_type_name');
		return $this->db->get('driven_type')->result();
	}
	
	function get_total_list_driven_type_filtered($param)
	{
		$this->db->like("driven_type.name",$param['search'],'both');
		$this->db->select("count(driven_type.id) as total");
		return $this->db->get('driven_type')->result()[0]->total;
	}
	
	function get_total_list_driven_type_unfiltered($param)
	{
		$this->db->select("count(driven_type.id) as total");
		return $this->db->get('driven_type')->result()[0]->total;
	}
	
	function get_input_driven_type_parameter()
	{
		$result = array();
		$result['functional_type'] = $this->db->get('functional_type')->result();
		
		return $result;
	}
	
	function add_driven_type($param)
	{
		$this->db->insert('driven_type',$param);
	}
	
	function edit_driven_type($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('driven_type',$param);
	}
	
	function get_driven_type($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('driven_type')->row();
	}
	
	function delete_driven_type($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('driven_type');
	}
	
/** functional Fuel **/
	function get_list_fuel($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("name",$param['search'],'both');
		return $this->db->get('fuel')->result();
	}
	
	function get_total_list_fuel_filtered($param)
	{
		$this->db->like("name",$param['search'],'both');
		$this->db->select("count(fuel.id) as total");
		return $this->db->get('fuel')->result()[0]->total;
	}
	
	function get_total_list_fuel_unfiltered($param)
	{
		$this->db->select("count(fuel.id) as total");
		return $this->db->get('fuel')->result()[0]->total;
	}
	
	function add_fuel($param)
	{
		$this->db->insert('fuel',$param);
	}
	
	function edit_fuel($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('fuel',$param);
	}
	
	function get_fuel($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('fuel')->row();
	}
	function delete_fuel($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('fuel');
	}
	
	function get_list_vehicle($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->group_by('rent_vehicles_item.id');
	
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like("status.name",$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.title",$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.year",$param['search'],'both');
		
		$this->db->select('rent_vehicles_item.id, rent_vehicles_item.title, rent_vehicles_item.functional_type, functional_type.name as functional_type_name,rent_vehicles_item.year,
		vehicle_type.name as vehicle_type_name,color.name as color_name,color.value as color_value,rent_vehicles_item.price, rent_vehicles_item.price_with_driver_basic, rent_vehicles_item.price_with_driver_full, rent_vehicles_item_images.img as img, partners.company_name,rent_vehicles_item.status, status.name as status_name');
		
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('color','color.id = rent_vehicles_item.color_id','left');
		$this->db->join('functional_type','functional_type.id = rent_vehicles_item.functional_type','left');
		$this->db->join('vehicle_type','vehicle_type.id = rent_vehicles_item.vehicle_type','left');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->join('status','status.id = rent_vehicles_item.status','left');
		return $this->db->get('rent_vehicles_item')->result();
	}
	
	function get_total_list_vehicle_filtered($param)
	{
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like("status.name",$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.title",$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.year",$param['search'],'both');
		
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->join('status','status.id = rent_vehicles_item.status','left');
		$this->db->select("count(rent_vehicles_item.id) as total");
		return $this->db->get('rent_vehicles_item')->result()[0]->total;
	}
	
	function get_total_list_vehicle_unfiltered($param)
	{
		$this->db->select("count(rent_vehicles_item.id) as total");
		return $this->db->get('rent_vehicles_item')->result()[0]->total;
	}
	
	function get_status()
	{
		return $this->db->get('status')->result();
	}
	
	function update_status_vehicle($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('rent_vehicles_item');
	}
	
	function get_list_vehicle_transaction($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);

		$this->db->order_by('transaction_rent_vehicle.date_added','DESC');
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like('CONCAT(accounts.first_name," ",accounts.last_name)',$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.title",$param['search'],'both');
		$this->db->or_like("transaction_rent_vehicle_status.name",$param['search'],'both');
		
		$this->db->group_by('transaction_rent_vehicle.id');
		$this->db->join('transaction_rent_vehicle_status','transaction_rent_vehicle_status.id = transaction_rent_vehicle.status','left');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = transaction_rent_vehicle.item_id','left');
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('accounts','accounts.id = transaction_rent_vehicle.account_id','left');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->select('transaction_rent_vehicle.id,transaction_rent_vehicle.price_package_name, DATE_FORMAT(transaction_rent_vehicle.start_date, "%d %M %Y %H:%i:%s") as start_date, DATE_FORMAT(transaction_rent_vehicle.end_date, "%d %M %Y %H:%i:%s") as end_date,transaction_rent_vehicle.price,transaction_rent_vehicle.total_payment, DATE_FORMAT(transaction_rent_vehicle.date_added, "%d %M %Y %H:%i:%s") as date_added, DATE_FORMAT(transaction_rent_vehicle.date_modified, "%d %M %Y %H:%i:%s") as date_modified, rent_vehicles_item.title as vehicle_title,rent_vehicles_item_images.img as img, transaction_rent_vehicle.status, transaction_rent_vehicle_status.name as status_name, CONCAT(accounts.first_name," ",accounts.last_name) as customer_name, partners.company_name');
		return $this->db->get('transaction_rent_vehicle')->result();
	}
	
	function get_total_list_vehicle_transaction_filtered($param)
	{
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like('CONCAT(accounts.first_name," ",accounts.last_name)',$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.title",$param['search'],'both');
		$this->db->or_like("transaction_rent_vehicle_status.name",$param['search'],'both');
		
		$this->db->join('transaction_rent_vehicle_status','transaction_rent_vehicle_status.id = transaction_rent_vehicle.status','left');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = transaction_rent_vehicle.item_id','left');
		$this->db->join('accounts','accounts.id = transaction_rent_vehicle.account_id','left');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		
		$this->db->select("count(transaction_rent_vehicle.id) as total");
		return $this->db->get('transaction_rent_vehicle')->result()[0]->total;
	}
	
	function get_total_list_vehicle_transaction_unfiltered($param)
	{
		$this->db->select("count(transaction_rent_vehicle.id) as total");
		return $this->db->get('transaction_rent_vehicle')->result()[0]->total;
	}
	
	function get_list_promote_vehicle_transaction($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);

		$this->db->order_by('promote_rent_vehicle.date_added','DESC');
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like('CONCAT(accounts.first_name," ",accounts.last_name)',$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.title",$param['search'],'both');
		$this->db->or_like("promote_rent_vehicle_status.name",$param['search'],'both');
		
		$this->db->join('promote_rent_vehicle_status','promote_rent_vehicle_status.id = promote_rent_vehicle.status','left');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('accounts','accounts.id = promote_rent_vehicle.account_id','left');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		$this->db->select('promote_rent_vehicle.id, DATE_FORMAT(promote_rent_vehicle.start_date, "%d %M %Y") as start_date, DATE_FORMAT(promote_rent_vehicle.end_date, "%d %M %Y") as end_date, promote_rent_vehicle.days,promote_rent_vehicle.price_per_day,promote_rent_vehicle.total_payment, promote_rent_vehicle.canceled_total_return, promote_rent_vehicle.viewer, DATE_FORMAT(promote_rent_vehicle.date_added, "%d %M %Y %H:%i:%s") as date_added, rent_vehicles_item.title as vehicle_title,rent_vehicles_item_images.img as img, promote_rent_vehicle.status, promote_rent_vehicle_status.name as status_name, CONCAT(accounts.first_name," ",accounts.last_name) as customer_name, partners.company_name');
		return $this->db->get('promote_rent_vehicle')->result();
	}
	
	function get_total_list_promote_vehicle_transaction_filtered($param)
	{
		$this->db->like("partners.company_name",$param['search'],'both');
		$this->db->or_like('CONCAT(accounts.first_name," ",accounts.last_name)',$param['search'],'both');
		$this->db->or_like("rent_vehicles_item.title",$param['search'],'both');
		$this->db->or_like("promote_rent_vehicle_status.name",$param['search'],'both');
		
		$this->db->join('promote_rent_vehicle_status','promote_rent_vehicle_status.id = promote_rent_vehicle.status','left');
		$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
		$this->db->join('rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
		$this->db->join('accounts','accounts.id = promote_rent_vehicle.account_id','left');
		$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
		
		$this->db->select("count(promote_rent_vehicle.id) as total");
		return $this->db->get('promote_rent_vehicle')->result()[0]->total;
	}
	
	function get_total_list_promote_vehicle_transaction_unfiltered($param)
	{
		$this->db->select("count(promote_rent_vehicle.id) as total");
		return $this->db->get('promote_rent_vehicle')->result()[0]->total;
	}
}
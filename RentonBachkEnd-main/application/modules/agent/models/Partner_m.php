<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Partner_m extends MY_Model {
	
	function get_list($agent_id,$param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->where('partners.agent_id',$agent_id);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
		$this->db->join('regencies','regencies.id = partners.regencies_id','left');
		$this->db->join('active_status','active_status.id = partners.status','left');
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname,accounts.email,accounts.phone, partners.img_profile, ownerships.name as ownership, partners.company_name, regencies.name as regencies, partners.status, active_status.name as status_name");
		return $this->db->get('accounts')->result();
	}
	
	function get_total_list_filtered($agent_id,$param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->where('partners.agent_id',$agent_id);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($agent_id,$param)
	{
		$this->db->where('partners.agent_id',$agent_id);
		$this->db->where('accounts_groups.group_id','4');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('partners','partners.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
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
			
			$this->db->select('accounts.id,accounts.username as identity, accounts.email,accounts.first_name, accounts.last_name,accounts.phone');
			$this->db->select('customers.img_profile,customers.identity_number');
			$this->db->select('customers_file.img_identity');
			$this->db->select('partners.company_name,partners.description,partners.address, partners.ownership_id,partners.tax_number, partners.img_profile as img_profile_partner, partners.regencies_id,partners.latitude, partners.longitude, regencies.name as regencies_name, ownerships.name as ownership_name');
			$this->db->select('partners_file.img_driver_licence, partners_file.img_bussiness_licence, partners_file.img_bussiness_registration');
			$this->db->join('regencies','regencies.id = partners.regencies_id','left');
			$this->db->join('ownerships','ownerships.id = partners.ownership_id','left');
			
			$this->db->join('accounts','accounts.id = partners.account_id','left');
			$this->db->join('customers','customers.account_id = partners.account_id','left');
			$this->db->join('customers_file','customers_file.account_id = partners.account_id','left');
			$this->db->join('partners_file','partners_file.account_id = partners.account_id','left');
			return $this->db->get('partners')->row();
		}else
			return null;
	}
	
	function register($customer_data,$customer_file,$partner_data, $partner_file)
	{
		$this->db->insert('customers',$customer_data);
		
		$this->db->set('account_id',$customer_data['account_id']);
		$this->db->insert('accounts_balance');
		$this->db->set('account_id',$customer_data['account_id']);
		$this->db->insert('customers_location');
		$this->db->set('account_id',$customer_data['account_id']);
		
		$this->db->insert('customers_file',$customer_file);
		
		$this->db->insert('partners',$partner_data);
		$this->db->set('account_id',$partner_data['account_id']);
		$this->db->set('status','1');
		$this->db->set('feature_id','1');
		$this->db->insert('partners_features');
		$this->db->set('account_id',$partner_data['account_id']);
		$this->db->insert('partners_config');
		
		$this->db->insert('partners_file',$partner_file);
	}
	
	function update($id,$customer_data,$customer_file,$partner_data, $partner_file)
	{
		$this->db->where('customers.account_id',$id);
		$this->db->update('customers',$customer_data);
		
		$this->db->where('customers_file.account_id',$id);
		$this->db->update('customers_file',$customer_file);
		
		$this->db->where('partners.account_id',$id);
		$this->db->update('partners',$partner_data);
		
		$this->db->where('partners_file.account_id',$id);
		$this->db->update('partners_file',$partner_file);
	}
	
	function get_list_commission($agent_id,$param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->order_by('history_agent_transaction.date_added','DESC');
		$this->db->group_start();
		$this->db->like("detail.title",$param['search'],'both');
		$this->db->or_like("detail.company_name",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('history_agent_transaction.account_id',$agent_id);
		$this->db->join("
			(
				(SElECT 
					transaction_rent_vehicle.id, 
					transaction_rent_vehicle.feature_id, 
					rent_vehicles_item.title,
					partners.company_name,
					transaction_rent_vehicle.total_payment as total_payment,
					transaction_rent_vehicle.total_overtime_fee as total_fee,
					transaction_rent_vehicle.admin_fee as admin_fee,
					CONCAT('".base_url()."data/vehicles/thumb_',rent_vehicles_item_images.img) as thumb_image,
					CONCAT('".base_url()."data/vehicles/',rent_vehicles_item_images.img) as image,
					transaction_rent_vehicle_status.name as status_name
					FROM transaction_rent_vehicle
					LEFT JOIN rent_vehicles_item 
					ON rent_vehicles_item.id = transaction_rent_vehicle.item_id
					LEFT JOIN rent_vehicles_item_images
					ON rent_vehicles_item_images.item_id = rent_vehicles_item.id
					LEFT JOIN transaction_rent_vehicle_status
					ON transaction_rent_vehicle_status.id = transaction_rent_vehicle.status
					LEFT JOIN partners
					ON partners.account_id = rent_vehicles_item.account_id
					GROUP BY transaction_rent_vehicle.id
				)
			UNION
				(
					SElECT 
						transaction_repair_vehicle.id, 
						transaction_repair_vehicle.feature_id, null as title, 
						null as company_name, 
						transaction_repair_vehicle.total_payment as total_payment,
						transaction_repair_vehicle.total_overtime_fee as total_fee,
						transaction_repair_vehicle.admin_fee as admin_fee,
						null as thumb_image, 
						null as image,
						null as status_name
						FROM transaction_repair_vehicle
				) 
			) detail
		",'detail.id = history_agent_transaction.transaction_id AND history_agent_transaction.feature_id = detail.feature_id','left');
		$this->db->select("history_agent_transaction.*,detail.company_name, detail.title, detail.thumb_image, detail.image, detail.total_payment, detail.total_fee, detail.admin_fee, detail.status_name");
		return $this->db->get('history_agent_transaction')->result();
	}
	
	function get_total_list_commission_filtered($agent_id,$param)
	{
		$this->db->group_start();
		$this->db->like("detail.title",$param['search'],'both');
		$this->db->or_like("detail.company_name",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('history_agent_transaction.account_id',$agent_id);
		$this->db->join("
			(
				(SElECT 
					transaction_rent_vehicle.id, 
					transaction_rent_vehicle.feature_id, 
					rent_vehicles_item.title,
					partners.company_name,
					transaction_rent_vehicle.total_payment as total_payment,
					transaction_rent_vehicle.total_overtime_fee as total_fee,
					transaction_rent_vehicle.admin_fee as admin_fee,
					CONCAT('".base_url()."data/vehicles/thumb_',rent_vehicles_item_images.img) as thumb_image,
					CONCAT('".base_url()."data/vehicles/',rent_vehicles_item_images.img) as image,
					transaction_rent_vehicle_status.name as status_name
					FROM transaction_rent_vehicle
					LEFT JOIN rent_vehicles_item 
					ON rent_vehicles_item.id = transaction_rent_vehicle.item_id
					LEFT JOIN rent_vehicles_item_images
					ON rent_vehicles_item_images.item_id = rent_vehicles_item.id
					LEFT JOIN transaction_rent_vehicle_status
					ON transaction_rent_vehicle_status.id = transaction_rent_vehicle.status
					LEFT JOIN partners
					ON partners.account_id = rent_vehicles_item.account_id
					GROUP BY transaction_rent_vehicle.id
				)
			UNION
				(
					SElECT 
						transaction_repair_vehicle.id, 
						transaction_repair_vehicle.feature_id, null as title, 
						null as company_name, 
						transaction_repair_vehicle.total_payment as total_payment,
						transaction_repair_vehicle.total_overtime_fee as total_fee,
						transaction_repair_vehicle.admin_fee as admin_fee,
						null as thumb_image, 
						null as image,
						null as status_name
						FROM transaction_repair_vehicle
				) 
			) detail
		",'detail.id = history_agent_transaction.transaction_id AND history_agent_transaction.feature_id = detail.feature_id','left');
		$this->db->select("count(history_agent_transaction.id) as total");
		return $this->db->get('history_agent_transaction')->result()[0]->total;
	}
	
	function get_total_list_commission_unfiltered($agent_id,$param)
	{
		$this->db->where('history_agent_transaction.account_id',$agent_id);
		$this->db->select("count(history_agent_transaction.id) as total");
		return $this->db->get('history_agent_transaction')->result()[0]->total;
	}
	
	function get_list_transaction($agent_id,$param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->order_by('transaction.date_added','DESC');
		$this->db->group_start();
		$this->db->like("transaction.company_name",$param['search'],'both');
		$this->db->or_like("transaction.title",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('transaction.agent_id',$agent_id);
		
		$this->db->select("*");
		return $this->db->get("
		(
			(SElECT 
				partners.agent_id,
				transaction_rent_vehicle.id, 
				transaction_rent_vehicle.feature_id, 
				feature.name as feature_name, 
				rent_vehicles_item.title,
				partners.company_name,
				transaction_rent_vehicle.total_payment as total_payment,
				transaction_rent_vehicle.total_overtime_fee as total_fee,
				transaction_rent_vehicle.admin_fee as admin_fee,
				transaction_rent_vehicle.date_added,
				CONCAT('".base_url()."data/vehicles/thumb_',rent_vehicles_item_images.img) as thumb_image,
				CONCAT('".base_url()."data/vehicles/',rent_vehicles_item_images.img) as image,
				transaction_rent_vehicle_status.name as status_name
				FROM transaction_rent_vehicle
				LEFT JOIN rent_vehicles_item 
				ON rent_vehicles_item.id = transaction_rent_vehicle.item_id
				LEFT JOIN rent_vehicles_item_images
				ON rent_vehicles_item_images.item_id = rent_vehicles_item.id
				LEFT JOIN transaction_rent_vehicle_status
				ON transaction_rent_vehicle_status.id = transaction_rent_vehicle.status
				LEFT JOIN partners
				ON partners.account_id = rent_vehicles_item.account_id
				LEFT JOIN feature
				ON feature.id = transaction_rent_vehicle.feature_id
				GROUP BY transaction_rent_vehicle.id
			)
		
		) transaction
		")->result();
	}
	
	function get_total_list_transaction_filtered($agent_id,$param)
	{
		$this->db->where('transaction.agent_id',$agent_id);
		$this->db->group_start();
		$this->db->like("transaction.company_name",$param['search'],'both');
		$this->db->or_like("transaction.title",$param['search'],'both');
		$this->db->group_end();
		
		
		$this->db->select("count(*) as total");
		return $this->db->get("
		(
			(SElECT 
				partners.agent_id,
				transaction_rent_vehicle.id, 
				transaction_rent_vehicle.feature_id, 
				feature.name as feature_name, 
				rent_vehicles_item.title,
				partners.company_name,
				transaction_rent_vehicle.total_payment as total_payment,
				transaction_rent_vehicle.total_overtime_fee as total_fee,
				transaction_rent_vehicle.admin_fee as admin_fee,
				CONCAT('".base_url()."data/vehicles/thumb_',rent_vehicles_item_images.img) as thumb_image,
				CONCAT('".base_url()."data/vehicles/',rent_vehicles_item_images.img) as image,
				transaction_rent_vehicle_status.name as status_name
				FROM transaction_rent_vehicle
				LEFT JOIN rent_vehicles_item 
				ON rent_vehicles_item.id = transaction_rent_vehicle.item_id
				LEFT JOIN rent_vehicles_item_images
				ON rent_vehicles_item_images.item_id = rent_vehicles_item.id
				LEFT JOIN transaction_rent_vehicle_status
				ON transaction_rent_vehicle_status.id = transaction_rent_vehicle.status
				LEFT JOIN partners
				ON partners.account_id = rent_vehicles_item.account_id
				LEFT JOIN feature
				ON feature.id = transaction_rent_vehicle.feature_id
				GROUP BY transaction_rent_vehicle.id
			)
		
		) transaction
		")->result()[0]->total;
	}
	
	function get_total_list_transaction_unfiltered($agent_id,$param)
	{
		$this->db->where('transaction.agent_id',$agent_id);
		
		$this->db->select("count(*) as total");
		return $this->db->get("
		(
			(SElECT 
				partners.agent_id,
				transaction_rent_vehicle.id, 
				transaction_rent_vehicle.feature_id, 
				feature.name as feature_name, 
				rent_vehicles_item.title,
				partners.company_name,
				transaction_rent_vehicle.total_payment as total_payment,
				transaction_rent_vehicle.total_overtime_fee as total_fee,
				transaction_rent_vehicle.admin_fee as admin_fee,
				CONCAT('".base_url()."data/vehicles/thumb_',rent_vehicles_item_images.img) as thumb_image,
				CONCAT('".base_url()."data/vehicles/',rent_vehicles_item_images.img) as image,
				transaction_rent_vehicle_status.name as status_name
				FROM transaction_rent_vehicle
				LEFT JOIN rent_vehicles_item 
				ON rent_vehicles_item.id = transaction_rent_vehicle.item_id
				LEFT JOIN rent_vehicles_item_images
				ON rent_vehicles_item_images.item_id = rent_vehicles_item.id
				LEFT JOIN transaction_rent_vehicle_status
				ON transaction_rent_vehicle_status.id = transaction_rent_vehicle.status
				LEFT JOIN partners
				ON partners.account_id = rent_vehicles_item.account_id
				LEFT JOIN feature
				ON feature.id = transaction_rent_vehicle.feature_id
				GROUP BY transaction_rent_vehicle.id
			)
		
		) transaction
		")->result()[0]->total;
	}
}
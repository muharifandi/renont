<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Report_m extends MY_Model {
		
		function get_agent_withdraw($id)
		{
			$this->db->where('agent_withdraw.id',$id);
			
			$this->db->join('accounts','accounts.id = agent_withdraw.account_id','left');
			$this->db->join('agents_bank','agents_bank.id = agent_withdraw.account_bank_id','left');
			$this->db->join('bank','bank.id = agents_bank.bank_id','left');
			$this->db->join('agent_withdraw_status','agent_withdraw_status.id = agent_withdraw.status','left');
			$this->db->select('agent_withdraw.*,accounts.first_name,accounts.last_name, agents_bank.bank_number,bank.name as bank_name,agent_withdraw_status.name as status_name');
			return $this->db->get('agent_withdraw')->row();
		}
		
		function get_agent_transaction($param)
		{
			if($param['group'] != null && $param['group'] == true)
			{
				$table = $this->get_agent_transaction_calculation($param);
				$table_count = 0;
				$result = array();
				$result_count = 0;
				$temp_id = 0;
				$row = null;
				foreach($table as $val)
				{
					
					if($temp_id != $val->account_id)
					{
						if($temp_id != 0 && $row != null)
						{
							$result[] = $row;
						}
						
						$row = new stdClass();
						$row->group = true;
						
						$row->account_id = $val->account_id;
						$row->name = $val->name;
						$row->start_date = $param['start_date'];
						$row->end_date = $param['end_date'];
						
						$data = $param;
						$data['ids'] = array($val->account_id);
						$sum = $this->get_agent_transaction_calculation($data,true);
						$row->sum_total_payment = $sum->sum_total_payment;
						$row->sum_total_fee = $sum->sum_total_fee;
						$row->sum_admin_fee = $sum->sum_admin_fee;
						$row->sum_value = $sum->sum_value;
						
						$row->data = array();
						
						$row->data[] = $val;
						
						$temp_id = $val->account_id;
					}else if($temp_id == $val->account_id)
					{
						$row->data[] = $val;
					}
					
					$table_count++;
					if(sizeof($table) == $table_count)
					{
						$result[] = $row;
					}
				}
				return $result;
			}else
			{
				$row = new stdClass();
				$row->group = false;
				
				$row->start_date = $param['start_date'];
				$row->end_date = $param['end_date'];
				
				$data = $param;
				
				$sum = $this->get_agent_transaction_calculation($data,true,true);
				$row->sum_total_payment = $sum->sum_total_payment;
				$row->sum_total_fee = $sum->sum_total_fee;
				$row->sum_admin_fee = $sum->sum_admin_fee;
				$row->sum_value = $sum->sum_value;
				
				$row->data = $this->get_agent_transaction_calculation($param,false);
				return $row;
			}
		}
		
		function get_agent_transaction_calculation($param,$grouped = false, $single_sum = false)
		{
			if($grouped)
			{
				if(!$single_sum)
				$this->db->group_by('history_agent_transaction.account_id');
			}
			
			if($param['ids'] != null)
			{
				$this->db->where_in('history_agent_transaction.account_id',$param['ids']);
			}
			
			if($param['start_date'] != null && $param['start_date'] != null)
			{
				$this->db->where("DATE(history_agent_transaction.date_added) >='".$param['start_date']."'");
				$this->db->where("DATE(history_agent_transaction.date_added) <='".$param['end_date']."'");
			}
			
			if($param['group'] == true)
			{
				$this->db->order_by('history_agent_transaction.account_id','ASC');
				$this->db->order_by('history_agent_transaction.date_added','ASC');
			}else
			{
				$this->db->order_by('history_agent_transaction.date_added','ASC');
				$this->db->order_by('history_agent_transaction.account_id','ASC');
			}
			
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
			$this->db->join('accounts','accounts.id = history_agent_transaction.account_id','left');
			
			if($grouped){
				$this->db->select('SUM(detail.total_payment) as sum_total_payment, SUM(detail.total_fee) as sum_total_fee, SUM(detail.admin_fee) as sum_admin_fee, SUM(history_agent_transaction.value) as sum_value');
				return $this->db->get('history_agent_transaction')->row();
				}else{
				$this->db->select("history_agent_transaction.*, CONCAT(accounts.first_name,' ',accounts.last_name) as name, detail.company_name, detail.title, detail.thumb_image, detail.image, detail.total_payment, detail.total_fee, detail.admin_fee, detail.status_name");
				
				return $this->db->get('history_agent_transaction')->result();
			}
		}
		
		function get_partner_transaction($param)
		{
			if($param['group'] != null && $param['group'] == true)
			{
				$table = $this->get_partner_transaction_calculation($param);
				$table_count = 0;
				$result = array();
				$result_count = 0;
				$temp_id = 0;
				$row = null;
				foreach($table as $val)
				{
					
					if($temp_id != $val->account_id)
					{
						if($temp_id != 0 && $row != null)
						{
							$result[] = $row;
						}
						
						$row = new stdClass();
						$row->group = true;
						
						$row->account_id = $val->account_id;
						$row->name = $val->company_name;
						$row->start_date = $param['start_date'];
						$row->end_date = $param['end_date'];
						
						$data = $param;
						$data['ids'] = array($val->account_id);
						$sum = $this->get_partner_transaction_calculation($data,true);
						$row->sum_total_payment = $sum->sum_total_payment;
						$row->sum_overtime_fee = $sum->sum_overtime_fee;
						$row->sum_admin_fee = $sum->sum_admin_fee;
						$row->sum_value = $sum->sum_value;
						
						$row->data = array();
						
						$row->data[] = $val;
						
						$temp_id = $val->account_id;
					}else if($temp_id == $val->account_id)
					{
						$row->data[] = $val;
					}
					
					$table_count++;
					if(sizeof($table) == $table_count)
					{
						$result[] = $row;
					}
				}
				return $result;
			}else
			{
				$row = new stdClass();
				$row->group = false;
				
				$row->start_date = $param['start_date'];
				$row->end_date = $param['end_date'];
				
				$data = $param;
				
				$sum = $this->get_partner_transaction_calculation($data,true,true);
				$row->sum_total_payment = $sum->sum_total_payment;
				$row->sum_overtime_fee = $sum->sum_overtime_fee;
				$row->sum_admin_fee = $sum->sum_admin_fee;
				$row->sum_value = $sum->sum_value;
				
				$row->data = $this->get_partner_transaction_calculation($param,false);
				return $row;
			}
		}
		
		function get_partner_transaction_calculation($param,$grouped = false, $single_sum = false)
		{
			if($grouped)
			{
				if(!$single_sum)
				$this->db->group_by('partners.account_id');
			}
			
			if($param['ids'] != null)
			{
				$this->db->where_in('partners.account_id',$param['ids']);
			}
			
			if($param['start_date'] != null && $param['start_date'] != null)
			{
				$this->db->where("DATE(transaction_rent_vehicle.date_modified) >='".$param['start_date']."'");
				$this->db->where("DATE(transaction_rent_vehicle.date_modified) <='".$param['end_date']."'");
			}
			
			if($param['group'] == true)
			{
				$this->db->order_by('partners.account_id','ASC');
				$this->db->order_by('transaction_rent_vehicle.date_modified','ASC');
			}else
			{
				$this->db->order_by('transaction_rent_vehicle.date_modified','ASC');
				$this->db->order_by('partners.account_id','ASC');
			}
			
			$this->db->where('transaction_rent_vehicle.status',8);
			$this->db->join('rent_vehicles_item','rent_vehicles_item.id = transaction_rent_vehicle.item_id','left');
			$this->db->join('accounts','accounts.id = transaction_rent_vehicle.account_id','left');
			$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
			
			
			if($grouped){
				$this->db->select('SUM(transaction_rent_vehicle.total_payment) as sum_total_payment, SUM(transaction_rent_vehicle.overtime_fee) as sum_overtime_fee, SUM(transaction_rent_vehicle.admin_fee) as sum_admin_fee');
				return $this->db->get('transaction_rent_vehicle')->row();
				}else{
				$this->db->select('transaction_rent_vehicle.id, partners.account_id, transaction_rent_vehicle.price_package_name,transaction_rent_vehicle.price,DATEDIFF(transaction_rent_vehicle.end_date,transaction_rent_vehicle.start_date) as days, transaction_rent_vehicle.total_payment, transaction_rent_vehicle.overtime_fee, transaction_rent_vehicle.admin_fee, transaction_rent_vehicle.date_modified, rent_vehicles_item.title, CONCAT(accounts.first_name," ",accounts.last_name) as customer_name, partners.company_name');
				
				return $this->db->get('transaction_rent_vehicle')->result();
			}
		}
		
		function get_topup($param)
		{
			if($param['group'] != null && $param['group'] == true)
			{
				$table = $this->get_topup_calculation($param);
				$table_count = 0;
				$result = array();
				$result_count = 0;
				$temp_id = 0;
				$row = null;
				foreach($table as $val)
				{
					
					if($temp_id != $val->company_bank_id)
					{
						if($temp_id != 0 && $row != null)
						{
							$result[] = $row;
						}
						
						$row = new stdClass();
						$row->group = true;
						
						$row->company_bank_id = $val->company_bank_id;
						$row->bank_name = $val->bank_name;
						$row->start_date = $param['start_date'];
						$row->end_date = $param['end_date'];
						
						$data = $param;
						$data['ids'] = array($val->company_bank_id);
						$sum = $this->get_topup_calculation($data,true);
						$row->sum_total_value = $sum->sum_total_value;
						$row->sum_total_value_with_code = $sum->sum_total_value_with_code;
						
						$row->data = array();
						
						$row->data[] = $val;
						
						$temp_id = $val->company_bank_id;
					}else if($temp_id == $val->company_bank_id)
					{
						$row->data[] = $val;
					}
					
					$table_count++;
					if(sizeof($table) == $table_count)
					{
						$result[] = $row;
					}
				}
				return $result;
			}else
			{
				$row = new stdClass();
				$row->group = false;
				
				$row->start_date = $param['start_date'];
				$row->end_date = $param['end_date'];
				
				$data = $param;
				$sum = $this->get_topup_calculation($data,true,true);
				$row->sum_total_value = $sum->sum_total_value;
				$row->sum_total_value_with_code = $sum->sum_total_value_with_code;
				
				$row->data = $this->get_topup_calculation($param,false);
				return $row;
			}
		}
		
		function get_topup_calculation($param,$grouped = false, $single_sum = false)
		{
			if($grouped)
			{
				if(!$single_sum)
				$this->db->group_by('company_bank.id');
			}
			
			if($param['ids'] != null)
			{
				$this->db->where_in('company_bank.id',$param['ids']);
			}
			
			if($param['start_date'] != null && $param['start_date'] != null)
			{
				$this->db->where("DATE(customer_topup.date_added) >='".$param['start_date']."'");
				$this->db->where("DATE(customer_topup.date_added) <='".$param['end_date']."'");
			}
			
			if($param['group'] == true)
			{
				$this->db->order_by('customer_topup.company_bank_id','ASC');
				$this->db->order_by('customer_topup.date_added','ASC');
			}else
			{
				$this->db->order_by('customer_topup.date_added','ASC');
				$this->db->order_by('customer_topup.company_bank_id','ASC');
			}
			
			$this->db->where('customer_topup.status',3);
			$this->db->where('customer_topup.processed',1);
			
			$this->db->join('accounts','accounts.id = customer_topup.account_id','left');
			$this->db->join('company_bank','company_bank.id = customer_topup.company_bank_id','left');
			$this->db->join('bank','bank.id = company_bank.bank_id','left');
			$this->db->join('customer_topup_status','customer_topup_status.id = customer_topup.status','left');
			
			
			if($grouped){
				$this->db->select('SUM(customer_topup.value) as sum_total_value,SUM(customer_topup.value_with_code) as sum_total_value_with_code');
				return $this->db->get('customer_topup')->row();
				}else{
				$this->db->select("customer_topup.id, customer_topup.company_bank_id, customer_topup.value, customer_topup.value_with_code, customer_topup.status,customer_topup.date_added, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, bank.name as bank_name, company_bank.bank_number, customer_topup_status.name as status_name");
				
				return $this->db->get('customer_topup')->result();
			}
		}
		
		function get_withdraw($param)
		{
			if($param['group'] != null && $param['group'] == true)
			{
				$table = $this->get_withdraw_calculation($param);
				$table_count = 0;
				$result = array();
				$result_count = 0;
				$temp_id = 0;
				$row = null;
				foreach($table as $val)
				{
					
					if($temp_id != $val->account_id)
					{
						if($temp_id != 0 && $row != null)
						{
							$result[] = $row;
						}
						
						$row = new stdClass();
						$row->group = true;
						
						$row->account_id = $val->account_id;
						$row->fullname = $val->fullname;
						$row->company_name = $val->company_name;
						$row->start_date = $param['start_date'];
						$row->end_date = $param['end_date'];
						
						$data = $param;
						$data['ids'] = array($val->account_id);
						$sum = $this->get_withdraw_calculation($data,true);
						$row->sum_total_value = $sum->sum_total_value;
						
						$row->data = array();
						
						$row->data[] = $val;
						
						$temp_id = $val->account_id;
						
					}else if($temp_id == $val->account_id)
					{
						$row->data[] = $val;
					}
					
					$table_count++;
					if(sizeof($table) == $table_count)
					{
						$result[] = $row;
					}
				}
				return $result;
			}else
			{
				$row = new stdClass();
				$row->group = false;
				
				$row->start_date = $param['start_date'];
				$row->end_date = $param['end_date'];
				
				$data = $param;
				$sum = $this->get_withdraw_calculation($data,true,true);
				$row->sum_total_value = $sum->sum_total_value;
				
				$row->data = $this->get_withdraw_calculation($param,false);
				return $row;
			}
		}
		
		function get_withdraw_calculation($param,$grouped = false, $single_sum = false)
		{
			if($grouped)
			{
				if(!$single_sum)
				$this->db->group_by('customer_withdraw.account_id');
			}
			
			if($param['ids'] != null)
			{
				$this->db->where_in('customer_withdraw.account_id',$param['ids']);
			}
			
			if($param['start_date'] != null && $param['start_date'] != null)
			{
				$this->db->where("DATE(customer_withdraw.date_added) >='".$param['start_date']."'");
				$this->db->where("DATE(customer_withdraw.date_added) <='".$param['end_date']."'");
			}
			
			if($param['group'] == true)
			{
				$this->db->order_by('customer_withdraw.account_id','ASC');
				$this->db->order_by('customer_withdraw.date_added','ASC');
			}else
			{
				$this->db->order_by('customer_withdraw.date_added','ASC');
				$this->db->order_by('customer_withdraw.account_id','ASC');
			}
			
			$this->db->where('customer_withdraw.status',2);
			$this->db->where('customer_withdraw.processed',1);
			
			$this->db->join('accounts','accounts.id = customer_withdraw.account_id','left');
			$this->db->join('partners','partners.account_id = accounts.id','left');
			$this->db->join('accounts_bank','accounts_bank.id = customer_withdraw.account_bank_id','left');
			$this->db->join('bank','bank.id = accounts_bank.bank_id','left');
			$this->db->join('customer_withdraw_status','customer_withdraw_status.id = customer_withdraw.status','left');
			
			
			if($grouped){
				$this->db->select('SUM(customer_withdraw.value) as sum_total_value');
				return $this->db->get('customer_withdraw')->row();
				}else{
				$this->db->select("customer_withdraw.id, customer_withdraw.account_id, customer_withdraw.account_bank_id, customer_withdraw.value, customer_withdraw.status,customer_withdraw.date_added, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, bank.name as bank_name, accounts_bank.bank_number,accounts_bank.name as account_bank_name, customer_withdraw_status.name as status_name, IF(IsNull(partners.account_id), 'Bukan Mitra', partners.company_name) as company_name");
				
				return $this->db->get('customer_withdraw')->result();
			}
		}
		
		function get_partner_promote_transaction($param)
		{
			if($param['group'] != null && $param['group'] == true)
			{
				$table = $this->get_partner_promote_transaction_calculation($param);
				$table_count = 0;
				$result = array();
				$result_count = 0;
				$temp_id = 0;
				$row = null;
				foreach($table as $val)
				{
					
					if($temp_id != $val->account_id)
					{
						if($temp_id != 0 && $row != null)
						{
							$result[] = $row;
						}
						
						$row = new stdClass();
						$row->group = true;
						
						$row->account_id = $val->account_id;
						$row->name = $val->company_name;
						$row->start_date = $param['start_date'];
						$row->end_date = $param['end_date'];
						
						$data = $param;
						$data['ids'] = array($val->account_id);
						$sum = $this->get_partner_promote_transaction_calculation($data,true);
						$row->sum_total_days = $sum->sum_total_days;
						$row->sum_total_viewers = $sum->sum_total_viewers;
						$row->sum_total_payment = $sum->sum_total_payment;
						$row->sum_total_return = $sum->sum_total_return;
						$row->sum_total_income = $sum->sum_total_income;
						
						$row->data = array();
						
						$row->data[] = $val;
						
						$temp_id = $val->account_id;
					}else if($temp_id == $val->account_id)
					{
						$row->data[] = $val;
					}
					
					$table_count++;
					if(sizeof($table) == $table_count)
					{
						$result[] = $row;
					}
				}
				return $result;
			}else
			{
				$row = new stdClass();
				$row->group = false;
				
				$row->start_date = $param['start_date'];
				$row->end_date = $param['end_date'];
				
				$data = $param;
				
				$sum = $this->get_partner_promote_transaction_calculation($data,true,true);
				$row->sum_total_days = $sum->sum_total_days;
				$row->sum_total_viewers = $sum->sum_total_viewers;
				$row->sum_total_payment = $sum->sum_total_payment;
				$row->sum_total_return = $sum->sum_total_return;
				$row->sum_total_income = $sum->sum_total_income;
				
				$row->data = $this->get_partner_promote_transaction_calculation($param,false);
				return $row;
			}
		}
		
		function get_partner_promote_transaction_calculation($param,$grouped = false, $single_sum = false)
		{
			if($grouped)
			{
				if(!$single_sum)
				$this->db->group_by('partners.account_id');
			}
			
			if($param['ids'] != null)
			{
				$this->db->where_in('partners.account_id',$param['ids']);
			}
			
			if($param['start_date'] != null && $param['start_date'] != null)
			{
				$this->db->where("DATE(promote_rent_vehicle.date_modified) >='".$param['start_date']."'");
				$this->db->where("DATE(promote_rent_vehicle.date_modified) <='".$param['end_date']."'");
			}
			
			if($param['group'] == true)
			{
				$this->db->order_by('partners.account_id','ASC');
				$this->db->order_by('promote_rent_vehicle.date_modified','ASC');
			}else
			{
				$this->db->order_by('promote_rent_vehicle.date_modified','ASC');
				$this->db->order_by('partners.account_id','ASC');
			}
			
			$this->db->group_start();
			$this->db->where('promote_rent_vehicle.status',2);
			$this->db->or_where('promote_rent_vehicle.status',3);
			$this->db->group_end();
			$this->db->join('promote_rent_vehicle_status','promote_rent_vehicle_status.id = promote_rent_vehicle.status','left');
			$this->db->join('rent_vehicles_item','rent_vehicles_item.id = promote_rent_vehicle.item_id','left');
			$this->db->join('partners','partners.account_id = rent_vehicles_item.account_id','left');
			
			if($grouped){
				$this->db->select('SUM(promote_rent_vehicle.days) as sum_total_days, SUM(promote_rent_vehicle.viewer) as sum_total_viewers, SUM(promote_rent_vehicle.total_payment) as sum_total_payment, SUM(promote_rent_vehicle.canceled_total_return) as sum_total_return, ( SUM(promote_rent_vehicle.total_payment) - SUM(promote_rent_vehicle.canceled_total_return) ) as sum_total_income');
				return $this->db->get('promote_rent_vehicle')->row();
				}else{
				$this->db->select('promote_rent_vehicle.id, promote_rent_vehicle.days,promote_rent_vehicle.price_per_day,promote_rent_vehicle.total_payment, promote_rent_vehicle.canceled_total_return, promote_rent_vehicle.viewer, promote_rent_vehicle.date_modified, rent_vehicles_item.title as vehicle_title, promote_rent_vehicle.status, promote_rent_vehicle_status.name as status_name, partners.account_id, partners.company_name, (promote_rent_vehicle.total_payment - promote_rent_vehicle.canceled_total_return) as total_income');
				
				return $this->db->get('promote_rent_vehicle')->result();
			}
		}
	}				
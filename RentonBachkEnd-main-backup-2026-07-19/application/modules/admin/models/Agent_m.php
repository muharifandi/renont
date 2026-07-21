<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Agent_m extends MY_Model {
	
	function get_list($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("accounts.id",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('accounts_groups.group_id','7');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('agents','agents.account_id = accounts.id','left');
		$this->db->join('agents_balance','agents_balance.account_id = accounts.id','left');
		$this->db->join('(
			SELECT agent_id, count(agent_id) as total from partners GROUP BY agent_id
			) p','p.agent_id = accounts.id','left');
		$this->db->join('regencies','regencies.id = agents.regencies_id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->select("accounts.id, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname,accounts.email,accounts.phone,agents_balance.balance, agents.img_profile, regencies.name as regencies, IFNULL(p.total,0) as partner_total, accounts.active, active_status.name as status_name");
		return $this->db->get('accounts')->result();
	}
	
	function get_total_list_filtered($param)
	{
		$this->db->group_start();
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("accounts.id",$param['search'],'both');
		$this->db->group_end();
		$this->db->where('accounts_groups.group_id','7');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('agents','agents.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($param)
	{
		$this->db->where('accounts_groups.group_id','7');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('agents','agents.account_id = accounts.id','left');
		$this->db->select("count(accounts.id) as total");
		return $this->db->get('accounts')->result()[0]->total;
	}
	
	function get_active_status()
	{
		return $this->db->get('active_status')->result();
	}
	
	function update_active_status($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('active',$status);
		$this->db->update('accounts');
	}
	
	function delete($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('accounts');
		$this->db->where('account_id',$id);
		$this->db->delete('agents');
	}
	
	function register($data)
	{
		$this->db->insert('agents',$data);
		$this->db->set('account_id',$data['account_id']);
		$this->db->insert('agents_balance');
		$this->db->set('account_id',$data['account_id']);
	}
	
	function insert_agent_file($data)
	{
		$this->db->insert('agents_file',$data);
	}
	
	function update($id, $data)
	{
		$this->db->where('account_id',$id);
		$this->db->set($data);
		$this->db->update('agents');
	}
	
	function update_agent_file($id, $data)
	{
		$this->db->where('account_id',$id);
		$this->db->set($data);
		$this->db->update('agents_file');
	}
	
	function detail($id)
	{
		$this->db->where('accounts.id',$id);
		$this->db->where('accounts_groups.group_id','7');
		$this->db->join('accounts_groups','accounts_groups.account_id = accounts.id','left');
		$this->db->join('agents','agents.account_id = accounts.id','left');
		$this->db->join('agents_file','agents_file.account_id = accounts.id','left');
		$this->db->join('agents_balance','agents_balance.account_id = accounts.id','left');
		$this->db->join('(
			SELECT agent_id, count(agent_id) as total from partners GROUP BY agent_id
			) p','p.agent_id = accounts.id','left');
		$this->db->join('regencies','regencies.id = agents.regencies_id','left');
		$this->db->join('active_status','active_status.id = accounts.active','left');
		$this->db->select("accounts.id, accounts.first_name, accounts.last_name, accounts.email, accounts.phone,agents_balance.balance, agents.regencies_id, regencies.name as regencies_name,agents.img_profile,agents.identity_number,agents_file.img_identity, accounts.active");
		return $this->db->get('accounts')->row();
	}
	
	function get_list_commision($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->order_by('min_target','asc');
		$this->db->like("agents_commision.title",$param['search'],'both');
		$this->db->or_like("agents_commision.description",$param['search'],'both');
		
		$this->db->select("agents_commision.*");
		return $this->db->get('agents_commision')->result();
	}
	
	function get_total_list_commision_filtered($param)
	{
		$this->db->like("agents_commision.title",$param['search'],'both');
		$this->db->or_like("agents_commision.description",$param['search'],'both');
		$this->db->select("count(agents_commision.id) as total");
		return $this->db->get('agents_commision')->result()[0]->total;
	}
	
	function get_total_list_commision_unfiltered($param)
	{
		$this->db->select("count(agents_commision.id) as total");
		return $this->db->get('agents_commision')->result()[0]->total;
	}
	
	function get_commision($id)
	{
		$this->db->where('agents_commision.id',$id);
		
		return $this->db->get('agents_commision')->row();
	}
	
	function add_commision($data)
	{
		$this->db->insert('agents_commision',$data);
	}
	
	function edit_commision($id,$data)
	{
		$this->db->where('agents_commision.id',$id);
		$this->db->set($data);
		$this->db->update('agents_commision');
	}
	
	
	function delete_commision($id)
	{
		$this->db->where('agents_commision.id',$id);
		$this->db->delete('agents_commision');
	}
	
	function get_list_withdraw($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like('customer_withdraw_status.name',$param['search'],'both');
		$this->db->order_by('agent_withdraw.date_added','DESC');
		$this->db->join('accounts','accounts.id = agent_withdraw.account_id','left');
		$this->db->join('agents_bank','agents_bank.id = agent_withdraw.account_bank_id','left');
		$this->db->join('bank','bank.id = agents_bank.bank_id','left');
		$this->db->join('customer_withdraw_status','customer_withdraw_status.id = agent_withdraw.status','left');
		$this->db->select("agent_withdraw.id, CONCAT('Rp. ', FORMAT(agent_withdraw.value, 0),',-') as value, agent_withdraw.status, agent_withdraw.date_added, CONCAT(accounts.first_name,' ',accounts.last_name) as fullname, bank.name as bank_name, bank.code as bank_code, bank.icon, agents_bank.bank_number, agents_bank.name, customer_withdraw_status.name as status_name");
		return $this->db->get('agent_withdraw')->result();
	}
	
	function get_total_list_withdraw_filtered($param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->join('accounts','accounts.id = agent_withdraw.account_id','left');
		$this->db->select("count(agent_withdraw.id) as total");
		return $this->db->get('agent_withdraw')->result()[0]->total;
	}
	
	function get_total_list_withdraw_unfiltered($param)
	{
		$this->db->select("count(agent_withdraw.id) as total");
		return $this->db->get('agent_withdraw')->result()[0]->total;
	}
	
	function withdraw_detail($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('agent_withdraw')->row();
	}
	
	function get_withdraw_status($id = null)
	{
		if($id == null)
			return $this->db->get('customer_withdraw_status')->result();
		else
		{
			$this->db->where('id',$id);
			return $this->db->get('customer_withdraw_status')->row();
		}
	}
	
	
	function update_withdraw_status($id,$status,$description)
	{
		$this->db->where('id',$id);
		$withdraw = $this->db->get('agent_withdraw')->row();
		$this->db->where('account_id',$withdraw->account_id);
		$balance = $this->db->get('agents_balance')->row();
		if($status == 2 && $withdraw->processed == 0)
		{
			if($balance->balance >= $withdraw->value)
			{
				$this->db->where('account_id',$withdraw->account_id);
				$this->db->set('balance','balance - '.$withdraw->value,false);
				$this->db->update('agents_balance');
				
				$this->db->set('processed',1);
				$this->db->set('status',$status);
			}else
			{
				$this->db->set('processed',1);
				$this->db->set('status',3);
			}
		}
		$this->db->where('id',$id);
		$this->db->set('description',$description);
		$this->db->update('agent_withdraw');
	}
	
	function withdraw_unprocessed_count()
	{
		$this->db->where('status',1);
		$this->db->select('count(id) count');
		return $this->db->get('agent_withdraw')->row()->count;
	}
	
	function get_token($account_id)
	{
		$this->db->where('id',$account_id);
		
		return $this->db->get('accounts')->row()->token;
	}
	
	function get_list_transaction($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->order_by('history_agent_transaction.date_added','DESC');
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("detail.title",$param['search'],'both');
		$this->db->or_like("detail.company_name",$param['search'],'both');
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
		$this->db->select("history_agent_transaction.*, CONCAT(accounts.first_name,' ',accounts.last_name) as name, detail.company_name, detail.title, detail.thumb_image, detail.image, detail.total_payment, detail.total_fee, detail.admin_fee, detail.status_name");
		return $this->db->get('history_agent_transaction')->result();
	}
	
	function get_total_list_transaction_filtered($param)
	{
		$this->db->like("CONCAT(accounts.first_name,' ',accounts.last_name)",$param['search'],'both');
		$this->db->or_like("detail.title",$param['search'],'both');
		$this->db->or_like("detail.company_name",$param['search'],'both');
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
		$this->db->select("count(history_agent_transaction.id) as total");
		return $this->db->get('history_agent_transaction')->result()[0]->total;
	}
	
	function get_total_list_transaction_unfiltered($param)
	{
		$this->db->select("count(history_agent_transaction.id) as total");
		return $this->db->get('history_agent_transaction')->result()[0]->total;
	}
	
	
	function get_partner_agent($partner_id)
	{
		$this->db->where('account_id', $partner_id);
		$partner = $this->db->get('partners')->row();
		
		return $partner->agent_id;
	}
	
	function pair_partner($partner_id,$agent_id)
	{
		$this->db->where('account_id',$partner_id);
		$this->db->set('agent_id',$agent_id);
		$this->db->update('partners');
	}
	
}
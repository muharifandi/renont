<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PartnerReward_m extends MY_Model {
	
	function get_list($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("partner_rewards.title",$param['search'],'both');
		$this->db->or_like("reward_scope.name",$param['search'],'both');
		$this->db->or_like("reward_type.name",$param['search'],'both');
		$this->db->join('status','status.id = partner_rewards.status','left');
		$this->db->join('reward_type','reward_type.id = partner_rewards.reward_type','left');
		$this->db->join('reward_scope','reward_scope.id = partner_rewards.reward_scope','left');
		$this->db->join('feature','feature.id = partner_rewards.feature_id','left');
		/**$this->db->select('vouchers.id, code, feature.name as feature, groups.description as user_type, voucher_type.name as voucher_type,value, vouchers.description, use_expire, DATE_FORMAT(start_date, "%d %M %Y") as start_date, DATE_FORMAT(end_date, "%d %M %Y") as end_date, use_quota, quota,status.id as status_id, status.name as status, date_modified');**/
		$this->db->select('partner_rewards.id, partner_rewards.img, partner_rewards.title, partner_rewards.description, feature.name as feature_name, reward_scope.name as reward_scope_name, reward_type.name as reward_type_name,partner_rewards.target, partner_rewards.point_reward, status.id as status_id,status.name as status_name, partner_rewards.date_added');
		return $this->db->get('partner_rewards')->result();
	}
	
	function get_total_list_filtered($param)
	{
		$this->db->like("partner_rewards.title",$param['search'],'both');
		$this->db->or_like("reward_scope.name",$param['search'],'both');
		$this->db->or_like("reward_type.name",$param['search'],'both');
		
		$this->db->join('reward_type','reward_type.id = partner_rewards.reward_type','left');
		$this->db->join('reward_scope','reward_scope.id = partner_rewards.reward_scope','left');
		$this->db->select("count(partner_rewards.id) as total");
		return $this->db->get('partner_rewards')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($param)
	{
		$this->db->select("count(partner_rewards.id) as total");
		return $this->db->get('partner_rewards')->result()[0]->total;
	}
	
	
	function get_reward_type()
	{
		return $this->db->get('reward_type')->result();
	}
	
	function get_reward_scope()
	{
		return $this->db->get('reward_scope')->result();
	}
	
	function update_status($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('partner_rewards');
	}
	
	
	function add_reward($param)
	{
		$this->db->insert('partner_rewards',$param);
	}
	
	function delete($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('partner_rewards');
	}
	
	function get_list_claim_reward($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		
		$this->db->order_by('history_partner_reward.date_modified','DESC');
		$this->db->where('history_partner_reward.claimed',1);
		$this->db->where('history_partner_reward.processed',0);
		$this->db->where('partner_rewards.reward_type',2);
		
		$this->db->like("partner_rewards.title",$param['search'],'both');
	
		
		$this->db->join('partners','partners.account_id = history_partner_reward.account_id','left');
		$this->db->join('partner_rewards','partner_rewards.id = history_partner_reward.reward_id','left');
		
		$this->db->select('history_partner_reward.*,partner_rewards.title,partner_rewards.img,partner_rewards.reward_type, partners.company_name,partners.address');
		return $this->db->get('history_partner_reward')->result();
	}
	
	function get_total_list_claim_reward_filtered($param)
	{
		$this->db->where('history_partner_reward.claimed',1);
		$this->db->where('history_partner_reward.processed',0);
		$this->db->where('partner_rewards.reward_type',2);
		$this->db->like("partner_rewards.title",$param['search'],'both');
		
		
		$this->db->join('partners','partners.account_id = history_partner_reward.account_id','left');
		$this->db->join('partner_rewards','partner_rewards.id = history_partner_reward.reward_id','left');
		
		$this->db->select("count(history_partner_reward.id) as total");
		return $this->db->get('history_partner_reward')->result()[0]->total;
	}
	
	function get_total_list_claim_reward_unfiltered($param)
	{
		$this->db->where('history_partner_reward.claimed',1);
		$this->db->where('history_partner_reward.processed',0);
		$this->db->where('partner_rewards.reward_type',2);
		$this->db->select("count(history_partner_reward.id) as total");
		$this->db->join('partner_rewards','partner_rewards.id = history_partner_reward.reward_id','left');
		return $this->db->get('history_partner_reward')->result()[0]->total;
	}
	
	function update_history_reward($id,$data)
	{
		$this->db->where('id',$id);
		
		$this->db->set($data);
		
		$this->db->update('history_partner_reward');
	}
	
	function reward_unprocessed_count()
	{
		$this->db->where('claimed',1);
		$this->db->where('processed',0);
		
		$this->db->select('count(history_partner_reward.id) as total');
		return $this->db->get('history_partner_reward')->row()->total;
	}
	
	function history_reward($id)
	{
		$this->db->where('id',$id);
		
		return $this->db->get('history_partner_reward')->row();
	}
	
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PartnerReward_m extends MY_Model {
	
	function list_reward($feature_id = null, $reward_scope = null)
	{
		$this->db->where('partner_rewards.status',1);
		$this->db->order_by('target','ASC');
		
		if($feature_id != null)
			$this->db->where('feature_id',$feature_id);
		
		if($reward_scope != null)
			$this->db->where('reward_scope',$reward_scope);
		return $this->db->get('partner_rewards')->result();
	}
	
	function is_reward_added($account_id,$reward_id,$start_date,$end_date)
	{
		$this->db->where('account_id',$account_id);
		$this->db->where('reward_id',$reward_id);
		$this->db->where("DATE(date_added) >='".$start_date."'");
		$this->db->where("DATE(date_added) <='".$end_date."'");
		$result = $this->db->get('history_partner_reward')->row();
		
		if($result)
			return true;
		else
			return false;
	}
	
	function add_reward($data)
	{
		$this->db->insert('history_partner_reward',$data);
	}
	
	function list_scope()
	{
		return $this->db->get('reward_scope')->result();
	}
	
	function reward_scope_detail($reward_scope)
	{
		$this->db->where('id',$reward_scope);
		return $this->db->get('reward_scope')->row();
	}
	
	function reward_aquired($account_id,$feature_id,$reward_scope,$start_date,$end_date)
	{	
		$this->db->order_by('partner_rewards.target','ASC');
		$this->db->where('partner_rewards.status',1);
		$this->db->where('partner_rewards.feature_id',$feature_id);
		$this->db->where('partner_rewards.reward_scope',$reward_scope);
		
	
		$this->db->join('history_partner_reward',"history_partner_reward.reward_id = partner_rewards.id AND DATE(history_partner_reward.date_added) >='".$start_date."' AND DATE(history_partner_reward.date_added) <='".$end_date."' AND history_partner_reward.account_id=".$account_id,'left');
		
		//$this->db->join('history_partner_reward',"history_partner_reward.reward_id = partner_rewards.id",'left');
		$this->db->select('partner_rewards.id,partner_rewards.title,partner_rewards.img,partner_rewards.reward_type,partner_rewards.target,partner_rewards.point_reward, IF(history_partner_reward.id IS NULL,0,1) as aquired, history_partner_reward.processed, history_partner_reward.claimed, history_partner_reward.id as reward_id');
		return $this->db->get('partner_rewards')->result();
	}
	
	function claim_reward($id)
	{
		$this->db->where('id',$id);
		$this->db->set('claimed',1);
		$this->db->update('history_partner_reward');
	}
	
	
}
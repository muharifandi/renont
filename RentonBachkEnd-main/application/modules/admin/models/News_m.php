<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News_m extends MY_Model {
	
	function get_list($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		$this->db->like("title",$param['search'],'both');
		$this->db->join('groups','groups.id = news.user_type','left');
		$this->db->join('status','status.id = news.status','left');
		$this->db->join('vouchers','vouchers.id = news.voucher_id','left');
		$this->db->select('news.id, news.img, groups.description as user_type, news.title, news.is_voucher, vouchers.code as voucher_code, status.id as status_id, status.name as status, news.date_modified');
		return $this->db->get('news')->result();
	}
	
	function get_total_list_filtered($param)
	{
		$this->db->like("title",$param['search'],'both');
		$this->db->select("count(news.id) as total");
		return $this->db->get('news')->result()[0]->total;
	}
	
	function get_total_list_unfiltered($param)
	{
		$this->db->select("count(news.id) as total");
		return $this->db->get('news')->result()[0]->total;
	}
	
	
	function update_status($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('news');
	}
	
	function add_news($param)
	{
		$this->db->insert('news',$param);
	}
	
	function edit_news($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('news',$param);
	}
	
	function get_news($id)
	{
		$this->db->where('news.id',$id);
		$this->db->join('vouchers','vouchers.id = news.voucher_id','left');
		$this->db->select('news.*, vouchers.code as voucher_code');
		return $this->db->get('news')->row();
	}
	function delete($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('news');
	}
	
	function get_list_preview($param)
	{
		$this->db->limit($param['limit']['length'],$param['limit']['start']);
		
		$this->db->order_by('order','ASC');
		$this->db->like("title",$param['search'],'both');
		$this->db->join('status','status.id = news_preview.status','left');
		$this->db->join('news','news.id = news_preview.news_id','left');
		$this->db->select('news_preview.*,news.title, news.img, status.name as status_name');
		return $this->db->get('news_preview')->result();
	}
	
	function get_total_list_preview_filtered($param)
	{
		$this->db->like("news.title",$param['search'],'both');
		$this->db->join('news','news.id = news_preview.news_id','left');
		$this->db->select("count(news_preview.id) as total");
		return $this->db->get('news_preview')->result()[0]->total;
	}
	
	function get_total_list_preview_unfiltered($param)
	{
		$this->db->select("count(news_preview.id) as total");
		return $this->db->get('news_preview')->result()[0]->total;
	}
	
	
	function update_status_preview($id,$status)
	{
		$this->db->where('id',$id);
		$this->db->set('status',$status);
		$this->db->update('news_preview');
	}
	
	function add_news_preview($param)
	{
		$this->db->insert('news_preview',$param);
	}
	
	function edit_news_preview($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('news_preview',$param);
	}
	
	function get_news_preview($id)
	{
		$this->db->where('news_preview.id',$id);
		$this->db->join('news','news.id = news_preview.news_id','left');
		$this->db->select('news_preview.*, news.title, news.img');
		return $this->db->get('news_preview')->row();
	}
	
	function delete_preview($id)
	{
		$this->db->where('id',$id);
		$this->db->delete('news_preview');
	}
	
	function add_preview($param)
	{
		$this->db->insert('news_preview',$param);
	}
	
	function edit_preview($id,$param)
	{
		$this->db->where('id',$id);
		$this->db->update('news_preview',$param);
	}
	
	
	
}
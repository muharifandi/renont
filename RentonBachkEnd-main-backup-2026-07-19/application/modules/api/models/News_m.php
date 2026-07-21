<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News_m extends MY_Model {

	function list($param)
	{
		$this->db->order_by('date_added','DESC');
		$this->db->where('status',1);
		$this->db->limit($param['limit'],( ($param['page']-1) * $param['limit'] ));
		
		if($param['partner_valid'] == false)
		{
			$this->db->where('user_type',5);
		}
		return $this->db->get('news')->result();
	}
	
	function detail($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('news')->row();
	}
	
	function list_preview()
	{
		$this->db->where('news_preview.status',1);
		$this->db->order_by('order','ASC');
		$this->db->join('status','status.id = news_preview.status','left');
		$this->db->join('news','news.id = news_preview.news_id','left');
		$this->db->select('news.id,news.title, news.img, status.name as status_name');
		return $this->db->get('news_preview')->result();
	}
	
}
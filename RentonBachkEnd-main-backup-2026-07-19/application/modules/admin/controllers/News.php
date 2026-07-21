<?php defined('BASEPATH') OR exit('No direct script access allowed');

class News extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		
		
		$this->load->model('admin/News_m');
		
	}
	
	public function index()
	{
		redirect('admin/dashboard', 'refresh');
	}
	
	public function list()
	{
		$this->load->model('Base_m');
		$list_status = $this->Base_m->get_status();
		$user_type = $this->Base_m->get_user_type_filtered();
		$result = array(
			'list_status' => $list_status, 
			'user_type' => $user_type, 
		);
		$this->show('news_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->News_m->get_list($param),
			"recordsTotal" => $this->News_m->get_total_list_unfiltered($param),
			"recordsFiltered" => $this->News_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function change_status($id)
	{
		$status_id = $this->input->post('status_id');
		$this->News_m->update_status($id,$status_id);
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function add()
	{
		$this->load->model('Base_m');
		$user_type = $this->Base_m->get_user_type_filtered();
		
		$result = array(
			'user_type' => $user_type, 
		);
		$this->show('news_add',$result,TRUE);
	}
	
	public function add_news()
	{
		$img_filename = null;
		if($_FILES["img_banner"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/news';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config);
		
			if ($this->upload->do_upload('img_banner')) {
				$img_filename = $this->upload->data("file_name");
			}
		}
		$param = array(
			'title' => $this->input->post('title'),
			'img' => $img_filename,
			'content' => $this->input->post('content'),
			'user_type' => $this->input->post('user_type'),
			'is_voucher' => $this->input->post('is_voucher'),
			'voucher_id' => $this->input->post('voucher_id'),
		);
		
		$this->News_m->add_news($param);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menambahkan berita',
		);
		
		redirect('admin/news/list','refresh');
	}
	
	public function edit($id)
	{
		$this->load->model('Base_m');
		$user_type = $this->Base_m->get_user_type_filtered();
		$news = $this->News_m->get_news($id);
		
		$result = array(
			'user_type' => $user_type, 
			'news' =>$news,
			'edit' =>true,
		);
		$this->show('news_add',$result,TRUE);
	}
	
	public function edit_news()
	{
		
		$id = $this->input->post('id');
		$img_filename = null;
		if($_FILES["img_banner"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/news';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config);
		
			if ($this->upload->do_upload('img_banner')) {
				$img_filename = $this->upload->data("file_name");
			}
		}
		
		$param = array(
			'title' => $this->input->post('title'),
			'content' => $this->input->post('content'),
			'user_type' => $this->input->post('user_type'),
			'is_voucher' => $this->input->post('is_voucher'),
			'voucher_id' => $this->input->post('voucher_id'),
		);
		
		if($img_filename)
			$param['img'] = $img_filename;
		
		$this->News_m->edit_news($id,$param);
		$result = array(
			'status' => true,
			'message' => 'Berhasil mengubah berita',
		);
		
		redirect('admin/news/list','refresh');
	}
	
	public function send_notification($id)
	{
		$this->load->model('Partner_m');
		$this->load->model('Customer_m');
		$news = $this->News_m->get_news($id);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
        $this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		if($news->user_type == 4)
		{
			//kirim ke mitra
			$tokens = $this->Partner_m->get_all_token();
			$this->fcm->setRecepients($tokens);
			$data_payload = array(
				'data_type' => 'news',
				'id' => $id,
			);
			$this->fcm->setData($data_payload);
			$notif = array("title" => $news->title, "text" => $news->title,'image' => base_url().'data/news/'.$news->img,'android_channel_id' => 3, 'sound' => 'default');
			$this->fcm->setNotification($notif);
			$this->fcm->send();
			
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengirim notifikasi berita "'.$news->title.'" ke mitra dengan total penerima '.sizeof($tokens).' Mitra' ,
			);
			echo json_encode($result);
		}else
		{
			//kirim ke pelanggan
			$tokens = $this->Customer_m->get_all_token();
			$this->fcm->setRecepients($tokens);
			$data_payload = array(
				'data_type' => 'news',
				'id' => $id,
			);
			$this->fcm->setData($data_payload);
			$notif = array("title" => $news->title, "text" => $news->title,'image' => base_url().'data/news/'.$news->img,'android_channel_id' => 3, 'sound' => 'default');
			$this->fcm->setNotification($notif);
			$this->fcm->send();
			
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengirim notifikasi berita "'.$news->title.'" ke pelanggan dengan total penerima '.sizeof($tokens).' Pelanggan',
			);
			echo json_encode($result);
		}
		//end notification
	}
	
	public function delete($id)
	{
		$this->News_m->delete($id);
		$result = array(
			'status' => true,
			'message' => $id.' Dihapus',
		);
		echo json_encode($result);
	}
	
	public function list_preview()
	{
		$this->load->model('Base_m');
		$list_status = $this->Base_m->get_status();
		$result = array(
			'list_status' => $list_status, 
		);
		$this->show('news_preview_list',$result,TRUE);
	}
	
	public function get_list_preview()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->News_m->get_list_preview($param),
			"recordsTotal" => $this->News_m->get_total_list_preview_unfiltered($param),
			"recordsFiltered" => $this->News_m->get_total_list_preview_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_preview($id)
	{
		$preview = $this->News_m->get_news_preview($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Pratinjau',
				'data' => $preview,
			);
		echo json_encode($result);
	}
	
	public function change_status_preview($id)
	{
		$status_id = $this->input->post('status_id');
		$this->News_m->update_status_preview($id,$status_id);
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function post_preview($id = null)
	{
		$param = array(
			'order' => $this->input->post('order'),
			'news_id' => $this->input->post('news_id'),
		);
		
		if($id == NULL)
		{
			$this->News_m->add_preview($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Pratinjau',
			);
			echo json_encode($result);
		}else
		{
			$this->News_m->edit_preview($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Pratinjau',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_preview($id)
	{
		$this->News_m->delete_preview($id);
		$result = array(
			'status' => true,
			'message' => $id.' Dihapus',
		);
		echo json_encode($result);
	}
	
	public function get_news_select()
	{
		$search = $this->input->get('search');
		$page = $this->input->get('page');
		$current_page = 0;
		
		if($page)
		{
			$current_page = $page;
		}
			
			
		$param = array(
			'search' => $search,
			'limit' => array('start'=>$current_page * 30,'length'=>30),
		);
		
		$result = array(
			'items' => $this->News_m->get_list($param),
			"total_count" => $this->News_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	
}
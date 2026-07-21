<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class Partner extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('Partner_m');
		$this->load->helper('image_manipulation');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Partner",200);
	}
	
	public function register_post()
	{
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
	
		$id  = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		if($id)
		{
			$referal_code = $this->post('referal');
			$referal_id = null;
			if($referal_code)
				$referal_id = $this->Basic_m->get_account_id_by_referal_code($referal_code);
	
			$img_profile = "";
			$img_identity = "";
			$img_driver_licence = "";
			$img_bussiness_licence = "";
			$img_bussiness_registration = "";
			if($_FILES["img_profile"]['name'] != null)
			{
				$config['upload_path'] = FCPATH . 'data/partners/profile';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'profileupload');
			
				if ($this->profileupload->do_upload('img_profile')) {
					$img_profile = $this->profileupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/profile/'.$img_profile, FCPATH . 'data/partners/profile/thumb_rentone_'.$img_profile, 250);
					
					$img_profile = resize_image(FCPATH . 'data/partners/profile/'.$img_profile, FCPATH . 'data/partners/profile/rentone_'.$img_profile, 600, 1, TRUE);
				}else
					$upload_log .= "profile:".$this->profileupload->display_errors()."\n";
			}
			
			if($_FILES["img_identity"]['name'] != null)
			{
				$config['upload_path'] = FCPATH . 'data/partners/files/identity';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'identityupload');
			
				if ($this->identityupload->do_upload('img_identity')) {
					$img_identity = $this->identityupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/files/identity/'.$img_identity, FCPATH . 'data/partners/files/identity/thumb_rentone_'.$img_identity, 250);
					
					$img_identity = resize_image(FCPATH . 'data/partners/files/identity/'.$img_identity, FCPATH . 'data/partners/files/identity/rentone_'.$img_identity, 600, 1, TRUE);
				}else
					$upload_log .= "Identity:".$this->identityupload->display_errors()."\n";
			}
			
			if($_FILES["img_driver_licence"]['name'] != null)
			{
				$config['upload_path'] = FCPATH . 'data/partners/files/driver_licence';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'driverlicenceupload');
			
				if ($this->driverlicenceupload->do_upload('img_driver_licence')) {
					$img_driver_licence = $this->driverlicenceupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/files/driver_licence/'.$img_driver_licence, FCPATH . 'data/partners/files/driver_licence/thumb_rentone_'.$img_driver_licence, 250);
					
					$img_driver_licence = resize_image(FCPATH . 'data/partners/files/driver_licence/'.$img_driver_licence, FCPATH . 'data/partners/files/driver_licence/rentone_'.$img_driver_licence, 600, 1, TRUE);
				}else
					$upload_log .= "Identity:".$this->driverlicenceupload->display_errors()."\n";
			}
			
			if($_FILES["img_bussiness_licence"]['name'] != null)
			{
				$config['upload_path'] = FCPATH . 'data/partners/files/bussiness_licence';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'bussinesslicenceupload');
			
				if ($this->bussinesslicenceupload->do_upload('img_bussiness_licence')) {
					$img_bussiness_licence = $this->bussinesslicenceupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/files/bussiness_licence/'.$img_bussiness_licence, FCPATH . 'data/partners/files/bussiness_licence/thumb_rentone_'.$img_bussiness_licence, 250);
					
					$img_bussiness_licence = resize_image(FCPATH . 'data/partners/files/bussiness_licence/'.$img_bussiness_licence, FCPATH . 'data/partners/files/bussiness_licence/rentone_'.$img_bussiness_licence, 600, 1, TRUE);
				}else
					$upload_log .= "Bussiness_licence:".$this->bussinesslicenceupload->display_errors()."\n";
			}
			
			if($_FILES["img_bussiness_registration"]['name'] != null)
			{
				$config['upload_path'] = FCPATH . 'data/partners/files/bussiness_registration';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'bussinessregistrationupload');
			
				if ($this->bussinessregistrationupload->do_upload('img_bussiness_registration')) {
					$img_bussiness_registration = $this->bussinessregistrationupload->data("file_name");
					
					$config['image_library']='gd2';
                    $config['source_image']= FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration;
                    $config['create_thumb']= FALSE;
                    $config['master_dim'] = 'width';
                    $config['maintain_ratio']= TRUE;
                    //$config['quality']= '50%';
                    $config['width']= 600;
                    $config['height']= 1;
                    $config['new_image']= FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration;
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
					
					thumb_image(FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration, FCPATH . 'data/partners/files/bussiness_registration/thumb_rentone_'.$img_bussiness_registration, 250);
					
					$img_bussiness_registration = resize_image(FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration, FCPATH . 'data/partners/files/bussiness_registration/rentone_'.$img_bussiness_registration, 600, 1, TRUE);
				}else
					$upload_log .= "Bussiness_registration:".$this->bussinessregistrationupload->display_errors()."\n";
			}
			
			$partner_data = array(
				'account_id' => $id,
				'ownership_id' => $this->post('ownership_id'),
				'img_profile' => $img_profile,
				'company_name' => $this->post('company_name'),
				'regencies_id' => $this->post('regencies_id'),
				'address' => $this->post('address'),
				'latitude' => $this->post('latitude'),
				'longitude' => $this->post('longitude'),
				'description' => $this->post('description'),
				'tax_number' => $this->post('tax_number'),
				'agent_id' => $this->post('agent_id'),
				'referal_id' => $referal_id,
			);
			
			$this->Partner_m->register($partner_data);
			
			$partner_file = array(
				'account_id' => $id,
				'img_identity' => $img_identity,
				'img_driver_licence' => $img_driver_licence,
				'img_bussiness_licence' => $img_bussiness_licence,
				'img_bussiness_registration' => $img_bussiness_registration,
			);
			
			$this->Partner_m->insert_partner_file($partner_file);
			
			$keyinfo = array(
				'account_id' => $id,
			);
			$key = $this->_generate_key();
			$this->_insert_key($key,$keyinfo);
			
			$this->ion_auth->add_to_group(4, $id);
			
			//notification
			$this->load->library('Fcm');
			$this->load->config('fcm');
			$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
			
			//kirim ke semua admin
			$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
			$data_payload = array(
				'data_type' => 'partner_register',
				'id' => $id,
				'link_action' => base_url().'admin/partner/list_register_request'
			);
			$this->fcm->setData($data_payload);
			$notif = array("title" => "Registrasi Mitra", "body" => "Registrasi mitra baru dengan id akun #".$id."",'android_channel_id' => 3, 'sound' => 'default');
			$this->fcm->setNotification($notif);
			$this->fcm->send();
			
			//kirim ke pelanggan
			$this->fcm->clearRecepients();// bersihkan token
			$this->fcm->addRecepient($this->Customer_m->get_token($id));
			$data_payload = array(
				'data_type' => 'partner_register',
				'id' => $id,
			);
			$this->fcm->setData($data_payload);
			$notif = array("title" => "Registrasi Mitra", "text" => "Registrasi berhasil. Permintaan akan diproses dalam waktu dekat",'android_channel_id' => 3, 'sound' => 'default');
			$this->fcm->setNotification($notif);
			$this->fcm->send();
			//end notification
			
			$response = array(
				'status' => true,
				'message' => "Berhasil Registrasi",
				'error' => $upload_log
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => false,
				'message' => "Gagal Registrasi! Cek kembali form registrasi."
			);
			$this->response($response,200);
		}
		
	}
	
	public function resubmit_register_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$this->Partner_m->delete($account_id);
		
		$response = array(
			'status' => true,
			'message' => "Sekarang anda dapat melakukan registrasi ulang"
		);
		$this->response($response,200);
	}
	
	public function status_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$this->Partner_m->get_status($account_id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil"
		);
		$this->response($response,200);
	}
	
	public function detail_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$response = array( 
			'status' => true,
			'message' => 'Berhasil',
			'partner' => $this->Partner_m->detail($account_id),
			'features' => $this->Partner_m->list_feature_pair($account_id),
		);
		$this->response($response,200);
	}
	
	public function upload_profile_image_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$img_profile = "";
		if($_FILES["img_profile"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/partners/profile';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'profileupload');
		
			if ($this->profileupload->do_upload('img_profile')) {
				$img_profile = $this->profileupload->data("file_name");
				
				thumb_image(FCPATH . 'data/partners/profile/'.$img_profile, FCPATH . 'data/partners/profile/thumb_rentone_'.$img_profile, 250);
					
				$img_profile = resize_image(FCPATH . 'data/partners/profile/'.$img_profile, FCPATH . 'data/partners/profile/rentone_'.$img_profile, 600, 1, TRUE);
			}else
				$upload_log .= "profile:".$this->profileupload->display_errors()."\n";
		}
		
		$this->Partner_m->update_profile_image($account_id,$img_profile);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah foto profil pelanggan",
		);
		$this->response($response,200);
		
	}
	
	public function change_company_name_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'company_name' => $this->post('company_name'),
		);
		$this->Partner_m->update_partner($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah nama perusahaan",
		);
		$this->response($response,200);
	}
	
	public function change_description_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'description' => $this->post('description'),
		);
		$this->Partner_m->update_partner($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah deskripsi",
		);
		$this->response($response,200);
	}
	
	public function change_address_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'address' => $this->post('address'),
		);
		$this->Partner_m->update_partner($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah deskripsi",
		);
		$this->response($response,200);
	}
	
	public function change_regency_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'regencies_id' => $this->post('regencies_id'),
		);
		$this->Partner_m->update_partner($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah kawasan",
		);
		$this->response($response,200);
	}
	
	public function change_bussiness_location_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'latitude' => $this->post('latitude'),
			'longitude' => $this->post('longitude'),
		);
		$this->Partner_m->update_partner($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah lokasi bisnis",
		);
		$this->response($response,200);
	}
	
	public function request_feature_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'feature_id' => $this->post('feature_id'),
			'status' => 2,
		);
		
		$status = $this->Partner_m->request_feature($account_id,$data);
		
		if($status)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil mengirim permintaan layanan. Permintaan akan diproses dalam waktu dekat.",
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Gagal mengirim permintaan layanan. Hubungi administator jika terjadi masalah",
			);
			$this->response($response,200);
		}
	}
	
}
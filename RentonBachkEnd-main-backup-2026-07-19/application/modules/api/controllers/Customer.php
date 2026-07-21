<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class Customer extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('Customer_m');
		$this->load->helper('image_manipulation');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Customer",200);
	}
	
	public function register_post()
	{
		$this->load->model('Basic_m');
		$email = $this->post('email');
		$password = $this->post('password');
		$password = $this->post('password');
		$additional_data = [
				'first_name' => $this->post('first_name'),
				'last_name' => $this->post('last_name'),
				'phone' => $this->post('phone'),
			];
		$result = $this->ion_auth->register($email, $password, $email, $additional_data,array('5'));
		
		$this->load->config('ion_auth');
		$use_email = $this->config->item('email_activation', 'ion_auth');
		$manual_activation = $this->config->item('manual_activation', 'ion_auth');
		
		if($use_email)
			$id  = $result['id'];
		else 
			$id = $result;
		
		if($id)
		{
			$referal_code = $this->post('referal');
			$referal_id = null;
			if($referal_code)
				$referal_id = $this->Basic_m->get_account_id_by_referal_code($referal_code);
	
			$img_profile = "";
			$img_identity = "";
		
			if($_FILES["img_profile"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/customers/profile';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'profileupload');
			
				if ($this->profileupload->do_upload('img_profile')) {
					$img_profile = $this->profileupload->data("file_name");
					
					thumb_image(FCPATH . 'data/customers/profile/'.$img_profile, FCPATH . 'data/customers/profile/thumb_rentone_'.$img_profile, 250);
					
					$img_profile = resize_image(FCPATH . 'data/customers/profile/'.$img_profile, FCPATH . 'data/customers/profile/rentone_'.$img_profile, 600, 1, TRUE);
				}else
					$upload_log .= "profile:".$this->profileupload->display_errors()."\n";
			}
			
			if($_FILES["img_identity"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/customers/files/identity';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'identityupload');
			
				if ($this->identityupload->do_upload('img_identity')) {
					$img_identity = $this->identityupload->data("file_name");
					
					thumb_image(FCPATH . 'data/customers/files/identity/'.$img_identity , FCPATH . 'data/customers/files/identity/thumb_rentone_'.$img_identity, 250);
					
					$img_identity = resize_image(FCPATH . 'data/customers/files/identity/'.$img_identity, FCPATH . 'data/customers/files/identity/rentone_'.$img_identity, 600, 1, TRUE);
				}else
					$upload_log .= "Identity:".$this->identityupload->display_errors()."\n";
			}
			
			$customer_data = array(
				'account_id' => $id,
				'identity_number' => $this->post('identity_number'),
				'img_profile' => $img_profile,
				'referal_id' => $referal_id,
			);
			
			$this->Customer_m->register($customer_data);
			
			$customer_file = array(
				'account_id' => $id,
				'img_identity' => $img_identity,
			);
			
			$this->Customer_m->insert_customer_file($customer_file);
			
			$keyinfo = array(
				'account_id' => $id,
			);
			$key = $this->_generate_key();
			$this->_insert_key($key,$keyinfo);
			
			if($use_email)
			{
				//send email activation 
				error_reporting(0); // hilangkan error php karena jika konfigurasi email gagal, maka output rest api ini jadi brantakan, dan menghasilkan error di response
				$this->load->config('mail');
				$mail_config = $this->config->item('mail_setting');
			
				$this->load->library('email');
				$this->email->initialize($mail_config);
				$this->email->set_newline("\r\n"); 
				$this->email->from($this->config->item('mail_email'), $this->config->item('mail_name')); 
				$this->email->to($email);
				$this->email->subject('Aktifasi Akun RentOne'); 
				
				$message = $this->load->view('email_template', $result, true);
				$this->email->message($message); 
				$this->email->set_mailtype("html");
				//Send mail 
				$this->email->send();
			}
			
			if($manual_activation == FALSE && $referal_id != null)
			{
				$detail = $this->Customer_m->detail($id);
		
				if($detail)
				{
					if($detail->referal_id)
					{
						$data = array(
							'account_id' => $detail->referal_id,
							'target_id' => $id,
							'point_debit' => $this->Basic_m->get_config_value('referal_point_reward_customer'),
							'description' => 'Poin Referal',
						);
						$status = $this->Basic_m->insert_point_reward($data);
					}
				}
			}
			$response = array(
				'status' => true,
				'message' => "Berhasil Registrasi",
				'error' => $id
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => false,
				'message' => "Gagal Registrasi! Cek kembali form registrasi.",
				"error" => $result,
			);
			$this->response($response,200);
		}
		
	}
	
	public function login_post()
	{
		$this->load->library('ion_auth');
		$this->lang->load('auth');
		$this->load->helper(['url','language']);
		
		$email = $this->input->post('email');
        $password = $this->input->post('password');
        
		$result = $this->ion_auth->login($email, $password, FALSE);
		
		if($result)
		{
			$id = $this->ion_auth->get_user_id();
			if($this->Customer_m->check_account_valid($id))
			{	
				$key = $this->Customer_m->get_key($id);
				$response = array(
					'status' => true,
					'message' => "Sukses Login",
					'id' => $id,
					'key' => $key,
				);
				
				$this->response($response,200);
			}else
			{
					$response = array(
					'status' => false,
					'message' => "Akun ini tidak bisa login disini! Gunakan akun lain atau buat baru.",
				);
				$this->response($response,200);
			}
			
		}else
		{	
			$errors = $this->ion_auth->errors_array(false);
			
			if(in_array("login_unsuccessful_not_active",$errors))
			{
				$this->load->config('ion_auth');
				$use_email = $this->config->item('email_activation', 'ion_auth');
				
				
				$response = array(
					'status' => false,
					'message' => (($use_email)?"Akun belum aktif, cek email untuk mendapatkan link aktifasi.":"Akun Belum Aktif, akun masih dalam proses review"),
				);
				
				$this->response($response,200);
			}else
			{
				$response = array(
					'status' => false,
					'message' => "Gagal Login. Cek kembali email dan password.",
				);
				
				$this->response($response,200);
			}
		}
	}
	
	public function detail_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$response = array( 
			'status' => true,
			'message' => 'Berhasil',
			'customer' => $this->Customer_m->detail($account_id),
			'balance' => $this->Customer_m->balance($account_id),
			'bank_total' => $this->Customer_m->bank_total($account_id)
		);
		$this->response($response,200);
	}
	
	public function banks_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$response = array( 
			'status' => true,
			'message' => 'Berhasil',
			'banks' => $this->Customer_m->banks($account_id),
		);
		$this->response($response,200);
	}
	
	public function post_bank_post()
	{
		$data = array(
			'bank_id' => $this->post("bank_id"),
			'name' => $this->post("name"),
			'bank_number' => $this->post("bank_number"),
		);
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$item_id = $this->post('id');
		
		$id = null;
		if($item_id)
			$id = $this->Customer_m->update_bank($item_id,$data);
		else
			$id = $this->Customer_m->add_bank($account_id,$data);	
		
		if($item_id)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil Mengubah Bank",
			);
			$this->response($response,200);
		}else
		{
			$response = array(
			'status' => true,
			'message' => "Berhasil Menambahkan Bank",
		);
		$this->response($response,200);
		}
	}
	
	public function bank_detail_post()
	{
		$id = $this->post('id');
		$bank = $this->Customer_m->bank_detail($id);

		$response = array(
				'status' => true,
				'message' => "Berhasil",
				'bank' => $bank,
			);
		$this->response($response,200);
	}
	
	public function get_input_bank_config_post()
	{
		$this->load->model('Basic_m');
		
		$banks = $this->Basic_m->get_banks();
		$response = array(
			'status' => true,
			'message' => 'Sukses',
			'banks' => $banks,
		);
		$this->response($response,200);
	}
	
	public function delete_bank_post()
	{
		$id = $this->post('id');
		$this->Customer_m->delete_bank($id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",

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
			$config['upload_path'] = FCPATH . 'data/customers/profile';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'profileupload');
		
			if ($this->profileupload->do_upload('img_profile')) {
				$img_profile = $this->profileupload->data("file_name");
				
				thumb_image(FCPATH . 'data/customers/profile/'.$img_profile, FCPATH . 'data/customers/profile/thumb_rentone_'.$img_profile, 250);
					
				$img_profile = resize_image(FCPATH . 'data/customers/profile/'.$img_profile, FCPATH . 'data/customers/profile/rentone_'.$img_profile, 600, 1, TRUE);
				
			}else
				$upload_log .= "profile:".$this->profileupload->display_errors()."\n";
		}
		
		$this->Customer_m->update_profile_image($account_id,$img_profile);
		
		$response = array(
			'status' => true,
			'config' => $config['upload_path'],
			'message' => "Berhasil mengubah foto profil pelanggan",
		);
		$this->response($response,200);
		
	}
	
	public function change_name_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'first_name' => $this->post('first_name'),
			'last_name' => $this->post('last_name'),
		);
		$this->Customer_m->change_name($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah nama",
		);
		$this->response($response,200);
		
	}
	
	public function change_password_post()
	{
		$this->load->library('ion_auth');
		$this->lang->load('auth');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$identity = $this->Customer_m->detail($account_id)->email;
		$change = $this->ion_auth->change_password($identity, $this->post('old_password'), $this->post('new_password'));
		
		if($change)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil mengubah kata sandi",
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => false,
				'message' => "Gagal mengubah kata sandi. Periksa kembali kata sandi lama",
			);
			$this->response($response,200);
		}
	}
	
	public function home_post()
	{
		$this->load->model('RentVehicle_m');
		$this->load->model('News_m');
		$account_id = null;
		if($this->input->get_request_header("key"))
		{
			$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
			$balance = $this->Customer_m->balance($account_id);
			$referal_code = $this->Customer_m->referal_code($account_id);
		}
		
		$vehicles_recomendation = $this->RentVehicle_m->vehicles_recomendation($account_id);
		$promote_vehicles_recomendation = $this->RentVehicle_m->promote_vehicles_recomendation($account_id);
		$news_preview = $this->News_m->list_preview();
		$response = array(
			'tes' => $account_id,
			'status' => true,
			'message' => "Berhasil",
			'balance' => $balance,
			'referal_code' => $referal_code,
			'vehicles_recomendation' => $vehicles_recomendation,
			'promote_vehicles_recomendation' => $promote_vehicles_recomendation,
			'news_preview' => $news_preview,
		);
		$this->response($response,200);
	}
	
	public function activate_get()
	{
		$id = $this->get('id');
		$code = $this->get('code');
		$this->load->library('ion_auth');
		$this->lang->load('auth');
		$this->load->helper(['url','language']);
		$this->load->model('Basic_m');
		$activation = FALSE;

		if ($code)
		{
			$activation = $this->ion_auth->activate($id, $code);
		}

		if ($activation)
		{			
			$detail = $this->Customer_m->detail($id);
		
			if($detail)
			{
				if($detail->referal_id)
				{
					$data = array(
						'account_id' => $detail->referal_id,
						'target_id' => $id,
						'point_debit' => $this->Basic_m->get_config_value('referal_point_reward_customer'),
						'description' => 'Poin Referal',
					);
					$status = $this->Basic_m->insert_point_reward($data);
				}
			}
			echo $this->ion_auth->messages();
		}
		else
		{
			// redirect them to the forgot password page
			echo $this->ion_auth->errors();
		}
	}
	
	public function status_post()
	{
		$this->load->model('Basic_m');
		$this->load->model('Partner_m');
		$this->load->model('Chat_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$customer_status = $this->Customer_m->get_status($account_id);
		$partner_status = $this->Partner_m->get_status($account_id);
		$partner_chat_unread = $this->Chat_m->partner_chatroom_unread($account_id)->total_unread;
		$customer_chat_unread = $this->Chat_m->customer_chatroom_unread($account_id)->total_unread;
		$response = array(
			'status' => true,
			'message' => 'Berhasil',
			'notification' => 12,
			'partner_chat_unread' => $partner_chat_unread,
			'customer_chat_unread' => $customer_chat_unread,
			'customer_status' => $customer_status,
			'partner_status' => $partner_status,
			'maintenance' => $this->Basic_m->get_config('maintenance')->value,
			'android_app_version_code' => $this->Basic_m->get_config('android_app_version_code')->value,
			'android_app_version_name' => $this->Basic_m->get_config('android_app_version_name')->value,
		);
		
		if($partner_status == 1)
			$response['partner_features'] = $this->Partner_m->features($account_id);
		
		$this->response($response,200);
	}
	
	public function balance_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$balance = $this->Customer_m->balance($account_id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'balance' => $balance->balance,
		);
		$this->response($response,200);
	}
	
	public function get_request_topup_config_post()
	{
		$this->load->model('Basic_m');
		
		$response = array( 
			'status' => true,
			'message' => 'Berhasil',
			'topup_minimum' => $this->Basic_m->get_config('topup_minimum')->value,
			'banks' => $this->Basic_m->get_company_banks(),
		);
		$this->response($response,200);
	}
	
	public function post_request_topup_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$value_with_code = $this->Customer_m->get_unique_value_topup($this->post('value'));
		$data = array(
			'account_id' => $account_id,
			'company_bank_id' => $this->post('company_bank_id'),
			'value' => $this->post('value'),
			'value_with_code' => $value_with_code,
			'status' => 1,
		);
		$id = $this->Customer_m->add_request_topup($data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengirimkan permintaan topup",
			'topup_id' => $id,
		);
		$this->response($response,200);
	}
	
	public function topup_detail_post()
	{
		$topup_detail = $this->Customer_m->topup_detail($this->post('topup_id'));
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'detail' => $topup_detail,
		);
		$this->response($response,200);
	}
	
	public function verification_topup_post()
	{
		$this->load->model('Basic_m');
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$img_proof = null;
		if($_FILES["img_proof"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/customers/topup';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'prooftopupupload');
		
			if ($this->prooftopupupload->do_upload('img_proof')) {
				$img_proof = $this->prooftopupupload->data("file_name");
				
				thumb_image(FCPATH . 'data/customers/topup/'.$img_proof, FCPATH .'data/customers/topup/thumb_rentone_'.$img_proof, 250);
					
				$img_proof = resize_image(FCPATH . 'data/customers/topup/'.$img_proof, FCPATH . 'data/customers/topup/rentone_'.$img_proof, 600, 1, TRUE);
			}else
				$upload_log .= "prooftopup:".$this->prooftopupupload->display_errors()."\n";
		}
		
		$topup_id = $this->post('topup_id');
		$data = array(
			'status' => 2,
			'img_proof' => $img_proof,
		);
		$this->Customer_m->update_topup($topup_id,$data);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke semua admin
		$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
		$data_payload = array(
			'data_type' => 'customer_topup',
			'id' => $id,
			'link_action' => base_url().'admin/customer/list_topup'
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Permintaan Pengisian Dana", "body" => "ID #".$topup_id,'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		
		//kirim ke pelanggan
		$this->fcm->clearRecepients();// bersihkan token
		$this->fcm->addRecepient($this->Customer_m->get_token($account_id));
		$data_payload = array(
			'data_type' => 'partner_register',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Permintaan Pengisian Dana", "text" => "Pengisian dana sedang diproses.",'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
			
		$response = array(
			'status' => true,
			'message' => "Berhasil mengirim bukti verifikasi topup",
		);
		$this->response($response,200);
	}
	
	public function list_topup_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
		);
		$result = $this->Customer_m->list_topup($account_id,$param);
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'topups' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'topups' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function list_withdraw_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
		);
		$result = $this->Customer_m->list_withdraw($account_id,$param);
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'withdraws' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'withdraws' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function get_request_withdraw_config_post()
	{
		$this->load->model('Basic_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$response = array( 
			'status' => true,
			'message' => 'Berhasil',
			'withdraw_minimum' => $this->Basic_m->get_config('withdraw_minimum')->value,
			'banks' => $this->Customer_m->banks($account_id),
		);
		$this->response($response,200);
	}
	
	public function post_request_withdraw_post()
	{
		$this->load->model('Basic_m');
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$balance = $this->Customer_m->balance($account_id)->balance;
		$value = $this->post('value');
		
		if($balance >= $value)
		{
			$data = array(
				'account_id' => $account_id,
				'account_bank_id' => $this->post('account_bank_id'),
				'value' => $value,
				'status' => 1,
			);
			$this->Customer_m->add_request_withdraw($data);
			
			//notification
			$this->load->library('Fcm');
			$this->load->config('fcm');
			$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
			
			//kirim ke semua admin
			$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
			$data_payload = array(
				'data_type' => 'customer_withdraw',
				'id' => $id,
				'link_action' => base_url().'admin/customer/list_withdraw'
			);
			$this->fcm->setData($data_payload);
			$notif = array("title" => "Permintaan Pencairan Dana", "body" => "Sebesar :".$value,'android_channel_id' => 3, 'sound' => 'default');
			$this->fcm->setNotification($notif);
			$this->fcm->send();
			
			//kirim ke pelanggan
			$this->fcm->clearRecepients();// bersihkan token
			$this->fcm->addRecepient($this->Customer_m->get_token($account_id));
			$data_payload = array(
				'data_type' => 'partner_register',
				'id' => $id,
			);
			$this->fcm->setData($data_payload);
			$notif = array("title" => "Permintaan Pencairan Dana", "text" => "Pencairan sebesar :".$value." sedang diproses.",'android_channel_id' => 3, 'sound' => 'default');
			$this->fcm->setNotification($notif);
			$this->fcm->send();
			//end notification
			
			$response = array(
				'status' => true,
				'message' => "Berhasil mengirimkan permintaan withdraw",
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Gagal mengirimkan permintaan withdraw. Saldo anda kurang dari permintaan pencairan",
			);
			$this->response($response,200);
		}
	}
	
	public function point_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$balance = $this->Customer_m->balance($account_id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'point' => $balance->point,
		);
		$this->response($response,200);
	}
	
	public function get_exchange_point_config_post()
	{
		$this->load->model('Basic_m');
		
		$response = array( 
			'status' => true,
			'message' => 'Berhasil',
			'exchange_point_minimum' => $this->Basic_m->get_config('exchange_point_minimum')->value,
			'rate_point_to_balance' => $this->Basic_m->get_config('rate_point_to_balance')->value,
		);
		$this->response($response,200);
	}
	
	public function post_exchange_point_post()
	{
		$this->load->model('Basic_m');
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$point = $this->Customer_m->balance($account_id)->point;
		$value = $this->post('point');
		
		if($point >= $value)
		{
			$data = array(
				'account_id' => $account_id,
				'point_credit' => $value,
				'description' => 'Penukaran Poin ke Saldo',
			);
			$this->Customer_m->exchange_point_to_balance($data,$this->Basic_m->get_config('rate_point_to_balance')->value);
			
			$response = array(
				'status' => true,
				'message' => "Berhasil menukar poin ke saldo",
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Gagal menukar poin ke saldo. Poin anda kurang dari permintaan penukaran",
			);
			$this->response($response,200);
		}
	}
	
	public function list_transaction_point_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
		);
		$result = $this->Customer_m->list_transaction_point($account_id,$param);
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'transaction_point' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'transaction_point' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function update_customer_location_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'latitude' => $this->post('latitude'),
			'longitude' => $this->post('longitude'),
		);
		$this->Customer_m->update_customer_location($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil memperbaharui lokasi terakhir",
		);
		$this->response($response,200);
	}
	
	public function update_token_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'token' => $this->post('token'),
		);
		$this->Customer_m->update_token($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil memperbaharui token",
		);
		$this->response($response,200);
	}
	
	
}
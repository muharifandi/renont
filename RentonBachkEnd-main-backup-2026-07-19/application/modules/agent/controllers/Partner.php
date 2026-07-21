<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Partner extends AgentController
{
	

	public function __construct()
	{
		parent::__construct();
		$this->load->model('agent/Partner_m');
		$this->load->helper('image_manipulation');
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function add()
	{
		//$data['map_key'] = 'AIzaSyDPS2h6Okr6y_hRHM_kZkH0GT_htUjUvJY';
		$this->show('partner_add',$data,TRUE);
	}
	
	public function edit($id)
	{
		$detail = $this->Partner_m->detail($id);
		if($detail)
		{
			$data['edit'] = 1;
			$data['partner'] = $detail;
			//$data['map_key'] = 'AIzaSyDPS2h6Okr6y_hRHM_kZkH0GT_htUjUvJY';
			$this->show('partner_add',$data,TRUE);
		}else
		{
			redirect("agent/partner/list", 'refresh');
		}
	}
	
	public function list()
	{
		$this->show('partner_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$agent_id = $this->get_user_id();
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Partner_m->get_list($agent_id,$param),
			"recordsTotal" => $this->Partner_m->get_total_list_unfiltered($agent_id,$param),
			"recordsFiltered" => $this->Partner_m->get_total_list_filtered($agent_id,$param),
		);
		echo json_encode($result);
	}
	
	public function add_partner()
	{
		$agent_id = $this->get_user_id();
		
		$this->load->model('Config_m');
		
		$this->lang->load('auth');
		$this->load->library('form_validation');
		$tables = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');
		$data['identity_column'] = $identity_column;

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
		}else if($this->input->post('img_profile_filename'))
		{
			$img_profile = $this->input->post('img_profile_filename');
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
			
		}else if($this->input->post('img_identity_filename'))
		{
			$img_identity = $this->input->post('img_identity_filename');
		}
		
		if($_FILES["img_profile_partner"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/partners/profile';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'partnerprofileupload');
		
			if ($this->partnerprofileupload->do_upload('img_profile_partner')) {
				$img_profile_partner = $this->partnerprofileupload->data("file_name");
				
				thumb_image(FCPATH . 'data/partners/profile/'.$img_profile_partner, FCPATH . 'data/partners/profile/thumb_rentone_'.$img_profile_partner, 250);
				
				$img_profile_partner = resize_image(FCPATH . 'data/partners/profile/'.$img_profile_partner, FCPATH . 'data/partners/profile/rentone_'.$img_profile_partner, 600, 1, TRUE);
			}else
				$upload_log .= "profile:".$this->partnerprofileupload->display_errors()."\n";
		}else if($this->input->post('img_profile_partner_filename'))
		{
			$img_profile_partner = $this->input->post('img_profile_partner_filename');
		}
		
		if($_FILES["img_driver_licence"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/partners/files/driver_licence';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'driverlicenceupload');
		
			if ($this->driverlicenceupload->do_upload('img_driver_licence')) {
				$img_driver_licence = $this->driverlicenceupload->data("file_name");
				
				thumb_image(FCPATH . 'data/partners/files/driver_licence/'.$img_driver_licence , FCPATH . 'data/partners/files/driver_licence/thumb_rentone_'.$img_driver_licence, 250);
				
				$img_driver_licence = resize_image(FCPATH . 'data/partners/files/driver_licence/'.$img_driver_licence, FCPATH . 'data/partners/files/driver_licence/rentone_'.$img_driver_licence, 600, 1, TRUE);
			}else
				$upload_log .= "Driver Licence:".$this->driverlicenceupload->display_errors()."\n";
			
		}else if($this->input->post('img_driver_licence_filename'))
		{
			$img_driver_licence = $this->input->post('img_driver_licence_filename');
		}
		
		if($_FILES["img_bussiness_licence"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/partners/files/bussiness_licence';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'bussinesslicenceupload');
		
			if ($this->bussinesslicenceupload->do_upload('img_bussiness_licence')) {
				$img_bussiness_licence = $this->bussinesslicenceupload->data("file_name");
				
				thumb_image(FCPATH . 'data/partners/files/bussiness_licence/'.$img_bussiness_licence , FCPATH . 'data/partners/files/bussiness_licence/thumb_rentone_'.$img_bussiness_licence, 250);
				
				$img_bussiness_licence = resize_image(FCPATH . 'data/partners/files/bussiness_licence/'.$img_bussiness_licence, FCPATH . 'data/partners/files/bussiness_licence/rentone_'.$img_bussiness_licence, 600, 1, TRUE);
			}else
				$upload_log .= "Driver Licence:".$this->bussinesslicenceupload->display_errors()."\n";
			
		}else if($this->input->post('img_bussiness_licence_filename'))
		{
			$img_bussiness_licence = $this->input->post('img_bussiness_licence_filename');
		}
		
		if($_FILES["img_bussiness_registration"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/partners/files/bussiness_registration';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'bussinessregistrationupload');
		
			if ($this->bussinessregistrationupload->do_upload('img_bussiness_registration')) {
				$img_bussiness_registration = $this->bussinessregistrationupload->data("file_name");
				
				thumb_image(FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration , FCPATH . 'data/partners/files/bussiness_registration/thumb_rentone_'.$img_bussiness_registration, 250);
				
				$img_bussiness_registration = resize_image(FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration, FCPATH . 'data/partners/files/bussiness_registration/rentone_'.$img_bussiness_registration, 600, 1, TRUE);
			}else
				$upload_log .= "Driver Licence:".$this->bussinessregistrationupload->display_errors()."\n";
			
		}else if($this->input->post('img_bussiness_registration_filename'))
		{
			$img_bussiness_registration = $this->input->post('img_bussiness_registration_filename');
		}
			
		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'trim|required');
		if ($identity_column !== 'email')
		{
			$this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'trim|required|is_unique[' . $tables['users'] . '.' . $identity_column . ']');
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email');
		}
		else
		{
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email|is_unique[' . $tables['users'] . '.email]');
		}
		$this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');
		
		$this->form_validation->set_rules('company_name', "Nama Perusahaan Wajib Diisi", 'required');
		$this->form_validation->set_rules('description', "Deskripsi Perusahaan Wajib Diisi", 'required');
		
		$this->form_validation->set_rules('regencies_id', "Lokasi Wajib Diisi", 'required');
		$this->form_validation->set_rules('identity_number', "No Identitas Wajib Diisi", 'required');

		if ($this->form_validation->run() === TRUE)
		{
			$email = strtolower($this->input->post('email'));
			$identity = ($identity_column === 'email') ? $email : $this->input->post('identity');
			$password = $this->input->post('password');

			$additional_data = [
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'phone' => $this->input->post('phone'),
			];
			
			$use_email = $this->config->item('email_activation', 'ion_auth');
		
			$result = $this->ion_auth->register($identity, $password, $email, $additional_data,array('5'));
			
			if($use_email)
				$id  = $result['id'];
			else 
				$id = $result;
			
		}
		
		
		if ($id)
		{
			$this->ion_auth->activate($id);
			$customer_data = array(
				'account_id' => $id,
				'identity_number' => $this->input->post('identity_number'),
			);
			if($img_profile)
				$customer_data['img_profile'] = $img_profile;
			
			$customer_file = array(
				'account_id' => $id,
			);
			
			if($img_identity)
				$customer_file['img_identity'] = $img_identity;
			
			$partner_data = array(
				'account_id' => $id,
				'ownership_id' => $this->input->post('ownership_id'),
				'company_name' => $this->input->post('company_name'),
				'regencies_id' => $this->input->post('regencies_id'),
				'address' => $this->input->post('address'),
				'latitude' => $this->input->post('latitude'),
				'longitude' => $this->input->post('longitude'),
				'description' => $this->input->post('description'),
				'tax_number' => $this->input->post('tax_number'),
				'agent_id' => $agent_id,
				'status' => 1,
			);
			
			if($img_profile_partner)
				$partner_data['img_profile'] = $img_profile_partner;
		
			$partner_file = array(
				'account_id' => $id,
			);
			
			if($img_driver_licence)
				$partner_file['img_driver_licence'] = $img_driver_licence;
			
			if($img_bussiness_licence)
				$partner_file['img_bussiness_licence'] = $img_bussiness_licence;
			
			if($img_bussiness_registration)
				$partner_file['img_bussiness_registration'] = $img_bussiness_registration;
			
			$this->Partner_m->register($customer_data,$customer_file,$partner_data, $partner_file);
			$this->ion_auth->add_to_group(4, $id);
			
			$this->load->library('MY_Api');
			
			$keyinfo = array(
				'account_id' => $id,
			);
			$key = $this->my_api->_generate_key();
			$this->my_api->_insert_key($key,$keyinfo);
			
			// check to see if we are creating the user
			// redirect them back to the admin page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("agent/partner/list", 'refresh');
		}
		else
		{
			// display the create user form
			// set the flash data error message if there is one
			$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
			
			$regencies = $this->Config_m->get_regencies_by_id($this->form_validation->set_value('regencies_id'));
			$partner = new stdClass();
			$partner->first_name = $this->form_validation->set_value('first_name');
			$partner->last_name = $this->form_validation->set_value('last_name');
			$partner->identity = $this->form_validation->set_value('identity');
			$partner->email = $this->form_validation->set_value('email');
			$partner->phone = $this->form_validation->set_value('phone');
			$partner->identity_number = $this->form_validation->set_value('identity_number');
			$partner->img_profile = $img_profile;
			$partner->img_identity = $img_identity;
			
			$partner->company_name = $this->form_validation->set_value('company_name');
			$partner->description = $this->form_validation->set_value('description');
			
			$partner->ownership_id = $this->input->post('ownership_id');
			$partner->tax_number = $this->input->post('tax_number');
			$partner->regencies_id = $this->form_validation->set_value('regencies_id');
			$partner->regencies_name = $regencies->name;
			$partner->address = $this->input->post('address');
			$partner->img_profile_partner = $img_profile_partner;
			
			$partner->latitude = $this->input->post('latitude');
			$partner->longitude = $this->input->post('longitude');
			
			$partner->img_driver_licence = $img_driver_licence;
			$partner->img_bussiness_licence = $img_bussiness_licence;
			$partner->img_bussiness_registration = $img_bussiness_registration;
			$data['partner'] = $partner;
			
			$this->show('partner_add',$data,TRUE);
		}
	}
	
	public function edit_partner()
	{
		$this->load->model('Config_m');
		$id = $this->input->post('id');
		
		if($id)
		{
			$this->lang->load('auth');
			$this->load->library('form_validation');
			$user = $this->ion_auth->user($id)->row();

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
			}else if($this->input->post('img_profile_filename'))
			{
				$img_profile = $this->input->post('img_profile_filename');
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
				
			}else if($this->input->post('img_identity_filename'))
			{
				$img_identity = $this->input->post('img_identity_filename');
			}
			
			if($_FILES["img_profile_partner"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/partners/profile';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'partnerprofileupload');
			
				if ($this->partnerprofileupload->do_upload('img_profile_partner')) {
					$img_profile_partner = $this->partnerprofileupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/profile/'.$img_profile_partner, FCPATH . 'data/partners/profile/thumb_rentone_'.$img_profile_partner, 250);
					
					$img_profile_partner = resize_image(FCPATH . 'data/partners/profile/'.$img_profile_partner, FCPATH . 'data/partners/profile/rentone_'.$img_profile_partner, 600, 1, TRUE);
				}else
					$upload_log .= "profile:".$this->partnerprofileupload->display_errors()."\n";
			}else if($this->input->post('img_profile_partner_filename'))
			{
				$img_profile_partner = $this->input->post('img_profile_partner_filename');
			}
			
			if($_FILES["img_driver_licence"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/partners/files/driver_licence';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'driverlicenceupload');
			
				if ($this->driverlicenceupload->do_upload('img_driver_licence')) {
					$img_driver_licence = $this->driverlicenceupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/files/driver_licence/'.$img_driver_licence , FCPATH . 'data/partners/files/driver_licence/thumb_rentone_'.$img_driver_licence, 250);
					
					$img_driver_licence = resize_image(FCPATH . 'data/partners/files/driver_licence/'.$img_driver_licence, FCPATH . 'data/partners/files/driver_licence/rentone_'.$img_driver_licence, 600, 1, TRUE);
				}else
					$upload_log .= "Driver Licence:".$this->driverlicenceupload->display_errors()."\n";
				
			}else if($this->input->post('img_driver_licence_filename'))
			{
				$img_driver_licence = $this->input->post('img_driver_licence_filename');
			}
			
			if($_FILES["img_bussiness_licence"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/partners/files/bussiness_licence';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'bussinesslicenceupload');
			
				if ($this->bussinesslicenceupload->do_upload('img_bussiness_licence')) {
					$img_bussiness_licence = $this->bussinesslicenceupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/files/bussiness_licence/'.$img_bussiness_licence , FCPATH . 'data/partners/files/bussiness_licence/thumb_rentone_'.$img_bussiness_licence, 250);
					
					$img_bussiness_licence = resize_image(FCPATH . 'data/partners/files/bussiness_licence/'.$img_bussiness_licence, FCPATH . 'data/partners/files/bussiness_licence/rentone_'.$img_bussiness_licence, 600, 1, TRUE);
				}else
					$upload_log .= "Driver Licence:".$this->bussinesslicenceupload->display_errors()."\n";
				
			}else if($this->input->post('img_bussiness_licence_filename'))
			{
				$img_bussiness_licence = $this->input->post('img_bussiness_licence_filename');
			}
			
			if($_FILES["img_bussiness_registration"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/partners/files/bussiness_registration';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'bussinessregistrationupload');
			
				if ($this->bussinessregistrationupload->do_upload('img_bussiness_registration')) {
					$img_bussiness_registration = $this->bussinessregistrationupload->data("file_name");
					
					thumb_image(FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration , FCPATH . 'data/partners/files/bussiness_registration/thumb_rentone_'.$img_bussiness_registration, 250);
					
					$img_bussiness_registration = resize_image(FCPATH . 'data/partners/files/bussiness_registration/'.$img_bussiness_registration, FCPATH . 'data/partners/files/bussiness_registration/rentone_'.$img_bussiness_registration, 600, 1, TRUE);
				}else
					$upload_log .= "Driver Licence:".$this->bussinessregistrationupload->display_errors()."\n";
				
			}else if($this->input->post('img_bussiness_registration_filename'))
			{
				$img_bussiness_registration = $this->input->post('img_bussiness_registration_filename');
			}

			// validate form input
			$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
			$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
			$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim');
		
			$this->form_validation->set_rules('company_name', "Nama Perusahaan Wajib Diisi", 'required');
			$this->form_validation->set_rules('description', "Deskripsi Perusahaan Wajib Diisi", 'required');
		
			$this->form_validation->set_rules('regencies_id', "Lokasi Wajib Diisi", 'required');
			$this->form_validation->set_rules('identity_number', "No Identitas Wajib Diisi", 'required');
			if (isset($_POST) && !empty($_POST))
			{
				

				// update the password if it was posted
				if ($this->input->post('password'))
				{
					$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
					$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
				}

				if ($this->form_validation->run() === TRUE)
				{
					$data = [
						'first_name' => $this->input->post('first_name'),
						'last_name' => $this->input->post('last_name'),
						'phone' => $this->input->post('phone'),
					];

					// update the password if it was posted
					if ($this->input->post('password'))
					{
						$data['password'] = $this->input->post('password');
					}

					// check to see if we are updating the user
					if ($this->ion_auth->update($user->id, $data))
					{
						$customer_data = array(
							'identity_number' => $this->input->post('identity_number'),
						);
						if($img_profile)
							$customer_data['img_profile'] = $img_profile;
						
						$customer_file = array();
						
						if($img_identity)
							$customer_file['img_identity'] = $img_identity;
						
						$partner_data = array(
							'ownership_id' => $this->input->post('ownership_id'),
							'company_name' => $this->input->post('company_name'),
							'regencies_id' => $this->input->post('regencies_id'),
							'address' => $this->input->post('address'),
							'latitude' => $this->input->post('latitude'),
							'longitude' => $this->input->post('longitude'),
							'description' => $this->input->post('description'),
							'tax_number' => $this->input->post('tax_number'),
						);
						
						if($img_profile_partner)
							$partner_data['img_profile'] = $img_profile_partner;
					
						$partner_file = array();
						
						if($img_driver_licence)
							$partner_file['img_driver_licence'] = $img_driver_licence;
						
						if($img_bussiness_licence)
							$partner_file['img_bussiness_licence'] = $img_bussiness_licence;
						
						if($img_bussiness_registration)
							$partner_file['img_bussiness_registration'] = $img_bussiness_registration;
						
						$this->Partner_m->update($id, $customer_data,$customer_file,$partner_data, $partner_file);
						// redirect them back to the admin page if admin, or to the base url if non admin
						$this->session->set_flashdata('message', $this->ion_auth->messages());
						redirect("agent/partner/list", 'refresh');

					}
					else
					{
						// redirect them back to the admin page if admin, or to the base url if non admin
						$this->session->set_flashdata('message', $this->ion_auth->errors());
						redirect("agent/partner/list", 'refresh');

					}

				}
			}


			// set the flash data error message if there is one
			$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			

			$regencies = $this->Config_m->get_regencies_by_id($this->form_validation->set_value('regencies_id'));
			$agent = new stdClass();
			$agent->first_name = $this->form_validation->set_value('first_name');
			$agent->last_name = $this->form_validation->set_value('last_name');
			$agent->identity = $this->form_validation->set_value('identity');
			$agent->email = $this->input->post('email');
			$agent->phone = $this->form_validation->set_value('phone');
			$agent->regencies_id = $this->form_validation->set_value('regencies_id');
			$agent->regencies_name = $regencies->name;
			$agent->address = $this->input->post('address');
			$agent->identity_number = $this->form_validation->set_value('identity_number');
			$agent->img_profile = $img_profile;
			$agent->img_identity = $img_identity;

			$data['agent'] = $agent;
			$data['edit'] = 1;
			$this->show('agent_add',$data,TRUE);
		}else
		{
			redirect("admin/agent/list", 'refresh');
		}
	}
	
	public function list_commission()
	{
		$this->show('commission_list',$result,TRUE);
	}
	
	public function get_list_commission()
	{
		$agent_id = $this->get_user_id();
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Partner_m->get_list_commission($agent_id,$param),
			"recordsTotal" => $this->Partner_m->get_total_list_commission_unfiltered($agent_id,$param),
			"recordsFiltered" => $this->Partner_m->get_total_list_commission_filtered($agent_id,$param),
		);
		echo json_encode($result);
	}
	
	public function list_transaction()
	{
		$this->show('partner_transaction_list',$result,TRUE);
	}
	
	public function get_list_transaction()
	{
		$agent_id = $this->get_user_id();
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Partner_m->get_list_transaction($agent_id,$param),
			"recordsTotal" => $this->Partner_m->get_total_list_transaction_unfiltered($agent_id,$param),
			"recordsFiltered" => $this->Partner_m->get_total_list_transaction_filtered($agent_id,$param),
		);
		echo json_encode($result);
	}
}
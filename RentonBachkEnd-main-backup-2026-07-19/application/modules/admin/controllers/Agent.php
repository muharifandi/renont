<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Agent extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('Agent_m');
		$this->load->helper('image_manipulation');
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	
	public function get_agents_select()
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
			'items' => $this->Agent_m->get_list($param),
			"total_count" => $this->Agent_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function add()
	{
		$this->show('agent_add',$data,TRUE);
	}
	
	public function edit($id)
	{
		$detail = $this->Agent_m->detail($id);
		if($detail)
		{
			$data['edit'] = 1;
			$data['agent'] = $detail;
			$this->show('agent_add',$data,TRUE);
		}else
		{
			redirect("admin/agent/list", 'refresh');
		}
	}
	
	public function list()
	{
		$list_active_status = $this->Agent_m->get_active_status();
		$result = array(
			'list_active_status' => $list_active_status, 
		);
		$this->show('agent_list',$result,TRUE);
	}
	
	public function get_list()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Agent_m->get_list($param),
			"recordsTotal" => $this->Agent_m->get_total_list_unfiltered($param),
			"recordsFiltered" => $this->Agent_m->get_total_list_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function change_status($id)
	{
		$status_id = $this->input->post('status_id');
		$this->Agent_m->update_active_status($id,$status_id);
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function delete($id)
	{
		$this->Agent_m->delete($id);
		$this->ion_auth->remove_from_group(7, $id);
		$result = array(
			'status' => true,
			'message' => $id.' Dihapus',
		);
		echo json_encode($result);
	}
	
	public function add_agent()
	{
		$this->load->model('Config_m');
		
		$this->lang->load('auth');
		$this->load->library('form_validation');
		$tables = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');
		$data['identity_column'] = $identity_column;

		if($_FILES["img_profile"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/agents/profile';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'profileupload');
		
			if ($this->profileupload->do_upload('img_profile')) {
				$img_profile = $this->profileupload->data("file_name");
				
				thumb_image(FCPATH . 'data/agents/profile/'.$img_profile, FCPATH . 'data/agents/profile/thumb_rentone_'.$img_profile, 250);
				
				$img_profile = resize_image(FCPATH . 'data/agents/profile/'.$img_profile, FCPATH . 'data/agents/profile/rentone_'.$img_profile, 600, 1, TRUE);
			}else
				$upload_log .= "profile:".$this->profileupload->display_errors()."\n";
		}else if($this->input->post('img_profile_filename'))
		{
			$img_profile = $this->input->post('img_profile_filename');
		}
		
		if($_FILES["img_identity"]['name'] != null)
		{
			//$config['file_name'] = $id;
			$config['upload_path'] = FCPATH . 'data/agents/files/identity';
			$config['allowed_types'] = '*';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config,'identityupload');
		
			if ($this->identityupload->do_upload('img_identity')) {
				$img_identity = $this->identityupload->data("file_name");
				
				thumb_image(FCPATH . 'data/agents/files/identity/'.$img_identity , FCPATH . 'data/agents/files/identity/thumb_rentone_'.$img_identity, 250);
				
				$img_identity = resize_image(FCPATH . 'data/agents/files/identity/'.$img_identity, FCPATH . 'data/agents/files/identity/rentone_'.$img_identity, 600, 1, TRUE);
			}else
				$upload_log .= "Identity:".$this->identityupload->display_errors()."\n";
		}else if($this->input->post('img_identity_filename'))
		{
			$img_identity = $this->input->post('img_identity_filename');
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
		
			$result = $this->ion_auth->register($identity, $password, $email, $additional_data,array('7'));
			
			if($use_email)
				$id  = $result['id'];
			else 
				$id = $result;
			
		}
		
		
		if ($id)
		{
			
			
			$agent_data = array(
				'account_id' => $id,
				'identity_number' => $this->input->post('identity_number'),
				'regencies_id' => $this->input->post('regencies_id'),
				'address' => $this->input->post('address'),
			);
			
			if($img_profile)
				$agent_data['img_profile'] = $img_profile;
		
			$this->Agent_m->register($agent_data);
			
			
			$agent_file = array(
				'account_id' => $id,
			);
			
			if($img_identity)
				$agent_file['img_identity'] = $img_identity;
			
			$this->Agent_m->insert_agent_file($agent_file);
			
			// check to see if we are creating the user
			// redirect them back to the admin page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("admin/agent/list", 'refresh');
		}
		else
		{
			// display the create user form
			// set the flash data error message if there is one
			$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			$data['first_name'] = [
				'name' => 'first_name',
				'id' => 'first_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('first_name'),
			];
			$data['last_name'] = [
				'name' => 'last_name',
				'id' => 'last_name',
				'type' => 'text',
				'value' => $this->form_validation->set_value('last_name'),
			];
			$data['identity'] = [
				'name' => 'identity',
				'id' => 'identity',
				'type' => 'text',
				'value' => $this->form_validation->set_value('identity'),
			];
			$data['email'] = [
				'name' => 'email',
				'id' => 'email',
				'type' => 'text',
				'value' => $this->form_validation->set_value('email'),
			];
			$data['phone'] = [
				'name' => 'phone',
				'id' => 'phone',
				'type' => 'text',
				'value' => $this->form_validation->set_value('phone'),
			];
			$data['password'] = [
				'name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password'),
			];
			$data['password_confirm'] = [
				'name' => 'password_confirm',
				'id' => 'password_confirm',
				'type' => 'password',
				'value' => $this->form_validation->set_value('password_confirm'),
			];
			
			
			$regencies = $this->Config_m->get_regencies_by_id($this->form_validation->set_value('regencies_id'));
			$agent = new stdClass();
			$agent->first_name = $this->form_validation->set_value('first_name');
			$agent->last_name = $this->form_validation->set_value('last_name');
			$agent->identity = $this->form_validation->set_value('identity');
			$agent->email = $this->form_validation->set_value('email');
			$agent->phone = $this->form_validation->set_value('phone');
			$agent->regencies_id = $this->form_validation->set_value('regencies_id');
			$agent->regencies_name = $regencies->name;
			$agent->address = $this->input->post('address');
			$agent->identity_number = $this->form_validation->set_value('identity_number');
			$agent->img_profile = $img_profile;
			$agent->img_identity = $img_identity;

			$data['agent'] = $agent;
			
			$this->show('agent_add',$data,TRUE);
		}
	}
	
	public function edit_agent()
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
				$config['upload_path'] = FCPATH . 'data/agents/profile';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'profileupload');
			
				if ($this->profileupload->do_upload('img_profile')) {
					$img_profile = $this->profileupload->data("file_name");
					
					thumb_image(FCPATH . 'data/agents/profile/'.$img_profile, FCPATH . 'data/agents/profile/thumb_rentone_'.$img_profile, 250);
					
					$img_profile = resize_image(FCPATH . 'data/agents/profile/'.$img_profile, FCPATH . 'data/agents/profile/rentone_'.$img_profile, 600, 1, TRUE);
				}else
					$upload_log .= "profile:".$this->profileupload->display_errors()."\n";
			}else if($this->input->post('img_profile_filename'))
			{
				$img_profile = $this->input->post('img_profile_filename');
			}
			
			if($_FILES["img_identity"]['name'] != null)
			{
				//$config['file_name'] = $id;
				$config['upload_path'] = FCPATH . 'data/agents/files/identity';
				$config['allowed_types'] = '*';
				$config['max_size'] = '20480';
				$config['overwrite'] = false;
				$this->load->library('upload', $config,'identityupload');
			
				if ($this->identityupload->do_upload('img_identity')) {
					$img_identity = $this->identityupload->data("file_name");
					
					thumb_image(FCPATH . 'data/agents/files/identity/'.$img_identity , FCPATH . 'data/agents/files/identity/thumb_rentone_'.$img_identity, 250);
					
					$img_identity = resize_image(FCPATH . 'data/agents/files/identity/'.$img_identity, FCPATH . 'data/agents/files/identity/rentone_'.$img_identity, 600, 1, TRUE);
				}else
					$upload_log .= "Identity:".$this->identityupload->display_errors()."\n";
			}else if($this->input->post('img_identity_filename'))
			{
				$img_identity = $this->input->post('img_identity_filename');
			}

			// validate form input
			$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
			$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
			$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim');
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
						$agent_data = array(
							'identity_number' => $this->input->post('identity_number'),
							'regencies_id' => $this->input->post('regencies_id'),
							'address' => $this->input->post('address'),
						);
						
						if($img_profile)
							$agent_data['img_profile'] = $img_profile;
					
						$this->Agent_m->update($id,$agent_data);
						
						
						$agent_file = array(
						);
						
						if($img_identity)
							$agent_file['img_identity'] = $img_identity;
						
						$this->Agent_m->update_agent_file($id,$agent_file);
						// redirect them back to the admin page if admin, or to the base url if non admin
						$this->session->set_flashdata('message', $this->ion_auth->messages());
						redirect("admin/agent/list", 'refresh');

					}
					else
					{
						// redirect them back to the admin page if admin, or to the base url if non admin
						$this->session->set_flashdata('message', $this->ion_auth->errors());
						redirect("admin/agent/list", 'refresh');

					}

				}
			}


			// set the flash data error message if there is one
			$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			$data['first_name'] = [
				'name'  => 'first_name',
				'id'    => 'first_name',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('first_name', $user->first_name),
			];
			$data['last_name'] = [
				'name'  => 'last_name',
				'id'    => 'last_name',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('last_name', $user->last_name),
			];
			$data['company'] = [
				'name'  => 'company',
				'id'    => 'company',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('company', $user->company),
			];
			$data['phone'] = [
				'name'  => 'phone',
				'id'    => 'phone',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('phone', $user->phone),
			];
			$data['password'] = [
				'name' => 'password',
				'id'   => 'password',
				'type' => 'password'
			];
			$data['password_confirm'] = [
				'name' => 'password_confirm',
				'id'   => 'password_confirm',
				'type' => 'password'
			];

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
	
	public function list_commision()
	{
		$this->show('agent_commision_list',$result,TRUE);
	}
	
	public function get_list_commision()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Agent_m->get_list_commision($param),
			"recordsTotal" => $this->Agent_m->get_total_list_commision_unfiltered($param),
			"recordsFiltered" => $this->Agent_m->get_total_list_commision_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function get_commision($id)
	{
		$commision = $this->Agent_m->get_commision($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Komisi',
				'data' => $commision,
			);
		echo json_encode($result);
	}
	
	public function post_commision($id = NULL)
	{
		$param = array(
			'title' => $this->input->post('title'),
			'description' => $this->input->post('description'),
			'min_target' => $this->input->post('min_target'),
			'max_target' => $this->input->post('max_target'),
			'percentage' => $this->input->post('percentage'),
		);
		
		if($id == NULL)
		{
			$this->Agent_m->add_commision($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Komisi',
			);
			echo json_encode($result);
		}else
		{
			$this->Agent_m->edit_commision($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Komisi',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_commision($id)
	{
		$this->Agent_m->delete_commision($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Komisi',
		);
		echo json_encode($result);
	}
	
	public function list_withdraw()
	{
		$list_withdraw_status = $this->Agent_m->get_withdraw_status();
		$result = array(
			'list_withdraw_status' => $list_withdraw_status, 
		);
		$this->show('agent_withdraw_list',$result,TRUE);
	}
	
	public function get_list_withdraw()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Agent_m->get_list_withdraw($param),
			"recordsTotal" => $this->Agent_m->get_total_list_withdraw_unfiltered($param),
			"recordsFiltered" => $this->Agent_m->get_total_list_withdraw_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function verification_withdraw($id)
	{
		$status_id = $this->input->post('status_id');
		$description = $this->input->post('description');
		$this->Agent_m->update_withdraw_status($id,$status_id,$description);
		
		$detail = $this->Agent_m->withdraw_detail($id);
		$status = $this->Agent_m->get_withdraw_status($status_id);
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke agent
		$this->fcm->addRecepient($this->Agent_m->get_token($detail->account_id));
		$data_payload = array(
			'data_type' => 'customer_withdraw',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Pencairan Dana", "text" => 'Permintaan #'.$id.' '.$status->name ,'android_channel_id' => 3, 'sound' => 'default');
		$this->fcm->setNotification($notif);
		$this->fcm->send();
		//end notification
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function list_transaction()
	{
		$this->show('agent_transaction_list',$result,TRUE);
	}
	
	public function get_list_transaction()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Agent_m->get_list_transaction($param),
			"recordsTotal" => $this->Agent_m->get_total_list_transaction_unfiltered($param),
			"recordsFiltered" => $this->Agent_m->get_total_list_transaction_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function pair_partner()
	{
		$this->show('agent_pair_partner',$data,TRUE);
	}
	
	public function post_pair_partner()
	{
		$agent_email = $this->input->post('agent_email');
		$partner_email = $this->input->post('partner_email');
		
		$identity_column = $this->config->item('identity', 'ion_auth');
		
		$agent = $this->ion_auth->where($identity_column, $agent_email)->users()->row();
		
		$groups = $this->ion_auth->get_users_groups($agent->id)->result();
		
		$is_agent = false;
		foreach($groups as $val)
		{
			if($val->id == 7)
				$is_agent = true;
		}
		
		$partner = $this->ion_auth->where($identity_column, $partner_email)->users()->row();
		
		$groups = $this->ion_auth->get_users_groups($partner->id)->result();
		
		$is_partner = false;
		
		foreach($groups as $val)
		{
			if($val->id == 4)
				$is_partner = true;
		}
		
		$data = array();
		
		$input = new stdClass();
		$input->agent_email = $agent_email;
		$input->partner_email = $partner_email;
		$data['input'] = $input;
		
		if( ($is_agent != TRUE) || ($is_partner != TRUE) )
		{	
			if($is_agent != TRUE)
			{
				$data['error_message'] .= "Email Marketing yang dimasukan salah!</br>";
			}
			
			if($is_partner != TRUE)
			{
				$data['error_message'] .= "Email Mitra yang dimasukan salah!</br>";
			}
			
		}else
		{
			$partner_agent_id = $this->Agent_m->get_partner_agent($partner->id);
			if($partner_agent_id == $agent->id){
				$data['error_message'] = "Mitra sudah disandingkan dengan Marketing ini";
			}else if($partner_agent_id != NULL){
				$partner_agent = $this->ion_auth->user($partner_agent_id)->row();
				$data['error_message'] = "Mitra sudah disandingkan dengan Marketing <b>".$partner_agent->first_name." ".$partner_agent->last_name."</b>";
			}else
			{
				$this->Agent_m->pair_partner($partner->id,$agent->id);
				$data['success_message'] .= "Berhasil mensandingkan </br> Marketing <b>".$agent->first_name." ".$agnet->last_name."</b> </br>dengan </br>Mitra <b>".$partner->first_name." ".$partner->last_name."</b></br>";
				$data['input'] = NULL;
			}
		}
		
		$this->show('agent_pair_partner',$data,TRUE);
	}
}
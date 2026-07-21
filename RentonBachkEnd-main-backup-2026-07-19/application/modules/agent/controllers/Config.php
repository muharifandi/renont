<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends AgentController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('agent/Config_m');
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function profile()
	{
		$agent_id = $this->get_user_id();
		$data = array(
				'agent' => $this->Config_m->get_profile_detail($agent_id),
		);
		$this->show('config_profile',$data,TRUE);
	}
	
	public function profile_update()
	{
		$this->load->model('Config_m');
		$id = $this->get_user_id();
		
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
					
						$this->Config_m->update_profile($id,$agent_data);
						
						
						$agent_file = array(
						);
						
						if($img_identity)
							$agent_file['img_identity'] = $img_identity;
						
						$this->Config_m->update_profile_file($id,$agent_file);
						// redirect them back to the admin page if admin, or to the base url if non admin
						$this->session->set_flashdata('message', $this->ion_auth->messages());
						redirect("agent/config/profile", 'refresh');

					}
					else
					{
						// redirect them back to the admin page if admin, or to the base url if non admin
						$this->session->set_flashdata('message', $this->ion_auth->errors());
						redirect("agent/config/profile", 'refresh');

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
	
	public function list_bank()
	{
		$data = array(
				'banks' => $this->Config_m->get_all_bank(),
		);
		$this->show('config_bank_list',$data,TRUE);
	}
	
	public function get_list_bank()
	{
		$agent_id = $this->get_user_id();
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Config_m->get_list_bank($agent_id,$param),
			"recordsTotal" => $this->Config_m->get_total_list_bank_unfiltered($agent_id,$param),
			"recordsFiltered" => $this->Config_m->get_total_list_bank_filtered($agent_id,$param),
		);
		echo json_encode($result);
	
	}
	
	public function get_bank($id)
	{
		$agent_id = $this->get_user_id();
		$bank = $this->Config_m->get_bank($agent_id,$id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Bank',
				'data' => $bank,
			);
		echo json_encode($result);
	}
	
	public function post_bank($id = NULL)
	{
		$agent_id = $this->get_user_id();
		$param = array(
			'bank_id' => $this->input->post('bank_id'),
			'name' => $this->input->post('name'),
			'bank_number' => $this->input->post('bank_number'),
		);
		
		
		if($id == NULL)
		{
			$this->Config_m->add_bank($agent_id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Bank',
			);
			echo json_encode($result);
		}else
		{
			$this->Config_m->edit_bank($agent_id,$id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Bank',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_bank($id)
	{
		$agent_id = $this->get_user_id();
		$this->Config_m->delete_bank($agent_id,$id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Bank',
		);
		echo json_encode($result);
	}
	
	
	
	public function change_password_admin($id)
	{
		$this->lang->load('auth');
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		$user = $this->ion_auth->user($id)->row();

		if ($this->form_validation->run() === FALSE)
		{
			// display the form
			// set the flash data error message if there is one
			$data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
			$data['old_password'] = [
				'name' => 'old',
				'id' => 'old',
				'type' => 'password',
			];
			$data['new_password'] = [
				'name' => 'new',
				'id' => 'new',
				'type' => 'password',
				'pattern' => '^.{' . $data['min_password_length'] . '}.*$',
			];
			$data['new_password_confirm'] = [
				'name' => 'new_confirm',
				'id' => 'new_confirm',
				'type' => 'password',
				'pattern' => '^.{' . $data['min_password_length'] . '}.*$',
			];
			$data['user_id'] = [
				'name' => 'user_id',
				'id' => 'user_id',
				'type' => 'hidden',
				'value' => $user->id,
			];

			$this->show('config_admin_change_password',$data,TRUE);
		}
		else
		{
			$identity = $user->email;

			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect('admin/config/list_admin', 'refresh');
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect('admin/config/list_admin', 'refresh');
			}
		}
	}
	
	
	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return [$key => $value];
	}

	/**
	 * @return bool Whether the posted CSRF token matches
	 */
	public function _valid_csrf_nonce(){
		$csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
		if ($csrfkey && $csrfkey === $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
			return FALSE;
	}
	
	public function save_admin_token($id,$token)
	{
		$this->Config_m->update_admin_token($id,$token);
		
		echo "Token diperbaharui";
	}
	
	public function get_admin_notification_count($id)
	{
		$this->load->model('Partner_m');
		$this->load->model('Customer_m');
		$this->load->model('PartnerReward_m');
		$partner_verification = $this->Partner_m->partner_unprocessed_count();
		$partner_feature = $this->Partner_m->partner_feature_unprocessed_count();
		
		$withdraw_request = $this->Customer_m->withdraw_unprocessed_count();
		$topup_request = $this->Customer_m->topup_unprocessed_count();
		$partner_claim_reward = $this->PartnerReward_m->reward_unprocessed_count();
		
		$request = $withdraw_request + $topup_request+$partner_claim_reward;
		$data = array(
			'notification' => 0,
			'partner' => $partner_verification,
			'partner_verification' => $partner_verification,
			'request' => $request,
			'withdraw_request' => $withdraw_request,
			'topup_request' => $topup_request,
			'partner_claim_reward' => $partner_claim_reward,
			'feature_request' => $partner_feature,
			'support_request' => 0,
			'chat' => 0,
		);
		echo json_encode($data);
	}
	
	public function get_regencies_select()
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
			'items' => $this->Config_m->get_list_regencies($param),
			"total_count" => $this->Config_m->get_total_list_regencies_filtered($param),
		);
		echo json_encode($result);
	}
}
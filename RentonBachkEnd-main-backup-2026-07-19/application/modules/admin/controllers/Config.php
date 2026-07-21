<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Config_m');
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function basic()
	{
		$array_name = array(
		'admin_fee_use_percentage',
		'admin_fee',
		'referal_point_reward_partner',
		'referal_point_reward_customer',
		'transaction_point_reward_partner',
		'transaction_point_reward_customer',
		'exchange_point_minimum',
		'rate_point_to_balance',
		'topup_minimum',
		'withdraw_minimum',
		'distance_recomendation_rentvehicle',
		'distance_max_rentvehicle',
		'maintenance',
		'maintenance_message',
		'android_app_version_code',
		'android_app_version_name',
		'android_app_update_link',
		'promote_price_per_day_rent_vehicle',
		'promote_max_rent_vehicle',
		'promote_info_rent_vehicle',
		'report_title',
		'report_description',
		);
		$config_data = $this->Config_m->get_config($array_name);
		$data = array(
			'config_data' => $config_data,
		);
		
		$this->show('config_basic',$data,TRUE);
	}
	
	public function set_config()
	{
		$post = $this->input->post();
		$this->Config_m->set_config($post);
		
		redirect('admin/config/basic','refresh');
	}
	public function list_bank()
	{
		$this->show('config_bank_list',$result,TRUE);
	}
	
	public function get_list_bank()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Config_m->get_list_bank($param),
			"recordsTotal" => $this->Config_m->get_total_list_bank_unfiltered($param),
			"recordsFiltered" => $this->Config_m->get_total_list_bank_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_bank($id)
	{
		$bank = $this->Config_m->get_bank($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Bank',
				'data' => $bank,
			);
		echo json_encode($result);
	}
	
	public function post_bank($id = NULL)
	{
		$param = array(
			'name' => $this->input->post('name'),
			'code' => $this->input->post('code'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->Config_m->add_bank($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Bank',
			);
			echo json_encode($result);
		}else
		{
			$this->Config_m->edit_bank($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Bank',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_bank($id)
	{
		$this->Config_m->delete_bank($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Bank',
		);
		echo json_encode($result);
	}
	
	public function list_feature()
	{
		$this->show('config_feature_list',$result,TRUE);
	}
	
	public function get_list_feature()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Config_m->get_list_feature($param),
			"recordsTotal" => $this->Config_m->get_total_list_feature_unfiltered($param),
			"recordsFiltered" => $this->Config_m->get_total_list_feature_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_feature($id)
	{
		$feature = $this->Config_m->get_feature($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Layanan',
				'data' => $feature,
			);
		echo json_encode($result);
	}
	public function post_feature($id = NULL)
	{
		$param = array(
			'id' => $this->input->post('id'),
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->Config_m->add_feature($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Layanan',
			);
			echo json_encode($result);
		}else
		{
			$this->Config_m->edit_feature($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Layanan',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_feature($id)
	{
		$this->Config_m->delete_feature($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Layanan',
		);
		echo json_encode($result);
	}
	
	public function list_bank_company()
	{
		$data = array(
				'banks' => $this->Config_m->get_all_bank(),
		);
		$this->show('config_bank_company_list',$data,TRUE);
	}
	
	public function get_list_bank_company()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Config_m->get_list_bank_company($param),
			"recordsTotal" => $this->Config_m->get_total_list_bank_company_unfiltered($param),
			"recordsFiltered" => $this->Config_m->get_total_list_bank_company_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_bank_company($id)
	{
		$bank = $this->Config_m->get_bank_company($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Bank',
				'data' => $bank,
			);
		echo json_encode($result);
	}
	
	public function post_bank_company($id = NULL)
	{
		$param = array(
			'bank_id' => $this->input->post('bank_id'),
			'name' => $this->input->post('name'),
			'bank_number' => $this->input->post('bank_number'),
		);
		
		
		if($id == NULL)
		{
			$this->Config_m->add_bank_company($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Bank',
			);
			echo json_encode($result);
		}else
		{
			$this->Config_m->edit_bank_company($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Bank',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_bank_company($id)
	{
		$this->Config_m->delete_bank_company($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Bank',
		);
		echo json_encode($result);
	}
	
	public function list_admin()
	{
		$users = array();
		$temp['users'] = $this->ion_auth->users()->result();

		foreach ($temp['users'] as $k => $val)
		{
			$temp['users'][$k]->groups = $this->ion_auth->get_users_groups($val->id)->result();
			
			$match = false;
			$groups = array();
			
			foreach($temp['users'][$k]->groups as $val)
			{
				if($val->id == 1 || $val->id == 2 || $val->id == 3 || $val->id == 6){
					$groups[] = $val->name;
					$match = true;
				}
			}
				
				echo $sizegroups;
			$temp['users'][$k]->groups = implode(",",$groups);
			if($match)
				$users[] = $temp['users'][$k];
		}
		
		$data['users'] = $users;
		$data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
		$this->show('config_admin_list',$data,TRUE);
	}
	
	public function add_admin()
	{
		$this->show('config_admin_add',$data,TRUE);
	}
	
	public function admin_add()
	{
		$this->lang->load('auth');
		$this->load->library('form_validation');
		$tables = $this->config->item('tables', 'ion_auth');
		$identity_column = $this->config->item('identity', 'ion_auth');
		$data['identity_column'] = $identity_column;

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
		$this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'trim');
		$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

		if ($this->form_validation->run() === TRUE)
		{
			$email = strtolower($this->input->post('email'));
			$identity = ($identity_column === 'email') ? $email : $this->input->post('identity');
			$password = $this->input->post('password');

			$additional_data = [
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'company' => $this->input->post('company'),
				'phone' => $this->input->post('phone'),
			];
		}
		if ($this->form_validation->run() === TRUE && $this->ion_auth->register($identity, $password, $email, $additional_data,array('1')))
		{
			// check to see if we are creating the user
			// redirect them back to the admin page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect("admin/config/list_admin", 'refresh');
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
			$data['company'] = [
				'name' => 'company',
				'id' => 'company',
				'type' => 'text',
				'value' => $this->form_validation->set_value('company'),
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

			$this->show('config_admin_add',$data,TRUE);
		}
	}
	
	
	public function edit_admin($id)
	{
		$this->lang->load('auth');
		$this->load->library('form_validation');
		$user = $this->ion_auth->user($id)->row();
		$groups = $this->ion_auth->groups()->result_array();
		$currentGroups = $this->ion_auth->get_users_groups($id)->result();

		//USAGE NOTE - you can do more complicated queries like this
		//$groups = $this->ion_auth->where(['field' => 'value'])->groups()->result_array();


		// validate form input
		$this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim');
		$this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'trim');

		if (isset($_POST) && !empty($_POST))
		{
			// do we have a valid request?
			if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
			{
				show_error($this->lang->line('error_csrf'));
			}

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
					'company' => $this->input->post('company'),
					'phone' => $this->input->post('phone'),
				];

				// update the password if it was posted
				if ($this->input->post('password'))
				{
					$data['password'] = $this->input->post('password');
				}

				// Only allow updating groups if user is admin
				if ($this->ion_auth->is_admin())
				{
					// Update the groups user belongs to
					$this->ion_auth->remove_from_group('', $id);

					$groupData = $this->input->post('groups');
					if (isset($groupData) && !empty($groupData))
					{
						foreach ($groupData as $grp)
						{
							$this->ion_auth->add_to_group($grp, $id);
						}

					}
				}

				// check to see if we are updating the user
				if ($this->ion_auth->update($user->id, $data))
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->messages());
					redirect("admin/config/list_admin", 'refresh');

				}
				else
				{
					// redirect them back to the admin page if admin, or to the base url if non admin
					$this->session->set_flashdata('message', $this->ion_auth->errors());
					redirect("admin/config/list_admin", 'refresh');

				}

			}
		}

		// display the edit user form
		$data['csrf'] = $this->_get_csrf_nonce();

		// set the flash data error message if there is one
		$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		// pass the user to the view
		$data['user'] = $user;
		$data['groups'] = $groups;
		$data['currentGroups'] = $currentGroups;

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

		$this->show('config_admin_edit',$data,TRUE);
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
	
	public function activate_admin($id)
	{
		$activation = $this->ion_auth->activate($id);

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect('admin/config/list_admin', 'refresh');
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect('admin/config/list_admin', 'refresh');
		}
	}
	
	public function deactivate_admin($id)
	{
		$activation = $this->ion_auth->deactivate($id);

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect('admin/config/list_admin', 'refresh');
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect('admin/config/list_admin', 'refresh');
		}
	}
		
	public function delete_admin($id)
	{
		$this->lang->load('auth');
		if($this->ion_auth->delete_user($id))
		{
			$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect('admin/config/list_admin', 'refresh');
		}else
		{
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect('admin/config/list_admin', 'refresh');
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
		$this->load->model('Agent_m');
		$partner_verification = $this->Partner_m->partner_unprocessed_count();
		$partner_feature = $this->Partner_m->partner_feature_unprocessed_count();
		
		$withdraw_request = $this->Customer_m->withdraw_unprocessed_count();
		$agent_withdraw_request = $this->Agent_m->withdraw_unprocessed_count();
		$topup_request = $this->Customer_m->topup_unprocessed_count();
		$partner_claim_reward = $this->PartnerReward_m->reward_unprocessed_count();
		
		$request = $withdraw_request + $topup_request+$partner_claim_reward;
		$data = array(
			'notification' => 0,
			'partner' => $partner_verification,
			'partner_verification' => $partner_verification,
			'request' => $request,
			'withdraw_request' => $withdraw_request,
			'agent_withdraw_request' => $agent_withdraw_request,
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
	
	public function get_bank_company_select()
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
			'items' => $this->Config_m->get_list_bank_company($param),
			"total_count" => $this->Config_m->get_total_list_bank_company_filtered($param),
		);
		echo json_encode($result);
	
	}
}
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Agent extends AgentController
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('agent/Agent_m');
	}
	
	public function index()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function list_withdraw()
	{
		$this->load->model('agent/Config_m');
		$agent_id = $this->get_user_id();
		$data['config'] = $this->Config_m->get_config(array("withdraw_minimum"));
		$data['agent_banks'] = $this->Config_m->get_all_agent_bank($agent_id);
		$this->show('withdraw_list',$data,TRUE);
	}
	
	public function get_list_withdraw()
	{
		$agent_id = $this->get_user_id();
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->Agent_m->get_list_withdraw($agent_id,$param),
			"recordsTotal" => $this->Agent_m->get_total_list_withdraw_unfiltered($agent_id,$param),
			"recordsFiltered" => $this->Agent_m->get_total_list_withdraw_filtered($agent_id,$param),
		);
		echo json_encode($result);
	}
	
	public function request_withdraw()
	{
		$this->load->model('agent/Config_m');
		$agent_id = $this->get_user_id();
		$value = $this->input->post('value');
		$config = $this->Config_m->get_config(array("withdraw_minimum"));
		if(is_numeric($value))
		{
			if($value > $config['withdraw_minimum'])
			{
				$balance = $this->Agent_m->get_agent_balance($agent_id);
				
				if($balance->balance >= $value)
				{
					$param = array(
						'account_id' => $agent_id,
						'account_bank_id' => $this->input->post('account_bank_id'),
						'value' => $value,
						'description' => $this->input->post('description'),
						'status' => 1,
					);
					$this->Agent_m->request_withdraw($param);
					$result = array(
						'status' => true,
						'message' => 'Permintaan pencairan berhasil',
					);
					echo json_encode($result);
				}else
				{
					$result = array(
						'status' => false,
						'message' => 'Permintaan pencairan gagal. Saldo anda saat ini adalah Rp.'.number_format($balance->balance, 2 ,",",".").",-",
					);
					echo json_encode($result);
				}
			}else
			{
				$result = array(
					'status' => false,
					'message' => 'nominal yang dimasukan lebih kecil dari minimum pencairan',
				);
				echo json_encode($result);
			}
		}else
		{
			$result = array(
				'status' => false,
				'message' => 'Maaf nominal yang dimasukan tidak valid',
			);
			echo json_encode($result);
		}
	}
	
}
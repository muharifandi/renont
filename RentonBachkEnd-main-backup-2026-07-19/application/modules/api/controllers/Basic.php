<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class Basic extends MY_Api {

    public function __construct() {
        parent::__construct();
        
    }
	
	public function index_get()
	{
		$a = date("Y-m-d", strtotime('monday this week'));   
		$b = date("Y-m-d", strtotime('sunday this week'));
		$c = date("Y-m-d", strtotime('now'));
		
		$response = array(
			'awal_minggu_sekarang' => $a,
			'akhir_minggu_sekarang' => $b,
			'tanggal_sekarang' => $c,
		);
		$this->response($response,200);
	}
	
	public function application_status_post()
	{
		$this->load->model('Basic_m');
		$response = array(
			'maintenance' => $this->Basic_m->get_config('maintenance')->value,
			'maintenance_message' => $this->Basic_m->get_config('maintenance_message')->value,
			'android_app_version_code' => $this->Basic_m->get_config('android_app_version_code')->value,
			'android_app_version_name' => $this->Basic_m->get_config('android_app_version_name')->value,
			'android_app_update_link' => $this->Basic_m->get_config('android_app_update_link')->value,
		);
		$this->response($response,200);
	}
	
	public function check_email_post()
	{
		$email = $this->post('email');
		$this->load->model('Basic_m');
		$result = $this->Basic_m->check_email($email);
		
		if($result)
		{
			$response = array(
				'status' => true,
				'use_email' => false,
				'message' => 'Email telah terdaftar',
				'additional_info' => 'Email telah digunakan oleh '.$result->first_name,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'use_email' => true,
				'message' => 'Email dapat digunakan'
			);
			$this->response($response,200);
		}
	}
	
	public function check_agent_post()
	{
		$id = $this->post('id');
		$this->load->model('Basic_m');
		$result = $this->Basic_m->check_agent($id);
		
		if($result)
		{
			$response = array(
				'status' => true,
				'valid' => true,
				'message' => 'Agen '.$result->first_name." ".$result->last_name,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'valid' => false,
				'message' => 'Agen tidak ditemukan'
			);
			$this->response($response,200);
		}
	}
	
	public function check_phone_post()
	{
		$phone = $this->post('phone');
		$this->load->model('Basic_m');
		$result = $this->Basic_m->check_phone($phone);
		
		if($result)
		{
			$response = array(
				'status' => true,
				'use_phone' => false,
				'message' => 'Nomor telah terdaftar',
				'additional_info' => 'Nomor telah digunakan oleh '.$result->first_name,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'use_phone' => true,
				'message' => 'Nomor dapat digunakan'
			);
			$this->response($response,200);
		}
	}
	
	public function get_regencies_post()
	{
		$regency = $this->post('regency');
		$this->load->model('Basic_m');
		$result = $this->Basic_m->get_regencies($regency);
		
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => 'Sukses',
				'regencies' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => 'Sukses',
				'regencies' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function get_active_regencies_post()
	{
		$this->load->model('Basic_m');
		
		$result = $this->Basic_m->get_active_regencies();
		$response = array(
			'status' => true,
			'message' => 'Sukses',
			'data' => $result,
		);
		$this->response($response,200);
	}
	
	
	
}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class CustomerRent extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('CustomerRent_m');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Basic",200);
	}
	
	public function list_transaction_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
			'status' => $this->post('status'),
		);
		$result = $this->CustomerRent_m->list_transaction($account_id,$param);
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'transaction_rent_vehicle' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'transaction_rent_vehicle' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function transaction_detail_post()
	{
		$this->load->model('Partner_m');
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');
		$this->load->model('RentVehicle_m');
		
		$id = $this->post('id');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$vehicle->photos = $this->RentVehicle_m->vehicle_photos($transaction_detail->item_id);
		
		$voucher = $this->Basic_m->get_voucher($transaction_detail->voucher_id);
		
		$balance = $this->Customer_m->balance($account_id);
		
		$now = new DateTime(date('Y-m-d H:i:s')); 
		$end_date = new DateTime($transaction_detail->end_date);
		
		$hour_overtime = 0;
		if($now > $end_date)
		{
			$interval = $now->diff($end_date);
			$hour_overtime = $interval->h;
			$hour_overtime = $hour_overtime + ($interval->days*24);
		}
		
		$is_reviewed = $this->CustomerRent_m->is_review_submit($transaction_detail->id,$transaction_detail->account_id);
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'partner' => $this->Partner_m->partner_info($vehicle->account_id),
			'vehicle' => $vehicle,
			'transaction_detail' => $transaction_detail,
			'voucher' => $voucher,
			'balance' => $balance,
			'hour_overtime' => $hour_overtime,
			'feedback' => ($is_reviewed)?0:1,
		);
		$this->response($response,200);
	}
	
	public function cancel_transaction_post()
	{
		$this->load->model('Customer_m');
		$this->load->model('RentVehicle_m');
		
		$id = $this->post('id');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$this->RentVehicle_m->update_transaction_status($id,11);
		$status = $this->RentVehicle_m->get_transaction_status_name(11);
		
		$timeline = array(
			'transaction_id' => $id,
			'title' => $status->name,
			'description' => $status->name,
			
		);
		$this->RentVehicle_m->add_timeline_transaction($timeline);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
        $this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke mitra
		$this->fcm->addRecepient($this->Customer_m->get_token($vehicle->account_id));
		$data_payload = array(
			'data_type' => 'partner_rent_vehicle_transaction',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Rental Kendaraan", "text" => "Transaksi #".$id." telah dibatalkan oleh pelanggan.",'android_channel_id' => 2, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		
		//kirim ke pelanggan
		$this->fcm->clearRecepients();// bersihkan token
		$this->fcm->addRecepient($this->Customer_m->get_token($account_id));
		$data_payload = array(
			'data_type' => 'customer_rent_vehicle_transaction',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Rental Kendaraan", "text" => "Berhasil membatalkan rental kendaraan #".$id."",'android_channel_id' => 2, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		//end notification
		
		if($transaction_detail->cash_on_delivery == 1)
		{
			$this->Customer_m->increase_balance($vehicle->account_id,$transaction_detail->admin_fee);
			$response = array(
				'status' => true,
				'message' => "Berhasil membatalkan pemesanan.",
			);
			$this->response($response,200);
		}else
		{
			$this->Customer_m->increase_balance($account_id,$transaction_detail->total_payment);
			
			$response = array(
				'status' => true,
				'message' => "Berhasil membatalkan pemesanan. Pembayaran akan dikembalikan ke saldo",
			);
			$this->response($response,200);
		}
	}
	
	public function update_status_transaction_post()
	{
		$this->load->model('Customer_m');
		$this->load->model('RentVehicle_m');
		
		$id = $this->post('id');
		$status = $this->post('status');
		
		$this->RentVehicle_m->update_transaction_status($id,$status);
		$status = $this->RentVehicle_m->get_transaction_status_name($status);
		
		$timeline = array(
			'transaction_id' => $id,
			'title' => $status->name,
			'description' => $status->name,
			
		);
		$this->RentVehicle_m->add_timeline_transaction($timeline);
		
		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		
		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
        $this->fcm->setApiKey($this->config->item('fcm_api_key_android','fcm'));
		
		//kirim ke mitra
		$this->fcm->addRecepient($this->Customer_m->get_token($vehicle->account_id));
		$data_payload = array(
			'data_type' => 'partner_rent_vehicle_transaction',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Rental Kendaraan", "text" => "Transaksi #".$id." : ".$status->name,'android_channel_id' => 2, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		
		//kirim ke pelanggan
		$this->fcm->clearRecepients();// bersihkan token
		$this->fcm->addRecepient($this->Customer_m->get_token($transaction_detail->account_id));
		$data_payload = array(
			'data_type' => 'customer_rent_vehicle_transaction',
			'id' => $id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Rental Kendaraan", "text" => "Transaksi #".$id." : ".$status->name,'android_channel_id' => 2, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		//end notification
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah status pemesanan",
		);
		$this->response($response,200);
	}
	
	public function post_review_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$data = array(
			'transaction_id' => $this->post('id'),
			'account_id' => $account_id,
			'rating' => $this->post('rating'),
			'comment' => $this->post('comment'),
		);
		
		$this->CustomerRent_m->post_review($data);
		$response = array(
			'status' => true,
			'message' => "Berhasil mengirim ulasan",
		);
		$this->response($response,200);
	}
}
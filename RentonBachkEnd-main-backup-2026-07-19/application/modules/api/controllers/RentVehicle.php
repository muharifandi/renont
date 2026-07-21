<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class RentVehicle extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('RentVehicle_m');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Rental",200);
	}
	
	public function list_post()
	{
		$this->load->model('Basic_m');
		$this->load->model('PartnerRent_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'functional_type' => $this->post('functional_type'),
			'start_date' => $this->post('start_date'),
			'end_date' => $this->post('end_date'),
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
			'sort' => $this->post('sort'),
			'status' => $this->post('status'),
			'min_passenger' => $this->post('min_passenger'),
			'max_passenger' => $this->post('max_passenger'),
			'min_price' => $this->post('min_price'),
			'max_price' => $this->post('max_price'),
			'vehicle_functional_type_selected' => $this->post('vehicle_functional_type_selected'),
			'with_driver' => $this->post('with_driver'),
			'regencies' => $this->post('regency'),
		);
		$result = $this->RentVehicle_m->list_vehicle($account_id,$param);
		
		$regency = $this->Basic_m->get_regency($this->post('regency'))->name;
		$minmax_price = $this->RentVehicle_m->list_vehicle_min_max_value($account_id);
		$functional_type = $this->PartnerRent_m->get_functional_type();
		
		if($param['page'] == 1)
		{
			$param['page'] = 1;
			$param['limit'] = $this->Basic_m->get_config_value('promote_max_rent_vehicle');
			
			$promote = $this->RentVehicle_m->list_promote_vehicle($account_id,$param);
			$result = array_merge($promote,$result);
		}
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicles' => $result,
				'price_min' => $minmax_price->price_min,
				'price_max' => $minmax_price->price_max,
				'functional_type' => $functional_type,
				'regencies' => $regency,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicles' => [],
				'price_min' => $minmax_price->price_min,
				'price_max' => $minmax_price->price_max,
				'functional_type' => $functional_type,
				'regencies' => $regency,
			);
			$this->response($response,200);
		}
	}
	
	public function detail_post()
	{
		$this->load->model('Partner_m');
		$id = $this->post('id');
		$vehicle = $this->RentVehicle_m->vehicle_detail($id);
		
		$param_review = array(
			'page' => 1,
			'limit' => 5,
		);
		$vehicle_booked = $this->RentVehicle_m->vehicle_booked($id);
		$vehicle_review = $this->RentVehicle_m->vehicle_review($id, $param_review);
		$vehicle_review_total = $this->RentVehicle_m->vehicle_review_total($id);
		$vehicle->photos = $this->RentVehicle_m->vehicle_photos($id);
		
		$partner = $this->Partner_m->partner_info($vehicle->account_id);
		
		$this->load->model('PartnerRent_m');
		$configRent = $this->PartnerRent_m->config($vehicle->account_id);
		
		
		$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicle' => $vehicle,
				'vehicle_booked' => $vehicle_booked,
				'force_with_driver' => $configRent->force_with_driver,
				'partner' => $partner,
				'review' => $vehicle_review,
				'review_total' => $vehicle_review_total,
			);
		$this->response($response,200);
	}
	
	public function list_vehicle_review_post()
	{
		$param_review = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
		);
		
		$id = $this->post('id');
		$vehicle_review = $this->RentVehicle_m->vehicle_review($id, $param_review);
		$vehicle_review_total = $this->RentVehicle_m->vehicle_review_total($id);
		
		$response = array(
				'status' => true,
				'message' => "Berhasil",
				'review' => $vehicle_review,
				'review_total' => $vehicle_review_total,
			);
		$this->response($response,200);
	}
	
	public function checkout_detail_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
		
		$id = $this->post('vehicle_id');
		$price_package = $this->post('price_package');
		$start_date = $this->post('start_date');
		$end_date = $this->post('end_date');
		
		//TODO: nanti pakai startdate end date untuk kalkulasi apakah kendaraan bisa disewa atau tidak.
		$vehicle = $this->RentVehicle_m->vehicle_detail($id);
		$vehicle->photos = $this->RentVehicle_m->vehicle_photos($id);
		
		$start_time = new DateTime($start_date);
		$end_time = new DateTime($end_date);
		//$end_time->modify('+1 day');
		$interval = $start_time->diff($end_time);
		$daysRent = $interval->days;
		
		$price = 0;
		if($price_package == 0)
			$price = $vehicle->price;
		else if($price_package == 1)
			$price = $vehicle->price_with_driver_basic;
		else if($price_package == 2)
			$price = $vehicle->price_with_driver_full;

		$rent_payment = $price * $daysRent;
		$this->load->model('PartnerRent_m');
		$configRent = $this->PartnerRent_m->config($vehicle->account_id);
		
		$now = new DateTime(date('Y-m-d')); 
		$your_date = new DateTime($start_date);
		
		$interval = $now->diff($your_date);
		$daysCod = $interval->days;
		
		$admin_fee = (double)$this->Basic_m->get_config_value('admin_fee');
		$balance = $this->Customer_m->balance($vehicle->account_id)->balance;
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'vehicle' => $vehicle,
			'rent_payment' => $rent_payment,
			'days' => $daysRent,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'config' => $configRent,
			'cash_on_delivery' => ($daysCod <= $configRent->max_day_cod && $configRent->max_day_cod != 0 && ($balance > $admin_fee))? 1:0,
		);
		$this->response($response,200);
	}
	
	public function check_voucher_checkout_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'code' =>$this->post('code'),
			'start_date' =>$this->post('start_date'),
			'voucher_type' =>2,
			'user_type' =>5,
			'feature_id' => 1,
		);
		
		$this->load->model('Basic_m');
		$voucher = $this->Basic_m->get_voucher_by_code($param);
		
		
		if($voucher != null)
		{
			if($this->Basic_m->is_voucher_used($account_id,$voucher->id))
			{
				$response = array(
					'status' => true,
					'message' => "Voucher ini sudah pernah anda gunakan",
					'voucher' => null,
				);
				$this->response($response,200);
			}
			
			if($voucher->use_expire == 1)
			{
				$start_request_ts = new DateTime($this->post('start_date')." 00:00:00");
				$start_ts = new DateTime($voucher->start_date." 00:00:00");
				$end_ts = new DateTime($voucher->end_date." 23:59:59");
				$user_ts = new DateTime(date('Y-m-d'));

				$in_date_range = (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
				$in_date_range_request = (($start_request_ts >= $start_ts) && ($start_request_ts <= $end_ts));
				
				if(!$in_date_range)
				{
					$response = array(
						'status' => true,
						'message' => "Voucher ".$voucher->description." dapat digunakan tanggal ".$start_ts->format('d F Y H:i')." sampai tanggal ".$end_ts->format('d F Y H:i'),
						'voucher' => null,
					);
					$this->response($response,200);
				}
				
				if(!$in_date_range_request)
				{
					$response = array(
						'status' => true,
						'message' => "Voucher tidak valid. Tanggal awal penyewaan harus saat masa berlaku voucher yaitu antara tangga ".$start_ts->format('d F Y H:i')." sampai tanggal ".$end_ts->format('d F Y H:i'),
						'voucher' => null,
					);
					$this->response($response,200);
				}
				
				if($voucher->use_quota == 1 && $voucher->quota == 0)
				{
					$response = array(
						'status' => true,
						'message' => "Kuota voucher ini sudah habis",
						'voucher' => null,
					);
					$this->response($response,200);
				}
			}
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'voucher' => $voucher,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Voucher tidak ditemukan",
				'voucher' => null,
			);
			$this->response($response,200);
		}
	}
	
	public function post_checkout_post()
	{
		$this->load->model('PartnerRent_m');
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'account_id' => $account_id,
			'item_id' => $this->post('item_id'),
			'price_package' => $this->post('price_package'),
			'start_date' => $this->post('start_date')." ".$this->post('time'),
			'end_date' => $this->post('end_date')." ".$this->post('time'),
			'delivery' => $this->post('delivery'),
			'pickoff' => $this->post('pickoff'),
			'voucher_id' => $this->post('voucher_id'),
			'cash_on_delivery' => $this->post('cash_on_delivery'),
			'status'=> 1,
			'description'=> $this->post('description'),
		);
		
		$vehicle = $this->RentVehicle_m->vehicle_detail($param['item_id']);
		
		if($param['price_package'] == 0)
		{
			$param['price_package_name'] = "Car Only";
			$param['price'] = $vehicle->price;
		}else if($param['price_package'] == 1)
		{
			$param['price_package_name'] = "Car + Driver Basic";
			$param['price'] = $vehicle->price_with_driver_basic;
		}else if($param['price_package'] == 2)
		{
			$param['price_package_name'] = "Car + Driver All In";
			$param['price'] = $vehicle->price_with_driver_full;
		} 
		
		$configRent = $this->PartnerRent_m->config($vehicle->account_id);
		$param['overtime_fee'] = $configRent->overtime_fee;
		$addtitional_fee = 0;
		if($param['delivery'] == "1"){
			$param['delivery_date'] = $this->post('start_date')." ".$this->post('delivery_time');
			$param['delivery_address'] = $this->post('delivery_address');
			$param['delivery_latitude'] = $this->post('delivery_latitude');
			$param['delivery_longitude'] = $this->post('delivery_longitude');
			$param['delivery_fee'] = $configRent->delivery_fee;
			$addtitional_fee += $configRent->delivery_fee;
		}
		
		if($param['pickoff'] == "1"){
			$param['pickoff_date'] = $this->post('start_date')." ".$this->post('pickoff_time');
			$param['pickoff_address'] = $this->post('pickoff_address');
			$param['pickoff_latitude'] = $this->post('pickoff_latitude');
			$param['pickoff_longitude'] = $this->post('pickoff_longitude');
			$param['pickoff_fee'] = $configRent->pickoff_fee;
			$addtitional_fee += $configRent->pickoff_fee;
		}
		
		$discount = 0;
		$voucher;
		
		if($param['voucher_id'] != null){
			$voucher = $this->Basic_m->get_voucher($param['voucher_id']);
			
			if($voucher->use_quota == 1 && $voucher->quota > 0)
			{
				$discount = $voucher->value;
			}else
			{
				$param['voucher_id'] = null;
			}
			$param['discount'] = $discount;
			
		}
		
		$start_time = new DateTime($this->post('start_date'));
		$end_time = new DateTime($this->post('end_date'));
		$interval = $start_time->diff($end_time);
		$daysRent = $interval->days;
		
		$rent_payment = $param['price'] * $daysRent;
		
		$total_payment = 0;
		
		$total_payment += $rent_payment;
		$total_payment += $addtitional_fee;
		$total_payment -= $discount;
		
		$param['total_payment'] = $total_payment;
		
		$balance = $this->Customer_m->balance($account_id)->balance;
		$admin_fee_use_percentage = (int)$this->Basic_m->get_config_value('admin_fee_use_percentage');
		
		if($admin_fee_use_percentage == 1){
			$fee = (int) (($this->Basic_m->get_config_value('admin_fee') / 100) * $rent_payment) ;
			$param['admin_fee'] = $fee;
		}else
			$param['admin_fee'] = (double)$this->Basic_m->get_config_value('admin_fee');
		
		$partner_balance = $this->Customer_m->balance($vehicle->account_id)->balance;
		
		if($param['cash_on_delivery'] != 1)
		{
			if($balance < $total_payment)
			{
				$response = array(
					'status' => false,
					'message' => "Gagal Checkout. Saldo anda kurang dari yang dibtutuhkan untuk melakukan transaksi ini",
				);
				$this->response($response,200);
			}else
			{
				$this->Customer_m->decrease_balance($account_id,$total_payment);
				
				if($param['voucher_id'] != null){
					if($voucher->use_quota == 1 && $voucher->quota > 0)
					{
						$this->Basic_m->decrease_voucher_quota($param['voucher_id']);
					}
				}
			}
		}else
		{
			if($partner_balance < $param['admin_fee'])
			{
				$response = array(
					'status' => false,
					'message' => "Gagal Checkout. Saat ini mitra tidak dapat menerima COD.\nHarap matikan fitur COD untuk melanjutkan. Hubungi mitra via chat untuk menerima keterangan.",
				);
				$this->response($response,200);
			}
			
			$this->Customer_m->decrease_balance($vehicle->account_id,$param['admin_fee']);
			if($param['voucher_id'] != null){
					if($voucher->use_quota == 1 && $voucher->quota > 0)
					{
						$this->Basic_m->decrease_voucher_quota($param['voucher_id']);
					}
				}
		}
		$transaction_id = $this->RentVehicle_m->post_checkout($param);
		
		
		$status = $this->RentVehicle_m->get_transaction_status_name("1");
		
		$timeline = array(
			'transaction_id' => $transaction_id,
			'title' => $status->name,
			'description' => ($param['description'] != null)?$param['description']:$status->name,
			
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
			'id' => $transaction_id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Rental Kendaraan", "text" => "Pesanan Baru #".$transaction_id."",'android_channel_id' => 2, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		
		//kirim ke pelanggan
		$this->fcm->clearRecepients();// bersihkan token
		$this->fcm->addRecepient($this->Customer_m->get_token($account_id));
		$data_payload = array(
			'data_type' => 'customer_rent_vehicle_transaction',
			'id' => $transaction_id,
		);
		$this->fcm->setData($data_payload);
		$notif = array("title" => "Rental Kendaraan", "text" => "Berhasil memesan kendaraan #".$transaction_id."",'android_channel_id' => 2, 'sound' => 'default');
		$this->fcm->setNotification($notif);
        $this->fcm->send();
		//end notification
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
		);
		$this->response($response,200);
	}
	
}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

class PartnerRent extends MY_Api {

    public function __construct() {
        parent::__construct();
        $this->load->model('PartnerRent_m');
		$this->load->helper('image_manipulation');
    }
	
	public function index_get()
	{
		$this->response("Ini adalah API Partner",200);
	}
	
	public function get_functional_type_post()
	{
		$type = $this->PartnerRent_m->get_functional_type();
		$response = array(
				'status' => true,
				'message' => "Berhasil",
				'type' => $type,
		);
		$this->response($response,200);
	}
	
	public function get_input_config_post()
	{
		$functional_type = $this->post('functional_type');
		
		$vehicle_type = $this->PartnerRent_m->get_vehicle_type($functional_type);
		$brand = $this->PartnerRent_m->get_brand($functional_type);
		$color = $this->PartnerRent_m->get_color();
		$transmition_type = $this->PartnerRent_m->get_transmition_type($functional_type);
		$driven_type = $this->PartnerRent_m->get_driven_type($functional_type);
		$fuel = $this->PartnerRent_m->get_fuel();
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'vehicle_type' => $vehicle_type,
			'brand' => $brand,
			'color' => $color,
			'transmition_type' => $transmition_type,
			'driven_type' => $driven_type,
			'fuel' => $fuel,
		);
		$this->response($response,200);
	}
	
	public function get_input_vehicle_model_post()
	{
		$brand_id = $this->post('brand_id');
		
		$data = $this->PartnerRent_m->get_vehicle_model($brand_id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'data' => $data,
		);
		$this->response($response,200);
	}
	
	public function post_vehicle_post()
	{
		$data = array(
			'title' => $this->post("title"),
			'vehicle_type' => $this->post("vehicle_type"),
			'brand_id' => $this->post("brand_id"),
			'vehicle_model' => $this->post("vehicle_model"),
			'max_passenger' => $this->post("max_passenger"),
			'max_baggage' => $this->post("max_baggage"),
			'year' => $this->post("year"),
			'color_id' => $this->post("color_id"),
			'transmition_type' => $this->post("transmition_type"),
			'driven_type' => $this->post("driven_type"),
			'fuel_type' => $this->post("fuel_type"),
			'price' => $this->post("price"),
			'price_with_driver_basic' => $this->post("price_with_driver_basic"),
			'price_with_driver_full' => $this->post("price_with_driver_full"),
			'with_driver' => $this->post("with_driver"),
			'delivered' => $this->post("delivered"),
			'pickoff' => $this->post("pickoff"),
			'functional_type' => $this->post("functional_type"),
			'status' => $this->post("status"),
		);
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$item_id = $this->post('id');
		
		$id = null;
		if($item_id)
			$id = $this->PartnerRent_m->update_vehicle($item_id,$data);
		else
			$id = $this->PartnerRent_m->add_vehicle($account_id,$data);
		
		if($id)
		{
				$photos = $this->post("photos");
				if($photos != null)
				{
					for($i = 0; $i < count($photos);$i++)
					{	
						$this->PartnerRent_m->add_vehicle_photo($id,$photos[$i]);
					}
				}
				
			
		}
		
		if($item_id)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil Mengubah Kendaraan",
			);
			$this->response($response,200);
		}else
		{
			$response = array(
			'status' => true,
			'message' => "Berhasil Menambahkan Kendaraan",
		);
		$this->response($response,200);
		}
	}
	
	public function upload_vehicle_image_post()
	{
		$config['upload_path'] = FCPATH . 'data/vehicles';
		$config['allowed_types'] = '*';
		$config['max_size'] = '200480';
		$config['overwrite'] = false;
		$this->load->library('upload', $config);
	
		if ($this->upload->do_upload('photo')) {
			$photo = $this->upload->data("file_name");
			
			$config['image_library']='gd2';
			$config['source_image']= FCPATH . 'data/vehicles/'.$photo;
			$config['create_thumb']= TRUE;
			$config['master_dim'] = 'width';
			$config['maintain_ratio']= TRUE;
			//$config['quality']= '80%';
			$config['width']= 600;
			$config['height']= 1;
			$config['new_image']= FCPATH . 'data/vehicles/'.$photo;
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
			
			thumb_image(FCPATH . 'data/vehicles/'.$photo, FCPATH . 'data/vehicles/thumb_rentone_'.$photo, 250);
					
			$photo = resize_image(FCPATH . 'data/vehicles/'.$photo, FCPATH . 'data/vehicles/rentone_'.$photo, 600, 1, TRUE);
			
			//$this->PartnerRent_m->upload_vehicle_photo($photo);
			
			$response = array(
				'status' => true,
				'message' => "Berhasil Menambahkan Foto Kendaraan",
				'filename' => $photo,
			);
			$this->response($response,200);
		}else{
			$response = array(
				'status' => false,
				'message' => "Gagal Menambahkan Foto Kendaraan.\nError:".$this->upload->display_errors(),
			);
			$this->response($response,200);
		}
	}
	
	public function list_vehicle_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
			
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
			'sort' => $this->post('sort'),
			'status' => $this->post('status'),
			'min_passenger' => $this->post('min_passenger'),
			'max_passenger' => $this->post('max_passenger'),
			'min_price' => $this->post('min_price'),
			'max_price' => $this->post('max_price'),
			'vehicle_functional_type_selected' => $this->post('vehicle_functional_type_selected'),
		);
		$result = $this->PartnerRent_m->list_vehicle($account_id,$param);
		$minmax_price = $this->PartnerRent_m->list_vehicle_min_max_value($account_id);
		$functional_type = $this->PartnerRent_m->get_functional_type();
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicles' => $result,
				'price_min' => $minmax_price->price_min,
				'price_max' => $minmax_price->price_max,
				'functional_type' => $functional_type,
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
			);
			$this->response($response,200);
		}
	}
	
	public function vehicle_detail_post()
	{
		$id = $this->post('id');
		$vehicle = $this->PartnerRent_m->vehicle_detail($id);
		$vehicle->photos = $this->PartnerRent_m->vehicle_photos($id);
		$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicle' => $vehicle,
			);
		$this->response($response,200);
	}
	
	public function delete_vehicle_photo_post()
	{
		$this->load->helper("file");
		$id = $this->post('id');
		$img_filename = $this->PartnerRent_m->delete_vehicle_photo($id);
		
		$path = FCPATH. "data/vehicles/".$img_filename;
		@unlink($path);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
		);
		$this->response($response,200);
	}
	
	public function delete_vehicle_post()
	{
		$id = $this->post('id');
		$this->PartnerRent_m->delete_vehicle($id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",

		);
		$this->response($response,200);
	}
	
	public function config_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$rent_config = $this->PartnerRent_m->config($account_id);
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'rent_config' => $rent_config,

		);
		$this->response($response,200);
	}
	
	public function update_config_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$data = array(
			'force_with_driver' => $this->post('force_with_driver'),
			'force_disable_delivery' => $this->post('force_disable_delivery'),
			'force_disable_pickoff' => $this->post('force_disable_pickoff'),
			'delivery_fee' => $this->post('delivery_fee'),
			'pickoff_fee' => $this->post('pickoff_fee'),
			'max_day_cod' => $this->post('max_day_cod'),
			'overtime_fee' => $this->post('overtime_fee'),
		);
		
		$this->PartnerRent_m->update_config($account_id,$data);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil mengubah pengaturan rental kendaraan",
		);
		$this->response($response,200);
	}
	
	public function list_transaction_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
			'status' => $this->post('status'),
		);
		$result = $this->PartnerRent_m->list_transaction($account_id,$param);
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
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');
		$this->load->model('RentVehicle_m');
		
		$id = $this->post('id');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
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
		
		$is_reviewed = $this->PartnerRent_m->is_review_submit($transaction_detail->id,$vehicle->account_id);
		
		$response = array(
			'status' => true,
			'message' => "Berhasil",
			'customer' => $this->Customer_m->customer_info($transaction_detail->account_id),
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
		
		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$this->RentVehicle_m->update_transaction_status($id,10);
		$status = $this->RentVehicle_m->get_transaction_status_name(10);
		
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
		$notif = array("title" => "Rental Kendaraan", "text" => "Berhasil membatalkan rental kendaraan #".$id."",'android_channel_id' => 2, 'sound' => 'default');
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
		$notif = array("title" => "Rental Kendaraan", "text" => "Transaksi #".$id." telah dibatalkan oleh mitra.",'android_channel_id' => 2, 'sound' => 'default');
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
			$this->Customer_m->increase_balance($transaction_detail->account_id,$transaction_detail->total_payment);
			
			$response = array(
				'status' => true,
				'message' => "Berhasil membatalkan pesanan. Biaya Admin akan dikembalikan ke saldo mitra",
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
		
		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
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
			'message' => "Berhasil mengubah status pesanan",
		);
		$this->response($response,200);
	}
	
	public function done_transaction_post()
	{
		$this->load->model('RentVehicle_m');
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');
		
		$id = $this->post('id');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$now = new DateTime(date('Y-m-d H:i:s')); 
		$end_date = new DateTime($transaction_detail->end_date);
		
		$hour_overtime = 0;
		if($now > $end_date)
		{
			$interval = $now->diff($end_date);
			$hour_overtime = $interval->h;
			$hour_overtime = $hour_overtime + ($interval->days*24);
		}
		
		$data = array(
			'overtime' => ($hour_overtime > 0)?1:0,
			'overtime_hour' => $hour_overtime,
			'total_overtime_fee' => ($hour_overtime * $transaction_detail->overtime_fee),
		);
		$this->RentVehicle_m->update_transaction($id,$data);
		$this->RentVehicle_m->update_transaction_status($id,8);
		$status = $this->RentVehicle_m->get_transaction_status_name(8);
		
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
		
		$data_reward = array(
			'account_id' => $transaction_detail->account_id,
			'transaction_id' => $transaction_detail->id,
			'point_debit' => $this->Basic_m->get_config_value('transaction_point_reward_customer'),
			'description' => 'Bonus Point transaksi #'.$transaction_detail->id,
		);
		$this->Basic_m->insert_point_reward($data_reward);
		
		$data_reward = array(
			'account_id' => $vehicle->account_id,
			'point_debit' => $this->Basic_m->get_config_value('transaction_point_reward_partner'),
			'transaction_id' => $transaction_detail->id,
			'description' => 'Bonus Point transaksi #'.$transaction_detail->id,
		);
		
		$this->Basic_m->insert_point_reward($data_reward);
		
		// partner reward calculation
		$this->load->model('PartnerReward_m');
		
		$list_scope = $this->PartnerReward_m->list_scope();
		
		for($j = 0; $j < count($list_scope);$j++)
		{
			//kalkulasi reward harian , 1 adalah rental kendaraan , scope 1 adalah harian
			$start_date = date("Y-m-d", strtotime($list_scope[$j]->start));
			$end_date = date("Y-m-d", strtotime($list_scope[$j]->end));
			$list_reward = $this->PartnerReward_m->list_reward(1,$list_scope[$j]->id);
			$transction_success = $this->PartnerRent_m->count_transaction_success($vehicle->account_id,$start_date,$end_date);
			
			if($list_reward > 0)
			{
				for($i = 0; $i < count($list_reward);$i++)
				{
					
					if($list_reward[$i]->target <= $transction_success && !$this->PartnerReward_m->is_reward_added($vehicle->account_id,$list_reward[$i]->id,$start_date,$end_date) && $list_reward[$i]->status == 1)
					{
						if($list_reward[$i]->reward_type == 1)
						{
							$partner_reward = array(
								'account_id' => $vehicle->account_id,
								'reward_id' => $list_reward[$i]->id,
								'processed' => 1,
							);
							$this->PartnerReward_m->add_reward($partner_reward);
							
							$data_reward = array(
								'account_id' => $vehicle->account_id,
								'point_debit' => $list_reward[$i]->point_reward,
								'description' => 'Bonus Point Target '.$list_reward[$i]->target.' Transaksi '.$list_reward[$i]->title,
							);
							
							$this->Basic_m->insert_point_reward($data_reward);
							
							//notifikasi
							//kirim ke mitra
							$this->fcm->clearRecepients();
							$this->fcm->addRecepient($this->Customer_m->get_token($vehicle->account_id));
							$data_payload = array(
								'data_type' => 'partner_reward',
								'id' => $id,
							);
							$this->fcm->setData($data_payload);
							$notif = array("title" => "Hadiah untuk Mitra", "text" => $list_reward[$i]->title,'android_channel_id' => 4, 'sound' => 'default');
							$this->fcm->setNotification($notif);
							$this->fcm->send();
						}else if($list_reward[$i]->reward_type == 2)
						{
							$partner_reward = array(
								'account_id' => $vehicle->account_id,
								'reward_id' => $list_reward[$i]->id,
								'processed' => 0,
							);
							$partner_reward_id = $this->PartnerReward_m->add_reward($partner_reward);
							
							//notifikasi
							//kirim ke mitra
							$this->fcm->clearRecepients();
							$this->fcm->addRecepient($this->Customer_m->get_token($vehicle->account_id));
							$data_payload = array(
								'data_type' => 'partner_reward',
								'id' => $id,
							);
							$this->fcm->setData($data_payload);
							$notif = array("title" => "Hadiah untuk Mitra", "text" => "Klaim Sekarang  ".$list_reward[$i]->title,'android_channel_id' => 4, 'sound' => 'default');
							$this->fcm->setNotification($notif);
							$this->fcm->send();
							
							//kirim ke semua admin
							$this->fcm->clearRecepients();
							$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
							$data_payload = array(
								'data_type' => 'partner_reward_claim',
								'id' => $id,
								'link_action' => base_url().'admin/partnerReward/list_claim'
							);
							$this->fcm->setData($data_payload);
							$notif = array("title" => "Permintaan Klaim Hadiah", "body" => "ID #".$partner_reward_id,'android_channel_id' => 3, 'sound' => 'default');
							$this->fcm->setNotification($notif);
							$this->fcm->send();
						}
					}
				}
			}
		}
		
		//end partner reward calculation
		
		
		//start agent commision 
		$this->load->model('Agent_m');
		$this->load->model('Partner_m');
		$partner = $this->Partner_m->detail($vehicle->account_id);
		$agent_id = $partner->agent_id;
		
		if($agent_id)
		{
			$list_commision = $this->Agent_m->get_list_commision();
			$count = $this->PartnerRent_m->count_vehicle_transaction_success($transaction_detail->item_id);
			
			foreach($list_commision as $val)
			{
				if($count >= $val->min_target && $count <= $val->max_target)
				{
					//ambil data komisi, lalu looping berdasarkan target, dapatkan presentase dan nilai komisi.
					//increase nilai balance agent berdasarkan nilai komisi.
					//simpan ke history_agent 
					$agent_commision = ( ($val->percentage / 100) * $transaction_detail->admin_fee);
					
					$this->Agent_m->increase_balance($agent_id,$agent_commision);
					$data = array(
						'account_id' => $agent_id,
						'feature_id' => 1,
						'transaction_id' => $transaction_detail->id,
						'description' => "Komisi ".number_format($agent_commision,2,",",".")."(".$val->percentage."% ) dari Transaksi Rental Kendaraan #".$transaction_detail->id." : ".$vehicle->title." ( Mitra : ".$partner->company_name." )",
						'percentage' => $val->percentage,
						'value' => $agent_commision,
						
					);
					$this->Agent_m->add_history_transaction($data);
					break;
				}
			}
		}
		
		//end agent commision
		
		if($transaction_detail->cash_on_delivery == 1)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil menyelesaikan pesanan. Pastikan mitra mendapat denda keterlambatan dari pelanggan jika tersedia.",
			);
			$this->response($response,200);
		}else
		{
			$this->Customer_m->increase_balance($vehicle->account_id, ($transaction_detail->total_payment - $transaction_detail->admin_fee));
			
			$response = array(
				'status' => true,
				'message' => "Berhasil menyelesaikan pesanan. Pembayaran yang telah dipotong biaya admin akan ditambahkan ke saldo mitra",
			);
			$this->response($response,200);
		}
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
		
		$this->PartnerRent_m->post_review($data);
		$response = array(
			'status' => true,
			'message' => "Berhasil mengirim ulasan",
		);
		$this->response($response,200);
	}
	
	public function list_promote_vehicle_post()
	{
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
			
		$param = array(
			'page' => $this->post('page'),
			'limit' => $this->post('limit'),
		);
		$result = $this->PartnerRent_m->list_promote_vehicle($account_id,$param);
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'promotes' => $result,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'promotes' => [],
			);
			$this->response($response,200);
		}
	}
	
	public function get_input_promote_config_post()
	{
		$this->load->model('Basic_m');
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
			
		$param = array(
			'sort' => 1
		);
		$result = $this->PartnerRent_m->list_vehicle($account_id,$param);
		$price_per_day = $this->Basic_m->get_config_value('promote_price_per_day_rent_vehicle');
		if($result)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicles' => $result,
				'info' => $this->Basic_m->get_config_value('promote_info_rent_vehicle'),
				'price_per_day' => $price_per_day,
			);
			$this->response($response,200);
		}else
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil",
				'vehicles' => [],
				'info' => $this->Basic_m->get_config_value('promote_info_rent_vehicle'),
				'price_per_day' => $price_per_day,
			);
			$this->response($response,200);
		}
	}
	
	public function post_promote_post()
	{
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		$balance = $this->Customer_m->balance($account_id);
		
		$start_date = new DateTime($this->post('start_date')); 
		$end_date = new DateTime($this->post('end_date'));
		
		$interval = $start_date->diff($end_date);
		$day_interval = ($interval->days)+1;
		
		$price_per_day = $this->Basic_m->get_config_value('promote_price_per_day_rent_vehicle');
		$total_payment = $price_per_day * $day_interval;
		
		if($balance->balance < $total_payment)
		{
			$response = array(
				'status' => false,
				'message' => "Saldo anda tidak mencukupi untuk melakukan pemesanan promosi rental kendaraan. Segera lakukan pengisian saldo",
				'days' => $day_interval,
				'balance' => $balance,
			);
			$this->response($response,200);
		}else
		{
			$data = array(
				'item_id' => $this->post('item_id'),
				'start_date' => $this->post('start_date'),
				'end_date' => $this->post('end_date'),
				'days' => $day_interval,
				'price_per_day' => $price_per_day,
				'total_payment' => $total_payment,
			);
			
			$this->PartnerRent_m->add_promote($account_id,$data);
			
			$this->Customer_m->decrease_balance($account_id,$total_payment);
			$response = array(
				'status' => true,
				'message' => "Berhasil",
			);
			$this->response($response,200);
		}
	}
	
	public function cancel_promote_post()
	{
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
		
		$account_id = $this->get_detail_key($this->input->get_request_header("key"))->account_id;
		
		$id = $this->post('id');
			
		$promote = $this->PartnerRent_m->promote_detail($id);
		if($promote->status == 0 || $promote->status == 1)
		{
			$now_date = new DateTime(date('Y-m-d'));
			$start_date = new DateTime($promote->start_date); 
			$end_date = new DateTime($promote->end_date);
			
			$start_interval = ($now_date->diff($start_date)->days);
			$end_interval = ($now_date->diff($end_date)->days);
			
			$interval = 0;
			
			if($now_date < $start_date)
				$interval = $promote->days;
			else if ($now_date >= $start_date && $now_date <= $end_date)
				$interval = $end_interval;
			else 
				$interval = 0;
				
			$total_return = $promote->price_per_day * $interval;
			
			$data = array(
				'canceled_total_return' => $total_return,
				'status' => 3,
			);
			$this->PartnerRent_m->update_promote($id,$data);
			$this->Customer_m->increase_balance($account_id,$total_return);
			$response = array(
				'status' => true,
				'message' => "Berhasil membatalkan promosi. Sisa ".$interval." Hari sebesar Rp.".number_format($total_return,2,",",".")." telah dikembalikan ke saldo.",
			);
			$this->response($response,200);
		}else if($promote->status == 2)
		{
			$response = array(
				'status' => false,
				'message' => "Promosi yang sudah selesai tidak bisa dibatalkan.",
			);
			$this->response($response,200);
		}else if($promote->status == 3)
		{
			$response = array(
				'status' => false,
				'message' => "Promosi ini sudah dibatalkan sebelumnya.",
			);
			$this->response($response,200);
		}
	}
}
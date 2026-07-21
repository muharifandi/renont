<?php defined('BASEPATH') OR exit('No direct script access allowed');

class RentVehicle extends AdminController
{

	public function __construct()
	{
		parent::__construct();
		
		
		$this->load->model('AdminRentVehicle_m');
		
	}
	
	public function index()
	{
		redirect('admin/dashboard', 'refresh');
	}
	
	public function list_functional_type()
	{
		$this->show('rentvehicle_functional_type_list',$result,TRUE);
	}
	
	public function get_list_functional_type()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_functional_type($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_functional_type_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_functional_type_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_functional_type($id)
	{
		$functional_type = $this->AdminRentVehicle_m->get_functional_type($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Tipe Fungsi',
				'data' => $functional_type,
			);
		echo json_encode($result);
	}
	public function post_functional_type($id = NULL)
	{
		$param = array(
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_functional_type($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Tipe Fungsi',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_functional_type($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Tipe Fungsi',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_functional_type($id)
	{
		$this->AdminRentVehicle_m->delete_functional_type($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Tipe Fungsi',
		);
		echo json_encode($result);
	}
	
	public function list_brand()
	{
		$result = array(
			'functional_type' => $this->AdminRentVehicle_m->get_input_brand_parameter()['functional_type'],
		);
		$this->show('rentvehicle_brand_list',$result,TRUE);
	}
	
	public function get_list_brand()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_brand($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_brand_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_brand_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_brand($id)
	{
		$brand = $this->AdminRentVehicle_m->get_brand($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Merek',
				'data' => $brand,
			);
		echo json_encode($result);
	}
	public function post_brand($id = NULL)
	{
		$param = array(
			'functional_type' => $this->input->post('functional_type'),
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_brand($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Merek',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_brand($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Merek',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_brand($id)
	{
		$this->AdminRentVehicle_m->delete_brand($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Merek',
		);
		echo json_encode($result);
	}
	
	public function list_vehicle_model()
	{
		$result = array(
			'brand' => $this->AdminRentVehicle_m->get_input_vehicle_model_parameter()['brand'],
		);
		$this->show('rentvehicle_vehicle_model_list',$result,TRUE);
	}
	
	public function get_list_vehicle_model()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_vehicle_model($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_vehicle_model_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_vehicle_model_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_vehicle_model($id)
	{
		$brand = $this->AdminRentVehicle_m->get_vehicle_model($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Model Kendaraan',
				'data' => $brand,
			);
		echo json_encode($result);
	}
	public function post_vehicle_model($id = NULL)
	{
		$param = array(
			'brand_id' => $this->input->post('brand_id'),
			'name' => $this->input->post('name'),
		);
		
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_vehicle_model($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Model Kendaraan',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_vehicle_model($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Model Kendaraan',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_vehicle_model($id)
	{
		$this->AdminRentVehicle_m->delete_vehicle_model($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Model Kendaraan',
		);
		echo json_encode($result);
	}
	
	public function list_vehicle_type()
	{
		$result = array(
			'functional_type' => $this->AdminRentVehicle_m->get_input_vehicle_type_parameter()['functional_type'],
		);
		$this->show('rentvehicle_vehicle_type_list',$result,TRUE);
	}
	
	public function get_list_vehicle_type()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_vehicle_type($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_vehicle_type_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_vehicle_type_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_vehicle_type($id)
	{
		$brand = $this->AdminRentVehicle_m->get_vehicle_type($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Jenis Kendaraan',
				'data' => $brand,
			);
		echo json_encode($result);
	}
	public function post_vehicle_type($id = NULL)
	{
		$param = array(
			'functional_type' => $this->input->post('functional_type'),
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_vehicle_type($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Jenis Kendaraan',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_vehicle_type($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Jenis Kendaraan',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_vehicle_type($id)
	{
		$this->AdminRentVehicle_m->delete_vehicle_type($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Jenis Kendaraan',
		);
		echo json_encode($result);
	}
	
	public function list_color()
	{
		$this->show('rentvehicle_color_list',$result,TRUE);
	}
	
	public function get_list_color()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_color($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_color_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_color_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_color($id)
	{
		$color = $this->AdminRentVehicle_m->get_color($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Warna',
				'data' => $color,
			);
		echo json_encode($result);
	}
	public function post_color($id = NULL)
	{
		$param = array(
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('value')!="")
			$param['value'] = $this->input->post('value');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_color($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Warna',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_color($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Warna',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_color($id)
	{
		$this->AdminRentVehicle_m->delete_color($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Warna',
		);
		echo json_encode($result);
	}
	
	public function list_transmition_type()
	{
		$result = array(
			'functional_type' => $this->AdminRentVehicle_m->get_input_brand_parameter()['functional_type'],
		);
		$this->show('rentvehicle_transmition_type_list',$result,TRUE);
	}
	
	public function get_list_transmition_type()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_transmition_type($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_transmition_type_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_transmition_type_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_transmition_type($id)
	{
		$brand = $this->AdminRentVehicle_m->get_transmition_type($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Jenis Transmisi',
				'data' => $brand,
			);
		echo json_encode($result);
	}
	public function post_transmition_type($id = NULL)
	{
		$param = array(
			'functional_type' => $this->input->post('functional_type'),
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_transmition_type($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Jenis Transmisi',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_transmition_type($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Jenis Transmisi',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_transmition_type($id)
	{
		$this->AdminRentVehicle_m->delete_transmition_type($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Jenis Transmisi',
		);
		echo json_encode($result);
	}
	
	public function list_driven_type()
	{
		$result = array(
			'functional_type' => $this->AdminRentVehicle_m->get_input_driven_type_parameter()['functional_type'],
		);
		$this->show('rentvehicle_driven_type_list',$result,TRUE);
	}
	
	public function get_list_driven_type()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_driven_type($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_driven_type_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_driven_type_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_driven_type($id)
	{
		$brand = $this->AdminRentVehicle_m->get_driven_type($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Jenis Penggerak',
				'data' => $brand,
			);
		echo json_encode($result);
	}
	public function post_driven_type($id = NULL)
	{
		$param = array(
			'functional_type' => $this->input->post('functional_type'),
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_driven_type($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Jenis Penggerak',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_driven_type($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Jenis Penggerak',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_driven_type($id)
	{
		$this->AdminRentVehicle_m->delete_driven_type($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Jenis Penggerak',
		);
		echo json_encode($result);
	}
	
	public function list_fuel()
	{
		$this->show('rentvehicle_fuel_list',$result,TRUE);
	}
	
	public function get_list_fuel()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_fuel($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_fuel_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_fuel_filtered($param),
		);
		echo json_encode($result);
	
	}
	
	public function get_fuel($id)
	{
		$functional_type = $this->AdminRentVehicle_m->get_fuel($id);
		$result = array(
				'status' => true,
				'message' => 'Berhasil mengambil Jenis Bahan Bakar',
				'data' => $functional_type,
			);
		echo json_encode($result);
	}
	public function post_fuel($id = NULL)
	{
		$param = array(
			'name' => $this->input->post('name'),
		);
		
		if($this->input->post('icon')!="")
			$param['icon'] = $this->input->post('icon');
		if($id == NULL)
		{
			$this->AdminRentVehicle_m->add_fuel($param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil menambahkan Jenis Bahan Bakar',
			);
			echo json_encode($result);
		}else
		{
			$this->AdminRentVehicle_m->edit_fuel($id,$param);
			$result = array(
				'status' => true,
				'message' => 'Berhasil mengubah Jenis Bahan Bakar',
			);
			echo json_encode($result);
		}
	}
	
	public function delete_fuel($id)
	{
		$this->AdminRentVehicle_m->delete_fuel($id);
		$result = array(
			'status' => true,
			'message' => 'Berhasil menghapus Jenis Bahan Bakar',
		);
		echo json_encode($result);
	}
	
	public function list_vehicle()
	{
		$list_active_status = $this->AdminRentVehicle_m->get_status();
		$result = array(
			'list_active_status' => $list_active_status, 
		);
		$this->show('vehicle_list',$result,TRUE);
	}
	
	public function get_list_vehicle()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_vehicle($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_vehicle_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_vehicle_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function change_status_vehicle($id)
	{
		$status_id = $this->input->post('status_id');
		$this->AdminRentVehicle_m->update_status_vehicle($id,$status_id);
		
		$result = array(
			'status' => true,
			'message' => $id.' Status diubah',
		);
		echo json_encode($result);
	}
	
	public function list_vehicle_transaction()
	{
		$list_active_status = $this->AdminRentVehicle_m->get_status();
		$result = array(
			'list_active_status' => $list_active_status, 
		);
		$this->show('transaction_vehicle_list',$result,TRUE);
	}
	
	public function get_list_vehicle_transaction()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_vehicle_transaction($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_vehicle_transaction_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_vehicle_transaction_filtered($param),
		);
		echo json_encode($result);
	}
	
	public function cancel_vehicle_transaction()
	{	
		$this->load->model('api/PartnerRent_m');
		$this->load->model('api/Customer_m');
		$this->load->model('api/RentVehicle_m');
		
		$id = $this->input->post('id');
		
		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$this->RentVehicle_m->update_transaction_status($id,12);
		$status = $this->RentVehicle_m->get_transaction_status_name(12);
		
		$timeline = array(
			'transaction_id' => $id,
			'title' => $status->name,
			'description' => $status->name,
			
		);
		$this->RentVehicle_m->add_timeline_transaction($timeline);
		
		if($transaction_detail->cash_on_delivery == 1)
		{
			$this->Customer_m->increase_balance($vehicle->account_id,$transaction_detail->admin_fee);
			$response = array(
				'status' => true,
				'message' => "Berhasil membatalkan pemesanan.",
			);
			echo json_encode($response);
		}else
		{
			$this->Customer_m->increase_balance($transaction_detail->account_id,$transaction_detail->total_payment);
			
			$response = array(
				'status' => true,
				'message' => "Berhasil membatalkan pesanan. Biaya Admin akan dikembalikan ke saldo mitra",
			);
			echo json_encode($response);
		}
	}
	
	function finish_vehicle_transaction()
	{
		$this->load->model('api/PartnerRent_m');
		$this->load->model('api/RentVehicle_m');
		$this->load->model('api/Customer_m');
		$this->load->model('api/Basic_m');
		
		$id = $this->input->post('id');
		
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
		
		if($transaction_detail->cash_on_delivery == 1)
		{
			$response = array(
				'status' => true,
				'message' => "Berhasil menyelesaikan pesanan. Pastikan mitra mendapat denda keterlambatan dari pelanggan jika tersedia.",
			);
			echo json_encode($response);
		}else
		{
			$this->Customer_m->increase_balance($vehicle->account_id, ($transaction_detail->total_payment - $transaction_detail->admin_fee));
			
			$response = array(
				'status' => true,
				'message' => "Berhasil menyelesaikan pesanan. Pembayaran yang telah dipotong biaya admin akan ditambahkan ke saldo mitra",
			);
			echo json_encode($response);
		}
	}
	
	public function list_promote_vehicle_transaction()
	{
		$list_active_status = $this->AdminRentVehicle_m->get_status();
		$result = array(
			'list_active_status' => $list_active_status, 
		);
		$this->show('transaction_promote_vehicle_list',$result,TRUE);
	}
	
	public function get_list_promote_vehicle_transaction()
	{
		$param = array(
			'limit' => array('start' => $this->input->get('start'),'length' => $this->input->get('length')),
			'search' => $this->input->get('search')['value'],
		);
		
		$result = array(
			'draw' => $this->input->get('draw'),
			'data' => $this->AdminRentVehicle_m->get_list_promote_vehicle_transaction($param),
			"recordsTotal" => $this->AdminRentVehicle_m->get_total_list_promote_vehicle_transaction_unfiltered($param),
			"recordsFiltered" => $this->AdminRentVehicle_m->get_total_list_promote_vehicle_transaction_filtered($param),
		);
		echo json_encode($result);
	}
}
<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Partner-side vehicle inventory, booking management, and promotion resource.
 */
class PartnerRent extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('PartnerRent_m');
		$this->load->helper('image_manipulation');
	}

	public function index_get()
	{
		$this->ok(null, 'Partner Rent API — RentOn');
	}

	// ---------------------------------------------------------------------
	// Vehicle input config (lookups for the "add vehicle" form)
	// ---------------------------------------------------------------------

	public function functional_types_get()
	{
		$this->ok(['type' => $this->PartnerRent_m->get_functional_type()]);
	}

	/** GET api/partnerRent/vehicle_input_config?functional_type=.. */
	public function vehicle_input_config_get()
	{
		$functional_type = $this->get('functional_type');
		$this->ok([
			'vehicle_type' => $this->PartnerRent_m->get_vehicle_type($functional_type),
			'brand' => $this->PartnerRent_m->get_brand($functional_type),
			'color' => $this->PartnerRent_m->get_color(),
			'transmition_type' => $this->PartnerRent_m->get_transmition_type($functional_type),
			'driven_type' => $this->PartnerRent_m->get_driven_type($functional_type),
			'fuel' => $this->PartnerRent_m->get_fuel(),
		]);
	}

	/** GET api/partnerRent/vehicle_models?brand_id=.. */
	public function vehicle_models_get()
	{
		$this->ok(['data' => $this->PartnerRent_m->get_vehicle_model($this->get('brand_id'))]);
	}

	// ---------------------------------------------------------------------
	// Vehicles resource
	// ---------------------------------------------------------------------

	private function _vehicle_payload()
	{
		return [
			'title' => $this->post('title'),
			'vehicle_type' => $this->post('vehicle_type'),
			'brand_id' => $this->post('brand_id'),
			'vehicle_model' => $this->post('vehicle_model'),
			'max_passenger' => $this->post('max_passenger'),
			'max_baggage' => $this->post('max_baggage'),
			'year' => $this->post('year'),
			'color_id' => $this->post('color_id'),
			'transmition_type' => $this->post('transmition_type'),
			'driven_type' => $this->post('driven_type'),
			'fuel_type' => $this->post('fuel_type'),
			'price' => $this->post('price'),
			'price_with_driver_basic' => $this->post('price_with_driver_basic'),
			'price_with_driver_full' => $this->post('price_with_driver_full'),
			'with_driver' => $this->post('with_driver'),
			'delivered' => $this->post('delivered'),
			'pickoff' => $this->post('pickoff'),
			'functional_type' => $this->post('functional_type'),
			'status' => $this->post('status'),
		];
	}

	private function _attach_photos($item_id)
	{
		$photos = $this->post('photos');
		if (!empty($photos)) {
			foreach ($photos as $photo) {
				$this->PartnerRent_m->add_vehicle_photo($item_id, $photo);
			}
		}
	}

	/** POST api/partnerRent/vehicles -- create. body: vehicle fields + photos[] (filenames from vehicle_photos_post) */
	public function vehicles_post()
	{
		$account = $this->require_auth();
		$id = $this->PartnerRent_m->add_vehicle($account->id, $this->_vehicle_payload());
		if ($id) {
			$this->_attach_photos($id);
		}
		$this->created(['id' => (int) $id], 'Berhasil Menambahkan Kendaraan');
	}

	/** PUT api/partnerRent/vehicles/{id} */
	public function vehicles_put($id = null)
	{
		$this->require_auth();
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->PartnerRent_m->update_vehicle($id, $this->_vehicle_payload());
		$this->_attach_photos($id);
		$this->ok(null, 'Berhasil Mengubah Kendaraan');
	}

	/**
	 * GET api/partnerRent/vehicles          -> list (query: page, limit, sort, status, min/max_passenger, min/max_price, vehicle_functional_type_selected)
	 * GET api/partnerRent/vehicles/{id}     -> detail
	 */
	public function vehicles_get($id = null)
	{
		$account = $this->require_auth();

		if (!empty($id)) {
			$vehicle = $this->PartnerRent_m->vehicle_detail($id);
			if (!$vehicle) return $this->not_found('Kendaraan tidak ditemukan');
			$vehicle->photos = $this->PartnerRent_m->vehicle_photos($id);
			return $this->ok(['vehicle' => $vehicle]);
		}

		$param = [
			'page' => (int) ($this->get('page') ?: 1),
			'limit' => min((int) ($this->get('limit') ?: 10), 50),
			'sort' => $this->get('sort'),
			'status' => $this->get('status'),
			'min_passenger' => $this->get('min_passenger'),
			'max_passenger' => $this->get('max_passenger'),
			'min_price' => $this->get('min_price'),
			'max_price' => $this->get('max_price'),
			'vehicle_functional_type_selected' => $this->get('vehicle_functional_type_selected'),
		];

		$result = $this->PartnerRent_m->list_vehicle($account->id, $param);
		$minmax_price = $this->PartnerRent_m->list_vehicle_min_max_value($account->id);

		$this->ok([
			'vehicles' => $result ?: [],
			'price_min' => $minmax_price->price_min,
			'price_max' => $minmax_price->price_max,
			'functional_type' => $this->PartnerRent_m->get_functional_type(),
		]);
	}

	/** DELETE api/partnerRent/vehicles/{id} */
	public function vehicles_delete($id = null)
	{
		$this->require_auth();
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->PartnerRent_m->delete_vehicle($id);
		$this->ok(null, 'Berhasil menghapus kendaraan');
	}

	/** POST api/partnerRent/vehicle_photos (multipart: photo) */
	public function vehicle_photos_post()
	{
		$this->require_auth();

		$config = ['upload_path' => FCPATH.'data/vehicles', 'allowed_types' => 'jpg|jpeg|png', 'max_size' => '10240', 'overwrite' => false];
		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('photo')) {
			return $this->fail("Gagal Menambahkan Foto Kendaraan.\nError:".$this->upload->display_errors(), 422);
		}

		$photo = $this->upload->data('file_name');
		$path = FCPATH.'data/vehicles/'.$photo;

		$image_lib_config = [
			'image_library' => 'gd2', 'source_image' => $path, 'create_thumb' => TRUE,
			'master_dim' => 'width', 'maintain_ratio' => TRUE, 'width' => 600, 'height' => 1, 'new_image' => $path,
		];
		$this->load->library('image_lib', $image_lib_config);
		$this->image_lib->resize();

		thumb_image($path, FCPATH.'data/vehicles/thumb_rentone_'.$photo, 250);
		$photo = resize_image($path, FCPATH.'data/vehicles/rentone_'.$photo, 600, 1, TRUE);

		$this->created(['filename' => $photo], 'Berhasil Menambahkan Foto Kendaraan');
	}

	/** DELETE api/partnerRent/vehicle_photos/{id} */
	public function vehicle_photos_delete($id = null)
	{
		$this->require_auth();
		$this->load->helper('file');

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$img_filename = $this->PartnerRent_m->delete_vehicle_photo($id);
		@unlink(FCPATH.'data/vehicles/'.$img_filename);

		$this->ok(null, 'Berhasil menghapus foto kendaraan');
	}

	// ---------------------------------------------------------------------
	// Partner rental config
	// ---------------------------------------------------------------------

	public function config_get()
	{
		$account = $this->require_auth();
		$this->ok(['rent_config' => $this->PartnerRent_m->config($account->id)]);
	}

	/** PUT api/partnerRent/config body: {force_with_driver, force_disable_delivery, force_disable_pickoff, delivery_fee, pickoff_fee, max_day_cod, overtime_fee} */
	public function config_put()
	{
		$account = $this->require_auth();
		$this->PartnerRent_m->update_config($account->id, [
			'force_with_driver' => $this->put('force_with_driver'),
			'force_disable_delivery' => $this->put('force_disable_delivery'),
			'force_disable_pickoff' => $this->put('force_disable_pickoff'),
			'delivery_fee' => $this->put('delivery_fee'),
			'pickoff_fee' => $this->put('pickoff_fee'),
			'max_day_cod' => $this->put('max_day_cod'),
			'overtime_fee' => $this->put('overtime_fee'),
		]);
		$this->ok(null, 'Berhasil mengubah pengaturan rental kendaraan');
	}

	// ---------------------------------------------------------------------
	// Bookings (transaction_rent_vehicle) resource
	// ---------------------------------------------------------------------

	/**
	 * GET api/partnerRent/bookings          -> list (query: page, limit, status)
	 * GET api/partnerRent/bookings/{id}     -> detail
	 */
	public function bookings_get($id = null)
	{
		$account = $this->require_auth();

		if (!empty($id)) {
			return $this->_booking_detail($account->id, $id);
		}

		$param = [
			'page' => (int) ($this->get('page') ?: 1),
			'limit' => min((int) ($this->get('limit') ?: 10), 50),
			'status' => $this->get('status'),
		];
		$this->ok(['transaction_rent_vehicle' => $this->PartnerRent_m->list_transaction($account->id, $param) ?: []]);
	}

	private function _booking_detail($account_id, $id)
	{
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');
		$this->load->model('RentVehicle_m');

		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}

		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$vehicle->photos = $this->RentVehicle_m->vehicle_photos($transaction_detail->item_id);
		$voucher = $this->Basic_m->get_voucher($transaction_detail->voucher_id);
		$balance = $this->Customer_m->balance($account_id);

		$now = new DateTime(date('Y-m-d H:i:s'));
		$end_date = new DateTime($transaction_detail->end_date);
		$hour_overtime = 0;
		if ($now > $end_date) {
			$interval = $now->diff($end_date);
			$hour_overtime = $interval->h + ($interval->days * 24);
		}

		$is_reviewed = $this->PartnerRent_m->is_review_submit($transaction_detail->id, $vehicle->account_id);

		$this->ok([
			'customer' => $this->Customer_m->customer_info($transaction_detail->account_id),
			'vehicle' => $vehicle,
			'transaction_detail' => $transaction_detail,
			'voucher' => $voucher,
			'balance' => $balance,
			'hour_overtime' => $hour_overtime,
			'feedback' => $is_reviewed ? 0 : 1,
		]);
	}

	/** DELETE api/partnerRent/bookings/{id} -- partner-side cancel */
	public function bookings_delete($id = null)
	{
		$account = $this->require_auth();
		$this->load->model('Customer_m');
		$this->load->model('RentVehicle_m');

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}

		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		if ((int) $vehicle->account_id !== (int) $account->id) {
			return $this->forbidden();
		}

		$this->RentVehicle_m->update_transaction_status($id, 10);
		$status = $this->RentVehicle_m->get_transaction_status_name(10);
		$this->RentVehicle_m->add_timeline_transaction(['transaction_id' => $id, 'title' => $status->name, 'description' => $status->name]);

		$this->_notify_booking($id, $vehicle->account_id, $transaction_detail->account_id,
			'Berhasil membatalkan rental kendaraan #'.$id, 'Transaksi #'.$id.' telah dibatalkan oleh mitra.');

		if ($transaction_detail->cash_on_delivery == 1) {
			$this->Customer_m->increase_balance($vehicle->account_id, $transaction_detail->admin_fee);
			$this->ok(null, 'Berhasil membatalkan pemesanan.');
		} else {
			$this->Customer_m->increase_balance($transaction_detail->account_id, $transaction_detail->total_payment);
			$this->ok(null, 'Berhasil membatalkan pesanan. Biaya Admin akan dikembalikan ke saldo mitra');
		}
	}

	/** PUT api/partnerRent/booking_status/{id} body: {status} */
	public function booking_status_put($id = null)
	{
		$this->require_auth();
		$this->load->model('Customer_m');
		$this->load->model('RentVehicle_m');

		$new_status = $this->put('status');
		if (empty($id) || empty($new_status)) {
			return $this->validation_error(['id' => 'wajib diisi', 'status' => 'wajib diisi']);
		}

		$this->RentVehicle_m->update_transaction_status($id, $new_status);
		$status = $this->RentVehicle_m->get_transaction_status_name($new_status);
		$this->RentVehicle_m->add_timeline_transaction(['transaction_id' => $id, 'title' => $status->name, 'description' => $status->name]);

		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);

		$this->_notify_booking($id, $vehicle->account_id, $transaction_detail->account_id,
			'Transaksi #'.$id.' : '.$status->name, 'Transaksi #'.$id.' : '.$status->name);

		$this->ok(null, 'Berhasil mengubah status pesanan');
	}

	private function _notify_booking($id, $partner_account_id, $customer_account_id, $partner_text, $customer_text)
	{
		$this->load->model('Customer_m');
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		$this->fcm->addRecepient($this->Customer_m->get_token($partner_account_id));
		$this->fcm->setData(['data_type' => 'partner_rent_vehicle_transaction', 'id' => $id]);
		$this->fcm->setNotification(['title' => 'Rental Kendaraan', 'text' => $partner_text, 'android_channel_id' => 2, 'sound' => 'default']);
		$this->fcm->send();

		$this->fcm->clearRecepients();
		$this->fcm->addRecepient($this->Customer_m->get_token($customer_account_id));
		$this->fcm->setData(['data_type' => 'customer_rent_vehicle_transaction', 'id' => $id]);
		$this->fcm->setNotification(['title' => 'Rental Kendaraan', 'text' => $customer_text, 'android_channel_id' => 2, 'sound' => 'default']);
		$this->fcm->send();
	}

	/** PUT api/partnerRent/booking_done/{id} -- mark booking complete, settle payment, rewards & commission */
	public function booking_done_put($id = null)
	{
		$account = $this->require_auth();
		$this->load->model('RentVehicle_m');
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}

		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);

		$now = new DateTime(date('Y-m-d H:i:s'));
		$end_date = new DateTime($transaction_detail->end_date);
		$hour_overtime = 0;
		if ($now > $end_date) {
			$interval = $now->diff($end_date);
			$hour_overtime = $interval->h + ($interval->days * 24);
		}

		$this->RentVehicle_m->update_transaction($id, [
			'overtime' => ($hour_overtime > 0) ? 1 : 0,
			'overtime_hour' => $hour_overtime,
			'total_overtime_fee' => $hour_overtime * $transaction_detail->overtime_fee,
		]);
		$this->RentVehicle_m->update_transaction_status($id, 8);
		$status = $this->RentVehicle_m->get_transaction_status_name(8);
		$this->RentVehicle_m->add_timeline_transaction(['transaction_id' => $id, 'title' => $status->name, 'description' => $status->name]);

		$this->_notify_booking($id, $vehicle->account_id, $transaction_detail->account_id,
			'Transaksi #'.$id.' : '.$status->name, 'Transaksi #'.$id.' : '.$status->name);

		$this->Basic_m->insert_point_reward([
			'account_id' => $transaction_detail->account_id,
			'transaction_id' => $transaction_detail->id,
			'point_debit' => $this->Basic_m->get_config_value('transaction_point_reward_customer'),
			'description' => 'Bonus Point transaksi #'.$transaction_detail->id,
		]);
		$this->Basic_m->insert_point_reward([
			'account_id' => $vehicle->account_id,
			'point_debit' => $this->Basic_m->get_config_value('transaction_point_reward_partner'),
			'transaction_id' => $transaction_detail->id,
			'description' => 'Bonus Point transaksi #'.$transaction_detail->id,
		]);

		$this->_process_partner_rewards($id, $vehicle);
		$this->_process_agent_commission($transaction_detail, $vehicle);

		if ($transaction_detail->cash_on_delivery == 1) {
			$this->ok(null, 'Berhasil menyelesaikan pesanan. Pastikan mitra mendapat denda keterlambatan dari pelanggan jika tersedia.');
		} else {
			$this->Customer_m->increase_balance($vehicle->account_id, $transaction_detail->total_payment - $transaction_detail->admin_fee);
			$this->ok(null, 'Berhasil menyelesaikan pesanan. Pembayaran yang telah dipotong biaya admin akan ditambahkan ke saldo mitra');
		}
	}

	private function _process_partner_rewards($id, $vehicle)
	{
		$this->load->model('PartnerReward_m');
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');

		$list_scope = $this->PartnerReward_m->list_scope();

		foreach ($list_scope as $scope) {
			$start_date = date('Y-m-d', strtotime($scope->start));
			$end_date = date('Y-m-d', strtotime($scope->end));
			$list_reward = $this->PartnerReward_m->list_reward(1, $scope->id);
			$transaction_success = $this->PartnerRent_m->count_transaction_success($vehicle->account_id, $start_date, $end_date);

			if (empty($list_reward)) {
				continue;
			}

			foreach ($list_reward as $reward) {
				$already_added = $this->PartnerReward_m->is_reward_added($vehicle->account_id, $reward->id, $start_date, $end_date);
				if ($reward->target > $transaction_success || $already_added || $reward->status != 1) {
					continue;
				}

				if ($reward->reward_type == 1) {
					$this->PartnerReward_m->add_reward(['account_id' => $vehicle->account_id, 'reward_id' => $reward->id, 'processed' => 1]);
					$this->Basic_m->insert_point_reward([
						'account_id' => $vehicle->account_id,
						'point_debit' => $reward->point_reward,
						'description' => 'Bonus Point Target '.$reward->target.' Transaksi '.$reward->title,
					]);
					$this->_notify_reward($vehicle->account_id, $id, 'Hadiah untuk Mitra', $reward->title);
				} else if ($reward->reward_type == 2) {
					$partner_reward_id = $this->PartnerReward_m->add_reward(['account_id' => $vehicle->account_id, 'reward_id' => $reward->id, 'processed' => 0]);
					$this->_notify_reward($vehicle->account_id, $id, 'Hadiah untuk Mitra', 'Klaim Sekarang  '.$reward->title);
					$this->_notify_admins_reward_claim($id, $partner_reward_id);
				}
			}
		}
	}

	private function _notify_reward($partner_account_id, $id, $title, $text)
	{
		$this->load->model('Customer_m');
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));
		$this->fcm->clearRecepients();
		$this->fcm->addRecepient($this->Customer_m->get_token($partner_account_id));
		$this->fcm->setData(['data_type' => 'partner_reward', 'id' => $id]);
		$this->fcm->setNotification(['title' => $title, 'text' => $text, 'android_channel_id' => 4, 'sound' => 'default']);
		$this->fcm->send();
	}

	private function _notify_admins_reward_claim($id, $partner_reward_id)
	{
		$this->load->model('Basic_m');
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));
		$this->fcm->clearRecepients();
		$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
		$this->fcm->setData(['data_type' => 'partner_reward_claim', 'id' => $id, 'link_action' => base_url().'admin/partnerReward/list_claim']);
		$this->fcm->setNotification(['title' => 'Permintaan Klaim Hadiah', 'body' => 'ID #'.$partner_reward_id, 'android_channel_id' => 3, 'sound' => 'default']);
		$this->fcm->send();
	}

	private function _process_agent_commission($transaction_detail, $vehicle)
	{
		$this->load->model('Agent_m');
		$this->load->model('Partner_m');

		$partner = $this->Partner_m->detail($vehicle->account_id);
		$agent_id = $partner->agent_id;
		if (!$agent_id) {
			return;
		}

		$list_commision = $this->Agent_m->get_list_commision();
		$count = $this->PartnerRent_m->count_vehicle_transaction_success($transaction_detail->item_id);

		foreach ($list_commision as $tier) {
			if ($count >= $tier->min_target && $count <= $tier->max_target) {
				$agent_commision = ($tier->percentage / 100) * $transaction_detail->admin_fee;
				$this->Agent_m->increase_balance($agent_id, $agent_commision);
				$this->Agent_m->add_history_transaction([
					'account_id' => $agent_id,
					'feature_id' => 1,
					'transaction_id' => $transaction_detail->id,
					'description' => 'Komisi '.number_format($agent_commision, 2, ',', '.').'('.$tier->percentage.'% ) dari Transaksi Rental Kendaraan #'.$transaction_detail->id.' : '.$vehicle->title.' ( Mitra : '.$partner->company_name.' )',
					'percentage' => $tier->percentage,
					'value' => $agent_commision,
				]);
				break;
			}
		}
	}

	/** POST api/partnerRent/booking_review/{id} body: {rating, comment} */
	public function booking_review_post($id = null)
	{
		$account = $this->require_auth();
		$rating = $this->post('rating');

		if (empty($id) || empty($rating)) {
			return $this->validation_error(['id' => 'wajib diisi', 'rating' => 'wajib diisi']);
		}

		$this->PartnerRent_m->post_review([
			'transaction_id' => $id,
			'account_id' => $account->id,
			'rating' => $rating,
			'comment' => $this->post('comment'),
		]);

		$this->created(null, 'Berhasil mengirim ulasan');
	}

	// ---------------------------------------------------------------------
	// Promotions resource
	// ---------------------------------------------------------------------

	public function promotions_get()
	{
		$account = $this->require_auth();
		$param = ['page' => (int) ($this->get('page') ?: 1), 'limit' => min((int) ($this->get('limit') ?: 10), 50)];
		$this->ok(['promotes' => $this->PartnerRent_m->list_promote_vehicle($account->id, $param) ?: []]);
	}

	public function promotion_input_config_get()
	{
		$account = $this->require_auth();
		$this->load->model('Basic_m');

		$result = $this->PartnerRent_m->list_vehicle($account->id, ['sort' => 1]);

		$this->ok([
			'vehicles' => $result ?: [],
			'info' => $this->Basic_m->get_config_value('promote_info_rent_vehicle'),
			'price_per_day' => $this->Basic_m->get_config_value('promote_price_per_day_rent_vehicle'),
		]);
	}

	/** POST api/partnerRent/promotions body: {item_id, start_date, end_date} */
	public function promotions_post()
	{
		$account = $this->require_auth();
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');

		$item_id = $this->post('item_id');
		$start_date_raw = $this->post('start_date');
		$end_date_raw = $this->post('end_date');

		if (empty($item_id) || empty($start_date_raw) || empty($end_date_raw)) {
			return $this->validation_error(['item_id' => 'wajib diisi', 'start_date' => 'wajib diisi', 'end_date' => 'wajib diisi']);
		}

		$balance = $this->Customer_m->balance($account->id);
		$start_date = new DateTime($start_date_raw);
		$end_date = new DateTime($end_date_raw);
		$day_interval = $start_date->diff($end_date)->days + 1;

		$price_per_day = $this->Basic_m->get_config_value('promote_price_per_day_rent_vehicle');
		$total_payment = $price_per_day * $day_interval;

		if ($balance->balance < $total_payment) {
			return $this->fail('Saldo anda tidak mencukupi untuk melakukan pemesanan promosi rental kendaraan. Segera lakukan pengisian saldo', 402, [
				'days' => $day_interval, 'balance' => $balance,
			]);
		}

		$id = $this->PartnerRent_m->add_promote($account->id, [
			'item_id' => $item_id,
			'start_date' => $start_date_raw,
			'end_date' => $end_date_raw,
			'days' => $day_interval,
			'price_per_day' => $price_per_day,
			'total_payment' => $total_payment,
		]);

		$this->Customer_m->decrease_balance($account->id, $total_payment);
		$this->created(['id' => (int) $id], 'Berhasil membuat promosi kendaraan');
	}

	/** DELETE api/partnerRent/promotions/{id} */
	public function promotions_delete($id = null)
	{
		$account = $this->require_auth();
		$this->load->model('Customer_m');

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$promote = $this->PartnerRent_m->promote_detail($id);
		if (!$promote) {
			return $this->not_found('Promosi tidak ditemukan');
		}

		if ($promote->status == 2) {
			return $this->fail('Promosi yang sudah selesai tidak bisa dibatalkan.', 409);
		}
		if ($promote->status == 3) {
			return $this->fail('Promosi ini sudah dibatalkan sebelumnya.', 409);
		}

		$now_date = new DateTime(date('Y-m-d'));
		$start_date = new DateTime($promote->start_date);
		$end_date = new DateTime($promote->end_date);
		$end_interval = $now_date->diff($end_date)->days;

		if ($now_date < $start_date) {
			$interval = $promote->days;
		} else if ($now_date >= $start_date && $now_date <= $end_date) {
			$interval = $end_interval;
		} else {
			$interval = 0;
		}

		$total_return = $promote->price_per_day * $interval;

		$this->PartnerRent_m->update_promote($id, ['canceled_total_return' => $total_return, 'status' => 3]);
		$this->Customer_m->increase_balance($account->id, $total_return);

		$this->ok(null, 'Berhasil membatalkan promosi. Sisa '.$interval.' Hari sebesar Rp.'.number_format($total_return, 2, ',', '.').' telah dikembalikan ke saldo.');
	}
}

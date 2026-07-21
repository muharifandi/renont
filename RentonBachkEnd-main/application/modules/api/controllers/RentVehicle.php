<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/Api_Base_Controller.php';

/**
 * Vehicle search, detail, and booking (checkout) resource.
 * Converted to REST_Base_Controller: standard {status,message,data} envelope,
 * proper HTTP status codes, and require_auth() instead of the old unguarded
 * get_detail_key() call (which threw a fatal error on an invalid/missing key).
 */
class RentVehicle extends Api_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('RentVehicle_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Vehicles API — RentOn');
	}

	/**
	 * GET api/rentvehicle/list
	 * Query params: functional_type, start_date, end_date, page, limit, sort,
	 * min_passenger, max_passenger, min_price, max_price,
	 * vehicle_functional_type_selected (csv), with_driver, regency
	 * Header: key (optional -- enables distance sort & personalization)
	 */
	public function list_get()
	{
		$this->load->model('Basic_m');
		$this->load->model('PartnerRent_m');

		$account_id = null;
		$key = $this->input->get_request_header('key', TRUE);
		if (!empty($key)) {
			$key_row = $this->get_detail_key($key);
			$account_id = $key_row ? $key_row->account_id : null;
		}

		$param = [
			'functional_type' => $this->get('functional_type'),
			'start_date' => $this->get('start_date'),
			'end_date' => $this->get('end_date'),
			'page' => (int) ($this->get('page') ?: 1),
			'limit' => min((int) ($this->get('limit') ?: 10), 50),
			'sort' => $this->get('sort'),
			'status' => $this->get('status'),
			'min_passenger' => $this->get('min_passenger'),
			'max_passenger' => $this->get('max_passenger'),
			'min_price' => $this->get('min_price'),
			'max_price' => $this->get('max_price'),
			'vehicle_functional_type_selected' => $this->get('vehicle_functional_type_selected'),
			'with_driver' => $this->get('with_driver'),
			'regencies' => $this->get('regency'),
		];

		$result = $this->RentVehicle_m->list_vehicle($account_id, $param);
		$minmax_price = $this->RentVehicle_m->list_vehicle_min_max_value();
		$functional_type = $this->PartnerRent_m->get_functional_type();

		$regency_name = null;
		if ($param['regencies'] != null) {
			$regency = $this->Basic_m->get_regency($param['regencies']);
			$regency_name = $regency ? $regency->name : null;
		}

		if ($param['page'] == 1) {
			$promote_param = $param;
			$promote_param['page'] = 1;
			$promote_param['limit'] = (int) $this->Basic_m->get_config_value('promote_max_rent_vehicle');
			$promote = $this->RentVehicle_m->list_promote_vehicle($account_id, $promote_param);
			$result = array_merge($promote, $result);
		}

		$this->ok([
			'vehicles' => $result,
			'price_min' => $minmax_price->price_min,
			'price_max' => $minmax_price->price_max,
			'functional_type' => $functional_type,
			'regency' => $regency_name,
		]);
	}

	/**
	 * GET api/rentvehicle/detail/{id}
	 */
	public function detail_get($id = null)
	{
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('Partner_m');
		$this->load->model('PartnerRent_m');

		$vehicle = $this->RentVehicle_m->vehicle_detail($id);
		if (!$vehicle) {
			return $this->not_found('Kendaraan tidak ditemukan');
		}

		$vehicle_booked = $this->RentVehicle_m->vehicle_booked($id);
		$vehicle_review = $this->RentVehicle_m->vehicle_review($id, ['page' => 1, 'limit' => 5]);
		$vehicle_review_total = $this->RentVehicle_m->vehicle_review_total($id);
		$vehicle->photos = $this->RentVehicle_m->vehicle_photos($id);

		$partner = $this->Partner_m->partner_info($vehicle->account_id);
		$configRent = $this->PartnerRent_m->config($vehicle->account_id);

		$this->ok([
			'vehicle' => $vehicle,
			'vehicle_booked' => $vehicle_booked,
			'force_with_driver' => $configRent->force_with_driver,
			'partner' => $partner,
			'review' => $vehicle_review,
			'review_total' => $vehicle_review_total,
		]);
	}

	/**
	 * GET api/rentvehicle/reviews/{id}
	 * Query params: page, limit
	 */
	public function reviews_get($id = null)
	{
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'page' => (int) ($this->get('page') ?: 1),
			'limit' => min((int) ($this->get('limit') ?: 10), 50),
		];

		$this->ok([
			'review' => $this->RentVehicle_m->vehicle_review($id, $param),
			'review_total' => $this->RentVehicle_m->vehicle_review_total($id),
		]);
	}

	/**
	 * POST api/rentvehicle/quote
	 * body: { vehicle_id, price_package, start_date, end_date }
	 * header: key (required)
	 */
	public function quote_post()
	{
		$account = $this->require_auth();

		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
		$this->load->model('PartnerRent_m');

		$id = $this->post('vehicle_id');
		$price_package = $this->post('price_package');
		$start_date = $this->post('start_date');
		$end_date = $this->post('end_date');

		if (empty($id) || $price_package === null || empty($start_date) || empty($end_date)) {
			return $this->validation_error([
				'vehicle_id' => 'wajib diisi', 'price_package' => 'wajib diisi',
				'start_date' => 'wajib diisi', 'end_date' => 'wajib diisi',
			]);
		}

		$vehicle = $this->RentVehicle_m->vehicle_detail($id);
		if (!$vehicle) {
			return $this->not_found('Kendaraan tidak ditemukan');
		}
		$vehicle->photos = $this->RentVehicle_m->vehicle_photos($id);

		$start_time = new DateTime($start_date);
		$end_time = new DateTime($end_date);
		$daysRent = $start_time->diff($end_time)->days;

		$price = 0;
		if ($price_package == 0) $price = $vehicle->price;
		else if ($price_package == 1) $price = $vehicle->price_with_driver_basic;
		else if ($price_package == 2) $price = $vehicle->price_with_driver_full;

		$rent_payment = $price * $daysRent;
		$configRent = $this->PartnerRent_m->config($vehicle->account_id);

		$now = new DateTime(date('Y-m-d'));
		$your_date = new DateTime($start_date);
		$daysCod = $now->diff($your_date)->days;

		$admin_fee = (double) $this->Basic_m->get_config_value('admin_fee');
		$balance = $this->Customer_m->balance($account->id)->balance;

		$this->ok([
			'vehicle' => $vehicle,
			'rent_payment' => $rent_payment,
			'days' => $daysRent,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'config' => $configRent,
			'cash_on_delivery' => ($daysCod <= $configRent->max_day_cod && $configRent->max_day_cod != 0 && ($balance > $admin_fee)) ? 1 : 0,
		]);
	}

	/**
	 * POST api/rentvehicle/voucher
	 * body: { code, start_date }
	 * header: key (required)
	 */
	public function voucher_post()
	{
		$account = $this->require_auth();
		$this->load->model('Basic_m');

		$code = $this->post('code');
		if (empty($code)) {
			return $this->validation_error(['code' => 'wajib diisi']);
		}

		$param = [
			'code' => $code,
			'start_date' => $this->post('start_date'),
			'voucher_type' => 2,
			'user_type' => 5,
			'feature_id' => 1,
		];

		$voucher = $this->Basic_m->get_voucher_by_code($param);

		if ($voucher === null) {
			return $this->not_found('Voucher tidak ditemukan');
		}

		if ($this->Basic_m->is_voucher_used($account->id, $voucher->id)) {
			return $this->fail('Voucher ini sudah pernah anda gunakan', 409);
		}

		if ($voucher->use_expire == 1) {
			$start_request_ts = new DateTime($param['start_date'].' 00:00:00');
			$start_ts = new DateTime($voucher->start_date.' 00:00:00');
			$end_ts = new DateTime($voucher->end_date.' 23:59:59');
			$user_ts = new DateTime(date('Y-m-d'));

			$in_date_range = (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
			$in_date_range_request = (($start_request_ts >= $start_ts) && ($start_request_ts <= $end_ts));

			if (!$in_date_range) {
				return $this->fail('Voucher '.$voucher->description.' dapat digunakan tanggal '.$start_ts->format('d F Y H:i').' sampai tanggal '.$end_ts->format('d F Y H:i'), 409);
			}
			if (!$in_date_range_request) {
				return $this->fail('Voucher tidak valid. Tanggal awal penyewaan harus saat masa berlaku voucher yaitu antara tanggal '.$start_ts->format('d F Y H:i').' sampai tanggal '.$end_ts->format('d F Y H:i'), 409);
			}
			if ($voucher->use_quota == 1 && $voucher->quota == 0) {
				return $this->fail('Kuota voucher ini sudah habis', 409);
			}
		}

		$this->ok(['voucher' => $voucher]);
	}

	/**
	 * POST api/rentvehicle/bookings
	 * Creates a booking (was post_checkout). header: key (required)
	 */
	public function bookings_post()
	{
		$account = $this->require_auth();

		$this->load->model('PartnerRent_m');
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');

		$item_id = $this->post('item_id');
		$price_package = $this->post('price_package');
		$start_date = $this->post('start_date');
		$end_date = $this->post('end_date');
		$time = $this->post('time');

		if (empty($item_id) || $price_package === null || empty($start_date) || empty($end_date)) {
			return $this->validation_error([
				'item_id' => 'wajib diisi', 'price_package' => 'wajib diisi',
				'start_date' => 'wajib diisi', 'end_date' => 'wajib diisi',
			]);
		}

		$vehicle = $this->RentVehicle_m->vehicle_detail($item_id);
		if (!$vehicle) {
			return $this->not_found('Kendaraan tidak ditemukan');
		}

		$param = [
			'account_id' => $account->id,
			'item_id' => $item_id,
			'price_package' => $price_package,
			'start_date' => $start_date.' '.$time,
			'end_date' => $end_date.' '.$time,
			'delivery' => $this->post('delivery'),
			'pickoff' => $this->post('pickoff'),
			'voucher_id' => $this->post('voucher_id'),
			'cash_on_delivery' => $this->post('cash_on_delivery'),
			'status' => 1,
			'description' => $this->post('description'),
		];

		if ($param['price_package'] == 0) {
			$param['price_package_name'] = 'Car Only';
			$param['price'] = $vehicle->price;
		} else if ($param['price_package'] == 1) {
			$param['price_package_name'] = 'Car + Driver Basic';
			$param['price'] = $vehicle->price_with_driver_basic;
		} else if ($param['price_package'] == 2) {
			$param['price_package_name'] = 'Car + Driver All In';
			$param['price'] = $vehicle->price_with_driver_full;
		}

		$configRent = $this->PartnerRent_m->config($vehicle->account_id);
		$param['overtime_fee'] = $configRent->overtime_fee;
		$addtitional_fee = 0;

		if ($param['delivery'] == '1') {
			$param['delivery_date'] = $start_date.' '.$this->post('delivery_time');
			$param['delivery_address'] = $this->post('delivery_address');
			$param['delivery_latitude'] = $this->post('delivery_latitude');
			$param['delivery_longitude'] = $this->post('delivery_longitude');
			$param['delivery_fee'] = $configRent->delivery_fee;
			$addtitional_fee += $configRent->delivery_fee;
		}

		if ($param['pickoff'] == '1') {
			$param['pickoff_date'] = $start_date.' '.$this->post('pickoff_time');
			$param['pickoff_address'] = $this->post('pickoff_address');
			$param['pickoff_latitude'] = $this->post('pickoff_latitude');
			$param['pickoff_longitude'] = $this->post('pickoff_longitude');
			$param['pickoff_fee'] = $configRent->pickoff_fee;
			$addtitional_fee += $configRent->pickoff_fee;
		}

		$discount = 0;
		$voucher = null;

		if ($param['voucher_id'] != null) {
			$voucher = $this->Basic_m->get_voucher($param['voucher_id']);
			if ($voucher && $voucher->use_quota == 1 && $voucher->quota > 0) {
				$discount = $voucher->value;
			} else {
				$param['voucher_id'] = null;
			}
			$param['discount'] = $discount;
		}

		$start_time = new DateTime($start_date);
		$end_time = new DateTime($end_date);
		$daysRent = $start_time->diff($end_time)->days;

		$rent_payment = $param['price'] * $daysRent;
		$total_payment = $rent_payment + $addtitional_fee - $discount;
		$param['total_payment'] = $total_payment;

		$balance = $this->Customer_m->balance($account->id)->balance;
		$admin_fee_use_percentage = (int) $this->Basic_m->get_config_value('admin_fee_use_percentage');

		if ($admin_fee_use_percentage == 1) {
			$param['admin_fee'] = (int) (($this->Basic_m->get_config_value('admin_fee') / 100) * $rent_payment);
		} else {
			$param['admin_fee'] = (double) $this->Basic_m->get_config_value('admin_fee');
		}

		$partner_balance = $this->Customer_m->balance($vehicle->account_id)->balance;

		if ($param['cash_on_delivery'] != 1) {
			if ($balance < $total_payment) {
				return $this->fail('Gagal Checkout. Saldo anda kurang dari yang dibutuhkan untuk melakukan transaksi ini', 402);
			}
		} else {
			if ($partner_balance < $param['admin_fee']) {
				return $this->fail("Gagal Checkout. Saat ini mitra tidak dapat menerima COD.\nHarap matikan fitur COD untuk melanjutkan. Hubungi mitra via chat untuk menerima keterangan.", 409);
			}
		}

		$this->db->trans_start();

		if ($param['cash_on_delivery'] != 1) {
			$this->Customer_m->decrease_balance($account->id, $total_payment);
			if ($param['voucher_id'] != null && $voucher->use_quota == 1 && $voucher->quota > 0) {
				$this->Basic_m->decrease_voucher_quota($param['voucher_id']);
			}
		} else {
			$this->Customer_m->decrease_balance($vehicle->account_id, $param['admin_fee']);
			if ($param['voucher_id'] != null && $voucher->use_quota == 1 && $voucher->quota > 0) {
				$this->Basic_m->decrease_voucher_quota($param['voucher_id']);
			}
		}

		$transaction_id = $this->RentVehicle_m->post_checkout($param);
		$status = $this->RentVehicle_m->get_transaction_status_name('1');

		$this->RentVehicle_m->add_timeline_transaction([
			'transaction_id' => $transaction_id,
			'title' => $status->name,
			'description' => ($param['description'] != null) ? $param['description'] : $status->name,
		]);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			return $this->fail('Gagal membuat pesanan, silakan coba lagi', 500);
		}

		$this->_notify_new_booking($transaction_id, $vehicle->account_id, $account->id);

		$this->created(['id' => (int) $transaction_id], 'Berhasil membuat pesanan');
	}

	private function _notify_new_booking($transaction_id, $partner_account_id, $customer_account_id)
	{
		$this->load->model('Customer_m');
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		$this->fcm->addRecepient($this->Customer_m->get_token($partner_account_id));
		$this->fcm->setData(['data_type' => 'partner_rent_vehicle_transaction', 'id' => $transaction_id]);
		$this->fcm->setNotification(['title' => 'Rental Kendaraan', 'text' => 'Pesanan Baru #'.$transaction_id, 'android_channel_id' => 2, 'sound' => 'default']);
		$this->fcm->send();

		$this->fcm->clearRecepients();
		$this->fcm->addRecepient($this->Customer_m->get_token($customer_account_id));
		$this->fcm->setData(['data_type' => 'customer_rent_vehicle_transaction', 'id' => $transaction_id]);
		$this->fcm->setNotification(['title' => 'Rental Kendaraan', 'text' => 'Berhasil memesan kendaraan #'.$transaction_id, 'android_channel_id' => 2, 'sound' => 'default']);
		$this->fcm->send();
	}
}

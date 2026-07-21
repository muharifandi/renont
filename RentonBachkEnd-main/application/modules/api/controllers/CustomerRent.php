<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Customer-side view of bookings (transaction_rent_vehicle resource).
 */
class CustomerRent extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('CustomerRent_m');
		$this->load->model('RentVehicle_m');
		$this->load->model('Customer_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Customer Bookings API — RentOn');
	}

	/**
	 * GET api/customerRent/bookings          -> list (query: page, limit, status)
	 * GET api/customerRent/bookings/{id}     -> detail
	 */
	public function bookings_get($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_CUSTOMER]);

		if (!empty($id)) {
			return $this->_booking_detail($account->id, $id);
		}

		$param = [
			'page' => (int) ($this->get('page') ?: 1),
			'limit' => min((int) ($this->get('limit') ?: 10), 50),
			'status' => $this->get('status'),
		];
		$result = $this->CustomerRent_m->list_transaction($account->id, $param);
		$this->ok(['transaction_rent_vehicle' => $result ?: []]);
	}

	private function _booking_detail($account_id, $id)
	{
		$this->load->model('Partner_m');
		$this->load->model('Basic_m');

		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}
		if ((int) $transaction_detail->account_id !== (int) $account_id) {
			return $this->forbidden();
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

		$is_reviewed = $this->CustomerRent_m->is_review_submit($transaction_detail->id, $transaction_detail->account_id);

		$this->ok([
			'partner' => $this->Partner_m->partner_info($vehicle->account_id),
			'vehicle' => $vehicle,
			'transaction_detail' => $transaction_detail,
			'voucher' => $voucher,
			'balance' => $balance,
			'hour_overtime' => $hour_overtime,
			'feedback' => $is_reviewed ? 0 : 1,
		]);
	}

	/** DELETE api/customerRent/bookings/{id} -- cancel a booking */
	public function bookings_delete($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_CUSTOMER]);

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}
		if ((int) $transaction_detail->account_id !== (int) $account->id) {
			return $this->forbidden();
		}

		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$this->RentVehicle_m->update_transaction_status($id, 11);
		$status = $this->RentVehicle_m->get_transaction_status_name(11);
		$this->RentVehicle_m->add_timeline_transaction(['transaction_id' => $id, 'title' => $status->name, 'description' => $status->name]);

		$this->_notify_status($id, $vehicle->account_id, $account->id, 'Transaksi #'.$id.' telah dibatalkan oleh pelanggan.', 'Berhasil membatalkan rental kendaraan #'.$id);

		if ($transaction_detail->cash_on_delivery == 1) {
			$this->Customer_m->increase_balance($vehicle->account_id, $transaction_detail->admin_fee);
			$this->ok(null, 'Berhasil membatalkan pemesanan.');
		} else {
			$this->Customer_m->increase_balance($account->id, $transaction_detail->total_payment);
			$this->ok(null, 'Berhasil membatalkan pemesanan. Pembayaran akan dikembalikan ke saldo');
		}
	}

	/** PUT api/customerRent/booking_status/{id} body: {status} */
	public function booking_status_put($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_CUSTOMER]);
		$new_status = $this->put('status');

		if (empty($id) || empty($new_status)) {
			return $this->validation_error(['id' => 'wajib diisi', 'status' => 'wajib diisi']);
		}

		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}
		if ((int) $transaction_detail->account_id !== (int) $account->id) {
			return $this->forbidden();
		}

		$this->RentVehicle_m->update_transaction_status($id, $new_status);
		$status = $this->RentVehicle_m->get_transaction_status_name($new_status);
		$this->RentVehicle_m->add_timeline_transaction(['transaction_id' => $id, 'title' => $status->name, 'description' => $status->name]);

		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
		$this->_notify_status($id, $vehicle->account_id, $transaction_detail->account_id, 'Transaksi #'.$id.' : '.$status->name, 'Transaksi #'.$id.' : '.$status->name);

		$this->ok(null, 'Berhasil mengubah status pemesanan');
	}

	private function _notify_status($id, $partner_account_id, $customer_account_id, $partner_text, $customer_text)
	{
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

	/** POST api/customerRent/booking_review/{id} body: {rating, comment} */
	public function booking_review_post($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_CUSTOMER]);
		$rating = $this->post('rating');

		if (empty($id) || empty($rating)) {
			return $this->validation_error(['id' => 'wajib diisi', 'rating' => 'wajib diisi']);
		}

		$transaction_detail = $this->CustomerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}
		if ((int) $transaction_detail->account_id !== (int) $account->id) {
			return $this->forbidden();
		}

		$this->CustomerRent_m->post_review([
			'transaction_id' => $id,
			'account_id' => $account->id,
			'rating' => $rating,
			'comment' => $this->post('comment'),
		]);

		$this->created(null, 'Berhasil mengirim ulasan');
	}
}

<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/Api_Base_Controller.php';

/**
 * Partner account & profile resource.
 */
class Partner extends Api_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Partner_m');
		$this->load->helper('image_manipulation');
	}

	public function index_get()
	{
		$this->ok(null, 'Partner API — RentOn');
	}

	private function _upload($field, $path)
	{
		if (empty($_FILES[$field]['name'])) {
			return null;
		}
		$config = ['upload_path' => $path, 'allowed_types' => 'jpg|jpeg|png|pdf', 'max_size' => '5120', 'overwrite' => false];
		$this->load->library('upload', $config, $field);
		if ($this->{$field}->do_upload($field)) {
			$file_name = $this->{$field}->data('file_name');
			thumb_image($path.'/'.$file_name, $path.'/thumb_rentone_'.$file_name, 250);
			return resize_image($path.'/'.$file_name, $path.'/rentone_'.$file_name, 600, 1, TRUE);
		}
		return null;
	}

	/** POST api/partner -- promote an already-logged-in customer account to partner (multipart) */
	public function index_post()
	{
		$account = $this->require_auth();

		$this->load->model('Basic_m');
		$this->load->model('Customer_m');

		$referal_id = null;
		$referal_code = $this->post('referal');
		if (!empty($referal_code)) {
			$referal_id = $this->Basic_m->get_account_id_by_referal_code($referal_code);
		}

		$img_profile = $this->_upload('img_profile', FCPATH.'data/partners/profile');
		$img_identity = $this->_upload('img_identity', FCPATH.'data/partners/files/identity');
		$img_driver_licence = $this->_upload('img_driver_licence', FCPATH.'data/partners/files/driver_licence');
		$img_bussiness_licence = $this->_upload('img_bussiness_licence', FCPATH.'data/partners/files/bussiness_licence');
		$img_bussiness_registration = $this->_upload('img_bussiness_registration', FCPATH.'data/partners/files/bussiness_registration');

		$this->Partner_m->register([
			'account_id' => $account->id,
			'ownership_id' => $this->post('ownership_id'),
			'img_profile' => $img_profile,
			'company_name' => $this->post('company_name'),
			'regencies_id' => $this->post('regencies_id'),
			'address' => $this->post('address'),
			'latitude' => $this->post('latitude'),
			'longitude' => $this->post('longitude'),
			'description' => $this->post('description'),
			'tax_number' => $this->post('tax_number'),
			'agent_id' => $this->post('agent_id'),
			'referal_id' => $referal_id,
		]);

		$this->Partner_m->insert_partner_file([
			'account_id' => $account->id,
			'img_identity' => $img_identity,
			'img_driver_licence' => $img_driver_licence,
			'img_bussiness_licence' => $img_bussiness_licence,
			'img_bussiness_registration' => $img_bussiness_registration,
		]);

		$this->load->library('ion_auth');
		$this->ion_auth->add_to_group(self::GROUP_PARTNER, $account->id);

		$this->_notify_new_partner($account->id);

		$this->created(null, 'Berhasil Registrasi');
	}

	private function _notify_new_partner($account_id)
	{
		$this->load->model('Basic_m');
		$this->load->model('Customer_m');
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
		$this->fcm->setData(['data_type' => 'partner_register', 'id' => $account_id, 'link_action' => base_url().'admin/partner/list_register_request']);
		$this->fcm->setNotification(['title' => 'Registrasi Mitra', 'body' => 'Registrasi mitra baru dengan id akun #'.$account_id, 'android_channel_id' => 3, 'sound' => 'default']);
		$this->fcm->send();

		$this->fcm->clearRecepients();
		$this->fcm->addRecepient($this->Customer_m->get_token($account_id));
		$this->fcm->setData(['data_type' => 'partner_register', 'id' => $account_id]);
		$this->fcm->setNotification(['title' => 'Registrasi Mitra', 'text' => 'Registrasi berhasil. Permintaan akan diproses dalam waktu dekat', 'android_channel_id' => 3, 'sound' => 'default']);
		$this->fcm->send();
	}

	/** DELETE api/partner -- discard a rejected registration to allow resubmission */
	public function index_delete()
	{
		$account = $this->require_auth();
		$this->Partner_m->delete($account->id);
		$this->ok(null, 'Sekarang anda dapat melakukan registrasi ulang');
	}

	public function status_get()
	{
		$account = $this->require_auth();
		$this->ok(['status' => $this->Partner_m->get_status($account->id)]);
	}

	public function detail_get()
	{
		$account = $this->require_auth();
		$this->ok([
			'partner' => $this->Partner_m->detail($account->id),
			'features' => $this->Partner_m->list_feature_pair($account->id),
		]);
	}

	/** POST api/partner/profile_image (multipart) */
	public function profile_image_post()
	{
		$account = $this->require_auth();
		$img_profile = $this->_upload('img_profile', FCPATH.'data/partners/profile');

		if (!$img_profile) {
			return $this->fail('Gagal mengunggah foto profil', 422);
		}

		$this->Partner_m->update_profile_image($account->id, $img_profile);
		$this->ok(['img_profile' => $img_profile], 'Berhasil mengubah foto profil');
	}

	/**
	 * PUT api/partner/profile
	 * body: any of {company_name, description, address, regencies_id, latitude, longitude}
	 */
	public function profile_put()
	{
		$account = $this->require_auth();

		$fields = ['company_name', 'description', 'address', 'regencies_id', 'latitude', 'longitude'];
		$data = [];
		foreach ($fields as $f) {
			$v = $this->put($f);
			if ($v !== null) $data[$f] = $v;
		}

		if (empty($data)) {
			return $this->validation_error(['*' => 'sertakan minimal satu field untuk diubah']);
		}

		$this->Partner_m->update_partner($account->id, $data);
		$this->ok(null, 'Berhasil mengubah profil mitra');
	}

	/** POST api/partner/feature_requests body: {feature_id} */
	public function feature_requests_post()
	{
		$account = $this->require_auth();
		$feature_id = $this->post('feature_id');

		if (empty($feature_id)) {
			return $this->validation_error(['feature_id' => 'wajib diisi']);
		}

		$status = $this->Partner_m->request_feature($account->id, ['feature_id' => $feature_id, 'status' => 2]);

		if ($status) {
			$this->created(null, 'Berhasil mengirim permintaan layanan. Permintaan akan diproses dalam waktu dekat.');
		} else {
			$this->fail('Gagal mengirim permintaan layanan. Hubungi administrator jika terjadi masalah', 500);
		}
	}
}

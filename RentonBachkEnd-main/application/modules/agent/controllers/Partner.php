<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Partner resource -- the partners a single agent has recruited/registered
 * themselves, plus that agent's own commission and transaction reports.
 *
 * GET    agent/partner                -> list own partners (query: page, limit, search)
 * GET    agent/partner/{id}           -> partner detail (must belong to this agent)
 * POST   agent/partner                -> recruit/register a new partner (multipart)
 * PUT    agent/partner/{id}           -> edit a recruited partner (multipart)
 * GET    agent/partner/commissions    -> agent's own commission history (query: page, limit, search)
 * GET    agent/partner/transactions   -> agent's own partners' transaction history (query: page, limit, search)
 */
class Partner extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('agent/Partner_m');
		$this->load->helper('image_manipulation');
	}

	/**
	 * Read a field from either the parsed PUT body or the raw $_POST superglobal.
	 * Multipart bodies (file uploads) can't be parsed by CI's raw-input-stream PUT
	 * parser, so a PUT-with-files request is expected to arrive as a real POST with
	 * `_method=PUT` (the REST library's method-override) -- in that case $_POST is
	 * what's actually populated, hence the fallback.
	 */
	private function _field($key)
	{
		$value = $this->put($key);
		return ($value === null || $value === '') ? $this->post($key) : $value;
	}

	/** Uploads $_FILES[$field] (if present) into $path, generating a thumbnail + resized copy.
	 *  Falls back to $existing_filename when no new file was sent (matches the old
	 *  "img_x_filename" passthrough fields used to keep an existing image on edit). */
	private function _handle_upload($field, $path, $existing_filename = null)
	{
		if (empty($_FILES[$field]['name'])) {
			return $existing_filename ?: null;
		}

		$config = [
			'upload_path' => $path,
			'allowed_types' => '*',
			'max_size' => '20480',
			'overwrite' => false,
		];
		$this->load->library('upload', $config, $field);

		if ($this->{$field}->do_upload($field)) {
			$file_name = $this->{$field}->data('file_name');
			thumb_image($path.'/'.$file_name, $path.'/thumb_rentone_'.$file_name, 250);
			return resize_image($path.'/'.$file_name, $path.'/rentone_'.$file_name, 600, 1, TRUE);
		}
		return null;
	}

	/** GET agent/partner?page=&limit=&search=  OR  GET agent/partner/{id} */
	public function index_get($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		if (!empty($id)) {
			// Partner_m::detail() only verifies the account is a partner account,
			// not that it belongs to this agent -- enforce that ownership here.
			$this->db->where('account_id', $id);
			$this->db->where('agent_id', $account->id);
			$owned = $this->db->get('partners')->row();
			if (!$owned) {
				return $this->not_found('Mitra tidak ditemukan');
			}

			$detail = $this->Partner_m->detail($id);
			if (!$detail) {
				return $this->not_found('Mitra tidak ditemukan');
			}
			return $this->ok($detail);
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Partner_m->get_list($account->id, $param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Partner_m->get_total_list_filtered($account->id, $param),
				'total_unfiltered' => (int) $this->Partner_m->get_total_list_unfiltered($account->id, $param),
			]
		);
	}

	/**
	 * POST agent/partner multipart body:
	 * {first_name,last_name,email,phone,password,password_confirm,company_name,
	 *  description,regencies_id,identity_number,ownership_id,address,latitude,longitude,tax_number,
	 *  img_profile,img_identity,img_profile_partner,img_driver_licence,img_bussiness_licence,img_bussiness_registration (files)}
	 */
	public function index_post()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);
		$this->load->library('ion_auth');

		$first_name = $this->post('first_name');
		$last_name = $this->post('last_name');
		$email = strtolower((string) $this->post('email'));
		$phone = $this->post('phone');
		$password = $this->post('password');
		$password_confirm = $this->post('password_confirm');
		$company_name = $this->post('company_name');
		$description = $this->post('description');
		$regencies_id = $this->post('regencies_id');
		$identity_number = $this->post('identity_number');

		$errors = [];
		if (empty($first_name)) $errors['first_name'] = 'wajib diisi';
		if (empty($last_name)) $errors['last_name'] = 'wajib diisi';
		if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'wajib diisi dengan format yang benar';

		$min_length = $this->config->item('min_password_length', 'ion_auth');
		if (empty($password) || strlen($password) < $min_length) {
			$errors['password'] = 'wajib diisi, minimal '.$min_length.' karakter';
		} elseif ($password !== $password_confirm) {
			$errors['password_confirm'] = 'Tidak sama dengan password';
		}

		if (empty($company_name)) $errors['company_name'] = 'wajib diisi';
		if (empty($description)) $errors['description'] = 'wajib diisi';
		if (empty($regencies_id)) $errors['regencies_id'] = 'wajib diisi';
		if (empty($identity_number)) $errors['identity_number'] = 'wajib diisi';

		if (!empty($errors)) {
			return $this->validation_error($errors);
		}

		// Partners are registered as customers (group 5) first -- they can also rent
		// vehicles on the platform -- then explicitly added to the partner group (4)
		// below. This mirrors the original add_partner() exactly.
		$additional_data = ['first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone];
		$id = $this->ion_auth->register($email, $password, $email, $additional_data, [5]);

		if (!$id) {
			return $this->fail('Gagal mendaftarkan mitra. Periksa kembali data yang dikirim.', 422, $this->ion_auth->errors());
		}

		$this->ion_auth->activate($id);

		$img_profile = $this->_handle_upload('img_profile', FCPATH.'data/customers/profile', $this->post('img_profile_filename'));
		$img_identity = $this->_handle_upload('img_identity', FCPATH.'data/customers/files/identity', $this->post('img_identity_filename'));
		$img_profile_partner = $this->_handle_upload('img_profile_partner', FCPATH.'data/partners/profile', $this->post('img_profile_partner_filename'));
		$img_driver_licence = $this->_handle_upload('img_driver_licence', FCPATH.'data/partners/files/driver_licence', $this->post('img_driver_licence_filename'));
		$img_bussiness_licence = $this->_handle_upload('img_bussiness_licence', FCPATH.'data/partners/files/bussiness_licence', $this->post('img_bussiness_licence_filename'));
		$img_bussiness_registration = $this->_handle_upload('img_bussiness_registration', FCPATH.'data/partners/files/bussiness_registration', $this->post('img_bussiness_registration_filename'));

		$customer_data = ['account_id' => $id, 'identity_number' => $identity_number];
		if ($img_profile) $customer_data['img_profile'] = $img_profile;

		$customer_file = ['account_id' => $id];
		if ($img_identity) $customer_file['img_identity'] = $img_identity;

		$partner_data = [
			'account_id' => $id,
			'ownership_id' => $this->post('ownership_id'),
			'company_name' => $company_name,
			'regencies_id' => $regencies_id,
			'address' => $this->post('address'),
			'latitude' => $this->post('latitude'),
			'longitude' => $this->post('longitude'),
			'description' => $description,
			'tax_number' => $this->post('tax_number'),
			'agent_id' => $account->id,
			'status' => 1,
		];
		if ($img_profile_partner) $partner_data['img_profile'] = $img_profile_partner;

		$partner_file = ['account_id' => $id];
		if ($img_driver_licence) $partner_file['img_driver_licence'] = $img_driver_licence;
		if ($img_bussiness_licence) $partner_file['img_bussiness_licence'] = $img_bussiness_licence;
		if ($img_bussiness_registration) $partner_file['img_bussiness_registration'] = $img_bussiness_registration;

		$this->Partner_m->register($customer_data, $customer_file, $partner_data, $partner_file);
		$this->ion_auth->add_to_group(4, $id);

		$key = $this->_generate_key();
		$this->_insert_key($key, ['account_id' => $id]);

		$this->created(['account_id' => (int) $id, 'key' => $key], 'Berhasil mendaftarkan mitra baru');
	}

	/**
	 * PUT agent/partner/{id} multipart body: same fields as create (password optional --
	 * only validated/changed when sent); files optional, pass the matching
	 * `img_x_filename` field to keep the existing image untouched.
	 */
	public function index_put($id = null)
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		// Ownership guard -- the original edit_partner() had none at all: any account
		// id posted would be updated, partner or not, belonging to this agent or not.
		$this->db->where('account_id', $id);
		$this->db->where('agent_id', $account->id);
		$owned = $this->db->get('partners')->row();
		if (!$owned) {
			return $this->not_found('Mitra tidak ditemukan');
		}

		$this->load->library('ion_auth');
		$user = $this->ion_auth->user($id)->row();

		$first_name = $this->_field('first_name');
		$last_name = $this->_field('last_name');
		$phone = $this->_field('phone');
		$company_name = $this->_field('company_name');
		$description = $this->_field('description');
		$regencies_id = $this->_field('regencies_id');
		$identity_number = $this->_field('identity_number');
		$password = $this->_field('password');
		$password_confirm = $this->_field('password_confirm');

		$errors = [];
		if (empty($first_name)) $errors['first_name'] = 'wajib diisi';
		if (empty($last_name)) $errors['last_name'] = 'wajib diisi';
		if (empty($company_name)) $errors['company_name'] = 'wajib diisi';
		if (empty($description)) $errors['description'] = 'wajib diisi';
		if (empty($regencies_id)) $errors['regencies_id'] = 'wajib diisi';
		if (empty($identity_number)) $errors['identity_number'] = 'wajib diisi';

		if (!empty($password)) {
			$min_length = $this->config->item('min_password_length', 'ion_auth');
			if (strlen($password) < $min_length) {
				$errors['password'] = 'Panjang minimal '.$min_length.' karakter';
			} elseif ($password !== $password_confirm) {
				$errors['password_confirm'] = 'Tidak sama dengan password';
			}
		}

		if (!empty($errors)) {
			return $this->validation_error($errors);
		}

		$img_profile = $this->_handle_upload('img_profile', FCPATH.'data/customers/profile', $this->_field('img_profile_filename'));
		$img_identity = $this->_handle_upload('img_identity', FCPATH.'data/customers/files/identity', $this->_field('img_identity_filename'));
		$img_profile_partner = $this->_handle_upload('img_profile_partner', FCPATH.'data/partners/profile', $this->_field('img_profile_partner_filename'));
		$img_driver_licence = $this->_handle_upload('img_driver_licence', FCPATH.'data/partners/files/driver_licence', $this->_field('img_driver_licence_filename'));
		$img_bussiness_licence = $this->_handle_upload('img_bussiness_licence', FCPATH.'data/partners/files/bussiness_licence', $this->_field('img_bussiness_licence_filename'));
		$img_bussiness_registration = $this->_handle_upload('img_bussiness_registration', FCPATH.'data/partners/files/bussiness_registration', $this->_field('img_bussiness_registration_filename'));

		$update_data = ['first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone];
		if (!empty($password)) $update_data['password'] = $password;

		if (!$this->ion_auth->update($user->id, $update_data)) {
			return $this->fail('Gagal memperbaharui data mitra', 400, $this->ion_auth->errors());
		}

		$customer_data = ['identity_number' => $identity_number];
		if ($img_profile) $customer_data['img_profile'] = $img_profile;

		$customer_file = [];
		if ($img_identity) $customer_file['img_identity'] = $img_identity;

		$partner_data = [
			'ownership_id' => $this->_field('ownership_id'),
			'company_name' => $company_name,
			'regencies_id' => $regencies_id,
			'address' => $this->_field('address'),
			'latitude' => $this->_field('latitude'),
			'longitude' => $this->_field('longitude'),
			'description' => $description,
			'tax_number' => $this->_field('tax_number'),
		];
		if ($img_profile_partner) $partner_data['img_profile'] = $img_profile_partner;

		$partner_file = [];
		if ($img_driver_licence) $partner_file['img_driver_licence'] = $img_driver_licence;
		if ($img_bussiness_licence) $partner_file['img_bussiness_licence'] = $img_bussiness_licence;
		if ($img_bussiness_registration) $partner_file['img_bussiness_registration'] = $img_bussiness_registration;

		$this->Partner_m->update($id, $customer_data, $customer_file, $partner_data, $partner_file);

		$this->ok(null, 'Berhasil memperbaharui data mitra');
	}

	/** GET agent/partner/commissions?page=&limit=&search= */
	public function commissions_get()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Partner_m->get_list_commission($account->id, $param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Partner_m->get_total_list_commission_filtered($account->id, $param),
				'total_unfiltered' => (int) $this->Partner_m->get_total_list_commission_unfiltered($account->id, $param),
			]
		);
	}

	/** GET agent/partner/transactions?page=&limit=&search= */
	public function transactions_get()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->Partner_m->get_list_transaction($account->id, $param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Partner_m->get_total_list_transaction_filtered($account->id, $param),
				'total_unfiltered' => (int) $this->Partner_m->get_total_list_transaction_unfiltered($account->id, $param),
			]
		);
	}
}

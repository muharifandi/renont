<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Rent-vehicle master data + vehicle/transaction resources (admin backoffice).
 *
 * Master data CRUD groups (identical shape, repeated per group):
 *   GET    admin/rentvehicle/functional_type[/{id}]  -> list (query: search) / detail
 *   POST   admin/rentvehicle/functional_type          -> create
 *   PUT    admin/rentvehicle/functional_type/{id}     -> update
 *   DELETE admin/rentvehicle/functional_type/{id}      -> delete
 *   ... identically for: brand, vehicle_model, vehicle_type, color, transmition_type, driven_type, fuel
 *   GET    admin/rentvehicle/{group}_form_options      -> dropdown data for the create/edit form (brand, vehicle_model, vehicle_type, transmition_type, driven_type only)
 *
 * Vehicle + transaction resources:
 *   GET admin/rentvehicle/vehicles                              -> list (query: page, limit, search)
 *   GET admin/rentvehicle/vehicle_form_options                  -> dropdown data (status)
 *   PUT admin/rentvehicle/vehicle_status/{id}                   -> change status (body: status_id)
 *   GET admin/rentvehicle/vehicle_transactions                  -> list (query: page, limit, search)
 *   GET admin/rentvehicle/vehicle_transaction_form_options       -> dropdown data (status)
 *   PUT admin/rentvehicle/vehicle_transaction_cancel/{id}        -> cancel transaction
 *   PUT admin/rentvehicle/vehicle_transaction_finish/{id}        -> finish transaction
 *   GET admin/rentvehicle/vehicle_promote_transactions            -> list (query: page, limit, search)
 *   GET admin/rentvehicle/vehicle_promote_transaction_form_options -> dropdown data (status)
 */
class RentVehicle extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('AdminRentVehicle_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Admin Rent Vehicle API — RentOn');
	}

	/* =========================================================================
	 * Functional Type
	 * ========================================================================= */

	/** GET admin/rentvehicle/functional_type?search=  |  GET admin/rentvehicle/functional_type/{id} */
	public function functional_type_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_functional_type($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Tipe Fungsi');
		}

		$param = ['limit' => ['start' => 0, 'length' => 100000], 'search' => $this->get('search')];
		$this->ok($this->AdminRentVehicle_m->get_list_functional_type($param), 'Berhasil mengambil Tipe Fungsi');
	}

	/** POST admin/rentvehicle/functional_type body: {name, icon} */
	public function functional_type_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = ['name' => $name];
		if ($this->post('icon') != '') {
			$param['icon'] = $this->post('icon');
		}

		$this->AdminRentVehicle_m->add_functional_type($param);
		$this->created(null, 'Berhasil menambahkan Tipe Fungsi');
	}

	/** PUT admin/rentvehicle/functional_type/{id} body: {name, icon} */
	public function functional_type_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = ['name' => $this->put('name')];
		if ($this->put('icon') != '') {
			$param['icon'] = $this->put('icon');
		}

		$this->AdminRentVehicle_m->edit_functional_type($id, $param);
		$this->ok(null, 'Berhasil mengubah Tipe Fungsi');
	}

	/** DELETE admin/rentvehicle/functional_type/{id} */
	public function functional_type_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_functional_type($id);
		$this->ok(null, 'Berhasil menghapus Tipe Fungsi');
	}

	/* =========================================================================
	 * Brand
	 * ========================================================================= */

	/** GET admin/rentvehicle/brand?page=&limit=&search=  |  GET admin/rentvehicle/brand/{id} */
	public function brand_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_brand($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Merek');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_brand($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_brand_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_brand_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/brand_form_options -- dropdown data (functional_type) for the create/edit form */
	public function brand_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['functional_type' => $this->AdminRentVehicle_m->get_input_brand_parameter()['functional_type']]);
	}

	/** POST admin/rentvehicle/brand body: {functional_type, name, icon} */
	public function brand_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->post('functional_type'),
			'name' => $name,
		];
		if ($this->post('icon') != '') {
			$param['icon'] = $this->post('icon');
		}

		$this->AdminRentVehicle_m->add_brand($param);
		$this->created(null, 'Berhasil menambahkan Merek');
	}

	/** PUT admin/rentvehicle/brand/{id} body: {functional_type, name, icon} */
	public function brand_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->put('functional_type'),
			'name' => $this->put('name'),
		];
		if ($this->put('icon') != '') {
			$param['icon'] = $this->put('icon');
		}

		$this->AdminRentVehicle_m->edit_brand($id, $param);
		$this->ok(null, 'Berhasil mengubah Merek');
	}

	/** DELETE admin/rentvehicle/brand/{id} */
	public function brand_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_brand($id);
		$this->ok(null, 'Berhasil menghapus Merek');
	}

	/* =========================================================================
	 * Vehicle Model
	 * ========================================================================= */

	/** GET admin/rentvehicle/vehicle_model?page=&limit=&search=  |  GET admin/rentvehicle/vehicle_model/{id} */
	public function vehicle_model_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_vehicle_model($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Model Kendaraan');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_vehicle_model($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_model_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_model_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/vehicle_model_form_options -- dropdown data (brand) for the create/edit form */
	public function vehicle_model_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['brand' => $this->AdminRentVehicle_m->get_input_vehicle_model_parameter()['brand']]);
	}

	/** POST admin/rentvehicle/vehicle_model body: {brand_id, name} */
	public function vehicle_model_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = [
			'brand_id' => $this->post('brand_id'),
			'name' => $name,
		];

		$this->AdminRentVehicle_m->add_vehicle_model($param);
		$this->created(null, 'Berhasil menambahkan Model Kendaraan');
	}

	/** PUT admin/rentvehicle/vehicle_model/{id} body: {brand_id, name} */
	public function vehicle_model_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'brand_id' => $this->put('brand_id'),
			'name' => $this->put('name'),
		];

		$this->AdminRentVehicle_m->edit_vehicle_model($id, $param);
		$this->ok(null, 'Berhasil mengubah Model Kendaraan');
	}

	/** DELETE admin/rentvehicle/vehicle_model/{id} */
	public function vehicle_model_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_vehicle_model($id);
		$this->ok(null, 'Berhasil menghapus Model Kendaraan');
	}

	/* =========================================================================
	 * Vehicle Type
	 * ========================================================================= */

	/** GET admin/rentvehicle/vehicle_type?page=&limit=&search=  |  GET admin/rentvehicle/vehicle_type/{id} */
	public function vehicle_type_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_vehicle_type($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Jenis Kendaraan');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_vehicle_type($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_type_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_type_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/vehicle_type_form_options -- dropdown data (functional_type) for the create/edit form */
	public function vehicle_type_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['functional_type' => $this->AdminRentVehicle_m->get_input_vehicle_type_parameter()['functional_type']]);
	}

	/** POST admin/rentvehicle/vehicle_type body: {functional_type, name, icon} */
	public function vehicle_type_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->post('functional_type'),
			'name' => $name,
		];
		if ($this->post('icon') != '') {
			$param['icon'] = $this->post('icon');
		}

		$this->AdminRentVehicle_m->add_vehicle_type($param);
		$this->created(null, 'Berhasil menambahkan Jenis Kendaraan');
	}

	/** PUT admin/rentvehicle/vehicle_type/{id} body: {functional_type, name, icon} */
	public function vehicle_type_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->put('functional_type'),
			'name' => $this->put('name'),
		];
		if ($this->put('icon') != '') {
			$param['icon'] = $this->put('icon');
		}

		$this->AdminRentVehicle_m->edit_vehicle_type($id, $param);
		$this->ok(null, 'Berhasil mengubah Jenis Kendaraan');
	}

	/** DELETE admin/rentvehicle/vehicle_type/{id} */
	public function vehicle_type_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_vehicle_type($id);
		$this->ok(null, 'Berhasil menghapus Jenis Kendaraan');
	}

	/* =========================================================================
	 * Color
	 * ========================================================================= */

	/** GET admin/rentvehicle/color?search=  |  GET admin/rentvehicle/color/{id} */
	public function color_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_color($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Warna');
		}

		$param = ['limit' => ['start' => 0, 'length' => 100000], 'search' => $this->get('search')];
		$this->ok($this->AdminRentVehicle_m->get_list_color($param), 'Berhasil mengambil Warna');
	}

	/** POST admin/rentvehicle/color body: {name, value} */
	public function color_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = ['name' => $name];
		if ($this->post('value') != '') {
			$param['value'] = $this->post('value');
		}

		$this->AdminRentVehicle_m->add_color($param);
		$this->created(null, 'Berhasil menambahkan Warna');
	}

	/** PUT admin/rentvehicle/color/{id} body: {name, value} */
	public function color_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = ['name' => $this->put('name')];
		if ($this->put('value') != '') {
			$param['value'] = $this->put('value');
		}

		$this->AdminRentVehicle_m->edit_color($id, $param);
		$this->ok(null, 'Berhasil mengubah Warna');
	}

	/** DELETE admin/rentvehicle/color/{id} */
	public function color_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_color($id);
		$this->ok(null, 'Berhasil menghapus Warna');
	}

	/* =========================================================================
	 * Transmition Type
	 * ========================================================================= */

	/** GET admin/rentvehicle/transmition_type?page=&limit=&search=  |  GET admin/rentvehicle/transmition_type/{id} */
	public function transmition_type_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_transmition_type($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Jenis Transmisi');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_transmition_type($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_transmition_type_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_transmition_type_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/transmition_type_form_options -- dropdown data (functional_type) for the create/edit form */
	public function transmition_type_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['functional_type' => $this->AdminRentVehicle_m->get_input_transmition_type_parameter()['functional_type']]);
	}

	/** POST admin/rentvehicle/transmition_type body: {functional_type, name, icon} */
	public function transmition_type_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->post('functional_type'),
			'name' => $name,
		];
		if ($this->post('icon') != '') {
			$param['icon'] = $this->post('icon');
		}

		$this->AdminRentVehicle_m->add_transmition_type($param);
		$this->created(null, 'Berhasil menambahkan Jenis Transmisi');
	}

	/** PUT admin/rentvehicle/transmition_type/{id} body: {functional_type, name, icon} */
	public function transmition_type_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->put('functional_type'),
			'name' => $this->put('name'),
		];
		if ($this->put('icon') != '') {
			$param['icon'] = $this->put('icon');
		}

		$this->AdminRentVehicle_m->edit_transmition_type($id, $param);
		$this->ok(null, 'Berhasil mengubah Jenis Transmisi');
	}

	/** DELETE admin/rentvehicle/transmition_type/{id} */
	public function transmition_type_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_transmition_type($id);
		$this->ok(null, 'Berhasil menghapus Jenis Transmisi');
	}

	/* =========================================================================
	 * Driven Type
	 * ========================================================================= */

	/** GET admin/rentvehicle/driven_type?page=&limit=&search=  |  GET admin/rentvehicle/driven_type/{id} */
	public function driven_type_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_driven_type($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Jenis Penggerak');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_driven_type($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_driven_type_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_driven_type_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/driven_type_form_options -- dropdown data (functional_type) for the create/edit form */
	public function driven_type_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['functional_type' => $this->AdminRentVehicle_m->get_input_driven_type_parameter()['functional_type']]);
	}

	/** POST admin/rentvehicle/driven_type body: {functional_type, name, icon} */
	public function driven_type_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->post('functional_type'),
			'name' => $name,
		];
		if ($this->post('icon') != '') {
			$param['icon'] = $this->post('icon');
		}

		$this->AdminRentVehicle_m->add_driven_type($param);
		$this->created(null, 'Berhasil menambahkan Jenis Penggerak');
	}

	/** PUT admin/rentvehicle/driven_type/{id} body: {functional_type, name, icon} */
	public function driven_type_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'functional_type' => $this->put('functional_type'),
			'name' => $this->put('name'),
		];
		if ($this->put('icon') != '') {
			$param['icon'] = $this->put('icon');
		}

		$this->AdminRentVehicle_m->edit_driven_type($id, $param);
		$this->ok(null, 'Berhasil mengubah Jenis Penggerak');
	}

	/** DELETE admin/rentvehicle/driven_type/{id} */
	public function driven_type_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_driven_type($id);
		$this->ok(null, 'Berhasil menghapus Jenis Penggerak');
	}

	/* =========================================================================
	 * Fuel
	 * ========================================================================= */

	/** GET admin/rentvehicle/fuel?search=  |  GET admin/rentvehicle/fuel/{id} */
	public function fuel_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$detail = $this->AdminRentVehicle_m->get_fuel($id);
			if (!$detail) {
				return $this->not_found();
			}
			return $this->ok($detail, 'Berhasil mengambil Jenis Bahan Bakar');
		}

		$param = ['limit' => ['start' => 0, 'length' => 100000], 'search' => $this->get('search')];
		$this->ok($this->AdminRentVehicle_m->get_list_fuel($param), 'Berhasil mengambil Jenis Bahan Bakar');
	}

	/** POST admin/rentvehicle/fuel body: {name, icon} */
	public function fuel_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$name = $this->post('name');
		if (empty($name)) {
			return $this->validation_error(['name' => 'wajib diisi']);
		}

		$param = ['name' => $name];
		if ($this->post('icon') != '') {
			$param['icon'] = $this->post('icon');
		}

		$this->AdminRentVehicle_m->add_fuel($param);
		$this->created(null, 'Berhasil menambahkan Jenis Bahan Bakar');
	}

	/** PUT admin/rentvehicle/fuel/{id} body: {name, icon} */
	public function fuel_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = ['name' => $this->put('name')];
		if ($this->put('icon') != '') {
			$param['icon'] = $this->put('icon');
		}

		$this->AdminRentVehicle_m->edit_fuel($id, $param);
		$this->ok(null, 'Berhasil mengubah Jenis Bahan Bakar');
	}

	/** DELETE admin/rentvehicle/fuel/{id} */
	public function fuel_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->delete_fuel($id);
		$this->ok(null, 'Berhasil menghapus Jenis Bahan Bakar');
	}

	/* =========================================================================
	 * Vehicle listing / status
	 * ========================================================================= */

	/** GET admin/rentvehicle/vehicles?page=&limit=&search= */
	public function vehicles_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_vehicle($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/vehicle_form_options -- dropdown data (status) for the vehicle list/status form */
	public function vehicle_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['status' => $this->AdminRentVehicle_m->get_status()]);
	}

	/** PUT admin/rentvehicle/vehicle_status/{id} body: {status_id} */
	public function vehicle_status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->AdminRentVehicle_m->update_status_vehicle($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/* =========================================================================
	 * Vehicle transactions
	 * ========================================================================= */

	/** GET admin/rentvehicle/vehicle_transactions?page=&limit=&search= */
	public function vehicle_transactions_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_vehicle_transaction($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_transaction_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_vehicle_transaction_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/vehicle_transaction_form_options -- dropdown data (status) for the vehicle transaction list */
	public function vehicle_transaction_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['status' => $this->AdminRentVehicle_m->get_status()]);
	}

	/** PUT admin/rentvehicle/vehicle_transaction_cancel/{id} */
	public function vehicle_transaction_cancel_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('api/PartnerRent_m');
		$this->load->model('api/Customer_m');
		$this->load->model('api/RentVehicle_m');

		$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
		if (!$transaction_detail) {
			return $this->not_found('Transaksi tidak ditemukan');
		}
		$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);

		$this->RentVehicle_m->update_transaction_status($id, 12);
		$status = $this->RentVehicle_m->get_transaction_status_name(12);

		$timeline = [
			'transaction_id' => $id,
			'title' => $status->name,
			'description' => $status->name,
		];
		$this->RentVehicle_m->add_timeline_transaction($timeline);

		if ($transaction_detail->cash_on_delivery == 1) {
			$this->Customer_m->increase_balance($vehicle->account_id, $transaction_detail->admin_fee);
			$this->ok(null, 'Berhasil membatalkan pemesanan.');
		} else {
			$this->Customer_m->increase_balance($transaction_detail->account_id, $transaction_detail->total_payment);
			$this->ok(null, 'Berhasil membatalkan pesanan. Biaya Admin akan dikembalikan ke saldo mitra');
		}
	}

	/** PUT admin/rentvehicle/vehicle_transaction_finish/{id} */
	public function vehicle_transaction_finish_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('api/PartnerRent_m');
		$this->load->model('api/RentVehicle_m');
		$this->load->model('api/Customer_m');
		$this->load->model('api/Basic_m');

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
			$hour_overtime = $interval->h;
			$hour_overtime = $hour_overtime + ($interval->days * 24);
		}

		$data = [
			'overtime' => ($hour_overtime > 0) ? 1 : 0,
			'overtime_hour' => $hour_overtime,
			'total_overtime_fee' => ($hour_overtime * $transaction_detail->overtime_fee),
		];
		$this->RentVehicle_m->update_transaction($id, $data);
		$this->RentVehicle_m->update_transaction_status($id, 8);
		$status = $this->RentVehicle_m->get_transaction_status_name(8);

		$timeline = [
			'transaction_id' => $id,
			'title' => $status->name,
			'description' => $status->name,
		];
		$this->RentVehicle_m->add_timeline_transaction($timeline);

		$data_reward = [
			'account_id' => $transaction_detail->account_id,
			'transaction_id' => $transaction_detail->id,
			'point_debit' => $this->Basic_m->get_config_value('transaction_point_reward_customer'),
			'description' => 'Bonus Point transaksi #'.$transaction_detail->id,
		];
		$this->Basic_m->insert_point_reward($data_reward);

		$data_reward = [
			'account_id' => $vehicle->account_id,
			'point_debit' => $this->Basic_m->get_config_value('transaction_point_reward_partner'),
			'transaction_id' => $transaction_detail->id,
			'description' => 'Bonus Point transaksi #'.$transaction_detail->id,
		];
		$this->Basic_m->insert_point_reward($data_reward);

		if ($transaction_detail->cash_on_delivery == 1) {
			$this->ok(null, 'Berhasil menyelesaikan pesanan. Pastikan mitra mendapat denda keterlambatan dari pelanggan jika tersedia.');
		} else {
			$this->Customer_m->increase_balance($vehicle->account_id, ($transaction_detail->total_payment - $transaction_detail->admin_fee));
			$this->ok(null, 'Berhasil menyelesaikan pesanan. Pembayaran yang telah dipotong biaya admin akan ditambahkan ke saldo mitra');
		}
	}

	/* =========================================================================
	 * Promote-vehicle transactions
	 * ========================================================================= */

	/** GET admin/rentvehicle/vehicle_promote_transactions?page=&limit=&search= */
	public function vehicle_promote_transactions_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->AdminRentVehicle_m->get_list_promote_vehicle_transaction($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->AdminRentVehicle_m->get_total_list_promote_vehicle_transaction_filtered($param),
				'total_unfiltered' => (int) $this->AdminRentVehicle_m->get_total_list_promote_vehicle_transaction_unfiltered($param),
			]
		);
	}

	/** GET admin/rentvehicle/vehicle_promote_transaction_form_options -- dropdown data (status) for the promote transaction list */
	public function vehicle_promote_transaction_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->ok(['status' => $this->AdminRentVehicle_m->get_status()]);
	}
}

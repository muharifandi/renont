<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/MY_Api.php';

/**
 * Shared base for every REST endpoint in the application (api, admin, agent).
 *
 * Replaces the old split between key-based auth (mobile `api` module) and
 * Ion Auth session-based auth (`admin`/`agent` web panels) with a single
 * token mechanism: every account (customer, partner, agent, admin/staff)
 * authenticates via the `key` HTTP header, resolved against the `keys` table.
 * Group/role checks are done against `accounts_groups` instead of a PHP session,
 * so every endpoint here is stateless.
 *
 * Extends MY_Api (not REST_Controller directly) to reuse its key-generation
 * helpers (_generate_key/_insert_key/get_detail_key) instead of duplicating them.
 */
class REST_Base_Controller extends MY_Api
{
	/** Group ids from table `groups` -- shared constants so every controller
	 *  references the same role list instead of redefining it. */
	const GROUP_ADMIN      = 1;
	const GROUP_SUPERVISOR = 2;
	const GROUP_STAFF      = 3;
	const GROUP_PARTNER    = 4;
	const GROUP_CUSTOMER   = 5;
	const GROUP_READER     = 6;
	const GROUP_AGENT      = 7;

	/** Groups allowed into the former "admin panel" backend. */
	const STAFF_GROUP_IDS = [self::GROUP_ADMIN, self::GROUP_SUPERVISOR, self::GROUP_STAFF, self::GROUP_READER];

	/** @var stdClass|null resolved account row tied to the current request's key, or null if unauthenticated */
	protected $auth_account = null;

	/** @var int[] group ids the current authenticated account belongs to */
	protected $auth_group_ids = [];

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Standard response envelope for every endpoint in the app.
	 * Always call this instead of $this->response() directly.
	 *
	 * @param mixed  $data       payload for the 'data' key (array/object/null)
	 * @param string $message    human-readable message (Indonesian, matches rest of app)
	 * @param bool   $success    true/false -> reflected 1:1 in both 'status' and the HTTP code family
	 * @param int    $http_code  e.g. 200, 201, 400, 401, 403, 404, 422, 500
	 * @param array  $meta       optional extra info (pagination, counts, etc.)
	 */
	protected function send($data = null, $message = '', $success = true, $http_code = 200, $meta = [])
	{
		$body = [
			'status'  => (bool) $success,
			'message' => $message,
			'data'    => $data,
		];
		if (!empty($meta)) {
			$body['meta'] = $meta;
		}
		$this->response($body, $http_code);
	}

	protected function ok($data = null, $message = 'Berhasil', $meta = [])
	{
		$this->send($data, $message, true, 200, $meta);
	}

	protected function created($data = null, $message = 'Berhasil dibuat')
	{
		$this->send($data, $message, true, 201);
	}

	protected function fail($message, $http_code = 400, $errors = null)
	{
		$this->send($errors, $message, false, $http_code);
	}

	protected function not_found($message = 'Data tidak ditemukan')
	{
		$this->send(null, $message, false, 404);
	}

	protected function unauthorized($message = 'Kunci API tidak valid atau tidak disertakan')
	{
		$this->send(null, $message, false, 401);
	}

	protected function forbidden($message = 'Anda tidak memiliki akses ke sumber daya ini')
	{
		$this->send(null, $message, false, 403);
	}

	protected function validation_error($errors, $message = 'Data yang dikirim tidak valid')
	{
		$this->send($errors, $message, false, 422);
	}

	/**
	 * Resolve the account tied to the request's `key` header.
	 * Call this at the top of any endpoint that requires a logged-in account.
	 * Halts the request (sends 401 JSON and stops execution) if the key is
	 * missing or invalid -- fixes the old bug where an invalid key caused a
	 * PHP fatal error instead of a clean error response.
	 *
	 * @return stdClass the resolved accounts-table row (never null; halts otherwise)
	 */
	protected function require_auth()
	{
		$key = $this->input->get_request_header('key', TRUE);

		if (empty($key)) {
			$this->unauthorized('Header "key" wajib disertakan');
			exit;
		}

		$this->db->where(config_item('rest_key_column'), $key);
		$key_row = $this->db->get(config_item('rest_keys_table'))->row();

		if (!$key_row || (int) $key_row->level === 0) {
			$this->unauthorized('Kunci API tidak valid atau sudah dinonaktifkan');
			exit;
		}

		// date_expires is nullable for backward compatibility with keys issued
		// before this check existed -- those stay valid; every key issued from
		// now on always gets a real expiry (see MY_Api::_insert_key()).
		if (!empty($key_row->date_expires) && (int) $key_row->date_expires < time()) {
			$this->unauthorized('Kunci API sudah kedaluwarsa, silakan login kembali');
			exit;
		}

		$this->db->where('id', $key_row->account_id);
		$account = $this->db->get('accounts')->row();

		if (!$account || (int) $account->active !== 1) {
			$this->unauthorized('Akun tidak ditemukan atau belum aktif');
			exit;
		}

		$this->db->select('group_id');
		$this->db->where('account_id', $account->id);
		$groups = $this->db->get('accounts_groups')->result();
		$this->auth_group_ids = array_map(function ($g) { return (int) $g->group_id; }, $groups);

		$this->auth_account = $account;
		return $account;
	}

	/**
	 * Call after require_auth(). Halts with 403 JSON if the authenticated
	 * account does not belong to any of the given group ids.
	 *
	 * Group ids (from the `groups` table): 1=admin, 2=supervisor, 3=staff,
	 * 4=partner, 5=user(customer), 6=reader, 7=agent.
	 *
	 * @param int[] $allowed_group_ids
	 */
	protected function require_group(array $allowed_group_ids)
	{
		if (empty(array_intersect($allowed_group_ids, $this->auth_group_ids))) {
			$this->forbidden('Role akun Anda tidak diizinkan mengakses endpoint ini');
			exit;
		}
	}

	/** Convenience: require_auth() + require_group() in one call. */
	protected function require_auth_group(array $allowed_group_ids)
	{
		$this->require_auth();
		$this->require_group($allowed_group_ids);
		return $this->auth_account;
	}
}

<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Agent's own commission withdraw requests.
 *
 * GET  agent/agent/withdraw             -> list (query: page, limit, search) + withdraw_minimum + own bank accounts
 * POST agent/agent/withdraw             -> request a new withdraw (body: value, account_bank_id, description)
 */
class Agent extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('agent/Agent_m');
		$this->load->model('agent/Config_m');
	}

	/** GET agent/agent/withdraw?page=&limit=&search= */
	public function withdraw_get()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$config = $this->Config_m->get_config(['withdraw_minimum']);

		$this->ok(
			[
				'withdraws' => $this->Agent_m->get_list_withdraw($account->id, $param),
				'withdraw_minimum' => $config['withdraw_minimum'],
				'agent_banks' => $this->Config_m->get_all_agent_bank($account->id),
			],
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->Agent_m->get_total_list_withdraw_filtered($account->id, $param),
				'total_unfiltered' => (int) $this->Agent_m->get_total_list_withdraw_unfiltered($account->id, $param),
			]
		);
	}

	/** POST agent/agent/withdraw body: {value, account_bank_id, description} */
	public function withdraw_post()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);

		$value = $this->post('value');
		$account_bank_id = $this->post('account_bank_id');

		if (!is_numeric($value)) {
			return $this->validation_error(['value' => 'nominal yang dimasukan tidak valid']);
		}
		if (empty($account_bank_id)) {
			return $this->validation_error(['account_bank_id' => 'wajib diisi']);
		}

		$config = $this->Config_m->get_config(['withdraw_minimum']);
		if ($value <= $config['withdraw_minimum']) {
			return $this->fail('nominal yang dimasukan lebih kecil dari minimum pencairan', 422);
		}

		$balance = $this->Agent_m->get_agent_balance($account->id);
		if ($balance->balance < $value) {
			return $this->fail('Permintaan pencairan gagal. Saldo anda saat ini adalah Rp.'.number_format($balance->balance, 2, ',', '.').',-', 402);
		}

		$id = $this->Agent_m->request_withdraw([
			'account_id' => $account->id,
			'account_bank_id' => $account_bank_id,
			'value' => $value,
			'description' => $this->post('description'),
			'status' => 1,
		]);

		$this->created(['id' => (int) $id], 'Permintaan pencairan berhasil');
	}
}

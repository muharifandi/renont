<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/Api_Base_Controller.php';

/**
 * Partner reward programs (targets & point rewards) resource.
 */
class PartnerReward extends Api_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('PartnerReward_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Partner Reward API — RentOn');
	}

	public function scopes_get()
	{
		$this->ok(['data' => $this->PartnerReward_m->list_scope()]);
	}

	/** GET api/partnerReward/detail?reward_scope=.. */
	public function detail_get()
	{
		$account = $this->require_auth();
		$this->load->model('Partner_m');
		$this->load->model('PartnerRent_m');

		$reward_scope = $this->get('reward_scope');

		if (empty($reward_scope)) {
			$list_scope = $this->PartnerReward_m->list_scope();
			if (count($list_scope) > 0) {
				$reward_scope = $list_scope[0]->id;
			}
		}

		$reward_scope_detail = $this->PartnerReward_m->reward_scope_detail($reward_scope);
		if (!$reward_scope_detail) {
			return $this->not_found('Periode reward tidak ditemukan');
		}

		$start_date = date('Y-m-d', strtotime($reward_scope_detail->start));
		$end_date = date('Y-m-d', strtotime($reward_scope_detail->end));

		$feature = $this->Partner_m->list_feature();
		$data = [];
		foreach ($feature as $val) {
			$data[] = [
				'feature_name' => $val->name,
				'transaction_success' => $this->PartnerRent_m->count_transaction_success($account->id, $start_date, $end_date),
				'rewards' => $this->PartnerReward_m->reward_aquired($account->id, $val->id, $reward_scope, $start_date, $end_date),
			];
		}

		$this->ok(['data' => $data]);
	}

	/** POST api/partnerReward/claims body: {reward_id} */
	public function claims_post()
	{
		$account = $this->require_auth();
		$this->load->model('Customer_m');
		$this->load->model('Basic_m');

		$reward_id = $this->post('reward_id');
		if (empty($reward_id)) {
			return $this->validation_error(['reward_id' => 'wajib diisi']);
		}

		$this->PartnerReward_m->claim_reward($reward_id);

		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		$this->fcm->addRecepient($this->Customer_m->get_token($account->id));
		$this->fcm->setData(['data_type' => 'partner_reward_claim', 'id' => $reward_id]);
		$this->fcm->setNotification(['title' => 'Klaim Hadiah', 'text' => 'Klaim Hadiah Sedang diproses', 'android_channel_id' => 4, 'sound' => 'default']);
		$this->fcm->send();

		$this->fcm->clearRecepients();
		$this->fcm->setRecepients($this->Basic_m->get_all_admin_token());
		$this->fcm->setData(['data_type' => 'partner_reward_claim', 'id' => $reward_id, 'link_action' => base_url().'admin/partnerReward/list_claim']);
		$this->fcm->setNotification(['title' => 'Permintaan Klaim Hadiah', 'body' => 'ID #'.$reward_id, 'android_channel_id' => 3, 'sound' => 'default']);
		$this->fcm->send();

		$this->created(null, 'Berhasil Klaim Hadiah');
	}
}

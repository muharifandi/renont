<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Partner reward resource (admin backoffice).
 *
 * GET    admin/partnerreward                    -> list (query: page, limit, search)
 * GET    admin/partnerreward/form_options        -> dropdown data (status, reward type, reward scope, feature) for the create/edit form
 * PUT    admin/partnerreward/status/{id}         -> change status (body: status_id)
 * POST   admin/partnerreward                     -> create (multipart form, file field: img)
 * DELETE admin/partnerreward/{id}                -> delete
 * GET    admin/partnerreward/claim                -> claim-reward list (query: page, limit, search)
 * PUT    admin/partnerreward/claim_process/{id}   -> process a claim
 */
class PartnerReward extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('PartnerReward_m');
	}

	/** GET admin/partnerreward?page=&limit=&search= */
	public function index_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->PartnerReward_m->get_list($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->PartnerReward_m->get_total_list_filtered($param),
				'total_unfiltered' => (int) $this->PartnerReward_m->get_total_list_unfiltered($param),
			]
		);
	}

	/** GET admin/partnerreward/form_options -- dropdown data for the create/edit form */
	public function form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->model('Base_m');

		$this->ok([
			'status' => $this->Base_m->get_status(),
			'reward_type' => $this->PartnerReward_m->get_reward_type(),
			'reward_scope' => $this->PartnerReward_m->get_reward_scope(),
			'feature' => $this->Base_m->get_feature(),
		]);
	}

	/** PUT admin/partnerreward/status/{id} body: {status_id} */
	public function status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->PartnerReward_m->update_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** POST admin/partnerreward multipart body: {title, description, feature_id, reward_scope, reward_type, target, point_reward, img (file)} */
	public function index_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$title = $this->post('title');
		if (empty($title)) {
			return $this->validation_error(['title' => 'wajib diisi']);
		}

		$img_filename = null;
		$upload_log = null;
		if (!empty($_FILES['img']['name'])) {
			$config['upload_path'] = FCPATH.'data/rewards';
			$config['allowed_types'] = 'jpg|jpeg|png';
			$config['max_size'] = '20480';
			$config['overwrite'] = false;
			$this->load->library('upload', $config);

			if ($this->upload->do_upload('img')) {
				$img_filename = $this->upload->data('file_name');
			} else {
				$upload_log .= 'profile:'.$this->upload->display_errors()."\n";
			}
		}

		$param = [
			'title' => $title,
			'description' => $this->post('description'),
			'img' => $img_filename,
			'feature_id' => $this->post('feature_id'),
			'reward_scope' => $this->post('reward_scope'),
			'reward_type' => $this->post('reward_type'),
			'target' => $this->post('target'),
			'point_reward' => $this->post('point_reward'),
			'status' => 0,
		];

		$this->PartnerReward_m->add_reward($param);
		$this->created(['log' => $upload_log], 'Berhasil menambahkan hadiah');
	}

	/** DELETE admin/partnerreward/{id} */
	public function index_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->PartnerReward_m->delete($id);
		$this->ok(null, $id.' Dihapus');
	}

	/** GET admin/partnerreward/claim?page=&limit=&search= */
	public function claim_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->PartnerReward_m->get_list_claim_reward($param),
			'Berhasil',
			[
				'page' => $page,
				'limit' => $limit,
				'total' => (int) $this->PartnerReward_m->get_total_list_claim_reward_filtered($param),
				'total_unfiltered' => (int) $this->PartnerReward_m->get_total_list_claim_reward_unfiltered($param),
			]
		);
	}

	/** PUT admin/partnerreward/claim_process/{id} */
	public function claim_process_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('Customer_m');

		$data = ['processed' => 1];
		$this->PartnerReward_m->update_history_reward($id, $data);
		$detail = $this->PartnerReward_m->history_reward($id);

		if (!$detail) {
			return $this->not_found('Klaim hadiah tidak ditemukan');
		}

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));
		//kirim ke pelanggan
		$this->fcm->addRecepient($this->Customer_m->get_token($detail->account_id));
		$data_payload = [
			'data_type' => 'partner_claim_reward',
			'id' => $id,
		];
		$this->fcm->setData($data_payload);
		$notif = ['title' => 'Klaim Hadiah', 'text' => 'Permintaan Klaim Hadiah sedang diproses', 'android_channel_id' => 3, 'sound' => 'default'];
		$this->fcm->setNotification($notif);
		$this->fcm->send();

		$this->ok(null, $id.' Berhasil diubah');
	}
}

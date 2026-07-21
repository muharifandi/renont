<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * News resource (admin backoffice), plus its "preview/carousel" sub-resource
 * and a broadcast (push notification) action.
 *
 * GET    admin/news                        -> list (query: page, limit, search)
 * GET    admin/news/{id}                   -> detail
 * POST   admin/news                        -> create (multipart: img_banner file)
 * POST   admin/news/{id}                   -> update (multipart: img_banner file)
 *        (uses POST rather than PUT for both create and update so file uploads keep working --
 *        PHP never populates $_FILES for PUT requests, regardless of content type)
 * PUT    admin/news/status/{id}            -> change status (body: status_id)
 * DELETE admin/news/{id}                   -> delete
 * POST   admin/news/send_notification/{id} -> broadcast push notification for a news item
 * GET    admin/news/form_options           -> dropdown data for create/edit form
 * GET    admin/news/select                 -> typeahead lookup (query: search, page)
 *
 * GET    admin/news/preview                 -> preview list (query: page, limit, search)
 * GET    admin/news/preview/{id}             -> preview detail
 * POST   admin/news/preview                 -> create preview entry (body: order, news_id)
 * PUT    admin/news/preview/{id}             -> edit preview entry (body: order, news_id)
 * DELETE admin/news/preview/{id}             -> delete preview entry
 * PUT    admin/news/preview_status/{id}      -> change preview status (body: status_id)
 * GET    admin/news/preview_form_options     -> dropdown data for preview list/form
 */
class News extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/News_m');
	}

	/** GET admin/news?page=&limit=&search=  |  GET admin/news/{id} */
	public function index_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$news = $this->News_m->get_news($id);
			if (!$news) {
				return $this->not_found();
			}
			return $this->ok($news);
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->News_m->get_list($param),
			'Berhasil',
			['page' => $page, 'limit' => $limit, 'total' => (int) $this->News_m->get_total_list_filtered($param), 'total_unfiltered' => (int) $this->News_m->get_total_list_unfiltered($param)]
		);
	}

	/**
	 * POST admin/news          (multipart) -> create news
	 * POST admin/news/{id}     (multipart) -> update news
	 */
	public function index_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			return $this->_update_news($id);
		}
		return $this->_create_news();
	}

	private function _handle_banner_upload()
	{
		if (empty($_FILES['img_banner']['name'])) {
			return null;
		}

		$config['upload_path'] = FCPATH.'data/news';
		$config['allowed_types'] = '*';
		$config['max_size'] = '20480';
		$config['overwrite'] = false;
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('img_banner')) {
			return $this->upload->data('file_name');
		}

		return null;
	}

	private function _create_news()
	{
		$title = $this->post('title');
		if (empty($title)) {
			return $this->validation_error(['title' => 'wajib diisi']);
		}

		$img_filename = $this->_handle_banner_upload();

		$param = [
			'title' => $title,
			'img' => $img_filename,
			'content' => $this->post('content'),
			'user_type' => $this->post('user_type'),
			'is_voucher' => $this->post('is_voucher'),
			'voucher_id' => $this->post('voucher_id'),
		];

		$this->News_m->add_news($param);
		$this->created(null, 'Berhasil menambahkan berita');
	}

	private function _update_news($id)
	{
		$news = $this->News_m->get_news($id);
		if (!$news) {
			return $this->not_found();
		}

		$img_filename = $this->_handle_banner_upload();

		$param = [
			'title' => $this->post('title'),
			'content' => $this->post('content'),
			'user_type' => $this->post('user_type'),
			'is_voucher' => $this->post('is_voucher'),
			'voucher_id' => $this->post('voucher_id'),
		];

		if ($img_filename) {
			$param['img'] = $img_filename;
		}

		$this->News_m->edit_news($id, $param);
		$this->ok(null, 'Berhasil mengubah berita');
	}

	/** PUT admin/news/status/{id} body: {status_id} */
	public function status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->News_m->update_status($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** DELETE admin/news/{id} */
	public function index_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->News_m->delete($id);
		$this->ok(null, $id.' Dihapus');
	}

	/** POST admin/news/send_notification/{id} -- broadcast a push notification for a news item */
	public function send_notification_post($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$this->load->model('Partner_m');
		$this->load->model('Customer_m');
		$news = $this->News_m->get_news($id);
		if (!$news) {
			return $this->not_found();
		}

		//notification
		$this->load->library('Fcm');
		$this->load->config('fcm');
		$this->fcm->setApiKey($this->config->item('fcm_api_key_android', 'fcm'));

		if ($news->user_type == 4) {
			//kirim ke mitra
			$tokens = $this->Partner_m->get_all_token();
			$this->fcm->setRecepients($tokens);
			$data_payload = ['data_type' => 'news', 'id' => $id];
			$this->fcm->setData($data_payload);
			$notif = ['title' => $news->title, 'text' => $news->title, 'image' => base_url().'data/news/'.$news->img, 'android_channel_id' => 3, 'sound' => 'default'];
			$this->fcm->setNotification($notif);
			$this->fcm->send();

			$this->ok(null, 'Berhasil mengirim notifikasi berita "'.$news->title.'" ke mitra dengan total penerima '.sizeof($tokens).' Mitra');
		} else {
			//kirim ke pelanggan
			$tokens = $this->Customer_m->get_all_token();
			$this->fcm->setRecepients($tokens);
			$data_payload = ['data_type' => 'news', 'id' => $id];
			$this->fcm->setData($data_payload);
			$notif = ['title' => $news->title, 'text' => $news->title, 'image' => base_url().'data/news/'.$news->img, 'android_channel_id' => 3, 'sound' => 'default'];
			$this->fcm->setNotification($notif);
			$this->fcm->send();

			$this->ok(null, 'Berhasil mengirim notifikasi berita "'.$news->title.'" ke pelanggan dengan total penerima '.sizeof($tokens).' Pelanggan');
		}
		//end notification
	}

	/** GET admin/news/form_options -- dropdown options for the create/edit form */
	public function form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->model('Base_m');

		$this->ok([
			'status' => $this->Base_m->get_status(),
			'user_type' => $this->Base_m->get_user_type_filtered(),
		]);
	}

	/** GET admin/news/select?search=&page= -- typeahead lookup */
	public function select_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$page = (int) ($this->get('page') ?: 0);
		$param = ['search' => $this->get('search'), 'limit' => ['start' => $page * 30, 'length' => 30]];

		$this->ok([
			'items' => $this->News_m->get_list($param),
			'total_count' => (int) $this->News_m->get_total_list_filtered($param),
		]);
	}

	/** GET admin/news/preview?page=&limit=&search=  |  GET admin/news/preview/{id} */
	public function preview_get($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		if ($id !== null) {
			$preview = $this->News_m->get_news_preview($id);
			if (!$preview) {
				return $this->not_found();
			}
			return $this->ok($preview, 'Berhasil mengambil Pratinjau');
		}

		$page = max(1, (int) ($this->get('page') ?: 1));
		$limit = min((int) ($this->get('limit') ?: 20), 100);
		$param = ['limit' => ['start' => ($page - 1) * $limit, 'length' => $limit], 'search' => $this->get('search')];

		$this->ok(
			$this->News_m->get_list_preview($param),
			'Berhasil',
			['page' => $page, 'limit' => $limit, 'total' => (int) $this->News_m->get_total_list_preview_filtered($param), 'total_unfiltered' => (int) $this->News_m->get_total_list_preview_unfiltered($param)]
		);
	}

	/** POST admin/news/preview body: {order, news_id} */
	public function preview_post()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$news_id = $this->post('news_id');
		if (empty($news_id)) {
			return $this->validation_error(['news_id' => 'wajib diisi']);
		}

		$param = [
			'order' => $this->post('order'),
			'news_id' => $news_id,
		];
		$this->News_m->add_preview($param);
		$this->created(null, 'Berhasil menambahkan Pratinjau');
	}

	/** PUT admin/news/preview/{id} body: {order, news_id} */
	public function preview_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$param = [
			'order' => $this->put('order'),
			'news_id' => $this->put('news_id'),
		];
		$this->News_m->edit_preview($id, $param);
		$this->ok(null, 'Berhasil mengubah Pratinjau');
	}

	/** DELETE admin/news/preview/{id} */
	public function preview_delete($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->News_m->delete_preview($id);
		$this->ok(null, $id.' Dihapus');
	}

	/** PUT admin/news/preview_status/{id} body: {status_id} */
	public function preview_status_put($id = null)
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}
		$this->News_m->update_status_preview($id, $this->put('status_id'));
		$this->ok(null, $id.' Status diubah');
	}

	/** GET admin/news/preview_form_options -- dropdown options for the preview list/form */
	public function preview_form_options_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);
		$this->load->model('Base_m');

		$this->ok(['status' => $this->Base_m->get_status()]);
	}
}

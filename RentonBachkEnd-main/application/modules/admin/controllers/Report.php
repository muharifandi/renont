<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * PDF report generation (admin backoffice).
 *
 * These endpoints return an actual PDF binary (via TCPDF's Output(..., 'I')),
 * not JSON -- everything else in the app is JSON, but a report download is
 * the one legitimate exception.
 *
 * GET  admin/report/agent_withdraw_invoice/{id}    -> single agent withdraw invoice PDF
 * POST admin/report/agent_transaction              -> body: {ids, start_date, end_date, group}
 * POST admin/report/partner_transaction             -> body: {ids, start_date, end_date, group}
 * POST admin/report/topup                            -> body: {ids, start_date, end_date, group}
 * POST admin/report/withdraw                          -> body: {ids, start_date, end_date, group}
 * POST admin/report/partner_promote_transaction        -> body: {ids, start_date, end_date, group}
 */
class Report extends REST_Base_Controller
{
	private $pdf;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Report_m');
	}

	/** GET admin/report */
	public function index_get()
	{
		$this->ok(null, 'Admin Report API — RentOn');
	}

	/**
	 * @return bool whether both start_date and end_date were posted
	 */
	private function _has_required_report_params()
	{
		return (bool) ($this->post('start_date') && $this->post('end_date'));
	}

	private function init_report()
	{
		// set default header data
		$this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$this->pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$this->pdf->setLanguageArray($l);
		}
	}

	/** GET admin/report/agent_withdraw_invoice/{id} */
	public function agent_withdraw_invoice_get($id = null)
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		if (empty($id)) {
			return $this->validation_error(['id' => 'wajib diisi']);
		}

		$agent_withdraw = $this->Report_m->get_agent_withdraw($id);

		$this->load->library('MYPDF');
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor($account->first_name.' '.$account->last_name);
		$this->pdf->SetTitle('Faktur Pencairan dana Marketing');
		$this->pdf->SetSubject('Marketing #'.$agent_withdraw->account_id.' - '.$agent_withdraw->first_name.' '.$agent_withdraw->last_name);
		$this->pdf->SetKeywords('RentOn, Marketing, Pencairan');

		$this->init_report();

		$this->pdf->AddPage();

		$data = array('agent_withdraw' => $agent_withdraw);
		$result = $this->load->view('report_invoice_agent_withdraw', $data, TRUE);
		$this->pdf->writeHTML($result, true, false, true, false, '');
		$this->pdf->Output('pencairan_dana_marketing_'.$id.'__'.date('d_m_Y_H_i').'.pdf', 'I');
	}

	/** POST admin/report/agent_transaction body: {ids, start_date, end_date, group} */
	public function agent_transaction_post()
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!$this->_has_required_report_params()) {
			return $this->validation_error(['start_date' => 'wajib diisi', 'end_date' => 'wajib diisi']);
		}

		$param = array(
			'ids' => $this->post('ids'),
			'start_date' => $this->post('start_date'),
			'end_date' => $this->post('end_date'),
			'group' => (boolean) $this->post('group'),
		);
		$agents_transaction = $this->Report_m->get_agent_transaction($param);

		$this->load->library('MYPDF');
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor($account->first_name.' '.$account->last_name);
		$this->pdf->SetTitle('Transaksi Marketing');
		$this->pdf->SetSubject('Marketing');
		$this->pdf->SetKeywords('RentOn, Marketing, Transaksi');

		$this->init_report();

		if ($param['group'] == true) {
			foreach ($agents_transaction as $val) {
				$this->pdf->AddPage();
				$result = $this->load->view('report_agent_transaction', $val, TRUE);
				$this->pdf->writeHTML($result, true, false, true, false, '');
			}
		} else {
			$this->pdf->AddPage();
			$result = $this->load->view('report_agent_transaction', $agents_transaction, TRUE);
			$this->pdf->writeHTML($result, true, false, true, false, '');
		}

		$this->pdf->Output('transaksi_marketing__'.date('d_m_Y_H_i').'.pdf', 'I');
	}

	/** POST admin/report/partner_transaction body: {ids, start_date, end_date, group} */
	public function partner_transaction_post()
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!$this->_has_required_report_params()) {
			return $this->validation_error(['start_date' => 'wajib diisi', 'end_date' => 'wajib diisi']);
		}

		$param = array(
			'ids' => $this->post('ids'),
			'start_date' => $this->post('start_date'),
			'end_date' => $this->post('end_date'),
			'group' => (boolean) $this->post('group'),
		);
		$partners_transaction = $this->Report_m->get_partner_transaction($param);

		$this->load->library('MYPDF');
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor($account->first_name.' '.$account->last_name);
		$this->pdf->SetTitle('Transaksi Mitra');
		$this->pdf->SetSubject('Mitra');
		$this->pdf->SetKeywords('RentOn, Mitra, Transaksi');

		$this->init_report();

		if ($param['group'] == true) {
			foreach ($partners_transaction as $val) {
				$this->pdf->AddPage();
				$result = $this->load->view('report_partner_transaction', $val, TRUE);
				$this->pdf->writeHTML($result, true, false, true, false, '');
			}
		} else {
			$this->pdf->AddPage();
			$result = $this->load->view('report_partner_transaction', $partners_transaction, TRUE);
			$this->pdf->writeHTML($result, true, false, true, false, '');
		}

		$this->pdf->Output('transaksi_mitra__'.date('d_m_Y_H_i').'.pdf', 'I');
	}

	/** POST admin/report/topup body: {ids, start_date, end_date, group} */
	public function topup_post()
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!$this->_has_required_report_params()) {
			return $this->validation_error(['start_date' => 'wajib diisi', 'end_date' => 'wajib diisi']);
		}

		$param = array(
			'ids' => $this->post('ids'),
			'start_date' => $this->post('start_date'),
			'end_date' => $this->post('end_date'),
			'group' => (boolean) $this->post('group'),
		);

		$topups = $this->Report_m->get_topup($param);

		$this->load->library('MYPDF');
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor($account->first_name.' '.$account->last_name);
		$this->pdf->SetTitle('Uang Masuk Perusahaan');
		$this->pdf->SetSubject('Admin');
		$this->pdf->SetKeywords('RentOn, Admin, Uang Masuk');

		$this->init_report();

		if ($param['group'] == true) {
			foreach ($topups as $val) {
				$this->pdf->AddPage();
				$result = $this->load->view('report_topup', $val, TRUE);
				$this->pdf->writeHTML($result, true, false, true, false, '');
			}
		} else {
			$this->pdf->AddPage();
			$result = $this->load->view('report_topup', $topups, TRUE);
			$this->pdf->writeHTML($result, true, false, true, false, '');
		}

		$this->pdf->Output('uang_masuk__'.date('d_m_Y_H_i').'.pdf', 'I');
	}

	/** POST admin/report/withdraw body: {ids, start_date, end_date, group} */
	public function withdraw_post()
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!$this->_has_required_report_params()) {
			return $this->validation_error(['start_date' => 'wajib diisi', 'end_date' => 'wajib diisi']);
		}

		$param = array(
			'ids' => $this->post('ids'),
			'start_date' => $this->post('start_date'),
			'end_date' => $this->post('end_date'),
			'group' => (boolean) $this->post('group'),
		);

		$withdraws = $this->Report_m->get_withdraw($param);

		$this->load->library('MYPDF');
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor($account->first_name.' '.$account->last_name);
		$this->pdf->SetTitle('Uang Masuk Perusahaan');
		$this->pdf->SetSubject('Admin');
		$this->pdf->SetKeywords('RentOn, Admin, Uang Masuk');

		$this->init_report();

		if ($param['group'] == true) {
			foreach ($withdraws as $val) {
				$this->pdf->AddPage();
				$result = $this->load->view('report_withdraw', $val, TRUE);
				$this->pdf->writeHTML($result, true, false, true, false, '');
			}
		} else {
			$this->pdf->AddPage();
			$result = $this->load->view('report_withdraw', $withdraws, TRUE);
			$this->pdf->writeHTML($result, true, false, true, false, '');
		}

		$this->pdf->Output('pencairan__'.date('d_m_Y_H_i').'.pdf', 'I');
	}

	/** POST admin/report/partner_promote_transaction body: {ids, start_date, end_date, group} */
	public function partner_promote_transaction_post()
	{
		$account = $this->require_auth_group(self::STAFF_GROUP_IDS);
		if (!$this->_has_required_report_params()) {
			return $this->validation_error(['start_date' => 'wajib diisi', 'end_date' => 'wajib diisi']);
		}

		$param = array(
			'ids' => $this->post('ids'),
			'start_date' => $this->post('start_date'),
			'end_date' => $this->post('end_date'),
			'group' => (boolean) $this->post('group'),
		);
		$partners_promote_transaction = $this->Report_m->get_partner_promote_transaction($param);

		$this->load->library('MYPDF');
		$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor($account->first_name.' '.$account->last_name);
		$this->pdf->SetTitle('Transaksi Promosi Mitra');
		$this->pdf->SetSubject('Mitra');
		$this->pdf->SetKeywords('RentOn, Mitra, Transaksi');

		$this->init_report();

		if ($param['group'] == true) {
			foreach ($partners_promote_transaction as $val) {
				$this->pdf->AddPage();
				$result = $this->load->view('report_partner_promote_transaction', $val, TRUE);
				$this->pdf->writeHTML($result, true, false, true, false, '');
			}
		} else {
			$this->pdf->AddPage();
			$result = $this->load->view('report_partner_promote_transaction', $partners_promote_transaction, TRUE);
			$this->pdf->writeHTML($result, true, false, true, false, '');
		}

		$this->pdf->Output('transaksi_promosi_mitra__'.date('d_m_Y_H_i').'.pdf', 'I');
	}
}

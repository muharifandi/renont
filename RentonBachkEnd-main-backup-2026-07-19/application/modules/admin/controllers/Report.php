<?php defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Report extends AdminController
	{
		private $pdf;
		public function __construct()
		{
			parent::__construct();
			$this->load->model('Report_m');
			
		}
		
		public function index()
		{
			$this->show('report',$data,TRUE);
		}
		
		private function check_parameter()
		{
			if(!$this->input->post('start_date') || !$this->input->post('end_date'))
			{
				die("Jangan Refresh, harap klik lagi dari tombol laporan");
			}
		}
		
		private function init_report()
		{
			// set default header data
			$this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
			
			// set header and footer fonts
			$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			
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
		
		public function agent_withdraw_invoice($id)
		{
			$agent_withdraw = $this->Report_m->get_agent_withdraw($id);
			
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor($user->first_name." ".$user->last_name);
			$this->pdf->SetTitle('Faktur Pencairan dana Marketing');
			$this->pdf->SetSubject('Marketing #'.$agent_withdraw->account_id." - ".$agent_withdraw->first_name." ".$agent_withdraw->last_name);
			$this->pdf->SetKeywords('RentOn, Marketing, Pencairan');
			
			$this->init_report();
			
			$this->pdf->AddPage();
			
			$data = array(
			'agent_withdraw' => $agent_withdraw,
			);
			$result = $this->load->view('report_invoice_agent_withdraw',$data,TRUE);
			$this->pdf->writeHTML($result,true,false,true,false,'');
			$this->pdf->Output('pencairan_dana_marketing_'.$id."__".date('d_m_Y_H_i').".pdf", 'I');
		}
		
		public function agent_transaction_report()
		{	
			$this->check_parameter();
			$param = array(
			'ids' => $this->input->post('ids'),
			'start_date' => $this->input->post('start_date'),
			'end_date' => $this->input->post('end_date'),
			'group' => (boolean)$this->input->post('group'),
			);
			$agents_transaction = $this->Report_m->get_agent_transaction($param);
			
			
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor($user->first_name." ".$user->last_name);
			$this->pdf->SetTitle('Transaksi Marketing');
			$this->pdf->SetSubject('Marketing');
			$this->pdf->SetKeywords('RentOn, Marketing, Transaksi');
			
			$this->init_report();
			
			if($param['group'] == true)
			{
				foreach($agents_transaction as $val)
				{
					$this->pdf->AddPage();
					$result = $this->load->view('report_agent_transaction',$val,TRUE);
					$this->pdf->writeHTML($result,true,false,true,false,'');
				}
			}else
			{
				$this->pdf->AddPage();
				$result = $this->load->view('report_agent_transaction',$agents_transaction,TRUE);
				$this->pdf->writeHTML($result,true,false,true,false,'');
			}
			
			$this->pdf->Output("transaksi_marketing__".date('d_m_Y_H_i').".pdf", 'I');
			
		}
		
		public function partner_transaction_report()
		{	
			$this->check_parameter();
			$param = array(
			'ids' => $this->input->post('ids'),
			'start_date' => $this->input->post('start_date'),
			'end_date' => $this->input->post('end_date'),
			'group' => (boolean)$this->input->post('group'),
			);
			$partners_transaction = $this->Report_m->get_partner_transaction($param);
			
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor($user->first_name." ".$user->last_name);
			$this->pdf->SetTitle('Transaksi Mitra');
			$this->pdf->SetSubject('Mitra');
			$this->pdf->SetKeywords('RentOn, Mitra, Transaksi');
			
			$this->init_report();
			
			if($param['group'] == true)
			{
				foreach($partners_transaction as $val)
				{
					$this->pdf->AddPage();
					$result = $this->load->view('report_partner_transaction',$val,TRUE);
					$this->pdf->writeHTML($result,true,false,true,false,'');
				}
			}else
			{
				$this->pdf->AddPage();
				$result = $this->load->view('report_partner_transaction',$partners_transaction,TRUE);
				$this->pdf->writeHTML($result,true,false,true,false,'');
			}
			
			
			$this->pdf->Output("transaksi_mitra__".date('d_m_Y_H_i').".pdf", 'I');
			
		}
		
		public function topup_report()
		{	
			$this->check_parameter();
			$param = array(
			'ids' => $this->input->post('ids'),
			'start_date' => $this->input->post('start_date'),
			'end_date' => $this->input->post('end_date'),
			'group' => (boolean)$this->input->post('group'),
			);
			
			
			$topups = $this->Report_m->get_topup($param);
			
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor($user->first_name." ".$user->last_name);
			$this->pdf->SetTitle('Uang Masuk Perusahaan');
			$this->pdf->SetSubject('Admin');
			$this->pdf->SetKeywords('RentOn, Admin, Uang Masuk');
			
			$this->init_report();
			
			if($param['group'] == true)
			{
				foreach($topups as $val)
				{
					$this->pdf->AddPage();
					$result = $this->load->view('report_topup',$val,TRUE);
					$this->pdf->writeHTML($result,true,false,true,false,'');
				}
			}else
			{
				$this->pdf->AddPage();
				$result = $this->load->view('report_topup',$topups,TRUE);
				$this->pdf->writeHTML($result,true,false,true,false,'');
			}
			
			
			$this->pdf->Output("uang_masuk__".date('d_m_Y_H_i').".pdf", 'I');
			
		}
		
		public function withdraw_report()
		{	
			$this->check_parameter();
			$param = array(
			'ids' => $this->input->post('ids'),
			'start_date' => $this->input->post('start_date'),
			'end_date' => $this->input->post('end_date'),
			'group' => (boolean)$this->input->post('group'),
			);
			
			
			$withdraws = $this->Report_m->get_withdraw($param);
			
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor($user->first_name." ".$user->last_name);
			$this->pdf->SetTitle('Uang Masuk Perusahaan');
			$this->pdf->SetSubject('Admin');
			$this->pdf->SetKeywords('RentOn, Admin, Uang Masuk');
			
			$this->init_report();
			
			if($param['group'] == true)
			{
				foreach($withdraws as $val)
				{
					$this->pdf->AddPage();
					$result = $this->load->view('report_withdraw',$val,TRUE);
					$this->pdf->writeHTML($result,true,false,true,false,'');
				}
			}else
			{
				$this->pdf->AddPage();
				$result = $this->load->view('report_withdraw',$withdraws,TRUE);
				$this->pdf->writeHTML($result,true,false,true,false,'');
			}
			
			
			$this->pdf->Output("pencairan__".date('d_m_Y_H_i').".pdf", 'I');
			
		}
		
		public function partner_promote_transaction_report()
		{	
			$this->check_parameter();
			$param = array(
			'ids' => $this->input->post('ids'),
			'start_date' => $this->input->post('start_date'),
			'end_date' => $this->input->post('end_date'),
			'group' => (boolean)$this->input->post('group'),
			);
			$partners_promote_transaction = $this->Report_m->get_partner_promote_transaction($param);
			
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			// set document information
			$user = $this->ion_auth_model->user($this->session->userdata('user_id'))->row();
			$this->pdf->SetCreator(PDF_CREATOR);
			$this->pdf->SetAuthor($user->first_name." ".$user->last_name);
			$this->pdf->SetTitle('Transaksi Promosi Mitra');
			$this->pdf->SetSubject('Mitra');
			$this->pdf->SetKeywords('RentOn, Mitra, Transaksi');
			
			$this->init_report();
			
			if($param['group'] == true)
			{
				foreach($partners_promote_transaction as $val)
				{
					$this->pdf->AddPage();
					$result = $this->load->view('report_partner_promote_transaction',$val,TRUE);
					$this->pdf->writeHTML($result,true,false,true,false,'');
				}
			}else
			{
				$this->pdf->AddPage();
				$result = $this->load->view('report_partner_promote_transaction',$partners_promote_transaction,TRUE);
				$this->pdf->writeHTML($result,true,false,true,false,'');
			}
			
			
			$this->pdf->Output("transaksi_promosi_mitra__".date('d_m_Y_H_i').".pdf", 'I');
			
		}
		
		public function example()
		{
			$this->load->library('MYPDF');
			$this->pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			
			$this->init_report();
			$this->pdf->AddPage();
			
			$tabel = '
			<div style="text-align:center"><h3>Faktur Pencairan Dana Marketing</h3></div>
			<div style="align:center">
			<table border="0">
			<tbody>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>Kode Marketing</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">209</td>
			<td width="115"></td>
			</tr>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>Nama Marketing</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">Muh Arifandi</td>
			<td width="115"></td>
			</tr>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>No Rekening</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">12345677</td>
			<td width="115"></td>
			</tr>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>Nama Bank</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">Bank Rakyat Indonesia</td>
			<td width="115"></td>
			</tr>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>Nominal</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">Rp.250.000</td>
			<td width="115"></td>
			</tr>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>Status</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">Selesai</td>
			<td width="115"></td>
			</tr>
			<tr>
			<td width="115"></td>
			<td width="200" style="text-align:center"><b>Tanggal Pencairan</b></td>
			<td width="10"><b>:</b></td>
			<td width="200">26/03.2020</td>
			<td width="115"></td>
			</tr>
			</tbody>
			</table>
			</div>
			';
			$this->pdf->writeHTML($tabel,true,false,true,false,'');
			$this->pdf->Output('file-pdf-codeigniter.pdf', 'I');
		}
		
	}			
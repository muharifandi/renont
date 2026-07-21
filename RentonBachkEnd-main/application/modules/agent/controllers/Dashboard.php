<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Agent dashboard summary/statistics resource -- scoped to the authenticated
 * agent's own recruited partners (see admin/Dashboard.php for the platform-wide
 * equivalent this mirrors).
 */
class Dashboard extends REST_Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Dashboard_m');
	}

	public function index_get()
	{
		$this->ok(null, 'Agent Dashboard API — RentOn');
	}

	/** GET agent/dashboard/summary?start=YYYY-MM-DD&end=YYYY-MM-DD */
	public function summary_get()
	{
		$account = $this->require_auth_group([self::GROUP_AGENT]);
		$agent_id = $account->id;

		$start = $this->get('start');
		$end = $this->get('end');

		if (empty($start) || empty($end)) {
			return $this->validation_error(['start' => 'wajib diisi', 'end' => 'wajib diisi']);
		}

		$total_partner_transaction = $this->Dashboard_m->get_total_partner_transaction($agent_id, $start, $end);
		$total_commission_transaction = $this->Dashboard_m->get_total_commission_transaction($agent_id, $start, $end);
		$total_partner_register = $this->Dashboard_m->get_total_partner_register($agent_id, $start, $end);
		$total_partner = $this->Dashboard_m->get_total_partner($agent_id);
		$revenue = $this->Dashboard_m->get_total_revenue($agent_id, $start, $end);

		$start_date = new DateTime($start);
		$end_date = (new DateTime($end))->modify('+1 day');
		$daterange = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);

		$chart_label = [];
		$chart_value = [];
		foreach ($daterange as $date) {
			$day_date = $date->format('Y-m-d');
			$temp_revenue = $this->Dashboard_m->get_total_revenue($agent_id, $day_date, $day_date);
			$chart_label[] = $date->format('d M');
			$chart_value[] = (float) $temp_revenue;
		}

		$this->ok([
			'total_partner_transaction' => (int) $total_partner_transaction,
			'total_commission_transaction' => (int) $total_commission_transaction,
			'total_partner_register' => (int) $total_partner_register,
			'total_partner' => (int) $total_partner,
			'revenue' => (float) $revenue,
			'chart' => ['revenue' => ['label' => $chart_label, 'value' => $chart_value]],
		]);
	}
}

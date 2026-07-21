<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Base_Controller.php';

/**
 * Admin dashboard summary/statistics resource.
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
		$this->ok(null, 'Admin Dashboard API — RentOn');
	}

	/** GET admin/dashboard/summary?start=YYYY-MM-DD&end=YYYY-MM-DD */
	public function summary_get()
	{
		$this->require_auth_group(self::STAFF_GROUP_IDS);

		$start = $this->get('start');
		$end = $this->get('end');

		if (empty($start) || empty($end)) {
			return $this->validation_error(['start' => 'wajib diisi', 'end' => 'wajib diisi']);
		}

		$total_register = $this->Dashboard_m->get_total_register($start, $end);
		$total_partner = $this->Dashboard_m->get_total_partner($start, $end);
		$total_claim_reward = $this->Dashboard_m->get_total_claim_reward($start, $end);
		$total_topup = $this->Dashboard_m->get_total_topup($start, $end);
		$total_withdraw = $this->Dashboard_m->get_total_withdraw($start, $end);
		$total_transaction = $this->Dashboard_m->get_total_transaction_rent_vehicle($start, $end);
		$total_admin_fee_transaction = $this->Dashboard_m->get_total_admin_fee_transaction_rent_vehicle($start, $end);
		$total_income_promote_transaction = $this->Dashboard_m->get_total_income_promote_transaction_rent_vehicle($start, $end);
		$total_agent_commission = $this->Dashboard_m->get_total_agent_commission($start, $end);
		$revenue = $total_admin_fee_transaction + $total_income_promote_transaction - $total_agent_commission;

		$start_date = new DateTime($start);
		$end_date = (new DateTime($end))->modify('+1 day');
		$daterange = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);

		$chart_label = [];
		$chart_value = [];
		foreach ($daterange as $date) {
			$day_date = $date->format('Y-m-d');
			$temp_revenue = $this->Dashboard_m->get_total_admin_fee_transaction_rent_vehicle($day_date, $day_date)
				+ $this->Dashboard_m->get_total_income_promote_transaction_rent_vehicle($day_date, $day_date)
				- $this->Dashboard_m->get_total_agent_commission($day_date, $day_date);
			$chart_label[] = $date->format('d M');
			$chart_value[] = $temp_revenue;
		}

		$this->ok([
			'total_register' => (int) $total_register,
			'total_partner' => (int) $total_partner,
			'total_transaction' => (int) $total_transaction,
			'total_claim_reward' => (int) $total_claim_reward,
			'total_admin_fee_transaction' => (float) $total_admin_fee_transaction,
			'total_income_promote_transaction' => (float) $total_income_promote_transaction,
			'total_agent_commission' => (float) $total_agent_commission,
			'revenue' => (float) $revenue,
			'total_topup' => (int) $total_topup,
			'total_withdraw' => (int) $total_withdraw,
			'chart' => ['revenue' => ['label' => $chart_label, 'value' => $chart_value]],
		]);
	}
}

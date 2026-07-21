<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends AdminController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Dashboard_m');
	}
	
	public function index()
	{
		redirect('admin/dashboard/summary','refresh');
	}
	public function summary()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function get_summary($start,$end)
	{
		$total_register = $this->Dashboard_m->get_total_register($start,$end);
		$total_partner = $this->Dashboard_m->get_total_partner($start,$end);
		$total_claim_reward = $this->Dashboard_m->get_total_claim_reward($start,$end);
		$total_topup = $this->Dashboard_m->get_total_topup($start,$end);
		$total_withdraw = $this->Dashboard_m->get_total_withdraw($start,$end);
		$total_transaction = 0;
		$total_transaction += $this->Dashboard_m->get_total_transaction_rent_vehicle($start,$end);
		
		$total_admin_fee_transaction = 0;
		$total_admin_fee_transaction += $this->Dashboard_m->get_total_admin_fee_transaction_rent_vehicle($start,$end);
		
		$total_income_promote_transaction = 0;
		$total_income_promote_transaction += $this->Dashboard_m->get_total_income_promote_transaction_rent_vehicle($start,$end);
		
		$total_agent_commission = 0;
		$total_agent_commission += $this->Dashboard_m->get_total_agent_commission($start,$end);
		
		
		$revenue = $total_admin_fee_transaction + $total_income_promote_transaction - $total_agent_commission;
		
		
		$start_date = new DateTime($start);
		$end_date = new DateTime($end);
		$end_date = $end_date->modify( '+1 day' ); 
		$interval = $start_date->diff($end_date);
		
		$daterange = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);

		$chart = new stdClass();
		$chart->revenue = new stdClass();
		$chart->revenue->label = array();
		$chart->revenue->value = array();
		
		foreach($daterange as $date){
			$day_date = $date->format("Y-m-d");
			$temp_admin_fee_transaction = $this->Dashboard_m->get_total_admin_fee_transaction_rent_vehicle($day_date,$day_date);
			$temp_income_promote_transaction = $this->Dashboard_m->get_total_income_promote_transaction_rent_vehicle($day_date,$day_date);
			$temp_agent_commission = $this->Dashboard_m->get_total_agent_commission($day_date,$day_date);
		
			$temp_revenue = $temp_admin_fee_transaction + $temp_income_promote_transaction - $temp_agent_commission;
			
			/**
			$row = new stdClass();
			$row->label = $date->format('d M Y');
			$row->admin_fee = $temp_admin_fee_transaction;
			$row->income_promote = $temp_income_promote_transaction;
			$row->agent_commission = $temp_agent_commission;
			$row->revenue = $temp_revenue;
			
			$chart[] = $row;
			
			**/
			$chart->revenue->label[] = $date->format('d M');
			$chart->revenue->value[] = $temp_revenue;
			
		}
		
		$result = array(
			'total_register' => $total_register,
			'total_partner' => $total_partner,
			'total_transaction' => $total_transaction,
			'total_claim_reward' => $total_claim_reward,
			'total_admin_fee_transaction' => "Rp. ".number_format($total_admin_fee_transaction, 0 ,",",".").",-",
			'total_income_promote_transaction' => "Rp. ".number_format($total_income_promote_transaction, 0 ,",",".").",-",
			'total_agent_commission' => "Rp. ".number_format($total_agent_commission, 0 ,",",".").",-",
			'revenue' => "Rp. ".number_format($revenue, 0 ,",",".").",-",
			'total_topup' => $total_topup,
			'total_withdraw' => $total_withdraw,
			'chart' => $chart,
		);
		echo json_encode($result);
	}
	
	
	
	
}
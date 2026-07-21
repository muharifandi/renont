<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends AgentController
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Dashboard_m');
	}
	
	public function index()
	{
		redirect('agent/dashboard/summary','refresh');
	}
	
	public function summary()
	{
		$this->show('dashboard',$data,TRUE);
	}
	
	public function get_summary($start,$end)
	{
		$agent_id = $this->get_user_id();
		$total_partner_transaction = $this->Dashboard_m->get_total_partner_transaction($agent_id,$start,$end);
		$total_commission_transaction = $this->Dashboard_m->get_total_commission_transaction($agent_id,$start,$end);
		$total_partner_register = $this->Dashboard_m->get_total_partner_register($agent_id,$start,$end);
		$total_partner = $this->Dashboard_m->get_total_partner($agent_id);
		
		$revenue = $this->Dashboard_m->get_total_revenue($agent_id,$start,$end);
		
		
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
		
			$temp_revenue = $this->Dashboard_m->get_total_revenue($agent_id,$day_date,$day_date);
			$chart->revenue->label[] = $date->format('d M');
			$chart->revenue->value[] = $temp_revenue;
			
		}
		
		$result = array(
			'total_partner_transaction' => $total_partner_transaction,
			'total_commission_transaction' => $total_commission_transaction,
			'total_partner_register' => $total_partner_register,
			'total_partner' => $total_partner,
			'revenue' => "Rp. ".number_format($revenue, 0 ,",",".").",-",
			'chart' => $chart,
		);
		echo json_encode($result);
	}
}
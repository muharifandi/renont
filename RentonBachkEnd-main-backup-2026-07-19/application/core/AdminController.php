<?php defined('BASEPATH') or exit('No direct script access allowed');

class AdminController extends MY_Controller
{
    public $CI;
    protected $data = array();

    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in())
		{
			redirect('auth/login', 'refresh');
		}else
		{
			$user = $this->ion_auth->user()->row();
			$groups = $this->ion_auth->get_users_groups($user->id)->result();
			
			$in_scope = false;
			foreach($groups as $val)
			{
				if($val->id == 1 || $val->id == 2 || $val->id == 3 || $val->id == 6) 
					$in_scope = true;
			}
			
			if(!$in_scope)
				redirect('auth/restrict', 'refresh');
			
		}
    }

   
    public function show($template_name, $data, $return)
    {
		$this->CI =& get_instance();
        if ($return === true) {
            $content  = $this->CI->load->view('templates/header', $this->data);
            $content .= $this->CI->load->view('templates/main_header', $this->data);
            $content .= $this->CI->load->view('templates/main_sidebar', $this->data);
            $content .= $this->CI->load->view($template_name, $data);
            $content .= $this->CI->load->view('templates/footer', $this->data);
            $content .= $this->CI->load->view('templates/control_sidebar', $this->data);

            return $content;
        } else {
            $this->CI->load->view($template_name, $this->data);
        }
    }
}

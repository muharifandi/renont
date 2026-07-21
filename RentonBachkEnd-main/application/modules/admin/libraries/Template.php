<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Template
{	
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
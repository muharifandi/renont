<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'libraries/REST_Controller.php';

class MY_Api extends REST_Controller {

    public function __construct() {
        parent::__construct();
        
    }
	
	public function get_detail_key($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->get(config_item('rest_keys_table'))
            ->row();
    }
	
	public function _generate_key()
    {
        do
        {
            // Generate a random salt
            $salt = base_convert(bin2hex($this->security->get_random_bytes(64)), 16, 36);

            // If an error occurred, then fall back to the previous method
            if ($salt === FALSE)
            {
                $salt = hash('sha256', time() . mt_rand());
            }

            $new_key = substr($salt, 0, config_item('rest_key_length'));
        }
        while ($this->_key_exists($new_key));

        return $new_key;
    }
	
	private function _key_exists($key)
    {
        return $this->rest->db
            ->where(config_item('rest_key_column'), $key)
            ->count_all_results(config_item('rest_keys_table')) > 0;
    }
	
	public function _insert_key($key, $data)
    {
        $data[config_item('rest_key_column')] = $key;
        $data['date_created'] = function_exists('now') ? now() : time();
        // Keys never used to expire (audit finding) -- every key issued from
        // here on is valid 30 days from creation; checked in
        // REST_Base_Controller::require_auth().
        if (!isset($data['date_expires'])) {
            $data['date_expires'] = time() + (30 * 24 * 60 * 60);
        }

        return $this->rest->db
            ->set($data)
            ->insert(config_item('rest_keys_table'));
    }
	
}
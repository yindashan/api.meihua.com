<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  用户model
 */
class User_token_model extends CI_Model {

    private $table_name = 'ci_user_token';

	function __construct()
	{
		parent::__construct();
	}

    function get_token_info($token, $fields = '*') {
        $this->db->select($fields);
        $this->db->where('hash_key', $token);
        $this->db->limit(1);
        $result = $this->db->get($this->table_name); 
        if (false === $result) {
            log_message('error', 'get_token_info error: msg['.$this->db->_error_message().']');
            return false; 
        } else if (0 === $result->num_rows) {
            return NULL; 
        } else {
            return $result->result_array()[0];
        }
    }

}

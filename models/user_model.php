<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  用户model
 */
class User_model extends CI_Model {

    private $table_name = 'ci_user';
	function __construct()
	{
		parent::__construct();
    }

    function get_user_by_phone($phone, $fields = '*') {
        $this->db->select($fields);
        $this->db->where('umobile', $phone);
        $this->db->limit(1);
        $result = $this->db->get($this->table_name);
        if (false === $result) {
            return false;
        }
        if (0 < $result->num_rows) {
            return $result->result_array()[0];
        }
        return NULL;
    }

    function get_user_info($uid, $fields = '*') {
        $this->db->select($fields);
        $this->db->where('id', $uid);
        $this->db->limit(1);
        $result = $this->db->get($this->table_name); 
        if (false === $result) {
            log_message('error', 'get_user_info error: msg['.$this->db->_error_message().']');
            return false; 
        } else if (0 === $result->num_rows) {
            return NULL; 
        } else {
            return $result->result_array()[0];
        }
    }

    function get_uid_by_sname($sname) {

        $this->db->select('id');
        $this->db->where('sname', $sname);

        $result = $this->db->get($this->table_name); 
        if($result->num_rows > 0) {
            return $result->result_array();
        }   
    }

    function get_uids_by_phone($tele_list) {
        $uids = array();
        for ($i = 0; $i < count($tele_list); $i = $i + 10) {
            $sub_list = array_slice($tele_list, $i, 10); 
            $this->db->select('id'); 
            $this->db->where_in('umobile', $sub_list);
            $result = $this->db->get($this->table_name);
            if ($result && $result->num_rows > 0) {
                foreach ($result->result_array() as $row) {
                    $uids[] = $row['id']; 
                }
            }
        }
        return $uids;
    }

    function get_uid_by_phone($phone) {
        $this->db->select('id');
        $this->db->where('umobile', $phone);
        $result = $this->db->get($this->table_name);
        if (false === $result) {
            return false;
        }
        if ($result->num_rows >= 0) {
            return $result->result_array()[0]['id'];
        }

        return NULL;
    }

    function create_user($request) {
        $result = $this->db->insert($this->table_name, $request);
        if ($this->db->affected_rows() > 0) {
            $uid = $this->db->insert_id();
            return $uid;
        }

        return false;
    }

    function get_uid_by_oauth($auth_name, $auth_id) {
        $this->db->select('id');
        $this->db->where('oauth_name', $auth_name);
        $this->db->where('oauth_key', $auth_id);
        $result = $this->db->get($this->table_name);
        if (false === $result) {
            return false;
        }
        if ($result->num_rows > 0) {
            return $result->result_array()[0]['id'];
        }

        return NULL;
    }

    function update_by_uid($uid, $data) {
        $this->db->where('id', $uid);
        $result = $this->db->update($this->table_name, $data);
        if ($this->db->affected_rows() > 0) {
            return $result;
        }

        return false;
    }

    function add($query) {
        $result = $this->db->insert($this->table_name, $query);
        if (false === $result) {
            return false;
        }
        if(0 < $this->db->affected_rows()) {
            return $this->db->insert_id();
        }

        return NULL;
    }
}

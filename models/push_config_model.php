<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Push_config_model extends CI_Model {
    private $table_name = 'ci_config_push';

    function __construct() {
        parent::__construct();
    }

    function update($uid, $config) {
        $sql = "INSERT INTO ".$this->table_name." (`uid`, `config`) values (?,?) ON DUPLICATE KEY UPDATE `config`=?";
        try {
            $this->db->query($sql, array($uid, $config, $config));
        } catch (Exception $e) {
            return false;
        }
        //echo $this->db->last_query();exit;

        return true;
    }

    function get($uid) {
        $this->db->select('config');
        $this->db->from($this->table_name);
        $this->db->where('uid', $uid);

        $query = $this->db->get();

        return $query->result_array();
    }
}

      

<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Favorite_model extends CI_Model {
    private $table_name = 'ci_favorite';

	function __construct() {
		parent::__construct();
	}

    /**
     * add favorite
     * @param $uid long user id
     * @param $tid long tweet id
     *
     * @return true/false
     */
    function add($uid, $tid) {
        $data = array(
            'uid' => $uid,
            'tid' => $tid,
            'ctime' => time(),
        );
        $result = $this->db->insert($this->table_name, $data);
        log_message('error', $this->db->last_query());
        log_message('error', var_export($result, true));
        log_message('error', $this->db->affected_rows());
        if ($result) {
            return $this->db->affected_rows();
        }
        return false;
    }


    /**
     * cancel favorite
     * @param $uid long user id
     * @param $tid long 贴子id
     *
     * @return true/false
     */
    function remove($uid, $tid) {
        $data = array(
            'uid' => $uid,
            'tid' => $tid,
        );
        $result = $this->db->delete($this->table_name, $data);
        if (false !== $result) {
            return $this->db->affected_rows();
        } else {
            return false;
        }
    }

    /**
     * get favorite list by uid
     * @param $uid long
     *
     * @return tweet list
     */
    function get_favorite_list_by_uid($uid, $limit) {
        $this->db->select('*');
        $this->db->where('uid', $uid);
        $this->db->order_by('ctime', 'desc');
        $this->db->limit($limit);
        
        $result = $this->db->get($this->table_name);
        log_message('error', $this->db->last_query());
        if($result->num_rows > 0) {
            return $result->result_array();
        }
        return false;
    }


    /**
     * get next favorite list by uid
     * @param $uid 
     * @param $fid
     *
     * @return tweet list
     */
    function get_next_favorite_list_by_uid($uid, $fid, $limit) {
        $this->db->select('*');
        $this->db->where('uid', $uid);
        $this->db->where('fid <', $fid);
        $this->db->order_by('ctime', 'desc');
        $this->db->limit($limit);

        $result = $this->db->get($this->table_name);
        if($result->num_rows > 0) {
            return $result->result_array();
        }
        return false;
    }
}



/* End of file favorite_model.php */
/* Location: ./application/models/favorite_model.php*/

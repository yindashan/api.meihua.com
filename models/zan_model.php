<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Zan_model extends CI_Model {
    private $table_name = 'ci_zan';

	function __construct() {
		parent::__construct();
	}

    /**add favourite
     * @param $uid long user id
     * @param $tid long 贴子id
     *
     * @return true/false
     */
    function add($uid, $tid, $sname, $owneruid) {
        $data = array(
            'uid' => $uid,
            'tid' => $tid,
            'username' => $sname,
            'owneruid' => $owneruid,
            'ctime' => time(),
        );
        $result = $this->db->insert($this->table_name, $data);
        log_message('error', $this->db->last_query());
        log_message('error', var_export($result, true));
        if ($result) {
            return $this->db->affected_rows();
        }
        else {
            return false;
        }
    }


    /**
     * cancel favourite
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
     * judge a list weibo if favourite by one user
     * @param $uid user id
     * @param $tid_list 
     * @return dict key:weibo_id, value:true/false
     */
    function get_tid_dianzan_dict($uid, $tid_list) {
        $this->db->select('tid');
        $this->db->from($this->table_name);
        $this->db->where('uid', $uid);
        $this->db->where_in('tid', $tid_list);
        $query = $this->db->get();
        $dict = array();
        foreach($query->result_array() as $value) {
            $id = $value['tid'];
            $dict[$id] = true;
        }

        foreach ($tid_list as $id) {
            if (!isset($dict[$id])) {
                $dict[$id] = false;
            }
        }

        return $dict;
    }

    /**
     *get total count by tid
     *@param $tid long 
     *@return favorite count of tid
     */
    function get_count_by_tid($tid) {
        $this->db->from($this->table_name);
        $this->db->where('tid', $tid);

        return $this->db->count_all_results();
    }

    /**
     * get favourite user list by tid
     * @param $tid long
     *
     * @return usename list
     */
    function get_user_list($tid, $limit) {
        $this->db->select('uid, username, ctime');
        $this->db->from($this->table_name);
        $this->db->where('tid', $tid);
        $this->db->order_by('ctime', 'asc');
        $this->db->limit($limit);
        
        $query = $this->db->get();
        if (!$query) {
            return false;
        }
        
        return $query->result_array();
    }

    /**
     * get praised tweet list
     * 
     * @param uid
     *
     * @return tweet list
     */
    function get_praised_list_by_uid($uid) {
        $sql = "SELECT count(`tid`) as 'praisednum',`tid` FROM `ci_zan` where `owneruid` = ".$uid." group by tid order by praisednum desc";
        $result = $this->db->query($sql);
        log_message('error', $this->db->last_query());

        if($result->num_rows() > 0) {
            return $result->result_array(); 
        }
        return false;
    }
 }



/* End of file zan_model.php */
/* Location: ./application/models/zan_model.php*/

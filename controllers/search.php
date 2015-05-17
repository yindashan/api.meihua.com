<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


class Search extends MY_Controller {

	/**
	 * 构造函数
	 *
	 * @return void
	 * @author
	 **/
	function __construct()
	{
		parent::__construct();
        $this->load->library('seclient');
	}

    /**
     * 搜索结果页
     */
	function test()
    {
        $request = $this->request_array;
        $wd = $request['wd'];
        $pn = isset($request['pn']) ? $request['pn'] : 0;
        $rn = isset($request['rn']) ? $request['rn'] : 10;
        $type = isset($request['type']) ? intval($request['type']) : 1;
        $catalog = isset($request['catalog']) ? intval($request['type']) : 0;
        $tag = isset($request['tag']) ? explode("|", $request['tag']) : array();
        $se_result = $this->seclient->search(array(
            'wd' => $wd,
            'pn' => $pn,
            'rn' => $rn,
            'type' => $type,
            'catalog' => $catalog,
            'tag' => $tag,
        ));
        echo json_encode($se_result);
	}

}

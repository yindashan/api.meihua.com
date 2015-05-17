<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


class Material extends MY_Controller {

    private $img1;
    private $img2;
    private $img3;
	/**
	 * 构造函数
	 *
	 * @return void
	 * @author
	 **/
	function __construct()
	{
		parent::__construct();

        //$this->load->model('Community_model');
        //$this->load->model('Zan_model');
        $this->load->model('tweet_model');

        $this->request_array['rn'] = isset($this->request_array['rn']) ? $this->request_array['rn'] : 5;
        //$this->_set_token_check(true);
        $this->img1 = array(
            'small' =>array(
                'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                'width' => 250,
                'height' => 200,
            ),  
            'middle' =>array(
                'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                'width' => 350,
                'height' => 300,
            ),  
            'big' =>array(
                'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                'width' => 450,
                'height' => 400,
            ),  
        );  
        $this->img2 = array(
            'small' =>array(
                'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                'width' => 350,
                'height' => 200,
            ),  
            'middle' =>array(
                'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                'width' => 450,
                'height' => 300,
            ),  
            'big' =>array(
                'url' => 'http://a.hiphotos.baidu.com/image/pic/item/e4dde71190ef76c6ef2f8e929e16fdfaae51678d.jpg',
                'width' => 550,
                'height' => 400,
            ),  
        );  
	}

    /**
     * 列表页
     */
	function material_list()
    {
        $request = $this->request_array;
        $response = $this->response_array;

        /*
        if(!isset($request['type'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            goto end;
        }
        $type = $request['type'];
         */

        $tid = 13612;
        $tweet = $this->get_tweet_detail($tid);

        /*
        $tweet_content = $tweet['content'];
        $tweet['content'] = $tweet_content[0];
         */


        $tweet['imgs'] = $tweet['imgs'][0];
        for($i=0; $i<25; $i++) {
            $r = rand(1, 10);
            if(intval($r % 3) == 0) {
                $tweet['imgs']['s']['h'] = 400;
            }else if(intval($r % 3) == 1){
                $tweet['imgs']['s']['h'] = 500;
            }else if(intval($r % 3) == 2) {
                $tweet['imgs']['s']['h'] = 600;
            }
            $res_content[] = $tweet;
        }
        $response['data'] = array(
            'content' => $res_content,
            'choose_type' => 4,    
        );

        end:
            $this->renderJson($response['errno'], $response['data']);

    }

    /**
     * 搜索结果页
     */
	function search()
    {
        $request = $this->request_array;
        $response = $this->response_array;

        /*
        if(!isset($request['type'])) {
            $response['errno'] = STATUS_ERR_REQUEST;
            goto end;
        }
        $type = $request['type'];
         */

        $tid = 13612;
        $tweet = $this->get_tweet_detail($tid);

        $tweet['imgs'] = $tweet['imgs'][0];
        for($i=0; $i<25; $i++) {
            $r = rand(1, 10);
            if(intval($r % 3) == 0) {
                $tweet['imgs']['s']['h'] = 400;
            }else if(intval($r % 3) == 1){
                $tweet['imgs']['s']['h'] = 500;
            }else if(intval($r % 3) == 2) {
                $tweet['imgs']['s']['h'] = 600;
            }
            $res_content[] = $tweet;
        }
        $response['data'] = array(
            'content' => $res_content,
            'choose_type' => 4,    
        );

        end:
        $this->renderJson($response['errno'], $response['data']);

	}


}

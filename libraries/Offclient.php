<?php
require_once __DIR__."/Thrift/ClassLoader/ThriftClassLoader.php";

use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Protocol\TBinaryProtocol as TBinaryProtocol;  
use Thrift\Transport\TSocket as TSocket;  
use Thrift\Transport\TFramedTransport as TFramedTransport;  

class Offclient{

    private $socket = null;
    private $transport = null;
    private $protocol = null;
    private $client = null;

    function __construct() {
        $loader = new ThriftClassLoader();
        $loader->registerNamespace('Thrift', __DIR__);
        $loader->registerDefinition('offhub', realpath(__DIR__.'/..').'/service/' );
        $loader->register();
    }

    function __destruct() {
        //$this -> dis_connect();
    }

    /**
    * @Theme  : 
    * @Return : boolean
    */
    public function connect() {
        $this->socket = new TSocket('mhback1', 9029);
        $this->socket->setSendTimeout(10000);
        $this->socket->setRecvTimeout(20000);

        $this->transport = new TFramedTransport($this->socket);
        $this->protocol = new TBinaryProtocol($this->transport);
        $this->client = new \offhub\PostServiceClient($this->protocol); 
        $this->transport->open(); 
    }
    public function dis_connect() {
        $this->transport->close();
        $this->client = null;
        $this->protocol = null;
        $this->transport = null;
        $this->socket = null;
    }

    public function SendNewPost($post_params) {
        try {
            $ps_request = new \offhub\PostServiceRequest();
            $ps_request->tid = isset($post_params['tid']) ? $post_params['tid'] : 0;
            $ps_request->uid = isset($post_params['uid']) ? $post_params['uid'] : 0;;
            $ps_request->title = isset($post_params['title']) ? $post_params['title'] : '';
            $ps_request->content = isset($post_params['content']) ? $post_params['content'] : '';
            $ps_request->img = isset($post_params['img']) ? $post_params['img'] : '';
            $ps_request->ctime = isset($post_params['ctime'])  ? $post_params['ctime'] : time();
            $ps_request->tags = isset($post_params['tags']) ? $post_params['tags'] : '';
            $ps_request->type = isset($post_params['type']) ? $post_params['type'] : -1;
            $ps_request->f_catalog = isset($post_params['f_catalog']) ? $post_params['f_catalog'] : '';
            $ps_request->s_catalog = isset($post_params['s_catalog']) ? $post_params['s_catalog'] : '';
            $this->connect();     
            $res = $this->client->SendNewPost($ps_request);
            $this->dis_connect();
            return $res;
        } catch (Exception $e) {
            log_message('error', 'send new post error, msg['.$e-> getMessage().']');
            return false;
        }
    }

    public function send_event($tid, $type) {
        try {
            $request = new \offhub\EventServiceRequest(); 
            $request->tid = $tid;
            $request->type = $type;
            $this->connect();
            $this->client->SendNewEvent($request);
            $this->dis_connect();
        } catch (Exception $e) {
            log_message('error', 'send new event error, msg['.$e->getMessage().']'); 
        } 
    }

}



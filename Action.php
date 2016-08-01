<?php
class Access_Action implements Widget_Interface_Do
{

    private $response;
    private $request;

    public function __construct()
    {
        $this->response = Typecho_Response::getInstance();
        $this->request = Typecho_Request::getInstance();
    }

    public function execute()
    {
    }

    public function action()
    {
    }

    public function ip()
    {
        $ip = $this->request->get('ip');
        $response = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip);
        exit($response);
    }

}

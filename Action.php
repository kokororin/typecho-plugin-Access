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

    public function ipip()
    {
        $ip = $this->request->get('ip');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.ipip.net/ip.html');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('ip' => $ip)));
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.ipip.net/ip.html');
        $result = curl_exec($ch);
        curl_close($ch);
        echo $result;
    }

}

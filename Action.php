<?php
class Access_Action implements Widget_Interface_Do
{

    private $response;
    private $request;
    private $access;

    public function __construct()
    {
        $this->response = Typecho_Response::getInstance();
        $this->request = Typecho_Request::getInstance();
        require_once __DIR__ . '/Access_Bootstrap.php';
        $this->access = new Access_Core();
    }

    public function execute()
    {
    }

    public function action()
    {
    }

    public function ip()
    {
        $this->response->setContentType('application/json');
        try {
            $this->checkAuth();
            $ip = $this->request->get('ip');
            $response = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip);
            if (!$response) {
                throw new Exception('HTTP request failed');
            }
            exit($response);
        } catch (Exception $e) {
            exit(Json::encode(array(
                'code' => 100,
                'message' => $e->getMessage(),
            )));
        }
    }

    public function deleteLogs()
    {
        $this->response->setContentType('application/json');
        try {
            $this->checkAuth();
            $data = @file_get_contents('php://input');
            $data = Json::decode($data, true);
            if (!is_array($data)) {
                throw new Exception('params invalid');
            }
            $this->access->deleteLogs($data);
            exit(Json::encode(array(
                'code' => 0,
            )));

        } catch (Exception $e) {
            exit(Json::encode(array(
                'code' => 100,
                'message' => $e->getMessage(),
            )));
        }
    }

    protected function checkAuth()
    {
        if (!$this->access->isAdmin()) {
            throw new Exception('Access Denied');
        }
    }

}

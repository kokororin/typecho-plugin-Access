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

    public function writeLogs()
    {
        $this->access->writeLogs($this->request->u);
        $this->response->setStatus(206);
        exit;
    }

    public function ip()
    {
        $this->response->setContentType('application/json');
        try {
            $this->checkAuth();
            $ip = $this->request->get('ip');
            $response = Access_Ip::find($ip);
            if (is_array($response)) {
                $response = array(
                    'code' => 0,
                    'data' => implode(' ', $response),
                );
            } else {
                $response = array(
                    'code' => 100,
                    'message' => '解析ip失败',
                );
            }
            exit(Json::encode($response));
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

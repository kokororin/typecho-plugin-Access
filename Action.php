<?php
require_once __DIR__ . '/Access_Bootstrap.php';

class Access_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $access;

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);

        $this->access = new Access_Core();
    }

    public function execute()
    {}

    public function action()
    {}

    public function writeLogs()
    {
        $image = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAQUAP8ALAAAAAABAAEAAAICRAEAOw==');
        $this->response->setContentType('image/gif');
        if ($this->access->config->writeType == 1) {
            $this->access->writeLogs(null, $this->request->u, $this->request->cid, $this->request->mid);
        }
        echo $image;
    }

    protected function checkAuth()
    {
        if (!$this->access->isAdmin()) {
            throw new Exception('Access Denied', 401);
        }
    }

    public function migration() {
        try {
            $this->checkAuth(); # 鉴权
            $rpcType = $this->request->get('rpc'); # 业务类型
            $logs = new Access_Migration($this->request);
            $data = $logs->invoke($rpcType); # 进行业务分发并调取数据
            $errCode = 0;
            $errMsg = 'ok';
        } catch (Exception $e) {
            $data = null;
            $errCode = $e->getCode();
            $errMsg = $e->getMessage();
        }

        $this->response->throwJson([
            'code' => $errCode,
            'message' => $errMsg,
            'data' => $data
        ]);
    }

    public function logs() {
        try {
            $this->checkAuth(); # 鉴权
            $rpcType = $this->request->get('rpc'); # 业务类型
            $logs = new Access_Logs($this->request);
            $data = $logs->invoke($rpcType); # 进行业务分发并调取数据
            $errCode = 0;
            $errMsg = 'ok';
        } catch (Exception $e) {
            $data = null;
            $errCode = $e->getCode();
            $errMsg = $e->getMessage();
        }

        $this->response->throwJson([
            'code' => $errCode,
            'message' => $errMsg,
            'data' => $data
        ]);
    }

    public function statistic() {
        try {
            $this->checkAuth(); # 鉴权
            if(!$this->request->isGet())
                throw new Exception('Method Not Allowed', 405);

            $rpcType = $this->request->get('rpc'); # 业务类型
            $statistic = new Access_Statistic($this->request);
            $data = $statistic->invoke($rpcType); # 进行业务分发并调取数据
            $errCode = 0;
            $errMsg = 'ok';
        } catch (Exception $e) {
            $data = null;
            $errCode = $e->getCode();
            $errMsg = $e->getMessage();
        }

        $this->response->throwJson([
            'code' => $errCode,
            'message' => $errMsg,
            'data' => $data
        ]);
    }
}

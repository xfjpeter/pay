<?php
namespace johnxu\pay\alipay;

use johnxu\pay\alipay\Surport;
use johnxu\pay\Http;
use johnxu\pay\Request;

class Alipay implements IPayInterface
{
    /**
     * config
     */
    public $config = [];

    /**
     * alipay payload
     */
    public $payload;

    /**
     * construct
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->payload = [
            'app_id'      => $this->config['app_id'],
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $this->config['return_url'],
            'notify_url'  => $this->config['notify_url'],
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
        ];
    }

    /**
     * pay
     *
     * @access public
     *
     * @param  string $method pay method
     * @param  array  $params business data
     *
     * @return mixed;
     */
    public function pay(string $method, array $params = [])
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($method);

        if ($class instanceof IPayInterface) {
            throw new \Exception('not found ' . $class);
        }
        $this->payload['biz_content'] = json_encode($params, JSON_UNESCAPED_UNICODE);
        $app                          = new $class();

        return $app->pay($this);
    }

    /**
     * query order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return mixed       [description]
     */
    public function query(array $data)
    {
        $payload = $this->getData($data, 'alipay.trade.query');

        return $this->requestApi($payload);
    }

    /**
     * query order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return mixed       [description]
     */
    public function refundQuery(array $data)
    {
        $payload = $this->getData($data, 'alipay.trade.fastpay.refund.query');

        return $this->requestApi($payload);
    }

    /**
     * refund order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return mixed
     */
    public function refund(array $data)
    {
        $payload = $this->getData($data, 'alipay.trade.refund');

        return $this->requestApi($payload);
    }

    /**
     * close order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return mixed
     */
    public function close(array $data)
    {
        $payload = $this->getData($data, 'alipay.trade.close');

        return $this->requestApi($payload);
    }

    public function cancel(array $data)
    {}

    /**
     * verify signature
     *
     * @access public
     *
     * @return johnxu\pay\Alipay
     */
    public function verify()
    {
        $this->tmp = Request::{Request::method()}();

        $str = Surport::getStringParam((Surport::sorts($this->tmp, true)));

        $res = Surport::verifySign($str, $this->tmp['sign'], $this->config['ali_public_key']);

        if (!$res) {
            // throw new \Exception('verify signature error!');
            return false;
        }

        return $this;
    }

    /**
     * get sign data
     *
     * @access public
     *
     * @param  string $key
     *
     * @return string
     */
    public function __get(string $key): string
    {
        return isset($this->tmp[$key]) ? $this->tmp[$key] : '';
    }

    /**
     * get data
     *
     * @access public
     *
     * @param  array  $data
     * @param  string $method
     *
     * @return array
     */
    public function getData(array $data, string $method): array
    {
        $this->payload['method']      = $method;
        $this->payload['biz_content'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $payload                      = Surport::sorts($this->payload);
        $payload['sign']              = Surport::generateSign(Surport::getStringParam($payload), $this->config['app_private_key']);

        return $payload;
    }

    /**
     * request alipay api
     *
     * @access public
     *
     * @param  array  $data data
     * @param  string $method POST|GET
     *
     * @return mixed
     */
    public function requestApi(array $data, string $method = 'post')
    {
        $headers = ['Accept: application/json'];
        $res     = Http::instance()->{$method}($this->config['api_url'], $data);
        if ($res) {
            return $res->toObject();
        } else {
            return false;
        }
    }
}

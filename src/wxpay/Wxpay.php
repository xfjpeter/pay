<?php
namespace johnxu\pay\wxpay;

use johnxu\pay\WxpayException;

class Wxpay implements WxpayInterface
{
    public $payload = [];
    public $config  = [];

    /**
     * construct data
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config['api_uri'] = 'https://api.mch.weixin.qq.com';
        $this->config            = array_merge($this->config, $config);
        $this->payload           = [
            'appid'            => $this->config['appid'],
            'mch_id'           => $this->config['mch_id'],
            'nonce_str'        => Support::instance()->generateNonceStr(),
            'sign_type'        => 'MD5',
            'spbill_create_ip' => Support::instance()->getClientIp(),
            'notify_url'       => $this->config['notify_url'],
        ];
        $this->param = [
            'appid'     => $this->config['appid'],
            'mch_id'    => $this->config['mch_id'],
            'nonce_str' => Support::instance()->generateNonceStr(),
            'sign_type' => 'MD5',
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
        $app           = new $class();
        $this->payload = array_merge($this->payload, $params);

        return $app->pay($this);
    }

    /**
     * Query order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return array|void
     */
    public function query(array $data)
    {
        $uri     = $this->config['api_uri'] . '/pay/orderquery';
        $payload = array_merge($this->param, $data);
        if (!isset($payload['transaction_id']) && !isset($payload['out_trade_no'])) {
            throw new WxpayException("transaction_id OR out_trade_no Not Empty");
        }
        $data = Support::arrayToXml(
            Support::instance()->signature($payload, $this->config['key'])
        );

        return $this->requestApi($uri, $data);
    }

    /**
     * Reverse order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return array|void
     */
    public function reverse(array $data)
    {
        $uri     = $this->config['api_uri'] . '/secapi/pay/reverse';
        $payload = array_merge($this->param, $data);
        if (!isset($payload['transaction_id']) && !isset($payload['out_trade_no'])) {
            throw new WxpayException("transaction_id OR out_trade_no Not Empty");
        }
        $data = Support::arrayToXml(
            Support::instance()->signature($payload, $this->config['key'])
        );

        return $this->requestApi($uri, $data, 'POST', $this->config['apiclient_cert'], $this->config['apiclient_key']);
    }

    /**
     * Close order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return mixed|array
     */
    public function close(array $data)
    {
        $uri     = $this->config['api_uri'] . '/pay/closeorder';
        $payload = array_merge($this->param, $data);
        if (!isset($payload['out_trade_no'])) {
            throw new WxpayException("transaction_id OR out_trade_no Not Empty");
        }
        $data = Support::arrayToXml(
            Support::instance()->signature($payload, $this->config['key'])
        );

        return $this->requestApi($uri, $data);
    }

    /**
     * Refund order
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return mixed|array
     */
    public function refund(array $data)
    {
        $uri     = $this->config['api_uri'] . '/secapi/pay/refund';
        $payload = array_merge($this->param, $data);
        if (!isset($payload['transaction_id']) && !isset($payload['out_trade_no'])) {
            throw new WxpayException("transaction_id OR out_trade_no Not Empty");
        }
        $data = Support::arrayToXml(
            Support::instance()->signature($payload, $this->config['key'])
        );

        return $this->requestApi($uri, $data, 'POST', $this->config['apiclient_cert'], $this->config['apiclient_key']);
    }

    /**
     * Query refund
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return array|void
     */
    public function queryRefund(array $data)
    {
        $uri     = $this->config['api_uri'] . '/pay/refundquery';
        $payload = array_merge($this->param, $data);
        if (!isset($payload['transaction_id']) && !isset($payload['out_trade_no'])) {
            throw new WxpayException("transaction_id OR out_trade_no Not Empty");
        }
        $data = Support::arrayToXml(
            Support::instance()->signature($payload, $this->config['key'])
        );

        return $this->requestApi($uri, $data);
    }

    public function download(array $data)
    {
    }

    /**
     * Jugde signature
     *
     * @access public
     *
     * @param  string $data
     *
     * @return bool
     */
    public function verify(array $data): bool
    {
        if (!isset($data['sign'])) {
            throw new WxpayException("Not found sign in data");
        }
        $signature = $data['sign'];
        $res       = Support::instance()->signature($data);
        if ($res['sign'] == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get wechat return info
     *
     * @access public
     *
     * @param  string $param
     *
     * @return array
     */
    public function unifiedorder(string $param)
    {
        $uri = $this->config['api_uri'] . '/pay/unifiedorder';

        return $this->requestApi($uri, $param);
    }

    /**
     * Requset Api
     *
     * @access public
     *
     * @param  string $uri
     * @param  string $data
     *
     * @return mixed
     */
    protected function requestApi(string $uri, string $data, $method = 'POST', $cert = null, $key = null)
    {
        $res = Support::instance()->requestApi($uri, $data, 'POST', $cert, $key);
        if ($res) {
            $res = Support::instance()->xmlToArray($res);
        } else {
            throw new WxpayException("Requset Wechat api error");
        }

        try {
            if ($res['return_code'] == 'FAIL') {
                throw new WxpayException($res['return_msg']);
            }
        } catch (WxpayException $e) {
            exit($e->customFunction());
        }

        return $res;
    }
}

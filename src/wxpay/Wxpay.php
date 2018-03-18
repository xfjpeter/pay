<?php
namespace johnxu\pay\wxpay;

class Wxpay implements WxpayInterface
{
    protected $payload = [];
    protected $config  = [];

    /**
     * construct data
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->payload = [
            'appid'            => $this->config['appid'],
            'mch_id'           => $this->config['mch_id'],
            'nonce_str'        => Support::instance()->generateNonceStr(),
            'sign_type'        => 'HMAC-SHA256',
            'spbill_create_ip' => Support::instance()->getClientIp(),
            'notify_url'       => $this->config['notify_url'],
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

    public function query(array $data)
    {
    }

    public function close(array $data)
    {
    }

    public function refund(array $data)
    {
    }

    public function queryRefund(array $data)
    {
    }

    public function download(array $data)
    {
    }
}

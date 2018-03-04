<?php
namespace johnxu\pay\alipay;

/**
 * Scan pay
 */
class Scan
{
    /**
     * pay
     *
     * @access public
     *
     * @param  Alipay $alipay
     *
     * @return mixed
     */
    public function pay(Alipay $alipay)
    {
        $alipay->payload['method']      = $this->getMethod();
        $alipay->payload['biz_content'] = json_encode(
            array_merge(
                json_decode($alipay->payload['biz_content'], true),
                []
            ), JSON_UNESCAPED_UNICODE
        );

        $payload         = Surport::sorts($alipay->payload);
        $payload['sign'] = Surport::generateSign(Surport::getStringParam($payload), $alipay->config['app_private_key']);

        return $alipay->requestApi($payload, 'get');
    }

    /**
     * Get method config.
     *
     * @access protected
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'alipay.trade.precreate';
    }
}

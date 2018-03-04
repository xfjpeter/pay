<?php
namespace johnxu\pay\alipay;

/**
 * Wap pay
 */
class Wap
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
                ['product_code' => $this->getProductCode()]
            ), JSON_UNESCAPED_UNICODE
        );

        $payload         = Surport::sorts($alipay->payload);
        $payload['sign'] = Surport::generateSign(Surport::getStringParam($payload), $alipay->config['app_private_key']);

        $uri = $alipay->config['api_url'] . '?' . Surport::getStringParam($payload, true);

        @header('location:' . $uri);
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
        return 'alipay.trade.wap.pay';
    }
    /**
     * Get productCode config.
     *
     * @access protected
     *
     * @return string
     */
    protected function getProductCode(): string
    {
        return 'QUICK_WAP_WAY';
    }
}

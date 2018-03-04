<?php
namespace johnxu\pay\alipay;

/**
 * Face pay
 */
class Face
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
                [
                    'product_code' => $this->getProductCode(),
                    'scene'        => 'bar_code',
                ]
            ), JSON_UNESCAPED_UNICODE
        );

        $payload         = Surport::sorts($alipay->payload);
        $payload['sign'] = Surport::generateSign(Surport::getStringParam($payload), $alipay->config['app_private_key']);

        $uri = $alipay->config['api_url'] . '?' . Surport::getStringParam($payload, true);
        // @header('location:' . $uri);

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
        return 'alipay.trade.pay';
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
        return 'FACE_TO_FACE_PAYMENT';
    }
}

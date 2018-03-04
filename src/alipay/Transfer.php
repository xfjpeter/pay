<?php
namespace johnxu\pay\alipay;

/**
 * Transfer pay
 */
class Transfer
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
     * refund order
     *
     * @access public
     *
     * @param  Alipay $alipay
     * @param  array   $data
     *
     * @return mixed
     */
    public function refund(Alipay $alipay, array $data)
    {
        $payload = $alipay->getData($data, 'alipay.fund.trans.order.query');

        return $alipay->requestApi($payload);
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
        return 'alipay.fund.trans.toaccount.transfer';
    }
}

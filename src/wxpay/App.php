<?php
namespace johnxu\pay\wxpay;

class App
{
    /**
     * Get app pay string
     *
     * @access public
     *
     * @param  Wxpay  $wxpay
     *
     * @return string
     */
    public function pay(Wxpay $wxpay)
    {
        $wxpay->payload = array_merge([
            'trade_type' => $this->getTradeType(),
            'product_id' => Support::instance()->generateNonceStr(),
        ], $wxpay->payload);
        $payload = Support::instance()->arrayToXml(
            Support::instance()->signature($wxpay->payload, $wxpay->config['key'])
        );
        $result = $wxpay->unifiedorder($payload);

        if ($result['return_code'] == 'SUCCESS' && $result['return_msg'] == 'OK') {
            $prepay_id         = $result['prepay_id'];
            $data['appid']     = $wxpay->config['appid'];
            $data['partnerid'] = $wxpay->config['mch_id'];
            $data['prepayid']  = $result['prepay_id'];
            $data['noncestr']  = Support::instance()->generateNonceStr();
            $data['timestamp'] = time();
            $data['package']   = 'Sign=WXPay';
            $signature         = Support::instance()->signature($data, $wxpay->config['key']);

            return json_encode($signature, JSON_UNESCAPED_UNICODE);
        } else {
            throw new WxpayException("Unifiedorder return error: {$this->returnData['return_msg']}");
        }
    }

    /**
     * Get pay method
     *
     * @access public
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'APP';
    }
}

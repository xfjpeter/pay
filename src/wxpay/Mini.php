<?php
/**
 * Author: johnxu
 * Date:   2018/3/30 0030
 * Email:  fsyzxz@163.com
 * Link:   https://www.johnxu.net/
 */

namespace johnxu\pay\wxpay;

class Mini
{
    /**
     * Miniprogram pay
     *
     * @access public
     *
     * @param Wxpay $wxpay
     *
     * @return string
     * @throws \Exception
     */
    public function pay(Wxpay $wxpay)
    {
        $wxpay->payload = array_merge(
            [
                'trade_type' => $this->getTradeType(),
                'product_id' => Support::instance()->generateNonceStr(),
            ], $wxpay->payload);
        $payload        = Support::instance()->arrayToXml(
            Support::instance()->signature($wxpay->payload, $wxpay->config['key'])
        );
        $result         = $wxpay->unifiedorder($payload);

        if ($result['return_code'] == 'SUCCESS' && $result['return_msg'] == 'OK') {
            $data['appId']        = $wxpay->config['mini_appid'];
            $data['signType']     = $wxpay->config['sign_type'] ?? 'MD5';
            $data['nonceStr']     = Support::instance()->generateNonceStr();
            $data['timeStamp']    = time();
            $data['package']      = 'prepay_id=' . $result['prepay_id'];
            $signature            = Support::instance()->signature($data, $wxpay->config['key']);
            $signature['paySign'] = $signature['sign'];
            unset($sign);

            return json_encode($signature, JSON_UNESCAPED_UNICODE);
        } else {
            throw new WxpayException("Unifiedorder return error: {$this->returnData['return_msg']}");
        }
    }

    /**
     * Get Trade Type
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'JSAPI';
    }
}
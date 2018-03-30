<?php
namespace johnxu\pay\wxpay;

/**
 * Class Scan
 * @package johnxu\pay\wxpay
 */
class Scan
{
    /**
     * @var
     */
    private $returnData;

    /**
     * Scan pay
     *
     * @access public
     *
     * @param Wxpay $wxpay
     *
     * @return $this
     * @throws \Exception
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
        $this->returnData = $wxpay->unifiedorder($payload);

        return $this;
    }

    /**
     * Scan mode two
     *
     * @access public
     *
     * @param bool $uri
     *
     * @return mixed
     */
    public function getScanTwo(bool $uri = false)
    {
        if ($this->returnData['return_code'] == 'SUCCESS' && $this->returnData['return_msg'] == 'OK') {
            ;
            return $uri ? $this->returnData['code_url'] : \PHPQRCode\QRcode::png($this->returnData['code_url']);
        } else {
            throw new WxpayException("Unifiedorder return error: {$this->returnData['return_msg']}");
        }
    }

    /**
     * Get qrcode string
     *
     * @access protected
     *
     * @param  string $product_id
     * @return string
     */
    protected function generateQrcodeString(string $product_id)
    {
        $param = [
            'appid'      => $this->config['appid'],
            'mch_id'     => $this->config['mch_id'],
            'time_stamp' => time(),
            'nonce_str'  => Support::instance()->generateNonceStr(),
            'product_id' => $product_id,
        ];

        return 'weixin://' . Support::instance()->getStringParam(
            Support::instance()->signature($param)
        );
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
        return 'NATIVE';
    }
}

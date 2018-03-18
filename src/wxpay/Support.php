<?php
namespace johnxu\pay\wxpay;

class Support
{
    private static $instance;

    private function __construct()
    {
    }

    /**
     * instance class
     *
     * @access public
     *
     * @return johnxu\pay\wxpay\Support
     */
    public static function instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * signature
     *
     * @access public
     *
     * @param array $data
     * @param string $key
     *
     * @return mixed
     */
    public function signature(array $data, string $key)
    {
        $stringSignTemp = self::instance()->getStringParam($data) . '&key=' . $key;
        if (strtoupper($data['sign_type']) == 'HMAC-SHA2') {
            $signature = hash_hmac('sha256', $stringSignTemp, $key);
        } else if (strtoupper($data['sign_type']) == 'MD5') {
            $signature = md5($stringSignTemp);
        }

        $data['sign'] = strtoupper($signature);
        print_r($data);
    }

    /**
     * Get string param as key1=val1&key2=val2....
     *
     * @access public
     *
     * @param  array  $data
     *
     * @return string
     */
    public function getStringParam(array $data): string
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        ksort($data);
        $str = '';
        foreach ($data as $key => $item) {
            if (empty($item)) {
                unset($data[$key]);
            } else {
                $str .= "{$key}={$item}&";
            }
        }

        return trim($str, '&');
    }

    /**
     * Get nonce string
     *
     * @access public
     *
     * @param  integer $length nonce string length
     *
     * @return string
     */
    public function generateNonceStr(int $length = 16): string
    {
        $str    = 'ABCDEFGHIGKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $return = '';
        $strLen = strlen($str);
        for ($i = 0; $i < 16; $i++) {
            $return .= $str[mt_rand(0, $strLen - 1)];
        }

        return $return;
    }

    /**
     * Get real ip
     *
     * @access public
     *
     * @return string
     */
    public function getClientIp(): string
    {
        $realIp = 'unknown';
        if ($_SERVER['REMOTE_ADDR']) {
            $realIp = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv('REMOTE_ADDR')) {
            $realIp = getenv('REMOTE_ADDR');
        }

        return $realIp;
    }
}

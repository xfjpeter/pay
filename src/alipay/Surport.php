<?php
namespace johnxu\pay\alipay;

class Surport
{
    private static $instance;

    /**
     * instance
     *
     * @access public
     *
     * @return \johnxu\pay\alipay\Surport
     */
    public static function instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * generate sign
     *
     * @access public
     *
     * @param  string $data        data
     * @param  string $private_key public key
     * @param  string $type        RSA|RSA2
     *
     * @return string
     */
    public static function generateSign(string $data, string $private_key, string $type = 'RSA2'): string
    {
        $search = [
            "-----BEGIN RSA PRIVATE KEY-----",
            "-----END RSA PRIVATE KEY-----",
            "\n",
            "\r",
            "\r\n",
        ];
        $private_key = str_replace($search, '', $private_key);
        $private_key = $search[0] . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . $search[1];
        $res         = openssl_pkey_get_private($private_key);
        if ($res) {
            $type == 'RSA' ? openssl_sign($data, $signature, $res) : openssl_sign($data, $signature, $res, OPENSSL_ALGO_SHA256);
            openssl_pkey_free($res);
        } else {
            throw new \Exception('private key error !');
        }

        return base64_encode($signature);
    }

    /**
     * verify signature
     *
     * @access public
     *
     * @param  string $data
     * @param  string $signature
     * @param  string $public_key
     * @param  string $type       RSA|RSA2
     *
     * @return bool
     */
    public static function verifySign(string $data, string $signature, string $public_key, string $type = 'RSA2'): bool
    {
        $search = [
            "-----BEGIN PUBLIC KEY-----",
            "-----END PUBLIC KEY-----",
            "\n",
            "\r",
            "\r\n",
        ];
        $public_key = str_replace($search, "", $public_key);
        $public_key = $search[0] . PHP_EOL . wordwrap($public_key, 64, "\n", true) . PHP_EOL . $search[1];
        $res        = openssl_pkey_get_public($public_key);
        if ($res) {
            $result = $type == 'RSA' ? (bool) openssl_verify($data, base64_decode($signature), $public_key) : (bool) openssl_verify($data, base64_decode($signature), $public_key, OPENSSL_ALGO_SHA256);
            openssl_pkey_free($res);
        } else {
            throw new \Exception('public key error or signature error or data error !');
        }

        return $result;
    }

    /**
     * array to string by &
     *
     * @access public
     *
     * @param  array   $data   origin data
     * @param  boolean $decode to data
     *
     * @return string
     */
    public static function getStringParam(array $data, bool $decode = false): string
    {
        return $decode ? http_build_query($data) : urldecode(http_build_query($data));
    }

    /**
     * screen and sort
     *
     * @access public
     *
     * @param  array        $data
     * @param  bool|boolean $verify is verify sing
     *
     * @return array
     */
    public static function sorts(array $data, bool $verify = false): array
    {
        if ($verify && isset($data['sign_type'])) {
            unset($data['sign_type']);
        }
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        ksort($data);

        return $data;
    }

    /**
     * convert encoding
     *
     * @access public
     *
     * @param  string $data
     * @param  string $from
     * @param  string $to
     * @return string
     */
    public static function encoding(string $data, string $to = 'GBK', string $from = 'UTF-8'): string
    {
        // return mb_convert_encoding($data, $to, $from);
        return iconv($from, $to, $data);
    }
}

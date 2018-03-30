<?php

namespace johnxu\pay\wxpay;

class Support
{
    private static $instance;

    private function __construct()
    {
    }

    /**
     * Instance
     *
     * @access public
     *
     * @return Support
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
     * @param array  $data
     * @param string $key
     *
     * @return mixed
     */
    public function signature(array $data, $key = null): array
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        $stringSignTemp = self::instance()->getStringParam($data);
        $stringSignTemp .= $key ? '&key=' . $key : '';
        $signature      = '';
        if (isset($data['sign_type'])) {
            if (strtoupper($data['sign_type']) == 'HMAC-SHA256') {
                $signature = hash_hmac('sha256', $stringSignTemp, $key);
            } else if (strtoupper($data['sign_type']) == 'MD5') {
                $signature = md5($stringSignTemp);
            }
        } else {
            $signature = md5($stringSignTemp);
        }

        $data['sign'] = strtoupper($signature);

        return $data;
    }

    /**
     * Array to xml
     *
     * @access public
     *
     * @param  array $data
     *
     * @return string
     */
    public function arrayToXml(array $data): string
    {
        $xml = '<xml>';
        foreach ($data as $key => $item) {
            if (is_numeric($key)) {
                $xml .= "<{$key}>{$item}</{$key}>";
            } else {
                $xml .= "<{$key}><![CDATA[{$item}]]></{$key}>";
            }
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     * Xml to array
     *
     * @access public
     *
     * @param  string $xml
     *
     * @return array
     */
    public function xmlToArray(string $xml): array
    {
        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * Get string param as key1=val1&key2=val2....
     *
     * @access public
     *
     * @param  array $data
     *
     * @return string
     */
    public function getStringParam(array $data): string
    {
        array_filter($data);
        ksort($data);
        $str = '';
        foreach ($data as $key => $item) {
            if (!empty($item)) {
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
            $return .= $str[ mt_rand(0, $strLen - 1) ];
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

    /**
     * Request
     *
     * @access public
     *
     * @param string      $uri
     * @param mixed       $data
     * @param string      $method
     * @param string|null $secret
     * @param string|null $key
     *
     * @return mixed
     * @throws \Exception
     */
    public function requestApi(string $uri, $data, string $method = 'get', string $secret = null, string $key = null)
    {
        $method = strtoupper($method);
        $ch     = curl_init();
        if ($method == 'GET') {
            $uri .= '?' . !is_array($data) ? : urldecode(http_build_query($data));
        }
        $params[ CURLOPT_URL ]            = $uri;
        $params[ CURLOPT_RETURNTRANSFER ] = 1;
        $params[ CURLOPT_SSL_VERIFYPEER ] = false;
        $params[ CURLOPT_SSL_VERIFYHOST ] = false;
        if ($method == 'POST') {
            $params[ CURLOPT_POST ]       = 1;
            $params[ CURLOPT_POSTFIELDS ] = $data;
        }

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? urldecode(http_build_query($data)) : $data);
        }
        if ($secret && $key) {
            $params[ CURLOPT_SSLCERTTYPE ] = 'PEM';
            $params[ CURLOPT_SSLCERT ]     = $secret;
            $params[ CURLOPT_SSLKEYTYPE ]  = 'PEM';
            $params[ CURLOPT_SSLKEY ]      = $key;
        }
        curl_setopt_array($ch, $params);
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

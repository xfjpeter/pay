<?php
namespace johnxu\pay;

/**
 * Http请求类.
 * @method static post($uri, $params = array(), $header = array());
 * @method static get($uri, $params = array(), $header = array());
 * @method static put($uri, $params = array(), $header = array());
 * @method static delete($uri, $params = array(), $header = array());
 * @method static head($uri, $params = array(), $header = array());
 * @method static patch($uri, $params = array(), $header = array());
 * @method static upload($uri, $params = array());
 */
class Http
{
    private $url         = ''; // 抓取的url
    private $error       = ''; // 错误信息
    private $headers     = []; // 抓取头
    private $timeout     = 30; // 超时时间
    private $cookie_name = 'johnxu'; // cookie名称
    private $cookie_path = './cookie/';
    private $data; // 抓取的结果
    private $ch               = null; // curl句柄
    private static $_instance = null;

    /**
     * Curl constructor.
     *
     * @param array $config
     */
    private function __construct(array $config = [])
    {
        $this->set($config);
    }

    /**
     *
     */
    private function __clone()
    {}

    /**
     * 设置初始值
     *
     * @param array $config
     */
    private function set(array $config = [])
    {
        foreach ($config as $key => $item) {
            if (isset($this->$key)) {
                $this->$key = $item;
            }
        }
    }

    /**
     * 获取curl设置项
     *
     * @param array $option
     *
     * @return array
     */
    private function returnOptions(array $option = [])
    {
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_URL            => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($option && is_array($option)) {
            foreach ($option as $key => $item) {
                $options[$key] = $item;
            }
        }

        return $options;
    }

    /**
     * 解析Netscape Http Cookie
     *
     * @param $string
     *
     * @return array
     */
    private function extractCookies($string)
    {
        $cookies = array();
        $lines   = explode("\n", $string);
        foreach ($lines as $line) {
            // we only care for valid cookie def lines
            if (isset($line[0]) && 6 == substr_count($line, "\t")) {
                $tokens           = explode("\t", $line);
                $tokens           = array_map('trim', $tokens);
                $cookie           = array();
                $cookie['domain'] = $tokens[0];
                $cookie['flag']   = $tokens[1];
                $cookie['path']   = $tokens[2];
                $cookie['secure'] = $tokens[3];
                // $cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);
                $cookie['name']  = $tokens[5];
                $cookie['value'] = $tokens[6];
                $cookies[]       = $cookie;
            }
        }

        return $cookies;
    }

    /**
     * 设置cookie
     */
    private function setCookie()
    {
        if ($this->cookie_name) {
            // 判断cookie路径是否存在
            if (!is_dir($this->cookie_path)) {
                $this->mkdirs($this->cookie_path);
            }
            $cookie_file = $this->cookie_path . $this->cookie_name;
            if (is_file($cookie_file)) {
                $cookies = $this->extractCookies(file_get_contents($cookie_file));
                $cookie  = '';
                foreach ($cookies as $item) {
                    $cookie .= "{$item['name']}={$item['value']}; ";
                }
                $this->headers = array_merge($this->headers, ["Cookie: {$cookie}"]);
            }
        }
    }

    /**
     * 递归创建目录
     *
     * @param $path
     *
     * @return bool
     */
    private function mkdirs($path)
    {
        if (!is_dir($path)) {
            if (!static::mkdirs(dirname($path))) {
                return false;
            }
            if (!mkdir($path, 0777)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 返回当前对象
     *
     * @param array $config
     *
     * @return Curl|null
     */
    public static function instance(array $config = [])
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($config);
        }

        return self::$_instance;
    }

    /**
     * 各种请求
     *
     * @param string $url
     * @param array  $data
     * @param array  $headers
     * @param string $cookie
     * @param bool   $json
     * @param string $method
     * @param array  $option
     *
     * @return null
     */
    public function param($url = '', array $data = [], array $headers = [], $cookie = 'johnxu', $json = false, $method = 'GET', array $option = [])
    {
        $method = strtoupper($method);
        if ($url) {
            $this->url = $url;
        }
        $options = [];
        if ($method == 'GET') {
            $data = http_build_query($data);
            if (strpos($url, '?')) {
                $this->url .= '&' . $data;
            } else {
                $this->url .= '?' . $data;
            }
        } else {
            if ('UPLOAD' == $method) {
                $method = 'POST';
                if (isset($data['_file'])) {
                    $_file = each($data['_file']);
                    if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
                        $data[$_file['key']] = new \CURLFile(realpath($_file['value']));
                    } else {
                        $data[$_file['key']] = '@' . $_file['value'];
                    }
                    unset($data['_file']);
                }
            }
            $options[CURLOPT_POST]          = true;
            $options[CURLOPT_POSTFIELDS]    = $json ? json_encode($data, JSON_UNESCAPED_UNICODE) : http_build_query($data);
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }
        if ($option && is_array($option)) {
            foreach ($option as $key => $item) {
                $options[$key] = $item;
            }
        }
        if ($headers) {
            //$this->headers = $headers;
            $this->headers = array_merge($this->headers, $headers);
        }
        if ($cookie) {
            $this->cookie_name           = $cookie;
            $options[CURLOPT_COOKIEJAR]  = $this->cookie_path . $this->cookie_name;
            $options[CURLOPT_COOKIEFILE] = $this->cookie_path . $this->cookie_name;
        }
        $this->ch = curl_init();
        $this->setCookie();
        $options = $this->returnOptions($options);
        curl_setopt_array($this->ch, $options);
        $this->data = curl_exec($this->ch);
        if (curl_errno($this->ch)) {
            $this->error = curl_error($this->ch);
        }

        return self::$_instance;
    }

    /**
     * 返回原生数据
     * @return bool
     */
    public function toHtml()
    {
        if (empty($this->error)) {
            return $this->data;
        } else {
            return false;
        }
    }

    /**
     * 返回数组格式数据
     *
     * @param bool $is_array
     *
     * @return bool|mixed
     */
    public function toArray($is_array = true)
    {
        if (empty($this->error)) {
            // return json_decode($this->data, $is_array);
            $encode = mb_detect_encoding($this->data, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
            if ($encode == 'UTF-8') {
                return json_decode($this->data, $is_array);
            } else {
                return json_decode(iconv($encode, 'UTF-8//IGNORE', $this->data), $is_array);
            }
        } else {
            return false;
        }
    }

    /**
     * 返回对象格式数据
     * @return bool|mixed
     */
    public function toObject()
    {
        return $this->toArray(false);
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 动态调用
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $arguments[0] = isset($arguments[0]) ? $arguments[0] : '';
        $arguments[1] = isset($arguments[1]) ? $arguments[1] : [];
        $arguments[2] = isset($arguments[2]) ? $arguments[2] : [];
        $arguments[3] = isset($arguments[3]) ? $arguments[3] : '';
        $arguments[4] = isset($arguments[4]) ? $arguments[4] : false;
        $arguments[5] = isset($arguments[5]) ? $arguments[5] : $name;
        $arguments[6] = isset($arguments[6]) ? $arguments[6] : [];

        return call_user_func_array([$this, 'param'], $arguments);
    }
}

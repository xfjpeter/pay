# 支付集成

## 支付宝

### PC支付方式

```php
// 配置文件
$config = [
    'app_id'          => '', // 应用ID
    'api_url'         => 'https://openapi.alipaydev.com/gateway.do', // 这是沙箱的，如果是线上的填写 https://openapi.alipay.com/gateway.do
    'app_public_key'  => '', // RSA2公钥

    'app_private_key' => '', // RSA2私钥

    'ali_public_key'  => '', // 支付宝公钥

    'notify_url'      => '', // 异步通知地址，如： http://www.johnxu.net/return.php
    'return_url'      => '', // 同步通知地址，如： http://www.johnxu.net/return.php
];
```

1. 付款
```php
use johnxu\pay\Pay;

$business_param = [
    'out_trade_no' => date('YmdH:i:s'),
    'total_amount' => '0.01',
    'subject'      => '测试扫码支付',
    'body'         => '测试扫码支付的描述',
];
Pay::alipay($config)->pay('web', $business_param);
// 即可跳转至支付宝扫码付款页面
```

2. 查询
```php
use johnxu\pay\Pay;
$data = [
    'out_trade_no' => '20180303143713',
];
$query = Pay::alipay($config)->query($data);
var_dump($query);
```
3. 同步或异步验证签名是否正确
```php
use johnxu\pay\Pay;
$config = require 'config.php';
$res = Pay::alipay($config)->verify(); // 验证
echo $res->out_trade_no; // 获取返回的值
```
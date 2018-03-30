## 支付宝集成支付

1. 首先保证安装好了`composer`，如果没有安装的可以前往[安装快速通道](https://getcomposer.org/download/)
2. 打开终端，输入：`composer require johnxu/pay`即可使用

### 配置文件
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

### 调用支付方式
```php
// 根据文档设置好业务参数
$business_param = [
    'out_trade_no' => date('YmdH:i:s'),
    'total_amount' => '0.01',
    'subject'      => '测试扫码支付',
    'body'         => '测试扫码支付的描述',
];

// 如果使用laravel 或 tp5直接return即可

// PC支付
Pay::alipay($config)->pay('web', $business_param);
// 扫码支付(返回二维码链接，用phpqrcode)生成二维码即可
$res = Pay::alipay($config)->pay('scan', $business_param);
var_dump($res);
// 当面付
Pay::alipay($config)->pay('face', $business_param);
// app支付
return Pay::alipay($config)->pay('app', $business_param);
```

### 即时转账

**参考网址：** [单笔转账](https://docs.open.alipay.com/api_28/alipay.fund.trans.toaccount.transfer)

```php
$business_param = [
    'out_biz_no' => date('YmdHis'), // 商户转账唯一订单号
    'payee_type' => 'ALIPAY_LOGONID', 
    //收款方账户类型
    // 1、ALIPAY_USERID：支付宝账号对应的支付宝唯一用户号。以2088开头的16位纯数字组成。 
    // 2、ALIPAY_LOGONID：支付宝登录号，支持邮箱和手机号格式。
    'payee_account' => 'sdfs@163.com', // 收款方账户
    'amount'     => '0.01', // 转账金额
];
$res = Pay::alipay($config)->pay('transfer', $business_param);

var_dump($res); // 打印结果
```

### 查询及时转账
**参考网址：** [查询转账订单接口](https://docs.open.alipay.com/api_28/alipay.fund.trans.order.query/)
```php
// 二者传一个即可
$business_param = [
    'out_biz_no' => '234214324', // 商户转账唯一订单号
    // 'order_id' => 'asdfs', // 支付宝转账单据号
];

$res = (new Transfer())->refund(Pay::alipay($config), $business_param);
var_dump($res); // 打印查询结果
```

### 统一收单交易退款接口
**参考网址** [统一收单交易退款接口](https://docs.open.alipay.com/api_1/alipay.trade.refund)
```php
$business_param = [
    'out_trade_no'  => '', // 订单支付时传入的商户订单号
    // 'trade_no'   => '', // 支付宝交易号
    'refund_amount' => '0.01', // 需要退款的金额
];
$res = Pay::alipay($config)->refund($business_param);

var_dump($res);
```

### 统一收单交易退款查询
**参考网址** [统一收单交易退款查询](https://docs.open.alipay.com/api_1/alipay.trade.fastpay.refund.query)
```php
$business_param = [
    'out_trade_no'  => '', // 订单支付时传入的商户订单号
    // 'trade_no'   => '', // 支付宝交易号
    'refund_amount' => '0.01', // 需要退款的金额
];
$res = Pay::alipay($config)->refundQuery($business_param);

var_dump($res);
```

### 统一收单线下交易查询
**参考网址** [统一收单线下交易查询](https://docs.open.alipay.com/api_1/alipay.trade.query)
```php
$business_param = [
    'out_trade_no'  => '', // 订单支付时传入的商户订单号
    // 'trade_no'   => '', // 支付宝交易号
];
$res = Pay::alipay($config)->query($business_param);

var_dump($res);
```

### 统一收单交易关闭接口
**参考网址** [统一收单交易关闭接口](https://docs.open.alipay.com/api_1/alipay.trade.close)
```php
$business_param = [
    'out_trade_no'  => '', // 订单支付时传入的商户订单号
    // 'trade_no'   => '', // 支付宝交易号
];
$res = Pay::alipay($config)->close($business_param);

var_dump($res);
```


## 微信支付
> 配置文件如下
```php
$config = [
    'appid'          => 'wx426b3015555a46be', // 微信appid
    'mini_appid'     => 'dafdfsd', // 小程序id（用于小程序支付）
    'mch_id'         => '1900009851',
    'notify_url'     => 'http://pay.johnxu.net/notiry_url.php',
    'key'            => '8934e7d15453e97507ef794cf7b0519d',
    'api_uri'        => 'https://api.mch.weixin.qq.com',
    'apiclient_cert' => 'apiclient_cert.pem', // 证书路径(尽量写绝对路径)
    'apiclient_key'  => 'apiclient_key.pem', // 证书路径(尽量写绝对路径)
];
```

### 扫码支付
```php
$business = [
    'out_trade_no' => date("YmdHis"),
    'total_fee'    => 1,
    'body'         => '支付测试',
];
print_r(Pay::wxpay($config)->pay('app', $business))
```

### APP支付
```php
print_r(Pay::wxpay($config)->pay('app', $business));
// {"appid":"wx426b3015555a46be","partnerid":"1900009851","prepayid":"wx2018032613390793349092c30094032721","noncestr":"vBURayiSZTlEaUvq","timestamp":1522042747,"package":"Sign=WXPay","sign":"0EDD2A627366FC5724F5EAD54F32A442"}
```

### 订单查询
```php
print_r(Pay::wxpay($config)->query([
    'out_trade_no' => '20180326113409',
]));
```

### 撤销订单
```php
// 需要证书
print_r(Pay::wxpay($config)->reverse([
    'out_trade_no' => '20180326113409',
]));
```

### 申请退款
```php
// 需要证书
print_r(Pay::wxpay($config)->refund([
    'out_trade_no'  => '20180326113409', // 商户订单号
    'out_refund_no' => date('YmdHis'), // 退款订单号
    'total_fee'     => 1, // 总金额
    'refund_fee'    => 1, // 退款金额
]));
```

### 退款查询
```php
// 下面四个参数随便选个一即可
print_r(Pay::wxpay($config)->queryRefund([
    'out_trade_no'      => '20180326113409', // 商户订单号
    // 'transaction_id' => '', // 微信订单号
    // 'out_refund_no'  => '', // 商户退款单号
    // 'refund_id'      => '', // 微信退款单号
]));
```

### 关闭订单
```php
// 关闭订单只能用商户订单号
print_r(Pay::wxpay($config)->close([
    'out_trade_no' => '20180326113409',
]));
```

### 异步通知
```php
$res = Pay::wxpay($config)->verify();

if ($res) {
    print_r($res);
} else {
    echo '异步验证失败';
}

echo 'SUCCESS';
```

### 微信小程序支付
```php
$res = Pay::wxpay($config)->pay('mini', $business);

// 这里返回的是一个json格式的字符串
```
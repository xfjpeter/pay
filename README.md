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
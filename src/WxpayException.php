<?php
namespace johnxu\pay;

class WxpayException extends \Exception
{
    public function __toString()
    {
        return 'Wechat Return Error:' . $this->getMessage();
    }

    public function customFunction()
    {
        return 'Wechat Return Error[' . $this->getCode() . ']:' . $this->getMessage();
    }
}

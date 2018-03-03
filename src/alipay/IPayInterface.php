<?php
namespace johnxu\pay\alipay;

/**
 * pay interface
 */
interface IPayInterface
{

    /**
     * pay enter
     *
     * @access public
     *
     * @param  string $method
     * @param  array  $data business data
     */
    public function pay(string $method, array $data);

    /**
     * query order
     *
     * @access public
     *
     * @param array $data business data
     */
    public function query(array $data);

    /**
     * cancel order
     *
     * @access public
     *
     * @param array $data business data
     */
    public function cancel(array $data);

    /**
     * refund order
     *
     * @access public
     *
     * @param array $data business data
     */
    public function refund(array $data);

    /**
     * close order
     *
     * @access public
     *
     * @param  array  $data business data
     */
    public function close(array $data);

    /**
     * verify sign
     *
     * @access public
     */
    public function verify();
}

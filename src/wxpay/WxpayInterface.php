<?php
namespace johnxu\pay\wxpay;

/**
 * pay interface
 */
interface InterfaceName
{
    /**
     * Unified order
     *
     * @access public
     *
     * @param  string $method
     * @param  array  $data
     */
    public function pay(string $method, array $data);

    /**
     * Query order
     *
     * @access public
     *
     * @param  array  $data
     */
    public function query(array $data);

    /**
     * Close order
     *
     * @access public
     *
     * @param  array  $data
     */
    public function close(array $data);

    /**
     * Application for refund
     *
     * @access public
     *
     * @param  array  $data
     */
    public function refund(array $data);

    /**
     * Query refunds
     *
     * @access public
     *
     * @param  array  $data
     */
    public function queryRefund(array $data);

    /**
     * Download the bill
     *
     * @access public
     *
     * @param  array  $data
     */
    public function download(array $data);
}

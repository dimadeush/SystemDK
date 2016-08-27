<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop_ipayment.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
interface shop_ipayment
{
    /**
     * Calculate final invoice/bill amount which will be sent to Payment system API
     *
     * @param float $orderAmount
     * @param float $deliveryAmount
     * @param float $paymentCommission
     * @param bool  $convertCurrency
     *
     * @throws system_exception
     * @return float
     */
    public function calculateInvoice($orderAmount, $deliveryAmount, $paymentCommission, $convertCurrency = true);

    /**
     * @throws system_exception
     * @return bool
     */
    public function createInvoice();

    /**
     * Send request to payment system and check if order is paid and then check if amount on payment system side === invoice amount in our db
     *
     * @param float $invoiceAmount
     *
     * @throws system_exception
     * @return bool
     */
    public function isPaid($invoiceAmount);

    /**
     * Return name of the template
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Get data for payment form
     *
     * @throws system_exception
     * @return array
     */
    public function getFormData();

    /**
     * @throws system_exception
     */
    public function ExternalPaymentError();

    /**
     * Return minutes within customer can pay for order
     *
     * @return int
     */
    public function getInvoiceLifeTime();

    /**
     * @throws system_exception
     * @return string
     */
    public function getInvoiceCurrencyCode();

    /**
     * @throws system_exception
     * @return float
     */
    public function getInvoiceExchangeRate();
}
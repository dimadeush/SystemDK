<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop_payment.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class shop_payment extends model_base implements shop_ipayment
{
    const PAYMENT_UNKNOWN_ERROR_TEXT = 'Unknown error happened';
    const PAYMENT_MINIMUM_ERROR_TEXT = 'Error happened. Please try again later';
    /**
     * Time in minutes within order invoice should be paid
     */
    const INVOICE_LIFE_TIME = 60;

    /**
     * @var string|bool
     */
    protected $error;
    /**
     * @var array|bool
     */
    protected $error_array;
    /**
     * @var array|bool
     */
    protected $result;
    /**
     * @var shop_payment_log
     */
    private $log;

    /**
     * Can be 'invoicing'|'calculate'|'check_payment'
     *
     * @var string
     */
    private $processType;
    /**
     * Payment system id
     *
     * @var int
     */
    private $systemId;
    /**
     * Order ID
     *
     * @var int
     */
    private $orderId;
    /**
     * Order hash
     *
     * @var string
     */
    private $orderHash;
    /**
     * This amount will be sent to Payment system
     *
     * @var float
     */
    private $invoiceAmount;
    /**
     * Payment description
     *
     * @var string
     */
    private $description;
    /**
     * Invoice Amount currency code
     *
     * @var string
     */
    private $invoiceCurrencyCode;
    /**
     * Exchange rate which used when invoice was formed
     *
     * @var float
     */
    private $invoiceExchangeRate;
    /**
     * Currency of your items in the shop
     *
     * @var string
     */
    private $shopCurrencyCode;
    /**
     * Logging mode, can be 'all'|'errors'|'off'
     *
     * @var string
     */
    private $logMode;
    /**
     * If value is '1' then email notification will be sent in case error
     *
     * @var int
     */
    private $errorNotify;
    /**
     * Error notification type determine to whom error mail will be sent
     *
     * @var string
     */
    private $errorNotifyType;
    /**
     * Can be 'minimum'|'normal'|'debug'
     *
     * @var string
     */
    private $displayErrorMode;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->log = singleton::getinstance('shop_payment_log', $registry);
    }


    /**
     * Get data of some private properties
     *
     * @param string $property
     *
     * @return bool|array|string
     */
    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


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
    public function calculateInvoice($orderAmount, $deliveryAmount, $paymentCommission, $convertCurrency = true)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
    }


    /**
     * @throws system_exception
     * @return bool
     */
    public function createInvoice()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $invoiceDate = $this->registry->main_class->get_time();
        $invoiceAmount = $this->registry->main_class->float_to_db($this->getInvoiceAmount());
        $invoiceCurrencyCode = $this->registry->main_class->processing_data($this->getInvoiceCurrencyCode());
        $invoiceExchangeRate = $this->getInvoiceExchangeRate();

        if (!empty($invoiceExchangeRate)) {
            $invoiceExchangeRate = "'" . $this->registry->main_class->float_to_db($invoiceExchangeRate) . "'";
        } else {
            $invoiceExchangeRate = 'NULL';
        }

        $orderId = $this->getOrderId();
        $sql = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET invoice_date = '" . $invoiceDate . "', invoice_amount = '" . $invoiceAmount . "', "
               . "invoice_currency_code = " . $invoiceCurrencyCode . ", invoice_exch_rate = " . $invoiceExchangeRate
               . ", invoice_paid = '0', invoice_paid_date = NULL WHERE order_id = '" . $orderId . "'";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->generateException('Error while creating invoice', $this->db->ErrorMsg(), $this->db->ErrorNo(), __CLASS__, __LINE__);
        }

        $numResult = $this->db->Affected_Rows();

        if ($numResult < 1) {
            $this->generateException('Invoice not created', '', 0, __CLASS__, __LINE__);
        }

        return true;
    }


    /**
     * Send request to payment system and check if order is paid and then check if amount on payment system side === invoice amount in our db
     *
     * @param float $invoiceAmount
     *
     * @throws system_exception
     * @return bool
     */
    public function isPaid($invoiceAmount)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
    }


    /**
     * Return name of the template
     *
     * @return string
     */
    public function getTemplate()
    {
        return '';
    }


    /**
     * Get data for payment form
     *
     * @throws system_exception
     * @return array
     */
    public function getFormData()
    {
        return [];
    }


    /**
     * @throws system_exception
     */
    public function ExternalPaymentError()
    {
        $this->generateException('Error while processing payment on Payment system side', '', 0, __CLASS__, __LINE__);
    }


    /**
     * Return minutes within customer can pay for order
     *
     * @return int
     */
    public function getInvoiceLifeTime()
    {
        return self::INVOICE_LIFE_TIME;
    }


    /**
     * @param string $value
     */
    protected function setProcessType($value)
    {
        $this->processType = $value;
    }


    /**
     * @return string
     */
    protected function getProcessType()
    {
        return $this->processType;
    }


    /**
     * @param int $value
     */
    protected function setSystemId($value)
    {
        $this->systemId = intval($value);
    }


    /**
     * @return int
     */
    protected function getSystemId()
    {
        return $this->systemId;
    }


    /**
     * @param int $value
     */
    protected function setOrderId($value)
    {
        $this->orderId = intval($value);
    }


    /**
     * @throws system_exception
     * @return int
     */
    protected function getOrderId()
    {
        if ($this->orderId < 1) {
            $this->generateException('Incorrect order id', '', 0, __CLASS__, __LINE__);
        }

        return $this->orderId;
    }


    /**
     * @param string $value
     */
    protected function setOrderHash($value)
    {
        $this->orderHash = $value;
    }


    /**
     * @throws system_exception
     * @return string
     */
    protected function getOrderHash()
    {
        if (empty($this->orderHash)) {
            $this->generateException('Incorrect order hash', '', 0, __CLASS__, __LINE__);
        }

        return $this->orderHash;
    }


    /**
     * @param float $value
     */
    protected function setInvoiceAmount($value)
    {
        $this->invoiceAmount = $this->registry->main_class->round($value);
    }


    /**
     * @throws system_exception
     * @return float
     */
    protected function getInvoiceAmount()
    {
        if (empty($this->invoiceAmount)) {
            $this->generateException('Incorrect invoice amount', '', 0, __CLASS__, __LINE__);
        }

        return $this->registry->main_class->round($this->invoiceAmount);
    }


    /**
     * @param string $value
     */
    protected function setDescription($value)
    {
        $this->description = $value;
    }


    /**
     * @throws system_exception
     * @return string
     */
    protected function getDescription()
    {
        if (empty($this->description)) {
            $this->generateException('Incorrect payment description', '', 0, __CLASS__, __LINE__);
        }

        $this->description = str_ireplace(
            ['$site_url', '$order_number', '$site_name'],
            [$this->registry->main_class->get_site_url(), $this->getOrderId(), $this->registry->main_class->get_site_name()], $this->description
        );

        return $this->description;
    }


    /**
     * @param string $value
     */
    protected function setInvoiceCurrencyCode($value)
    {
        $this->invoiceCurrencyCode = $value;
    }


    /**
     * @throws system_exception
     * @return string
     */
    public function getInvoiceCurrencyCode()
    {
        if (empty($this->invoiceCurrencyCode)) {
            $this->generateException('Incorrect invoice currency code', '', 0, __CLASS__, __LINE__);
        }

        return $this->invoiceCurrencyCode;
    }


    /**
     * @param float $value
     */
    protected function setInvoiceExchangeRate($value)
    {
        $this->invoiceExchangeRate = $value;
    }


    /**
     * @throws system_exception
     * @return float
     */
    public function getInvoiceExchangeRate()
    {
        if (empty($this->invoiceExchangeRate) && $this->getInvoiceCurrencyCode() != $this->getShopCurrencyCode()) {
            $this->generateException('Incorrect invoice exchange rate', '', 0, __CLASS__, __LINE__);
        }

        return $this->invoiceExchangeRate;
    }


    /**
     * @param string $value
     */
    protected function setShopCurrencyCode($value)
    {
        $this->shopCurrencyCode = $value;
    }


    /**
     * @throws system_exception
     * @return string
     */
    protected function getShopCurrencyCode()
    {
        if (empty($this->shopCurrencyCode)) {
            $this->generateException('Incorrect shop currency code', '', 0, __CLASS__, __LINE__);
        }

        return $this->shopCurrencyCode;
    }


    /**
     * @param string $value
     */
    protected function setLogMode($value)
    {
        $this->logMode = $value;
    }


    /**
     * @return string
     */
    protected function getLogMode()
    {
        return $this->logMode;
    }


    /**
     * @param int $value
     */
    protected function setErrorNotify($value)
    {
        $this->errorNotify = intval($value);
    }


    /**
     * @return int
     */
    protected function getErrorNotify()
    {
        return $this->errorNotify;
    }


    /**
     * @param string $value
     */
    protected function setErrorNotifyType($value)
    {
        $this->errorNotifyType = $value;
    }


    /**
     * @return string
     */
    protected function getErrorNotifyType()
    {
        return $this->errorNotifyType;
    }


    /**
     * @param string $value
     */
    protected function setDisplayErrorMode($value)
    {
        $this->displayErrorMode = $value;
    }


    /**
     * @return string
     */
    protected function getDisplayErrorMode()
    {
        return $this->displayErrorMode;
    }


    /**
     * @throws system_exception
     * @return bool
     */
    protected function setStatusPaid()
    {
        $invoicePaidDate = $this->registry->main_class->get_time();
        $orderId = $this->getOrderId();
        $sql = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET invoice_paid_date = '" . $invoicePaidDate . "', invoice_paid = '1', "
               . "order_status = '6' WHERE order_id = '" . $orderId . "'";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->generateException('Error while set status paid for the order', $this->db->ErrorMsg(), $this->db->ErrorNo(), __CLASS__, __LINE__);
        }

        $numResult = $this->db->Affected_Rows();

        if ($numResult < 1) {
            $this->generateException('Status paid was not set for the order', '', 0, __CLASS__, __LINE__);
        }

        return true;
    }


    /**
     * @param string $toCurrencyCode
     *
     * @throws system_exception
     * @return float
     */
    protected function getExchangeRate($toCurrencyCode)
    {
        $now = $this->registry->main_class->get_time();
        $sql = "SELECT id,rate FROM " . PREFIX . "_s_currency_exch WHERE from_currency_code = '" . $this->getShopCurrencyCode() . "' "
               . "and to_currency_code = '" . $toCurrencyCode . "' and date_from <= " . $now . " and (date_till >= " . $now . " or date_till IS NULL)";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->generateException('Sql error while getting currency exchange rate', '', 0, __CLASS__, __LINE__);
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {
            $this->generateException('Currency exchange rate not found', '', 0, __CLASS__, __LINE__);
        }

        $rate = $this->registry->main_class->round($result->fields['1']);

        return $rate;
    }


    /**
     * @return string
     */
    protected function getSuccessUrl()
    {
        $successUrl =
            $this->registry->main_class->get_site_url() . 'index.php?path=shop&func=pay_done&order_id=' . $this->getOrderId() . '&order_hash=' . $this->getOrderHash()
            . '&lang=' . $this->registry->sitelang;

        if ($this->registry->main_class->get_mode_rewrite()) {
            $successUrl =
                $this->registry->main_class->get_site_url() . $this->registry->sitelang . '/shop/pay_done/' . $this->getOrderId() . '/' . $this->getOrderHash();
        }

        return $successUrl;
    }


    /**
     * @return string
     */
    protected function getFailureUrl()
    {
        $failureUrl =
            $this->registry->main_class->get_site_url() . 'index.php?path=shop&func=pay_error&order_id=' . $this->getOrderId() . '&order_hash=' . $this->getOrderHash()
            . '&lang=' . $this->registry->sitelang;

        if ($this->registry->main_class->get_mode_rewrite()) {
            $failureUrl =
                $this->registry->main_class->get_site_url() . $this->registry->sitelang . '/shop/pay_error/' . $this->getOrderId() . '/' . $this->getOrderHash();
        }

        return $failureUrl;
    }


    /**
     * Save data to log
     *
     * @param array $additionalParams
     *
     * @return bool
     */
    protected function saveToLog($additionalParams = [])
    {
        if ($this->getLogMode() != 'all') {
            return true;
        }

        $this->log->clear();
        $data = [];

        if (!empty($additionalParams)) {
            $data = array_merge($data, $additionalParams);
        }

        $data['system_id'] = $this->getSystemId();
        $data['order_number'] = $this->getOrderId();
        $result = $this->log->save($data);

        return $result;
    }


    /**
     * Process errors and then throw exception
     *
     * @param string $errorText
     * @param string $originalError
     * @param int    $errorCode
     * @param string $class
     * @param int    $line
     * @param array  $additionalParams
     *
     * @throws system_exception
     */
    protected function generateException($errorText = '', $originalError = '', $errorCode = 0, $class = '', $line = 0, $additionalParams = [])
    {
        if (in_array($this->getLogMode(), ['all', 'errors', null]) || !empty($this->getErrorNotify())) {
            $logAddText = ' in class: ' . $class . ', line: ' . $line;
            $logError = !empty($originalError) ? $originalError : $errorText;
            $data = [];

            if (!empty($additionalParams)) {
                $data = array_merge($data, $additionalParams);
            }

            if (!empty($this->getSystemId())) {
                $data['system_id'] = $this->getSystemId();
            }

            if (!empty($this->orderId) && $this->orderId > 0) {
                $data['order_number'] = $this->getOrderId();
            }

            $data['process_status'] = 'exception';
            $data['error_data'] = !empty($errorCode) ? $errorCode . ' ' . $logError . $logAddText : $logError . $logAddText;

            if (in_array($this->getLogMode(), ['all', 'errors', null])) {
                $this->log->save($data);
            }

            if (!empty($this->getErrorNotify())) {
                $this->send($data);
            }
        }

        $this->log->clear();

        if ($this->getDisplayErrorMode() == 'minimum') {
            $errorText = self::PAYMENT_MINIMUM_ERROR_TEXT;
            $errorCode = 0;
        } elseif ($this->getDisplayErrorMode() == 'debug') {

            if (!empty($class)) {
                $errorText = $errorText . ' in class: ' . $class;
            }

            if (!empty($line)) {
                $errorText = $errorText . ', line: ' . $line;
            }

            if (!empty($originalError)) {
                $errorText = $errorText . '. Details: ' . $originalError;
            }
        }

        if (empty($errorText)) {
            $errorText = self::PAYMENT_UNKNOWN_ERROR_TEXT;
        }

        throw new system_exception($errorText, $errorCode);
    }


    /**
     * Send mail to "Admin" or "order notify" mailbox in case error
     *
     * @param array $array
     *
     * @return bool
     */
    private function send($array)
    {
        $dataArray = !empty($this->registry->paymentSystemData) ? $this->registry->paymentSystemData : [];
        $to = ADMIN_EMAIL;

        if ($this->getErrorNotifyType() == 'order_notify' && !empty($dataArray['shop_order_notify_custom_mailbox'])) {
            $to = $dataArray['shop_order_notify_custom_mailbox'];
        }

        if (empty($dataArray['shop_payment_error_notify_mail_subject'])
            || empty($dataArray['shop_payment_error_notify_mail_content'])
            || empty($to)
        ) {
            return false;
        }

        $array = var_export($array, true);
        $dataArray['shop_payment_error_notify_mail_content'] =
            str_replace("module_shop_payment_error_data", $array, $dataArray['shop_payment_error_notify_mail_content']);
        $dataArray['shop_payment_error_notify_mail_content'] =
            str_replace("modules_shop_site_url", $this->registry->main_class->get_site_url(), $dataArray['shop_payment_error_notify_mail_content']);
        $this->registry->mail->from = ADMIN_EMAIL;
        $this->registry->mail->sendmail($to, $dataArray['shop_payment_error_notify_mail_subject'], $dataArray['shop_payment_error_notify_mail_content']);

        if ($this->registry->mail->send_error) {
            return false;
        }

        return true;
    }
}
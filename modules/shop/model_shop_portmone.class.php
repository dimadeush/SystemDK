<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop_portmone.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class shop_portmone extends shop_payment
{
    /**
     * Portmone working only with uah currency!
     */
    const PORTMONE_CURRENCY_CODE = 'uah';
    /**
     * Form for generate POST request to Portmone in case acquiring agreement
     */
    const PORTMONE_ACQUIRING_TEMPLATE = 'shop_pay_portmone_acq_form.html';
    const PORTMONE_URL = 'https://www.portmone.com.ua/gateway/';
    const PORTMONE_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0'; // workaround for Portmone firewall
    const PORTMONE_PAYMENT_STATUS_METHOD = 'result';
    const PORTMONE_STATUS_PAID = 'PAYED';
    /**
     * Portmone have different integration types. SystemDK support now only type 'acquiring_agreement'
     *
     * @var string
     */
    private $integrationType;
    /**
     * @var string
     */
    private $payeeId;
    /**
     * Portmone login, using when request payment status
     *
     * @var string
     */
    private $login;
    /**
     * Portmone password, using when request payment status
     *
     * @var string
     */
    private $password;


    /**
     * shop_portmone constructor.
     *
     * @param registry $registry
     *
     * @throws system_exception
     * @return shop_portmone
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->init();
    }


    /**
     * Payment system initialization
     *
     * @throws system_exception
     */
    private function init()
    {
        $this->checkParams();
        $this->setParams();
        $this->loadConfiguration();
    }


    /**
     * Check all incoming params
     *
     * @throws system_exception
     */
    private function checkParams()
    {
        if (!isset($this->registry->paymentSystemData) || !is_array($this->registry->paymentSystemData) || empty($this->registry->paymentSystemData)) {
            $this->generateException('Empty incoming params for Portmone', '', 0, __CLASS__, __LINE__);
        }

        $data = $this->registry->paymentSystemData;

        if (empty($data['shop_process_type']) || !in_array($data['shop_process_type'], ['invoicing', 'calculate', 'check_payment'])) {
            $this->generateException('Empty or incorrect incoming param shop_process_type for Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (empty($data['shop_system_id']) || intval($data['shop_system_id']) < 1) {
            $this->generateException('Empty incoming param shop_system_id for Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (empty($data['shop_system_integration_type'])) {
            $this->generateException('Empty incoming param shop_system_integration_type for Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (in_array($data['shop_process_type'], ['invoicing', 'check_payment']) && (!isset($data['shop_order_id']) || intval($data['shop_order_id']) < 1)) {
            $this->generateException('Empty or incorrect incoming param shop_order_id for Portmone', '', 0, __CLASS__, __LINE__);
        }

        if ($data['shop_process_type'] == 'invoicing' && empty($data['shop_order_hash'])) {
            $this->generateException('Empty or incorrect incoming param shop_order_hash for Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (empty($data['shop_currency']) || strlen($data['shop_currency']) != 3) {
            $this->generateException('Empty or incorrect incoming param shop_currency for Portmone', '', 0, __CLASS__, __LINE__);
        }
    }


    /**
     * Set main properties
     */
    private function setParams()
    {
        $data = $this->registry->paymentSystemData;
        $this->setProcessType($data['shop_process_type']);
        $this->setSystemId($data['shop_system_id']);
        $this->integrationType = $data['shop_system_integration_type'];

        if (in_array($this->getProcessType(), ['invoicing', 'check_payment'])) {
            $this->setOrderId($data['shop_order_id']);
        }

        if ($this->getProcessType() == 'invoicing') {
            $this->setOrderHash($data['shop_order_hash']);
        }

        $this->setShopCurrencyCode($data['shop_currency']);
    }


    /**
     * Set additional properties
     *
     * @throws system_exception
     */
    private function loadConfiguration()
    {
        if ($this->integrationType != 'acquiring_agreement') {
            return;
        }

        $sql = "SELECT payee_id,login,password,payment_description,log_mode,error_notify,error_notify_type,display_error_mode "
               . "FROM " . PREFIX . "_s_portmone_ac_conf";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->generateException('Sql error while getting Portmone configuration', '', 0, __CLASS__, __LINE__);
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {
            $this->generateException('Portmone configuration not found', '', 0, __CLASS__, __LINE__);
        }

        $this->payeeId = $this->registry->main_class->extracting_data($result->fields['0']);
        $this->login = $this->registry->main_class->extracting_data($result->fields['1']);
        $this->password = $this->registry->main_class->extracting_data($result->fields['2']);
        $this->setDescription($this->registry->main_class->extracting_data($result->fields['3']));
        $this->setInvoiceCurrencyCode(self::PORTMONE_CURRENCY_CODE);
        $this->setLogMode($this->registry->main_class->extracting_data($result->fields['4']));
        $this->setErrorNotify(intval($result->fields['5']));
        $this->setErrorNotifyType($this->registry->main_class->extracting_data($result->fields['6']));
        $this->setDisplayErrorMode($this->registry->main_class->extracting_data($result->fields['7']));
    }


    /**
     * Calculate final invoice/bill amount which will be sent to Portmone API
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
        parent::calculateInvoice($orderAmount, $deliveryAmount, $paymentCommission, $convertCurrency);

        if ($paymentCommission > 99) {
            $this->generateException('Incorrect commission', '', 0, __CLASS__, __LINE__);
        }

        $amount = $orderAmount + $deliveryAmount;
        $amount = $amount / (1 - ($paymentCommission / 100));

        if ($this->getShopCurrencyCode() != self::PORTMONE_CURRENCY_CODE && $convertCurrency) {
            $this->setInvoiceExchangeRate($this->getExchangeRate(self::PORTMONE_CURRENCY_CODE));
            $amount = $amount * $this->getInvoiceExchangeRate();
        }

        $this->setInvoiceAmount($amount);

        return $this->getInvoiceAmount();
    }


    /**
     * @throws system_exception
     * @return bool
     */
    public function createInvoice()
    {
        parent::createInvoice();
        $data = [
            'request_url'    => self::PORTMONE_URL,
            'request_data'   => $this->getFormData(),
            'process_status' => 'post_form_created',
        ];

        $this->saveToLog($data);

        return true;
    }


    /**
     * Send request to Portmone and check if order is paid and then check if amount on Portmone side === invoice amount in our db
     *
     * @param float $invoiceAmount
     *
     * @throws system_exception
     * @return bool
     */
    public function isPaid($invoiceAmount)
    {
        parent::isPaid($invoiceAmount);
        $data = [
            'method'            => self::PORTMONE_PAYMENT_STATUS_METHOD,
            'payee_id'          => $this->payeeId,
            'login'             => $this->login,
            'password'          => $this->password,
            'shop_order_number' => $this->getOrderId(),
            'status'            => self::PORTMONE_STATUS_PAID,
            'start_date'        => date("d.m.Y", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))),
            'end_date'          => date("d.m.Y", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))),
        ];
        $response = $this->request(self::PORTMONE_URL, $data);

        return $this->processIsPaidResponse($response, $invoiceAmount);
    }


    /**
     * Return name of the template
     *
     * @return string
     */
    public function getTemplate()
    {
        return self::PORTMONE_ACQUIRING_TEMPLATE;
    }


    /**
     * Get data for payment form
     *
     * @throws system_exception
     * @return array
     */
    public function getFormData()
    {
        return [
            'payee_id'          => $this->payeeId,
            'shop_order_number' => $this->getOrderId(),
            'bill_amount'       => $this->getInvoiceAmount(),
            'description'       => $this->getDescription(),
            'success_url'       => $this->getSuccessUrl(),
            'failure_url'       => $this->getFailureUrl(),
        ];
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
        $originalError = $this->convert($originalError);
        parent::generateException($errorText, $originalError, $errorCode, $class, $line, $additionalParams);
    }


    private function request($url, $data)
    {
        $logData = [
            'request_url'  => self::PORTMONE_URL,
            'method'       => $data['method'],
            'request_data' => $data,
        ];
        $ch = curl_init();
        $options = [
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_ENCODING       => "",
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => [
                "cache-control: no-cache",
                "user-agent: " . self::PORTMONE_USER_AGENT,
            ],
            CURLOPT_POSTFIELDS     => $data,
        ];

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $logData['response_data'] = $this->convert($response);

        if ($err) {
            $this->generateException('Can not call Portmone API', 'cURL Error #:' . $err, 0, __CLASS__, __LINE__, $logData);
        }

        $logData['process_status'] = 'ok';
        $this->saveToLog($logData);

        return $response;
    }


    /**
     * Processing response from Portmone
     *
     * @param string $response
     * @param float  $invoiceAmount
     *
     * @return bool
     */
    private function processIsPaidResponse($response, $invoiceAmount)
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($response);

        if (!$doc) {
            $this->generateException('Error while processing xml response from Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (!is_object($doc->orders) || empty($doc->orders->order) || !is_object($doc->orders->order)) {
            $this->generateException('Incorrect payee_id or shop_order_number while call Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (empty($doc->orders->order->status)) {
            $this->generateException('Order status not found while call Portmone', '', 0, __CLASS__, __LINE__);
        }

        if (empty($doc->orders->order->bill_amount)) {
            $this->generateException('Bill amount not found while call Portmone', '', 0, __CLASS__, __LINE__);
        }

        $portmoneSideAmount = $this->registry->main_class->round(floatval($doc->orders->order->bill_amount));
        $systemSideAmount = $this->registry->main_class->round($invoiceAmount);

        if ($portmoneSideAmount != $systemSideAmount) {
            try {
                $this->generateException(
                    'Incorrect bill amount on Portmone side. Should be: ' . $systemSideAmount . ' ' . $this->getInvoiceCurrencyCode() . ', but Portmone side: '
                    . $portmoneSideAmount . ' ' . $this->getInvoiceCurrencyCode(), '', 0, __CLASS__, __LINE__
                );
            } catch (system_exception $exception) {
                return false;
            }
        }

        if ($doc->orders->order->status == self::PORTMONE_STATUS_PAID) {
            $this->setStatusPaid();

            return true;
        }

        try {
            $this->generateException('Order not paid on Portmone side', '', 0, __CLASS__, __LINE__);
        } catch (system_exception $exception) {
            return false;
        }
    }


    /**
     * @param string $text
     *
     * @return bool|string
     */
    private function convert($text)
    {
        $text = str_ireplace("password = " . $this->password, "password = ****", $text); // hide password in text for logs
        return iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
    }


}
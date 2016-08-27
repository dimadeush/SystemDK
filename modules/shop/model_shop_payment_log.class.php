<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop_payment_log.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class shop_payment_log extends model_base
{
    /**
     * @var string|bool
     */
    private $error;
    /**
     * @var array|bool
     */
    private $error_array;
    /**
     * @var array|bool
     */
    private $result;
    /**
     * Data for saving into db
     *
     * @var array
     */
    private $dataArray;
    /**
     * Life time in days for logs which have NULL value in system_id field. 0 - unlimited time.
     *
     * @var int
     */
    private $undefinedPaymentSystemLogLifeTime = 90;


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
     * @return bool
     */
    public function clear()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $sql = "SELECT id,system_class,system_integ_type FROM " . PREFIX . "_s_payment_systems WHERE system_status = '1'";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return false;
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {
            return false;
        }

        while (!$result->EOF) {
            $id = intval($result->fields['0']);
            $systemClass = $this->registry->main_class->extracting_data($result->fields['1']);
            $systemIntegType = $this->registry->main_class->extracting_data($result->fields['2']);

            if ($systemClass == 'shop_portmone' && $systemIntegType == 'acquiring_agreement') {
                $sql = "SELECT log_life_days FROM " . PREFIX . "_s_portmone_ac_conf";
                $resultPortmone = $this->db->Execute($sql);

                if (!$resultPortmone) {
                    $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                    $this->error = 'sql_error';
                    $this->error_array = $error;

                    return false;
                }

                if ($resultPortmone && isset($resultPortmone->fields['0']) && intval($resultPortmone->fields['0']) > 0) {
                    $logLifeDays = intval($resultPortmone->fields['0']);
                    $this->clearProcess($id, $logLifeDays);
                }
            }

            $result->MoveNext();
        }

        return true;
    }


    /**
     * @param int $systemId
     * @param int $logLifeDays
     */
    private function clearProcess($systemId, $logLifeDays)
    {
        $now = $this->registry->main_class->get_time();
        $systemId = intval($systemId);
        $logLifeDays = intval($logLifeDays);

        if ($systemId < 1 || $logLifeDays < 1) {
            return;
        }

        $logLifeSeconds = $logLifeDays * 86400;
        $sqlAdd = false;

        if ($this->undefinedPaymentSystemLogLifeTime > 0) {
            $undefinedPaymentSystemLogLifeSeconds = $this->undefinedPaymentSystemLogLifeTime * 86400;
            $sqlAdd = " OR ((log_date+" . $undefinedPaymentSystemLogLifeSeconds . ") < " . $now . " and system_id IS NULL)";
        }

        $sql = "DELETE FROM " . PREFIX . "_s_payment_logs_" . $this->registry->sitelang . " WHERE ((log_date+" . $logLifeSeconds . ") < " . $now . " "
               . "and system_id = '" . $systemId . "')" . $sqlAdd;
        $result = $this->db->Execute($sql);

        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        if ($result && $this->db->Affected_Rows() > 0) {
            $this->clearCache();
        }
    }


    /**
     * Save data to log
     *
     * @param array $array
     *
     * @return bool
     */
    public function save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->setDefaultData();

        if (!$this->processData($array)) {
            return false;
        }

        $now = $this->registry->main_class->get_time();
        $this->db->StartTrans();
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_s_payment_logs_id_" . $this->registry->sitelang, 'id');
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();

        if ($check_db_need_lobs == 'yes') {
            $sql = "INSERT INTO " . PREFIX . "_s_payment_logs_" . $this->registry->sitelang . " "
                   . "(" . $sequence_array['field_name_string'] . "system_id,request_url,method,order_number,request_data,response_data,process_status,error_data,"
                   . "log_date) VALUES "
                   . "(" . $sequence_array['sequence_value_string'] . $this->dataArray['system_id'] . "," . $this->dataArray['request_url'] . ","
                   . $this->dataArray['method'] . "," . $this->dataArray['order_number'] . ",empty_clob(),empty_clob(),"
                   . $this->dataArray['process_status'] . "," . "empty_clob(),'" . $now . "')";
            $result = $this->db->Execute($sql);

            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }

            $result2 = true;

            if ($this->dataArray['request_data'] != 'NULL') {
                $result2 = $this->db->UpdateClob(
                    PREFIX . '_s_payment_logs_' . $this->registry->sitelang, 'request_data', $this->dataArray['request_data'], 'id=' . $sequence_array['sequence_value']
                );

                if ($result2 === false) {
                    $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                }
            }

            $result3 = true;

            if ($this->dataArray['response_data'] != 'NULL') {
                $result3 = $this->db->UpdateClob(
                    PREFIX . '_s_payment_logs_' . $this->registry->sitelang, 'response_data', $this->dataArray['response_data'], 'id=' . $sequence_array['sequence_value']
                );

                if ($result3 === false) {
                    $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                }
            }

            if ($result2 === false || $result3 === false) {
                $result = false;
            }
        } else {
            $sql = "INSERT INTO " . PREFIX . "_s_payment_logs_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "system_id,request_url,method,order_number,request_data,response_data,process_status,error_data,log_date) VALUES ("
                   . $sequence_array['sequence_value_string'] . $this->dataArray['system_id'] . "," . $this->dataArray['request_url'] . ","
                   . $this->dataArray['method'] . "," . $this->dataArray['order_number'] . ","
                   . $this->dataArray['request_data'] . "," . $this->dataArray['response_data'] . "," . $this->dataArray['process_status'] . ","
                   . $this->dataArray['error_data'] . ",'" . $now . "')";
            $result = $this->db->Execute($sql);

            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
        }

        $this->db->CompleteTrans();

        if ($result === false) {
            $this->error = 'Sql error happened when saving log';
            $this->error_array = $error;

            return false;
        }

        $this->clearCache();

        return true;
    }


    private function setDefaultData()
    {
        $this->dataArray = [
            'system_id'      => 'NULL',
            'request_url'    => 'NULL',
            'method'         => 'NULL',
            'order_number'   => 'NULL',
            'request_data'   => 'NULL',
            'response_data'  => 'NULL',
            'process_status' => false,
            'error_data'     => 'NULL',
        ];
    }


    /**
     * Check incoming data array and prepare for saving into db
     *
     * @param array $array
     *
     * @return bool
     */
    private function processData($array)
    {
        if (empty($array['process_status']) || strlen($this->registry->main_class->format_striptags($array['process_status'])) < 1) {
            $this->error = 'Empty obligatory process_status log param';

            return false;
        }

        $dataRules = $this->getDataRules();

        foreach ($array as $key => $value) {

            if (is_array($value) && !empty($value['password'])) {
                $value['password'] = '****';
            }

            if ($dataRules[$key] == 'integer') {
                $value = intval($value);
                $this->dataArray[$key] = $value < 1 ? : "'" . intval($value) . "'";
            } elseif ($dataRules[$key] == 'string' && is_string($value)) {
                $value = trim($value);
                $this->dataArray[$key] = empty($value) ? : $this->registry->main_class->processing_data($value);
            } elseif ($dataRules[$key] == 'string' && !is_string($value)) {
                !isset($value['bill_amount']) ? : $value['bill_amount'] = (string)$value['bill_amount']; // workaround for var_export with float rounding
                $value = var_export($value, true);
                $this->dataArray[$key] = empty($value) ? : $this->registry->main_class->processing_data($value);
            }
        }

        return true;
    }


    /**
     * @return array
     */
    private function getDataRules()
    {
        return [
            'system_id'      => 'integer',
            'request_url'    => 'string',
            'method'         => 'string',
            'order_number'   => 'integer',
            'request_data'   => 'string',
            'response_data'  => 'string',
            'process_status' => 'string',
            'error_data'     => 'string',
        ];
    }


    private function clearCache()
    {
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|payment_logs");
    }

}
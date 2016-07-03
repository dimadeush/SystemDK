<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class admin_feedback extends model_base
{


    /**
     * @var feedback
     */
    private $model_feedback;
    private $error;
    private $error_array;
    private $result;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_feedback = singleton::getinstance('feedback', $registry);
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($num_page)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM " . PREFIX . "_mail_receivers_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT receiver_id,receiver_name,receiver_email,receiver_active FROM " . PREFIX . "_mail_receivers_" . $this->registry->sitelang
                   . " order by receiver_id DESC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist == 0 && $num_page > 1) {
                $this->error = 'unknown_page';

                return;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $receivers_all[] = [
                        "receiver_id"     => intval($result->fields['0']),
                        "receiver_name"   => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "receiver_email"  => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                        "receiver_active" => intval($result->fields['3']),
                    ];
                    $result->MoveNext();
                }
                $this->result['receivers_all'] = $receivers_all;
                if (isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if ($num_pages > 1) {
                    if ($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for ($i = 1; $i < $num_pages + 1; $i++) {
                        if ($i == $num_page) {
                            $html[] = ["number" => $i, "param1" => "1"];
                        } else {
                            $pagelink = 5;
                            if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "2"];
                            }
                            if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "3"];
                            }
                            if (($i == 1) && ($num_page > $pagelink + 1)) {
                                $html[] = ["number" => $i, "param1" => "4"];
                            }
                        }
                    }
                    if ($num_page < $num_pages) {
                        $nextpage = $num_page + 1;
                        $this->result['nextpage'] = $nextpage;
                    } else {
                        $this->result['nextpage'] = "no";
                    }
                    $this->result['html'] = $html;
                    $this->result['totalreceivers'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['receivers_all'] = "no";
                $this->result['html'] = "no";
            }
            if (defined('SYSTEMDK_FEEDBACK_RECEIVER') && SYSTEMDK_FEEDBACK_RECEIVER == "default") {
                $this->result['systemdk_feedback_receiver'] = "default";
            } else {
                $this->result['systemdk_feedback_receiver'] = "list";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'show_sql_error';
            $this->error_array = $error;

            return;
        }
    }


    public function feedback_add()
    {
        $this->result = 'add';
    }


    public function feedback_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'receiver_name',
                'receiver_email',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if (isset($array['receiver_active'])) {
            $data_array['receiver_active'] = intval($array['receiver_active']);
        }
        if ((!isset($array['receiver_name']) || !isset($array['receiver_email']) || !isset($array['receiver_active']))
            || ($data_array['receiver_name'] == ""
                || $data_array['receiver_email'] == "")
        ) {
            $this->error = 'add_not_all_data';

            return;
        }
        $data_array['receiver_name'] = $this->registry->main_class->format_striptags($data_array['receiver_name']);
        $data_array['receiver_name'] = $this->registry->main_class->processing_data($data_array['receiver_name']);
        $data_array['receiver_email'] = $this->registry->main_class->format_striptags($data_array['receiver_email']);
        if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['receiver_email']))) {
            $this->error = 'add_incorrect_email';

            return;
        }
        $data_array['receiver_email'] = $this->registry->main_class->processing_data($data_array['receiver_email']);
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_mail_receivers_id_" . $this->registry->sitelang, 'receiver_id');
        $sql = "INSERT INTO " . PREFIX . "_mail_receivers_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
               . "receiver_name,receiver_email,receiver_active) VALUES (" . $sequence_array['sequence_value_string'] . "" . $data_array['receiver_name'] . ","
               . $data_array['receiver_email'] . ",'" . $data_array['receiver_active'] . "')";
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($result === false) {
            $this->error = 'add_sql_error';
            $this->error_array = $error;

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|feedback|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|feedback|display");
            $this->result = 'add_ok';
        }
    }


    public function feedback_edit($receiver_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $receiver_id = intval($receiver_id);
        if ($receiver_id == 0) {
            $this->error = 'empty_receiver_id';

            return;
        }
        $sql = "SELECT receiver_id,receiver_name,receiver_email,receiver_active FROM " . PREFIX . "_mail_receivers_" . $this->registry->sitelang
               . " WHERE receiver_id = " . $receiver_id;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $receivers_all[] = [
                    "receiver_id"     => intval($result->fields['0']),
                    "receiver_name"   => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                    "receiver_email"  => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                    "receiver_active" => intval($result->fields['3']),
                ];
                $this->result['receivers_all'] = $receivers_all;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        }
        if (!isset($receivers_all)) {
            $this->error = 'edit_not_found';

            return;
        }
    }


    public function feedback_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'receiver_name',
                'receiver_email',
            ];
            $keys2 = [
                'receiver_id',
                'receiver_active',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if ((!isset($array['receiver_id']) || !isset($array['receiver_name']) || !isset($array['receiver_email']) || !isset($array['receiver_active']))
            || ($data_array['receiver_id'] == 0 || $data_array['receiver_name'] == "" || $data_array['receiver_email'] == "")
        ) {
            $this->error = 'edit_not_all_data';

            return;
        }
        $data_array['receiver_name'] = $this->registry->main_class->format_striptags($data_array['receiver_name']);
        $data_array['receiver_name'] = $this->registry->main_class->processing_data($data_array['receiver_name']);
        $data_array['receiver_email'] = $this->registry->main_class->format_striptags($data_array['receiver_email']);
        if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['receiver_email']))) {
            $this->error = 'edit_incorrect_email';

            return;
        }
        $data_array['receiver_email'] = $this->registry->main_class->processing_data($data_array['receiver_email']);
        $sql = "UPDATE " . PREFIX . "_mail_receivers_" . $this->registry->sitelang . " SET receiver_name = " . $data_array['receiver_name']
               . ", receiver_email = " . $data_array['receiver_email'] . ", receiver_active = '" . $data_array['receiver_active'] . "' WHERE receiver_id = '"
               . $data_array['receiver_id'] . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($result === false) {
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|feedback|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|feedback|display");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|feedback|edit|" . $data_array['receiver_id']);
            $this->result = 'edit_done';
        }
    }


    public function feedback_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['action'])) {
            $data_array['action'] = trim($array['action']);
        }
        if (isset($array['receiver_id'])) {
            $data_array['receiver_id'] = intval($array['receiver_id']);
        } else {
            $data_array['receiver_id'] = 0;
        }
        if ((!isset($array['action'])) || ($data_array['action'] == "" || $data_array['receiver_id'] == 0)) {
            $this->error = 'empty_data';

            return;
        }
        $data_array['action'] = $this->registry->main_class->format_striptags($data_array['action']);
        if ($data_array['action'] == "on") {
            $sql = "UPDATE " . PREFIX . "_mail_receivers_" . $this->registry->sitelang . " SET receiver_active = '1' WHERE receiver_id = '"
                   . $data_array['receiver_id'] . "'";
            $result = $this->db->Execute($sql);
        } elseif ($data_array['action'] == "off") {
            $sql = "UPDATE " . PREFIX . "_mail_receivers_" . $this->registry->sitelang . " SET receiver_active = '0' WHERE receiver_id = '"
                   . $data_array['receiver_id'] . "'";
            $result = $this->db->Execute($sql);
        } elseif ($data_array['action'] == "delete") {
            $sql = "DELETE FROM " . PREFIX . "_mail_receivers_" . $this->registry->sitelang . " WHERE receiver_id='" . $data_array['receiver_id'] . "'";
            $result = $this->db->Execute($sql);
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($result === false) {
            $this->error = 'status_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'status_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|feedback|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|feedback|display");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|feedback|edit|" . $data_array['receiver_id']);
            $this->result['feedback_message'] = $data_array['action'];
        }
    }
}
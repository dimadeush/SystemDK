<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_system_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class admin_system_pages extends model_base
{


    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($num_page = false)
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
        $sql0 = "SELECT count(system_page_id) FROM " . PREFIX . "_system_pages";
        $result = $this->db->Execute($sql0);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT a.system_page_id,a.system_page_author,a.system_page_name,a.system_page_title,a.system_page_lang,a.system_page_date FROM " . PREFIX
                   . "_system_pages a order by a.system_page_date DESC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
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
                $systempage_date = intval($result->fields['5']);
                $systempage_date = date("d.m.y H:i", $systempage_date);
                $systempage_all[] = [
                    "systempage_id"     => intval($result->fields['0']),
                    "systempage_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                    "systempage_name"   => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                    "systempage_title"  => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3'])),
                    "systempage_lang"   => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4'])),
                    "systempage_date"   => $systempage_date,
                ];
                $result->MoveNext();
            }
            $this->result['systempage_all'] = $systempage_all;
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
                $this->result['totalrules'] = $num_rows;
                $this->result['num_pages'] = $num_pages;
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $this->result['systempage_all'] = "no";
            $this->result['html'] = "no";
        }
    }


    public function systempage_add()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['systempage_author'] = $this->registry->main_class->get_user_login();
    }


    public function systempage_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'systempage_title',
                'systempage_content',
                'systempage_name',
                'systempage_lang',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if ((!isset($array['systempage_title']) || !isset($array['systempage_content']) || !isset($array['systempage_name']) || !isset($array['systempage_lang']))
            || ($data_array['systempage_name'] == "" || $data_array['systempage_title'] == "" || $data_array['systempage_content'] == ""
                || $data_array['systempage_lang'] == "")
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $now = $this->registry->main_class->get_time();
        $systempage_author = $this->registry->main_class->format_striptags($this->registry->main_class->get_user_login());
        $systempage_author = $this->registry->main_class->processing_data($systempage_author, 'need');
        foreach ($data_array as $key => $value) {
            if (in_array($key, ['systempage_title', 'systempage_name', 'systempage_lang'])) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $data_array['systempage_content'] = $this->registry->main_class->processing_data($data_array['systempage_content']);
        $data_array['systempage_content'] = substr(substr($data_array['systempage_content'], 0, -1), 1);
        $sql = "SELECT count(system_page_id) FROM " . PREFIX . "_system_pages WHERE system_page_name = " . $data_array['systempage_name']
               . " and system_page_lang = " . $data_array['systempage_lang'];
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $this->error = 'find_such_system_page';

                return;
            }
        }
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_system_pages_id", 'system_page_id');
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            // TODO: Make meta data available
            $sql = "INSERT INTO " . PREFIX . "_system_pages (" . $sequence_array['field_name_string']
                   . "system_page_author,system_page_name,system_page_title,system_page_date,system_page_content,system_page_lang,system_page_meta_title,system_page_meta_keywords,system_page_meta_description)  VALUES ("
                   . $sequence_array['sequence_value_string'] . $systempage_author . "," . $data_array['systempage_name'] . "," . $data_array['systempage_title'] . ",'"
                   . $now . "',empty_clob()," . $data_array['systempage_lang'] . ",null,null,null)";
            $insert_result = $this->db->Execute($sql);
            if ($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $insert_result2 = $this->db->UpdateClob(
                PREFIX . '_system_pages', 'system_page_content', $data_array['systempage_content'], 'system_page_id=' . $sequence_array['sequence_value']
            );
            if ($insert_result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $insert_result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $sql = "INSERT INTO " . PREFIX . "_system_pages (" . $sequence_array['field_name_string']
                   . "system_page_author,system_page_name,system_page_title,system_page_date,system_page_content,system_page_lang,system_page_meta_title,system_page_meta_keywords,system_page_meta_description)  VALUES ("
                   . $sequence_array['sequence_value_string'] . $systempage_author . "," . $data_array['systempage_name'] . "," . $data_array['systempage_title'] . ",'"
                   . $now . "','" . $data_array['systempage_content'] . "'," . $data_array['systempage_lang'] . ",null,null,null)";
            $insert_result = $this->db->Execute($sql);
            if ($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($insert_result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|show");
        $this->result = 'add_done';
    }


    public function systempage_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['action'])) {
            $action = trim($array['action']);
        }
        if (isset($array['systempage_id'])) {
            $systempage_id = intval($array['systempage_id']);
        }
        if ((!isset($array['action']) || !isset($array['systempage_id'])) || ($action == "" || $systempage_id == 0)) {
            $this->error = 'empty_data';

            return;
        }
        $action = $this->registry->main_class->format_striptags($action);
        if ($action != "delete") {
            $this->error = 'unknown_action';

            return;
        }
        $sql = "DELETE FROM " . PREFIX . "_system_pages WHERE system_page_id = '" . $systempage_id . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_done';

            return;
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|show");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|edit|" . $systempage_id);
        $this->registry->main_class->systemdk_clearcache("modules|account|register|rules");
        $this->result = $action . '_done';
    }


    public function systempage_edit($systempage_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $systempage_id = intval($systempage_id);
        if ($systempage_id == 0) {
            $this->error = 'no_id';

            return;
        }
        // TODO: Make meta data available
        $sql = "SELECT system_page_id,system_page_author,system_page_name,system_page_title,system_page_date,system_page_content,system_page_lang FROM " . PREFIX
               . "_system_pages WHERE system_page_id = '" . $systempage_id . "'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0) {
            $this->error = 'not_found';

            return;
        }
        $systempage_content = $this->registry->main_class->extracting_data($result->fields['5']);
        $systempage_all[] = [
            "systempage_id"      => intval($result->fields['0']),
            "systempage_author"  => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
            "systempage_name"    => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
            "systempage_title"   => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3'])),
            "systempage_date"    => intval($result->fields['4']),
            "systempage_content" => $systempage_content,
            "systempage_lang"    => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6'])),
        ];
        $this->result['systempage_all'] = $systempage_all;
    }


    public function systempage_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'systempage_name',
                'systempage_title',
                'systempage_content',
                'systempage_lang',
            ];
            $keys2 = [
                'systempage_id',
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
        if ((!isset($array['systempage_id']) || !isset($array['systempage_name']) || !isset($array['systempage_title']) || !isset($array['systempage_content'])
             || !isset($array['systempage_lang']))
            || ($data_array['systempage_id'] == 0 || $data_array['systempage_name'] == "" || $data_array['systempage_title'] == ""
                || $data_array['systempage_content'] == ""
                || $data_array['systempage_lang'] == "")
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $now = $this->registry->main_class->get_time();
        foreach ($data_array as $key => $value) {
            if (in_array($key, ['systempage_title', 'systempage_name', 'systempage_lang'])) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $data_array['systempage_content'] = $this->registry->main_class->processing_data($data_array['systempage_content']);
        $data_array['systempage_content'] = substr(substr($data_array['systempage_content'], 0, -1), 1);
        $sql = "SELECT count(system_page_id) FROM " . PREFIX . "_system_pages WHERE system_page_name = " . $data_array['systempage_name']
               . " and system_page_lang = " . $data_array['systempage_lang'] . " and system_page_id != " . $data_array['systempage_id'];
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $this->error = 'find_such_system_page';

                return;
            }
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE " . PREFIX . "_system_pages SET system_page_name = " . $data_array['systempage_name'] . ",system_page_title = "
                   . $data_array['systempage_title'] . ",system_page_date = '" . $now . "',system_page_content=empty_clob(),system_page_lang = "
                   . $data_array['systempage_lang'] . " WHERE system_page_id = '" . $data_array['systempage_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $result2 = $this->db->UpdateClob(
                PREFIX . '_system_pages', 'system_page_content', $data_array['systempage_content'], 'system_page_id = ' . $data_array['systempage_id']
            );
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $sql = "UPDATE " . PREFIX . "_system_pages SET system_page_name = " . $data_array['systempage_name'] . ",system_page_title = "
                   . $data_array['systempage_title'] . ",system_page_date = '" . $now . "',system_page_content = '" . $data_array['systempage_content']
                   . "',system_page_lang = " . $data_array['systempage_lang'] . " WHERE system_page_id = '" . $data_array['systempage_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_done';

            return;
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|show");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|edit|" . $data_array['systempage_id']);
        $this->registry->main_class->systemdk_clearcache("modules|account|register|rules");
        $this->result = 'edit_done';
    }
}
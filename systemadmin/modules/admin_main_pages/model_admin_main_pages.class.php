<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class admin_main_pages extends model_base
{


    /**
     * @var main_pages
     */
    private $model_main_pages;
    private $error;
    private $error_array;
    private $result;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_main_pages = singleton::getinstance('main_pages', $this->registry);
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
        $sql = "SELECT count(*) FROM " . PREFIX . "_main_pages_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT main_page_id,main_page_author,main_page_title,main_page_value_view,main_page_term_expire,main_page_status FROM " . PREFIX . "_main_pages_"
                   . $this->registry->sitelang . " order by main_page_date DESC";
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
                    $mainpage_termexpire = intval($result->fields['4']);
                    if (!isset($mainpage_termexpire) || $mainpage_termexpire == 0) {
                        $mainpage_termexpire = "unlim";
                    } else {
                        $mainpage_termexpire = "notunlim";
                    }
                    $mainpage_all[] = [
                        "mainpage_id"         => intval($result->fields['0']),
                        "mainpage_author"     => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "mainpage_title"      => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                        "mainpage_valueview"  => intval($result->fields['3']),
                        "mainpage_termexpire" => $mainpage_termexpire,
                        "mainpage_status"     => intval($result->fields['5']),
                    ];
                    $result->MoveNext();
                }
                $this->result['mainpage_all'] = $mainpage_all;
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
                    $this->result['totalmainpages'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['mainpage_all'] = "no";
                $this->result['html'] = "no";
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


    public function mainpage_add()
    {
        $this->result = false;
        $this->result['mainpage_author'] = trim($this->registry->main_class->get_user_login());
    }


    public function mainpage_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'mainpage_author',
                'mainpage_title',
                'mainpage_content',
                'mainpage_notes',
                'mainpage_termexpireaction',
            ];
            $keys2 = [
                'mainpage_status',
                'mainpage_termexpire',
                'mainpage_valueview',
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
        if ((!isset($array['mainpage_author']) || !isset($array['mainpage_title']) || !isset($array['mainpage_content']) || !isset($array['mainpage_notes'])
             || !isset($array['mainpage_status'])
             || !isset($array['mainpage_termexpire'])
             || !isset($array['mainpage_termexpireaction'])
             || !isset($array['mainpage_valueview']))
            || ($data_array['mainpage_author'] == "" || $data_array['mainpage_title'] == "" || $data_array['mainpage_content'] == "")
        ) {
            $this->error = 'add_not_all_data';

            return;
        }
        if ($data_array['mainpage_termexpire'] == "0") {
            $data_array['mainpage_termexpire'] = 'NULL';
            $data_array['mainpage_termexpireaction'] = 'NULL';
        } else {
            $data_array['mainpage_termexpire'] = $this->registry->main_class->get_time() + ($data_array['mainpage_termexpire'] * 86400);
            $data_array['mainpage_termexpire'] = "'" . $data_array['mainpage_termexpire'] . "'";
            $data_array['mainpage_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['mainpage_termexpireaction']);
            $data_array['mainpage_termexpireaction'] = $this->registry->main_class->processing_data($data_array['mainpage_termexpireaction']);
        }
        if ($data_array['mainpage_notes'] == "") {
            $data_array['mainpage_notes'] = 'NULL';
        } else {
            $data_array['mainpage_notes'] = $this->registry->main_class->processing_data($data_array['mainpage_notes']);
        }
        $now = $this->registry->main_class->get_time();
        $data_array['mainpage_author'] = $this->registry->main_class->format_striptags($data_array['mainpage_author']);
        $data_array['mainpage_author'] = $this->registry->main_class->processing_data($data_array['mainpage_author']);
        $data_array['mainpage_title'] = $this->registry->main_class->format_striptags($data_array['mainpage_title']);
        $data_array['mainpage_title'] = $this->registry->main_class->processing_data($data_array['mainpage_title']);
        $data_array['mainpage_content'] = $this->registry->main_class->processing_data($data_array['mainpage_content']);
        $data_array['mainpage_content'] = substr(substr($data_array['mainpage_content'], 0, -1), 1);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $mainpage_id = $this->db->GenID(PREFIX . "_main_pages_id_" . $this->registry->sitelang);
            $sql = "INSERT INTO " . PREFIX . "_main_pages_" . $this->registry->sitelang
                   . " (main_page_id,main_page_name_id,main_page_author,main_page_title,main_page_date,main_page_content,main_page_notes,main_page_value_view,main_page_status,main_page_term_expire,main_page_term_expire_action,main_page_meta_title,main_page_meta_keywords,main_page_meta_description)  VALUES ('"
                   . $mainpage_id . "',null," . $data_array['mainpage_author'] . "," . $data_array['mainpage_title'] . ",'" . $now . "',empty_clob(),empty_clob(),'"
                   . $data_array['mainpage_valueview'] . "','" . $data_array['mainpage_status'] . "'," . $data_array['mainpage_termexpire'] . ","
                   . $data_array['mainpage_termexpireaction'] . ",null,null,null)";
            $insert_result = $this->db->Execute($sql);
            if ($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $insert_result2 = $this->db->UpdateClob(
                PREFIX . '_main_pages_' . $this->registry->sitelang, 'main_page_content', $data_array['mainpage_content'], 'main_page_id=' . $mainpage_id
            );
            if ($insert_result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($data_array['mainpage_notes'] != 'NULL') {
                $data_array['mainpage_notes'] = substr(substr($data_array['mainpage_notes'], 0, -1), 1);
                $insert_result3 = $this->db->UpdateClob(
                    PREFIX . '_main_pages_' . $this->registry->sitelang, 'main_page_notes', $data_array['mainpage_notes'], 'main_page_id=' . $mainpage_id
                );
                if ($insert_result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $insert_result3 = true;
            }
            $this->db->CompleteTrans();
            if ($insert_result2 === false || $insert_result3 === false) {
                $insert_result = false;
            }
        } else {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_main_pages_id_" . $this->registry->sitelang, 'main_page_id');
            $sql = "INSERT INTO " . PREFIX . "_main_pages_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "main_page_name_id,main_page_author,main_page_title,main_page_date,main_page_content,main_page_notes,main_page_value_view,main_page_status,main_page_term_expire,main_page_term_expire_action,main_page_meta_title,main_page_meta_keywords,main_page_meta_description)  VALUES ("
                   . $sequence_array['sequence_value_string'] . "null," . $data_array['mainpage_author'] . "," . $data_array['mainpage_title'] . ",'" . $now . "','"
                   . $data_array['mainpage_content'] . "'," . $data_array['mainpage_notes'] . ",'" . $data_array['mainpage_valueview'] . "','"
                   . $data_array['mainpage_status'] . "'," . $data_array['mainpage_termexpire'] . "," . $data_array['mainpage_termexpireaction'] . ",null,null,null)";
            $insert_result = $this->db->Execute($sql);
            if ($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($insert_result === false) {
            $this->error = 'add_sql_error';
            $this->error_array = $error;

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|show");
            $this->result = 'add_done';
        }
    }


    public function mainpage_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['get_action'])) {
            $action = trim($array['get_action']);
        } else {
            $action = "";
        }
        if (!isset($array['get_action']) && isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }
        if (isset($array['get_mainpage_id'])) {
            $mainpage_id = intval($array['get_mainpage_id']);
        } else {
            $mainpage_id = 0;
        }
        if ($mainpage_id == 0 && isset($array['post_mainpage_id']) && is_array($array['post_mainpage_id']) && count($array['post_mainpage_id']) > 0) {
            $mainpage_id_is_arr = 1;
            for ($i = 0, $size = count($array['post_mainpage_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $mainpage_id = "'" . intval($array['post_mainpage_id'][$i]) . "'";
                } else {
                    $mainpage_id .= ",'" . intval($array['post_mainpage_id'][$i]) . "'";
                }
            }
        } else {
            $mainpage_id_is_arr = 0;
            $mainpage_id = "'" . $mainpage_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if (!isset($mainpage_id) || $action == "" || $mainpage_id == "'0'") {
            $this->error = 'empty_data';

            return;
        }
        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_main_pages_" . $this->registry->sitelang . " SET main_page_status = '1' WHERE main_page_id IN (" . $mainpage_id . ")";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            } else {
                $num_result = $this->db->Affected_Rows();
            }
        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_main_pages_" . $this->registry->sitelang . " SET main_page_status = '0' WHERE main_page_id IN (" . $mainpage_id . ")";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            } else {
                $num_result = $this->db->Affected_Rows();
            }
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $sql = "DELETE FROM " . PREFIX . "_main_pages_" . $this->registry->sitelang . " WHERE main_page_id IN (" . $mainpage_id . ")";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql2 = "SELECT main_menu_priority,main_menu_module,main_menu_submodule FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang
                    . " WHERE main_menu_module IN (" . $mainpage_id . ") or main_menu_submodule IN (" . $mainpage_id . ") order by main_menu_priority";
            $result2 = $this->db->Execute($sql2);
            if ($result2) {
                if (isset($result2->fields['0'])) {
                    $row_exist = intval($result2->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if ($row_exist > 0) {
                    $i = 0;
                    while (!$result2->EOF) {
                        $mainmenu_priority = intval($result2->fields['0'] - $i);
                        if (intval($result2->fields['1']) > 0) {
                            $mainpage_id2 = intval($result2->fields['1']);
                        } else {
                            $mainpage_id2 = intval($result2->fields['2']);
                        }
                        $result3 = $this->db->Execute(
                            "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_priority = main_menu_priority-1 WHERE main_menu_priority > "
                            . $mainmenu_priority
                        );
                        $num_result3 = $this->db->Affected_Rows();
                        if ($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        }
                        $sql4 = "DELETE FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang . " WHERE main_menu_module = '" . $mainpage_id2
                                . "' or main_menu_submodule = '" . $mainpage_id2 . "'";
                        $result4 = $this->db->Execute($sql4);
                        $num_result4 = $this->db->Affected_Rows();
                        if ($result4 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        }
                        if ($num_result4 > 0 || $num_result3 > 0) {
                            $clear = "yes";
                        }
                        $i++;
                        $result2->MoveNext();
                    }
                    if (isset($clear) && $clear == "yes") {
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
                    }
                    if (isset($error)) {
                        $result = false;
                    }
                }
            } else {
                $result = false;
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $this->db->CompleteTrans();
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result === false) {
            $this->error = 'status_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'status_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|show");
            if ($mainpage_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|edit");
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|edit|" . intval($array['get_mainpage_id']));
            }
            $this->result = $action;
        }
    }


    public function mainpage_edit($main_page_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $main_page_id = intval($main_page_id);
        if ($main_page_id == 0) {
            $this->error = 'empty_data';

            return;
        }
        $sql =
            "SELECT main_page_id,main_page_author,main_page_title,main_page_date,main_page_content,main_page_notes,main_page_value_view,main_page_status,main_page_term_expire,main_page_term_expire_action FROM "
            . PREFIX . "_main_pages_" . $this->registry->sitelang . " WHERE main_page_id='" . $main_page_id . "'";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        }
        if ($row_exist == 0) {
            $this->error = 'edit_not_found';

            return;
        }
        if (isset($result->fields['8']) && intval($result->fields['8']) != 0) {
            $mainpage_termexpire = intval($result->fields['8']);
            $mainpage_termexpire = date("d.m.y H:i", $mainpage_termexpire);
            $mainpage_termexpireday = intval(((intval($result->fields['8']) - $this->registry->main_class->get_time()) / 3600) / 24);
        } else {
            $mainpage_termexpire = "";
            $mainpage_termexpireday = "";
        }
        $mainpage_content = $this->registry->main_class->extracting_data($result->fields['4']);
        $mainpage_all[] = [
            "mainpage_id"               => intval($result->fields['0']),
            "mainpage_author"           => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
            "mainpage_title"            => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
            "mainpage_date"             => intval($result->fields['3']),
            "mainpage_content"          => $mainpage_content,
            "mainpage_notes"            => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5'])),
            "mainpage_valueview"        => intval($result->fields['6']),
            "mainpage_status"           => intval($result->fields['7']),
            "mainpage_termexpire"       => $mainpage_termexpire,
            "mainpage_termexpireaction" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9'])),
            "mainpage_termexpireday"    => $mainpage_termexpireday,
        ];
        $this->result['mainpage_all'] = $mainpage_all;
    }


    public function mainpage_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'mainpage_author',
                'mainpage_title',
                'mainpage_content',
                'mainpage_notes',
                'mainpage_termexpireaction',
            ];
            $keys2 = [
                'mainpage_id',
                'mainpage_status',
                'mainpage_termexpire',
                'mainpage_valueview',
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
        if ((!isset($array['mainpage_id']) || !isset($array['mainpage_author']) || !isset($array['mainpage_title']) || !isset($array['mainpage_content'])
             || !isset($array['mainpage_notes'])
             || !isset($array['mainpage_status'])
             || !isset($array['mainpage_termexpire'])
             || !isset($array['mainpage_termexpireaction'])
             || !isset($array['mainpage_valueview']))
            || ($data_array['mainpage_id'] == 0 || $data_array['mainpage_author'] == "" || $data_array['mainpage_title'] == "" || $data_array['mainpage_content'] == "")
        ) {
            $this->error = 'edit_not_all_data';

            return;
        }
        if ($data_array['mainpage_termexpire'] == "0") {
            $data_array['mainpage_termexpire'] = 'NULL';
            $data_array['mainpage_termexpireaction'] = 'NULL';
        } else {
            $data_array['mainpage_termexpire'] = $this->registry->main_class->get_time() + ($data_array['mainpage_termexpire'] * 86400);
            $data_array['mainpage_termexpire'] = "'" . $data_array['mainpage_termexpire'] . "'";
            $data_array['mainpage_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['mainpage_termexpireaction']);
            $data_array['mainpage_termexpireaction'] = $this->registry->main_class->processing_data($data_array['mainpage_termexpireaction']);
        }
        if ($data_array['mainpage_notes'] == "") {
            $data_array['mainpage_notes'] = 'NULL';
        } else {
            $data_array['mainpage_notes'] = $this->registry->main_class->processing_data($data_array['mainpage_notes']);
        }
        $data_array['mainpage_author'] = $this->registry->main_class->format_striptags($data_array['mainpage_author']);
        $data_array['mainpage_author'] = $this->registry->main_class->processing_data($data_array['mainpage_author']);
        $data_array['mainpage_title'] = $this->registry->main_class->format_striptags($data_array['mainpage_title']);
        $data_array['mainpage_title'] = $this->registry->main_class->processing_data($data_array['mainpage_title']);
        $data_array['mainpage_content'] = $this->registry->main_class->processing_data($data_array['mainpage_content']);
        $data_array['mainpage_content'] = substr(substr($data_array['mainpage_content'], 0, -1), 1);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE " . PREFIX . "_main_pages_" . $this->registry->sitelang . " SET main_page_author = " . $data_array['mainpage_author']
                   . ",main_page_title = " . $data_array['mainpage_title'] . ",main_page_content = empty_clob(),main_page_notes = empty_clob(),main_page_status = '"
                   . $data_array['mainpage_status'] . "',main_page_term_expire = " . $data_array['mainpage_termexpire'] . ",main_page_term_expire_action = "
                   . $data_array['mainpage_termexpireaction'] . ",main_page_value_view = '" . $data_array['mainpage_valueview'] . "' WHERE main_page_id = '"
                   . $data_array['mainpage_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $result2 = $this->db->UpdateClob(
                PREFIX . '_main_pages_' . $this->registry->sitelang, 'main_page_content', $data_array['mainpage_content'], 'main_page_id = ' . $data_array['mainpage_id']
            );
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($data_array['mainpage_notes'] != 'NULL') {
                $data_array['mainpage_notes'] = substr(substr($data_array['mainpage_notes'], 0, -1), 1);
                $result3 = $this->db->UpdateClob(
                    PREFIX . '_main_pages_' . $this->registry->sitelang, 'main_page_notes', $data_array['mainpage_notes'], 'main_page_id = ' . $data_array['mainpage_id']
                );
                if ($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $result3 = true;
            }
            $this->db->CompleteTrans();
            if ($result2 === false || $result3 === false) {
                $result = false;
            }
        } else {
            $sql = "UPDATE " . PREFIX . "_main_pages_" . $this->registry->sitelang . " SET main_page_author = " . $data_array['mainpage_author']
                   . ",main_page_title = " . $data_array['mainpage_title'] . ",main_page_content = '" . $data_array['mainpage_content'] . "',main_page_notes = "
                   . $data_array['mainpage_notes'] . ",main_page_status = '" . $data_array['mainpage_status'] . "',main_page_term_expire = "
                   . $data_array['mainpage_termexpire'] . ",main_page_term_expire_action = " . $data_array['mainpage_termexpireaction'] . ",main_page_value_view = '"
                   . $data_array['mainpage_valueview'] . "' WHERE main_page_id = '" . $data_array['mainpage_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($result === false) {
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|edit|" . $data_array['mainpage_id']);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|main_pages|show|" . $data_array['mainpage_id']);
            $this->result = 'edit_done';
        }
    }
}
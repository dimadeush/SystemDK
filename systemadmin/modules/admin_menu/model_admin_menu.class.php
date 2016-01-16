<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class admin_menu extends model_base
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


    public function index()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $sql =
            "SELECT a.main_menu_id,a.main_menu_type,a.main_menu_name,a.main_menu_module,a.main_menu_submodule,a.main_menu_add_param,a.main_menu_target,a.main_menu_priority,a.main_menu_in_menu,(SELECT max(b.main_menu_id) FROM "
            . PREFIX . "_main_menu_" . $this->registry->sitelang
            . " b WHERE b.main_menu_priority = a.main_menu_priority - 1) as main_menu_id2,(SELECT max(c.main_menu_id) FROM " . PREFIX . "_main_menu_"
            . $this->registry->sitelang . " c WHERE c.main_menu_priority = a.main_menu_priority + 1) as main_menu_id3 FROM " . PREFIX . "_main_menu_"
            . $this->registry->sitelang . " a ORDER BY a.main_menu_priority";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $mainmenu_id = intval($result->fields['0']);
                    $mainmenu_type = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $mainmenu_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $mainmenu_module = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $mainmenu_submodule = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $mainmenu_add_param = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    $mainmenu_target = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                    $mainmenu_priority = intval($result->fields['7']);
                    $mainmenu_inmenu = intval($result->fields['8']);
                    $menu_priority1 = $mainmenu_priority - 1;
                    $menu_priority2 = $mainmenu_priority + 1;
                    $mainmenu_id2 = $result->fields['9'];
                    if (isset($mainmenu_id2) && $mainmenu_id2 != 0) {
                        $param1 = $mainmenu_id2;
                    } else {
                        $param1 = "";
                    }
                    $mainmenu_id3 = $result->fields['10'];
                    if (isset($mainmenu_id3) && $mainmenu_id3 != 0) {
                        $param2 = $mainmenu_id3;
                    } else {
                        $param2 = "";
                    }
                    $menu_all[] = [
                        "mainmenu_id"        => $mainmenu_id,
                        "mainmenu_type"      => $mainmenu_type,
                        "mainmenu_name"      => $mainmenu_name,
                        "mainmenu_module"    => $mainmenu_module,
                        "mainmenu_submodule" => $mainmenu_submodule,
                        "mainmenu_add_param" => $mainmenu_add_param,
                        "mainmenu_target"    => $mainmenu_target,
                        "mainmenu_priority"  => $mainmenu_priority,
                        "mainmenu_inmenu"    => $mainmenu_inmenu,
                        "menu_priority1"     => $menu_priority1,
                        "menu_priority2"     => $menu_priority2,
                        "param1"             => $param1,
                        "param2"             => $param2,
                    ];
                    $result->MoveNext();
                }
                $this->result['menu_all'] = $menu_all;
            } else {
                $this->result['menu_all'] = "no";
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


    public function menu_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'mainmenu_id2',
                'mainmenu_priority',
                'mainmenu_priority2',
                'mainmenu_id',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if ((!isset($array['mainmenu_id2']) || !isset($array['mainmenu_priority']) || !isset($array['mainmenu_priority2']) || !isset($array['mainmenu_id']))
            || ($data_array['mainmenu_id2'] == 0 || $data_array['mainmenu_priority'] == 0 || $data_array['mainmenu_priority2'] == 0 || $data_array['mainmenu_id'] == 0)
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $this->db->StartTrans();
        $result1 = $this->db->Execute(
            "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_priority = '" . $data_array['mainmenu_priority'] . "' WHERE main_menu_id = '"
            . $data_array['mainmenu_id2'] . "'"
        );
        $num_result1 = $this->db->Affected_Rows();
        if ($result1 === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        $result2 = $this->db->Execute(
            "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_priority = '" . $data_array['mainmenu_priority2']
            . "' WHERE main_menu_id = '" . $data_array['mainmenu_id'] . "'"
        );
        $num_result2 = $this->db->Affected_Rows();
        if ($result2 === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        $this->db->CompleteTrans();
        if ($result1 === false || $result2 === false) {
            if ($num_result1 > 0 || $num_result2 > 0) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
            }
            $this->error = 'show_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result1 !== false && $result2 !== false && $num_result1 == 0 && $num_result2 == 0) {
            $this->error = 'save_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
            $this->result = 'range_done';
        }
    }


    public function menu_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['action'])) {
            $action = trim($array['action']);
        }
        if (isset($array['menu_id'])) {
            $menu_id = intval($array['menu_id']);
        }
        if ((!isset($array['action']) || !isset($array['menu_id'])) || ($action == "" || $menu_id == 0)) {
            $this->error = 'not_all_data';

            return;
        }
        $action = $this->registry->main_class->format_striptags($action);
        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_in_menu = '1' WHERE main_menu_id = '" . $menu_id . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_in_menu = '0' WHERE main_menu_id = '" . $menu_id . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        } elseif ($action == "delete") {
            $sql = "SELECT main_menu_priority FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang . " WHERE main_menu_id = '" . $menu_id . "'";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $mainmenu_priority = intval($result->fields['0']);
                } else {
                    $mainmenu_priority = 0;
                }
                if ($mainmenu_priority > 0) {
                    $this->db->StartTrans();
                    $result2 = $this->db->Execute(
                        "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_priority = main_menu_priority - 1 WHERE main_menu_priority > '"
                        . $mainmenu_priority . "'"
                    );
                    if ($result2 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $sql3 = "DELETE FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang . " WHERE main_menu_id = '" . $menu_id . "'";
                    $result3 = $this->db->Execute($sql3);
                    $num_result = $this->db->Affected_Rows();
                    if ($result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $this->db->CompleteTrans();
                    if ($result2 === false || $result3 === false) {
                        $result = false;
                    }
                } else {
                    $num_result = 0;
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
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
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
            $this->result = $action;
        }
    }


    public function menu_select_type()
    {
        $this->result = false;
        $this->result['menu_select_type'] = "yes";
    }


    public function menu_add($menu_type)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $menu_type = trim($menu_type);
        if ($menu_type == "") {
            $this->error = 'not_all_data';

            return;
        }
        $menu_type = $this->registry->main_class->format_striptags($menu_type);
        if ($menu_type == "module") {
            $modules_dir = dir("../modules");
            while ($file = $modules_dir->read()) {
                if (strpos($file, ".") === 0) {
                    continue;
                } elseif (preg_match("/html/i", $file)) {
                    continue;
                } else {
                    $modules_list[] = "$file";
                }
            }
            closedir($modules_dir->handle);
            @sort($modules_list);
            for ($i = 0; $i < sizeof($modules_list); $i++) {
                $modules_list[$i] = trim($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->format_striptags($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->processing_data($modules_list[$i]);
                $modules_list[$i] = substr(substr($modules_list[$i], 0, -1), 1);
                if ($modules_list[$i] != "" && $modules_list[$i] != "main_pages") {
                    $sql = "SELECT main_menu_id FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang . " WHERE main_menu_module = '" . $modules_list[$i]
                           . "' OR main_menu_submodule = '" . $modules_list[$i] . "'";
                    $result = $this->db->Execute($sql);
                    if ($result) {
                        if (isset($result->fields['0'])) {
                            $row_exist = intval($result->fields['0']);
                        } else {
                            $row_exist = 0;
                        }
                        $modules_list[$i] = $this->registry->main_class->extracting_data($modules_list[$i]);
                        if ($row_exist == 0 && $modules_list[$i] != "") {
                            $module_name[]['module_name'] = $modules_list[$i];
                        }
                    } else {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                        $this->error = 'add_sql_error';
                        $this->error_array = $error;

                        return;
                    }
                }
            }
            if (!isset($module_name) || $module_name == "") {
                $this->error = 'add_no_modules';

                return;
            } else {
                $this->result['module_name'] = $module_name;
            }
        } elseif ($menu_type == "main_page") {
            $sql = "select a.main_page_id, a.main_page_title from " . PREFIX . "_main_pages_" . $this->registry->sitelang
                   . " a where a.main_page_id not in (select b.main_menu_add_param from " . PREFIX . "_main_menu_" . $this->registry->sitelang
                   . " b where b.main_menu_type = 'main_page' and (b.main_menu_module = 'main_pages' or b.main_menu_submodule = 'main_pages') and b.main_menu_add_param is not null)";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if ($row_exist > 0) {
                    while (!$result->EOF) {
                        $mainpage_id = intval($result->fields['0']);
                        $mainpage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $page_all[] = ["mainpage_id" => $mainpage_id, "mainpage_title" => $mainpage_title];
                        $result->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'add_sql_error';
                $this->error_array = $error;

                return;
            }
            if (!isset($page_all) || $page_all == "") {
                $this->error = 'add_no_pages';

                return;
            }
            $this->result['page_all'] = $page_all;
        } elseif ($menu_type == "other") {
            // no actions
        } else {
            $this->error = 'unknown_menu_type';

            return;
        }
        $this->result['menu_type'] = $menu_type;
        $this->result['menu_select_type'] = "no";
    }


    public function menu_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'mainmenu_name',
                'menu_object',
                'menu_type',
                'mainmenu_module',
                'mainmenu_target',
            ];
            $keys2 = [
                'mainmenu_inmenu',
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
        if ((!isset($array['mainmenu_name']) || !isset($array['menu_object']) || !isset($array['menu_type']) || !isset($array['mainmenu_module'])
             || !isset($array['mainmenu_target'])
             || !isset($array['mainmenu_inmenu']))
            || ($data_array['mainmenu_name'] == "" || $data_array['menu_object'] == "" || $data_array['menu_type'] == "" || $data_array['mainmenu_module'] == ""
                || $data_array['mainmenu_target'] == "")
        ) {
            $this->error = 'add_not_all_data';

            return;
        }
        $sql = "SELECT MAX(main_menu_priority) FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'add_sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
            $mainmenu_priority = intval($result->fields['0']);
            $mainmenu_priority = $mainmenu_priority + 1;
        } else {
            $mainmenu_priority = 1;
        }
        $keys = [
            'mainmenu_module',
            'menu_type',
            'mainmenu_name',
            'mainmenu_target',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $data_array['menu_object'] = $this->registry->main_class->format_striptags($data_array['menu_object']);
        $main_menu_add_param = 'NULL';
        if ($data_array['menu_type'] == "'main_page'") {
            $main_menu_add_param = $data_array['mainmenu_module'];
            $data_array['mainmenu_module'] = "'main_pages'";
        }
        if ($data_array['menu_object'] == "parent") {
            $mainmenu_submodule = 'NULL';
        } elseif ($data_array['menu_object'] == "children") {
            $mainmenu_submodule = $data_array['mainmenu_module'];
            $data_array['mainmenu_module'] = 'NULL';
        } else {
            $this->error = 'unknown_menu_object';

            return;
        }
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_main_menu_id_" . $this->registry->sitelang, 'main_menu_id');
        $sql = "INSERT INTO " . PREFIX . "_main_menu_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
               . "main_menu_type,main_menu_name,main_menu_module,main_menu_submodule,main_menu_add_param,main_menu_target,main_menu_priority,main_menu_in_menu)  VALUES ("
               . $sequence_array['sequence_value_string'] . "" . $data_array['menu_type'] . "," . $data_array['mainmenu_name'] . "," . $data_array['mainmenu_module']
               . ","
               . $mainmenu_submodule . "," . $main_menu_add_param . "," . $data_array['mainmenu_target'] . ",'" . $mainmenu_priority . "','"
               . $data_array['mainmenu_inmenu']
               . "')";
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'add_sql_error';
            $this->error_array = $error;

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
            $this->result = 'add_ok';
        }
    }


    public function menu_edit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['menu_type'])) {
            $menu_type = trim($array['menu_type']);
        }
        if (isset($array['menu_id'])) {
            $menu_id = intval($array['menu_id']);
        }
        if ((!isset($array['menu_type']) || !isset($array['menu_id'])) || ($menu_type == "" || $menu_id == 0)) {
            $this->error = 'edit_not_all_data';

            return;
        }
        $sql1 =
            "SELECT main_menu_id,main_menu_type,main_menu_name,main_menu_module,main_menu_submodule,main_menu_add_param,main_menu_target,main_menu_priority,main_menu_in_menu FROM "
            . PREFIX . "_main_menu_" . $this->registry->sitelang . " WHERE main_menu_id = '" . $menu_id . "'";
        $result1 = $this->db->Execute($sql1);
        if ($result1) {
            if (isset($result1->fields['0'])) {
                $row_exist = intval($result1->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                while (!$result1->EOF) {
                    $mainmenu_id = intval($result1->fields['0']);
                    $mainmenu_type = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['1']));
                    $mainmenu_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['2']));
                    $mainmenu_module = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['3']));
                    $mainmenu_submodule = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['4']));
                    $mainmenu_add_param = intval($result1->fields['5']);
                    $mainmenu_target = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['6']));
                    $mainmenu_priority = intval($result1->fields['7']);
                    $mainmenu_inmenu = intval($result1->fields['8']);
                    $menu_all[] = [
                        "mainmenu_id"        => $mainmenu_id,
                        "mainmenu_type"      => $mainmenu_type,
                        "mainmenu_name"      => $mainmenu_name,
                        "mainmenu_module"    => $mainmenu_module,
                        "mainmenu_submodule" => $mainmenu_submodule,
                        "mainmenu_add_param" => $mainmenu_add_param,
                        "mainmenu_target"    => $mainmenu_target,
                        "mainmenu_priority"  => $mainmenu_priority,
                        "mainmenu_inmenu"    => $mainmenu_inmenu,
                    ];
                    $result1->MoveNext();
                }
            } else {
                $this->error = 'edit_not_found';

                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        }
        $menu_type = $this->registry->main_class->format_striptags($menu_type);
        if ($menu_type == "module") {
            $modules_dir = dir("../modules");
            while ($file = $modules_dir->read()) {
                if (strpos($file, ".") === 0) {
                    continue;
                } elseif (preg_match("/html/i", $file)) {
                    continue;
                } else {
                    $modules_list[] = "$file";
                }
            }
            @closedir($modules_dir->handle);
            @sort($modules_list);
            for ($i = 0; $i < sizeof($modules_list); $i++) {
                $modules_list[$i] = trim($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->format_striptags($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->processing_data($modules_list[$i]);
                $modules_list[$i] = substr(substr($modules_list[$i], 0, -1), 1);
                if ($modules_list[$i] != "" && $modules_list[$i] != "main_pages") {
                    $sql = "SELECT main_menu_id FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang . " WHERE main_menu_module = '" . $modules_list[$i]
                           . "' OR main_menu_submodule = '" . $modules_list[$i] . "'";
                    $result = $this->db->Execute($sql);
                    if ($result) {
                        if (isset($result->fields['0'])) {
                            $row_exist = intval($result->fields['0']);
                        } else {
                            $row_exist = 0;
                        }
                        $modules_list[$i] = $this->registry->main_class->extracting_data($modules_list[$i]);
                        if (($row_exist == 0 && $modules_list[$i] != "") || (intval($result->fields['0']) == $menu_id)) {
                            $module_name[]['module_name'] = $modules_list[$i];
                        }
                    } else {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                        $this->error = 'edit_sql_error';
                        $this->error_array = $error;

                        return;
                    }
                }
            }
            if (!isset($module_name) || $module_name == "") {
                $this->error = 'edit_no_modules';

                return;
            } else {
                $this->result['module_name'] = $module_name;
            }
        } elseif ($menu_type == "main_page") {
            $sql = "select a.main_page_id, a.main_page_title from " . PREFIX . "_main_pages_" . $this->registry->sitelang
                   . " a where a.main_page_id not in (select b.main_menu_add_param from " . PREFIX . "_main_menu_" . $this->registry->sitelang
                   . " b where b.main_menu_type = 'main_page' and (b.main_menu_module = 'main_pages' or b.main_menu_submodule = 'main_pages') and b.main_menu_add_param is not null and b.main_menu_id <> "
                   . $menu_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if ($row_exist > 0) {
                    while (!$result->EOF) {
                        $mainpage_id = intval($result->fields['0']);
                        $mainpage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $page_all[] = ["mainpage_id" => $mainpage_id, "mainpage_title" => $mainpage_title];
                        $result->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'edit_sql_error';
                $this->error_array = $error;

                return;
            }
            if (!isset($page_all) || $page_all == "") {
                $this->error = 'edit_no_pages';

                return;
            }
            $this->result['page_all'] = $page_all;
        } elseif ($menu_type == "other") {
            // no actions
        } else {
            $this->error = 'unknown_menu_type';

            return;
        }
        $this->result['menu_type'] = $menu_type;
        $this->result['menu_all'] = $menu_all;
    }


    public function menu_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'mainmenu_name',
                'menu_object',
                'menu_type',
                'mainmenu_module',
                'mainmenu_target',
            ];
            $keys2 = [
                'mainmenu_id',
                'mainmenu_inmenu',
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
        if ((!isset($array['mainmenu_id']) || !isset($array['mainmenu_name']) || !isset($array['menu_object']) || !isset($array['menu_type'])
             || !isset($array['mainmenu_module'])
             || !isset($array['mainmenu_target'])
             || !isset($array['mainmenu_inmenu']))
            || ($data_array['mainmenu_id'] == 0 || $data_array['mainmenu_name'] == "" || $data_array['menu_object'] == "" || $data_array['menu_type'] == ""
                || $data_array['mainmenu_module'] == ""
                || $data_array['mainmenu_target'] == "")
        ) {
            $this->error = 'edit_not_all_data';

            return;
        }
        $keys = [
            'mainmenu_module',
            'menu_type',
            'mainmenu_name',
            'mainmenu_target',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $data_array['menu_object'] = $this->registry->main_class->format_striptags($data_array['menu_object']);
        $main_menu_add_param = 'NULL';
        if ($data_array['menu_type'] == "'main_page'") {
            $main_menu_add_param = $data_array['mainmenu_module'];
            $data_array['mainmenu_module'] = "'main_pages'";
        }
        if ($data_array['menu_object'] == "parent") {
            $mainmenu_submodule = 'NULL';
        } elseif ($data_array['menu_object'] == "children") {
            $mainmenu_submodule = $data_array['mainmenu_module'];
            $data_array['mainmenu_module'] = 'NULL';
        } else {
            $this->error = 'unknown_menu_object';

            return;
        }
        $sql = "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang . " SET main_menu_type = " . $data_array['menu_type'] . ",main_menu_name = "
               . $data_array['mainmenu_name'] . ",main_menu_module = " . $data_array['mainmenu_module'] . ",main_menu_submodule = " . $mainmenu_submodule
               . ",main_menu_add_param = " . $main_menu_add_param . ",main_menu_target = " . $data_array['mainmenu_target'] . ",main_menu_in_menu = '"
               . $data_array['mainmenu_inmenu'] . "' WHERE main_menu_id = '" . $data_array['mainmenu_id'] . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|menu|edit|" . $data_array['menu_type'] . "|" . $data_array['mainmenu_id']
            );
            $this->result = 'edit_done';
        }
    }
}
<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_modules.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class admin_modules extends model_base
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


    public function index($cached = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $handle = opendir('../modules');
        while (false !== ($file = readdir($handle))) {
            if ((!preg_match("/[.]/i", $file))) {
                $modlist[] = "$file";
            }
        }
        closedir($handle);
        for ($i = 0; $i < sizeof($modlist); $i++) {
            $modlist[$i] = trim($modlist[$i]);
            $modlist[$i] = $this->registry->main_class->format_striptags($modlist[$i]);
            $modlist[$i] = $this->registry->main_class->processing_data($modlist[$i]);
            $modlist[$i] = substr(substr($modlist[$i], 0, -1), 1);
            if ($modlist[$i] != "") {
                $sql = "SELECT module_id FROM " . PREFIX . "_modules_" . $this->registry->sitelang . " WHERE module_name = '" . $modlist[$i] . "'";
                $result = $this->db->Execute($sql);
                if ($result) {
                    if (isset($result->fields['0'])) {
                        $row_exist = intval($result->fields['0']);
                    } else {
                        $row_exist = 0;
                    }
                    if ($row_exist > 0) {
                        $module_id = $result->fields['0'];
                        $module_id = intval($module_id);
                    } else {
                        $module_id = 0;
                    }
                    if ($module_id == 0) {
                        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_modules_id_" . $this->registry->sitelang, 'module_id');
                        $result = $this->db->Execute(
                            "INSERT INTO " . PREFIX . "_modules_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                            . "module_name,module_custom_name,module_version,module_status,module_value_view) VALUES (" . $sequence_array['sequence_value_string'] . " '"
                            . $modlist[$i] . "', '" . $modlist[$i] . "', NULL, '0', '0')"
                        );
                        if ($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        } else {
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|modules|show");
                            $cached = false;
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            }
        }
        $sql = "SELECT module_name,module_id FROM " . PREFIX . "_modules_" . $this->registry->sitelang;
        $result2 = $this->db->Execute($sql);
        if ($result2) {
            if (isset($result2->fields['1'])) {
                $row_exist = intval($result2->fields['1']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                while (!$result2->EOF) {
                    $module_name = $this->registry->main_class->extracting_data($result2->fields['0']);
                    $delete = "yes";
                    $handle = opendir('../modules');
                    while (false !== ($file = readdir($handle))) {
                        $file = trim($file);
                        $file = $this->registry->main_class->format_striptags($file);
                        if ($file == $module_name) {
                            $delete = "no";
                        }
                    }
                    closedir($handle);
                    if ($delete == "yes") {
                        $module_name = $this->registry->main_class->processing_data($module_name);
                        if (!isset($delete_module_name)) {
                            $delete_module_name = "module_name = " . $module_name . "";
                        } else {
                            $delete_module_name .= " or module_name = " . $module_name . "";
                        }
                    }
                    $result2->MoveNext();
                }
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if (isset($delete_module_name) && trim($delete_module_name) != "") {
            $result3 = $this->db->Execute("DELETE FROM " . PREFIX . "_modules_" . $this->registry->sitelang . " WHERE $delete_module_name");
            $num_result3 = $this->db->Affected_Rows();
            if ($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            } elseif ($num_result3 > 0) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|modules|show");
                $cached = false;
            }
        }
        if (!$cached) {
            $sql = "SELECT module_id, module_name, module_custom_name, module_version, module_status, module_value_view FROM " . PREFIX . "_modules_"
                   . $this->registry->sitelang . " ORDER BY module_name ASC";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if ($row_exist > 0) {
                    while (!$result->EOF) {
                        $module_id = intval($result->fields['0']);
                        $module_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $module_version = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $module_status = intval($result->fields['4']);
                        $module_valueview = intval($result->fields['5']);
                        $module_all[] = [
                            "module_id"         => $module_id,
                            "module_name"       => $module_name,
                            "module_customname" => $module_customname,
                            "module_version"    => $module_version,
                            "module_status"     => $module_status,
                            "module_valueview"  => $module_valueview,
                        ];
                        $result->MoveNext();
                    }
                    $this->result['module_all'] = $module_all;
                } else {
                    $this->result['module_all'] = "no";
                }
            } else {
                $this->result['module_all'] = "no";
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if (isset($error)) {
            $this->error = 'show_sql_error';
            $this->error_array = $error;
        } else {
            $this->result['error'] = "no";
        }
    }


    public function display_menu()
    {
        $sql = "SELECT module_id,module_name,module_custom_name FROM " . PREFIX . "_modules_" . $this->registry->sitelang
               . " WHERE module_name not in ('account','feedback','main_pages') order by module_id ASC";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['1'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $module_ordinal_number = 0;
                while (!$result->EOF) {
                    $module_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $additional_modules[] = [
                        "module_name"           => $module_name,
                        "module_customname"     => $module_customname,
                        "module_ordinal_number" => $module_ordinal_number,
                    ];
                    if ($module_ordinal_number == 5) {
                        $module_ordinal_number = 0;
                    } else {
                        $module_ordinal_number = $module_ordinal_number + 1;
                    }
                    $result->MoveNext();
                }
                $this->result['display_admin_graphic'] = DISPLAY_ADMIN_GRAPHIC;
                $this->result['display_menu'] = "yes";
                $this->result['additional_modules'] = $additional_modules;
            } else {
                $this->result['display_menu'] = "no";
            }
        } else {
            $this->result['display_menu'] = "no";
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'show_sql_error';
            $this->error_array = $error;
        }
    }


    public function edit_module($module_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $module_id = intval($module_id);
        if ($module_id == 0) {
            $this->error = 'empty_data';

            return;
        }
        $sql = "SELECT module_id, module_name, module_custom_name, module_version, module_status, module_value_view FROM " . PREFIX . "_modules_"
               . $this->registry->sitelang . " WHERE module_id = '" . $module_id . "'";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $module_id = intval($result->fields['0']);
                $module_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $module_version = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $module_status = intval($result->fields['4']);
                $module_valueview = intval($result->fields['5']);
                $module_all[] = [
                    "module_id"         => $module_id,
                    "module_name"       => $module_name,
                    "module_customname" => $module_customname,
                    "module_version"    => $module_version,
                    "module_status"     => $module_status,
                    "module_valueview"  => $module_valueview,
                ];
                $this->result['module_all'] = $module_all;
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
        //$this->result['module_edit_save'] = "save";
    }


    public function edit_module_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'module_customname',
            ];
            $keys2 = [
                'module_id',
                'module_status',
                'module_valueview',
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
        if ((!isset($array['module_id']) || !isset($array['module_customname']) || !isset($array['module_status']) || !isset($array['module_valueview']))
            || ($data_array['module_id'] == 0 || $data_array['module_customname'] == "")
        ) {
            $this->error = 'edit_not_all_data';

            return;
        }
        $data_array['module_customname'] = $this->registry->main_class->format_striptags($data_array['module_customname']);
        $data_array['module_customname'] = $this->registry->main_class->processing_data($data_array['module_customname']);
        $sql = "SELECT module_id FROM " . PREFIX . "_modules_" . $this->registry->sitelang . " WHERE module_custom_name = "
               . $data_array['module_customname'] . " AND module_id <> '" . $data_array['module_id'] . "'";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $this->error = 'edit_found';

                return;
            }
            $sql = "UPDATE " . PREFIX . "_modules_" . $this->registry->sitelang . " SET module_custom_name = " . $data_array['module_customname']
                   . ", module_status = '" . $data_array['module_status'] . "', module_value_view = '" . $data_array['module_valueview'] . "' WHERE module_id = '"
                   . $data_array['module_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        }
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
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|modules|edit|" . $data_array['module_id']);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|modules|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|homepage");
            $this->result['module_edit_save'] = 'edit_done';
        }
    }


    public function status_module($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['status'])) {
            $status = trim($_GET['status']);
        }
        if (isset($array['module_id'])) {
            $module_id = intval($array['module_id']);
        }
        if ((!isset($array['module_id']) || !isset($array['status'])) || ($module_id == 0 || $status == "")) {
            $this->error = 'empty_data';

            return;
        }
        $status = $this->registry->main_class->format_striptags($status);
        if ($status == "off") {
            $sql = "UPDATE " . PREFIX . "_modules_" . $this->registry->sitelang . " SET module_status = '0' WHERE module_id = '" . $module_id . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        } elseif ($status == "on") {
            $sql = "UPDATE " . PREFIX . "_modules_" . $this->registry->sitelang . " SET module_status = '1' WHERE module_id = '" . $module_id . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'status_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'status_ok';

            return;
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|modules|edit|" . $module_id);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|modules|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|homepage");
            $this->result['module_edit_save'] = $status;
        }
    }
}
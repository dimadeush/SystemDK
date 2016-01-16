<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      block_model_menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class block_menu extends model_base
{


    public function index()
    {
        $sql = "SELECT a.main_menu_type,a.main_menu_name,a.main_menu_module,a.main_menu_submodule,a.main_menu_add_param,a.main_menu_target,"
               . "(SELECT max(b.module_value_view) FROM " . PREFIX . "_modules_" . $this->registry->sitelang . " b"
                    . " WHERE (a.main_menu_module is not NULL AND b.module_name=a.main_menu_module OR a.main_menu_submodule is not NULL AND b.module_name=a.main_menu_submodule) AND b.module_status='1' AND a.main_menu_type='module') as module_value_view,"
               . "(SELECT max(c.main_page_value_view) FROM " . PREFIX . "_main_pages_" . $this->registry->sitelang . " c"
                    . " WHERE (a.main_menu_module is not NULL and a.main_menu_module = 'main_pages' or a.main_menu_submodule is not NULL and a.main_menu_submodule = 'main_pages') AND c.main_page_id=a.main_menu_add_param AND c.main_page_status='1' AND a.main_menu_type='main_page') as main_page_value_view,"
               . "a.main_menu_id FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang . " a"
               . " WHERE a.main_menu_in_menu='1' ORDER BY a.main_menu_priority";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['8'])) {
                $row_exist = intval($result->fields['8']);
            } else {
                $row_exist = 0;
            }
        }
        if (isset($row_exist) && $row_exist > 0) {
            $module_main_pages_status = $this->registry->main_class->module_is_active('main_pages');
            while (!$result->EOF) {
                $mainmenu_type = $this->registry->main_class->extracting_data($result->fields['0']);
                $mainmenu_name = $this->registry->main_class->extracting_data($result->fields['1']);
                $mainmenu_module = $this->registry->main_class->extracting_data($result->fields['2']);
                $mainmenu_submodule = $this->registry->main_class->extracting_data($result->fields['3']);
                $mainmenu_additional_param = $this->registry->main_class->extracting_data($result->fields['4']);
                $mainmenu_target = $this->registry->main_class->extracting_data($result->fields['5']);
                if (empty($mainmenu_module)) {
                    if ($mainmenu_type === 'main_page') {
                        $mainmenu_element = $mainmenu_additional_param;
                    } else {
                        $mainmenu_element = $mainmenu_submodule;
                    }
                    $mainmenu_element_type = "child";
                } elseif (empty($mainmenu_submodule)) {
                    if ($mainmenu_type === 'main_page') {
                        $mainmenu_element = $mainmenu_additional_param;
                    } else {
                        $mainmenu_element = $mainmenu_module;
                    }
                    $mainmenu_element_type = "parent";
                }
                if ($mainmenu_type != "other") {
                    $row_exist2 = false;
                    if ($mainmenu_type == "module") {
                        if (isset($result->fields['6']) && $this->registry->main_class->extracting_data($result->fields['6']) != "") {
                            $row_exist2 = 1;
                            $valueview = intval($result->fields['6']);
                        }
                    } elseif ($mainmenu_type == "main_page") {
                        if (isset($result->fields['7']) && $this->registry->main_class->extracting_data($result->fields['7']) != "" && $module_main_pages_status) {
                            $row_exist2 = 1;
                            $valueview = intval($result->fields['7']);
                        }
                    }
                    if (isset($row_exist2) && $row_exist2 > 0) {
                        if ($valueview == "0") {
                            $display_access = "yes";
                        } elseif ($valueview == "1" && ($this->registry->main_class->is_user() || $this->registry->main_class->is_admin())) {
                            $display_access = "yes";
                        } elseif ($valueview == "2" && $this->registry->main_class->is_admin()) {
                            $display_access = "yes";
                        } elseif ($valueview == "3" && (!$this->registry->main_class->is_user() || $this->registry->main_class->is_admin())) {
                            $display_access = "yes";
                        } else {
                            $display_access = "no";
                        }
                    } else {
                        $display_access = "no";
                    }
                } elseif ($mainmenu_type == "other") {
                    $display_access = "yes";
                } else {
                    $display_access = "no";
                }
                $block_content_array[] = [
                    "mainmenu_type"         => $mainmenu_type,
                    "mainmenu_name"         => $mainmenu_name,
                    "mainmenu_element"      => $mainmenu_element,
                    "mainmenu_element_type" => $mainmenu_element_type,
                    "mainmenu_target"       => $mainmenu_target,
                    "display_access"        => $display_access,
                ];
                $result->MoveNext();
            }

            return ['block_content_array' => $block_content_array];
        }

        return ['block_content_array' => 'no'];
    }
}
<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class block_menu_model extends model_base {


    private function assign($index,$value) {
        $this->registry->main_class->assign($index,$value);
    }


    private function fetch($template,$group = null) {
        return $this->registry->main_class->fetch($template,$group);
    }


    public function index() {
        $sql = "SELECT a.mainmenu_type,a.mainmenu_name,a.mainmenu_module,a.mainmenu_submodule,a.mainmenu_target,(SELECT max(b.module_valueview) FROM ".PREFIX."_modules_".$this->registry->sitelang." b WHERE (a.mainmenu_module is not NULL AND b.module_name=a.mainmenu_module OR a.mainmenu_submodule is not NULL AND b.module_name=a.mainmenu_submodule) AND b.module_status='1' AND a.mainmenu_type='module') as module_valueview, (SELECT max(c.mainpage_valueview) FROM ".PREFIX."_main_pages_".$this->registry->sitelang." c WHERE (a.mainmenu_module is not NULL AND c.mainpage_id=a.mainmenu_module OR a.mainmenu_submodule is not NULL AND c.mainpage_id=a.mainmenu_submodule) AND c.mainpage_status='1' AND a.mainmenu_type='main_page') as mainpage_valueview,a.mainmenu_id FROM ".PREFIX."_main_menu_".$this->registry->sitelang." a WHERE a.mainmenu_inmenu='1' ORDER BY a.mainmenu_priority";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['7'])) {
                $numrows = intval($result->fields['7']);
            } else {
                $numrows = 0;
            }
        }
        if(isset($numrows) and $numrows > 0) {
            $module_main_pages_status = $this->registry->main_class->module_is_active('main_pages');
            while(!$result->EOF) {
                $mainmenu_type = $this->registry->main_class->extracting_data($result->fields['0']);
                $mainmenu_name = $this->registry->main_class->extracting_data($result->fields['1']);
                $mainmenu_module = $this->registry->main_class->extracting_data($result->fields['2']);
                $mainmenu_submodule = $this->registry->main_class->extracting_data($result->fields['3']);
                $mainmenu_target = $this->registry->main_class->extracting_data($result->fields['4']);
                if(!isset($mainmenu_module) or $mainmenu_module == "") {
                    $mainmenu_element = $mainmenu_submodule;
                    $mainmenu_element_type = "child";
                } elseif(!isset($mainmenu_submodule) or $mainmenu_submodule == "") {
                    $mainmenu_element = $mainmenu_module;
                    $mainmenu_element_type = "parent";
                }
                if($mainmenu_type != "other") {
                    $numrows2 = false;
                    if($mainmenu_type == "module") {
                        if(isset($result->fields['5']) and $this->registry->main_class->extracting_data($result->fields['5']) != "") {
                            $numrows2 = 1;
                            $valueview = intval($result->fields['5']);
                        }
                    } elseif($mainmenu_type == "main_page") {
                        if(isset($result->fields['6']) and $this->registry->main_class->extracting_data($result->fields['6']) != "" and $module_main_pages_status) {
                            $numrows2 = 1;
                            $valueview = intval($result->fields['6']);
                        }
                    }
                    if(isset($numrows2) and $numrows2 > 0) {
                        if($valueview == "0") {
                            $display_access = "yes";
                        } elseif($valueview == "1" and ($this->registry->main_class->is_user_in_mainfunc() or $this->registry->main_class->is_admin_in_mainfunc())) {
                            $display_access = "yes";
                        } elseif($valueview == "2" and $this->registry->main_class->is_admin_in_mainfunc()) {
                            $display_access = "yes";
                        } elseif($valueview == "3" and (!$this->registry->main_class->is_user_in_mainfunc() or $this->registry->main_class->is_admin_in_mainfunc())) {
                            $display_access = "yes";
                        } else {
                            $display_access = "no";
                        }
                    } else {
                        $display_access = "no";
                    }
                } elseif($mainmenu_type == "other") {
                    $display_access = "yes";
                } else {
                    $display_access = "no";
                }
                $block_content_array[] = array(
                    "mainmenu_type" => $mainmenu_type,
                    "mainmenu_name" => $mainmenu_name,
                    "mainmenu_element" => $mainmenu_element,
                    "mainmenu_element_type" => $mainmenu_element_type,
                    "mainmenu_target" => $mainmenu_target,
                    "display_access" => $display_access
                );
                $result->MoveNext();
            }
            $this->assign("block_content_array",$block_content_array);
        } else {
            $this->assign("block_content_array","no");
        }
        return $block_content = $this->fetch("blocks/menu/menu.html");
    }
}

?>
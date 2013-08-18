<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_modules.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_modules extends model_base {


    public function index() {
        $this->allmodules();
        $this->display_menu();
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|show|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUMODULES'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/modules/modules.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|show|".$this->registry->language);
    }


    private function display_menu() {
        $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/modules/modules.conf");
        $sql = "SELECT module_id,module_name,module_customname FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE module_name not in ('account','feedback','main_pages') order by module_id ASC";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['1'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                $module_ordinal_number = 0;
                while(!$result->EOF) {
                    $module_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $additional_modules[] = array(
                        "module_name" => $module_name,
                        "module_customname" => $module_customname,
                        "module_ordinal_number" => $module_ordinal_number
                    );
                    if($module_ordinal_number == 5) {
                        $module_ordinal_number = 0;
                    } else {
                        $module_ordinal_number = $module_ordinal_number + 1;
                    }
                    $result->MoveNext();
                }
                $this->registry->main_class->assign("display_admin_graphic",DISPLAY_ADMIN_GRAPHIC);
                $this->registry->main_class->assign("display_menu","yes");
                $this->registry->main_class->assign("additional_modules",$additional_modules);
            } else {
                $this->registry->main_class->assign("display_menu","no");
            }
        } else {
            $this->registry->main_class->assign("display_menu","no");
        }
    }


    private function allmodules() {
        $handle = opendir('../modules');
        while(false !== ($file = readdir($handle))) {
            if((!preg_match("/[.]/i",$file))) {
                $modlist[] = "$file";
            }
        }
        closedir($handle);
        for($i = 0;$i < sizeof($modlist);$i++) {
            $modlist[$i] = trim($modlist[$i]);
            $modlist[$i] = $this->registry->main_class->format_striptags($modlist[$i]);
            $modlist[$i] = $this->registry->main_class->processing_data($modlist[$i]);
            $modlist[$i] = substr(substr($modlist[$i],0,-1),1);
            if($modlist[$i] != "") {
                $sql = "SELECT module_id FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE module_name='".$modlist[$i]."'";
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        $module_id = $result->fields['0'];
                        $module_id = intval($module_id);
                    } else {
                        $module_id = 0;
                    }
                    if($module_id == 0) {
                        $module_id = $this->db->GenID(PREFIX."_modules_id_".$this->registry->sitelang);
                        $result = $this->db->Execute("INSERT INTO ".PREFIX."_modules_".$this->registry->sitelang." VALUES ('$module_id', '$modlist[$i]', '$modlist[$i]', NULL, '0', '0')");
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } else {
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|modules|show");
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        }
        $sql = "SELECT module_name,module_id FROM ".PREFIX."_modules_".$this->registry->sitelang;
        $result2 = $this->db->Execute($sql);
        if($result2) {
            if(isset($result2->fields['1'])) {
                $numrows = intval($result2->fields['1']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                while(!$result2->EOF) {
                    $module_name = $this->registry->main_class->extracting_data($result2->fields['0']);
                    $delete = "yes";
                    $handle = opendir('../modules');
                    while(false !== ($file = readdir($handle))) {
                        $file = trim($file);
                        $file = $this->registry->main_class->format_striptags($file);
                        if($file == $module_name) {
                            $delete = "no";
                        }
                    }
                    closedir($handle);
                    if($delete == "yes") {
                        $module_name = $this->registry->main_class->processing_data($module_name);
                        if(!isset($delete_module_name)) {
                            $delete_module_name = "module_name=".$module_name."";
                        } else {
                            $delete_module_name .= " or module_name=".$module_name."";
                        }
                    }
                    $result2->MoveNext();
                }
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if(isset($delete_module_name) and trim($delete_module_name) != "") {
            $result3 = $this->db->Execute("DELETE FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE $delete_module_name");
            $num_result3 = $this->db->Affected_Rows();
            if($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            } elseif($num_result3 > 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|modules|show");
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|show|".$this->registry->language)) {
            $sql = "SELECT module_id, module_name, module_customname, module_version, module_status, module_valueview FROM ".PREFIX."_modules_".$this->registry->sitelang." ORDER BY module_name ASC";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $module_id = intval($result->fields['0']);
                        $module_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $module_version = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $module_status = intval($result->fields['4']);
                        $module_valueview = intval($result->fields['5']);
                        $module_all[] = array(
                            "module_id" => $module_id,
                            "module_name" => $module_name,
                            "module_customname" => $module_customname,
                            "module_version" => $module_version,
                            "module_status" => $module_status,
                            "module_valueview" => $module_valueview
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("module_all",$module_all);
                } else {
                    $this->registry->main_class->assign("module_all","no");
                }
            } else {
                $this->registry->main_class->assign("module_all","no");
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if(isset($error)) {
            $this->registry->main_class->assign("error",$error);
        } else {
            $this->registry->main_class->assign("error","no");
        }
    }


    public function edit_module() {
        if(isset($_GET['module_id'])) {
            $module_id = intval($_GET['module_id']);
        }
        if(!isset($_GET['module_id']) or $module_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_modules&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMODULEPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|edit|".$module_id."|".$this->registry->language)) {
            $sql = "SELECT module_id, module_name, module_customname, module_version, module_status, module_valueview FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE module_id='".$module_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $module_id = intval($result->fields['0']);
                    $module_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $module_version = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $module_status = intval($result->fields['4']);
                    $module_valueview = intval($result->fields['5']);
                    $module_all[] = array(
                        "module_id" => $module_id,
                        "module_name" => $module_name,
                        "module_customname" => $module_customname,
                        "module_version" => $module_version,
                        "module_status" => $module_status,
                        "module_valueview" => $module_valueview
                    );
                    $this->registry->main_class->assign("module_all",$module_all);
                } else {
                    $this->display_menu();
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editnotfind|".$this->registry->language)) {
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                        $this->registry->main_class->assign("module_edit_save","nosuchmodule");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editnotfind|".$this->registry->language);
                    exit();
                }
            } else {
                $this->display_menu();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                    $this->registry->main_class->assign("module_edit_save","notsave");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editsqlerror|".$this->registry->language);
                exit();
            }
        }
        $this->display_menu();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|edit|".$module_id."|".$this->registry->language)) {
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
            $this->registry->main_class->assign("module_edit_save","save");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|edit|".$module_id."|".$this->registry->language);
    }


    public function edit_module_save() {
        if(isset($_POST['module_id'])) {
            $module_id = intval($_POST['module_id']);
        }
        if(isset($_POST['module_customname'])) {
            $module_customname = trim($_POST['module_customname']);
        }
        if(isset($_POST['module_status'])) {
            $module_status = intval($_POST['module_status']);
        }
        if(isset($_POST['module_valueview'])) {
            $module_valueview = intval($_POST['module_valueview']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMODULEPAGETITLE'));
        if((!isset($_POST['module_id']) or !isset($_POST['module_customname']) or !isset($_POST['module_status']) or !isset($_POST['module_valueview'])) or ($module_id == 0 or $module_customname == "")) {
            $this->display_menu();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","noalldata");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editnotalldata|".$this->registry->language);
            exit();
        }
        $module_customname = $this->registry->main_class->format_striptags($module_customname);
        $module_customname = $this->registry->main_class->processing_data($module_customname);
        $sql = "SELECT module_id FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE module_customname=".$module_customname." AND module_id<>'".$module_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                $this->display_menu();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                    $this->registry->main_class->assign("module_edit_save","findinbase");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editfind|".$this->registry->language);
                exit();
            }
            $sql = "UPDATE ".PREFIX."_modules_".$this->registry->sitelang." SET module_customname=$module_customname, module_status='$module_status', module_valueview='$module_valueview' WHERE module_id='$module_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        }
        $this->display_menu();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","notsave");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","notsave2");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|modules|edit|".$module_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|modules|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|homepage");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","save_ok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|editok|".$this->registry->language);
        }
    }


    public function status_module() {
        if(isset($_GET['status'])) {
            $status = trim($_GET['status']);
        }
        if(isset($_GET['module_id'])) {
            $module_id = intval($_GET['module_id']);
        }
        if((!isset($_GET['module_id']) or !isset($_GET['status'])) or ($module_id == 0 or $status == "")) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_modules&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $status = $this->registry->main_class->format_striptags($status);
        if($status == "off") {
            $sql = "UPDATE ".PREFIX."_modules_".$this->registry->sitelang." SET module_status='0' WHERE module_id='".$module_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        } elseif($status == "on") {
            $sql = "UPDATE ".PREFIX."_modules_".$this->registry->sitelang." SET module_status='1' WHERE module_id='".$module_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_modules&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->display_menu();
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUMODULES'));
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","notsave");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","notsave2");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|modules|edit|".$module_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|modules|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|homepage");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|".$status."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/modules/edit_module.html");
                $this->registry->main_class->assign("module_edit_save","save_status");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|modules|".$status."|ok|".$this->registry->language);
        }
    }
}

?>
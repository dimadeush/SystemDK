<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_menu extends model_base {


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINBLOCKMAINNAME'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|show|".$this->registry->language)) {
            $sql = "SELECT a.mainmenu_id,a.mainmenu_type,a.mainmenu_name,a.mainmenu_module,a.mainmenu_submodule,a.mainmenu_target,a.mainmenu_priority,a.mainmenu_inmenu,(SELECT max(b.mainmenu_id) FROM ".PREFIX."_main_menu_".$this->registry->sitelang." b WHERE b.mainmenu_priority=a.mainmenu_priority-1) as mainmenu_id2,(SELECT max(c.mainmenu_id) FROM ".PREFIX."_main_menu_".$this->registry->sitelang." c WHERE c.mainmenu_priority=a.mainmenu_priority+1) as mainmenu_id3 FROM ".PREFIX."_main_menu_".$this->registry->sitelang." a ORDER BY a.mainmenu_priority";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows1 = intval($result->fields['0']);
                } else {
                    $numrows1 = 0;
                }
                if($numrows1 > 0) {
                    while(!$result->EOF) {
                        $mainmenu_id = intval($result->fields['0']);
                        $mainmenu_type = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $mainmenu_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $mainmenu_module = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $mainmenu_submodule = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                        $mainmenu_target = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                        $mainmenu_priority = intval($result->fields['6']);
                        $mainmenu_inmenu = intval($result->fields['7']);
                        $menu_priority1 = $mainmenu_priority - 1;
                        $menu_priority2 = $mainmenu_priority + 1;
                        $mainmenu_id2 = $result->fields['8'];
                        if(isset($mainmenu_id2) and $mainmenu_id2 != 0) {
                            $param1 = $mainmenu_id2;
                        } else {
                            $param1 = "";
                        }
                        $mainmenu_id3 = $result->fields['9'];
                        if(isset($mainmenu_id3) and $mainmenu_id3 != 0) {
                            $param2 = $mainmenu_id3;
                        } else {
                            $param2 = "";
                        }
                        $menu_all[] = array(
                            "mainmenu_id" => $mainmenu_id,
                            "mainmenu_type" => $mainmenu_type,
                            "mainmenu_name" => $mainmenu_name,
                            "mainmenu_module" => $mainmenu_module,
                            "mainmenu_submodule" => $mainmenu_submodule,
                            "mainmenu_target" => $mainmenu_target,
                            "mainmenu_priority" => $mainmenu_priority,
                            "mainmenu_inmenu" => $mainmenu_inmenu,
                            "menu_priority1" => $menu_priority1,
                            "menu_priority2" => $menu_priority2,
                            "param1" => $param1,
                            "param2" => $param2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("menu_all",$menu_all);
                } else {
                    $this->registry->main_class->assign("menu_all","no");
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|showsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("menu_update","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|showsqlerror|".$this->registry->language);
                exit();
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|show|".$this->registry->language)) {
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menu.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|show|".$this->registry->language);
    }


    public function menu_save() {
        if(isset($_GET['mainmenu_id2'])) {
            $mainmenu_id2 = intval($_GET['mainmenu_id2']);
        }
        if(isset($_GET['mainmenu_priority'])) {
            $mainmenu_priority = intval($_GET['mainmenu_priority']);
        }
        if(isset($_GET['mainmenu_priority2'])) {
            $mainmenu_priority2 = intval($_GET['mainmenu_priority2']);
        }
        if(isset($_GET['mainmenu_id'])) {
            $mainmenu_id = intval($_GET['mainmenu_id']);
        }
        if((!isset($_GET['mainmenu_id2']) or !isset($_GET['mainmenu_priority']) or !isset($_GET['mainmenu_priority2']) or !isset($_GET['mainmenu_id'])) or ($mainmenu_id2 == 0 or $mainmenu_priority == 0 or $mainmenu_priority2 == 0 or $mainmenu_id == 0)) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->db->StartTrans();
        $result1 = $this->db->Execute("UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_priority='".$mainmenu_priority."' WHERE mainmenu_id='".$mainmenu_id2."'");
        $num_result1 = $this->db->Affected_Rows();
        if($result1 === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $result2 = $this->db->Execute("UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_priority='".$mainmenu_priority2."' WHERE mainmenu_id='".$mainmenu_id."'");
        $num_result2 = $this->db->Affected_Rows();
        if($result2 === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $this->db->CompleteTrans();
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINBLOCKMAINNAME'));
        if($result1 === false or $result2 === false) {
            if($num_result1 > 0 or $num_result2 > 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
            }
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|showsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|showsqlerror|".$this->registry->language);
            exit();
        } elseif($result1 !== false and $result2 !== false and $num_result1 == 0 and $num_result2 == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|showok|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|showok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|range|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","yes");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|range|ok|".$this->registry->language);
        }
    }


    public function menu_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        if(isset($_GET['menu_id'])) {
            $menu_id = intval($_GET['menu_id']);
        }
        if((!isset($_GET['action']) or !isset($_GET['menu_id'])) or ($action == "" or $menu_id == 0)) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $action = $this->registry->main_class->format_striptags($action);
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_inmenu='1' WHERE mainmenu_id='".$menu_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->registry->main_class->assign("menu_update","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_inmenu='0' WHERE mainmenu_id='".$menu_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->registry->main_class->assign("menu_update","off");
        } elseif($action == "delete") {
            $sql = "SELECT mainmenu_priority FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_id='".$menu_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $mainmenu_priority = intval($result->fields['0']);
                } else {
                    $mainmenu_priority = 0;
                }
                if($mainmenu_priority > 0) {
                    $this->db->StartTrans();
                    $result2 = $this->db->Execute("UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_priority=mainmenu_priority-1 WHERE mainmenu_priority>'".$mainmenu_priority."'");
                    if($result2 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $sql3 = "DELETE FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_id='".$menu_id."'";
                    $result3 = $this->db->Execute($sql3);
                    $num_result = $this->db->Affected_Rows();
                    if($result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $this->db->CompleteTrans();
                    if($result2 === false or $result3 === false) {
                        $result = false;
                    }
                } else {
                    $num_result = 0;
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->registry->main_class->assign("menu_update","delete");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINBLOCKMAINNAME'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|".$action."|ok|".$this->registry->language);
        }
    }


    public function menu_select_type() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|select|".$this->registry->language)) {
            $this->registry->main_class->assign("menu_select_type","yes");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|select|".$this->registry->language);
    }


    public function menu_add() {
        if(isset($_POST['menu_type'])) {
            $menu_type = trim($_POST['menu_type']);
        }
        if(!isset($_POST['menu_type']) or $menu_type == "") {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $menu_type = $this->registry->main_class->format_striptags($menu_type);
        if($menu_type == "module") {
            $modules_dir = dir("../modules");
            while($file = $modules_dir->read()) {
                if(strpos($file,".") === 0) {
                    continue;
                } elseif(preg_match("/html/i",$file)) {
                    continue;
                } else {
                    $modules_list[] = "$file";
                }
            }
            closedir($modules_dir->handle);
            @sort($modules_list);
            for($i = 0;$i < sizeof($modules_list);$i++) {
                $modules_list[$i] = trim($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->format_striptags($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->processing_data($modules_list[$i]);
                $modules_list[$i] = substr(substr($modules_list[$i],0,-1),1);
                if($modules_list[$i] != "" and $modules_list[$i] != "main_pages") {
                    $sql = "SELECT mainmenu_id FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$modules_list[$i]."' OR mainmenu_submodule='".$modules_list[$i]."'";
                    $result = $this->db->Execute($sql);
                    if($result) {
                        if(isset($result->fields['0'])) {
                            $numrows = intval($result->fields['0']);
                        } else {
                            $numrows = 0;
                        }
                        $modules_list[$i] = $this->registry->main_class->extracting_data($modules_list[$i]);
                        if($numrows == 0 and $modules_list[$i] != "") {
                            $module_name[]['module_name'] = $modules_list[$i];
                        }
                    } else {
                        $this->registry->main_class->display_theme_adminheader();
                        $this->registry->main_class->display_theme_adminmain();
                        $this->registry->main_class->displayadmininfo();
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->registry->main_class->assign("error",$error);
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language)) {
                            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
                            $this->registry->main_class->assign("menu_update","notsave");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language);
                        exit();
                    }
                }
            }
            if(!isset($module_name) or $module_name == "") {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|addnomodules|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","nomodules");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|addnomodules|".$this->registry->language);
                exit();
            } else {
                $this->registry->main_class->assign("module_name",$module_name);
            }
            $this->registry->main_class->assign("menu_type","module");
        } elseif($menu_type == "main_page") {
            $sql = "select a.mainpage_id, a.mainpage_title from ".PREFIX."_main_pages_".$this->registry->sitelang." a where a.mainpage_id not in (select b.mainmenu_module from ".PREFIX."_main_menu_".$this->registry->sitelang." b where b.mainmenu_type='main_page' and b.mainmenu_module is not null) and a.mainpage_id not in (select c.mainmenu_submodule from ".PREFIX."_main_menu_".$this->registry->sitelang." c where c.mainmenu_type='main_page' and c.mainmenu_submodule is not null)";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $mainpage_id = intval($result->fields['0']);
                        $mainpage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $page_all[] = array("mainpage_id" => $mainpage_id,"mainpage_title" => $mainpage_title);
                        $result->MoveNext();
                    }
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language);
                exit();
            }
            if(!isset($page_all) or $page_all == "") {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|addnopages|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","nopages");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|addnopages|".$this->registry->language);
                exit();
            }
            $this->registry->main_class->assign("page_all",$page_all);
            $this->registry->main_class->assign("menu_type","main_page");
        } elseif($menu_type == "other") {
            $this->registry->main_class->assign("menu_type","other");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|add|".$menu_type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
            $this->registry->main_class->assign("menu_select_type","no");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|add|".$menu_type."|".$this->registry->language);
    }


    public function menu_add_inbase() {
        if(isset($_POST['mainmenu_name'])) {
            $mainmenu_name = trim($_POST['mainmenu_name']);
        }
        if(isset($_POST['menu_object'])) {
            $menu_object = trim($_POST['menu_object']);
        }
        if(isset($_POST['menu_type'])) {
            $menu_type = trim($_POST['menu_type']);
        }
        if(isset($_POST['mainmenu_module'])) {
            $mainmenu_module = trim($_POST['mainmenu_module']);
        }
        if(isset($_POST['mainmenu_target'])) {
            $mainmenu_target = trim($_POST['mainmenu_target']);
        }
        if(isset($_POST['mainmenu_inmenu'])) {
            $mainmenu_inmenu = intval($_POST['mainmenu_inmenu']);
        }
        if((!isset($_POST['mainmenu_name']) or !isset($_POST['menu_object'])or !isset($_POST['menu_type']) or !isset($_POST['mainmenu_module']) or !isset($_POST['mainmenu_target']) or !isset($_POST['mainmenu_inmenu'])) or ($mainmenu_name == "" or $menu_object == "" or $menu_type == "" or $mainmenu_module == "" or $mainmenu_target == "")) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
                $this->registry->main_class->assign("menu_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addnotalldata|".$this->registry->language);
            exit();
        }
        $sql = "SELECT MAX(mainmenu_priority) FROM ".PREFIX."_main_menu_".$this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if($result === false) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
                $this->registry->main_class->assign("menu_update","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language);
            exit();
        }
        if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
            $mainmenu_priority = intval($result->fields['0']);
            $mainmenu_priority = $mainmenu_priority + 1;
        } else {
            $mainmenu_priority = 1;
        }
        $mainmenu_module = $this->registry->main_class->format_striptags($mainmenu_module);
        $mainmenu_module = $this->registry->main_class->processing_data($mainmenu_module);
        $menu_object = $this->registry->main_class->format_striptags($menu_object);
        if($menu_object == "parent") {
            $mainmenu_module = "$mainmenu_module";
            $mainmenu_submodule = 'NULL';
        } elseif($menu_object == "children") {
            $mainmenu_submodule = "$mainmenu_module";
            $mainmenu_module = 'NULL';
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $menu_type = $this->registry->main_class->format_striptags($menu_type);
        $menu_type = $this->registry->main_class->processing_data($menu_type);
        $mainmenu_name = $this->registry->main_class->format_striptags($mainmenu_name);
        $mainmenu_name = $this->registry->main_class->processing_data($mainmenu_name);
        $mainmenu_target = $this->registry->main_class->format_striptags($mainmenu_target);
        $mainmenu_target = $this->registry->main_class->processing_data($mainmenu_target);
        $mainmenu_id = $this->db->GenID(PREFIX."_main_menu_id_".$this->registry->sitelang);
        $sql = "INSERT INTO ".PREFIX."_main_menu_".$this->registry->sitelang." (mainmenu_id,mainmenu_type,mainmenu_name,mainmenu_module,mainmenu_submodule,mainmenu_target,mainmenu_priority,mainmenu_inmenu)  VALUES ('$mainmenu_id',$menu_type,$mainmenu_name,$mainmenu_module,$mainmenu_submodule,$mainmenu_target,'$mainmenu_priority','$mainmenu_inmenu')";
        $result = $this->db->Execute($sql);
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMENUPAGETITLE'));
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|addok|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","add");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|addok|".$this->registry->language);
        }
    }


    public function menu_edit() {
        if(isset($_GET['menu_type'])) {
            $menu_type = trim($_GET['menu_type']);
        }
        if(isset($_GET['menu_id'])) {
            $menu_id = intval($_GET['menu_id']);
        }
        if((!isset($_GET['menu_type']) or !isset($_GET['menu_id'])) or ($menu_type == "" or $menu_id == 0)) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $sql1 = "SELECT mainmenu_id,mainmenu_type,mainmenu_name,mainmenu_module,mainmenu_submodule,mainmenu_target,mainmenu_priority,mainmenu_inmenu FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_id='".$menu_id."'";
        $result1 = $this->db->Execute($sql1);
        if($result1) {
            if(isset($result1->fields['0'])) {
                $numrows = intval($result1->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                while(!$result1->EOF) {
                    $mainmenu_id = intval($result1->fields['0']);
                    $mainmenu_type = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['1']));
                    $mainmenu_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['2']));
                    $mainmenu_module = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['3']));
                    $mainmenu_submodule = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['4']));
                    $mainmenu_target = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result1->fields['5']));
                    $mainmenu_priority = intval($result1->fields['6']);
                    $mainmenu_inmenu = intval($result1->fields['7']);
                    $menu_all[] = array(
                        "mainmenu_id" => $mainmenu_id,
                        "mainmenu_type" => $mainmenu_type,
                        "mainmenu_name" => $mainmenu_name,
                        "mainmenu_module" => $mainmenu_module,
                        "mainmenu_submodule" => $mainmenu_submodule,
                        "mainmenu_target" => $mainmenu_target,
                        "mainmenu_priority" => $mainmenu_priority,
                        "mainmenu_inmenu" => $mainmenu_inmenu
                    );
                    $result1->MoveNext();
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editnotfound|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","notfound");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editnotfound|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                $this->registry->main_class->assign("menu_update","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language);
            exit();
        }
        $menu_type = $this->registry->main_class->format_striptags($menu_type);
        if($menu_type == "module") {
            $modules_dir = dir("../modules");
            while($file = $modules_dir->read()) {
                if(strpos($file,".") === 0) {
                    continue;
                } elseif(preg_match("/html/i",$file)) {
                    continue;
                } else {
                    $modules_list[] = "$file";
                }
            }
            @closedir($modules_dir->handle);
            @sort($modules_list);
            for($i = 0;$i < sizeof($modules_list);$i++) {
                $modules_list[$i] = trim($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->format_striptags($modules_list[$i]);
                $modules_list[$i] = $this->registry->main_class->processing_data($modules_list[$i]);
                $modules_list[$i] = substr(substr($modules_list[$i],0,-1),1);
                if($modules_list[$i] != "" and $modules_list[$i] != "main_pages") {
                    $sql = "SELECT mainmenu_id FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$modules_list[$i]."' OR mainmenu_submodule='".$modules_list[$i]."'";
                    $result = $this->db->Execute($sql);
                    if($result) {
                        if(isset($result->fields['0'])) {
                            $numrows = intval($result->fields['0']);
                        } else {
                            $numrows = 0;
                        }
                        $modules_list[$i] = $this->registry->main_class->extracting_data($modules_list[$i]);
                        if(($numrows == 0 and $modules_list[$i] != "") or (intval($result->fields['0']) == $menu_id)) {
                            $module_name[]['module_name'] = $modules_list[$i];
                        }
                    } else {
                        $this->registry->main_class->display_theme_adminheader();
                        $this->registry->main_class->display_theme_adminmain();
                        $this->registry->main_class->displayadmininfo();
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->registry->main_class->assign("error",$error);
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language)) {
                            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                            $this->registry->main_class->assign("menu_update","notsave");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language);
                        exit();
                    }
                }
            }
            if(!isset($module_name) or $module_name == "") {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|editnomodules|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","nomodules");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|editnomodules|".$this->registry->language);
                exit();
            } else {
                $this->registry->main_class->assign("module_name",$module_name);
            }
            $this->registry->main_class->assign("menu_type","module");
        } elseif($menu_type == "main_page") {
            $sql = "select a.mainpage_id, a.mainpage_title from ".PREFIX."_main_pages_".$this->registry->sitelang." a where a.mainpage_id not in (select b.mainmenu_module from ".PREFIX."_main_menu_".$this->registry->sitelang." b where b.mainmenu_type='main_page' and b.mainmenu_module is not null and b.mainmenu_id <> ".$menu_id.") and a.mainpage_id not in (select c.mainmenu_submodule from ".PREFIX."_main_menu_".$this->registry->sitelang." c where c.mainmenu_type='main_page' and c.mainmenu_submodule is not null and c.mainmenu_id <> ".$menu_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $mainpage_id = intval($result->fields['0']);
                        $mainpage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $page_all[] = array("mainpage_id" => $mainpage_id,"mainpage_title" => $mainpage_title);
                        $result->MoveNext();
                    }
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if(!isset($page_all) or $page_all == "") {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|editnopages|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                    $this->registry->main_class->assign("menu_update","nopages");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|editnopages|".$this->registry->language);
                exit();
            }
            $this->registry->main_class->assign("page_all",$page_all);
            $this->registry->main_class->assign("menu_type","main_page");
        } elseif($menu_type == "other") {
            $this->registry->main_class->assign("menu_type","other");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->assign("menu_all",$menu_all);
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|edit|".$menu_type."|".$menu_id."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuedit.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|edit|".$menu_type."|".$menu_id."|".$this->registry->language);
    }


    public function menu_edit_inbase() {
        if(isset($_POST['mainmenu_id'])) {
            $mainmenu_id = intval($_POST['mainmenu_id']);
        }
        if(isset($_POST['mainmenu_name'])) {
            $mainmenu_name = trim($_POST['mainmenu_name']);
        }
        if(isset($_POST['menu_object'])) {
            $menu_object = trim($_POST['menu_object']);
        }
        if(isset($_POST['menu_type'])) {
            $menu_type = trim($_POST['menu_type']);
        }
        if(isset($_POST['mainmenu_module'])) {
            $mainmenu_module = trim($_POST['mainmenu_module']);
        }
        if(isset($_POST['mainmenu_target'])) {
            $mainmenu_target = trim($_POST['mainmenu_target']);
        }
        if(isset($_POST['mainmenu_inmenu'])) {
            $mainmenu_inmenu = intval($_POST['mainmenu_inmenu']);
        }
        if((!isset($_POST['mainmenu_id']) or !isset($_POST['mainmenu_name']) or !isset($_POST['menu_object']) or !isset($_POST['menu_type']) or !isset($_POST['mainmenu_module']) or !isset($_POST['mainmenu_target']) or !isset($_POST['mainmenu_inmenu'])) or ($mainmenu_id == 0 or $mainmenu_name == "" or $menu_object == "" or $menu_type == "" or $mainmenu_module == "" or $mainmenu_target == "")) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
                $this->registry->main_class->assign("menu_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editnotalldata|".$this->registry->language);
            exit();
        }
        $mainmenu_module = $this->registry->main_class->format_striptags($mainmenu_module);
        $mainmenu_module = $this->registry->main_class->processing_data($mainmenu_module);
        $menu_object = $this->registry->main_class->format_striptags($menu_object);
        if($menu_object == "parent") {
            $mainmenu_module = "$mainmenu_module";
            $mainmenu_submodule = 'NULL';
        } elseif($menu_object == "children") {
            $mainmenu_submodule = "$mainmenu_module";
            $mainmenu_module = 'NULL';
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $menu_type = $this->registry->main_class->format_striptags($menu_type);
        $menu_type = $this->registry->main_class->processing_data($menu_type);
        $mainmenu_name = $this->registry->main_class->format_striptags($mainmenu_name);
        $mainmenu_name = $this->registry->main_class->processing_data($mainmenu_name);
        $mainmenu_target = $this->registry->main_class->format_striptags($mainmenu_target);
        $mainmenu_target = $this->registry->main_class->processing_data($mainmenu_target);
        $sql = "UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_type=$menu_type,mainmenu_name=$mainmenu_name,mainmenu_module=$mainmenu_module,mainmenu_submodule=$mainmenu_submodule,mainmenu_target=$mainmenu_target,mainmenu_inmenu='$mainmenu_inmenu' WHERE mainmenu_id='$mainmenu_id'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMENUPAGETITLE'));
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|edit|".$menu_type."|".$mainmenu_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("menu_update","yes");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/menu/menuupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|menu|editok|".$this->registry->language);
        }
    }
}

?>
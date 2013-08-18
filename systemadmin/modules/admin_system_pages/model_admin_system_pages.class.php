<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_system_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_system_pages extends model_base {


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|show|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.systempage_id,a.systempage_author,a.systempage_name,a.systempage_title,a.systempage_lang,(SELECT count(b.systempage_id) FROM ".PREFIX."_system_pages b) as count_systempage_id,a.systempage_date FROM ".PREFIX."_system_pages a order by a.systempage_date DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['5']);
                        }
                        $systempage_date = intval($result->fields['6']);
                        $systempage_date = date("d.m.y H:i",$systempage_date);
                        $systempage_all[] = array(
                            "systempage_id" => intval($result->fields['0']),
                            "systempage_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                            "systempage_name" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                            "systempage_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3'])),
                            "systempage_lang" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4'])),
                            "systempage_date" => $systempage_date,
                            "count_systempage_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("systempage_all",$systempage_all);
                    if(isset($numrows2)) {
                        $num_pages = @ceil($numrows2 / $num_string_rows);
                    } else {
                        $num_pages = 0;
                    }
                    if($num_pages > 1) {
                        if($num_page > 1) {
                            $prevpage = $num_page - 1;
                            $this->registry->main_class->assign("prevpage",$prevpage);
                        } else {
                            $this->registry->main_class->assign("prevpage","no");
                        }
                        for($i = 1;$i < $num_pages + 1;$i++) {
                            if($i == $num_page) {
                                $html[] = array("number" => $i,"param1" => "1");
                            } else {
                                $pagelink = 5;
                                if(($i > $num_page) and ($i < $num_page + $pagelink) or ($i < $num_page) and ($i > $num_page - $pagelink)) {
                                    $html[] = array("number" => $i,"param1" => "2");
                                }
                                if(($i == $num_pages) and ($num_page < $num_pages - $pagelink)) {
                                    $html[] = array("number" => $i,"param1" => "3");
                                }
                                if(($i == 1) and ($num_page > $pagelink + 1)) {
                                    $html[] = array("number" => $i,"param1" => "4");
                                }
                            }
                        }
                        if($num_page < $num_pages) {
                            $nextpage = $num_page + 1;
                            $this->registry->main_class->assign("nextpage",$nextpage);
                        } else {
                            $this->registry->main_class->assign("nextpage","no");
                        }
                        $this->registry->main_class->assign("html",$html);
                        $this->registry->main_class->assign("totalrules",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("systempage_all","no");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|showsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUSYSTEMPAGES'));
                    $this->registry->main_class->assign("system_pagemessage","sqlerror");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|showsqlerror|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|show|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUSYSTEMPAGES'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pages.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|show|".$num_page."|".$this->registry->language);
    }


    public function systempage_add() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->assign("systempage_author",trim($this->registry->main_class->get_admin_login()));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|add|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDRULESPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pageadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|add|".$this->registry->language);
    }


    public function systempage_add_inbase() {
        if(isset($_POST['systempage_title'])) {
            $systempage_title = trim($_POST['systempage_title']);
        }
        if(isset($_POST['systempage_content'])) {
            $systempage_content = trim($_POST['systempage_content']);
        }
        if(isset($_POST['systempage_name'])) {
            $systempage_name = trim($_POST['systempage_name']);
        }
        if(isset($_POST['systempage_lang'])) {
            $systempage_lang = trim($_POST['systempage_lang']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDRULESPAGETITLE'));
        if((!isset($_POST['systempage_title'])or !isset($_POST['systempage_content']) or !isset($_POST['systempage_name']) or !isset($_POST['systempage_lang'])) or ($systempage_name == "" or $systempage_title == "" or $systempage_content == ""  or $systempage_lang == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|addnotalldata|".$this->registry->language);
            exit();
        }
        $now = $this->registry->main_class->gettime();
        $systempage_author = $this->registry->main_class->format_striptags(trim($this->registry->main_class->get_admin_login()));
        $systempage_author = $this->registry->main_class->processing_data($systempage_author,'need');
        $systempage_title = $this->registry->main_class->format_striptags($systempage_title);
        $systempage_title = $this->registry->main_class->processing_data($systempage_title);
        $systempage_content = $this->registry->main_class->processing_data($systempage_content);
        $systempage_content = substr(substr($systempage_content,0,-1),1);
        $systempage_name = $this->registry->main_class->format_striptags($systempage_name);
        $systempage_name = $this->registry->main_class->processing_data($systempage_name);
        $systempage_lang = $this->registry->main_class->format_striptags($systempage_lang);
        $systempage_lang = $this->registry->main_class->processing_data($systempage_lang);
        $sql = "SELECT count(systempage_id) FROM ".PREFIX."_system_pages WHERE systempage_name=".$systempage_name." and systempage_lang=".$systempage_lang;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|addfindsuchpage|".$this->registry->language)) {
                    $this->registry->main_class->assign("system_pagemessage","findsuchsystempage");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|addfindsuchpage|".$this->registry->language);
                exit();
            }
        }
        $systempage_id = $this->db->GenID(PREFIX."_system_pages_id");
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_system_pages (systempage_id,systempage_author,systempage_name,systempage_title,systempage_date,systempage_content,systempage_lang)  VALUES ('$systempage_id',$systempage_author,$systempage_name,$systempage_title,'$now',empty_clob(),$systempage_lang)";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $insert_result2 = $this->db->UpdateClob(PREFIX.'_system_pages','systempage_content',$systempage_content,'systempage_id='.$systempage_id);
            if($insert_result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $insert_result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $sql = "INSERT INTO ".PREFIX."_system_pages (systempage_id,systempage_author,systempage_name,systempage_title,systempage_date,systempage_content,systempage_lang)  VALUES ('$systempage_id',$systempage_author,$systempage_name,$systempage_title,'$now','$systempage_content',$systempage_lang)";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","sqlerror");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|add|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","add");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|add|ok|".$this->registry->language);
        }
    }


    public function systempage_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        if(isset($_GET['systempage_id'])) {
            $systempage_id = intval($_GET['systempage_id']);
        }
        if((!isset($_GET['action']) or !isset($_GET['systempage_id'])) or ($action == "" or $systempage_id == 0)) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $action = $this->registry->main_class->format_striptags($action);
        if($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_system_pages WHERE systempage_id='".$systempage_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->registry->main_class->assign("system_pagemessage","delete");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUSYSTEMPAGES'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","sqlerror");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|edit|".$systempage_id);
            $this->registry->main_class->systemdk_clearcache("modules|account|register|rules");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|".$action."|ok|".$this->registry->language);
        }
    }


    public function systempage_edit() {
        if(isset($_GET['systempage_id'])) {
            $systempage_id = intval($_GET['systempage_id']);
        }
        if(!isset($_GET['systempage_id']) or $systempage_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITRULESPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|edit|".$systempage_id."|".$this->registry->language)) {
            $sql = "SELECT systempage_id,systempage_author,systempage_name,systempage_title,systempage_date,systempage_content,systempage_lang FROM ".PREFIX."_system_pages WHERE systempage_id='".$systempage_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("system_pagemessage","sqlerror");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if($numrows == 0) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editnotfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("system_pagemessage","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editnotfind|".$this->registry->language);
                exit();
            } else {
                $systempage_content = $this->registry->main_class->extracting_data($result->fields['5']);
                $systempage_all[] = array(
                    "systempage_id" => intval($result->fields['0']),
                    "systempage_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                    "systempage_name" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                    "systempage_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3'])),
                    "systempage_date" => intval($result->fields['4']),
                    "systempage_content" => $systempage_content,
                    "systempage_lang" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']))
                );
                $this->registry->main_class->assign("systempage_all",$systempage_all);
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pageedit.html");
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|edit|".$systempage_id."|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|edit|".$systempage_id."|".$this->registry->language);
        }
    }


    public function systempage_edit_inbase() {
        if(isset($_POST['systempage_id'])) {
            $systempage_id = intval($_POST['systempage_id']);
        }
        if(isset($_POST['systempage_name'])) {
            $systempage_name = trim($_POST['systempage_name']);
        }
        if(isset($_POST['systempage_title'])) {
            $systempage_title = trim($_POST['systempage_title']);
        }
        if(isset($_POST['systempage_content'])) {
            $systempage_content = trim($_POST['systempage_content']);
        }
        if(isset($_POST['systempage_lang'])) {
            $systempage_lang = trim($_POST['systempage_lang']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITRULESPAGETITLE'));
        if((!isset($_POST['systempage_id']) or !isset($_POST['systempage_name']) or !isset($_POST['systempage_title']) or !isset($_POST['systempage_content']) or !isset($_POST['systempage_lang'])) or ($systempage_id == 0 or $systempage_name == "" or $systempage_title == "" or $systempage_content == "" or $systempage_lang == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editnotalldata|".$this->registry->language);
            exit();
        }
        $now = $this->registry->main_class->gettime();
        $systempage_name = $this->registry->main_class->format_striptags($systempage_name);
        $systempage_name = $this->registry->main_class->processing_data($systempage_name);
        $systempage_title = $this->registry->main_class->format_striptags($systempage_title);
        $systempage_title = $this->registry->main_class->processing_data($systempage_title);
        $systempage_content = $this->registry->main_class->processing_data($systempage_content);
        $systempage_content = substr(substr($systempage_content,0,-1),1);
        $systempage_lang = $this->registry->main_class->format_striptags($systempage_lang);
        $systempage_lang = $this->registry->main_class->processing_data($systempage_lang);
        $sql = "SELECT count(systempage_id) FROM ".PREFIX."_system_pages WHERE systempage_name=".$systempage_name." and systempage_lang=".$systempage_lang." and systempage_id!=".$systempage_id;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editfindsuchpage|".$this->registry->language)) {
                    $this->registry->main_class->assign("system_pagemessage","findsuchsystempage");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editfindsuchpage|".$this->registry->language);
                exit();
            }
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_system_pages SET systempage_name=$systempage_name,systempage_title=$systempage_title,systempage_date='$now',systempage_content=empty_clob(),systempage_lang=$systempage_lang WHERE systempage_id='$systempage_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_system_pages','systempage_content',$systempage_content,'systempage_id='.$systempage_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $sql = "UPDATE ".PREFIX."_system_pages SET systempage_name=$systempage_name,systempage_title=$systempage_title,systempage_date='$now',systempage_content='$systempage_content',systempage_lang=$systempage_lang WHERE systempage_id='$systempage_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","sqlerror");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("system_pagemessage","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|system_pages|edit|".$systempage_id);
            $this->registry->main_class->systemdk_clearcache("modules|account|register|rules");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|save|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/system_pages/system_pagemessage.html");
                $this->registry->main_class->assign("system_pagemessage","editok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|system_pages|save|ok|".$this->registry->language);
        }
    }
}

?>
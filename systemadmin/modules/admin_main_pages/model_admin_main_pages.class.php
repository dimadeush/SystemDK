<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_main_pages extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->main_pages();
    }


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
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|show|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.mainpage_id,a.mainpage_author,a.mainpage_title,a.mainpage_valueview,a.mainpage_termexpire,a.mainpage_status,(SELECT count(b.mainpage_id) FROM ".PREFIX."_main_pages_".$this->registry->sitelang." b) as count_mainpage_id FROM ".PREFIX."_main_pages_".$this->registry->sitelang." a order by a.mainpage_date DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['6']);
                        }
                        $mainpage_termexpire = intval($result->fields['4']);
                        if(!isset($mainpage_termexpire) or $mainpage_termexpire == 0) {
                            $mainpage_termexpire = "unlim";
                        } else {
                            $mainpage_termexpire = "notunlim";
                        }
                        $mainpage_all[] = array(
                            "mainpage_id" => intval($result->fields['0']),
                            "mainpage_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                            "mainpage_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                            "mainpage_valueview" => intval($result->fields['3']),
                            "mainpage_termexpire" => $mainpage_termexpire,
                            "mainpage_status" => intval($result->fields['5']),
                            "count_mainpage_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("mainpage_all",$mainpage_all);
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
                        $this->registry->main_class->assign("totalmainpages",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("mainpage_all","no");
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|showsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUPAGES'));
                    $this->registry->main_class->assign("main_pageupdate","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|showsqlerror|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|show|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUPAGES'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pages.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|show|".$num_page."|".$this->registry->language);
    }


    public function mainpage_add() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->assign("mainpage_author",trim($this->registry->main_class->get_admin_login()));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|add|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDPAGEPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|add|".$this->registry->language);
    }


    public function mainpage_add_inbase() {
        if(isset($_POST['mainpage_author'])) {
            $mainpage_author = trim($_POST['mainpage_author']);
        }
        if(isset($_POST['mainpage_title'])) {
            $mainpage_title = trim($_POST['mainpage_title']);
        }
        if(isset($_POST['mainpage_content'])) {
            $mainpage_content = trim($_POST['mainpage_content']);
        }
        if(isset($_POST['mainpage_notes'])) {
            $mainpage_notes = trim($_POST['mainpage_notes']);
        }
        if(isset($_POST['mainpage_status'])) {
            $mainpage_status = intval($_POST['mainpage_status']);
        }
        if(isset($_POST['mainpage_termexpire'])) {
            $mainpage_termexpire = intval($_POST['mainpage_termexpire']);
        }
        if(isset($_POST['mainpage_termexpireaction'])) {
            $mainpage_termexpireaction = trim($_POST['mainpage_termexpireaction']);
        }
        if(isset($_POST['mainpage_valueview'])) {
            $mainpage_valueview = intval($_POST['mainpage_valueview']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDPAGEPAGETITLE'));
        if((!isset($_POST['mainpage_author']) or !isset($_POST['mainpage_title']) or !isset($_POST['mainpage_content']) or !isset($_POST['mainpage_notes']) or !isset($_POST['mainpage_status']) or !isset($_POST['mainpage_termexpire']) or !isset($_POST['mainpage_termexpireaction']) or !isset($_POST['mainpage_valueview'])) or ($mainpage_author == "" or $mainpage_title == "" or $mainpage_content == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|addnotalldata|".$this->registry->language);
            exit();
        }
        if($mainpage_termexpire == "0") {
            $mainpage_termexpire = 'NULL';
            $mainpage_termexpireaction = 'NULL';
        } else {
            $mainpage_termexpire = $this->registry->main_class->gettime() + ($mainpage_termexpire * 86400);
            $mainpage_termexpire = "'$mainpage_termexpire'";
            $mainpage_termexpireaction = $this->registry->main_class->format_striptags($mainpage_termexpireaction);
            $mainpage_termexpireaction = $this->registry->main_class->processing_data($mainpage_termexpireaction);
        }
        if($mainpage_notes == "") {
            $mainpage_notes = 'NULL';
        } else {
            $mainpage_notes = $this->registry->main_class->processing_data($mainpage_notes);
        }
        $now = $this->registry->main_class->gettime();
        $mainpage_author = $this->registry->main_class->format_striptags($mainpage_author);
        $mainpage_author = $this->registry->main_class->processing_data($mainpage_author);
        $mainpage_title = $this->registry->main_class->format_striptags($mainpage_title);
        $mainpage_title = $this->registry->main_class->processing_data($mainpage_title);
        $mainpage_content = $this->registry->main_class->processing_data($mainpage_content);
        $mainpage_content = substr(substr($mainpage_content,0,-1),1);
        $mainpage_id = $this->db->GenID(PREFIX."_main_pages_id_".$this->registry->sitelang);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_main_pages_".$this->registry->sitelang." (mainpage_id,mainpage_author,mainpage_title,mainpage_date,mainpage_content,mainpage_notes,mainpage_valueview,mainpage_status,mainpage_termexpire,mainpage_termexpireaction)  VALUES ('$mainpage_id',$mainpage_author,$mainpage_title,'$now',empty_clob(),empty_clob(),'$mainpage_valueview','$mainpage_status',$mainpage_termexpire,$mainpage_termexpireaction)";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $insert_result2 = $this->db->UpdateClob(PREFIX.'_main_pages_'.$this->registry->sitelang,'mainpage_content',$mainpage_content,'mainpage_id='.$mainpage_id);
            if($insert_result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($mainpage_notes != 'NULL') {
                $mainpage_notes = substr(substr($mainpage_notes,0,-1),1);
                $insert_result3 = $this->db->UpdateClob(PREFIX.'_main_pages_'.$this->registry->sitelang,'mainpage_notes',$mainpage_notes,'mainpage_id='.$mainpage_id);
                if($insert_result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $insert_result3 = true;
            }
            $this->db->CompleteTrans();
            if($insert_result2 === false or $insert_result3 === false) {
                $insert_result = false;
            }
        } else {
            $sql = "INSERT INTO ".PREFIX."_main_pages_".$this->registry->sitelang." (mainpage_id,mainpage_author,mainpage_title,mainpage_date,mainpage_content,mainpage_notes,mainpage_valueview,mainpage_status,mainpage_termexpire,mainpage_termexpireaction)  VALUES ('$mainpage_id',$mainpage_author,$mainpage_title,'$now','$mainpage_content',$mainpage_notes,'$mainpage_valueview','$mainpage_status',$mainpage_termexpire,$mainpage_termexpireaction)";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|add|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","add");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|add|ok|".$this->registry->language);
        }
    }


    public function mainpage_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        } else {
            $action = "";
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $action = trim($_POST['action']);
        }
        if(isset($_GET['mainpage_id'])) {
            $mainpage_id = intval($_GET['mainpage_id']);
        } else {
            $mainpage_id = 0;
        }
        if($mainpage_id == 0 and isset($_POST['mainpage_id']) and is_array($_POST['mainpage_id']) and count($_POST['mainpage_id']) > 0) {
            $mainpage_id_is_arr = 1;
            for($i = 0,$size = count($_POST['mainpage_id']);$i < $size;++$i) {
                if($i == 0) {
                    $mainpage_id = "'".intval($_POST['mainpage_id'][$i])."'";
                } else {
                    $mainpage_id .= ",'".intval($_POST['mainpage_id'][$i])."'";
                }
            }
        } else {
            $mainpage_id_is_arr = 0;
            $mainpage_id = "'".$mainpage_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($mainpage_id) or $action == "" or $mainpage_id == "'0'") {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_main_pages_".$this->registry->sitelang." SET mainpage_status='1' WHERE mainpage_id IN (".$mainpage_id.")";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            } else {
                $num_result = $this->db->Affected_Rows();
            }
            $this->registry->main_class->assign("main_pageupdate","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_main_pages_".$this->registry->sitelang." SET mainpage_status='0' WHERE mainpage_id IN (".$mainpage_id.")";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            } else {
                $num_result = $this->db->Affected_Rows();
            }
            $this->registry->main_class->assign("main_pageupdate","off");
        } elseif($action == "delete") {
            $this->db->StartTrans();
            $sql = "DELETE FROM ".PREFIX."_main_pages_".$this->registry->sitelang." WHERE mainpage_id IN (".$mainpage_id.")";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql2 = "SELECT mainmenu_priority,mainmenu_module,mainmenu_submodule FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module IN (".$mainpage_id.") or mainmenu_submodule IN (".$mainpage_id.") order by mainmenu_priority";
            $result2 = $this->db->Execute($sql2);
            if($result2) {
                if(isset($result2->fields['0'])) {
                    $numrows = intval($result2->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $i = 0;
                    while(!$result2->EOF) {
                        $mainmenu_priority = intval($result2->fields['0'] - $i);
                        if(intval($result2->fields['1']) > 0) {
                            $mainpage_id2 = intval($result2->fields['1']);
                        } else {
                            $mainpage_id2 = intval($result2->fields['2']);
                        }
                        $result3 = $this->db->Execute("UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_priority=mainmenu_priority-1 WHERE mainmenu_priority>".$mainmenu_priority);
                        $num_result3 = $this->db->Affected_Rows();
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $sql4 = "DELETE FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$mainpage_id2."' or mainmenu_submodule='".$mainpage_id2."'";
                        $result4 = $this->db->Execute($sql4);
                        $num_result4 = $this->db->Affected_Rows();
                        if($result4 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        if($num_result4 > 0 or $num_result3 > 0) {
                            $clear = "yes";
                        }
                        $i++;
                        $result2->MoveNext();
                    }
                    if(isset($clear) and $clear == "yes") {
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
                    }
                    if(isset($error)) {
                        $result = false;
                    }
                }
            } else {
                $result = false;
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->db->CompleteTrans();
            $this->registry->main_class->assign("main_pageupdate","delete");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUPAGES'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|show");
            if($mainpage_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|edit");
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".intval($_GET['mainpage_id']));
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|".$action."|ok|".$this->registry->language);
        }
    }


    public function mainpage_edit() {
        if(isset($_GET['mainpage_id'])) {
            $mainpage_id = intval($_GET['mainpage_id']);
        }
        if(!isset($_GET['mainpage_id']) or $mainpage_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITPAGEPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".$mainpage_id."|".$this->registry->language)) {
            $sql = "SELECT mainpage_id,mainpage_author,mainpage_title,mainpage_date,mainpage_content,mainpage_readcounter,mainpage_notes,mainpage_valueview,mainpage_status,mainpage_termexpire,mainpage_termexpireaction FROM ".PREFIX."_main_pages_".$this->registry->sitelang." WHERE mainpage_id='".$mainpage_id."'";
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("main_pageupdate","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if($numrows == 0) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editnotfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("main_pageupdate","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editnotfind|".$this->registry->language);
                exit();
            } else {
                if(isset($result->fields['9']) and intval($result->fields['9']) != 0) {
                    $mainpage_termexpire = intval($result->fields['9']);
                    $mainpage_termexpire = date("d.m.y H:i",$mainpage_termexpire);
                    $mainpage_termexpireday = intval(((intval($result->fields['9']) - $this->registry->main_class->gettime()) / 3600) / 24);
                } else {
                    $mainpage_termexpire = "";
                    $mainpage_termexpireday = "";
                }
                $mainpage_content = $this->registry->main_class->extracting_data($result->fields['4']);
                $mainpage_all[] = array(
                    "mainpage_id" => intval($result->fields['0']),
                    "mainpage_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                    "mainpage_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                    "mainpage_date" => intval($result->fields['3']),
                    "mainpage_content" => $mainpage_content,
                    "mainpage_readcounter" => intval($result->fields['5']),
                    "mainpage_notes" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6'])),
                    "mainpage_valueview" => intval($result->fields['7']),
                    "mainpage_status" => intval($result->fields['8']),
                    "mainpage_termexpire" => $mainpage_termexpire,
                    "mainpage_termexpireaction" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10'])),
                    "mainpage_termexpireday" => $mainpage_termexpireday
                );
                $this->registry->main_class->assign("mainpage_all",$mainpage_all);
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageedit.html");
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".$mainpage_id."|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".$mainpage_id."|".$this->registry->language);
        }
    }


    public function mainpage_edit_inbase() {
        if(isset($_POST['mainpage_id'])) {
            $mainpage_id = intval($_POST['mainpage_id']);
        }
        if(isset($_POST['mainpage_author'])) {
            $mainpage_author = trim($_POST['mainpage_author']);
        }
        if(isset($_POST['mainpage_title'])) {
            $mainpage_title = trim($_POST['mainpage_title']);
        }
        if(isset($_POST['mainpage_content'])) {
            $mainpage_content = trim($_POST['mainpage_content']);
        }
        if(isset($_POST['mainpage_notes'])) {
            $mainpage_notes = trim($_POST['mainpage_notes']);
        }
        if(isset($_POST['mainpage_status'])) {
            $mainpage_status = intval($_POST['mainpage_status']);
        }
        if(isset($_POST['mainpage_termexpire'])) {
            $mainpage_termexpire = intval($_POST['mainpage_termexpire']);
        }
        if(isset($_POST['mainpage_termexpireaction'])) {
            $mainpage_termexpireaction = trim($_POST['mainpage_termexpireaction']);
        }
        if(isset($_POST['mainpage_valueview'])) {
            $mainpage_valueview = intval($_POST['mainpage_valueview']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITPAGEPAGETITLE'));
        if((!isset($_POST['mainpage_id']) or !isset($_POST['mainpage_author']) or !isset($_POST['mainpage_title']) or !isset($_POST['mainpage_content']) or !isset($_POST['mainpage_notes']) or !isset($_POST['mainpage_status']) or !isset($_POST['mainpage_termexpire']) or !isset($_POST['mainpage_termexpireaction']) or !isset($_POST['mainpage_valueview'])) or ($mainpage_id == 0 or $mainpage_author == "" or $mainpage_title == "" or $mainpage_content == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editnotalldata|".$this->registry->language);
            exit();
        }
        if($mainpage_termexpire == "0") {
            $mainpage_termexpire = 'NULL';
            $mainpage_termexpireaction = 'NULL';
        } else {
            $mainpage_termexpire = $this->registry->main_class->gettime() + ($mainpage_termexpire * 86400);
            $mainpage_termexpire = "'$mainpage_termexpire'";
            $mainpage_termexpireaction = $this->registry->main_class->format_striptags($mainpage_termexpireaction);
            $mainpage_termexpireaction = $this->registry->main_class->processing_data($mainpage_termexpireaction);
        }
        if($mainpage_notes == "") {
            $mainpage_notes = 'NULL';
        } else {
            $mainpage_notes = $this->registry->main_class->processing_data($mainpage_notes);
        }
        $mainpage_author = $this->registry->main_class->format_striptags($mainpage_author);
        $mainpage_author = $this->registry->main_class->processing_data($mainpage_author);
        $mainpage_title = $this->registry->main_class->format_striptags($mainpage_title);
        $mainpage_title = $this->registry->main_class->processing_data($mainpage_title);
        $mainpage_content = $this->registry->main_class->processing_data($mainpage_content);
        $mainpage_content = substr(substr($mainpage_content,0,-1),1);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_main_pages_".$this->registry->sitelang." SET mainpage_author=$mainpage_author,mainpage_title=$mainpage_title,mainpage_content=empty_clob(),mainpage_notes=empty_clob(),mainpage_status='$mainpage_status',mainpage_termexpire=$mainpage_termexpire,mainpage_termexpireaction=$mainpage_termexpireaction,mainpage_valueview='$mainpage_valueview' WHERE mainpage_id='$mainpage_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_main_pages_'.$this->registry->sitelang,'mainpage_content',$mainpage_content,'mainpage_id='.$mainpage_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($mainpage_notes != 'NULL') {
                $mainpage_notes = substr(substr($mainpage_notes,0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_main_pages_'.$this->registry->sitelang,'mainpage_notes',$mainpage_notes,'mainpage_id='.$mainpage_id);
                if($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $result3 = true;
            }
            $this->db->CompleteTrans();
            if($result2 === false or $result3 === false) {
                $result = false;
            }
        } else {
            $sql = "UPDATE ".PREFIX."_main_pages_".$this->registry->sitelang." SET mainpage_author=$mainpage_author,mainpage_title=$mainpage_title,mainpage_content='$mainpage_content',mainpage_notes=$mainpage_notes,mainpage_status='$mainpage_status',mainpage_termexpire=$mainpage_termexpire,mainpage_termexpireaction=$mainpage_termexpireaction,mainpage_valueview='$mainpage_valueview' WHERE mainpage_id='$mainpage_id'";
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
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".$mainpage_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|main_pages|show|".$mainpage_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|save|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/main_pages/main_pageupdate.html");
                $this->registry->main_class->assign("main_pageupdate","editok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|main_pages|save|ok|".$this->registry->language);
        }
    }
}

?>
<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_messages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_messages extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->messages_check();
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
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|show|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.message_id,a.message_author,a.message_title,a.message_termexpire,a.message_status,a.message_valueview,(SELECT count(b.message_id) FROM ".PREFIX."_messages_".$this->registry->sitelang." b) as count_message_id FROM ".PREFIX."_messages_".$this->registry->sitelang." a order by a.message_date DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_messages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['6']);
                        }
                        $message_termexpire = intval($result->fields['3']);
                        if(!isset($message_termexpire) or $message_termexpire == "" or $message_termexpire == 0) {
                            $message_termexpire = "unlim";
                        } else {
                            $message_termexpire = "notunlim";
                        }
                        $message_all[] = array(
                            "message_id" => intval($result->fields['0']),
                            "message_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                            "message_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                            "message_valueview" => intval($result->fields['5']),
                            "message_termexpire" => $message_termexpire,
                            "message_status" => intval($result->fields['4']),
                            "count_message_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("message_all",$message_all);
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
                        $this->registry->main_class->assign("totalmessages",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("message_all","no");
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|showsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUMESSAGES'));
                    $this->registry->main_class->assign("message_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|showsqlerror|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|show|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUMESSAGES'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/messages/messages.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|show|".$num_page."|".$this->registry->language);
    }


    public function message_add() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->assign("message_author",trim($this->registry->main_class->get_admin_login()));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|add|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMESSAGEPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_add.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|add|".$this->registry->language);
    }


    public function message_add_inbase() {
        if(isset($_POST['message_title'])) {
            $message_title = trim($_POST['message_title']);
        }
        if(isset($_POST['message_content'])) {
            $message_content = trim($_POST['message_content']);
        }
        if(isset($_POST['message_notes'])) {
            $message_notes = trim($_POST['message_notes']);
        }
        if(isset($_POST['message_status'])) {
            $message_status = intval($_POST['message_status']);
        }
        if(isset($_POST['message_termexpire'])) {
            $message_termexpire = intval($_POST['message_termexpire']);
        }
        if(isset($_POST['message_termexpireaction'])) {
            $message_termexpireaction = trim($_POST['message_termexpireaction']);
        }
        if(isset($_POST['message_valueview'])) {
            $message_valueview = intval($_POST['message_valueview']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDMESSAGEPAGETITLE'));
        if((!isset($_POST['message_title']) or !isset($_POST['message_content']) or !isset($_POST['message_notes']) or !isset($_POST['message_status']) or !isset($_POST['message_termexpire']) or !isset($_POST['message_termexpireaction']) or !isset($_POST['message_valueview'])) or ($message_title == "" or $message_content == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|addnotalldata|".$this->registry->language);
            exit();
        }
        $message_author = trim($this->registry->main_class->get_admin_login());
        if($message_termexpire == "0") {
            $message_termexpire = 'NULL';
            $message_termexpireaction = 'NULL';
        } else {
            $message_termexpire = $this->registry->main_class->gettime() + ($message_termexpire * 86400);
            $message_termexpire = "'$message_termexpire'";
            $message_termexpireaction = $this->registry->main_class->format_striptags($message_termexpireaction);
            $message_termexpireaction = $this->registry->main_class->processing_data($message_termexpireaction);
            $message_termexpireaction = "$message_termexpireaction";
        }
        if($message_notes == "") {
            $message_notes = 'NULL';
        } else {
            $message_notes = $this->registry->main_class->processing_data($message_notes);
        }
        $now = $this->registry->main_class->gettime();
        $message_author = $this->registry->main_class->format_striptags($message_author);
        $message_author = $this->registry->main_class->processing_data($message_author);
        $message_title = $this->registry->main_class->format_striptags($message_title);
        $message_title = $this->registry->main_class->processing_data($message_title);
        $message_content = $this->registry->main_class->processing_data($message_content);
        $message_content = substr(substr($message_content,0,-1),1);
        $message_id = $this->db->GenID(PREFIX."_messages_id_".$this->registry->sitelang);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_messages_".$this->registry->sitelang." (message_id,message_author,message_title,message_date,message_content,message_notes,message_valueview,message_status,message_termexpire,message_termexpireaction)  VALUES ('$message_id',$message_author,$message_title,'$now',empty_clob(),empty_clob(),'$message_valueview','$message_status',$message_termexpire,$message_termexpireaction)";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_content',$message_content,'message_id='.$message_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($message_notes != 'NULL') {
                $message_notes = substr(substr($message_notes,0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_notes',$message_notes,'message_id='.$message_id);
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
            $sql = "INSERT INTO ".PREFIX."_messages_".$this->registry->sitelang." (message_id,message_author,message_title,message_date,message_content,message_notes,message_valueview,message_status,message_termexpire,message_termexpireaction)  VALUES ('$message_id',$message_author,$message_title,'$now','$message_content',$message_notes,'$message_valueview','$message_status',$message_termexpire,$message_termexpireaction)";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|addok|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","add");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|addok|".$this->registry->language);
        }
    }


    public function message_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        } else {
            $action = "";
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $action = trim($_POST['action']);
        }
        if(isset($_GET['message_id'])) {
            $message_id = intval($_GET['message_id']);
        } else {
            $message_id = 0;
        }
        if($message_id == 0 and isset($_POST['message_id']) and is_array($_POST['message_id']) and count($_POST['message_id']) > 0) {
            $message_id_is_arr = 1;
            for($i = 0,$size = count($_POST['message_id']);$i < $size;++$i) {
                if($i == 0) {
                    $message_id = "'".intval($_POST['message_id'][$i])."'";
                } else {
                    $message_id .= ",'".intval($_POST['message_id'][$i])."'";
                }
            }
        } else {
            $message_id_is_arr = 0;
            $message_id = "'".$message_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($message_id) or $action == "" or $message_id == "'0'") {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_messages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_status='1' WHERE message_id IN (".$message_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("message_update","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_status='0' WHERE message_id IN (".$message_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("message_update","off");
        } elseif($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_messages_".$this->registry->sitelang." WHERE message_id IN (".$message_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("message_update","delete");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_messages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($result) {
            $num_result = $this->db->Affected_Rows();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUMESSAGES'));
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            if($message_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit");
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit|".intval($_GET['message_id']));
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|".$action."|ok|".$this->registry->language);
        }
    }


    public function message_edit() {
        if(isset($_GET['message_id'])) {
            $message_id = intval($_GET['message_id']);
        }
        if(!isset($_GET['message_id']) or $message_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_messages&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMESSAGEPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|edit|".$message_id."|".$this->registry->language)) {
            $sql = "SELECT message_id,message_author,message_title,message_date,message_content,message_notes,message_valueview,message_status,message_termexpire,message_termexpireaction FROM ".PREFIX."_messages_".$this->registry->sitelang." WHERE message_id='".$message_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0) {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editnotfind|".$this->registry->language)) {
                        $this->registry->main_class->assign("message_update","notfind");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editnotfind|".$this->registry->language);
                    exit();
                } else {
                    if(isset($result->fields['8']) and intval($result->fields['8']) !== "") {
                        $message_termexpire = intval($result->fields['8']);
                        $message_termexpire = date("d.m.y H:i",$message_termexpire);
                        $message_termexpireday = intval(((intval($result->fields['8']) - $this->registry->main_class->gettime()) / 3600) / 24);
                    } else {
                        $message_termexpire = "";
                        $message_termexpireday = "";
                    }
                    $message_content = $this->registry->main_class->extracting_data($result->fields['4']);
                    $message_all[] = array(
                        "message_id" => intval($result->fields['0']),
                        "message_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "message_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                        "message_date" => intval($result->fields['3']),
                        "message_content" => $message_content,
                        "message_notes" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5'])),
                        "message_valueview" => intval($result->fields['6']),
                        "message_status" => intval($result->fields['7']),
                        "message_termexpire" => $message_termexpire,
                        "message_termexpireaction" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9'])),
                        "message_termexpireday" => $message_termexpireday
                    );
                    $this->registry->main_class->assign("message_all",$message_all);
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_edit.html");
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|edit|".$message_id."|".$this->registry->language);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("message_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editsqlerror|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|edit|".$message_id."|".$this->registry->language);
        }
    }


    public function message_edit_inbase() {
        if(isset($_POST['message_id'])) {
            $message_id = intval($_POST['message_id']);
        }
        if(isset($_POST['message_author'])) {
            $message_author = trim($_POST['message_author']);
        }
        if(isset($_POST['message_title'])) {
            $message_title = trim($_POST['message_title']);
        }
        if(isset($_POST['message_content'])) {
            $message_content = trim($_POST['message_content']);
        }
        if(isset($_POST['message_notes'])) {
            $message_notes = trim($_POST['message_notes']);
        }
        if(isset($_POST['message_status'])) {
            $message_status = intval($_POST['message_status']);
        }
        if(isset($_POST['message_termexpire'])) {
            $message_termexpire = intval($_POST['message_termexpire']);
        }
        if(isset($_POST['message_termexpireaction'])) {
            $message_termexpireaction = trim($_POST['message_termexpireaction']);
        }
        if(isset($_POST['message_valueview'])) {
            $message_valueview = intval($_POST['message_valueview']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITMESSAGEPAGETITLE'));
        if((!isset($_POST['message_id']) or !isset($_POST['message_author']) or !isset($_POST['message_title']) or !isset($_POST['message_content']) or !isset($_POST['message_notes']) or !isset($_POST['message_status']) or !isset($_POST['message_termexpire']) or !isset($_POST['message_termexpireaction']) or !isset($_POST['message_valueview'])) or ($message_id == 0 or $message_author == "" or $message_title == "" or $message_content == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("main_pageupdate","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editnotalldata|".$this->registry->language);
            exit();
        }
        if($message_termexpire == "0") {
            $message_termexpire = 'NULL';
            $message_termexpireaction = 'NULL';
        } else {
            $message_termexpire = $this->registry->main_class->gettime() + ($message_termexpire * 86400);
            $message_termexpire = "'$message_termexpire'";
            $message_termexpireaction = $this->registry->main_class->format_striptags($message_termexpireaction);
            $message_termexpireaction = $this->registry->main_class->processing_data($message_termexpireaction);
        }
        if($message_notes == "") {
            $message_notes = 'NULL';
        } else {
            $message_notes = $this->registry->main_class->processing_data($message_notes);
        }
        $message_author = $this->registry->main_class->format_striptags($message_author);
        $message_author = $this->registry->main_class->processing_data($message_author);
        $message_title = $this->registry->main_class->format_striptags($message_title);
        $message_title = $this->registry->main_class->processing_data($message_title);
        $message_content = $this->registry->main_class->processing_data($message_content);
        $message_content = substr(substr($message_content,0,-1),1);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_author=$message_author,message_title=$message_title,message_content=empty_clob(),message_notes=empty_clob(),message_status='$message_status',message_termexpire=$message_termexpire,message_termexpireaction=$message_termexpireaction,message_valueview='$message_valueview' WHERE message_id='$message_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_content',$message_content,'message_id='.$message_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($message_notes != 'NULL') {
                $message_notes = substr(substr($message_notes,0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_notes',$message_notes,'message_id='.$message_id);
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
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_author=$message_author,message_title=$message_title,message_content='$message_content',message_notes=$message_notes,message_status='$message_status',message_termexpire=$message_termexpire,message_termexpireaction=$message_termexpireaction,message_valueview='$message_valueview' WHERE message_id='$message_id'";
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
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("message_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit|".$message_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/messages/message_update.html");
                $this->registry->main_class->assign("message_update","editok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|messages|editok|".$this->registry->language);
        }
    }
}

?>
<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_guestbook extends model_base {


    public function index() {
        if(isset($_GET['pend']) and intval($_GET['pend']) != 0 and intval($_GET['pend']) == 1) {
            $temp = "pending";
            $where1 = "a.post_status='0'";
            $where2 = "b.post_status='0'";
            $sql_order = "ASC";
        } else {
            $temp = "current";
            $where1 = "a.post_status='1' or a.post_status='2'";
            $where2 = "b.post_status='1' or b.post_status='2'";
            $sql_order = "DESC";
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|show".$temp."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.post_id,a.post_user_name,a.post_user_email,a.post_user_id,a.post_user_ip,a.post_date,a.post_status,a.post_unswer_date,(SELECT count(*) FROM ".PREFIX."_guestbook b WHERE ".$where2.") as count_posts FROM ".PREFIX."_guestbook a WHERE ".$where1." order by a.post_date ".$sql_order;
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if($temp == "current") {
                        header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_guestbook&func=index&pend=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['8']);
                        }
                        $post_id = intval($result->fields['0']);
                        $post_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $post_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $post_user_id = intval($result->fields['3']);
                        if($post_user_id == 0) {
                            $post_user_id = "no";
                        }
                        $post_user_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                        $post_date = date("d.m.y H:i",$this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5'])));
                        $post_status = intval($result->fields['6']);
                        $post_unswer_date = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                        if($post_unswer_date == "") {
                            $post_unswer_date = "no";
                        } else {
                            $post_unswer_date = date("d.m.y H:i",$post_unswer_date);
                        }
                        $posts_all[] = array(
                            "post_id" => $post_id,
                            "post_user_name" => $post_user_name,
                            "post_user_email" => $post_user_email,
                            "post_user_id" => $post_user_id,
                            "post_user_ip" => $post_user_ip,
                            "post_date" => $post_date,
                            "post_status" => $post_status,
                            "post_unswer_date" => $post_unswer_date
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("posts_all",$posts_all);
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
                        $this->registry->main_class->assign("totalposts",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("posts_all","no");
                    $this->registry->main_class->assign("html","no");
                }
                require_once('../modules/guestbook/guestbook_config.inc');
                if(isset($systemdk_guestbook_confirm_need) and $systemdk_guestbook_confirm_need == "yes" and $temp == "current") {
                    $this->registry->main_class->assign("systemdk_guestbook_confirm_need","yes");
                } else {
                    $this->registry->main_class->assign("systemdk_guestbook_confirm_need","no");
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|showsqlerror".$temp."|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                    if($temp == "current") {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKTITLE'));
                    } else {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKPENDLIST'));
                    }
                    $this->registry->main_class->assign("guestbook_message","sqlerror");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|showsqlerror".$temp."|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|show".$temp."|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
            if($temp == "current") {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKTITLE'));
                $this->registry->main_class->assign("adminmodules_guestbook_pending","no");
            } else {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKPENDLIST'));
                $this->registry->main_class->assign("adminmodules_guestbook_pending","yes");
            }
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_posts.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|show".$temp."|".$num_page."|".$this->registry->language);
    }


    public function guestbook_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        } else {
            $action = "";
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $action = trim($_POST['action']);
        }
        if(isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
        } else {
            $post_id = 0;
        }
        if($post_id == 0 and isset($_POST['post_id']) and is_array($_POST['post_id']) and count($_POST['post_id']) > 0) {
            $post_id_is_arr = 1;
            for($i = 0,$size = count($_POST['post_id']);$i < $size;++$i) {
                if($i == 0) {
                    $post_id = "'".intval($_POST['post_id'][$i])."'";
                } else {
                    $post_id .= ",'".intval($_POST['post_id'][$i])."'";
                }
            }
        } else {
            $post_id_is_arr = 0;
            $post_id = "'".$post_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($post_id) or $action == "" or $post_id == "'0'") {
            $this->registry->main_class->database_close();
            if($action == "add" or $action == "deletepend") {
                header("Location: index.php?path=admin_guestbook&func=index&pend=1&lang=".$this->registry->sitelang);
            } else {
                header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
            }
            exit();
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_guestbook SET post_status = '1' WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("guestbook_message","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_guestbook SET post_status = '2' WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("guestbook_message","off");
        } elseif($action == "delete" or $action == "deletepend") {
            $sql = "DELETE FROM ".PREFIX."_guestbook WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("guestbook_message",$action);
        } elseif($action == "add") {
            $sql = "UPDATE ".PREFIX."_guestbook SET post_status = '1' WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("guestbook_message","add");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK'));
                $this->registry->main_class->assign("guestbook_message","sqlerror");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK'));
                $this->registry->main_class->assign("guestbook_message","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|statusok|".$this->registry->language);
            exit();
        } else {
            if($action != "off" and $action != "on" and $action != "delete") {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showpending");
                if($post_id_is_arr == 1) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editpending");
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editpending|".intval($_GET['post_id']));
                }
            }
            if($action != "deletepend") {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
                $this->registry->main_class->systemdk_clearcache("modules|guestbook|display");
                if($post_id_is_arr == 1) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editcurrent");
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editcurrent|".intval($_GET['post_id']));
                }
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK'));
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|".$action."|ok|".$this->registry->language);
        }
    }


    public function guestbook_edit() {
        if(isset($_GET['pend']) and intval($_GET['pend']) != 0 and intval($_GET['pend']) == 1) {
            $temp = "pending";
            $where1 = "post_status='0'";
        } else {
            $temp = "current";
            $where1 = "(post_status='1' or post_status='2')";
        }
        if(isset($_GET['post_id'])) {
            $post_id = intval($_GET['post_id']);
        }
        if(!isset($_GET['post_id']) or $post_id == 0) {
            $this->registry->main_class->database_close();
            if($temp = "current") {
                header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
            } else {
                header("Location: index.php?path=admin_guestbook&func=index&pend=1&lang=".$this->registry->sitelang);
            }
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|edit".$temp."|".$post_id."|".$this->registry->language)) {
            $sql = "SELECT post_id,post_user_name,post_user_country,post_user_city,post_user_telephone,post_show_telephone,post_user_email,post_show_email,post_user_id,post_user_ip,post_date,post_text,post_status,post_unswer,post_unswer_date FROM ".PREFIX."_guestbook WHERE ".$where1." and post_id = ".$post_id;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $post_id = intval($result->fields['0']);
                    $post_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $post_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $post_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $post_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $post_show_telephone = intval($result->fields['5']);
                    $post_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                    $post_show_email = intval($result->fields['7']);
                    $post_user_id = intval($result->fields['8']);
                    if($post_user_id == 0) {
                        $post_user_id = "no";
                    }
                    $post_user_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                    $post_date = intval($result->fields['10']);
                    $post_date2 = date("d.m.y",$post_date);
                    $post_hour = date("H",$post_date);
                    $post_minute = date("i",$post_date);
                    $post_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                    $post_status = intval($result->fields['12']);
                    $post_unswer = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                    $post_unswer_date = intval($result->fields['14']);
                    if($post_unswer_date == 0) {
                        $post_unswer_date2 = "no";
                        $post_unswer_hour = "no";
                        $post_unswer_minute = "no";
                    } else {
                        $post_unswer_date2 = date("d.m.y",$post_unswer_date);
                        $post_unswer_hour = date("H",$post_unswer_date);
                        $post_unswer_minute = date("i",$post_unswer_date);
                    }
                    $posts_all[] = array(
                        "post_id" => $post_id,
                        "post_user_name" => $post_user_name,
                        "post_user_country" => $post_user_country,
                        "post_user_city" => $post_user_city,
                        "post_user_telephone" => $post_user_telephone,
                        "post_show_telephone" => $post_show_telephone,
                        "post_user_email" => $post_user_email,
                        "post_show_email" => $post_show_email,
                        "post_user_id" => $post_user_id,
                        "post_user_ip" => $post_user_ip,
                        "post_date" => $post_date2,
                        "post_hour" => $post_hour,
                        "post_minute" => $post_minute,
                        "post_text" => $post_text,
                        "post_status" => $post_status,
                        "post_unswer" => $post_unswer,
                        "post_unswer_date" => $post_unswer_date2,
                        "post_unswer_hour" => $post_unswer_hour,
                        "post_unswer_minute" => $post_unswer_minute
                    );
                    $this->registry->main_class->assign("posts_all",$posts_all);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                    $this->registry->main_class->assign("guestbook_message","sqlerror");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if(!isset($posts_all)) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editnotfind|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                    $this->registry->main_class->assign("guestbook_message","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editnotfind|".$this->registry->language);
                exit();
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|edit".$temp."|".$post_id."|".$this->registry->language)) {
            $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
            if($temp == "current") {
                $this->registry->main_class->assign("adminmodules_guestbook_pending","no");
            } else {
                $this->registry->main_class->assign("adminmodules_guestbook_pending","yes");
            }
            $this->registry->main_class->assign("include_jquery","yes");
            $this->registry->main_class->assign("include_jquery_data","dateinput");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_edit.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|edit".$temp."|".$post_id."|".$this->registry->language);
    }


    public function guestbook_edit_inbase() {
        if(isset($_POST['post_id'])) {
            $post_id = intval($_POST['post_id']);
        }
        if(isset($_POST['post_user_name'])) {
            $post_user_name = trim($_POST['post_user_name']);
        }
        if(isset($_POST['post_user_country'])) {
            $post_user_country = trim($_POST['post_user_country']);
        }
        if(isset($_POST['post_user_city'])) {
            $post_user_city = trim($_POST['post_user_city']);
        }
        if(isset($_POST['post_user_telephone'])) {
            $post_user_telephone = trim($_POST['post_user_telephone']);
        }
        if(isset($_POST['post_show_telephone'])) {
            $post_show_telephone = intval($_POST['post_show_telephone']);
        } else {
            $post_show_telephone = 1;
        }
        if(isset($_POST['post_user_email'])) {
            $post_user_email = trim($_POST['post_user_email']);
        }
        if(isset($_POST['post_show_email'])) {
            $post_show_email = intval($_POST['post_show_email']);
        } else {
            $post_show_email = 1;
        }
        if(isset($_POST['post_date'])) {
            $post_date = trim($_POST['post_date']);
        }
        if(isset($_POST['post_hour'])) {
            $post_hour = intval($_POST['post_hour']);
        } else {
            $post_hour = 0;
        }
        if(isset($_POST['post_minute'])) {
            $post_minute = intval($_POST['post_minute']);
        } else {
            $post_minute = 0;
        }
        if(isset($_POST['post_text'])) {
            $post_text = trim($_POST['post_text']);
        }
        if(isset($_POST['post_status'])) {
            $post_status = intval($_POST['post_status']);
        }
        if(isset($_POST['post_unswer'])) {
            $post_unswer = trim($_POST['post_unswer']);
        }
        if(isset($_POST['post_unswer_date'])) {
            $post_unswer_date = trim($_POST['post_unswer_date']);
        }
        if(isset($_POST['post_unswer_hour'])) {
            $post_unswer_hour = intval($_POST['post_unswer_hour']);
        } else {
            $post_unswer_hour = 0;
        }
        if(isset($_POST['post_unswer_minute'])) {
            $post_unswer_minute = intval($_POST['post_unswer_minute']);
        } else {
            $post_unswer_minute = 0;
        }
        if(isset($_POST['pend'])) {
            $pend = intval($_POST['pend']);
        } else {
            $pend = 0;
        }
        if($pend == 1) {
            $temp = "pending";
        } else {
            $temp = "current";
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if((!isset($_POST['post_id']) or !isset($_POST['post_user_name']) or !isset($_POST['post_user_country']) or !isset($_POST['post_user_city']) or !isset($_POST['post_user_telephone']) or !isset($_POST['post_user_email']) or !isset($_POST['post_date']) or !isset($_POST['post_hour']) or !isset($_POST['post_minute']) or !isset($_POST['post_text']) or !isset($_POST['post_status']) or !isset($_POST['post_unswer'])) or ($post_id == 0 or $post_user_name == "" or $post_user_email == "" or $post_date == "" or $post_text == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                $this->registry->main_class->assign("guestbook_message","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editnotalldata|".$this->registry->language);
            exit();
        }
        $post_user_name = $this->registry->main_class->format_striptags($post_user_name);
        $post_user_country = $this->registry->main_class->format_striptags($post_user_country);
        $post_user_city = $this->registry->main_class->format_striptags($post_user_city);
        $post_user_telephone = $this->registry->main_class->format_striptags($post_user_telephone);
        $post_user_email = $this->registry->main_class->format_striptags($post_user_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($post_user_email))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editincorrectemail|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                $this->registry->main_class->assign("guestbook_message","incorrectemail");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editincorrectemail|".$this->registry->language);
            exit();
        }
        $post_date = $this->registry->main_class->format_striptags($post_date);
        $post_date = $post_date.".".$post_hour.".".$post_minute;
        $post_date = $this->registry->main_class->timetounix($post_date);
        $post_text = $this->registry->main_class->format_striptags($post_text);
        $post_unswer = $this->registry->main_class->format_striptags($post_unswer);
        $post_unswer_date = $this->registry->main_class->format_striptags($post_unswer_date);
        if($post_unswer !== "") {
            if($post_unswer_date !== "no") {
                $post_unswer_date = $post_unswer_date.".".$post_unswer_hour.".".$post_unswer_minute;
                $post_unswer_date = "'".$this->registry->main_class->timetounix($post_unswer_date)."'";
            } else {
                $post_unswer_date = "'".$this->registry->main_class->gettime()."'";
            }
            $post_unswer = $this->registry->main_class->processing_data($post_unswer);
        } else {
            $post_unswer_date = 'NULL';
            $post_unswer = 'NULL';
        }
        $post_user_name = $this->registry->main_class->processing_data($post_user_name);
        $post_user_country = $this->registry->main_class->processing_data($post_user_country);
        $post_user_city = $this->registry->main_class->processing_data($post_user_city);
        $post_user_telephone = $this->registry->main_class->processing_data($post_user_telephone);
        $post_user_email = $this->registry->main_class->processing_data($post_user_email);
        $post_text = $this->registry->main_class->processing_data($post_text);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_guestbook SET post_user_name = ".$post_user_name.",post_user_country = ".$post_user_country.",post_user_city = ".$post_user_city.",post_user_telephone = ".$post_user_telephone.",post_show_telephone = '".$post_show_telephone."',post_user_email = ".$post_user_email.",post_show_email = '".$post_show_email."',post_date = '".$post_date."',post_text = empty_clob(),post_status = '".$post_status."',post_unswer = empty_clob(),post_unswer_date = ".$post_unswer_date." WHERE post_id = '".$post_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $post_text = substr(substr($post_text,0,-1),1);
            $result2 = $this->db->UpdateClob(PREFIX.'_guestbook','post_text',$post_text,'post_id='.$post_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($post_unswer != 'NULL') {
                $post_unswer = substr(substr($post_unswer,0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_guestbook','post_unswer',$post_unswer,'post_id='.$post_id);
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
            $sql = "UPDATE ".PREFIX."_guestbook SET post_user_name = ".$post_user_name.",post_user_country = ".$post_user_country.",post_user_city = ".$post_user_city.",post_user_telephone = ".$post_user_telephone.",post_show_telephone = '".$post_show_telephone."',post_user_email = ".$post_user_email.",post_show_email = '".$post_show_email."',post_date = '".$post_date."',post_text = ".$post_text.",post_status = '".$post_status."',post_unswer = ".$post_unswer.",post_unswer_date = ".$post_unswer_date." WHERE post_id = '".$post_id."'";
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
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                $this->registry->main_class->assign("guestbook_message","sqlerror");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editok|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                $this->registry->main_class->assign("guestbook_message","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|editok|".$this->registry->language);
            exit();
        } else {
            if($pend == 1) {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showpending");
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editpending|".$post_id);
            }
            if(($pend == 1 and $post_status > 0) or $pend == 0) {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
                $this->registry->main_class->systemdk_clearcache("modules|guestbook|display");
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editcurrent|".$post_id);
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|editok".$temp."|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKEDITTITLE'));
                $this->registry->main_class->assign("guestbook_message","editok".$temp);
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|editok".$temp."|".$this->registry->language);
        }
    }


    function guestbook_config() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|config|".$this->registry->language)) {
            $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKCONFIGPAGETITLE'));
            require_once('../modules/guestbook/guestbook_config.inc');
            $this->registry->main_class->assign("systemdk_guestbook_metakeywords",trim(SYSTEMDK_GUESTBOOK_METAKEYWORDS));
            $this->registry->main_class->assign("systemdk_guestbook_metadescription",trim(SYSTEMDK_GUESTBOOK_METADESCRIPTION));
            $this->registry->main_class->assign("systemdk_guestbook_storynum",intval(SYSTEMDK_GUESTBOOK_STORYNUM));
            $this->registry->main_class->assign("systemdk_guestbook_antispam_ipaddr",trim(SYSTEMDK_GUESTBOOK_ANTISPAM_IPADDR));
            $this->registry->main_class->assign("systemdk_guestbook_antispam_num",intval(SYSTEMDK_GUESTBOOK_ANTISPAM_NUM));
            $this->registry->main_class->assign("systemdk_guestbook_confirm_need",trim(SYSTEMDK_GUESTBOOK_CONFIRM_NEED));
            $this->registry->main_class->assign("systemdk_guestbook_sortorder",trim(SYSTEMDK_GUESTBOOK_SORTORDER));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_config.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|config|".$this->registry->language);
    }


    function guestbook_config_save() {
        if(isset($_POST['systemdk_guestbook_metakeywords'])) {
            $systemdk_guestbook_metakeywords = trim($_POST['systemdk_guestbook_metakeywords']);
        }
        if(isset($_POST['systemdk_guestbook_metadescription'])) {
            $systemdk_guestbook_metadescription = trim($_POST['systemdk_guestbook_metadescription']);
        }
        if(isset($_POST['systemdk_guestbook_storynum'])) {
            $systemdk_guestbook_storynum = intval($_POST['systemdk_guestbook_storynum']);
        }
        if(isset($_POST['systemdk_guestbook_antispam_ipaddr'])) {
            $systemdk_guestbook_antispam_ipaddr = trim($_POST['systemdk_guestbook_antispam_ipaddr']);
        }
        if(isset($_POST['systemdk_guestbook_antispam_num'])) {
            $systemdk_guestbook_antispam_num = intval($_POST['systemdk_guestbook_antispam_num']);
        }
        if(isset($_POST['systemdk_guestbook_confirm_need'])) {
            $systemdk_guestbook_confirm_need = trim($_POST['systemdk_guestbook_confirm_need']);
        }
        if(isset($_POST['systemdk_guestbook_sortorder'])) {
            $systemdk_guestbook_sortorder = trim($_POST['systemdk_guestbook_sortorder']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if((!isset($_POST['systemdk_guestbook_metakeywords']) or !isset($_POST['systemdk_guestbook_metadescription']) or !isset($_POST['systemdk_guestbook_storynum']) or !isset($_POST['systemdk_guestbook_antispam_ipaddr']) or !isset($_POST['systemdk_guestbook_antispam_num']) or !isset($_POST['systemdk_guestbook_confirm_need']) or !isset($_POST['systemdk_guestbook_sortorder'])) or ($systemdk_guestbook_storynum == 0 or $systemdk_guestbook_antispam_ipaddr == "" or $systemdk_guestbook_antispam_num == 0 or $systemdk_guestbook_confirm_need == "" or $systemdk_guestbook_sortorder == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|confnotalldata|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKCONFIGPAGETITLE'));
                $this->registry->main_class->assign("guestbook_message","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|confnotalldata|".$this->registry->language);
            exit();
        }
        $systemdk_guestbook_metakeywords = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_guestbook_metakeywords);
        $systemdk_guestbook_metadescription = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_guestbook_metadescription);
        $systemdk_guestbook_antispam_ipaddr = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_guestbook_antispam_ipaddr);
        $systemdk_guestbook_confirm_need = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_guestbook_confirm_need);
        @chmod("../modules/guestbook/guestbook_config.inc",0777);
        $file = @fopen("../modules/guestbook/guestbook_config.inc","w");
        if(!$file) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|confnofile|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKCONFIGPAGETITLE'));
                $this->registry->main_class->assign("guestbook_message","nofile");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|confnofile|".$this->registry->language);
            exit();
        }
        $content = "<?php\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_METAKEYWORDS","'.$systemdk_guestbook_metakeywords."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_METADESCRIPTION","'.$systemdk_guestbook_metadescription."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_STORYNUM",'.$systemdk_guestbook_storynum.");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_ANTISPAM_IPADDR","'.$systemdk_guestbook_antispam_ipaddr."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_ANTISPAM_NUM",'.$systemdk_guestbook_antispam_num.");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_CONFIRM_NEED","'.$systemdk_guestbook_confirm_need."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_SORTORDER","'.$systemdk_guestbook_sortorder."\");\n";
        $content .= "?>\n";
        $writefile = @fwrite($file,$content);
        if(!$writefile) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|confnowrite|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKCONFIGPAGETITLE'));
                $this->registry->main_class->assign("guestbook_message","nowrite");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|error|confnowrite|".$this->registry->language);
            exit();
        }
        $this->registry->main_class->systemdk_clearcache("modules|guestbook");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|config");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|configok|".$this->registry->language)) {
            $this->registry->main_class->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOKCONFIGPAGETITLE'));
            $this->registry->main_class->assign("guestbook_message","writeok");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/guestbook/guestbook_messages.html");
        }
        @fclose($file);
        @chmod("../modules/guestbook/guestbook_config.inc",0604);
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|configok|".$this->registry->language);
    }
}

?>
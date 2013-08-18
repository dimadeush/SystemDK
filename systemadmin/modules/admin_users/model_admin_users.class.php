<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_users.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_users extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->modules_account_autodelete();
    }


    public function index() {
        if(isset($_GET['temp']) and intval($_GET['temp']) != 0 and intval($_GET['temp']) == 1) {
            $temp = "temp";
        } else {
            $temp = "";
        }
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
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|show".$temp."|".$num_page."|".$this->registry->language)) {
            if($temp != "temp") {
                $sql = "SELECT a.user_id,a.user_name,a.user_surname,a.user_login,a.user_rights,a.user_email,a.user_active,a.user_regdate,(SELECT count(b.user_id) FROM ".PREFIX."_users b) as count_user_id FROM ".PREFIX."_users a order by a.user_id DESC";
            } else {
                $sql = "SELECT a.user_id,a.user_name,a.user_surname,a.user_login,a.user_rights,a.user_email,null,a.user_regdate,(SELECT count(b.user_id) FROM ".PREFIX."_users_temp b) as count_user_id FROM ".PREFIX."_users_temp a order by a.user_id DESC";
            }
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if($temp != "temp") {
                        header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_users&func=index&temp=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['8']);
                        }
                        $user_id = intval($result->fields['0']);
                        $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                        $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                        $user_active = intval($result->fields['6']);
                        $user_regdate = intval($result->fields['7']);
                        $user_regdate = date("d.m.y H:i",$user_regdate);
                        $user_all[] = array(
                            "user_id" => $user_id,
                            "user_name" => $user_name,
                            "user_surname" => $user_surname,
                            "user_email" => $user_email,
                            "user_login" => $user_login,
                            "user_rights" => $user_rights,
                            "user_active" => $user_active,
                            "user_regdate" => $user_regdate,
                            "count_user_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("user_all",$user_all);
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
                        $this->registry->main_class->assign("totalusers",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("user_all","no");
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|showsqlerror".$temp."|".$this->registry->language)) {
                    if($temp != "temp") {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUUSERS'));
                    } else {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONTEMPUSERSPAGETITLE'));
                    }
                    $this->registry->main_class->assign("user_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|showsqlerror".$temp."|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|show".$temp."|".$num_page."|".$this->registry->language)) {
            if($temp != "temp") {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUUSERS'));
                $this->registry->main_class->assign("adminmodules_users_temp","no");
            } else {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONTEMPUSERSPAGETITLE'));
                $this->registry->main_class->assign("adminmodules_users_temp","yes");
            }
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/users/users.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|show".$temp."|".$num_page."|".$this->registry->language);
    }


    public function user_add() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|add|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDUSERPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/users/useradd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|add|".$this->registry->language);
    }


    public function user_add_inbase() {
        if(isset($_POST['user_add_name'])) {
            $user_add_name = trim($_POST['user_add_name']);
        }
        if(isset($_POST['user_add_surname'])) {
            $user_add_surname = trim($_POST['user_add_surname']);
        }
        if(isset($_POST['user_add_website'])) {
            $user_add_website = trim($_POST['user_add_website']);
        }
        if(isset($_POST['user_add_email'])) {
            $user_add_email = trim($_POST['user_add_email']);
        }
        if(isset($_POST['user_add_login'])) {
            $user_add_login = trim($_POST['user_add_login']);
        }
        if(isset($_POST['user_add_password'])) {
            $user_add_password = trim($_POST['user_add_password']);
        }
        if(isset($_POST['user_add_password2'])) {
            $user_add_password2 = trim($_POST['user_add_password2']);
        }
        if(isset($_POST['user_add_country'])) {
            $user_add_country = trim($_POST['user_add_country']);
        }
        if(isset($_POST['user_add_city'])) {
            $user_add_city = trim($_POST['user_add_city']);
        }
        if(isset($_POST['user_add_address'])) {
            $user_add_address = trim($_POST['user_add_address']);
        }
        if(isset($_POST['user_add_telephone'])) {
            $user_add_telephone = trim($_POST['user_add_telephone']);
        }
        if(isset($_POST['user_add_icq'])) {
            $user_add_icq = trim($_POST['user_add_icq']);
        }
        if(isset($_POST['user_add_gmt'])) {
            $user_add_gmt = trim($_POST['user_add_gmt']);
        }
        if(isset($_POST['user_add_notes'])) {
            $user_add_notes = trim($_POST['user_add_notes']);
        }
        if(isset($_POST['user_add_active'])) {
            $user_add_active = intval($_POST['user_add_active']);
        }
        if(isset($_POST['user_add_activenotes'])) {
            $user_add_activenotes = trim($_POST['user_add_activenotes']);
        }
        if(isset($_POST['user_add_viewemail'])) {
            $user_add_viewemail = intval($_POST['user_add_viewemail']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDUSERPAGETITLE'));
        if((!isset($_POST['user_add_name']) or !isset($_POST['user_add_surname'])or !isset($_POST['user_add_website']) or !isset($_POST['user_add_email']) or !isset($_POST['user_add_login']) or !isset($_POST['user_add_password']) or !isset($_POST['user_add_password2']) or !isset($_POST['user_add_country']) or !isset($_POST['user_add_city']) or !isset($_POST['user_add_address']) or !isset($_POST['user_add_telephone']) or !isset($_POST['user_add_icq']) or !isset($_POST['user_add_gmt']) or !isset($_POST['user_add_notes']) or !isset($_POST['user_add_active']) or !isset($_POST['user_add_activenotes']) or !isset($_POST['user_add_viewemail'])) or ($user_add_email == "" or $user_add_login == "" or $user_add_password == "" or $user_add_password2 == "" or $user_add_gmt == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addnotalldata|".$this->registry->language);
            exit();
        }
        if($user_add_name == "") {
            $user_add_name = 'NULL';
        } else {
            $user_add_name = $this->registry->main_class->format_striptags($user_add_name);
            $user_add_name = $this->registry->main_class->processing_data($user_add_name);
        }
        if($user_add_surname == "") {
            $user_add_surname = 'NULL';
        } else {
            $user_add_surname = $this->registry->main_class->format_striptags($user_add_surname);
            $user_add_surname = $this->registry->main_class->processing_data($user_add_surname);
        }
        if($user_add_website == "") {
            $user_add_website = 'NULL';
        } else {
            $user_add_website = $this->registry->main_class->format_striptags($user_add_website);
            $user_add_website = $this->registry->main_class->processing_data($user_add_website);
        }
        if($user_add_country == "") {
            $user_add_country = 'NULL';
        } else {
            $user_add_country = $this->registry->main_class->format_striptags($user_add_country);
            $user_add_country = $this->registry->main_class->processing_data($user_add_country);
        }
        if($user_add_city == "") {
            $user_add_city = 'NULL';
        } else {
            $user_add_city = $this->registry->main_class->format_striptags($user_add_city);
            $user_add_city = $this->registry->main_class->processing_data($user_add_city);
        }
        if($user_add_address == "") {
            $user_add_address = 'NULL';
        } else {
            $user_add_address = $this->registry->main_class->format_striptags($user_add_address);
            $user_add_address = $this->registry->main_class->processing_data($user_add_address);
        }
        if($user_add_telephone == "") {
            $user_add_telephone = 'NULL';
        } else {
            $user_add_telephone = $this->registry->main_class->format_striptags($user_add_telephone);
            $user_add_telephone = $this->registry->main_class->processing_data($user_add_telephone);
        }
        if($user_add_icq == "") {
            $user_add_icq = 'NULL';
        } else {
            $user_add_icq = $this->registry->main_class->format_striptags($user_add_icq);
            $user_add_icq = $this->registry->main_class->processing_data($user_add_icq);
        }
        if($user_add_notes == "") {
            $user_add_notes = 'NULL';
        } else {
            $user_add_notes = $this->registry->main_class->format_striptags($user_add_notes);
            $user_add_notes = $this->registry->main_class->processing_data($user_add_notes);
        }
        if($user_add_activenotes == "") {
            $user_add_activenotes = 'NULL';
        } else {
            $user_add_activenotes = $this->registry->main_class->format_striptags($user_add_activenotes);
            $user_add_activenotes = $this->registry->main_class->processing_data($user_add_activenotes);
        }
        $user_add_email = $this->registry->main_class->format_striptags($user_add_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($user_add_email))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addincorrectemail|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","incorrectemail");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addincorrectemail|".$this->registry->language);
            exit();
        }
        $user_add_email = $this->registry->main_class->processing_data($user_add_email);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$user_add_login)) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addlogin|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","adderrorlogin");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addlogin|".$this->registry->language);
            exit();
        }
        $user_add_login = $this->registry->main_class->format_striptags($user_add_login);
        $user_add_login = $this->registry->main_class->processing_data($user_add_login);
        $user_add_gmt = $this->registry->main_class->format_striptags($user_add_gmt);
        $user_add_gmt = $this->registry->main_class->processing_data($user_add_gmt);
        $user_add_password = $this->registry->main_class->format_striptags($user_add_password);
        $user_add_password = $this->registry->main_class->processing_data($user_add_password);
        $user_add_password = substr(substr($user_add_password,0,-1),1);
        $user_add_password2 = $this->registry->main_class->format_striptags($user_add_password2);
        $user_add_password2 = $this->registry->main_class->processing_data($user_add_password2);
        $user_add_password2 = substr(substr($user_add_password2,0,-1),1);
        if($user_add_password !== $user_add_password2) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addpass|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","adderrorpass");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addpass|".$this->registry->language);
            exit();
        } else {
            $sql0 = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE a.user_email=".$user_add_email." or a.user_login=".$user_add_login.") as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE b.user_email=".$user_add_email." or b.user_login=".$user_add_login.") as count2 ".$this->registry->main_class->check_db_need_from_clause();
            $insert_result = $this->db->Execute($sql0);
            if($insert_result) {
                if(isset($insert_result->fields['0'])) {
                    $numrows = intval($insert_result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if(isset($insert_result->fields['1'])) {
                    $numrows2 = intval($insert_result->fields['1']);
                } else {
                    $numrows2 = 0;
                }
                if($numrows == 0 and $numrows2 == 0) {
                    $user_add_password = md5($user_add_password);
                    $now = $this->registry->main_class->gettime();
                    $user_add_id = $this->db->GenID(PREFIX."_users_id");
                    $sql = "INSERT INTO ".PREFIX."_users (user_id,user_name,user_surname,user_website,user_email,user_login,user_password,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail)  VALUES ($user_add_id,$user_add_name,$user_add_surname,$user_add_website,$user_add_email,$user_add_login,'$user_add_password',$user_add_country,$user_add_city,$user_add_address,$user_add_telephone,$user_add_icq,$user_add_gmt,$user_add_notes,'$user_add_active',$user_add_activenotes,'$now','$user_add_viewemail')";
                    $insert_result = $this->db->Execute($sql);
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addfinduser|".$this->registry->language)) {
                        $this->registry->main_class->assign("user_update","findsuchuser");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addfinduser|".$this->registry->language);
                    exit();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|add|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                $this->registry->main_class->assign("user_update","add");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|add|ok|".$this->registry->language);
        }
    }


    public function user_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        } else {
            $action = "";
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $action = trim($_POST['action']);
        }
        if(isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
        } else {
            $user_id = 0;
        }
        if($user_id == 0 and isset($_POST['user_id']) and is_array($_POST['user_id']) and count($_POST['user_id']) > 0) {
            $user_id_is_arr = 1;
            for($i = 0,$size = count($_POST['user_id']);$i < $size;++$i) {
                if($i == 0) {
                    $user_id = "'".intval($_POST['user_id'][$i])."'";
                } else {
                    $user_id .= ",'".intval($_POST['user_id'][$i])."'";
                }
            }
        } else {
            $user_id_is_arr = 0;
            $user_id = "'".$user_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($user_id) or $action == "" or ($user_id == "'0'" and $action != "deletetempall")) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_users SET user_active='1' WHERE user_id IN (".$user_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("user_update","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_users SET user_active='0' WHERE user_id IN (".$user_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("user_update","off");
        } elseif($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_users WHERE user_id IN (".$user_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("user_update","delete");
        } elseif($action == "deletetemp") {
            $sql = "DELETE FROM ".PREFIX."_users_temp WHERE user_id IN (".$user_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("user_update","deletetemp");
        } elseif($action == "deletetempall") {
            $sql = "DELETE FROM ".PREFIX."_users_temp";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("user_update","deletetempall");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
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
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUUSERS'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|statusok|".$this->registry->language);
            exit();
        } else {
            if($action == "deletetemp" or $action == "deletetempall") {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
            } else {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
                if($user_id_is_arr == 1) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|edit");
                    $this->registry->main_class->systemdk_clearcache("modules|account|show");
                    $this->registry->main_class->systemdk_clearcache("modules|account|edit");
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|edit|".intval($_GET['user_id']));
                    $this->registry->main_class->systemdk_clearcache("modules|account|show|".intval($_GET['user_id']));
                    $this->registry->main_class->systemdk_clearcache("modules|account|edit|".intval($_GET['user_id']));
                }
                $this->registry->main_class->systemdk_clearcache("homepage");
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|".$action."|ok|".$this->registry->language);
        }
    }


    public function user_edit() {
        if(isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
        }
        if(!isset($_GET['user_id']) or $user_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITUSERPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|edit|".$user_id."|".$this->registry->language)) {
            $sql = "SELECT user_id,user_name,user_surname,user_website,user_email,user_login,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail FROM ".PREFIX."_users WHERE user_id='".$user_id."'";
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("user_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if($numrows == 0) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editnotfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("user_update","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editnotfind|".$this->registry->language);
                exit();
            } else {
                $user_id = intval($result->fields['0']);
                $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $user_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $user_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                $user_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                $user_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $user_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                $user_active = intval($result->fields['14']);
                $user_activenotes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                $user_regdate = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
                $user_viewemail = intval($result->fields['17']);
                $user_all[] = array(
                    "user_id" => $user_id,
                    "user_name" => $user_name,
                    "user_surname" => $user_surname,
                    "user_website" => $user_website,
                    "user_email" => $user_email,
                    "user_login" => $user_login,
                    "user_rights" => $user_rights,
                    "user_country" => $user_country,
                    "user_city" => $user_city,
                    "user_address" => $user_address,
                    "user_telephone" => $user_telephone,
                    "user_icq" => $user_icq,
                    "user_gmt" => $user_gmt,
                    "user_notes" => $user_notes,
                    "user_active" => $user_active,
                    "user_activenotes" => $user_activenotes,
                    "user_regdate" => $user_regdate,
                    "user_viewemail" => $user_viewemail
                );
                $this->registry->main_class->assign("user_all",$user_all);
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/useredit.html");
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|edit|".$user_id."|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|edit|".$user_id."|".$this->registry->language);
        }
    }


    public function user_edit_inbase() {
        if(isset($_POST['user_edit_id'])) {
            $user_edit_id = intval($_POST['user_edit_id']);
        }
        if(isset($_POST['user_edit_name'])) {
            $user_edit_name = trim($_POST['user_edit_name']);
        }
        if(isset($_POST['user_edit_surname'])) {
            $user_edit_surname = trim($_POST['user_edit_surname']);
        }
        if(isset($_POST['user_edit_website'])) {
            $user_edit_website = trim($_POST['user_edit_website']);
        }
        if(isset($_POST['user_edit_email'])) {
            $user_edit_email = trim($_POST['user_edit_email']);
        }
        if(isset($_POST['user_edit_login'])) {
            $user_edit_login = trim($_POST['user_edit_login']);
        }
        if(isset($_POST['user_edit_newpassword'])) {
            $user_edit_newpassword = trim($_POST['user_edit_newpassword']);
        }
        if(isset($_POST['user_edit_newpassword2'])) {
            $user_edit_newpassword2 = trim($_POST['user_edit_newpassword2']);
        }
        if(isset($_POST['user_edit_country'])) {
            $user_edit_country = trim($_POST['user_edit_country']);
        }
        if(isset($_POST['user_edit_city'])) {
            $user_edit_city = trim($_POST['user_edit_city']);
        }
        if(isset($_POST['user_edit_address'])) {
            $user_edit_address = trim($_POST['user_edit_address']);
        }
        if(isset($_POST['user_edit_telephone'])) {
            $user_edit_telephone = trim($_POST['user_edit_telephone']);
        }
        if(isset($_POST['user_edit_icq'])) {
            $user_edit_icq = trim($_POST['user_edit_icq']);
        }
        if(isset($_POST['user_edit_gmt'])) {
            $user_edit_gmt = trim($_POST['user_edit_gmt']);
        }
        if(isset($_POST['user_edit_notes'])) {
            $user_edit_notes = trim($_POST['user_edit_notes']);
        }
        if(isset($_POST['user_edit_active'])) {
            $user_edit_active = intval($_POST['user_edit_active']);
        }
        if(isset($_POST['user_edit_activenotes'])) {
            $user_edit_activenotes = trim($_POST['user_edit_activenotes']);
        }
        if(isset($_POST['user_edit_viewemail'])) {
            $user_edit_viewemail = intval($_POST['user_edit_viewemail']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITUSERPAGETITLE'));
        if((!isset($_POST['user_edit_id']) or !isset($_POST['user_edit_name']) or !isset($_POST['user_edit_surname'])or !isset($_POST['user_edit_website']) or !isset($_POST['user_edit_email']) or !isset($_POST['user_edit_login']) or !isset($_POST['user_edit_newpassword']) or !isset($_POST['user_edit_newpassword2']) or !isset($_POST['user_edit_country']) or !isset($_POST['user_edit_city']) or !isset($_POST['user_edit_address']) or !isset($_POST['user_edit_telephone']) or !isset($_POST['user_edit_icq']) or !isset($_POST['user_edit_gmt']) or !isset($_POST['user_edit_notes']) or !isset($_POST['user_edit_active']) or !isset($_POST['user_edit_activenotes']) or !isset($_POST['user_edit_viewemail'])) or ($user_edit_id == 0 or $user_edit_email == "" or $user_edit_login == "" or $user_edit_gmt == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editnotalldata|".$this->registry->language);
            exit();
        }
        if($user_edit_name == "") {
            $user_edit_name = 'NULL';
        } else {
            $user_edit_name = $this->registry->main_class->format_striptags($user_edit_name);
            $user_edit_name = $this->registry->main_class->processing_data($user_edit_name);
        }
        if($user_edit_surname == "") {
            $user_edit_surname = 'NULL';
        } else {
            $user_edit_surname = $this->registry->main_class->format_striptags($user_edit_surname);
            $user_edit_surname = $this->registry->main_class->processing_data($user_edit_surname);
        }
        if($user_edit_website == "") {
            $user_edit_website = 'NULL';
        } else {
            $user_edit_website = $this->registry->main_class->format_striptags($user_edit_website);
            $user_edit_website = $this->registry->main_class->processing_data($user_edit_website);
        }
        if($user_edit_country == "") {
            $user_edit_country = 'NULL';
        } else {
            $user_edit_country = $this->registry->main_class->format_striptags($user_edit_country);
            $user_edit_country = $this->registry->main_class->processing_data($user_edit_country);
        }
        if($user_edit_city == "") {
            $user_edit_city = 'NULL';
        } else {
            $user_edit_city = $this->registry->main_class->format_striptags($user_edit_city);
            $user_edit_city = $this->registry->main_class->processing_data($user_edit_city);
        }
        if($user_edit_address == "") {
            $user_edit_address = 'NULL';
        } else {
            $user_edit_address = $this->registry->main_class->format_striptags($user_edit_address);
            $user_edit_address = $this->registry->main_class->processing_data($user_edit_address);
        }
        if($user_edit_telephone == "") {
            $user_edit_telephone = 'NULL';
        } else {
            $user_edit_telephone = $this->registry->main_class->format_striptags($user_edit_telephone);
            $user_edit_telephone = $this->registry->main_class->processing_data($user_edit_telephone);
        }
        if($user_edit_icq == "") {
            $user_edit_icq = 'NULL';
        } else {
            $user_edit_icq = $this->registry->main_class->format_striptags($user_edit_icq);
            $user_edit_icq = $this->registry->main_class->processing_data($user_edit_icq);
        }
        if($user_edit_notes == "") {
            $user_edit_notes = 'NULL';
        } else {
            $user_edit_notes = $this->registry->main_class->format_striptags($user_edit_notes);
            $user_edit_notes = $this->registry->main_class->processing_data($user_edit_notes);
        }
        if($user_edit_activenotes == "") {
            $user_edit_activenotes = 'NULL';
        } else {
            $user_edit_activenotes = $this->registry->main_class->format_striptags($user_edit_activenotes);
            $user_edit_activenotes = $this->registry->main_class->processing_data($user_edit_activenotes);
        }
        $user_edit_email = $this->registry->main_class->format_striptags($user_edit_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($user_edit_email))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editincorrectemail|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","incorrectemail");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editincorrectemail|".$this->registry->language);
            exit();
        }
        $user_edit_email = $this->registry->main_class->processing_data($user_edit_email);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$user_edit_login)) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editlogin|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","adderrorlogin");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editlogin|".$this->registry->language);
            exit();
        }
        $user_edit_login = $this->registry->main_class->format_striptags($user_edit_login);
        $user_edit_login = $this->registry->main_class->processing_data($user_edit_login);
        $user_edit_gmt = $this->registry->main_class->format_striptags($user_edit_gmt);
        $user_edit_gmt = $this->registry->main_class->processing_data($user_edit_gmt);
        if(isset($user_edit_newpassword) and $user_edit_newpassword != "") {
            $user_edit_newpassword = $this->registry->main_class->format_striptags($user_edit_newpassword);
            $user_edit_newpassword = $this->registry->main_class->processing_data($user_edit_newpassword);
            $user_edit_newpassword = substr(substr($user_edit_newpassword,0,-1),1);
            $user_edit_newpassword2 = $this->registry->main_class->format_striptags($user_edit_newpassword2);
            $user_edit_newpassword2 = $this->registry->main_class->processing_data($user_edit_newpassword2);
            $user_edit_newpassword2 = substr(substr($user_edit_newpassword2,0,-1),1);
            if($user_edit_newpassword !== $user_edit_newpassword2) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editpass|".$this->registry->language)) {
                    $this->registry->main_class->assign("user_update","adderrorpass");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editpass|".$this->registry->language);
                exit();
            } else {
                $sql0 = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE a.user_id<>'".$user_edit_id."' and (a.user_email=".$user_edit_email." or a.user_login=".$user_edit_login.")) as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE b.user_email=".$user_edit_email." or b.user_login=".$user_edit_login.") as count2 ".$this->registry->main_class->check_db_need_from_clause();
                $insert_result = $this->db->Execute($sql0);
                if($insert_result) {
                    if(isset($insert_result->fields['0'])) {
                        $numrows = intval($insert_result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if(isset($insert_result->fields['1'])) {
                        $numrows2 = intval($insert_result->fields['1']);
                    } else {
                        $numrows2 = 0;
                    }
                    if($numrows == 0 and $numrows2 == 0) {
                        $user_edit_password = md5($user_edit_newpassword);
                        $sql = "UPDATE ".PREFIX."_users SET user_name=$user_edit_name,user_surname=$user_edit_surname,user_website=$user_edit_website,user_email=$user_edit_email,user_login=$user_edit_login,user_password='$user_edit_password',user_country=$user_edit_country,user_city=$user_edit_city,user_address=$user_edit_address,user_telephone=$user_edit_telephone,user_icq=$user_edit_icq,user_gmt=$user_edit_gmt,user_notes=$user_edit_notes,user_active='$user_edit_active',user_activenotes=$user_edit_activenotes,user_viewemail='$user_edit_viewemail' WHERE user_id='$user_edit_id'";
                        $insert_result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($insert_result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                    } else {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editfinduser|".$this->registry->language)) {
                            $this->registry->main_class->assign("user_update","findsuchuser");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editfinduser|".$this->registry->language);
                        exit();
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        } else {
            $sql0 = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE a.user_id<>'".$user_edit_id."' and (a.user_email=".$user_edit_email." or a.user_login=".$user_edit_login.")) as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE b.user_email=".$user_edit_email." or b.user_login=".$user_edit_login.") as count2 ".$this->registry->main_class->check_db_need_from_clause();
            $insert_result = $this->db->Execute($sql0);
            if($insert_result) {
                if(isset($insert_result->fields['0'])) {
                    $numrows = intval($insert_result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if(isset($insert_result->fields['1'])) {
                    $numrows2 = intval($insert_result->fields['1']);
                } else {
                    $numrows2 = 0;
                }
                if($numrows == 0 and $numrows2 == 0) {
                    $sql = "UPDATE ".PREFIX."_users SET user_name=$user_edit_name,user_surname=$user_edit_surname,user_website=$user_edit_website,user_email=$user_edit_email,user_login=$user_edit_login,user_country=$user_edit_country,user_city=$user_edit_city,user_address=$user_edit_address,user_telephone=$user_edit_telephone,user_icq=$user_edit_icq,user_gmt=$user_edit_gmt,user_notes=$user_edit_notes,user_active='$user_edit_active',user_activenotes=$user_edit_activenotes,user_viewemail='$user_edit_viewemail' WHERE user_id='$user_edit_id'";
                    $insert_result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editfinduser|".$this->registry->language)) {
                        $this->registry->main_class->assign("user_update","findsuchuser");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editfinduser|".$this->registry->language);
                    exit();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($insert_result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("user_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|edit|".$user_edit_id);
            $this->registry->main_class->systemdk_clearcache("modules|account|show|".$user_edit_id);
            $this->registry->main_class->systemdk_clearcache("modules|account|edit|".$user_edit_id);
            $this->registry->main_class->systemdk_clearcache("homepage");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|edit|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/users/userupdate.html");
                $this->registry->main_class->assign("user_update","editok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|users|edit|ok|".$this->registry->language);
        }
    }
}

?>
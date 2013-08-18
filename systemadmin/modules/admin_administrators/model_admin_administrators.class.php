<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_administrators.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_administrators extends model_base {


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
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|show|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.admin_id,a.admin_name,a.admin_surname,a.admin_login,a.admin_rights,a.admin_email,a.admin_active,(SELECT count(b.admin_id) FROM ".PREFIX."_admins b) as count_admin_id FROM ".PREFIX."_admins a order by a.admin_id DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_administrators&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['7']);
                        }
                        $admin_id = intval($result->fields['0']);
                        $admin_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $admin_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $admin_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $admin_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                        $admin_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                        $admin_active = intval($result->fields['6']);
                        $admin_all[] = array(
                            "admin_id" => $admin_id,
                            "admin_name" => $admin_name,
                            "admin_surname" => $admin_surname,
                            "admin_email" => $admin_email,
                            "admin_login" => $admin_login,
                            "admin_rights" => $admin_rights,
                            "admin_active" => $admin_active,
                            "count_admin_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("admin_all",$admin_all);
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
                        $this->registry->main_class->assign("totaladmins",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("admin_all","no");
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|showsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUADMINS'));
                    $this->registry->main_class->assign("admin_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|showsqlerror|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|show|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUADMINS'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/admins.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|show|".$num_page."|".$this->registry->language);
    }


    public function admin_add() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDADMINPAGETITLE'));
        if($this->registry->main_class->get_admin_rights() !== "main_admin") {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addrights|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","norights");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addrights|".$this->registry->language);
            exit();
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|add|".$this->registry->language)) {
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|add|".$this->registry->language);
    }


    public function admin_add_inbase() {
        if(isset($_POST['admin_add_name'])) {
            $admin_add_name = trim($_POST['admin_add_name']);
        }
        if(isset($_POST['admin_add_surname'])) {
            $admin_add_surname = trim($_POST['admin_add_surname']);
        }
        if(isset($_POST['admin_add_website'])) {
            $admin_add_website = trim($_POST['admin_add_website']);
        }
        if(isset($_POST['admin_add_email'])) {
            $admin_add_email = trim($_POST['admin_add_email']);
        }
        if(isset($_POST['admin_add_login'])) {
            $admin_add_login = trim($_POST['admin_add_login']);
        }
        if(isset($_POST['admin_add_password'])) {
            $admin_add_password = trim($_POST['admin_add_password']);
        }
        if(isset($_POST['admin_add_password2'])) {
            $admin_add_password2 = trim($_POST['admin_add_password2']);
        }
        if(isset($_POST['admin_add_country'])) {
            $admin_add_country = trim($_POST['admin_add_country']);
        }
        if(isset($_POST['admin_add_city'])) {
            $admin_add_city = trim($_POST['admin_add_city']);
        }
        if(isset($_POST['admin_add_address'])) {
            $admin_add_address = trim($_POST['admin_add_address']);
        }
        if(isset($_POST['admin_add_telephone'])) {
            $admin_add_telephone = trim($_POST['admin_add_telephone']);
        }
        if(isset($_POST['admin_add_icq'])) {
            $admin_add_icq = trim($_POST['admin_add_icq']);
        }
        if(isset($_POST['admin_add_gmt'])) {
            $admin_add_gmt = trim($_POST['admin_add_gmt']);
        }
        if(isset($_POST['admin_add_notes'])) {
            $admin_add_notes = trim($_POST['admin_add_notes']);
        }
        if(isset($_POST['admin_add_active'])) {
            $admin_add_active = intval($_POST['admin_add_active']);
        }
        if(isset($_POST['admin_add_activenotes'])) {
            $admin_add_activenotes = trim($_POST['admin_add_activenotes']);
        }
        if(isset($_POST['admin_add_viewemail'])) {
            $admin_add_viewemail = intval($_POST['admin_add_viewemail']);
        }
        if(isset($_POST['admin_add_rights'])) {
            $admin_add_rights = trim($_POST['admin_add_rights']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDADMINPAGETITLE'));
        if($this->registry->main_class->get_admin_rights() !== "main_admin") {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addrights|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","norights");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addrights|".$this->registry->language);
            exit();
        }
        if((!isset($_POST['admin_add_name']) or !isset($_POST['admin_add_surname'])or !isset($_POST['admin_add_website']) or !isset($_POST['admin_add_email']) or !isset($_POST['admin_add_login']) or !isset($_POST['admin_add_password']) or !isset($_POST['admin_add_password2']) or !isset($_POST['admin_add_country']) or !isset($_POST['admin_add_city']) or !isset($_POST['admin_add_address']) or !isset($_POST['admin_add_telephone']) or !isset($_POST['admin_add_icq']) or !isset($_POST['admin_add_gmt']) or !isset($_POST['admin_add_notes']) or !isset($_POST['admin_add_active']) or !isset($_POST['admin_add_activenotes']) or !isset($_POST['admin_add_viewemail']) or !isset($_POST['admin_add_rights'])) or ($admin_add_email == "" or $admin_add_login == "" or $admin_add_password == "" or $admin_add_password2 == "" or $admin_add_gmt == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addnotalldata|".$this->registry->language);
            exit();
        }
        if($admin_add_name == "") {
            $admin_add_name = 'NULL';
        } else {
            $admin_add_name = $this->registry->main_class->format_striptags($admin_add_name);
            $admin_add_name = $this->registry->main_class->processing_data($admin_add_name);
        }
        if($admin_add_surname == "") {
            $admin_add_surname = 'NULL';
        } else {
            $admin_add_surname = $this->registry->main_class->format_striptags($admin_add_surname);
            $admin_add_surname = $this->registry->main_class->processing_data($admin_add_surname);
        }
        if($admin_add_website == "") {
            $admin_add_website = 'NULL';
        } else {
            $admin_add_website = $this->registry->main_class->format_striptags($admin_add_website);
            $admin_add_website = $this->registry->main_class->processing_data($admin_add_website);
        }
        if($admin_add_country == "") {
            $admin_add_country = 'NULL';
        } else {
            $admin_add_country = $this->registry->main_class->format_striptags($admin_add_country);
            $admin_add_country = $this->registry->main_class->processing_data($admin_add_country);
        }
        if($admin_add_city == "") {
            $admin_add_city = 'NULL';
        } else {
            $admin_add_city = $this->registry->main_class->format_striptags($admin_add_city);
            $admin_add_city = $this->registry->main_class->processing_data($admin_add_city);
        }
        if($admin_add_address == "") {
            $admin_add_address = 'NULL';
        } else {
            $admin_add_address = $this->registry->main_class->format_striptags($admin_add_address);
            $admin_add_address = $this->registry->main_class->processing_data($admin_add_address);
        }
        if($admin_add_telephone == "") {
            $admin_add_telephone = 'NULL';
        } else {
            $admin_add_telephone = $this->registry->main_class->format_striptags($admin_add_telephone);
            $admin_add_telephone = $this->registry->main_class->processing_data($admin_add_telephone);
        }
        if($admin_add_icq == "") {
            $admin_add_icq = 'NULL';
        } else {
            $admin_add_icq = $this->registry->main_class->format_striptags($admin_add_icq);
            $admin_add_icq = $this->registry->main_class->processing_data($admin_add_icq);
        }
        if($admin_add_notes == "") {
            $admin_add_notes = 'NULL';
        } else {
            $admin_add_notes = $this->registry->main_class->format_striptags($admin_add_notes);
            $admin_add_notes = $this->registry->main_class->processing_data($admin_add_notes);
        }
        if($admin_add_activenotes == "") {
            $admin_add_activenotes = 'NULL';
        } else {
            $admin_add_activenotes = $this->registry->main_class->format_striptags($admin_add_activenotes);
            $admin_add_activenotes = $this->registry->main_class->processing_data($admin_add_activenotes);
        }
        if($admin_add_rights == "") {
            $admin_add_rights = 'NULL';
        } else {
            $admin_add_rights = $this->registry->main_class->format_striptags($admin_add_rights);
            $admin_add_rights = $this->registry->main_class->processing_data($admin_add_rights);
        }
        $admin_add_email = $this->registry->main_class->format_striptags($admin_add_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($admin_add_email))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addincorrectemail|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","incorrectemail");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addincorrectemail|".$this->registry->language);
            exit();
        }
        $admin_add_email = $this->registry->main_class->processing_data($admin_add_email);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$admin_add_login)) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addlogin|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","adderrorlogin");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addlogin|".$this->registry->language);
            exit();
        }
        $admin_add_login = $this->registry->main_class->format_striptags($admin_add_login);
        $admin_add_login = $this->registry->main_class->processing_data($admin_add_login);
        $admin_add_gmt = $this->registry->main_class->format_striptags($admin_add_gmt);
        $admin_add_gmt = $this->registry->main_class->processing_data($admin_add_gmt);
        $admin_add_password = $this->registry->main_class->format_striptags($admin_add_password);
        $admin_add_password = $this->registry->main_class->processing_data($admin_add_password);
        $admin_add_password = substr(substr($admin_add_password,0,-1),1);
        $admin_add_password2 = $this->registry->main_class->format_striptags($admin_add_password2);
        $admin_add_password2 = $this->registry->main_class->processing_data($admin_add_password2);
        $admin_add_password2 = substr(substr($admin_add_password2,0,-1),1);
        if($admin_add_password !== $admin_add_password2) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addpass|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","adderrorpass");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addpass|".$this->registry->language);
            exit();
        } else {
            $sql0 = "SELECT admin_id FROM ".PREFIX."_admins WHERE admin_email=".$admin_add_email." or admin_login=".$admin_add_login."";
            $insert_result = $this->db->Execute($sql0);
            if($insert_result) {
                if(isset($insert_result->fields['0'])) {
                    $numrows = intval($insert_result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0) {
                    $now = $this->registry->main_class->gettime();
                    $admin_add_password = md5($admin_add_password);
                    $admin_add_id = $this->db->GenID(PREFIX."_admins_id");
                    $sql = "INSERT INTO ".PREFIX."_admins (admin_id,admin_name,admin_surname,admin_website,admin_email,admin_login,admin_password,admin_rights,admin_country,admin_city,admin_address,admin_telephone,admin_icq,admin_gmt,admin_notes,admin_active,admin_activenotes,admin_regdate,admin_viewemail)  VALUES ($admin_add_id,$admin_add_name,$admin_add_surname,$admin_add_website,$admin_add_email,$admin_add_login,'$admin_add_password',$admin_add_rights,$admin_add_country,$admin_add_city,$admin_add_address,$admin_add_telephone,$admin_add_icq,$admin_add_gmt,$admin_add_notes,'$admin_add_active',$admin_add_activenotes,'$now','$admin_add_viewemail')";
                    $insert_result = $this->db->Execute($sql);
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addfindadmin|".$this->registry->language)) {
                        $this->registry->main_class->assign("admin_update","findsuchadmin");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addfindadmin|".$this->registry->language);
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
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|add|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                $this->registry->main_class->assign("admin_update","add");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|add|ok|".$this->registry->language);
        }
    }


    public function admin_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        } else {
            $action = "";
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $action = trim($_POST['action']);
        }
        if($this->registry->main_class->get_admin_rights() !== "main_admin") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|statusrights|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUADMINS'));
                $this->registry->main_class->assign("admin_update","norights");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|statusrights|".$this->registry->language);
            exit();
        }
        if(isset($_GET['admin_id'])) {
            $admin_id = intval($_GET['admin_id']);
        } else {
            $admin_id = 0;
        }
        if($admin_id == 0 and isset($_POST['admin_id']) and is_array($_POST['admin_id']) and count($_POST['admin_id']) > 0) {
            $admin_id_is_arr = 1;
            for($i = 0,$size = count($_POST['admin_id']);$i < $size;++$i) {
                if($i == 0) {
                    $admin_id = "'".intval($_POST['admin_id'][$i])."'";
                } else {
                    $admin_id .= ",'".intval($_POST['admin_id'][$i])."'";
                }
            }
        } else {
            $admin_id_is_arr = 0;
            $admin_id = "'".$admin_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($admin_id) or $action == "" or $admin_id == "'0'") {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_administrators&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_admins SET admin_active='1' WHERE admin_id IN (".$admin_id.") AND admin_rights<>'main_admin'";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("admin_update","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_admins SET admin_active='0' WHERE admin_id IN (".$admin_id.") AND admin_rights<>'main_admin'";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("admin_update","off");
        } elseif($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_admins WHERE admin_id IN (".$admin_id.") AND admin_rights<>'main_admin'";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("admin_update","delete");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_administrators&func=index&lang=".$this->registry->sitelang);
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
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUADMINS'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|show");
            if($admin_id_is_arr == 1) {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|edit");
                $this->registry->main_class->systemdk_clearcache("systemadmin|edit");
            } else {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|edit|".intval($_GET['admin_id']));
                $this->registry->main_class->systemdk_clearcache("systemadmin|edit|".intval($_GET['admin_id']));
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|".$action."|ok|".$this->registry->language);
        }
    }


    public function admin_edit() {
        if(isset($_GET['admin_id'])) {
            $admin_id = intval($_GET['admin_id']);
        }
        if($this->registry->main_class->get_admin_rights() !== "main_admin" and isset($_GET['admin_id']) and intval($this->registry->main_class->get_admin_id()) != intval($_GET['admin_id'])) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editrights|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITADMINPAGETITLE'));
                $this->registry->main_class->assign("admin_update","norights");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editrights|".$this->registry->language);
            exit();
        }
        if(!isset($_GET['admin_id']) or $admin_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_administrators&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITADMINPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|edit|".$admin_id."|".$this->registry->language)) {
            $sql = "SELECT admin_id,admin_name,admin_surname,admin_website,admin_email,admin_login,admin_rights,admin_country,admin_city,admin_address,admin_telephone,admin_icq,admin_gmt,admin_notes,admin_active,admin_activenotes,admin_regdate,admin_viewemail FROM ".PREFIX."_admins WHERE admin_id='".$admin_id."'";
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
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("admin_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if($numrows == 0) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editnotfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("admin_update","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editnotfind|".$this->registry->language);
                exit();
            } else {
                $admin_id = intval($result->fields['0']);
                $admin_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $admin_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $admin_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $admin_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $admin_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                $admin_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $admin_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $admin_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $admin_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $admin_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                $admin_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                $admin_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $admin_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                $admin_active = intval($result->fields['14']);
                $admin_activenotes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                $admin_regdate = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
                $admin_viewemail = intval($result->fields['17']);
                $admin_all[] = array(
                    "admin_id" => $admin_id,
                    "admin_name" => $admin_name,
                    "admin_surname" => $admin_surname,
                    "admin_website" => $admin_website,
                    "admin_email" => $admin_email,
                    "admin_login" => $admin_login,
                    "admin_rights" => $admin_rights,
                    "admin_country" => $admin_country,
                    "admin_city" => $admin_city,
                    "admin_address" => $admin_address,
                    "admin_telephone" => $admin_telephone,
                    "admin_icq" => $admin_icq,
                    "admin_gmt" => $admin_gmt,
                    "admin_notes" => $admin_notes,
                    "admin_active" => $admin_active,
                    "admin_activenotes" => $admin_activenotes,
                    "admin_regdate" => $admin_regdate,
                    "admin_viewemail" => $admin_viewemail
                );
                $this->registry->main_class->assign("admin_all",$admin_all);
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminedit.html");
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|edit|".$admin_id."|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|edit|".$admin_id."|".$this->registry->language);
        }
    }


    public function admin_edit_inbase() {
        if(isset($_POST['admin_edit_id'])) {
            $admin_edit_id = intval($_POST['admin_edit_id']);
        }
        if(isset($_POST['admin_edit_name'])) {
            $admin_edit_name = trim($_POST['admin_edit_name']);
        }
        if(isset($_POST['admin_edit_surname'])) {
            $admin_edit_surname = trim($_POST['admin_edit_surname']);
        }
        if(isset($_POST['admin_edit_website'])) {
            $admin_edit_website = trim($_POST['admin_edit_website']);
        }
        if(isset($_POST['admin_edit_email'])) {
            $admin_edit_email = trim($_POST['admin_edit_email']);
        }
        if(isset($_POST['admin_edit_login'])) {
            $admin_edit_login = trim($_POST['admin_edit_login']);
        }
        if(isset($_POST['admin_edit_newpassword'])) {
            $admin_edit_newpassword = trim($_POST['admin_edit_newpassword']);
        }
        if(isset($_POST['admin_edit_newpassword2'])) {
            $admin_edit_newpassword2 = trim($_POST['admin_edit_newpassword2']);
        }
        if(isset($_POST['admin_edit_country'])) {
            $admin_edit_country = trim($_POST['admin_edit_country']);
        }
        if(isset($_POST['admin_edit_city'])) {
            $admin_edit_city = trim($_POST['admin_edit_city']);
        }
        if(isset($_POST['admin_edit_address'])) {
            $admin_edit_address = trim($_POST['admin_edit_address']);
        }
        if(isset($_POST['admin_edit_telephone'])) {
            $admin_edit_telephone = trim($_POST['admin_edit_telephone']);
        }
        if(isset($_POST['admin_edit_icq'])) {
            $admin_edit_icq = trim($_POST['admin_edit_icq']);
        }
        if(isset($_POST['admin_edit_gmt'])) {
            $admin_edit_gmt = trim($_POST['admin_edit_gmt']);
        }
        if(isset($_POST['admin_edit_notes'])) {
            $admin_edit_notes = trim($_POST['admin_edit_notes']);
        }
        if(isset($_POST['admin_edit_active'])) {
            $admin_edit_active = intval($_POST['admin_edit_active']);
        }
        if(isset($_POST['admin_edit_activenotes'])) {
            $admin_edit_activenotes = trim($_POST['admin_edit_activenotes']);
        }
        if(isset($_POST['admin_edit_viewemail'])) {
            $admin_edit_viewemail = intval($_POST['admin_edit_viewemail']);
        }
        if(isset($_POST['admin_old_login'])) {
            $admin_old_login = trim($_POST['admin_old_login']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITADMINPAGETITLE'));
        if($this->registry->main_class->get_admin_rights() !== "main_admin" and isset($_POST['admin_edit_id']) and intval($this->registry->main_class->get_admin_id()) != intval($_POST['admin_edit_id'])) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editrights|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","norights");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editrights|".$this->registry->language);
            exit();
        }
        if((!isset($_POST['admin_edit_id']) or !isset($_POST['admin_edit_name']) or !isset($_POST['admin_edit_surname'])or !isset($_POST['admin_edit_website']) or !isset($_POST['admin_edit_email']) or !isset($_POST['admin_edit_login']) or !isset($_POST['admin_edit_newpassword']) or !isset($_POST['admin_edit_newpassword2']) or !isset($_POST['admin_edit_country']) or !isset($_POST['admin_edit_city']) or !isset($_POST['admin_edit_address']) or !isset($_POST['admin_edit_telephone']) or !isset($_POST['admin_edit_icq']) or !isset($_POST['admin_edit_gmt']) or !isset($_POST['admin_edit_notes']) or !isset($_POST['admin_edit_active']) or !isset($_POST['admin_edit_activenotes']) or !isset($_POST['admin_edit_viewemail']) or !isset($_POST['admin_old_login'])) or ($admin_edit_id == 0 or $admin_edit_email == "" or $admin_edit_login == "" or $admin_edit_gmt == "" or $admin_old_login == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editnotalldata|".$this->registry->language);
            exit();
        }
        if($admin_edit_name == "") {
            $admin_edit_name = 'NULL';
        } else {
            $admin_edit_name = $this->registry->main_class->format_striptags($admin_edit_name);
            $admin_edit_name = $this->registry->main_class->processing_data($admin_edit_name);
        }
        if($admin_edit_surname == "") {
            $admin_edit_surname = 'NULL';
        } else {
            $admin_edit_surname = $this->registry->main_class->format_striptags($admin_edit_surname);
            $admin_edit_surname = $this->registry->main_class->processing_data($admin_edit_surname);
        }
        if($admin_edit_website == "") {
            $admin_edit_website = 'NULL';
        } else {
            $admin_edit_website = $this->registry->main_class->format_striptags($admin_edit_website);
            $admin_edit_website = $this->registry->main_class->processing_data($admin_edit_website);
        }
        if($admin_edit_country == "") {
            $admin_edit_country = 'NULL';
        } else {
            $admin_edit_country = $this->registry->main_class->format_striptags($admin_edit_country);
            $admin_edit_country = $this->registry->main_class->processing_data($admin_edit_country);
        }
        if($admin_edit_city == "") {
            $admin_edit_city = 'NULL';
        } else {
            $admin_edit_city = $this->registry->main_class->format_striptags($admin_edit_city);
            $admin_edit_city = $this->registry->main_class->processing_data($admin_edit_city);
        }
        if($admin_edit_address == "") {
            $admin_edit_address = 'NULL';
        } else {
            $admin_edit_address = $this->registry->main_class->format_striptags($admin_edit_address);
            $admin_edit_address = $this->registry->main_class->processing_data($admin_edit_address);
        }
        if($admin_edit_telephone == "") {
            $admin_edit_telephone = 'NULL';
        } else {
            $admin_edit_telephone = $this->registry->main_class->format_striptags($admin_edit_telephone);
            $admin_edit_telephone = $this->registry->main_class->processing_data($admin_edit_telephone);
        }
        if($admin_edit_icq == "") {
            $admin_edit_icq = 'NULL';
        } else {
            $admin_edit_icq = $this->registry->main_class->format_striptags($admin_edit_icq);
            $admin_edit_icq = $this->registry->main_class->processing_data($admin_edit_icq);
        }
        if($admin_edit_notes == "") {
            $admin_edit_notes = 'NULL';
        } else {
            $admin_edit_notes = $this->registry->main_class->format_striptags($admin_edit_notes);
            $admin_edit_notes = $this->registry->main_class->processing_data($admin_edit_notes);
        }
        if($admin_edit_activenotes == "") {
            $admin_edit_activenotes = 'NULL';
        } else {
            $admin_edit_activenotes = $this->registry->main_class->format_striptags($admin_edit_activenotes);
            $admin_edit_activenotes = $this->registry->main_class->processing_data($admin_edit_activenotes);
        }
        if($this->registry->main_class->get_admin_id() == $admin_edit_id and $admin_edit_active == 0) {
            $admin_edit_active = 1;
        }
        $admin_edit_email = $this->registry->main_class->format_striptags($admin_edit_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($admin_edit_email))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editincorrectemail|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","incorrectemail");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editincorrectemail|".$this->registry->language);
            exit();
        }
        $admin_edit_email = $this->registry->main_class->processing_data($admin_edit_email);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$admin_edit_login)) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editlogin|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","adderrorlogin");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editlogin|".$this->registry->language);
            exit();
        }
        $admin_edit_login = $this->registry->main_class->format_striptags($admin_edit_login);
        $admin_edit_login = $this->registry->main_class->processing_data($admin_edit_login);
        $admin_old_login = $this->registry->main_class->format_striptags($admin_old_login);
        $admin_old_login = $this->registry->main_class->processing_data($admin_old_login);
        $admin_edit_gmt = $this->registry->main_class->format_striptags($admin_edit_gmt);
        $admin_edit_gmt = $this->registry->main_class->processing_data($admin_edit_gmt);
        if(isset($admin_edit_newpassword) and $admin_edit_newpassword !== "") {
            $admin_edit_newpassword = $this->registry->main_class->format_striptags($admin_edit_newpassword);
            $admin_edit_newpassword = $this->registry->main_class->processing_data($admin_edit_newpassword);
            $admin_edit_newpassword = substr(substr($admin_edit_newpassword,0,-1),1);
            $admin_edit_newpassword2 = $this->registry->main_class->format_striptags($admin_edit_newpassword2);
            $admin_edit_newpassword2 = $this->registry->main_class->processing_data($admin_edit_newpassword2);
            $admin_edit_newpassword2 = substr(substr($admin_edit_newpassword2,0,-1),1);
            if($admin_edit_newpassword !== $admin_edit_newpassword2) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editpass|".$this->registry->language)) {
                    $this->registry->main_class->assign("admin_update","adderrorpass");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editpass|".$this->registry->language);
                exit();
            } else {
                $sql0 = "SELECT a.admin_login, (SELECT max(b.admin_id) FROM ".PREFIX."_admins b WHERE b.admin_id<>'".$admin_edit_id."' and (b.admin_email=".$admin_edit_email." or b.admin_login=".$admin_edit_login.")) as check_numrows FROM ".PREFIX."_admins a WHERE a.admin_id='".$admin_edit_id."'";
                $insert_result = $this->db->Execute($sql0);
                if($insert_result) {
                    if(isset($insert_result->fields['1'])) {
                        $numrows = intval($insert_result->fields['1']);
                    } else {
                        $numrows = 0;
                    }
                    $admin_old_login = trim($insert_result->fields['0']);
                    if($numrows == 0) {
                        $admin_edit_password = md5($admin_edit_newpassword);
                        $this->db->StartTrans();
                        $sql = "UPDATE ".PREFIX."_admins SET admin_name=$admin_edit_name,admin_surname=$admin_edit_surname,admin_website=$admin_edit_website,admin_email=$admin_edit_email,admin_login=$admin_edit_login,admin_password='$admin_edit_password',admin_country=$admin_edit_country,admin_city=$admin_edit_city,admin_address=$admin_edit_address,admin_telephone=$admin_edit_telephone,admin_icq=$admin_edit_icq,admin_gmt=$admin_edit_gmt,admin_notes=$admin_edit_notes,admin_active='$admin_edit_active',admin_activenotes=$admin_edit_activenotes,admin_viewemail='$admin_edit_viewemail' WHERE admin_id='$admin_edit_id'";
                        $insert_result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($insert_result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } else {
                            $admin_edit_login = substr(substr($admin_edit_login,0,-1),1);
                            if($num_result > 0 and $admin_old_login !== $admin_edit_login) {
                                $clear_langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
                                for($i = 0,$size = sizeof($clear_langlist);$i < $size;++$i) {
                                    $sql = "UPDATE ".PREFIX."_main_pages_".$clear_langlist[$i]['name']." SET mainpage_author='".$admin_edit_login."' WHERE mainpage_author='".$admin_old_login."'";
                                    $result1 = $this->db->Execute($sql);
                                    $num_result1 = $this->db->Affected_Rows();
                                    if($result1 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $error[] = array("code" => $error_code,"message" => $error_message);
                                    } elseif($num_result1 > 0) {
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|main_pages|show");
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|main_pages|edit");
                                    }
                                    $sql = "UPDATE ".PREFIX."_messages_".$clear_langlist[$i]['name']." SET message_author='".$admin_edit_login."' WHERE message_author='".$admin_old_login."'";
                                    $result2 = $this->db->Execute($sql);
                                    $num_result2 = $this->db->Affected_Rows();
                                    if($result2 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $error[] = array("code" => $error_code,"message" => $error_message);
                                    } elseif($num_result2 > 0) {
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|messages|show");
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|messages|edit");
                                    }
                                    $sql = "UPDATE ".PREFIX."_news_".$clear_langlist[$i]['name']." SET news_author='".$admin_edit_login."' WHERE news_author='".$admin_old_login."'";
                                    $result3 = $this->db->Execute($sql);
                                    $num_result3 = $this->db->Affected_Rows();
                                    if($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $error[] = array("code" => $error_code,"message" => $error_message);
                                    } elseif($num_result3 > 0) {
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|showcurrent");
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|edit");
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|modules|news|show|admin");
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|modules|news|newsread");
                                    }
                                    $sql = "UPDATE ".PREFIX."_news_auto_".$clear_langlist[$i]['name']." SET news_author='".$admin_edit_login."' WHERE news_author='".$admin_old_login."'";
                                    $result4 = $this->db->Execute($sql);
                                    $num_result4 = $this->db->Affected_Rows();
                                    if($result4 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $error[] = array("code" => $error_code,"message" => $error_message);
                                    } elseif($num_result4 > 0) {
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|showprogramm");
                                        $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|editprogramm");
                                    }
                                    if($result1 === false or $result2 === false or $result3 === false or $result4 === false) {
                                        $insert_result = false;
                                    }
                                }
                            }
                            if($admin_edit_id == $this->registry->main_class->get_admin_id() and $insert_result !== false) {
                                setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                            }
                        }
                        $this->db->CompleteTrans();
                    } else {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editfindadmin|".$this->registry->language)) {
                            $this->registry->main_class->assign("admin_update","findsuchadmin");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editfindadmin|".$this->registry->language);
                        exit();
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        } else {
            $sql0 = "SELECT a.admin_login, (SELECT max(b.admin_id) FROM ".PREFIX."_admins b WHERE b.admin_id<>'".$admin_edit_id."' and (b.admin_email=".$admin_edit_email." or b.admin_login=".$admin_edit_login.")) as check_numrows FROM ".PREFIX."_admins a WHERE a.admin_id='".$admin_edit_id."'";
            $insert_result = $this->db->Execute($sql0);
            if($insert_result) {
                if(isset($insert_result->fields['1'])) {
                    $numrows = intval($insert_result->fields['1']);
                } else {
                    $numrows = 0;
                }
                $admin_old_login = trim($insert_result->fields['0']);
                if($numrows == 0) {
                    $this->db->StartTrans();
                    $sql = "UPDATE ".PREFIX."_admins SET admin_name=$admin_edit_name,admin_surname=$admin_edit_surname,admin_website=$admin_edit_website,admin_email=$admin_edit_email,admin_login=$admin_edit_login,admin_country=$admin_edit_country,admin_city=$admin_edit_city,admin_address=$admin_edit_address,admin_telephone=$admin_edit_telephone,admin_icq=$admin_edit_icq,admin_gmt=$admin_edit_gmt,admin_notes=$admin_edit_notes,admin_active='$admin_edit_active',admin_activenotes=$admin_edit_activenotes,admin_viewemail='$admin_edit_viewemail' WHERE admin_id='$admin_edit_id'";
                    $insert_result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    } else {
                        $admin_edit_login = substr(substr($admin_edit_login,0,-1),1);
                        if($num_result > 0 and  $admin_old_login !== $admin_edit_login) {
                            $clear_langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
                            for($i = 0,$size = sizeof($clear_langlist);$i < $size;++$i) {
                                $sql = "UPDATE ".PREFIX."_main_pages_".$clear_langlist[$i]['name']." SET mainpage_author='".$admin_edit_login."' WHERE mainpage_author='".$admin_old_login."'";
                                $result1 = $this->db->Execute($sql);
                                $num_result1 = $this->db->Affected_Rows();
                                if($result1 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $error[] = array("code" => $error_code,"message" => $error_message);
                                } elseif($num_result1 > 0) {
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|main_pages|show");
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|main_pages|edit");
                                }
                                $sql = "UPDATE ".PREFIX."_messages_".$clear_langlist[$i]['name']." SET message_author='".$admin_edit_login."' WHERE message_author='".$admin_old_login."'";
                                $result2 = $this->db->Execute($sql);
                                $num_result2 = $this->db->Affected_Rows();
                                if($result2 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $error[] = array("code" => $error_code,"message" => $error_message);
                                } elseif($num_result2 > 0) {
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|messages|show");
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|messages|edit");
                                }
                                $sql = "UPDATE ".PREFIX."_news_".$clear_langlist[$i]['name']." SET news_author='".$admin_edit_login."' WHERE news_author='".$admin_old_login."'";
                                $result3 = $this->db->Execute($sql);
                                $num_result3 = $this->db->Affected_Rows();
                                if($result3 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $error[] = array("code" => $error_code,"message" => $error_message);
                                } elseif($num_result3 > 0) {
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|showcurrent");
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|edit");
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|modules|news|show|admin");
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|modules|news|newsread");
                                }
                                $sql = "UPDATE ".PREFIX."_news_auto_".$clear_langlist[$i]['name']." SET news_author='".$admin_edit_login."' WHERE news_author='".$admin_old_login."'";
                                $result4 = $this->db->Execute($sql);
                                $num_result4 = $this->db->Affected_Rows();
                                if($result4 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $error[] = array("code" => $error_code,"message" => $error_message);
                                } elseif($num_result4 > 0) {
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|showprogramm");
                                    $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|editprogramm");
                                }
                                if($result1 === false or $result2 === false or $result3 === false or $result4 === false) {
                                    $insert_result = false;
                                }
                            }
                        }
                        if($admin_edit_id == $this->registry->main_class->get_admin_id() and $admin_edit_login !== $this->registry->main_class->get_admin_login() and $insert_result !== false) {
                            setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                        }
                    }
                    $this->db->CompleteTrans();
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editfindadmin|".$this->registry->language)) {
                        $this->registry->main_class->assign("admin_update","findsuchadmin");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editfindadmin|".$this->registry->language);
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
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($insert_result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|edit|".$admin_edit_id);
            $this->registry->main_class->systemdk_clearcache("systemadmin|edit|".$admin_edit_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|edit|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
                $this->registry->main_class->assign("admin_update","editok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|edit|ok|".$this->registry->language);
        }
    }
}

?>
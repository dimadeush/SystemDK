<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class account extends model_base {


    private $usernow = false;
    private $user_id;
    private $user_name;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->modules_account_autodelete();
    }


    public function process_autoload() {
        if((isset($_POST['user_login'])) and (isset($_POST['user_password'])) and trim($_POST['user_login']) != "" and trim($_POST['user_password']) != "" and $_POST['func'] == "mainuser") {
            if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",trim($_POST['user_login']))) {
                if(ENTER_CHECK == "yes" and extension_loaded("gd")) {
                    if(session_id() == "") {
                        session_start();
                    }
                    if(isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->assign("error","error_login");
                $this->registry->main_class->assign("back_link","userenter");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|login|".$this->registry->language);
                exit();
            }
            $user_login = substr(trim($_POST['user_login']),0,25);
            $user_login = $this->registry->main_class->format_striptags($user_login);
            $user_login = $this->registry->main_class->processing_data($user_login);
            $user_password = substr(trim($_POST['user_password']),0,25);
            if(ENTER_CHECK == "yes") {
                if(extension_loaded("gd") and session_id() == "") {
                    session_start();
                }
                if(extension_loaded("gd")  and ((isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"]) or (isset($this->registry->captcha_check) and $this->registry->captcha_check == "noteq")))) {
                    if(isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->assign("error","error_checkkode");
                    $this->registry->main_class->assign("back_link","userenter");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|checkkode|".$this->registry->language);
                    exit();
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
            $user_password = $this->registry->main_class->format_striptags($user_password);
            $user_password = $this->registry->main_class->processing_data($user_password);
            $user_password = substr(substr($user_password,0,-1),1);
            $user_password = md5($user_password);
            $sql = "SELECT user_id, user_name, user_surname, user_login, user_password, user_active, user_activatekey FROM ".PREFIX."_users WHERE user_login=".$user_login;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
            }
            if(isset($numrows) and $numrows > 0) {
                if($this->registry->main_class->extracting_data($result->fields['4']) == $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) {
                    $user_login = substr(substr($user_login,0,-1),1);
                    $user_login = $this->registry->main_class->extracting_data($user_login);
                    $user_cookie = base64_encode("$user_login:$user_password");
                    setcookie("user_cookie","$user_cookie",time() + COOKIE_LIVE,"/".SITE_DIR,$this->registry->http_host);
                    $this->user_id = intval($result->fields['0']);
                    $this->user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    if((!isset($this->user_name) or $this->user_name == "") and (isset($user_surname) and $user_surname != "")) {
                        $this->user_name = $user_surname;
                    } elseif((!isset($this->user_name) or $this->user_name == "") and (!isset($user_surname) or $user_surname == "")) {
                        $this->user_name = $this->registry->main_class->extracting_data($result->fields['3']);
                    } elseif((isset($this->user_name) and $this->user_name != "") and (isset($user_surname) and $user_surname != "")) {
                        $this->user_name = $this->user_name." ".$user_surname;
                    }
                    $user_activatekey = $result->fields['6'];
                    if(isset($user_activatekey) and $user_activatekey != '') {
                        $this->db->Execute("UPDATE ".PREFIX."_users SET user_activatekey = NULL,user_newpassword = NULL WHERE user_id='".$this->user_id."'");
                    }
                    $this->usernow = 1;
                } elseif($this->registry->main_class->extracting_data($result->fields['4']) == $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0) {
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->assign("error","error_usernotactive");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notactive|".$this->registry->language);
                    exit();
                } elseif(($this->registry->main_class->extracting_data($result->fields['4']) != $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) or ($this->registry->main_class->extracting_data($result->fields['4']) != $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0)) {
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->assign("error","error_userincorrectpass");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|incorrectpass|".$this->registry->language);
                    exit();
                }
            } else {
                $this->registry->main_class->database_close();
                $this->registry->main_class->assign("error","error_usernotfind");
                $this->registry->main_class->assign("back_link","userenter");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notfind|".$this->registry->language);
                exit();
            }
        } elseif((isset($_POST['user_login']) or isset($_POST['user_password'])) and $_POST['func'] == "mainuser") {
            if(ENTER_CHECK == "yes" and extension_loaded("gd")) {
                if(session_id() == "") {
                    session_start();
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->assign("error","error_usernotalldata");
            $this->registry->main_class->assign("back_link","userenter");
            $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|enternotalldata|".$this->registry->language);
            exit();
        } elseif(isset($this->registry->user) and $this->registry->user != "" and !isset($_POST['user_login']) and !isset($_POST['user_password'])) {
            $user_cookie = base64_decode($this->registry->user);
            $user_cookie = explode(":",$user_cookie);
            $user_login = "$user_cookie[0]";
            $user_password = "$user_cookie[1]";
            if($user_login == "" or $user_password == "") {
                $this->registry->main_class->database_close();
                setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                if(SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
                } else {
                    header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
                }
                exit();
            }
            $user_login = substr($user_login,0,25);
            $user_login = $this->registry->main_class->format_striptags($user_login);
            $user_login = $this->registry->main_class->processing_data($user_login);
            $user_password = substr($user_password,0,40);
            $user_password = $this->registry->main_class->format_striptags($user_password);
            $user_password = $this->registry->main_class->processing_data($user_password);
            $user_password = substr(substr($user_password,0,-1),1);
            $sql = "SELECT user_id, user_name, user_surname, user_login, user_password, user_active, user_activatekey FROM ".PREFIX."_users WHERE user_login=".$user_login;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
            }
            if(isset($numrows) and $numrows > 0) {
                if($this->registry->main_class->extracting_data($result->fields['4']) == $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) {
                    $this->user_id = intval($result->fields['0']);
                    $this->user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    if((!isset($this->user_name) or $this->user_name == "") and (isset($user_surname) and $user_surname != "")) {
                        $this->user_name = $user_surname;
                    } elseif((!isset($this->user_name) or $this->user_name == "") and (!isset($user_surname) or $user_surname == "")) {
                        $this->user_name = $this->registry->main_class->extracting_data($result->fields['3']);
                    } elseif((isset($this->user_name) and $this->user_name != "") and (isset($user_surname) and $user_surname != "")) {
                        $this->user_name = $this->user_name." ".$user_surname;
                    }
                    $user_activatekey = $result->fields['6'];
                    if(isset($user_activatekey) and $user_activatekey != '') {
                        $this->db->Execute("UPDATE ".PREFIX."_users SET user_activatekey = NULL,user_newpassword = NULL WHERE user_id='".$this->user_id."'");
                    }
                    $this->usernow = 1;
                } elseif($this->registry->main_class->extracting_data($result->fields['4']) == $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0) {
                    if(isset($_COOKIE['user_cookie'])) {
                        setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->assign("error","error_usernotactive");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notactive|".$this->registry->language);
                    exit();
                } elseif(($this->registry->main_class->extracting_data($result->fields['4']) != $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) or ($this->registry->main_class->extracting_data($result->fields['4']) != $user_password and $this->registry->main_class->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0)) {
                    if(isset($_COOKIE['user_cookie'])) {
                        setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->assign("error","error_userincorrectpass");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|incorrectpass|".$this->registry->language);
                    exit();
                }
            } else {
                if(isset($_COOKIE['user_cookie'])) {
                    setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->assign("error","error_usernotfind");
                $this->registry->main_class->assign("back_link","userenter");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notfind|".$this->registry->language);
                exit();
            }
        }
    }


    public function account_enter() {
        if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
            $this->registry->main_class->assign("enter_check","yes");
        } else {
            $this->registry->main_class->assign("enter_check","no");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
        $this->registry->main_class->assign("include_center","modules/account/accountenter.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|enter|".$this->registry->language);
    }


    private function account_register_check() {
        if(!defined('SYSTEMDK_ACCOUNT_REGISTRATION') or SYSTEMDK_ACCOUNT_REGISTRATION != 'yes') {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|registernotallow|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_registernotallow");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|registernotallow|".$this->registry->language);
            exit();
        }
        if(defined('SYSTEMDK_ACCOUNT_MAXUSERS') and SYSTEMDK_ACCOUNT_MAXUSERS > 0) {
            $sql = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a) as count1,(SELECT count(b.user_id) FROM ".PREFIX."_users_temp b) as count2 ".$this->registry->main_class->check_db_need_from_clause();
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows1 = intval($result->fields['0']);
                    $numrows2 = intval($result->fields['1']);
                    $numrows = $numrows1 + $numrows2;
                } else {
                    $numrows = 0;
                }
                if($numrows >= SYSTEMDK_ACCOUNT_MAXUSERS and $numrows > 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|maxuserslimit|".$this->registry->language)) {
                        $this->registry->main_class->assign("error","error_maxuserslimit");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|maxuserslimit|".$this->registry->language);
                    exit();
                }
            }
        }
    }


    public function account_register() {
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTREGISTERTITLE'));
        $this->account_register_check();
        if(isset($_GET['rules']) and intval($_GET['rules']) == 1) {
            $_POST['register_rules_accept'] = "yes";
        }
        if(defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "yes" and !isset($_POST['register_rules_accept'])) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|register|rules|".$this->registry->language)) {
                $sql = "SELECT systempage_id, systempage_title, systempage_content FROM ".PREFIX."_system_pages WHERE systempage_name='account_rules' AND systempage_lang='".$this->registry->language."'";
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                }
                if(!isset($numrows) or $numrows == 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|accountrulesnotfind|".$this->registry->language)) {
                        $this->registry->main_class->assign("error","error_accountrulesnotfind");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|accountrulesnotfind|".$this->registry->language);
                    exit();
                } else {
                    $systempage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $systempage_content = $this->registry->main_class->extracting_data($result->fields['2']);
                    $systempage_all[] = array(
                        "systempage_title" => $systempage_title,
                        "systempage_content" => $systempage_content
                    );
                    $this->registry->main_class->assign("systempage_all",$systempage_all);
                    $this->registry->main_class->assign("include_center","modules/account/account_register.html");
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|rules|".$this->registry->language);
                    exit();
                }
            } else {
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|rules|".$this->registry->language);
                exit();
            }
        }
        if(defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "yes" and isset($_POST['register_rules_accept']) and $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($_POST['register_rules_accept']) == 'no') {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|accountrulesnotaccept|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_accountrulesnotaccept");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|accountrulesnotaccept|".$this->registry->language);
            exit();
        } elseif((defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "yes" and isset($_POST['register_rules_accept']) and $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($_POST['register_rules_accept']) == 'yes') or (defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "no")) {
            $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
            if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
                $this->registry->main_class->assign("enter_check","yes");
            } else {
                $this->registry->main_class->assign("enter_check","no");
            }
            $this->registry->main_class->assign("include_center","modules/account/account_add.html");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|displayform|".$this->registry->language);
            exit();
        } else {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|accountruleserror|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_accountruleserror");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|accountruleserror|".$this->registry->language);
            exit();
        }
    }


    public function account_add_save() {
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
        if(isset($_POST['user_add_viewemail'])) {
            $user_add_viewemail = intval($_POST['user_add_viewemail']);
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTREGISTERTITLE'));
        if((!isset($_POST['user_add_name']) or !isset($_POST['user_add_surname'])or !isset($_POST['user_add_website']) or !isset($_POST['user_add_email']) or !isset($_POST['user_add_login']) or !isset($_POST['user_add_password']) or !isset($_POST['user_add_password2']) or !isset($_POST['user_add_country']) or !isset($_POST['user_add_city']) or !isset($_POST['user_add_address']) or !isset($_POST['user_add_telephone']) or !isset($_POST['user_add_icq']) or !isset($_POST['user_add_gmt']) or !isset($_POST['user_add_notes']) or !isset($_POST['user_add_viewemail']) or (!isset($_POST['captcha']) and extension_loaded("gd") and (ENTER_CHECK == "yes"))) or ($user_add_email == "" or $user_add_login == "" or $user_add_password == "" or $user_add_password2 == "" or $user_add_gmt == "" or (extension_loaded("gd") and (ENTER_CHECK == "yes") and $this->registry->main_class->format_striptags($_POST['captcha']) == ""))) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotalldata");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notalldata|".$this->registry->language);
            exit();
        }
        if(ENTER_CHECK == "yes") {
            if(extension_loaded("gd") and session_id() == "") {
                session_start();
            }
            if(extension_loaded("gd") and ((!isset($_POST["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|regcheckkode|".$this->registry->language)) {
                    $this->registry->main_class->assign("back_link","userregform");
                    $this->registry->main_class->assign("error","error_checkkode");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|regcheckkode|".$this->registry->language);
                exit();
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        $this->account_register_check();
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
        $user_add_email = $this->registry->main_class->format_striptags($user_add_email);
        if(!$this->registry->main_class->check_email($user_add_email)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|regsaveuseremail|".$this->registry->language)) {
                $this->registry->main_class->assign("back_link","userregform");
                $this->registry->main_class->assign("error","error_reguseremail");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|regsaveuseremail|".$this->registry->language);
            exit();
        }
        $user_add_email = $this->registry->main_class->processing_data($user_add_email);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$user_add_login)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|reglogin|".$this->registry->language)) {
                $this->registry->main_class->assign("back_link","userregform");
                $this->registry->main_class->assign("error","error_login");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|reglogin|".$this->registry->language);
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
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|regpass|".$this->registry->language)) {
                $this->registry->main_class->assign("back_link","userregform");
                $this->registry->main_class->assign("error","error_usererrorpass");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|regpass|".$this->registry->language);
            exit();
        }
        $sql1 = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE a.user_email=".$user_add_email." or a.user_login=".$user_add_login.") as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE b.user_email=".$user_add_email." or b.user_login=".$user_add_login.") as count2 ".$this->registry->main_class->check_db_need_from_clause();
        $insert_result = $this->db->Execute($sql1);
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
                if(defined('SYSTEMDK_ACCOUNT_ACTIVATION') and SYSTEMDK_ACCOUNT_ACTIVATION == 'no') {
                    $user_add_password = md5($user_add_password);
                    $now = $this->registry->main_class->gettime();
                    $user_add_id = $this->db->GenID(PREFIX."_users_id");
                    $sql = "INSERT INTO ".PREFIX."_users (user_id,user_name,user_surname,user_website,user_email,user_login,user_password,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail)  VALUES ($user_add_id,$user_add_name,$user_add_surname,$user_add_website,$user_add_email,$user_add_login,'$user_add_password',$user_add_country,$user_add_city,$user_add_address,$user_add_telephone,$user_add_icq,$user_add_gmt,$user_add_notes,'1',NULL,'$now','$user_add_viewemail')";
                    $insert_result = $this->db->Execute($sql);
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    } else {
                        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
                    }
                } elseif(defined('SYSTEMDK_ACCOUNT_ACTIVATION') and SYSTEMDK_ACCOUNT_ACTIVATION == 'yes') {
                    $user_add_password = md5($user_add_password);
                    $now = $this->registry->main_class->gettime();
                    mt_srand((double)microtime() * 1000000);
                    $maxran = 1000000;
                    $check_num = mt_rand(0,$maxran);
                    $systemdk_act_id = md5($check_num);
                    $user_add_id = $this->db->GenID(PREFIX."_users_temp_id");
                    $this->db->StartTrans();
                    $sql = "INSERT INTO ".PREFIX."_users_temp (user_id,user_name,user_surname,user_website,user_email,user_login,user_password,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_regdate,user_viewemail,user_checknum)  VALUES ($user_add_id,$user_add_name,$user_add_surname,$user_add_website,$user_add_email,$user_add_login,'$user_add_password',$user_add_country,$user_add_city,$user_add_address,$user_add_telephone,$user_add_icq,$user_add_gmt,$user_add_notes,'$now','$user_add_viewemail','$systemdk_act_id')";
                    $insert_result = $this->db->Execute($sql);
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    } else {
                        $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
                        $this->registry->main_class->assign("modules_account_siteurl",SITE_URL);
                        if($user_add_name != 'NULL') {
                            $this->registry->main_class->assign("modules_account_regusername",$this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data(substr(substr($user_add_name,0,-1),1))));
                        } else {
                            $this->registry->main_class->assign("modules_account_regusername",$user_add_name);
                        }
                        $modules_account_regcontent = $this->registry->main_class->fetch("modules/account/account_regmail.html");
                        $modules_account_regcontent = str_replace("modules_account_reguserlogin",$this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data(substr(substr($user_add_login,0,-1),1))),$modules_account_regcontent);
                        $modules_account_regcontent = str_replace("modules_account_reguserpassword",$this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($user_add_password2)),$modules_account_regcontent);
                        $modules_account_regcontent = str_replace("modules_account_regactid",$systemdk_act_id,$modules_account_regcontent);
                        $this->registry->mail->sendmail($this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data(substr(substr($user_add_email,0,-1),1))),$this->registry->main_class->getConfigVars('_ACCOUNTACTIVATIONMAILTITLE'),$modules_account_regcontent);
                        if($this->registry->mail->send_error) {
                            $error_message = $this->registry->mail->smtp_msg;
                            $error_code = "";
                            $error[] = array("code" => $error_code,"message" => $error_message);
                            $this->db->FailTrans();
                            $insert_result = false;
                        } else {
                            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
                        }
                    }
                    $this->db->CompleteTrans();
                } else {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|registernotallow|".$this->registry->language)) {
                        $this->registry->main_class->assign("error","error_registernotallow");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|registernotallow|".$this->registry->language);
                    exit();
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|regfinduser|".$this->registry->language)) {
                    $this->registry->main_class->assign("back_link","userregform");
                    $this->registry->main_class->assign("error","error_findsuchuser");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|regfinduser|".$this->registry->language);
                exit();
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("errortext",$error);
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotupdate");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language);
            exit();
        } else {
            if(defined('SYSTEMDK_ACCOUNT_ACTIVATION') and SYSTEMDK_ACCOUNT_ACTIVATION == 'yes') {
                if(defined('SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS') and intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS) != 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|register|addactlimok|".$this->registry->language)) {
                        $this->registry->main_class->assign("modules_account_useractdayslim",intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS));
                        $this->registry->main_class->assign("error","user_addactok");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|addactlimok|".$this->registry->language);
                } else {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|register|addactok|".$this->registry->language)) {
                        $this->registry->main_class->assign("modules_account_useractdayslim","no");
                        $this->registry->main_class->assign("error","user_addactok");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|addactok|".$this->registry->language);
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|register|addok|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","user_addok");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|addok|".$this->registry->language);
            }
        }
    }


    public function account_add_confirm() {
        if(isset($_POST['modules_account_confcode'])) {
            $_GET['act_code'] = $this->registry->main_class->format_striptags(trim($_POST['modules_account_confcode']));
            $modules_account_confcode = 1;
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTREGISTERCONFTITLE'));
        if(isset($_GET['act_code'])) {
            $act_code = $this->registry->main_class->format_striptags(trim($_GET['act_code']));
        }
        if(isset($modules_account_confcode) and $modules_account_confcode == 1) {
            if(ENTER_CHECK == "yes") {
                if(extension_loaded("gd") and session_id() == "") {
                    session_start();
                }
                if(extension_loaded("gd")  and ((!isset($_POST["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                    if(isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|confregcheckkode|".$this->registry->language)) {
                        $this->registry->main_class->assign("back_link","confuserreg");
                        $this->registry->main_class->assign("error","error_checkkode");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|confregcheckkode|".$this->registry->language);
                    exit();
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
        }
        if(!isset($_GET['act_code']) or $act_code == "") {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language)) {
                $this->registry->main_class->assign("error","user_noactcode");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language);
            exit();
        }
        $sql = "SELECT user_id FROM ".PREFIX."_users_temp WHERE user_checknum = '".$act_code."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
        }
        if(!isset($numrows) or $numrows == 0) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language)) {
                $this->registry->main_class->assign("error","user_noactcode");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language);
            exit();
        } elseif(isset($modules_account_confcode) and $modules_account_confcode == 1 and isset($numrows) and $numrows > 0) {
            $user_add_id = $this->db->GenID(PREFIX."_users_id");
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_users(user_id,user_name,user_surname,user_website,user_email,user_login,user_password,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail) SELECT "."'".$user_add_id."'"." as user_id,user_name,user_surname,user_website,user_email,user_login,user_password,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,"."'1'".",NULL,user_regdate,user_viewemail FROM ".PREFIX."_users_temp WHERE user_checknum = '".$act_code."'";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql2 = "DELETE FROM ".PREFIX."_users_temp WHERE user_checknum = '".$act_code."'";
            $result2 = $this->db->Execute($sql2);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            } else {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
            }
            $this->db->CompleteTrans();
            if($result === false or $result2 === false) {
                $this->registry->main_class->assign("errortext",$error);
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","error_usernotupdate");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language);
            } else {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|register|addok|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","user_addok");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|addok|".$this->registry->language);
            }
            exit();
        } else {
            $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
            if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
                $this->registry->main_class->assign("enter_check","yes");
            } else {
                $this->registry->main_class->assign("enter_check","no");
            }
            $this->registry->main_class->assign("include_center","modules/account/account_addconf.html");
            $this->registry->main_class->assign("modules_account_confcode",$act_code);
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|register|displayformconf|".$this->registry->language);
            exit();
        }
    }


    public function account_lostpass() {
        if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
            $this->registry->main_class->assign("enter_check","yes");
        } else {
            $this->registry->main_class->assign("enter_check","no");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ACCOUNTLOSTPASSFORM'));
        $this->registry->main_class->assign("include_center","modules/account/account_lostpass.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|lostpass|".$this->registry->language);
    }


    public function account_lostpassconf() {
        if(isset($_POST['lost_loginoremail'])) {
            $lost_loginoremail = trim($_POST['lost_loginoremail']);
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTLOSTPASSTITLE'));
        if(!isset($_POST['lost_loginoremail']) or (!isset($_POST['captcha']) and extension_loaded("gd") and (ENTER_CHECK == "yes")) or ($lost_loginoremail == "" or (extension_loaded("gd") and (ENTER_CHECK == "yes") and $this->registry->main_class->format_striptags($_POST['captcha']) == ""))) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotalldata");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notalldata|".$this->registry->language);
            exit();
        }
        if(ENTER_CHECK == "yes") {
            if(extension_loaded("gd") and session_id() == "") {
                session_start();
            }
            if(extension_loaded("gd") and ((!isset($_POST["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|lostpasscheckkode|".$this->registry->language)) {
                    $this->registry->main_class->assign("back_link","userlostpass");
                    $this->registry->main_class->assign("error","error_checkkode");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|lostpasscheckkode|".$this->registry->language);
                exit();
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        $lost_loginoremail = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->format_striptags($lost_loginoremail));
        $lost_loginoremail = $this->registry->main_class->processing_data($lost_loginoremail);
        $sql = "SELECT user_id,user_login,user_email,user_name FROM ".PREFIX."_users WHERE user_login=".$lost_loginoremail." or user_email=".$lost_loginoremail;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
        }
        if(!isset($numrows) or $numrows == 0) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|lostpassnotfind|".$this->registry->language)) {
                $this->registry->main_class->assign("back_link","lostpass");
                $this->registry->main_class->assign("error","error_usernotfind");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|lostpassnotfind|".$this->registry->language);
            exit();
        }
        $user_change_id = intval($result->fields['0']);
        $user_change_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $user_change_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $user_change_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        mt_srand((double)microtime() * 1000000);
        $maxran = 1000000;
        $check_num = mt_rand(0,$maxran);
        $systemdk_act_id = md5($check_num);
        $arr = array(
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'v',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'V',
            'X',
            'Y',
            'Z',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0',
            '!',
            '?',
        );
        $user_new_password = "";
        for($i = 0;$i < 10;$i++) {
            $index = rand(0,count($arr) - 1);
            $user_new_password .= $arr[$index];
        }
        $user_new_password = substr(substr($this->registry->main_class->processing_data($this->registry->main_class->format_striptags($user_new_password),'need'),0,-1),1);
        $user_new_password_send = $user_new_password;
        $user_new_password = md5($user_new_password);
        $this->db->StartTrans();
        $sql = "UPDATE ".PREFIX."_users SET user_activatekey='".$systemdk_act_id."',user_newpassword='".$user_new_password."' WHERE user_id='".$user_change_id."'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        } else {
            $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
            $this->registry->main_class->assign("modules_account_siteurl",SITE_URL);
            if($user_change_name != '') {
                $this->registry->main_class->assign("modules_account_regusername",$this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($user_change_name)));
            } else {
                $this->registry->main_class->assign("modules_account_regusername","no");
            }
            $modules_account_regcontent = $this->registry->main_class->fetch("modules/account/account_lostpassmail.html");
            $modules_account_regcontent = str_replace("modules_account_reguserlogin",$user_change_login,$modules_account_regcontent);
            $modules_account_regcontent = str_replace("modules_account_reguserpassword",$user_new_password_send,$modules_account_regcontent);
            $modules_account_regcontent = str_replace("modules_account_regactid",$systemdk_act_id,$modules_account_regcontent);
            $this->registry->mail->sendmail($user_change_login,$this->registry->main_class->getConfigVars('_ACCOUNTLOSTPASSMAILTITLE'),$modules_account_regcontent);
            if($this->registry->mail->send_error) {
                $error_message = $this->registry->mail->smtp_msg;
                $error_code = "";
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->db->FailTrans();
                $result = false;
            }
        }
        $this->db->CompleteTrans();
        if($result === false) {
            $this->registry->main_class->assign("errortext",$error);
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotupdate");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|lostpassok|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotupdate2");
                $this->registry->main_class->assign("back_link","lostpass");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|lostpassok|".$this->registry->language);
            exit();
        } else {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|sendpassok|".$this->registry->language)) {
                $this->registry->main_class->assign("error","user_sendpassok");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|sendpassok|".$this->registry->language);
        }
    }


    public function account_newpassconf() {
        if(isset($_POST['modules_account_confcode'])) {
            $_GET['act_code'] = $this->registry->main_class->format_striptags(trim($_POST['modules_account_confcode']));
            $modules_account_confcode = 1;
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTLOSTPASSTITLE'));
        if(isset($_GET['act_code'])) {
            $act_code = $this->registry->main_class->format_striptags(trim($_GET['act_code']));
        }
        if(isset($modules_account_confcode) and $modules_account_confcode == 1) {
            if(ENTER_CHECK == "yes") {
                if(extension_loaded("gd") and session_id() == "") {
                    session_start();
                }
                if(extension_loaded("gd")  and ((!isset($_POST["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                    if(isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|confregcheckkode|".$this->registry->language)) {
                        $this->registry->main_class->assign("back_link","confuserreg");
                        $this->registry->main_class->assign("error","error_checkkode");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|confregcheckkode|".$this->registry->language);
                    exit();
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
        }
        if(!isset($_GET['act_code']) or $act_code == "") {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language)) {
                $this->registry->main_class->assign("error","user_noactcode");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language);
            exit();
        }
        $sql = "SELECT user_id,user_newpassword FROM ".PREFIX."_users WHERE user_activatekey = '".$act_code."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
        }
        if(!isset($numrows) or $numrows == 0) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language)) {
                $this->registry->main_class->assign("error","user_noactcode");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|noactcode|".$this->registry->language);
            exit();
        } elseif(isset($modules_account_confcode) and $modules_account_confcode == 1 and isset($numrows) and $numrows > 0) {
            $user_change_id = intval($result->fields['0']);
            $user_new_password = $result->fields['1'];
            $sql = "UPDATE ".PREFIX."_users SET user_password = '$user_new_password',user_activatekey = NULL,user_newpassword = NULL WHERE user_id='".$user_change_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result === false) {
                $this->registry->main_class->assign("errortext",$error);
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","error_usernotupdate");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language);
            } elseif($result !== false and $num_result == 0) {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|newpassconfok|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","error_usernotupdate2");
                    $this->registry->main_class->assign("back_link","confuserreg");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|newpassconfok|".$this->registry->language);
                exit();
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|newpassok|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","user_newpassok");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|newpassok|".$this->registry->language);
            }
            exit();
        } else {
            $this->registry->main_class->configLoad($this->registry->language."/modules/account/account.conf");
            if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
                $this->registry->main_class->assign("enter_check","yes");
            } else {
                $this->registry->main_class->assign("enter_check","no");
            }
            $this->registry->main_class->assign("include_center","modules/account/account_lostpassconf.html");
            $this->registry->main_class->assign("modules_account_confcode",$act_code);
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|lostpassconf|".$this->registry->language);
            exit();
        }
    }


    public function mainuser() {
        if(isset($this->user_id)) {
            $user_id = intval($this->user_id);
        }
        if(!isset($this->user_id) or (isset($user_id) and $user_id == 0)) {
            $this->registry->main_class->database_close();
            setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR.$this->registry->sitelang."/account/");
            } else {
                header("Location: /".SITE_DIR."index.php?path=account&lang=".$this->registry->sitelang);
            }
            exit();
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|show|".$user_id."|".$this->registry->language)) {
            $sql = "SELECT user_name,user_surname,user_website,user_email,user_login,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail,user_id FROM ".PREFIX."_users WHERE user_id='".$user_id."' AND user_active='1'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['17'])) {
                    $numrows = intval($result->fields['17']);
                } else {
                    $numrows = 0;
                }
            }
            if(!isset($numrows) or $numrows == 0) {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|notfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","error_usernotfind");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notfind|".$this->registry->language);
                exit();
            } else {
                $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['0']));
                $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $user_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                $user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $user_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $user_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                $user_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                $user_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $user_active = intval($result->fields['13']);
                $user_activenotes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                $user_regdate = intval($result->fields['15']);
                $user_regdate = date("d.m.y H:i",$user_regdate);
                $user_viewemail = intval($result->fields['16']);
                $user_all[] = array(
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
                if(isset($this->user_name)) {
                    $this->registry->main_class->assign("user_name",$this->user_name);
                }
                $this->registry->main_class->assign("include_center","modules/account/user.html");
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|show|".$user_id."|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|show|".$user_id."|".$this->registry->language);
        }
    }


    public function account_edit() {
        if(isset($this->user_id)) {
            $user_id = intval($this->user_id);
        }
        if(!isset($this->user_id) or (isset($user_id) and $user_id == 0)) {
            $this->registry->main_class->database_close();
            setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR.$this->registry->sitelang."/account/");
            } else {
                header("Location: /".SITE_DIR."index.php?path=account&lang=".$this->registry->sitelang);
            }
            exit();
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTEDITTITLE'));
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|edit|".$user_id."|".$this->registry->language)) {
            $sql = "SELECT user_name,user_surname,user_website,user_email,user_login,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail,user_id FROM ".PREFIX."_users WHERE user_id='".$user_id."' AND user_active='1'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['17'])) {
                    $numrows = intval($result->fields['17']);
                } else {
                    $numrows = 0;
                }
            }
            if(!isset($numrows) or $numrows == 0) {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|notfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","error_usernotfind");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notfind|".$this->registry->language);
                exit();
            } else {
                $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['0']));
                $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $user_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                $user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $user_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $user_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                $user_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                $user_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $user_active = intval($result->fields['13']);
                $user_activenotes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                $user_regdate = intval($result->fields['15']);
                $user_regdate = date("d.m.y H:i",$user_regdate);
                $user_viewemail = intval($result->fields['16']);
                $user_all[] = array(
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
                if(isset($this->user_name)) {
                    $this->registry->main_class->assign("user_name",$this->user_name);
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->assign("include_center","modules/account/user_edit.html");
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|edit|".$user_id."|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|edit|".$user_id."|".$this->registry->language);
        }
    }


    public function account_edit_save() {
        if(isset($this->user_id)) {
            $user_id = intval($this->user_id);
        }
        if(!isset($this->user_id) or (isset($user_id) and $user_id == 0)) {
            $this->registry->main_class->database_close();
            setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR.$this->registry->sitelang."/account/");
            } else {
                header("Location: /".SITE_DIR."index.php?path=account&lang=".$this->registry->sitelang);
            }
            exit();
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTEDITTITLE'));
        $user_edit_id = $user_id;
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
        if(isset($_POST['user_edit_viewemail'])) {
            $user_edit_viewemail = intval($_POST['user_edit_viewemail']);
        }
        if((!isset($_POST['user_edit_name']) or !isset($_POST['user_edit_surname'])or !isset($_POST['user_edit_website']) or !isset($_POST['user_edit_email']) or !isset($_POST['user_edit_newpassword']) or !isset($_POST['user_edit_newpassword2']) or !isset($_POST['user_edit_country']) or !isset($_POST['user_edit_city']) or !isset($_POST['user_edit_address']) or !isset($_POST['user_edit_telephone']) or !isset($_POST['user_edit_icq']) or !isset($_POST['user_edit_gmt']) or !isset($_POST['user_edit_notes']) or !isset($_POST['user_edit_viewemail'])) or ($user_edit_email == "" or $user_edit_gmt == "" or $user_edit_viewemail === "")) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotalldata");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|notalldata|".$this->registry->language);
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
        $user_edit_email = $this->registry->main_class->format_striptags($user_edit_email);
        if(!$this->registry->main_class->check_email($user_edit_email)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|editsaveuseremail|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_reguseremail");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|editsaveuseremail|".$this->registry->language);
            exit();
        }
        $user_edit_email = $this->registry->main_class->processing_data($user_edit_email);
        $user_edit_gmt = $this->registry->main_class->format_striptags($user_edit_gmt);
        $user_edit_gmt = $this->registry->main_class->processing_data($user_edit_gmt);
        if(isset($user_edit_newpassword) and $user_edit_newpassword != "") {
            $user_edit_newpassword = $this->registry->main_class->format_striptags($user_edit_newpassword);
            $user_edit_newpassword = $this->registry->main_class->processing_data($user_edit_newpassword);
            $user_edit_newpassword = substr(substr($user_edit_newpassword,0,-1),1);
            $user_edit_newpassword2 = $this->registry->main_class->format_striptags($user_edit_newpassword2);
            $user_edit_newpassword2 = $this->registry->main_class->processing_data($user_edit_newpassword2);
            $user_edit_newpassword2 = substr(substr($user_edit_newpassword2,0,-1),1);
            if($user_edit_newpassword !== $user_edit_newpassword2 or $user_edit_newpassword == "") {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|pass|".$this->registry->language)) {
                    $this->registry->main_class->assign("error","error_usererrorpass");
                    $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|pass|".$this->registry->language);
                exit();
            } else {
                $sql0 = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE a.user_id<>'".$user_edit_id."' and a.user_email=".$user_edit_email.") as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE b.user_email=".$user_edit_email.") as count2 ".$this->registry->main_class->check_db_need_from_clause();
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
                        $sql = "UPDATE ".PREFIX."_users SET user_name=$user_edit_name,user_surname=$user_edit_surname,user_website=$user_edit_website,user_email=$user_edit_email,user_password='$user_edit_password',user_country=$user_edit_country,user_city=$user_edit_city,user_address=$user_edit_address,user_telephone=$user_edit_telephone,user_icq=$user_edit_icq,user_gmt=$user_edit_gmt,user_notes=$user_edit_notes,user_viewemail='$user_edit_viewemail' WHERE user_id='$user_edit_id'";
                        $insert_result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($insert_result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } else {
                            setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                        }
                    } else {
                        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|findsuchuser|".$this->registry->language)) {
                            $this->registry->main_class->assign("error","error_usererrorfindsuchuser");
                            $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|findsuchuser|".$this->registry->language);
                        exit();
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        } else {
            $sql0 = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE a.user_id<>'".$user_edit_id."' and a.user_email=".$user_edit_email.") as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE b.user_email=".$user_edit_email.") as count2 ".$this->registry->main_class->check_db_need_from_clause();
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
                    $sql = "UPDATE ".PREFIX."_users SET user_name=$user_edit_name,user_surname=$user_edit_surname,user_website=$user_edit_website,user_email=$user_edit_email,user_country=$user_edit_country,user_city=$user_edit_city,user_address=$user_edit_address,user_telephone=$user_edit_telephone,user_icq=$user_edit_icq,user_gmt=$user_edit_gmt,user_notes=$user_edit_notes,user_viewemail='$user_edit_viewemail' WHERE user_id='$user_edit_id'";
                    $insert_result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|findsuchuser|".$this->registry->language)) {
                        $this->registry->main_class->assign("error","error_usererrorfindsuchuser");
                        $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|findsuchuser|".$this->registry->language);
                    exit();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("errortext",$error);
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotupdate");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|sqlerror|".$this->registry->language);
            exit();
        } elseif($insert_result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("error","error_usernotupdate2");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|ok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|edit|".$user_edit_id);
            $this->registry->main_class->systemdk_clearcache("modules|account|show|".$user_edit_id);
            $this->registry->main_class->systemdk_clearcache("modules|account|edit|".$user_edit_id);
            $this->registry->main_class->systemdk_clearcache("homepage");
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("error","user_editok");
                $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|editok|".$this->registry->language);
        }
    }


    public function account_logout() {
        if(isset($_COOKIE['user_cookie'])) {
            setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|exit|".$this->registry->language)) {
            $this->registry->main_class->assign("error","userexit");
            $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|exit|".$this->registry->language);
    }


    public function account_only_for_unreg() {
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|account|error|onlyforunreg|".$this->registry->language)) {
            $this->registry->main_class->assign("error","error_onlyforunreg");
            $this->registry->main_class->assign("include_center","modules/account/accounterror.html");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULEACCOUNTTITLE'));
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|account|error|onlyforunreg|".$this->registry->language);
    }


    public function is_usernow() {
        return $this->usernow;
    }
}
?>
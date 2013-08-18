<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class feedback extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->modules_feedback_autodelete();
    }


    public function index() {
        if(defined('SYSTEMDK_FEEDBACK_RECEIVER') and SYSTEMDK_FEEDBACK_RECEIVER != 'default') {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|display|".$this->registry->language)) {
                $sql = "SELECT receiver_id,receiver_name FROM ".PREFIX."_mail_receivers_".$this->registry->sitelang." WHERE receiver_active = '1'";
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        while(!$result->EOF) {
                            $receiver_id = intval($result->fields['0']);
                            $receiver_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                            $receivers_all[] = array("receiver_id" => $receiver_id,"receiver_name" => $receiver_name);
                            $result->MoveNext();
                        }
                        $this->registry->main_class->assign("receivers_all",$receivers_all);
                    } else {
                        $this->registry->main_class->assign("receivers_all","default");
                    }
                } else {
                    $this->registry->main_class->assign("receivers_all","default");
                }
            }
        } else {
            $this->registry->main_class->assign("receivers_all","default");
        }
        if($this->registry->main_class->is_user_in_mainfunc()) {
            $row = $this->registry->main_class->get_user_info($this->registry->user);
            if(isset($row) and $row != "") {
                $feedback_user_id = intval($row['0']);
                $feedback_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $feedback_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $feedback_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                if((!isset($feedback_user_name) or $feedback_user_name == "") and (isset($feedback_user_surname) and $feedback_user_surname != "")) {
                    $feedback_user_name = $feedback_user_surname;
                } elseif((!isset($feedback_user_name) or $feedback_user_name == "") and (!isset($feedback_user_surname) or $feedback_user_surname == "")) {
                    $feedback_user_name = $this->registry->main_class->extracting_data($row['5']);
                }
            } else {
                $feedback_user_name = "no";
                $feedback_user_id = "no";
                $feedback_user_email = "no";
            }
            $this->registry->main_class->assign("modules_feedback_sendername",$feedback_user_name);
            $this->registry->main_class->assign("modules_feedback_senderid",$feedback_user_id);
            $this->registry->main_class->assign("modules_feedback_senderemail",$feedback_user_email);
        } else {
            $this->registry->main_class->assign("modules_feedback_sendername","no");
            $this->registry->main_class->assign("modules_feedback_senderid","no");
            $this->registry->main_class->assign("modules_feedback_senderemail","no");
        }
        $this->registry->main_class->configLoad($this->registry->language."/modules/feedback/feedback.conf");
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_FEEDBACKMODULETITLE'));
        if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
            $this->registry->main_class->assign("enter_check","yes");
        } else {
            $this->registry->main_class->assign("enter_check","no");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/feedback/feedback.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|display|".$this->registry->language);
    }


    public function feedback_send() {
        if(isset($_POST['feedback_user_name'])) {
            $feedback_user_name = trim($_POST['feedback_user_name']);
        }
        if(isset($_POST['feedback_user_id'])) {
            $feedback_user_id = intval($_POST['feedback_user_id']);
        }
        if(isset($_POST['feedback_user_email'])) {
            $feedback_user_email = trim($_POST['feedback_user_email']);
        }
        if(isset($_POST['feedback_towhom'])) {
            $feedback_towhom = intval($_POST['feedback_towhom']);
        }
        if(isset($_POST['feedback_subject'])) {
            $feedback_subject = trim($_POST['feedback_subject']);
        }
        if(isset($_POST['feedback_message'])) {
            $feedback_message = trim($_POST['feedback_message']);
        }
        $this->registry->main_class->configLoad($this->registry->language."/modules/feedback/feedback.conf");
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_FEEDBACKMODULETITLE'));
        if((!isset($_POST['feedback_user_name']) or !isset($_POST['feedback_user_id']) or !isset($_POST['feedback_user_email']) or (!isset($_POST['feedback_towhom']) and SYSTEMDK_FEEDBACK_RECEIVER == 'list') or  !isset($_POST['feedback_subject']) or !isset($_POST['feedback_message'])) or ($feedback_user_name == "" or $feedback_user_email == "" or (isset($feedback_towhom) and $feedback_towhom == 0 and SYSTEMDK_FEEDBACK_RECEIVER == 'list') or $feedback_subject == "" or $feedback_message == "")) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("message","notalldata");
                $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|notalldata|".$this->registry->language);
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
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|checkkode|".$this->registry->language)) {
                    $this->registry->main_class->assign("message","checkkode");
                    $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|checkkode|".$this->registry->language);
                exit();
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        if(defined('SYSTEMDK_IPANTISPAM') and SYSTEMDK_IPANTISPAM == "yes") {
            $mail_date_check = $this->registry->main_class->gettime() - 86400;
            $sql = "SELECT count(*) FROM ".PREFIX."_mail_sendlog WHERE mail_ip = '".$this->registry->ip_addr."' and mail_module = 'feedback' and mail_date > ".$mail_date_check;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows >= intval(SYSTEMDK_IPANTISPAM_NUM) and intval(SYSTEMDK_IPANTISPAM_NUM) > 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|maillimit|".$this->registry->language)) {
                        $this->registry->main_class->assign("message","maillimit");
                        $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|maillimit|".$this->registry->language);
                    exit();
                }
            }
        }
        $feedback_user_email = $this->registry->main_class->format_striptags($feedback_user_email);
        if(!$this->registry->main_class->check_email($feedback_user_email)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|useremail|".$this->registry->language)) {
                $this->registry->main_class->assign("message","useremail");
                $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|useremail|".$this->registry->language);
            exit();
        }
        $feedback_user_email = $this->registry->main_class->processing_data($feedback_user_email);
        $feedback_user_name = $this->registry->main_class->format_striptags($feedback_user_name);
        $feedback_user_name = $this->registry->main_class->processing_data($feedback_user_name);
        $feedback_subject = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($feedback_subject);
        $feedback_message = $this->registry->main_class->format_striptags($feedback_message);
        $feedback_message = $this->registry->main_class->checkneed_stripslashes($feedback_message);
        if($this->registry->main_class->is_user_in_mainfunc()) {
            $row = $this->registry->main_class->get_user_info($this->registry->user);
            if(isset($row) and $row != "") {
                $feedback_user_id = intval($row['0']);
                $feedback_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $feedback_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $feedback_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                if((!isset($feedback_user_name) or $feedback_user_name == "") and (isset($feedback_user_surname) and $feedback_user_surname != "")) {
                    $feedback_user_name = $feedback_user_surname;
                } elseif((!isset($feedback_user_name) or $feedback_user_name == "") and (!isset($feedback_user_surname) or $feedback_user_surname == "")) {
                    $feedback_user_name = $this->registry->main_class->extracting_data($row['5']);
                }
                $feedback_user_login = $this->registry->main_class->extracting_data($row['5']);
                $feedback_user_login = $this->registry->main_class->format_striptags($feedback_user_login);
                $feedback_user_login = $this->registry->main_class->processing_data($feedback_user_login);
                $feedback_user_name = $this->registry->main_class->format_striptags($feedback_user_name);
                $feedback_user_name = $this->registry->main_class->processing_data($feedback_user_name);
                $feedback_user_email = $this->registry->main_class->format_striptags($feedback_user_email);
                $feedback_user_email = $this->registry->main_class->processing_data($feedback_user_email);
            } else {
                $feedback_user_login = 'NULL';
                $feedback_user_name = "no";
                $feedback_user_id = "no";
                $feedback_user_email = "no";
            }
        } else {
            $sql = "SELECT (SELECT count(a.user_id) FROM ".PREFIX."_users a WHERE lower(a.user_email) = ".strtolower($feedback_user_email)." or lower(a.user_login) = ".strtolower($feedback_user_name).") as count1, (SELECT count(b.user_id) FROM ".PREFIX."_users_temp b WHERE lower(b.user_email) = ".strtolower($feedback_user_email)." or lower(b.user_login) = ".strtolower($feedback_user_name).") as count2 ".$this->registry->main_class->check_db_need_from_clause();
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if(isset($result->fields['1'])) {
                    $numrows2 = intval($result->fields['1']);
                } else {
                    $numrows2 = 0;
                }
                if($numrows > 0 or $numrows2 > 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|findsuchuser|".$this->registry->language)) {
                        $this->registry->main_class->assign("message","findsuchuser");
                        $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|findsuchuser|".$this->registry->language);
                    exit();
                }
            }
            $feedback_user_id = "no";
            $feedback_user_login = 'NULL';
        }
        $feedback_message_length = strlen($feedback_message);
        if($feedback_message_length > 20000) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|messagelength|".$this->registry->language)) {
                $this->registry->main_class->assign("message","messagelength");
                $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|messagelength|".$this->registry->language);
            exit();
        }
        if(defined('SYSTEMDK_FEEDBACK_RECEIVER') and SYSTEMDK_FEEDBACK_RECEIVER != 'default') {
            $sql = "SELECT receiver_id,receiver_name,receiver_email FROM ".PREFIX."_mail_receivers_".$this->registry->sitelang." WHERE receiver_id = '".$feedback_towhom."' and receiver_active = '1'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $receiver_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $receiver_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                } else {
                    $receiver_name = $this->registry->main_class->getConfigVars('_FEEDBACKADMINNAME');
                    $receiver_email = trim(ADMIN_EMAIL);
                }
            } else {
                $receiver_name = $this->registry->main_class->getConfigVars('_FEEDBACKADMINNAME');
                $receiver_email = trim(ADMIN_EMAIL);
            }
        } else {
            $receiver_name = $this->registry->main_class->getConfigVars('_FEEDBACKADMINNAME');
            $receiver_email = trim(ADMIN_EMAIL);
        }
        $this->registry->main_class->assign("modules_feedback_receivername",$receiver_name);
        $this->registry->main_class->assign("modules_feedback_siteurl",trim(SITE_URL));
        $this->registry->main_class->assign("modules_feedback_sendername",$this->registry->main_class->extracting_data(substr(substr($feedback_user_name,0,-1),1)));
        $this->registry->main_class->assign("modules_feedback_senderip",$this->registry->ip_addr);
        $this->registry->main_class->assign("modules_feedback_senderid",$feedback_user_id);
        $modules_feedback_content = $this->registry->main_class->fetch("modules/feedback/feedback_mail.html");
        $modules_feedback_content = str_replace("modules_feedback_message",$feedback_message,$modules_feedback_content);
        $from = $this->registry->main_class->extracting_data(substr(substr($feedback_user_email,0,-1),1));
        if(!$this->registry->main_class->check_email($from)) {
            $this->registry->mail->from = trim(ADMIN_EMAIL);
        } else {
            $this->registry->mail->from = $from;
        }
        $this->registry->mail->sendmail($receiver_email,$feedback_subject,$modules_feedback_content);
        if($this->registry->mail->send_error) {
            $error_message = $this->registry->mail->smtp_msg;
            $error_code = "";
            $error[] = array("code" => $error_code,"message" => $error_message);
            $result = false;
        } else {
            $now = $this->registry->main_class->gettime();
            $mail_add_id = $this->db->GenID(PREFIX."_mail_sendlog_id");
            $sql = "INSERT INTO ".PREFIX."_mail_sendlog (mail_id,mail_user,mail_date,mail_module,mail_ip) VALUES ($mail_add_id,$feedback_user_login,$now,'feedback','".$this->registry->ip_addr."')";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->registry->main_class->assign("errortext",$error);
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("message","notinsert");
                $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|error|sqlerror|".$this->registry->language);
            exit();
        } else {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|feedback|sendok|".$this->registry->language)) {
                $this->registry->main_class->assign("message","sendok");
                $this->registry->main_class->assign("include_center","modules/feedback/feedback_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|feedback|sendok|".$this->registry->language);
        }
    }
}

?>
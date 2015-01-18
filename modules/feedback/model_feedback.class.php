<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class feedback extends model_base {


    private $error;
    private $error_array;
    private $result;
    private $send_mail_data;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->process_autodelete();
    }


    public function process_autodelete() {
        $systemdk_feedback_maxsavehistdays = $this->registry->main_class->get_time() - 86400;
        $this->db->Execute("DELETE FROM ".PREFIX."_mail_sendlog WHERE mail_module = 'feedback' and mail_date < ".$systemdk_feedback_maxsavehistdays);
    }


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index($get_receivers = true) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(defined('SYSTEMDK_FEEDBACK_RECEIVER') and SYSTEMDK_FEEDBACK_RECEIVER != 'default' and $get_receivers) {
            $sql = "SELECT receiver_id,receiver_name FROM ".PREFIX."_mail_receivers_".$this->registry->sitelang." WHERE receiver_active = '1'";
            $result = $this->db->Execute($sql);
            $this->result['receivers_all'] = 'default';
            if($result) {
                if(isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if($row_exist > 0) {
                    while(!$result->EOF) {
                        $receiver_id = intval($result->fields['0']);
                        $receiver_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $receivers_all[] = array("receiver_id" => $receiver_id,"receiver_name" => $receiver_name);
                        $result->MoveNext();
                    }
                    $this->result['receivers_all'] = $receivers_all;
                }
            }
        } elseif(!defined('SYSTEMDK_FEEDBACK_RECEIVER') or SYSTEMDK_FEEDBACK_RECEIVER == 'default') {
            $this->result['receivers_all'] = 'default';
        }
        if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
            $this->result['enter_check'] = 'yes';
        } else {
            $this->result['enter_check'] = 'no';
        }
        $this->result['modules_feedback_senderid'] = 'no';
        $this->result['modules_feedback_sendername'] = 'no';
        $this->result['modules_feedback_senderemail'] = 'no';
        if($this->registry->main_class->is_user()) {
            $row = $this->registry->main_class->get_user_info();
            if(!empty($row) and intval($row['0']) > 0) {
                $feedback_user_id = intval($row['0']);
                $feedback_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $feedback_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $feedback_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                if((!isset($feedback_user_name) or $feedback_user_name == "") and (isset($feedback_user_surname) and $feedback_user_surname != "")) {
                    $feedback_user_name = $feedback_user_surname;
                } elseif((!isset($feedback_user_name) or $feedback_user_name == "") and (!isset($feedback_user_surname) or $feedback_user_surname == "")) {
                    $feedback_user_name = $this->registry->main_class->extracting_data($row['5']);
                }
                $this->result['modules_feedback_senderid'] = $feedback_user_id;
                $this->result['modules_feedback_sendername'] = $feedback_user_name;
                $this->result['modules_feedback_senderemail'] = $feedback_user_email;
            }
        }
    }


    public function feedback_send($post_array,$process_check = true,$feedback_content = false) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if($process_check) {
            if(!empty($post_array)) {
                foreach($post_array as $key => $value) {
                    $keys = array(
                        'feedback_user_name',
                        'feedback_user_email',
                        'feedback_subject',
                        'feedback_message',
                        'captcha'
                    );
                    $keys2 = array(
                        'feedback_user_id',
                        'feedback_towhom'
                    );
                    if(in_array($key,$keys)) {
                        $data_array[$key] = trim($value);
                    }
                    if(in_array($key,$keys2)) {
                        $data_array[$key] = intval($value);
                    }
                }
            }
            if((!isset($post_array['feedback_user_name']) or !isset($post_array['feedback_user_id']) or !isset($post_array['feedback_user_email']) or (!isset($post_array['feedback_towhom']) and SYSTEMDK_FEEDBACK_RECEIVER == 'list') or !isset($post_array['feedback_subject']) or !isset($post_array['feedback_message'])) or ($data_array['feedback_user_name'] == "" or $data_array['feedback_user_email'] == "" or (isset($data_array['feedback_towhom']) and $data_array['feedback_towhom'] == 0 and SYSTEMDK_FEEDBACK_RECEIVER == 'list') or $data_array['feedback_subject'] == "" or $data_array['feedback_message'] == "")) {
                $this->error = 'not_all_data';
                return;
            }
            if(ENTER_CHECK == "yes") {
                if(extension_loaded("gd") and (!isset($data_array["captcha"]) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags(trim($data_array["captcha"]))) or !isset($_SESSION["captcha"]))) {
                    if(isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    $this->error = 'check_code';
                    return;
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
            if(defined('SYSTEMDK_IPANTISPAM') and SYSTEMDK_IPANTISPAM == "yes") {
                $mail_date_check = $this->registry->main_class->get_time() - 86400;
                $sql = "SELECT count(*) FROM ".PREFIX."_mail_sendlog WHERE mail_ip = '".$this->registry->ip_addr."' and mail_module = 'feedback' and mail_date > ".$mail_date_check;
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $num_rows = intval($result->fields['0']);
                    } else {
                        $num_rows = 0;
                    }
                    if($num_rows >= intval(SYSTEMDK_IPANTISPAM_NUM) and intval(SYSTEMDK_IPANTISPAM_NUM) > 0) {
                        $this->error = 'mail_limit';
                        return;
                    }
                }
            }
            $data_array['feedback_user_email'] = $this->registry->main_class->format_striptags($data_array['feedback_user_email']);
            if(!$this->registry->main_class->check_email($data_array['feedback_user_email'])) {
                $this->error = 'user_email';
                return;
            }
            $data_array['feedback_user_email'] = $this->registry->main_class->processing_data($data_array['feedback_user_email']);
            $data_array['feedback_user_name'] = $this->registry->main_class->format_striptags($data_array['feedback_user_name']);
            $data_array['feedback_user_name'] = $this->registry->main_class->processing_data($data_array['feedback_user_name']);
            $data_array['feedback_subject'] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($data_array['feedback_subject']);
            $data_array['feedback_message'] = $this->registry->main_class->format_striptags($data_array['feedback_message']);
            $data_array['feedback_message'] = $this->registry->main_class->checkneed_stripslashes($data_array['feedback_message']);
            if($this->registry->main_class->is_user()) {
                $row = $this->registry->main_class->get_user_info();
                if(!empty($row) and intval($row['0']) > 0) {
                    $data_array['feedback_user_id'] = intval($row['0']);
                    $data_array['feedback_user_name'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                    $data_array['feedback_user_surname'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                    $data_array['feedback_user_email'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                    if(empty($data_array['feedback_user_name']) and !empty($data_array['feedback_user_surname'])) {
                        $data_array['feedback_user_name'] = $data_array['feedback_user_surname'];
                    } elseif(empty($data_array['feedback_user_name']) and empty($data_array['feedback_user_surname'])) {
                        $data_array['feedback_user_name'] = $this->registry->main_class->extracting_data($row['5']);
                    }
                    $data_array['feedback_user_login'] = $this->registry->main_class->extracting_data($row['5']);
                    $data_array['feedback_user_login'] = $this->registry->main_class->format_striptags($data_array['feedback_user_login']);
                    $data_array['feedback_user_login'] = $this->registry->main_class->processing_data($data_array['feedback_user_login']);
                    $data_array['feedback_user_name'] = $this->registry->main_class->format_striptags($data_array['feedback_user_name']);
                    $data_array['feedback_user_name'] = $this->registry->main_class->processing_data($data_array['feedback_user_name']);
                    $data_array['feedback_user_email'] = $this->registry->main_class->format_striptags($data_array['feedback_user_email']);
                    $data_array['feedback_user_email'] = $this->registry->main_class->processing_data($data_array['feedback_user_email']);
                } else {
                    $data_array['feedback_user_login'] = 'NULL';
                    $data_array['feedback_user_name'] = "no";
                    $data_array['feedback_user_id'] = "no";
                    $data_array['feedback_user_email'] = "no";
                }
            } else {
                $sql = "SELECT count(*) as count FROM ".PREFIX."_users WHERE lower(user_email) = ".strtolower($data_array['feedback_user_email'])." or lower(user_login) = ".strtolower($data_array['feedback_user_name']);
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $num_rows = intval($result->fields['0']);
                    } else {
                        $num_rows = 0;
                    }
                    if($num_rows > 0) {
                        $this->error = 'find_such_user';
                        return;
                    }
                }
                $data_array['feedback_user_id'] = "no";
                $data_array['feedback_user_login'] = 'NULL';
            }
            $feedback_message_length = strlen($data_array['feedback_message']);
            if($feedback_message_length > 20000) {
                $this->error = 'message_length';
                return;
            }
            if(defined('SYSTEMDK_FEEDBACK_RECEIVER') and SYSTEMDK_FEEDBACK_RECEIVER != 'default') {
                $sql = "SELECT receiver_id,receiver_name,receiver_email FROM ".PREFIX."_mail_receivers_".$this->registry->sitelang." WHERE receiver_id = '".$data_array['feedback_towhom']."' and receiver_active = '1'";
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $row_exist = intval($result->fields['0']);
                    } else {
                        $row_exist = 0;
                    }
                    if($row_exist > 0) {
                        $receiver_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $receiver_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    } else {
                        $receiver_name = $post_array['feedback_admin_name'];
                        $receiver_email = trim(ADMIN_EMAIL);
                    }
                } else {
                    $receiver_name = $post_array['feedback_admin_name'];
                    $receiver_email = trim(ADMIN_EMAIL);
                }
            } else {
                $receiver_name = $post_array['feedback_admin_name'];
                $receiver_email = trim(ADMIN_EMAIL);
            }
            $this->send_mail_data = array(
                'receiver_email' => $receiver_email,
                'feedback_user_email' => $this->registry->main_class->extracting_data(substr(substr($data_array['feedback_user_email'],0,-1),1)),
                'feedback_user_login' => $data_array['feedback_user_login'],
                'feedback_subject' => $data_array['feedback_subject'],
                'feedback_message' => $data_array['feedback_message']
            );
            $this->result['modules_feedback_receivername'] = $receiver_name;
            $this->result['modules_feedback_siteurl'] = trim(SITE_URL);
            $this->result['modules_feedback_sendername'] = $this->registry->main_class->extracting_data(substr(substr($data_array['feedback_user_name'],0,-1),1));
            $this->result['modules_feedback_senderip'] = $this->registry->ip_addr;
            $this->result['modules_feedback_senderid'] = $data_array['feedback_user_id'];
        } else {
            $modules_feedback_content = str_replace("modules_feedback_message",$this->send_mail_data['feedback_message'],$feedback_content);
            $from = $this->send_mail_data['feedback_user_email'];
            if(!$this->registry->main_class->check_email($from)) {
                $this->registry->mail->from = trim(ADMIN_EMAIL);
            } else {
                $this->registry->mail->from = $from;
            }
            $this->registry->mail->sendmail($this->send_mail_data['receiver_email'],$this->send_mail_data['feedback_subject'],$modules_feedback_content);
            if($this->registry->mail->send_error) {
                $error_message = $this->registry->mail->smtp_msg;
                $error_code = "";
                $error[] = array("code" => $error_code,"message" => $error_message);
                $result = false;
            } else {
                $now = $this->registry->main_class->get_time();
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_mail_sendlog_id','mail_id');
                $sql = "INSERT INTO ".PREFIX."_mail_sendlog (".$sequence_array['field_name_string']."mail_user,mail_date,mail_module,mail_ip) VALUES (".$sequence_array['sequence_value_string']."".$this->send_mail_data['feedback_user_login'].",".$now.",'feedback','".$this->registry->ip_addr."')";
                $result = $this->db->Execute($sql);
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
            if($result === false) {
                $this->error_array = $error;
                $this->error = 'not_insert';
                return;
            }
            $this->result = 'send_ok';
        }
    }
}
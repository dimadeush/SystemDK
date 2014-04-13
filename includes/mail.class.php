<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      mail.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class mail {


    private $mail_method = 'php';
    private $site_name = "";
    private $charset = 'windows-1251';
    public $from = "";
    private $to = "";
    private $subject = "";
    private $message = "";
    private $header = "";
    private $error = "";
    public $bcc = array();
    private $mail_headers = "";
    private $html_mail = 0;
    private $smtp_fp = false;
    public $smtp_msg = "";
    private $smtp_port = "";
    private $smtp_host = "localhost";
    private $smtp_user = "";
    private $smtp_pass = "";
    private $smtp_code = "";
    public $send_error = false;
    private $endline = "\n";


    //private $is_html = "";
    function __construct($is_html = false) {
        $this->mail_method = SYSTEMDK_MAIL_METHOD;
        $this->site_name = SITE_NAME;
        $this->charset = SITE_CHARSET;
        $this->from = ADMIN_EMAIL;
        $this->smtp_host = SYSTEMDK_SMTP_HOST;
        $this->smtp_port = intval(SYSTEMDK_SMTP_PORT);
        $this->smtp_user = SYSTEMDK_SMTP_USER;
        $this->smtp_pass = SYSTEMDK_SMTP_PASS;
        /*if(!defined('IS_HTML'))
         {
           $this->is_html = false;
         }
        else
         {
           $this->is_html = IS_HTML;
         }*/
        $this->html_mail = $is_html;
    }


    public function sendmail($to,$subject,$message) {
        $this->to = preg_replace("/[ \t]+/","",$to);
        $this->from = preg_replace("/[ \t]+/","",$this->from);
        $this->to = preg_replace("/,,/",",",$this->to);
        $this->from = preg_replace("/,,/",",",$this->from);
        if($this->mail_method != 'smtp') {
            $this->to = preg_replace("#\#\[\]'\"\(\):;/\$!£%\^&\*\{\}#","",$this->to);
        } else {
            $this->to = '<'.preg_replace("#\#\[\]'\"\(\):;/\$!£%\^&\*\{\}#","",$this->to).'>';
        }
        $this->from = preg_replace("#\#\[\]'\"\(\):;/\$!£%\^&\*\{\}#","",$this->from);
        $this->subject = $subject;
        $this->message = $message;
        $this->message = str_replace("\r","",$this->message);
        $this->subject = "=?".$this->charset."?b?".base64_encode($this->subject)."?=";
        $from = "=?".$this->charset."?b?".base64_encode($this->site_name)."?=";
        if(isset($this->html_mail) and $this->html_mail == 1) {
            $this->mail_headers .= "MIME-Version: 1.0".$this->endline;
            $this->mail_headers .= "Content-type: text/html; charset=\"".$this->charset."\"".$this->endline;
        } else {
            $this->mail_headers .= "MIME-Version: 1.0".$this->endline;
            $this->mail_headers .= "Content-type: text/plain; charset=\"".$this->charset."\"".$this->endline;
        }
        if($this->mail_method != 'smtp') {
            if(count($this->bcc)) {
                $this->mail_headers .= "Bcc: ".implode(",",$this->bcc).$this->endline;
            }
        } else {
            $this->mail_headers .= "Subject: ".$this->subject.$this->endline;
            if($this->to) {
                $this->mail_headers .= "To: ".$this->to.$this->endline;
            }
        }
        $this->mail_headers .= "From: \"".$from."\" <".$this->from.">".$this->endline;
        $this->mail_headers .= "Return-Path: <".$this->from.">".$this->endline;
        $this->mail_headers .= "X-Priority: 3".$this->endline;
        $this->mail_headers .= "X-MSMail-Priority: Normal".$this->endline;
        $this->mail_headers .= "X-Mailer: SystemDK CMS".$this->endline;
        if(($this->to) and ($this->from) and ($this->subject)) {
            if($this->mail_method != 'smtp') {
                if(!@mail($this->to,$this->subject,$this->message,$this->mail_headers)) {
                    $this->smtp_msg = "PHP Mail Error.";
                    $this->send_error = true;
                }
            } else {
                $this->smtp_send();
            }
        }
        $this->mail_headers = "";
    }


    public function smtp_send() {
        $this->smtp_fp = @fsockopen($this->smtp_host,intval($this->smtp_port),$errno,$errstr,30);
        if(!$this->smtp_fp) {
            $this->smtp_msg = "Could not open a socket to the SMTP server";
            $this->send_error = true;
            return;
        }
        $this->smtp_msg = "";
        while($line = fgets($this->smtp_fp,515)) {
            $this->smtp_msg .= $line;
            if(substr($line,3,1) == " ") {
                break;
            }
        }
        $this->smtp_code = substr($this->smtp_msg,0,3);
        if($this->smtp_code == 220) {
            $data = $this->mail_headers."\n".$this->message;
            $data .= "\n";
            $data = str_replace("\n","\r\n",str_replace("\r","",$data));
            $data = str_replace("\n.\r\n","\n. \r\n",$data);
            $this->smtp_msg = "";
            $this->smtp_code = "";
            fputs($this->smtp_fp,"HELO ".$this->smtp_host."\r\n");
            $this->smtp_msg = "";
            while($line = fgets($this->smtp_fp,515)) {
                $this->smtp_msg .= $line;
                if(substr($line,3,1) == " ") {
                    break;
                }
            }
            $this->smtp_code = substr($this->smtp_msg,0,3);
            $this->smtp_code == "" ? false : true;
            if($this->smtp_code != 250) {
                $this->smtp_msg = "HELO";
                $this->send_error = true;
                return;
            }
            if($this->smtp_user and $this->smtp_pass) {
                $this->smtp_msg = "";
                $this->smtp_code = "";
                fputs($this->smtp_fp,"AUTH LOGIN"."\r\n");
                $this->smtp_msg = "";
                while($line = fgets($this->smtp_fp,515)) {
                    $this->smtp_msg .= $line;
                    if(substr($line,3,1) == " ") {
                        break;
                    }
                }
                $this->smtp_code = substr($this->smtp_msg,0,3);
                $this->smtp_code == "" ? false : true;
                if($this->smtp_code == 334) {
                    $this->smtp_msg = "";
                    $this->smtp_code = "";
                    fputs($this->smtp_fp,base64_encode($this->smtp_user)."\r\n");
                    $this->smtp_msg = "";
                    while($line = fgets($this->smtp_fp,515)) {
                        $this->smtp_msg .= $line;
                        if(substr($line,3,1) == " ") {
                            break;
                        }
                    }
                    $this->smtp_code = substr($this->smtp_msg,0,3);
                    $this->smtp_code == "" ? false : true;
                    if($this->smtp_code != 334) {
                        $this->smtp_msg = "Username not accepted from the server";
                        $this->send_error = true;
                        return;
                    }
                    $this->smtp_msg = "";
                    $this->smtp_code = "";
                    fputs($this->smtp_fp,base64_encode($this->smtp_pass)."\r\n");
                    $this->smtp_msg = "";
                    while($line = fgets($this->smtp_fp,515)) {
                        $this->smtp_msg .= $line;
                        if(substr($line,3,1) == " ") {
                            break;
                        }
                    }
                    $this->smtp_code = substr($this->smtp_msg,0,3);
                    $this->smtp_code == "" ? false : true;
                    if($this->smtp_code != 235) {
                        $this->smtp_msg = "Password not accepted from the SMTP server";
                        $this->send_error = true;
                        return;
                    }
                } else {
                    $this->smtp_msg = "This SMTP server does not support authorisation";
                    $this->send_error = true;
                    return;
                }
            }
            $this->smtp_msg = "";
            $this->smtp_code = "";
            fputs($this->smtp_fp,"MAIL FROM:<".$this->from.">"."\r\n");
            $this->smtp_msg = "";
            while($line = fgets($this->smtp_fp,515)) {
                $this->smtp_msg .= $line;
                if(substr($line,3,1) == " ") {
                    break;
                }
            }
            $this->smtp_code = substr($this->smtp_msg,0,3);
            $this->smtp_code == "" ? false : true;
            if($this->smtp_code != 250) {
                $this->smtp_msg = "Incorrect FROM address: $this->from";
                $this->send_error = true;
                return;
            }
            $to_array = array($this->to);
            if(count($this->bcc)) {
                foreach($this->bcc as $bcc) {
                    if(preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/",str_replace(" ","",$bcc))) {
                        $to_array[] = "<".$bcc.">";
                    }
                }
            }
            foreach($to_array as $to_email) {
                $this->smtp_msg = "";
                $this->smtp_code = "";
                fputs($this->smtp_fp,"RCPT TO:".$to_email."\r\n");
                $this->smtp_msg = "";
                while($line = fgets($this->smtp_fp,515)) {
                    $this->smtp_msg .= $line;
                    if(substr($line,3,1) == " ") {
                        break;
                    }
                }
                $this->smtp_code = substr($this->smtp_msg,0,3);
                $this->smtp_code == "" ? false : true;
                if($this->smtp_code != 250) {
                    $this->smtp_msg = "Incorrect email address: $to_email";
                    $this->send_error = true;
                    return;
                    break;
                }
            }
            $this->smtp_msg = "";
            $this->smtp_code = "";
            fputs($this->smtp_fp,"DATA"."\r\n");
            $this->smtp_msg = "";
            while($line = fgets($this->smtp_fp,515)) {
                $this->smtp_msg .= $line;
                if(substr($line,3,1) == " ") {
                    break;
                }
            }
            $this->smtp_code = substr($this->smtp_msg,0,3);
            $this->smtp_code == "" ? false : true;
            if($this->smtp_code == 354) {
                fputs($this->smtp_fp,$data."\r\n");
            } else {
                $this->smtp_msg = "Error on write to SMTP server";
                $this->send_error = true;
                return;
            }
            $this->smtp_msg = "";
            $this->smtp_code = "";
            fputs($this->smtp_fp,"."."\r\n");
            $this->smtp_msg = "";
            while($line = fgets($this->smtp_fp,515)) {
                $this->smtp_msg .= $line;
                if(substr($line,3,1) == " ") {
                    break;
                }
            }
            $this->smtp_code = substr($this->smtp_msg,0,3);
            $this->smtp_code == "" ? false : true;
            if($this->smtp_code != 250) {
                $this->smtp_msg = "";
                $this->send_error = true;
                return;
            }
            $this->smtp_msg = "";
            $this->smtp_code = "";
            fputs($this->smtp_fp,"quit"."\r\n");
            $this->smtp_msg = "";
            while($line = fgets($this->smtp_fp,515)) {
                $this->smtp_msg .= $line;
                if(substr($line,3,1) == " ") {
                    break;
                }
            }
            $this->smtp_code = substr($this->smtp_msg,0,3);
            $this->smtp_code == "" ? false : true;
            if($this->smtp_code != 221) {
                $this->smtp_msg = "";
                $this->send_error = true;
                return;
            }
            @fclose($this->smtp_fp);
        } else {
            $this->smtp_msg = "SMTP service unavailable";
            $this->send_error = true;
            return;
        }
    }
}
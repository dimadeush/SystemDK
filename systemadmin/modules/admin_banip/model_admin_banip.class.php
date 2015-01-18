<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_banip.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class admin_banip extends model_base {


    private $error;
    private $result;


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index() {
        $this->result = false;
        $this->error = false;
        if(!file_exists(__SITE_PATH."/includes/data/ban_ip.inc")) {
            $this->error = 'no_file';
            return;
        }
        require_once("../includes/data/ban_ip.inc");
        $ip_ban_list = preg_replace("/\|/",", ",BAN_IP);
        $ip_ban_array = explode("|",BAN_IP);
        $this->result['ban_ip'] = $ip_ban_list;
        $this->result['ip_ban_array'] = $ip_ban_array;
    }


    public function banip_save($post_array) {
        $this->result = false;
        $this->error = false;
        if(isset($post_array['ban_ip_add'])) {
            $ban_ip_add = trim($post_array['ban_ip_add']);
        }
        if(isset($post_array['ban_ip_del'])) {
            $ban_ip_del = trim($post_array['ban_ip_del']);
        }
        if(!isset($post_array['ban_ip_add']) or !isset($post_array['ban_ip_del'])) {
            $this->error = 'not_all_data';
            return;
        }
        if(!file_exists(__SITE_PATH."/includes/data/ban_ip.inc")) {
            $this->error = 'no_file';
            return;
        }
        require_once("../includes/data/ban_ip.inc");
        $ban_ip = trim(BAN_IP);
        $ban_ip_add = $this->registry->main_class->format_striptags($ban_ip_add);
        $ban_ip_del = $this->registry->main_class->format_striptags($ban_ip_del);
        if($ban_ip_del != "") {
            $ban_ip = preg_replace("/\|$ban_ip_del/","",$ban_ip);
            $ban_ip = preg_replace("/".$ban_ip_del."\|/","",$ban_ip);
            $ban_ip = preg_replace("/$ban_ip_del/","",$ban_ip);
        }
        if($ban_ip_add != "") {
            if($ban_ip != "") {
                $ban_ip_add = "".$ban_ip."|$ban_ip_add";
            }
        } else {
            $ban_ip_add = $ban_ip;
        }
        @chmod("../includes/data/ban_ip.inc",0777);
        @$file = fopen("../includes/data/ban_ip.inc","w");
        $content = "<?php\n";
        $content .= 'define("BAN_IP","'.$ban_ip_add."\");\n";
        @$write_file = fwrite($file,$content);
        @fclose($file);
        @chmod("../includes/data/ban_ip.inc",0604);
        if(!$write_file) {
            $this->error = 'not_write';
            return;
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|banip|show");
        $this->result = 'save_ok';
    }
}
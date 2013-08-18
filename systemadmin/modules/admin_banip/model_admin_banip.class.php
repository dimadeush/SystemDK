<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_banip.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_banip extends model_base {


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUBANIP'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|show|".$this->registry->language)) {
            if(!file_exists(__SITE_PATH."/includes/data/ban_ip.inc")) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|error|nofile|".$this->registry->language)) {
                    $this->registry->main_class->assign("ban_save","nofile");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/banip/banip.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|error|nofile|".$this->registry->language);
                exit();
            } else {
                require_once("../includes/data/ban_ip.inc");
                $ip_ban_list = preg_replace("/\|/",", ",BAN_IP);
                $ip_ban_array = explode("|",BAN_IP);
                $this->registry->main_class->assign("ban_ip",$ip_ban_list);
                $this->registry->main_class->assign("ip_ban_array",$ip_ban_array);
            }
            $this->registry->main_class->assign("ban_save","no");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/banip/banip.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|show|".$this->registry->language);
    }


    public function banip_save() {
        if(isset($_POST['ban_ip_add'])) {
            $ban_ip_add = trim($_POST['ban_ip_add']);
        }
        if(isset($_POST['ban_ip_del'])) {
            $ban_ip_del = trim($_POST['ban_ip_del']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUBANIP'));
        if(!isset($_POST['ban_ip_add']) or !isset($_POST['ban_ip_del'])) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("ban_save","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/banip/banip.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|error|notalldata|".$this->registry->language);
            exit();
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
        $content .= "?>\n";
        @$writefile = fwrite($file,$content);
        @fclose($file);
        @chmod("../includes/data/ban_ip.inc",0604);
        if(!$writefile) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|error|notwrite|".$this->registry->language)) {
                $this->registry->main_class->assign("ban_save","notwrite");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/banip/banip.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|error|notwrite|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|banip|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|save|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("ban_save","saveok");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/banip/banip.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|banip|save|ok|".$this->registry->language);
        }
    }
}

?>
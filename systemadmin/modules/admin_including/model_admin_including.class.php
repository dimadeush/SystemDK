<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_including.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_including extends model_base {


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUENTERS'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|show|".$this->registry->language)) {
            $filename1 = "../includes/data/header.inc";
            @$handle1 = fopen($filename1,"r");
            if(!$handle1) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|cantopen|".$this->registry->language)) {
                    $this->registry->main_class->assign("including_save","nofile");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|cantopen|".$this->registry->language);
                exit();
            }
            @$contents1 = fread($handle1,filesize($filename1));
            $contents1 = explode("<!-- Banner Text -->",$contents1);
            @fclose($handle1);
            $filename2 = "../includes/data/footer.inc";
            @$handle2 = fopen($filename2,"r");
            if(!$handle2) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|cantopen|".$this->registry->language)) {
                    $this->registry->main_class->assign("including_save","nofile");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|cantopen|".$this->registry->language);
                exit();
            }
            @$contents2 = fread($handle2,filesize($filename2));
            $contents2 = explode("<!-- Banner Text -->",$contents2);
            @fclose($handle2);
            $this->registry->main_class->assign("headerincluding",$contents1[1]);
            $this->registry->main_class->assign("footerincluding",$contents2[1]);
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|show|".$this->registry->language)) {
            $this->registry->main_class->assign("including_save","no");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|show|".$this->registry->language);
    }


    public function including_save() {
        if(isset($_POST['headerincluding'])) {
            $headerincluding = trim($_POST['headerincluding']);
        }
        if(isset($_POST['footerincluding'])) {
            $footerincluding = trim($_POST['footerincluding']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUENTERS'));
        if(!isset($_POST['headerincluding']) or !isset($_POST['footerincluding'])) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("including_save","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|notalldata|".$this->registry->language);
            exit();
        }
        $headerincluding = $this->registry->main_class->checkneed_stripslashes($headerincluding);
        $footerincluding = $this->registry->main_class->checkneed_stripslashes($footerincluding);
        @chmod("../includes/data/header.inc",0777);
        @$file1 = fopen("../includes/data/header.inc","w");
        $content1 = "<!-- Banner Text -->\n";
        $content1 .= "$headerincluding";
        @$writefile = fwrite($file1,$content1);
        @fclose($file1);
        @chmod("../includes/data/header.inc",0604);
        @chmod("../includes/data/footer.inc",0777);
        if(!$writefile) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|notwrite|".$this->registry->language)) {
                $this->registry->main_class->assign("including_save","nowrite");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|notwrite|".$this->registry->language);
            exit();
        }
        @$file2 = fopen("../includes/data/footer.inc","w");
        $content2 = "<!-- Banner Text -->\n";
        $content2 .= "$footerincluding";
        @$writefile = fwrite($file2,$content2);
        @fclose($file2);
        @chmod("../includes/data/footer.inc",0604);
        if(!$writefile) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|notwrite|".$this->registry->language)) {
                $this->registry->main_class->assign("including_save","nowrite");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|error|notwrite|".$this->registry->language);
            exit();
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|including|show");
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|save|ok|".$this->registry->language)) {
            $this->registry->main_class->assign("including_save","yes");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/including/including.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|including|save|ok|".$this->registry->language);
    }
}

?>
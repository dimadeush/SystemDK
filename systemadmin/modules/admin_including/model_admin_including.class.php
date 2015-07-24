<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_including.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class admin_including extends model_base {


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
        $filename1 = "../includes/data/header.inc";
        @$handle1 = fopen($filename1,"r");
        if(!$handle1) {
            $this->error = 'no_file';
            return;
        }
        @$contents1 = fread($handle1,filesize($filename1));
        $contents1 = explode("<!-- Banner Text -->",$contents1);
        @fclose($handle1);
        $filename2 = "../includes/data/footer.inc";
        @$handle2 = fopen($filename2,"r");
        if(!$handle2) {
            $this->error = 'no_file';
            return;
        }
        @$contents2 = fread($handle2,filesize($filename2));
        $contents2 = explode("<!-- Banner Text -->",$contents2);
        @fclose($handle2);
        $this->result['headerincluding'] = $contents1[1];
        $this->result['footerincluding'] = $contents2[1];
    }


    public function including_save($post_array) {
        if(isset($post_array['headerincluding'])) {
            $headerincluding = trim($post_array['headerincluding']);
        }
        if(isset($post_array['footerincluding'])) {
            $footerincluding = trim($post_array['footerincluding']);
        }
        if(!isset($post_array['headerincluding']) or !isset($post_array['footerincluding'])) {
            $this->error = 'not_all_data';
            return;
        }
        $headerincluding = $this->registry->main_class->checkneed_stripslashes($headerincluding);
        $footerincluding = $this->registry->main_class->checkneed_stripslashes($footerincluding);
        @chmod("../includes/data/header.inc",0777);
        @$file1 = fopen("../includes/data/header.inc","w");
        if(!$file1) {
            $this->error = 'no_file';
            return;
        }
        $content1 = "<!-- Banner Text -->\n";
        $content1 .= "$headerincluding";
        @$writefile = fwrite($file1,$content1);
        @fclose($file1);
        @chmod("../includes/data/header.inc",0604);
        @chmod("../includes/data/footer.inc",0777);
        if(!$writefile) {
            $this->error = 'not_write';
            return;
        }
        @$file2 = fopen("../includes/data/footer.inc","w");
        if(!$file2) {
            $this->error = 'no_file';
            return;
        }
        $content2 = "<!-- Banner Text -->\n";
        $content2 .= "$footerincluding";
        @$writefile = fwrite($file2,$content2);
        @fclose($file2);
        @chmod("../includes/data/footer.inc",0604);
        if(!$writefile) {
            $this->error = 'not_write';
            return;
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|including|show");
        $this->result = 'yes';
    }
}
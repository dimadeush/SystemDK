<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class block_account_model extends model_base {


    private function assign($index,$value) {
        $this->registry->main_class->assign($index,$value);
    }


    private function fetch($template,$group = null) {
        return $this->registry->main_class->fetch($template,$group);
    }


    public function index() {
        if($this->registry->main_class->is_user_in_mainfunc()) {
            $row = $this->registry->main_class->get_user_info($this->registry->user);
            if(isset($row) and $row != "") {
                $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                if((!isset($user_name) or $user_name == "") and (isset($user_surname) and $user_surname != "")) {
                    $block_account_username = $user_surname;
                } elseif((!isset($user_name) or $user_name == "") and (!isset($user_surname) or $user_surname == "")) {
                    $block_account_username = $this->registry->main_class->extracting_data($row['5']);
                } elseif((isset($user_name) or $user_name != "") and (isset($user_surname) or $user_surname != "")) {
                    $block_account_username = $user_name." ".$user_surname;
                }
            } else {
                $block_account_username = "no";
            }
            $this->assign("block_content_array",$block_account_username);
        } else {
            $this->assign("block_content_array","no");
        }
        return $block_content = $this->fetch("blocks/account/account.html");
    }
}

?>
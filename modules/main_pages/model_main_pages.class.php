<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class main_pages extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
    }


    public function index() {
        $mainpage_id = intval($_GET['main_pageid']);
        if($mainpage_id == 0) {
            $this->registry->main_class->database_close();
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
            } else {
                header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
            }
            exit();
        }
        $sql = "SELECT mainpage_id,mainpage_author,mainpage_title,mainpage_date,mainpage_content,mainpage_readcounter,mainpage_notes,mainpage_valueview,mainpage_status,mainpage_termexpire,mainpage_termexpireaction,(SELECT max(mainmenu_name) FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$mainpage_id."' OR mainmenu_submodule='".$mainpage_id."') as mainmenu_name FROM ".PREFIX."_main_pages_".$this->registry->sitelang." WHERE mainpage_id='".$mainpage_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
        }
        if($result and isset($numrows) and $numrows > 0) {
            $mainpage_id = intval($result->fields['0']);
            $mainpage_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $mainpage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
            $mainpage_date = intval($result->fields['3']);
            $mainpage_content = $this->registry->main_class->extracting_data($result->fields['4']);
            $mainpage_readcounter = intval($result->fields['5']);
            $mainpage_notes = $this->registry->main_class->extracting_data($result->fields['6']);
            $mainpage_valueview = intval($result->fields['7']);
            $mainpage_status = intval($result->fields['8']);
            $mainpage_termexpire = intval($result->fields['9']);
            $mainpage_termexpireaction = $this->registry->main_class->extracting_data($result->fields['10']);
            $page_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
            if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
                $num_page = intval($_GET['num_page']);
            } else {
                $num_page = 1;
            }
            $mainpage_content = str_ireplace("<span style=\"display: none;\">&nbsp;</span></div>","",$mainpage_content);
            $mainpage_content = explode("<div style=\"page-break-after: always;\">",$mainpage_content);
            $count_num_pages = count($mainpage_content);
            if($num_page > $count_num_pages) {
                $num_page = 1;
            }
            $mainpage_content = $mainpage_content[$num_page - 1];
            $mainpage_content = $this->registry->main_class->search_player_entry($mainpage_content);
            if($count_num_pages > 1) {
                if($num_page < $count_num_pages) {
                    $next_page = $num_page + 1;
                } else {
                    $next_page = "no";
                }
                if($num_page > 1) {
                    $prev_page = $num_page - 1;
                } else {
                    $prev_page = "no";
                }
                $pages_menu[] = array(
                    "current_page" => $num_page,
                    "next_page" => $next_page,
                    "prev_page" => $prev_page,
                    "count_num_pages" => $count_num_pages
                );
                $this->registry->main_class->assign("pages_menu",$pages_menu);
            } else {
                $this->registry->main_class->assign("pages_menu","no");
            }
            if($page_title != "") {
                $this->registry->main_class->assign("page_title",$page_title);
            } else {
                $this->registry->main_class->assign("page_title","no");
            }
            if($mainpage_status == 0) {
                $this->registry->main_class->database_close();
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|main_pages|error|notactive|".$this->registry->language)) {
                    $this->registry->main_class->assign("mainpage_all","notactive");
                    $this->registry->main_class->assign("include_center","modules/main_pages/main_pages.html");
                }
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|main_pages|error|notactive|".$this->registry->language);
                exit();
            }
            if($mainpage_valueview == "0") {
                $mainpage_all = array(
                    "mainpage_id" => $mainpage_id,
                    "mainpage_title" => $mainpage_title,
                    "mainpage_content" => $mainpage_content,
                    "mainpage_notes" => $mainpage_notes
                );
            } elseif($mainpage_valueview == "1" and ($this->registry->main_class->is_user_in_mainfunc() or $this->registry->main_class->is_admin_in_mainfunc())) {
                $mainpage_all = array(
                    "mainpage_id" => $mainpage_id,
                    "mainpage_title" => $mainpage_title,
                    "mainpage_content" => $mainpage_content,
                    "mainpage_notes" => $mainpage_notes
                );
            } elseif($mainpage_valueview == "2" and $this->registry->main_class->is_admin_in_mainfunc()) {
                $mainpage_all = array(
                    "mainpage_id" => $mainpage_id,
                    "mainpage_title" => $mainpage_title,
                    "mainpage_content" => $mainpage_content,
                    "mainpage_notes" => $mainpage_notes
                );
            } elseif($mainpage_valueview == "3" and (!$this->registry->main_class->is_user_in_mainfunc() or $this->registry->main_class->is_admin_in_mainfunc())) {
                $mainpage_all = array(
                    "mainpage_id" => $mainpage_id,
                    "mainpage_title" => $mainpage_title,
                    "mainpage_content" => $mainpage_content,
                    "mainpage_notes" => $mainpage_notes
                );
            }
            if(isset($mainpage_all) and $mainpage_all !== "") {
                $this->registry->main_class->database_close();
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|main_pages|show|".$mainpage_id."|".$num_page."|".$this->registry->language)) {
                    $this->registry->main_class->assign("mainpage_all",$mainpage_all);
                    $this->registry->main_class->assign("include_center","modules/main_pages/main_pages.html");
                }
                $this->registry->main_class->set_sitemeta($mainpage_title);
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|main_pages|show|".$mainpage_id."|".$num_page."|".$this->registry->language);
            } else {
                $this->registry->main_class->database_close();
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|main_pages|error|noaccess|".$this->registry->language)) {
                    $this->registry->main_class->assign("mainpage_all","noaccess");
                    $this->registry->main_class->assign("include_center","modules/main_pages/main_pages.html");
                }
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|main_pages|error|noaccess|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->database_close();
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|main_pages|error|nopage|".$this->registry->language)) {
                $this->registry->main_class->assign("mainpage_all","nopage");
                $this->registry->main_class->assign("include_center","modules/main_pages/main_pages.html");
            }
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|main_pages|error|nopage|".$this->registry->language);
        }
    }
}

?>
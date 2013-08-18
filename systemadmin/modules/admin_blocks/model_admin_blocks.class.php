<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_blocks.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_blocks extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->blocks_autocheck();
    }


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUBLOCKS'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|show|".$this->registry->language)) {
            $sql = "SELECT a.block_id, a.block_name, a.block_customname, a.block_version, a.block_status, a.block_valueview, a.block_content, a.block_url, a.block_arrangements, a.block_priority, (SELECT max(b.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." b WHERE b.block_priority=a.block_priority-1 AND b.block_arrangements=a.block_arrangements)as block_id2,(SELECT max(c.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." c WHERE c.block_priority=a.block_priority+1 AND c.block_arrangements=a.block_arrangements) as block_id3,a.block_termexpire FROM ".PREFIX."_blocks_".$this->registry->sitelang." a ORDER BY a.block_arrangements, a.block_priority";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $block_id = intval($result->fields['0']);
                        $block_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $block_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $block_version = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $block_status = intval($result->fields['4']);
                        $block_valueview = intval($result->fields['5']);
                        $block_content = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                        $block_url = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                        $block_arrangements = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                        $block_priority = intval($result->fields['9']);
                        $priority1 = $block_priority - 1;
                        $priority2 = $block_priority + 1;
                        $block_id2 = intval($result->fields['10']);
                        if(isset($block_id2) and $block_id2 != 0) {
                            $param1 = $block_id2;
                        } else {
                            $param1 = "";
                        }
                        $block_id3 = intval($result->fields['11']);
                        if(isset($block_id3) and $block_id3 != 0) {
                            $param2 = $block_id3;
                        } else {
                            $param2 = "";
                        }
                        $block_termexpire = intval($result->fields['12']);
                        if(!isset($block_termexpire) or $block_termexpire == 0) {
                            $block_termexpire = "unlim";
                        } else {
                            $block_termexpire = "notunlim";
                        }
                        $block_all[] = array(
                            "block_id" => $block_id,
                            "block_name" => $block_name,
                            "block_customname" => $block_customname,
                            "block_version" => $block_version,
                            "block_status" => $block_status,
                            "block_valueview" => $block_valueview,
                            "block_content" => $block_content,
                            "block_url" => $block_url,
                            "block_arrangements" => $block_arrangements,
                            "block_priority" => $block_priority,
                            "param1" => $param1,
                            "param2" => $param2,
                            "priority1" => $priority1,
                            "priority2" => $priority2,
                            "block_termexpire" => $block_termexpire
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("block_all",$block_all);
                } else {
                    $this->registry->main_class->assign("block_all","no");
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|showsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("block_save","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|showsqlerror|".$this->registry->language);
                exit();
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|show|".$this->registry->language)) {
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blocks.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|show|".$this->registry->language);
    }


    public function blockorder() {
        if(isset($_GET['block_newpriority'])) {
            $block_newpriority = intval($_GET['block_newpriority']);
        }
        if(isset($_GET['block_priority'])) {
            $block_priority = intval($_GET['block_priority']);
        }
        if(isset($_GET['block_nextid'])) {
            $block_nextid = intval($_GET['block_nextid']);
        }
        if(isset($_GET['block_id'])) {
            $block_id = intval($_GET['block_id']);
        }
        if((!isset($_GET['block_id']) or !isset($_GET['block_newpriority']) or !isset($_GET['block_priority']) or !isset($_GET['block_nextid'])) or ($block_id == 0 or $block_newpriority == 0 or $block_priority == 0 or $block_nextid == 0)) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->db->StartTrans();
        $result = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority='".$block_priority."' WHERE block_id='".$block_nextid."'");
        $num_result1 = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority='".$block_newpriority."' WHERE block_id='".$block_id."'");
        $num_result2 = $this->db->Affected_Rows();
        if($result2 === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $this->db->CompleteTrans();
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUBLOCKS'));
        if($result === false or $result2 === false) {
            if($num_result1 > 0 or $num_result2 > 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            }
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|showsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|showsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $result2 !== false and $num_result1 == 0 and $num_result2 == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|order|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","yes");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|order|ok|".$this->registry->language);
        }
    }


    public function select_block_type() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|selecttype|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
            $this->registry->main_class->assign("block_select_type","yes");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|selecttype|".$this->registry->language);
    }


    public function add_block() {
        if(isset($_POST['block_type'])) {
            $block_type = trim($_POST['block_type']);
        }
        if(!isset($_POST['block_type']) or $block_type == "") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                $this->registry->main_class->assign("blockadd","no");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language);
            exit();
        }
        $this->registry->main_class->assign("block_select_type","no");
        $block_type = $this->registry->main_class->format_striptags($block_type);
        if($block_type == "file") {
            $blocks_dir = dir("../blocks");
            while(false !== ($file = $blocks_dir->read())) {
                if(strpos($file,".") === 0) {
                    continue;
                } elseif(preg_match("/html/i",$file)) {
                    continue;
                } else {
                    $blocks_list[] = "$file";
                }
            }
            $blocks_dir->close();
            @sort($blocks_list);
            for($i = 0;$i < sizeof($blocks_list);$i++) {
                $blocks_list[$i] = trim($blocks_list[$i]);
                $blocks_list[$i] = $this->registry->main_class->format_striptags($blocks_list[$i]);
                $blocks_list[$i] = $this->registry->main_class->processing_data($blocks_list[$i]);
                $blocks_list[$i] = substr(substr($blocks_list[$i],0,-1),1);
                if($blocks_list[$i] != "") {
                    $sql = "SELECT block_id FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_name='".$blocks_list[$i]."'";
                    $result = $this->db->Execute($sql);
                    if($result) {
                        if(isset($result->fields['0'])) {
                            $numrows = intval($result->fields['0']);
                        } else {
                            $numrows = 0;
                        }
                        $blocks_list[$i] = $this->registry->main_class->extracting_data($blocks_list[$i]);
                        if($numrows == 0 and $blocks_list[$i] != "") {
                            $block_name[] = $blocks_list[$i];
                        }
                    } else {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->registry->main_class->assign("error",$error);
                        $this->registry->main_class->display_theme_adminheader();
                        $this->registry->main_class->display_theme_adminmain();
                        $this->registry->main_class->displayadmininfo();
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language)) {
                            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                            $this->registry->main_class->assign("block_save","notsave");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language);
                        exit();
                    }
                }
            }
            if(!isset($block_name)) {
                $this->registry->main_class->assign("blockadd","nofile");
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnofile|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnofile|".$this->registry->language);
                exit();
            } else {
                $this->registry->main_class->assign("block_name",$block_name);
            }
            $this->registry->main_class->assign("block_type","file");
        } elseif($block_type == "content") {
            $this->registry->main_class->assign("block_type","content");
        } elseif($block_type == "rss") {
            $sql = "SELECT rss_id,rss_sitename,rss_siteurl FROM ".PREFIX."_rss_".$this->registry->sitelang;
            $result = $this->db->Execute($sql);
            if($result) {
                while(!$result->EOF) {
                    $rss_all[] = array(
                        "rss_id" => intval($result->fields['0']),
                        "rss_sitename" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "rss_siteurl" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']))
                    );
                    $result->MoveNext();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("block_save","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language);
                exit();
            }
            if(!isset($rss_all)) {
                $this->registry->main_class->assign("rss_all","no");
            } else {
                $this->registry->main_class->assign("rss_all",$rss_all);
            }
            $this->registry->main_class->assign("block_type","rss");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|add|".$block_type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockadd.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|add|".$block_type."|".$this->registry->language);
    }


    public function add_block_inbase() {
        if(isset($_POST['block_type'])) {
            $block_type = trim($_POST['block_type']);
        }
        if(isset($_POST['block_custom_name'])) {
            $block_custom_name = trim($_POST['block_custom_name']);
        }
        if(isset($_POST['block_file'])) {
            $block_file = trim($_POST['block_file']);
        }
        if(isset($_POST['block_arrangements'])) {
            $block_arrangements = trim($_POST['block_arrangements']);
        }
        if(isset($_POST['block_status'])) {
            $block_status = intval($_POST['block_status']);
        }
        if(isset($_POST['block_termexpire'])) {
            $block_termexpire = intval($_POST['block_termexpire']);
        }
        if(isset($_POST['block_termexpireaction'])) {
            $block_termexpireaction = trim($_POST['block_termexpireaction']);
        }
        if(isset($_POST['block_valueview'])) {
            $block_valueview = intval($_POST['block_valueview']);
        }
        if(isset($_POST['block_content'])) {
            $block_content = trim($_POST['block_content']);
        }
        if(isset($_POST['block_url'])) {
            $block_url = trim($_POST['block_url']);
        }
        if(isset($_POST['block_refresh'])) {
            $block_refresh = intval($_POST['block_refresh']);
        }
        if(isset($_POST['rss_url'])) {
            $rss_url = trim($_POST['rss_url']);
        }
        if((!isset($_POST['block_type']) or !isset($_POST['block_custom_name']) or !isset($_POST['block_arrangements']) or !isset($_POST['block_status']) or !isset($_POST['block_termexpire']) or !isset($_POST['block_termexpireaction']) or !isset($_POST['block_valueview']) or !isset($_POST['block_content']) or !isset($_POST['block_url']) or !isset($_POST['block_refresh']) or !isset($_POST['rss_url'])) or ($block_type == "" or $block_custom_name == "" or $block_arrangements == "" or $block_termexpireaction == "")) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                $this->registry->main_class->assign("blockadd","no");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language);
            exit();
        }
        $block_file = $this->registry->main_class->format_striptags($block_file);
        $block_file = $this->registry->main_class->processing_data($block_file);
        $block_file = substr(substr($block_file,0,-1),1);
        $block_custom_name = $this->registry->main_class->format_striptags($block_custom_name);
        $block_custom_name = $this->registry->main_class->processing_data($block_custom_name);
        $block_custom_name = substr(substr($block_custom_name,0,-1),1);
        $block_arrangements = $this->registry->main_class->format_striptags($block_arrangements);
        $block_arrangements = $this->registry->main_class->processing_data($block_arrangements);
        $block_arrangements = substr(substr($block_arrangements,0,-1),1);
        $sql = "SELECT (SELECT MAX(a.block_priority) FROM ".PREFIX."_blocks_".$this->registry->sitelang." a WHERE a.block_arrangements='".$block_arrangements."') as block_priority,(SELECT MAX(b.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." b WHERE b.block_customname='".$block_custom_name."') as check_block_customname ".$this->registry->main_class->check_db_need_from_clause();
        $result0 = $this->db->Execute($sql);
        if($result0) {
            if(isset($result0->fields['0']) and intval($result0->fields['0']) > 0) {
                $block_priority = intval($result0->fields['0'] + 1);
            } else {
                $block_priority = 1;
            }
            if(isset($result0->fields['1'])) {
                $check_block_customname = intval($result0->fields['1']);
            } else {
                $check_block_customname = 0;
            }
            if($check_block_customname > 0) {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addfind|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                    $this->registry->main_class->assign("blockadd","findinbase");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addfind|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language);
            exit();
        }
        if($block_termexpire == "0") {
            $block_termexpire = 'NULL';
            $block_termexpireaction = 'NULL';
        } else {
            $block_termexpire = $this->registry->main_class->gettime() + ($block_termexpire * 86400);
            $block_termexpire = "'$block_termexpire'";
            $block_termexpireaction = $this->registry->main_class->format_striptags($block_termexpireaction);
            $block_termexpireaction = $this->registry->main_class->processing_data($block_termexpireaction);
            $block_termexpireaction = "$block_termexpireaction";
        }
        $block_type = $this->registry->main_class->format_striptags($block_type);
        if($block_type == "file") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!isset($_POST['block_file']) or $block_file == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","no");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language);
                exit();
            } else {
                $block_add_id = $this->db->GenID(PREFIX."_blocks_id_".$this->registry->sitelang);
                $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_name,block_customname,block_status,block_valueview,block_arrangements,block_priority,block_termexpire,block_termexpireaction)  VALUES ($block_add_id,'$block_file','$block_custom_name','$block_status','$block_valueview','$block_arrangements','$block_priority',$block_termexpire,$block_termexpireaction)";
                $result = $this->db->Execute($sql);
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        } elseif($block_type == "content") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!isset($block_content) or $block_content == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","no");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language);
                exit();
            } else {
                $block_content = $this->registry->main_class->processing_data($block_content);
                $block_content = substr(substr($block_content,0,-1),1);
                $block_add_id = $this->db->GenID(PREFIX."_blocks_id_".$this->registry->sitelang);
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if($check_db_need_lobs == 'yes') {
                    $this->db->StartTrans();
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_content,block_customname,block_status,block_valueview,block_arrangements,block_priority,block_termexpire, block_termexpireaction)  VALUES ($block_add_id,empty_clob(),'$block_custom_name','$block_status','$block_valueview','$block_arrangements','$block_priority',$block_termexpire,$block_termexpireaction)";
                    $result2 = $this->db->Execute($sql);
                    if($result2 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$block_content,'block_id='.$block_add_id);
                    $this->db->CompleteTrans();
                } else {
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_content,block_customname,block_status,block_valueview,block_arrangements,block_priority,block_termexpire, block_termexpireaction)  VALUES ($block_add_id,'$block_content','$block_custom_name','$block_status','$block_valueview','$block_arrangements','$block_priority',$block_termexpire,$block_termexpireaction)";
                    $result = $this->db->Execute($sql);
                    $result2 = true;
                }
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                if($result2 === false) {
                    $result = false;
                } else {
                    if($this->registry->main_class->check_player_entry($block_content)) {
                        $this->registry->main_class->clearAllCache();
                    }
                }
            }
        } elseif($block_type == "rss") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if($rss_url != "" and $rss_url != "0") {
                $block_url = $rss_url;
            }
            $block_url = $this->registry->main_class->format_striptags($block_url);
            if(!isset($block_url) or $block_url == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","no");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnotalldata|".$this->registry->language);
                exit();
            } else {
                if($block_url != "") {
                    $time = $this->registry->main_class->gettime();
                    if(!preg_match("/http:\\//i",$block_url)) {
                        $block_url = "http://".$block_url;
                    }
                    $addr = @parse_url($block_url);
                    $fp = @fsockopen($addr['host'],80,$errno,$errstr,20);
                    if(!$fp) {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnourl|".$this->registry->language)) {
                            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                            $this->registry->main_class->assign("blockadd","can'topenurl");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnourl|".$this->registry->language);
                        exit;
                    }
                    @fputs($fp,"GET ".$addr['path']."?".$addr['query']." HTTP/1.0\r\n");
                    @fputs($fp,"HOST: ".$addr['host']."\r\n\r\n");
                    $string = "";
                    while(!feof($fp)) {
                        $pagetext = @fgets($fp,300);
                        $string .= @chop($pagetext);
                        if(!isset($rss_encoding) and preg_match("/encoding/i","$pagetext")) {
                            $rss_encoding = explode("ENCODING=",strtoupper($pagetext));
                            $rss_encoding = $rss_encoding[1];
                            $rss_encoding1 = explode('"',$rss_encoding);
                            if(!isset($rss_encoding1[1])) {
                                $rss_encoding = explode("'",$rss_encoding);
                            } else {
                                $rss_encoding = $rss_encoding1;
                            }
                            $rss_encoding = $this->registry->main_class->format_striptags($rss_encoding[1]);
                        }
                    }
                    @fputs($fp,"Connection: close\r\n\r\n");
                    @fclose($fp);
                    if(!isset($rss_encoding) or $rss_encoding == "") {
                        $rss_encoding = "none";
                    }
                    $items = explode("</item>",$string);
                    $content = "";
                    for($i = 0;$i < 8;$i++) {
                        $link = @preg_replace('/.*<link>/i','',$items[$i]);
                        $link = @preg_replace('/<\/link>.*/i','',$link);
                        $link = @str_ireplace('<![CDATA[','',$link);
                        $link = @str_ireplace(']]>','',$link);
                        $link = @str_ireplace('&','&amp;',$link);
                        $title = @preg_replace('/.*<title>/i','',$items[$i]);
                        $title = @preg_replace('/<\/title>.*/i','',$title);
                        $title = @str_ireplace('<![CDATA[','',$title);
                        $title = @str_ireplace(']]>','',$title);
                        $description = @preg_replace('/.*<description>/i','',$items[$i]);
                        $description = @preg_replace('/<\/description>.*/i','',$description);
                        $description = @str_ireplace('<![CDATA[','',$description);
                        $description = @str_ireplace(']]>','',$description);
                        $image = @preg_replace('/\/>.*/i','>',$description);
                        if(strcasecmp($description,$image) == 0) {
                            $image = "";
                        }
                        $description = @preg_replace('/.*\/>/i','',$description);
                        $rssdate = @preg_replace('/.*<pubDate>/i','',$items[$i]);
                        $rssdate = @preg_replace('/<\/pubDate>.*/i','',$rssdate);
                        $rssdate = @strtotime($rssdate);
                        if(@$items[$i] == "" and isset($cont) and $cont != 1) {
                            $content = "";
                            $cont = 0;
                        } else {
                            if(strcmp($link,$title) AND $items[$i] != "") {
                                $cont = 1;
                                $title = $this->registry->main_class->encode_rss_text($title,$rss_encoding);
                                $description = $this->registry->main_class->encode_rss_text($description,$rss_encoding);
                                $link = $this->registry->main_class->format_striptags($link);
                                $image = $this->registry->main_class->format_striptags($image,'<img>');
                                $rssdate = $this->registry->main_class->format_striptags($rssdate);
                                $rsslink = array(
                                    "link" => $link,
                                    "title" => $title,
                                    "rssdate" => date("d.m.y H:i",$rssdate),
                                    "description" => $description,
                                    "image" => $image
                                );
                                $this->registry->main_class->assign("rsslink",$rsslink);
                                $this->registry->main_class->assign("language",$this->registry->language);
                                $content2 = $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
                                $content2 = $this->registry->main_class->processing_data($content2,'need');
                                $content2 = substr(substr($content2,0,-1),1);
                                $content .= $content2;
                            }
                        }
                    }
                }
                if($content == "") {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnourl|".$this->registry->language)) {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
                        $this->registry->main_class->assign("blockadd","can'topenurl");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addnourl|".$this->registry->language);
                    exit;
                }
                $block_url = $this->registry->main_class->processing_data($block_url);
                $block_add_id = $this->db->GenID(PREFIX."_blocks_id_".$this->registry->sitelang);
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if($check_db_need_lobs == 'yes') {
                    $this->db->StartTrans();
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_url,block_customname,block_status,block_valueview,block_content,block_arrangements,block_priority,block_refresh,block_lastrefresh,block_termexpire, block_termexpireaction)  VALUES ($block_add_id,$block_url,'$block_custom_name','$block_status','$block_valueview',empty_clob(),'$block_arrangements','$block_priority','$block_refresh','$time',$block_termexpire,$block_termexpireaction)";
                    $result2 = $this->db->Execute($sql);
                    if($result2 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$content,'block_id='.$block_add_id);
                    $this->db->CompleteTrans();
                } else {
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_url,block_customname,block_status,block_valueview,block_content,block_arrangements,block_priority,block_refresh,block_lastrefresh,block_termexpire, block_termexpireaction)  VALUES ($block_add_id,$block_url,'$block_custom_name','$block_status','$block_valueview','$content','$block_arrangements','$block_priority','$block_refresh','$time',$block_termexpire,$block_termexpireaction)";
                    $result = $this->db->Execute($sql);
                    $result2 = true;
                }
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                if($result2 === false) {
                    $result = false;
                }
            }
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDBLOCKPAGETITLE'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|add|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("blockadd","yes");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|add|ok|".$this->registry->language);
        }
    }


    public function block_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        }
        if(isset($_GET['block_id'])) {
            $block_id = intval($_GET['block_id']);
        }
        if((!isset($_GET['block_id']) or !isset($_GET['action'])) or ($block_id == 0 or $action == "")) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $action = $this->registry->main_class->format_striptags($action);
        if($action == "off") {
            $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_status='0' WHERE block_id='".$block_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            $this->registry->main_class->assign("block_save","off");
        } elseif($action == "on") {
            $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_status='1' WHERE block_id='".$block_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            $this->registry->main_class->assign("block_save","on");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUBLOCKS'));
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|file|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|rss|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|content|".$block_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|".$action."|ok|".$this->registry->language);
        }
    }


    public function block_delete() {
        if(isset($_GET['block_id'])) {
            $block_id = intval($_GET['block_id']);
        }
        if(!isset($_GET['block_id']) or $block_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUBLOCKS'));
        $sql = "SELECT block_priority,block_arrangements FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id='".$block_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $num_result = intval($result->fields['0']);
            } else {
                $num_result = 0;
            }
            if($num_result > 0) {
                $block_priority = intval($result->fields['0']);
                $block_arrangements = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority=block_priority-1 WHERE block_arrangements='".$block_arrangements."' and block_priority>'".$block_priority."'");
                $sql = "DELETE FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id='".$block_id."'";
                $result = $this->db->Execute($sql);
                $num_result = $this->db->Affected_Rows();
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusnotfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("blockadd","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusnotfind|".$this->registry->language);
                exit();
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statussqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statussqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|statusok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|file|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|rss|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|content|".$block_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|delete|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","delete");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|delete|ok|".$this->registry->language);
        }
    }


    public function block_edit() {
        if(isset($_GET['block_type'])) {
            $block_type = trim($_GET['block_type']);
        }
        if(isset($_GET['block_id'])) {
            $block_id = intval($_GET['block_id']);
        }
        if((!isset($_GET['block_id']) or !isset($_GET['block_type'])) or ($block_id == 0 or $block_type == "")) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $block_type = $this->registry->main_class->format_striptags($block_type);
        if($block_type == "file") {
            $blocks_dir = dir("../blocks");
            while(false !== ($file = $blocks_dir->read())) {
                if(strpos($file,".") === 0) {
                    continue;
                } elseif(preg_match("/html/i",$file)) {
                    continue;
                } else {
                    $blocks_list[] = "$file";
                }
            }
            $blocks_dir->close();
            @sort($blocks_list);
            for($i = 0;$i < sizeof($blocks_list);$i++) {
                $blocks_list[$i] = trim($blocks_list[$i]);
                $blocks_list[$i] = $this->registry->main_class->format_striptags($blocks_list[$i]);
                $blocks_list[$i] = $this->registry->main_class->processing_data($blocks_list[$i]);
                $blocks_list[$i] = substr(substr($blocks_list[$i],0,-1),1);
                if($blocks_list[$i] != "") {
                    $sql = "SELECT block_id FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_name='".$blocks_list[$i]."'";
                    $result = $this->db->Execute($sql);
                    if($result) {
                        if(isset($result->fields['0'])) {
                            $numrows = intval($result->fields['0']);
                        } else {
                            $numrows = 0;
                        }
                        $blocks_list[$i] = $this->registry->main_class->extracting_data($blocks_list[$i]);
                        if(($numrows == 0 and $blocks_list[$i] != "") or (intval($result->fields['0']) == $block_id)) {
                            $block_name[]['block_name'] = $blocks_list[$i];
                        }
                    } else {
                        $this->registry->main_class->display_theme_adminheader();
                        $this->registry->main_class->display_theme_adminmain();
                        $this->registry->main_class->displayadmininfo();
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->registry->main_class->assign("error",$error);
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language)) {
                            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                            $this->registry->main_class->assign("block_save","notsave");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language);
                        exit();
                    }
                }
            }
            if(!isset($block_name)) {
                $this->registry->main_class->assign("block_name","no");
            } else {
                $this->registry->main_class->assign("block_name",$block_name);
            }
            $this->registry->main_class->assign("block_type","file");
        } elseif($block_type == "content") {
            $this->registry->main_class->assign("block_type","content");
        } elseif($block_type == "rss") {
            $sql = "SELECT rss_id,rss_sitename,rss_siteurl FROM ".PREFIX."_rss_".$this->registry->sitelang;
            $result = $this->db->Execute($sql);
            if($result) {
                while(!$result->EOF) {
                    $rss_all[] = array(
                        "rss_id" => intval($result->fields['0']),
                        "rss_sitename" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "rss_siteurl" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']))
                    );
                    $result->MoveNext();
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("block_save","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language);
                exit();
            }
            if(!isset($rss_all)) {
                $this->registry->main_class->assign("rss_all","no");
            } else {
                $this->registry->main_class->assign("rss_all",$rss_all);
            }
            $this->registry->main_class->assign("block_type","rss");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $sql = "SELECT block_id,block_name,block_customname,block_version,block_status,block_valueview,block_content,block_url,block_arrangements,block_refresh,block_lastrefresh,block_termexpire,block_termexpireaction FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id='".$block_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                if(isset($result->fields['11']) and intval($result->fields['11']) != 0 and intval($result->fields['11']) !== "") {
                    $block_termexpire = intval($result->fields['11']);
                    $block_termexpire = date("d.m.y H:i",$block_termexpire);
                    $block_termexpireday = intval(((intval($result->fields['11']) - $this->registry->main_class->gettime()) / 3600) / 24);
                } else {
                    $block_termexpire = "";
                    $block_termexpireday = "";
                }
                if($block_type == "content") {
                    $block_content = $this->registry->main_class->extracting_data($result->fields['6']);
                } else {
                    $block_content = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                }
                $block_all[] = array(
                    "block_id" => intval($result->fields['0']),
                    "block_name" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                    "block_customname" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                    "block_version" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3'])),
                    "block_status" => intval($result->fields['4']),
                    "block_valueview" => intval($result->fields['5']),
                    "block_content" => $block_content,
                    "block_url" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7'])),
                    "block_arrangements" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8'])),
                    "block_refresh" => intval($result->fields['9']),
                    "block_lastrefresh" => intval($result->fields['10']),
                    "block_termexpire" => $block_termexpire,
                    "block_termexpireday" => $block_termexpireday,
                    "block_termexpireaction" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']))
                );
                $this->registry->main_class->assign("block_all",$block_all);
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotfind|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotfind|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|edit|".$block_type."|".$block_id."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockedit.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|edit|".$block_type."|".$block_id."|".$this->registry->language);
    }


    public function block_edit_save() {
        if(isset($_POST['block_id'])) {
            $block_id = intval($_POST['block_id']);
        }
        if(isset($_POST['block_type'])) {
            $block_type = trim($_POST['block_type']);
        }
        if(isset($_POST['block_custom_name'])) {
            $block_custom_name = trim($_POST['block_custom_name']);
        }
        if(isset($_POST['block_file'])) {
            $block_file = trim($_POST['block_file']);
        }
        if(isset($_POST['block_arrangements'])) {
            $block_arrangements = trim($_POST['block_arrangements']);
        }
        if(isset($_POST['block_status'])) {
            $block_status = intval($_POST['block_status']);
        }
        if(isset($_POST['block_termexpire'])) {
            $block_termexpire = intval($_POST['block_termexpire']);
        }
        if(isset($_POST['block_termexpireaction'])) {
            $block_termexpireaction = trim($_POST['block_termexpireaction']);
        }
        if(isset($_POST['block_valueview'])) {
            $block_valueview = intval($_POST['block_valueview']);
        }
        if(isset($_POST['block_content'])) {
            $block_content = trim($_POST['block_content']);
        }
        if(isset($_POST['block_url'])) {
            $block_url = trim($_POST['block_url']);
        }
        if(isset($_POST['block_refresh'])) {
            $block_refresh = intval($_POST['block_refresh']);
        }
        if(isset($_POST['rss_url'])) {
            $rss_url = trim($_POST['rss_url']);
        }
        if((!isset($_POST['block_id']) or !isset($_POST['block_type']) or !isset($_POST['block_custom_name']) or !isset($_POST['block_file']) or !isset($_POST['block_arrangements']) or !isset($_POST['block_status']) or !isset($_POST['block_termexpire']) or !isset($_POST['block_termexpireaction']) or !isset($_POST['block_valueview']) or !isset($_POST['block_content']) or !isset($_POST['block_url']) or !isset($_POST['block_refresh']) or !isset($_POST['rss_url'])) or ($block_type == "" or $block_id == 0 or $block_custom_name == "" or $block_arrangements == "" or $block_termexpireaction == "")) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotalldata|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                $this->registry->main_class->assign("blockadd","no");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotalldata|".$this->registry->language);
            exit();
        }
        $block_custom_name = $this->registry->main_class->format_striptags($block_custom_name);
        $block_custom_name = $this->registry->main_class->processing_data($block_custom_name);
        $block_arrangements = $this->registry->main_class->format_striptags($block_arrangements);
        $block_arrangements = $this->registry->main_class->processing_data($block_arrangements);
        $sql = "select a.block_arrangements,(select max(b.block_priority) from ".PREFIX."_blocks_".$this->registry->sitelang." b where b.block_arrangements=".$block_arrangements.") as block_new_priority,(SELECT max(c.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." c WHERE c.block_customname=".$block_custom_name." and c.block_id<>'".$block_id."') as check_custom_name,a.block_priority from ".PREFIX."_blocks_".$this->registry->sitelang." a where a.block_id='".$block_id."'";
        $result0 = $this->db->Execute($sql);
        if($result0 and isset($result0->fields['0']) and isset($result0->fields['3']) and trim($result0->fields['0']) !== '' and intval($result0->fields['3']) != 0) {
            $block_old_arrangements = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result0->fields['0']));
            $block_arrangements = substr(substr($block_arrangements,0,-1),1);
            if($this->registry->main_class->extracting_data($block_arrangements) !== "$block_old_arrangements") {
                $needupdate = "yes";
            } else {
                $needupdate = "no";
            }
            if(isset($result0->fields['1']) and intval($result0->fields['1']) > 0) {
                $block_new_priority = intval($result0->fields['1'] + 1);
            } else {
                $block_new_priority = 1;
            }
            if(isset($result0->fields['2'])) {
                $check_block_customname = intval($result0->fields['2']);
            } else {
                $check_block_customname = 0;
            }
            $block_old_priority = intval($result0->fields['3']);
            if($check_block_customname > 0) {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editfind|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                    $this->registry->main_class->assign("blockadd","findinbase");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editfind|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language);
            exit();
        }
        if($block_termexpire == "0") {
            $block_termexpire = 'NULL';
            $block_termexpireaction = 'NULL';
        } else {
            $block_termexpire = $this->registry->main_class->gettime() + ($block_termexpire * 86400);
            $block_termexpire = "'$block_termexpire'";
            $block_termexpireaction = $this->registry->main_class->format_striptags($block_termexpireaction);
            $block_termexpireaction = $this->registry->main_class->processing_data($block_termexpireaction);
        }
        $block_type = $this->registry->main_class->format_striptags($block_type);
        if($block_type == "file") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!isset($block_file) or $block_file == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnofile|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","no");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnofile|".$this->registry->language);
                exit();
            } else {
                $block_file = $this->registry->main_class->format_striptags($block_file);
                $block_file = $this->registry->main_class->processing_data($block_file);
                $this->db->StartTrans();
                if($needupdate == "yes") {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_name=$block_file,block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_arrangements='$block_arrangements',block_priority='$block_new_priority',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                    $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority=block_priority-1 WHERE block_arrangements='".$block_old_arrangements."' and block_priority>'".$block_old_priority."'");
                } else {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_name=$block_file,block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                    $result2 = true;
                }
                if($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $result = $this->db->Execute($sql);
                $num_result = $this->db->Affected_Rows();
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $this->db->CompleteTrans();
                if($result === false or $result2 === false) {
                    $result = false;
                }
                $this->registry->main_class->assign("block_type","file");
            }
        } elseif($block_type == "content") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!isset($block_content) or $block_content == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotalldata|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","no");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotalldata|".$this->registry->language);
                exit();
            } else {
                $block_content = $this->registry->main_class->processing_data($block_content);
                $block_content = substr(substr($block_content,0,-1),1);
                $this->db->StartTrans();
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if($needupdate == "yes") {
                    if($check_db_need_lobs == 'yes') {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content=empty_clob(),block_arrangements='$block_arrangements',block_priority='$block_new_priority', block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$block_content,'block_id='.$block_id);
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                    } else {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content='$block_content',block_arrangements='$block_arrangements',block_priority='$block_new_priority', block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = true;
                    }
                    $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority=block_priority-1 WHERE block_arrangements='".$block_old_arrangements."' and block_priority>'".$block_old_priority."'");
                } else {
                    if($check_db_need_lobs == 'yes') {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content=empty_clob(),block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$block_content,'block_id='.$block_id);
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                    } else {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content='$block_content',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = true;
                    }
                    $result2 = true;
                }
                if($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $this->db->CompleteTrans();
                if($result === false or $result2 === false or $result3 === false) {
                    $result = false;
                } else {
                    if($this->registry->main_class->check_player_entry($block_content)) {
                        $this->registry->main_class->clearAllCache();
                    }
                }
                $this->registry->main_class->assign("block_type","content");
            }
        } elseif($block_type == "rss") {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if($rss_url != "" and $rss_url != "0") {
                $block_url = $rss_url;
            }
            $block_url = $this->registry->main_class->format_striptags($block_url);
            if(!isset($block_url) or $block_url == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotalldata|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("blockadd","no");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnotalldata|".$this->registry->language);
                exit();
            } else {
                if($block_url != "") {
                    $time = $this->registry->main_class->gettime();
                    if(!preg_match("/http:\\//i",$block_url)) {
                        $block_url = "http://".$block_url;
                    }
                    $addr = @parse_url($block_url);
                    $fp = @fsockopen($addr['host'],80,$errno,$errstr,20);
                    if(!$fp) {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnourl|".$this->registry->language)) {
                            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                            $this->registry->main_class->assign("blockadd","can'topenurl");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnourl|".$this->registry->language);
                        exit;
                    }
                    @fputs($fp,"GET ".$addr['path']."?".$addr['query']." HTTP/1.0\r\n");
                    @fputs($fp,"HOST: ".$addr['host']."\r\n\r\n");
                    $string = "";
                    while(!feof($fp)) {
                        $pagetext = @fgets($fp,300);
                        $string .= @chop($pagetext);
                        if(!isset($rss_encoding) and preg_match("/encoding/i","$pagetext")) {
                            $rss_encoding = explode("ENCODING=",strtoupper($pagetext));
                            $rss_encoding = $rss_encoding[1];
                            $rss_encoding1 = explode('"',$rss_encoding);
                            if(!isset($rss_encoding1[1])) {
                                $rss_encoding = explode("'",$rss_encoding);
                            } else {
                                $rss_encoding = $rss_encoding1;
                            }
                            $rss_encoding = $this->registry->main_class->format_striptags($rss_encoding[1]);
                        }
                    }
                    @fputs($fp,"Connection: close\r\n\r\n");
                    @fclose($fp);
                    if(!isset($rss_encoding) or $rss_encoding == "") {
                        $rss_encoding = "none";
                    }
                    $items = explode("</item>",$string);
                    $content = "";
                    for($i = 0;$i < 8;$i++) {
                        $link = @preg_replace('/.*<link>/i','',$items[$i]);
                        $link = @preg_replace('/<\/link>.*/i','',$link);
                        $link = @str_ireplace('<![CDATA[','',$link);
                        $link = @str_ireplace(']]>','',$link);
                        $link = @preg_replace('/&/i','&amp;',$link);
                        $title = @preg_replace('/.*<title>/i','',$items[$i]);
                        $title = @preg_replace('/<\/title>.*/i','',$title);
                        $title = @str_ireplace('<![CDATA[','',$title);
                        $title = @str_ireplace(']]>','',$title);
                        $description = @preg_replace('/.*<description>/i','',$items[$i]);
                        $description = @preg_replace('/<\/description>.*/i','',$description);
                        $description = @str_ireplace('<![CDATA[','',$description);
                        $description = @str_ireplace(']]>','',$description);
                        $image = @preg_replace('/\/>.*/i','>',$description);
                        if(strcasecmp($description,$image) == 0) {
                            $image = "";
                        }
                        $description = @preg_replace('/.*\/>/i','',$description);
                        $rssdate = @preg_replace('/.*<pubDate>/i','',$items[$i]);
                        $rssdate = @preg_replace('/<\/pubDate>.*/i','',$rssdate);
                        $rssdate = @strtotime($rssdate);
                        if(@$items[$i] == "" and isset($cont) and $cont != 1) {
                            $content = "";
                            $cont = 0;
                        } else {
                            if(strcmp($link,$title) and $items[$i] != "") {
                                $cont = 1;
                                $title = $this->registry->main_class->encode_rss_text($title,$rss_encoding);
                                $description = $this->registry->main_class->encode_rss_text($description,$rss_encoding);
                                $link = $this->registry->main_class->format_striptags($link);
                                $image = $this->registry->main_class->format_striptags($image,'<img>');
                                $rssdate = $this->registry->main_class->format_striptags($rssdate);
                                $rsslink = array(
                                    "link" => $link,
                                    "title" => $title,
                                    "rssdate" => date("d.m.y H:i",$rssdate),
                                    "description" => $description,
                                    "image" => $image
                                );
                                $this->registry->main_class->assign("rsslink",$rsslink);
                                $this->registry->main_class->assign("language",$this->registry->language);
                                $content2 = $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
                                $content2 = $this->registry->main_class->processing_data($content2,'need');
                                $content2 = substr(substr($content2,0,-1),1);
                                $content .= $content2;
                            }
                        }
                    }
                }
                if($content == "") {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnourl|".$this->registry->language)) {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
                        $this->registry->main_class->assign("blockadd","can'topenurl");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editnourl|".$this->registry->language);
                    exit;
                }
                $block_url = $this->registry->main_class->processing_data($block_url);
                $this->db->StartTrans();
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if($needupdate == "yes") {
                    if($check_db_need_lobs == 'yes') {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content=empty_clob(),block_url=$block_url,block_arrangements='$block_arrangements',block_priority='$block_new_priority',block_refresh='$block_refresh',block_lastrefresh='$time',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$content,'block_id='.$block_id);
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                    } else {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content='$content',block_url=$block_url,block_arrangements='$block_arrangements',block_priority='$block_new_priority',block_refresh='$block_refresh',block_lastrefresh='$time',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = true;
                    }
                    $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority=block_priority-1 WHERE block_arrangements='".$block_old_arrangements."' and block_priority>'".$block_old_priority."'");
                } else {
                    if($check_db_need_lobs == 'yes') {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content=empty_clob(),block_url=$block_url,block_refresh='$block_refresh',block_lastrefresh='$time',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$content,'block_id='.$block_id);
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                    } else {
                        $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_customname=$block_custom_name,block_status='$block_status',block_valueview='$block_valueview',block_content='$content',block_url=$block_url,block_refresh='$block_refresh',block_lastrefresh='$time',block_termexpire=$block_termexpire,block_termexpireaction=$block_termexpireaction WHERE block_id='$block_id'";
                        $result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3 = true;
                    }
                    $result2 = true;
                }
                if($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $this->db->CompleteTrans();
                if($result === false or $result2 === false or $result3 === false) {
                    $result = false;
                }
                $this->registry->main_class->assign("block_type","rss");
            }
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITBLOCKPAGETITLE'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|".$block_type."|".$block_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|edit|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","yes");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|edit|ok|".$this->registry->language);
        }
    }


    public function block_rsssite() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|rss|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.rss_id,a.rss_sitename,a.rss_siteurl,(SELECT count(b.rss_id) FROM ".PREFIX."_rss_".$this->registry->sitelang." b) as count_rss_id FROM ".PREFIX."_rss_".$this->registry->sitelang." a order by a.rss_id DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_blocks&func=block_rsssite&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['3']);
                        }
                        $rss_all[] = array(
                            "rss_id" => intval($result->fields['0']),
                            "rss_sitename" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                            "rss_siteurl" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']))
                        );
                        $result->MoveNext();
                    }
                    if(isset($numrows2)) {
                        $num_pages = @ceil($numrows2 / $num_string_rows);
                    } else {
                        $num_pages = 0;
                    }
                    if($num_pages > 1) {
                        if($num_page > 1) {
                            $prevpage = $num_page - 1;
                            $this->registry->main_class->assign("prevpage",$prevpage);
                        } else {
                            $this->registry->main_class->assign("prevpage","no");
                        }
                        for($i = 1;$i < $num_pages + 1;$i++) {
                            if($i == $num_page) {
                                $html[] = array("number" => $i,"param1" => "1");
                            } else {
                                $pagelink = 5;
                                if(($i > $num_page) and ($i < $num_page + $pagelink) or ($i < $num_page) and ($i > $num_page - $pagelink)) {
                                    $html[] = array("number" => $i,"param1" => "2");
                                }
                                if(($i == $num_pages) and ($num_page < $num_pages - $pagelink)) {
                                    $html[] = array("number" => $i,"param1" => "3");
                                }
                                if(($i == 1) and ($num_page > $pagelink + 1)) {
                                    $html[] = array("number" => $i,"param1" => "4");
                                }
                            }
                        }
                        if($num_page < $num_pages) {
                            $nextpage = $num_page + 1;
                            $this->registry->main_class->assign("nextpage",$nextpage);
                        } else {
                            $this->registry->main_class->assign("nextpage","no");
                        }
                        $this->registry->main_class->assign("html",$html);
                        $this->registry->main_class->assign("totalrss",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|rsssqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONRSSBLOCKPAGETITLE'));
                    $this->registry->main_class->assign("block_save","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|rsssqlerror|".$this->registry->language);
                exit();
            }
            if(isset($rss_all)) {
                $this->registry->main_class->assign("rss_all",$rss_all);
            } else {
                $this->registry->main_class->assign("rss_all","no");
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|rss|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONRSSBLOCKPAGETITLE'));
            $this->registry->main_class->assign("addrss_inbase","no");
            $this->registry->main_class->assign("editrss","hidden");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|rss|".$num_page."|".$this->registry->language);
    }


    public function block_addrsssite() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|addrss|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDRSSBLOCKPAGETITLE'));
            $this->registry->main_class->assign("addrss_inbase","no");
            $this->registry->main_class->assign("editrss","no");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|addrss|".$this->registry->language);
    }


    public function block_addrsssite_inbase() {
        if(isset($_POST['rss_sitename'])) {
            $rss_sitename = trim($_POST['rss_sitename']);
        }
        if(isset($_POST['rss_siteurl'])) {
            $rss_siteurl = trim($_POST['rss_siteurl']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDRSSBLOCKPAGETITLE'));
        if((!isset($_POST['rss_sitename']) or !isset($_POST['rss_siteurl'])) or ($rss_sitename == "" or $rss_siteurl == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|addrss|error|addrssnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("blockadd","no");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|addrss|error|addrssnotalldata|".$this->registry->language);
            exit();
        }
        $rss_sitename = $this->registry->main_class->format_striptags($rss_sitename);
        $rss_sitename = $this->registry->main_class->processing_data($rss_sitename);
        $rss_siteurl = $this->registry->main_class->format_striptags($rss_siteurl);
        $rss_siteurl = $this->registry->main_class->processing_data($rss_siteurl);
        $rss_id = $this->db->GenID(PREFIX."_rss_id_".$this->registry->sitelang);
        $sql = "INSERT INTO ".PREFIX."_rss_".$this->registry->sitelang." (rss_id,rss_sitename,rss_siteurl) VALUES ($rss_id,$rss_sitename,$rss_siteurl)";
        $result = $this->db->Execute($sql);
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addrsssqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|addrsssqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|rss");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|addrssok|".$this->registry->language)) {
                $this->registry->main_class->assign("addrss_inbase","yes");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|addrssok|".$this->registry->language);
        }
    }


    public function block_editrsssite() {
        if(isset($_GET['rss_id'])) {
            $rss_id = intval($_GET['rss_id']);
        }
        if(!isset($_GET['rss_id']) or $rss_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITRSSBLOCKPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$rss_id."|".$this->registry->language)) {
            $sql = "SELECT rss_id,rss_sitename,rss_siteurl FROM ".PREFIX."_rss_".$this->registry->sitelang." WHERE rss_id = ".$rss_id;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $rss_edit_all[] = array(
                        "rss_id" => intval($result->fields['0']),
                        "rss_sitename" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "rss_siteurl" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']))
                    );
                    $this->registry->main_class->assign("rss_edit_all",$rss_edit_all);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrsssqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("block_save","notsave");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrsssqlerror|".$this->registry->language);
                exit();
            }
            if(!isset($rss_edit_all)) {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrssnotfindrss|".$this->registry->language)) {
                    $this->registry->main_class->assign("addrss_inbase","notfind");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrssnotfindrss|".$this->registry->language);
                exit();
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$rss_id."|".$this->registry->language)) {
            $this->registry->main_class->assign("addrss_inbase","no");
            $this->registry->main_class->assign("editrss","yes");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$rss_id."|".$this->registry->language);
    }


    public function block_deletersssite() {
        if(isset($_GET['rss_id'])) {
            $rss_id = intval($_GET['rss_id']);
        }
        if(!isset($_GET['rss_id']) or $rss_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $sql = "DELETE FROM ".PREFIX."_rss_".$this->registry->sitelang." WHERE rss_id='".$rss_id."'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONDELETERSSBLOCKPAGETITLE'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|delrsssqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|delrsssqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|delrssok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|delrssok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|rss");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$rss_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|deleterssok|".$this->registry->language)) {
                $this->registry->main_class->assign("addrss_inbase","delete");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|deleterssok|".$this->registry->language);
        }
    }


    public function block_editrsssite_inbase() {
        if(isset($_POST['rss_id'])) {
            $rss_id = intval($_POST['rss_id']);
        }
        if(isset($_POST['rss_sitename'])) {
            $rss_sitename = trim($_POST['rss_sitename']);
        }
        if(isset($_POST['rss_siteurl'])) {
            $rss_siteurl = trim($_POST['rss_siteurl']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITRSSBLOCKPAGETITLE'));
        if((!isset($_POST['rss_id']) or !isset($_POST['rss_sitename']) or !isset($_POST['rss_siteurl'])) or ($rss_id == 0 or $rss_sitename == "" or $rss_siteurl == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrssnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("blockadd","no");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddend.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrssnotalldata|".$this->registry->language);
            exit();
        }
        $rss_sitename = $this->registry->main_class->format_striptags($rss_sitename);
        $rss_sitename = $this->registry->main_class->processing_data($rss_sitename);
        $rss_siteurl = $this->registry->main_class->format_striptags($rss_siteurl);
        $rss_siteurl = $this->registry->main_class->processing_data($rss_siteurl);
        $sql = "UPDATE ".PREFIX."_rss_".$this->registry->sitelang." SET rss_sitename=".$rss_sitename.",rss_siteurl=".$rss_siteurl." WHERE rss_id='".$rss_id."'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrsssqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrsssqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrssok|".$this->registry->language)) {
                $this->registry->main_class->assign("block_save","notsave2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|error|editrssok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|rss");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$rss_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|editrssok|".$this->registry->language)) {
                $this->registry->main_class->assign("addrss_inbase","update");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/blocks/blockaddrsssite.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|blocks|editrssok|".$this->registry->language);
        }
    }
}

?>
<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_blocks.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class admin_blocks extends model_base {


    private $error;
    private $error_array;
    private $result;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->blocks_check_process();
    }


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index() {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $sql = "SELECT a.block_id, a.block_name, a.block_custom_name, a.block_version, a.block_status, a.block_value_view, a.block_content, a.block_url, a.block_arrangements, a.block_priority, (SELECT max(b.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." b WHERE b.block_priority = a.block_priority-1 AND b.block_arrangements = a.block_arrangements) as block_id2,(SELECT max(c.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." c WHERE c.block_priority = a.block_priority+1 AND c.block_arrangements = a.block_arrangements) as block_id3,a.block_term_expire FROM ".PREFIX."_blocks_".$this->registry->sitelang." a ORDER BY a.block_arrangements, a.block_priority";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist > 0) {
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
                $this->result['block_all'] = $block_all;
            } else {
                $this->result['block_all'] = "no";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'show_sql_error';
            $this->error_array = $error;
            return;
        }
    }


    public function blockorder($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['block_newpriority'])) {
            $block_newpriority = intval($array['block_newpriority']);
        }
        if(isset($array['block_priority'])) {
            $block_priority = intval($array['block_priority']);
        }
        if(isset($array['block_nextid'])) {
            $block_nextid = intval($array['block_nextid']);
        }
        if(isset($array['block_id'])) {
            $block_id = intval($array['block_id']);
        }
        if((!isset($array['block_id']) or !isset($array['block_newpriority']) or !isset($array['block_priority']) or !isset($array['block_nextid'])) or ($block_id == 0 or $block_newpriority == 0 or $block_priority == 0 or $block_nextid == 0)) {
            $this->error = 'empty_data';
            return;
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
        if($result === false or $result2 === false) {
            if($num_result1 > 0 or $num_result2 > 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            }
            $this->error = 'show_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $result2 !== false and $num_result1 == 0 and $num_result2 == 0) {
            $this->error = 'order_ok';
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->result = 'order_done';
        }
    }


    public function select_block_type() {
        $this->result = false;
        $this->result['block_select_type'] = "yes";
    }


    public function add_block($block_type) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($block_type)) {
            $block_type = trim($block_type);
        }
        if(empty($block_type)) {
            $this->error = 'add_not_all_data';
            return;
        }
        $this->result['block_select_type'] = "no";
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
                            $row_exist = intval($result->fields['0']);
                        } else {
                            $row_exist = 0;
                        }
                        $blocks_list[$i] = $this->registry->main_class->extracting_data($blocks_list[$i]);
                        if($row_exist == 0 and $blocks_list[$i] != "") {
                            $block_name[] = $blocks_list[$i];
                        }
                    } else {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->error = 'add_sql_error';
                        $this->error_array = $error;
                        return;
                    }
                }
            }
            if(!isset($block_name)) {
                $this->error = 'add_no_file';
                return;
            } else {
                $this->result['block_name'] = $block_name;
            }
            $this->result['block_type'] = "file";
        } elseif($block_type == "content") {
            $this->result['block_type'] = "content";
        } elseif($block_type == "rss") {
            $sql = "SELECT rss_id,rss_site_name,rss_site_url FROM ".PREFIX."_rss_".$this->registry->sitelang;
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
                $this->error = 'add_sql_error';
                $this->error_array = $error;
                return;
            }
            if(!isset($rss_all)) {
                $this->result['rss_all'] = "no";
            } else {
                $this->result['rss_all'] = $rss_all;
            }
            $this->result['block_type'] = "rss";
        } else {
            $this->error = 'unknown_block_type';
            return;
        }
    }


    public function add_block_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'block_type',
                    'block_custom_name',
                    'block_file',
                    'block_arrangements',
                    'block_termexpireaction',
                    'block_content',
                    'block_url',
                    'rss_url'
                );
                $keys2 = array(
                    'block_status',
                    'block_termexpire',
                    'block_valueview',
                    'block_refresh',
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if((!isset($array['block_type']) or !isset($array['block_custom_name']) or !isset($array['block_arrangements']) or !isset($array['block_status']) or !isset($array['block_termexpire']) or !isset($array['block_termexpireaction']) or !isset($array['block_valueview']) or !isset($array['block_content']) or !isset($array['block_url']) or !isset($array['block_refresh']) or !isset($array['rss_url'])) or ($data_array['block_type'] == "" or $data_array['block_custom_name'] == "" or $data_array['block_arrangements'] == "" or $data_array['block_termexpireaction'] == "")) {
            $this->error = 'add_not_all_data';
            return;
        }
        $data_array['block_file'] = $this->registry->main_class->format_striptags($data_array['block_file']);
        $data_array['block_file'] = $this->registry->main_class->processing_data($data_array['block_file']);
        $data_array['block_file'] = substr(substr($data_array['block_file'],0,-1),1);
        $data_array['block_custom_name'] = $this->registry->main_class->format_striptags($data_array['block_custom_name']);
        $data_array['block_custom_name'] = $this->registry->main_class->processing_data($data_array['block_custom_name']);
        $data_array['block_custom_name'] = substr(substr($data_array['block_custom_name'],0,-1),1);
        $data_array['block_arrangements'] = $this->registry->main_class->format_striptags($data_array['block_arrangements']);
        $data_array['block_arrangements'] = $this->registry->main_class->processing_data($data_array['block_arrangements']);
        $data_array['block_arrangements'] = substr(substr($data_array['block_arrangements'],0,-1),1);
        $sql = "SELECT (SELECT MAX(a.block_priority) FROM ".PREFIX."_blocks_".$this->registry->sitelang." a WHERE a.block_arrangements='".$data_array['block_arrangements']."') as block_priority,(SELECT MAX(b.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." b WHERE b.block_custom_name='".$data_array['block_custom_name']."') as check_block_custom_name ".$this->registry->main_class->check_db_need_from_clause();
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
                $this->error = 'add_found';
                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'add_sql_error';
            $this->error_array = $error;
            return;
        }
        if($data_array['block_termexpire'] == "0") {
            $data_array['block_termexpire'] = 'NULL';
            $data_array['block_termexpireaction'] = 'NULL';
        } else {
            $data_array['block_termexpire'] = $this->registry->main_class->get_time() + ($data_array['block_termexpire'] * 86400);
            $data_array['block_termexpire'] = "'".$data_array['block_termexpire']."'";
            $data_array['block_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['block_termexpireaction']);
            $data_array['block_termexpireaction'] = $this->registry->main_class->processing_data($data_array['block_termexpireaction']);
        }
        $data_array['block_type'] = $this->registry->main_class->format_striptags($data_array['block_type']);
        if($data_array['block_type'] == "file") {
            if(empty($data_array['block_file'])) {
                $this->error = 'add_not_all_data';
                return;
            } else {
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_blocks_id_".$this->registry->sitelang,'block_id');
                $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (".$sequence_array['field_name_string']."block_name,block_custom_name,block_status,block_value_view,block_arrangements,block_priority,block_term_expire,block_term_expire_action)  VALUES (".$sequence_array['sequence_value_string']."'".$data_array['block_file']."','".$data_array['block_custom_name']."','".$data_array['block_status']."','".$data_array['block_valueview']."','".$data_array['block_arrangements']."','".$block_priority."',".$data_array['block_termexpire'].",".$data_array['block_termexpireaction'].")";
                $result = $this->db->Execute($sql);
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        } elseif($data_array['block_type'] == "content") {
            if(empty($data_array['block_content'])) {
                $this->error = 'add_not_all_data';
                return;
            } else {
                $data_array['block_content'] = $this->registry->main_class->processing_data($data_array['block_content']);
                $data_array['block_content'] = substr(substr($data_array['block_content'],0,-1),1);
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if($check_db_need_lobs == 'yes') {
                    $this->db->StartTrans();
                    $block_add_id = $this->db->GenID(PREFIX."_blocks_id_".$this->registry->sitelang);
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_content,block_custom_name,block_status,block_value_view,block_arrangements,block_priority,block_term_expire, block_term_expire_action) VALUES ($block_add_id,empty_clob(),'".$data_array['block_custom_name']."','".$data_array['block_status']."','".$data_array['block_valueview']."','".$data_array['block_arrangements']."','".$block_priority."',".$data_array['block_termexpire'].",".$data_array['block_termexpireaction'].")";
                    $result2 = $this->db->Execute($sql);
                    if($result2 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$data_array['block_content'],'block_id='.$block_add_id);
                    $this->db->CompleteTrans();
                } else {
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_content,block_custom_name,block_status,block_value_view,block_arrangements,block_priority,block_term_expire, block_term_expire_action)  VALUES ('".$data_array['block_content']."','".$data_array['block_custom_name']."','".$data_array['block_status']."','".$data_array['block_valueview']."','".$data_array['block_arrangements']."','".$block_priority."',".$data_array['block_termexpire'].",".$data_array['block_termexpireaction'].")";
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
                    if($this->registry->main_class->check_player_entry($data_array['block_content'])) {
                        $this->registry->main_class->clearAllCache();
                    }
                }
            }
        } elseif($data_array['block_type'] == "rss") {
            if(!empty($data_array['rss_url'])) {
                $data_array['block_url'] = $data_array['rss_url'];
            }
            $data_array['block_url'] = $this->registry->main_class->format_striptags($data_array['block_url']);
            if(empty($data_array['block_url'])) {
                $this->error = 'add_not_all_data';
                return;
            } else {
                if($data_array['block_url'] != "") {
                    $time = $this->registry->main_class->get_time();
                    if(!preg_match("/http:\\//i",$data_array['block_url'])) {
                        $data_array['block_url'] = "http://".$data_array['block_url'];
                    }
                    $admin_rss_action = 'add';
                    $get_result = $this->registry->main_class->block_rss(false,false,$data_array['block_url'],false,false,false,$admin_rss_action);
                    if(!empty($get_result['rss_link']) and $get_result['rss_link'] === 'no') {
                        $this->error = 'error_url';
                        return;
                    } elseif(!empty($get_result['rss_link']) and $get_result['rss_link'] === 'no2') {
                        $content = "";
                    } elseif($get_result) {
                        $content = $get_result;
                    }
                }
                if(empty($content)) {
                    $this->error = 'error_url';
                    return;
                }
                $data_array['block_url'] = $this->registry->main_class->processing_data($data_array['block_url']);
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if($check_db_need_lobs == 'yes') {
                    $this->db->StartTrans();
                    $block_add_id = $this->db->GenID(PREFIX."_blocks_id_".$this->registry->sitelang);
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_id,block_url,block_custom_name,block_status,block_value_view,block_content,block_arrangements,block_priority,block_refresh,block_last_refresh,block_term_expire, block_term_expire_action)  VALUES (".$block_add_id.",".$data_array['block_url'].",'".$data_array['block_custom_name']."','".$data_array['block_status']."','".$data_array['block_valueview']."',empty_clob(),'".$data_array['block_arrangements']."','".$block_priority."','".$data_array['block_refresh']."','".$time."',".$data_array['block_termexpire'].",".$data_array['block_termexpireaction'].")";
                    $result2 = $this->db->Execute($sql);
                    if($result2 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$content,'block_id='.$block_add_id);
                    $this->db->CompleteTrans();
                } else {
                    $sql = "INSERT INTO ".PREFIX."_blocks_".$this->registry->sitelang." (block_url,block_custom_name,block_status,block_value_view,block_content,block_arrangements,block_priority,block_refresh,block_last_refresh,block_term_expire, block_term_expire_action)  VALUES (".$data_array['block_url'].",'".$data_array['block_custom_name']."','".$data_array['block_status']."','".$data_array['block_valueview']."','".$content."','".$data_array['block_arrangements']."','".$block_priority."','".$data_array['block_refresh']."','".$time."',".$data_array['block_termexpire'].",".$data_array['block_termexpireaction'].")";
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
            $this->error = 'unknown_block_type';
            return;
        }
        if($result === false) {
            $this->error = 'add_sql_error';
            $this->error_array = $error;
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->result = 'yes';
        }
    }


    public function block_status($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['action'])) {
            $action = trim($array['action']);
        }
        if(isset($array['block_id'])) {
            $block_id = intval($array['block_id']);
        }
        if((!isset($array['block_id']) or !isset($array['action'])) or ($block_id == 0 or $action == "")) {
            $this->error = 'not_all_data';
            return;
        }
        $action = $this->registry->main_class->format_striptags($action);
        if($action == "off") {
            $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_status='0' WHERE block_id='".$block_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        } elseif($action == "on") {
            $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_status='1' WHERE block_id='".$block_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
        } else {
            $this->error = 'unknown_action';
            return;
        }
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'status_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'status_ok';
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|file|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|rss|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|content|".$block_id);
            $this->result = $action;
        }
    }


    public function block_delete($block_id) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $block_id = intval($block_id);
        if($block_id == 0) {
            $this->error = 'not_all_data';
            return;
        }
        $sql = "SELECT block_priority,block_arrangements FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id = '".$block_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist > 0) {
                $block_priority = intval($result->fields['0']);
                $block_arrangements = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $this->db->StartTrans();
                $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority = block_priority-1 WHERE block_arrangements = '".$block_arrangements."' and block_priority > '".$block_priority."'");
                $sql = "DELETE FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id = '".$block_id."'";
                $result = $this->db->Execute($sql);
                if($result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $this->db->CompleteTrans();
            } else {
                $this->error = 'status_not_found';
                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'status_sql_error';
            $this->error_array = $error;
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|file|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|rss|".$block_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|content|".$block_id);
            $this->result = "delete";
        }
    }


    public function block_edit($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['block_type'])) {
            $block_type = trim($array['block_type']);
        }
        if(isset($array['block_id'])) {
            $block_id = intval($array['block_id']);
        }
        if((!isset($array['block_id']) or !isset($array['block_type'])) or ($block_id == 0 or $block_type == "")) {
            $this->error = 'not_all_data';
            return;
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
                    $sql = "SELECT block_id FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_name = '".$blocks_list[$i]."'";
                    $result = $this->db->Execute($sql);
                    if($result) {
                        if(isset($result->fields['0'])) {
                            $row_exist = intval($result->fields['0']);
                        } else {
                            $row_exist = 0;
                        }
                        $blocks_list[$i] = $this->registry->main_class->extracting_data($blocks_list[$i]);
                        if(($row_exist == 0 and $blocks_list[$i] != "") or (intval($result->fields['0']) == $block_id)) {
                            $block_name[]['block_name'] = $blocks_list[$i];
                        }
                    } else {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->error = 'edit_sql_error';
                        $this->error_array = $error;
                        return;
                    }
                }
            }
            if(!isset($block_name)) {
                $this->result['block_name'] = "no";
            } else {
                $this->result['block_name'] = $block_name;
            }
            $this->result['block_type'] = "file";
        } elseif($block_type == "content") {
            $this->result['block_type'] = "content";
        } elseif($block_type == "rss") {
            $sql = "SELECT rss_id,rss_site_name,rss_site_url FROM ".PREFIX."_rss_".$this->registry->sitelang;
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
                $this->error = 'edit_sql_error';
                $this->error_array = $error;
                return;
            }
            if(!isset($rss_all)) {
                $this->result['rss_all'] = "no";
            } else {
                $this->result['rss_all'] = $rss_all;
            }
            $this->result['block_type'] = "rss";
        } else {
            $this->error = 'unknown_block_type';
            return;
        }
        $sql = "SELECT block_id,block_name,block_custom_name,block_version,block_status,block_value_view,block_content,block_url,block_arrangements,block_refresh,block_last_refresh,block_term_expire,block_term_expire_action FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id = '".$block_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist > 0) {
                if(isset($result->fields['11']) and intval($result->fields['11']) != 0 and intval($result->fields['11']) !== "") {
                    $block_termexpire = intval($result->fields['11']);
                    $block_termexpire = date("d.m.y H:i",$block_termexpire);
                    $block_termexpireday = intval(((intval($result->fields['11']) - $this->registry->main_class->get_time()) / 3600) / 24);
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
                $this->result['block_all'] = $block_all;
            } else {
                $this->error = 'edit_not_found';
                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        }
    }


    public function block_edit_save($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'block_type',
                    'block_custom_name',
                    'block_file',
                    'block_arrangements',
                    'block_termexpireaction',
                    'block_content',
                    'block_url',
                    'rss_url'
                );
                $keys2 = array(
                    'block_id',
                    'block_status',
                    'block_termexpire',
                    'block_valueview',
                    'block_refresh',
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if((!isset($array['block_id']) or !isset($array['block_type']) or !isset($array['block_custom_name']) or !isset($array['block_file']) or !isset($array['block_arrangements']) or !isset($array['block_status']) or !isset($array['block_termexpire']) or !isset($array['block_termexpireaction']) or !isset($array['block_valueview']) or !isset($array['block_content']) or !isset($array['block_url']) or !isset($array['block_refresh']) or !isset($array['rss_url'])) or ($data_array['block_type'] == "" or $data_array['block_id'] == 0 or $data_array['block_custom_name'] == "" or $data_array['block_arrangements'] == "" or $data_array['block_termexpireaction'] == "")) {
            $this->error = 'edit_not_all_data';
            return;
        }
        $data_array['block_custom_name'] = $this->registry->main_class->format_striptags($data_array['block_custom_name']);
        $data_array['block_custom_name'] = $this->registry->main_class->processing_data($data_array['block_custom_name']);
        $data_array['block_arrangements'] = $this->registry->main_class->format_striptags($data_array['block_arrangements']);
        $data_array['block_arrangements'] = $this->registry->main_class->processing_data($data_array['block_arrangements']);
        $sql = "select a.block_arrangements,(select max(b.block_priority) from ".PREFIX."_blocks_".$this->registry->sitelang." b where b.block_arrangements = ".$data_array['block_arrangements'].") as block_new_priority,(SELECT max(c.block_id) FROM ".PREFIX."_blocks_".$this->registry->sitelang." c WHERE c.block_custom_name = ".$data_array['block_custom_name']." and c.block_id <> '".$data_array['block_id']."') as check_custom_name,a.block_priority from ".PREFIX."_blocks_".$this->registry->sitelang." a where a.block_id = '".$data_array['block_id']."'";
        $result0 = $this->db->Execute($sql);
        if($result0 and isset($result0->fields['0']) and isset($result0->fields['3']) and trim($result0->fields['0']) !== '' and intval($result0->fields['3']) != 0) {
            $block_old_arrangements = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result0->fields['0']));
            $data_array['block_arrangements'] = substr(substr($data_array['block_arrangements'],0,-1),1);
            if($this->registry->main_class->extracting_data($data_array['block_arrangements']) !== "$block_old_arrangements") {
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
                $this->error = 'edit_found';
                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        }
        if($data_array['block_termexpire'] == "0") {
            $data_array['block_termexpire'] = 'NULL';
            $data_array['block_termexpireaction'] = 'NULL';
        } else {
            $data_array['block_termexpire'] = $this->registry->main_class->get_time() + ($data_array['block_termexpire'] * 86400);
            $data_array['block_termexpire'] = "'".$data_array['block_termexpire']."'";
            $data_array['block_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['block_termexpireaction']);
            $data_array['block_termexpireaction'] = $this->registry->main_class->processing_data($data_array['block_termexpireaction']);
        }
        $data_array['block_type'] = $this->registry->main_class->format_striptags($data_array['block_type']);
        if($data_array['block_type'] == "file") {
            if(empty($data_array['block_file'])) {
                $this->error = 'edit_not_all_data';
                return;
            }
            $data_array['block_file'] = $this->registry->main_class->format_striptags($data_array['block_file']);
            $data_array['block_file'] = $this->registry->main_class->processing_data($data_array['block_file']);
            $this->db->StartTrans();
            if($needupdate == "yes") {
                $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_name = ".$data_array['block_file'].",block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_arrangements = '".$data_array['block_arrangements']."',block_priority = '".$block_new_priority."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority = block_priority-1 WHERE block_arrangements = '".$block_old_arrangements."' and block_priority > '".$block_old_priority."'");
            } else {
                $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_name = ".$data_array['block_file'].",block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
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
        } elseif($data_array['block_type'] == "content") {
            if(empty($data_array['block_content'])) {
                $this->error = 'edit_not_all_data';
                return;
            }
            $data_array['block_content'] = $this->registry->main_class->processing_data($data_array['block_content']);
            $data_array['block_content'] = substr(substr($data_array['block_content'],0,-1),1);
            $this->db->StartTrans();
            $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
            if($needupdate == "yes") {
                if($check_db_need_lobs == 'yes') {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = empty_clob(),block_arrangements = '".$data_array['block_arrangements']."',block_priority = '".$block_new_priority."', block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                    $result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$data_array['block_content'],'block_id = '.$data_array['block_id']);
                    if($result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = '".$data_array['block_content']."',block_arrangements = '".$data_array['block_arrangements']."',block_priority = '".$block_new_priority."', block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                    $result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result3 = true;
                }
                $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority = block_priority-1 WHERE block_arrangements = '".$block_old_arrangements."' and block_priority > '".$block_old_priority."'");
            } else {
                if($check_db_need_lobs == 'yes') {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = empty_clob(),block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                    $result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$data_array['block_content'],'block_id = '.$data_array['block_id']);
                    if($result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = '".$data_array['block_content']."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
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
                if($this->registry->main_class->check_player_entry($data_array['block_content'])) {
                    $this->registry->main_class->clearAllCache();
                }
            }
        } elseif($data_array['block_type'] == "rss") {
            if($data_array['rss_url'] != "" and $data_array['rss_url'] != "0") {
                $data_array['block_url'] = $data_array['rss_url'];
            }
            $data_array['block_url'] = $this->registry->main_class->format_striptags($data_array['block_url']);
            if(empty($data_array['block_url'])) {
                $this->error = 'edit_not_all_data';
                return;
            }
            if($data_array['block_url'] != "") {
                $time = $this->registry->main_class->get_time();
                if(!preg_match("/http:\\//i",$data_array['block_url'])) {
                    $data_array['block_url'] = "http://".$data_array['block_url'];
                }
                $admin_rss_action = true;
                $get_result = $this->registry->main_class->block_rss(false,false,$data_array['block_url'],false,false,false,$admin_rss_action);
                if(!empty($get_result['rss_link']) and $get_result['rss_link'] === 'no') {
                    $this->error = 'edit_block_rss_url';
                    return;
                } elseif(!empty($get_result['rss_link']) and $get_result['rss_link'] === 'no2') {
                    $content = "";
                } elseif($get_result) {
                    $content = $get_result;
                }
            }
            if(empty($content)) {
                $this->error = 'edit_block_rss_url';
                return;
            }
            $data_array['block_url'] = $this->registry->main_class->processing_data($data_array['block_url']);
            $this->db->StartTrans();
            $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
            if($needupdate == "yes") {
                if($check_db_need_lobs == 'yes') {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = empty_clob(),block_url = ".$data_array['block_url'].",block_arrangements = '".$data_array['block_arrangements']."',block_priority = '".$block_new_priority."',block_refresh = '".$data_array['block_refresh']."',block_last_refresh = '".$time."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                    $result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$content,'block_id = '.$data_array['block_id']);
                    if($result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = '".$content."',block_url = ".$data_array['block_url'].",block_arrangements = '".$data_array['block_arrangements']."',block_priority = '".$block_new_priority."',block_refresh = '".$data_array['block_refresh']."',block_last_refresh = '".$time."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                    $result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result3 = true;
                }
                $result2 = $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_priority = block_priority-1 WHERE block_arrangements = '".$block_old_arrangements."' and block_priority > '".$block_old_priority."'");
            } else {
                if($check_db_need_lobs == 'yes') {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = empty_clob(),block_url = ".$data_array['block_url'].",block_refresh = '".$data_array['block_refresh']."',block_last_refresh = '".$time."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
                    $result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $result3 = $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$content,'block_id = '.$data_array['block_id']);
                    if($result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_custom_name = ".$data_array['block_custom_name'].",block_status = '".$data_array['block_status']."',block_value_view = '".$data_array['block_valueview']."',block_content = '".$content."',block_url = ".$data_array['block_url'].",block_refresh = '".$data_array['block_refresh']."',block_last_refresh = '".$time."',block_term_expire = ".$data_array['block_termexpire'].",block_term_expire_action = ".$data_array['block_termexpireaction']." WHERE block_id = '".$data_array['block_id']."'";
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
        } else {
            $this->error = 'unknown_block_type';
            return;
        }
        if($result === false) {
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'edit_ok';
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit|".$data_array['block_type']."|".$data_array['block_id']);
            $this->result['block_type'] = $data_array['block_type'];
        }
    }


    public function block_rsssite($num_page = false) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM ".PREFIX."_rss_".$this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT rss_id,rss_site_name,rss_site_url FROM ".PREFIX."_rss_".$this->registry->sitelang." order by rss_id DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        }
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist == 0 and $num_page > 1) {
                $this->error = 'unknown_page';
                return;
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $rss_all[] = array(
                        "rss_id" => intval($result->fields['0']),
                        "rss_sitename" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "rss_siteurl" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']))
                    );
                    $result->MoveNext();
                }
                if(isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if($num_pages > 1) {
                    if($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
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
                        $this->result['nextpage'] = $nextpage;
                    } else {
                        $this->result['nextpage'] = "no";
                    }
                    $this->result['html'] = $html;
                    $this->result['totalrss'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'rss_sql_error';
            $this->error_array = $error;
            return;
        }
        if(isset($rss_all)) {
            $this->result['rss_all'] = $rss_all;
        } else {
            $this->result['rss_all'] = "no";
        }
        $this->result['addrss_inbase'] = "no";
        $this->result['editrss'] = "hidden";
    }


    public function block_addrsssite() {
        $this->result = false;
        $this->result['addrss_inbase'] = "no";
        $this->result['editrss'] = "no";
    }


    public function block_addrsssite_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'rss_sitename',
                    'rss_siteurl'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if((!isset($array['rss_sitename']) or !isset($array['rss_siteurl'])) or ($data_array['rss_sitename'] == "" or $data_array['rss_siteurl'] == "")) {
            $this->error = 'add_rss_not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                'rss_sitename',
                'rss_siteurl'
            );
            if(in_array($key,$keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_rss_id_".$this->registry->sitelang,'rss_id');
        $sql = "INSERT INTO ".PREFIX."_rss_".$this->registry->sitelang." (".$sequence_array['field_name_string']."rss_site_name,rss_site_url) VALUES (".$sequence_array['sequence_value_string']."".$data_array['rss_sitename'].",".$data_array['rss_siteurl'].")";
        $result = $this->db->Execute($sql);
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'add_rss_sql_error';
            $this->error_array = $error;
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|rss");
            $this->result['addrss_inbase'] = 'add_rss_ok';
        }
    }


    public function block_editrsssite($rss_id) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $rss_id = intval($rss_id);
        if($rss_id == 0) {
            $this->error = 'empty_rss_id';
            return;
        }
        $sql = "SELECT rss_id,rss_site_name,rss_site_url FROM ".PREFIX."_rss_".$this->registry->sitelang." WHERE rss_id = ".$rss_id;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist > 0) {
                $rss_edit_all[] = array(
                    "rss_id" => intval($result->fields['0']),
                    "rss_sitename" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                    "rss_siteurl" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']))
                );
                $this->result['rss_edit_all'] = $rss_edit_all;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_rss_sql_error';
            $this->error_array = $error;
            return;
        }
        if(!isset($rss_edit_all)) {
            $this->error = 'edit_rss_not_found';
            return;
        }
        $this->result['addrss_inbase'] = "no";
        $this->result['editrss'] = "yes";
    }


    public function block_editrsssite_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'rss_sitename',
                    'rss_siteurl'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if(isset($array['rss_id'])) {
            $data_array['rss_id'] = intval($array['rss_id']);
        }
        if((!isset($array['rss_id']) or !isset($array['rss_sitename']) or !isset($array['rss_siteurl'])) or ($data_array['rss_id'] == 0 or $data_array['rss_sitename'] == "" or $data_array['rss_siteurl'] == "")) {
            $this->error = 'edit_rss_not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                'rss_sitename',
                'rss_siteurl'
            );
            if(in_array($key,$keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $sql = "UPDATE ".PREFIX."_rss_".$this->registry->sitelang." SET rss_site_name = ".$data_array['rss_sitename'].",rss_site_url = ".$data_array['rss_siteurl']." WHERE rss_id = '".$data_array['rss_id']."'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'edit_rss_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'edit_rss_ok';
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|rss");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$data_array['rss_id']);
            $this->result['addrss_inbase'] = 'edit_rss_done';
        }
    }


    public function block_deletersssite($rss_id) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $rss_id = intval($rss_id);
        if($rss_id == 0) {
            $this->error = 'empty_rss_id';
            return;
        }
        $sql = "DELETE FROM ".PREFIX."_rss_".$this->registry->sitelang." WHERE rss_id = '".$rss_id."'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'del_rss_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'del_rss_ok';
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|rss");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|editrss|".$rss_id);
            $this->result['addrss_inbase'] = 'delete_rss_ok';
        }
    }
}
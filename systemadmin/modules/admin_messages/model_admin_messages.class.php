<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_messages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class admin_messages extends model_base {


    private $error;
    private $error_array;
    private $result;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->messages_check_process();
    }


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index($num_page) {
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
        $sql = "SELECT count(*) FROM ".PREFIX."_messages_".$this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT message_id,message_author,message_title,message_term_expire,message_status,message_value_view FROM ".PREFIX."_messages_".$this->registry->sitelang." order by message_date DESC";
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
                    $message_termexpire = intval($result->fields['3']);
                    if(!isset($message_termexpire) or $message_termexpire == "" or $message_termexpire == 0) {
                        $message_termexpire = "unlim";
                    } else {
                        $message_termexpire = "notunlim";
                    }
                    $message_all[] = array(
                        "message_id" => intval($result->fields['0']),
                        "message_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
                        "message_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
                        "message_valueview" => intval($result->fields['5']),
                        "message_termexpire" => $message_termexpire,
                        "message_status" => intval($result->fields['4'])
                    );
                    $result->MoveNext();
                }
                $this->result['message_all'] = $message_all;
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
                    $this->result['totalmessages'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['message_all'] = "no";
                $this->result['html'] = "no";
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


    public function message_add() {
        $this->result = false;
        $this->result['message_author'] = trim($this->registry->main_class->get_user_login());
    }


    public function message_add_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'message_title',
                    'message_content',
                    'message_notes',
                    'message_termexpireaction'
                );
                $keys2 = array(
                    'message_status',
                    'message_termexpire',
                    'message_valueview'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if((!isset($array['message_title']) or !isset($array['message_content']) or !isset($array['message_notes']) or !isset($array['message_status']) or !isset($array['message_termexpire']) or !isset($array['message_termexpireaction']) or !isset($array['message_valueview'])) or ($data_array['message_title'] == "" or $data_array['message_content'] == "")) {
            $this->error = 'add_not_all_data';
            return;
        }
        $message_author = trim($this->registry->main_class->get_user_login());
        if($data_array['message_termexpire'] == "0") {
            $data_array['message_termexpire'] = 'NULL';
            $data_array['message_termexpireaction'] = 'NULL';
        } else {
            $data_array['message_termexpire'] = $this->registry->main_class->get_time() + ($data_array['message_termexpire'] * 86400);
            $data_array['message_termexpire'] = "'".$data_array['message_termexpire']."'";
            $data_array['message_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['message_termexpireaction']);
            $data_array['message_termexpireaction'] = $this->registry->main_class->processing_data($data_array['message_termexpireaction']);
        }
        if($data_array['message_notes'] == "") {
            $data_array['message_notes'] = 'NULL';
        } else {
            $data_array['message_notes'] = $this->registry->main_class->processing_data($data_array['message_notes']);
        }
        $now = $this->registry->main_class->get_time();
        $message_author = $this->registry->main_class->format_striptags($message_author);
        $message_author = $this->registry->main_class->processing_data($message_author);
        $data_array['message_title'] = $this->registry->main_class->format_striptags($data_array['message_title']);
        $data_array['message_title'] = $this->registry->main_class->processing_data($data_array['message_title']);
        $data_array['message_content'] = $this->registry->main_class->processing_data($data_array['message_content']);
        $data_array['message_content'] = substr(substr($data_array['message_content'],0,-1),1);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $message_id = $this->db->GenID(PREFIX."_messages_id_".$this->registry->sitelang);
            $sql = "INSERT INTO ".PREFIX."_messages_".$this->registry->sitelang." (message_id,message_author,message_title,message_date,message_content,message_notes,message_value_view,message_status,message_term_expire,message_term_expire_action)  VALUES ('".$message_id."',".$message_author.",".$data_array['message_title'].",'".$now."',empty_clob(),empty_clob(),'".$data_array['message_valueview']."','".$data_array['message_status']."',".$data_array['message_termexpire'].",".$data_array['message_termexpireaction'].")";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_content',$data_array['message_content'],'message_id = '.$message_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($data_array['message_notes'] != 'NULL') {
                $data_array['message_notes'] = substr(substr($data_array['message_notes'],0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_notes',$data_array['message_notes'],'message_id = '.$message_id);
                if($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $result3 = true;
            }
            $this->db->CompleteTrans();
            if($result2 === false or $result3 === false) {
                $result = false;
            }
        } else {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_messages_id_".$this->registry->sitelang,'message_id');
            $sql = "INSERT INTO ".PREFIX."_messages_".$this->registry->sitelang." (".$sequence_array['field_name_string']."message_author,message_title,message_date,message_content,message_notes,message_value_view,message_status,message_term_expire,message_term_expire_action)  VALUES (".$sequence_array['sequence_value_string']."".$message_author.",".$data_array['message_title'].",'".$now."','".$data_array['message_content']."',".$data_array['message_notes'].",'".$data_array['message_valueview']."','".$data_array['message_status']."',".$data_array['message_termexpire'].",".$data_array['message_termexpireaction'].")";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->error = 'add_sql_error';
            $this->error_array = $error;
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            $this->result = 'add_done';
        }
    }


    public function message_status($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['get_action'])) {
            $action = trim($array['get_action']);
        } else {
            $action = "";
        }
        if(!isset($array['get_action']) and isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }
        if(isset($array['get_message_id'])) {
            $message_id = intval($array['get_message_id']);
        } else {
            $message_id = 0;
        }
        if($message_id == 0 and isset($array['post_message_id']) and is_array($array['post_message_id']) and count($array['post_message_id']) > 0) {
            $message_id_is_arr = 1;
            for($i = 0,$size = count($array['post_message_id']);$i < $size;++$i) {
                if($i == 0) {
                    $message_id = "'".intval($array['post_message_id'][$i])."'";
                } else {
                    $message_id .= ",'".intval($array['post_message_id'][$i])."'";
                }
            }
        } else {
            $message_id_is_arr = 0;
            $message_id = "'".$message_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($message_id) or $action == "" or $message_id == "'0'") {
            $this->error = 'empty_data';
            return;
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_status = '1' WHERE message_id IN (".$message_id.")";
            $result = $this->db->Execute($sql);
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_status = '0' WHERE message_id IN (".$message_id.")";
            $result = $this->db->Execute($sql);
        } elseif($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_messages_".$this->registry->sitelang." WHERE message_id IN (".$message_id.")";
            $result = $this->db->Execute($sql);
        } else {
            $this->error = 'unknown_action';
            return;
        }
        if($result) {
            $num_result = $this->db->Affected_Rows();
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
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            if($message_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit");
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit|".intval($array['get_message_id']));
            }
            $this->result = $action;
        }
    }


    public function message_edit($message_id) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $message_id = intval($message_id);
        if($message_id == 0) {
            $this->error = 'empty_data';
            return;
        }
        $sql = "SELECT message_id,message_author,message_title,message_date,message_content,message_notes,message_value_view,message_status,message_term_expire,message_term_expire_action FROM ".PREFIX."_messages_".$this->registry->sitelang." WHERE message_id = '".$message_id."'";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        }
        if($row_exist == 0) {
            $this->error = 'edit_not_found';
            return;
        }
        if(isset($result->fields['8']) and intval($result->fields['8']) !== "") {
            $message_termexpire = intval($result->fields['8']);
            $message_termexpire = date("d.m.y H:i",$message_termexpire);
            $message_termexpireday = intval(((intval($result->fields['8']) - $this->registry->main_class->get_time()) / 3600) / 24);
        } else {
            $message_termexpire = "";
            $message_termexpireday = "";
        }
        $message_content = $this->registry->main_class->extracting_data($result->fields['4']);
        $message_all[] = array(
            "message_id" => intval($result->fields['0']),
            "message_author" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
            "message_title" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2'])),
            "message_date" => intval($result->fields['3']),
            "message_content" => $message_content,
            "message_notes" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5'])),
            "message_valueview" => intval($result->fields['6']),
            "message_status" => intval($result->fields['7']),
            "message_termexpire" => $message_termexpire,
            "message_termexpireaction" => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9'])),
            "message_termexpireday" => $message_termexpireday
        );
        $this->result['message_all'] = $message_all;
    }


    public function message_edit_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'message_author',
                    'message_title',
                    'message_content',
                    'message_notes',
                    'message_termexpireaction'
                );
                $keys2 = array(
                    'message_id',
                    'message_status',
                    'message_termexpire',
                    'message_valueview'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if((!isset($array['message_id']) or !isset($array['message_author']) or !isset($array['message_title']) or !isset($array['message_content']) or !isset($array['message_notes']) or !isset($array['message_status']) or !isset($array['message_termexpire']) or !isset($array['message_termexpireaction']) or !isset($array['message_valueview'])) or ($data_array['message_id'] == 0 or $data_array['message_author'] == "" or $data_array['message_title'] == "" or $data_array['message_content'] == "")) {
            $this->error = 'edit_not_all_data';
            return;
        }
        if($data_array['message_termexpire'] == "0") {
            $data_array['message_termexpire'] = 'NULL';
            $data_array['message_termexpireaction'] = 'NULL';
        } else {
            $data_array['message_termexpire'] = $this->registry->main_class->get_time() + ($data_array['message_termexpire'] * 86400);
            $data_array['message_termexpire'] = "'".$data_array['message_termexpire']."'";
            $data_array['message_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['message_termexpireaction']);
            $data_array['message_termexpireaction'] = $this->registry->main_class->processing_data($data_array['message_termexpireaction']);
        }
        if($data_array['message_notes'] == "") {
            $data_array['message_notes'] = 'NULL';
        } else {
            $data_array['message_notes'] = $this->registry->main_class->processing_data($data_array['message_notes']);
        }
        $data_array['message_author'] = $this->registry->main_class->format_striptags($data_array['message_author']);
        $data_array['message_author'] = $this->registry->main_class->processing_data($data_array['message_author']);
        $data_array['message_title'] = $this->registry->main_class->format_striptags($data_array['message_title']);
        $data_array['message_title'] = $this->registry->main_class->processing_data($data_array['message_title']);
        $data_array['message_content'] = $this->registry->main_class->processing_data($data_array['message_content']);
        $data_array['message_content'] = substr(substr($data_array['message_content'],0,-1),1);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_author = ".$data_array['message_author'].",message_title = ".$data_array['message_title'].",message_content = empty_clob(),message_notes = empty_clob(),message_status = '".$data_array['message_status']."',message_term_expire = ".$data_array['message_termexpire'].",message_term_expire_action = ".$data_array['message_termexpireaction'].",message_value_view = '".$data_array['message_valueview']."' WHERE message_id = '".$data_array['message_id']."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_content',$data_array['message_content'],'message_id = '.$data_array['message_id']);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($data_array['message_notes'] != 'NULL') {
                $data_array['message_notes'] = substr(substr($data_array['message_notes'],0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_messages_'.$this->registry->sitelang,'message_notes',$data_array['message_notes'],'message_id = '.$data_array['message_id']);
                if($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $result3 = true;
            }
            $this->db->CompleteTrans();
            if($result2 === false or $result3 === false) {
                $result = false;
            }
        } else {
            $sql = "UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_author = ".$data_array['message_author'].",message_title = ".$data_array['message_title'].",message_content = '".$data_array['message_content']."',message_notes = ".$data_array['message_notes'].",message_status = '".$data_array['message_status']."',message_term_expire = ".$data_array['message_termexpire'].",message_term_expire_action = ".$data_array['message_termexpireaction'].",message_value_view = '".$data_array['message_valueview']."' WHERE message_id = '".$data_array['message_id']."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'edit_ok';
            return;
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit|".$data_array['message_id']);
            $this->result = 'edit_done';
        }
    }
}
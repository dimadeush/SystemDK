<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class admin_guestbook extends model_base {


    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['pend']) and intval($array['pend']) != 0 and intval($array['pend']) == 1) {
            $temp = "pending";
            $where = "post_status = '0'";
            $sql_order = "ASC";
        } else {
            $temp = "current";
            $where = "post_status = '1' or post_status = '2'";
            $sql_order = "DESC";
        }
        if(isset($array['num_page']) and intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM ".PREFIX."_guestbook WHERE ".$where;
        $result = $this->db->Execute($sql);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT post_id,post_user_name,post_user_email,post_user_id,post_user_ip,post_date,post_status,post_answer_date FROM ".PREFIX."_guestbook WHERE ".$where." order by post_date ".$sql_order;
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
                    $post_id = intval($result->fields['0']);
                    $post_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $post_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $post_user_id = intval($result->fields['3']);
                    if($post_user_id == 0) {
                        $post_user_id = "no";
                    }
                    $post_user_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $post_date = date("d.m.Y H:i",$this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5'])));
                    $post_status = intval($result->fields['6']);
                    $post_answer_date = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                    if($post_answer_date == "") {
                        $post_answer_date = "no";
                    } else {
                        $post_answer_date = date("d.m.Y H:i",$post_answer_date);
                    }
                    $posts_all[] = array(
                        "post_id" => $post_id,
                        "post_user_name" => $post_user_name,
                        "post_user_email" => $post_user_email,
                        "post_user_id" => $post_user_id,
                        "post_user_ip" => $post_user_ip,
                        "post_date" => $post_date,
                        "post_status" => $post_status,
                        "post_answer_date" => $post_answer_date
                    );
                    $result->MoveNext();
                }
                $this->result['posts_all'] = $posts_all;
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
                    $this->result['totalposts'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['posts_all'] = "no";
                $this->result['html'] = "no";
            }
            require_once('../modules/guestbook/guestbook_config.inc');
            if(defined('SYSTEMDK_GUESTBOOK_CONFIRM_NEED') and SYSTEMDK_GUESTBOOK_CONFIRM_NEED == "yes" and $temp == "current") {
                $this->result['systemdk_guestbook_confirm_need'] = "yes";
            } else {
                $this->result['systemdk_guestbook_confirm_need'] = "no";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'show_'.$temp.'_sql_error';
            $this->error_array = $error;
            return;
        }
        if($temp == "current") {
            $this->result['adminmodules_guestbook_pending'] = "no";
        } else {
            $this->result['adminmodules_guestbook_pending'] = "yes";
        }
    }


    public function guestbook_status($array) {
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
        if(isset($array['get_post_id'])) {
            $post_id = intval($array['get_post_id']);
        } else {
            $post_id = 0;
        }
        if($post_id == 0 and isset($array['post_post_id']) and is_array($array['post_post_id']) and count($array['post_post_id']) > 0) {
            $post_id_is_arr = 1;
            for($i = 0,$size = count($array['post_post_id']);$i < $size;++$i) {
                if($i == 0) {
                    $post_id = "'".intval($array['post_post_id'][$i])."'";
                } else {
                    $post_id .= ",'".intval($array['post_post_id'][$i])."'";
                }
            }
        } else {
            $post_id_is_arr = 0;
            $post_id = "'".$post_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($post_id) or $action == "" or $post_id == "'0'") {
            if($action == "add" or $action == "deletepend") {
                $this->error = 'empty_pend_data';
            } else {
                $this->error = 'empty_data';
            }
            return;
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_guestbook SET post_status = '1' WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_guestbook SET post_status = '2' WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
        } elseif($action == "delete" or $action == "deletepend") {
            $sql = "DELETE FROM ".PREFIX."_guestbook WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
        } elseif($action == "add") {
            $sql = "UPDATE ".PREFIX."_guestbook SET post_status = '1' WHERE post_id IN (".$post_id.")";
            $result = $this->db->Execute($sql);
        } else {
            $this->error = 'unknown_action';
            return;
        }
        if($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'status_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'status_ok';
            return;
        } else {
            if($action != "off" and $action != "on" and $action != "delete") {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showpending");
                if($post_id_is_arr == 1) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editpending");
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editpending|".intval($array['get_post_id']));
                }
            }
            if($action != "deletepend") {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
                $this->registry->main_class->systemdk_clearcache("modules|guestbook|display");
                if($post_id_is_arr == 1) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editcurrent");
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editcurrent|".intval($array['get_post_id']));
                }
            }
            $this->result = $action;
        }
    }


    public function guestbook_edit($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['pend']) and intval($array['pend']) != 0 and intval($array['pend']) == 1) {
            $temp = "pending";
            $where1 = "post_status = '0'";
        } else {
            $temp = "current";
            $where1 = "(post_status = '1' or post_status = '2')";
        }
        if(isset($array['post_id'])) {
            $post_id = intval($array['post_id']);
        }
        if(!isset($array['post_id']) or $post_id == 0) {
            $this->error = 'empty_data';
            return;
        }
        $sql = "SELECT post_id,post_user_name,post_user_country,post_user_city,post_user_telephone,post_show_telephone,post_user_email,post_show_email,post_user_id,post_user_ip,post_date,post_text,post_status,post_answer,post_answer_date FROM ".PREFIX."_guestbook WHERE ".$where1." and post_id = ".$post_id;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist > 0) {
                $post_id = intval($result->fields['0']);
                $post_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $post_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $post_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $post_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $post_show_telephone = intval($result->fields['5']);
                $post_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $post_show_email = intval($result->fields['7']);
                $post_user_id = intval($result->fields['8']);
                if($post_user_id == 0) {
                    $post_user_id = "no";
                }
                $post_user_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $post_date = intval($result->fields['10']);
                $post_date2 = date("d.m.Y",$post_date);
                $post_hour = date("H",$post_date);
                $post_minute = date("i",$post_date);
                $post_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                $post_status = intval($result->fields['12']);
                $post_answer = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                $post_answer_date = intval($result->fields['14']);
                if($post_answer_date == 0) {
                    $post_answer_date2 = "no";
                    $post_answer_hour = "no";
                    $post_answer_minute = "no";
                } else {
                    $post_answer_date2 = date("d.m.Y",$post_answer_date);
                    $post_answer_hour = date("H",$post_answer_date);
                    $post_answer_minute = date("i",$post_answer_date);
                }
                $posts_all[] = array(
                    "post_id" => $post_id,
                    "post_user_name" => $post_user_name,
                    "post_user_country" => $post_user_country,
                    "post_user_city" => $post_user_city,
                    "post_user_telephone" => $post_user_telephone,
                    "post_show_telephone" => $post_show_telephone,
                    "post_user_email" => $post_user_email,
                    "post_show_email" => $post_show_email,
                    "post_user_id" => $post_user_id,
                    "post_user_ip" => $post_user_ip,
                    "post_date" => $post_date2,
                    "post_hour" => $post_hour,
                    "post_minute" => $post_minute,
                    "post_text" => $post_text,
                    "post_status" => $post_status,
                    "post_answer" => $post_answer,
                    "post_answer_date" => $post_answer_date2,
                    "post_answer_hour" => $post_answer_hour,
                    "post_answer_minute" => $post_answer_minute
                );
                $this->result['posts_all'] = $posts_all;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        }
        if(!isset($posts_all)) {
            $this->error = 'edit_not_found';
            return;
        }
        if($temp == "current") {
            $this->result['adminmodules_guestbook_pending'] = "no";
        } else {
            $this->result['adminmodules_guestbook_pending'] = "yes";
        }
        $this->result['include_jquery'] = "yes";
        $this->result['include_jquery_data'] = "dateinput";
    }


    public function guestbook_edit_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'post_user_name',
                    'post_user_country',
                    'post_user_city',
                    'post_user_telephone',
                    'post_user_email',
                    'post_date',
                    'post_text',
                    'post_answer',
                    'post_answer_date',
                );
                $keys2 = array(
                    'post_id',
                    'post_show_telephone',
                    'post_show_email',
                    'post_hour',
                    'post_minute',
                    'post_status',
                    'post_answer_hour',
                    'post_answer_minute',
                    'pend'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(!isset($array['post_show_telephone'])) {
            $data_array['post_show_telephone'] = 1;
        }
        if(!isset($array['post_show_email'])) {
            $data_array['post_show_email'] = 1;
        }
        if(!isset($array['post_hour'])) {
            $data_array['post_hour'] = 0;
        }
        if(!isset($array['post_minute'])) {
            $data_array['post_minute'] = 0;
        }
        if(!isset($array['post_answer_hour'])) {
            $data_array['post_answer_hour'] = 0;
        }
        if(!isset($array['post_answer_minute'])) {
            $data_array['post_answer_minute'] = 0;
        }
        if(!isset($array['pend'])) {
            $data_array['pend'] = 0;
        }
        if($data_array['pend'] == 1) {
            $temp = "pending";
        } else {
            $temp = "current";
        }
        if((!isset($array['post_id']) or !isset($array['post_user_name']) or !isset($array['post_user_country']) or !isset($array['post_user_city']) or !isset($array['post_user_telephone']) or !isset($array['post_user_email']) or !isset($array['post_date']) or !isset($array['post_hour']) or !isset($array['post_minute']) or !isset($array['post_text']) or !isset($array['post_status']) or !isset($array['post_answer'])) or ($data_array['post_id'] == 0 or $data_array['post_user_name'] == "" or $data_array['post_user_email'] == "" or $data_array['post_date'] == "" or $data_array['post_text'] == "")) {
            $this->error = 'edit_not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                'post_user_name',
                'post_user_country',
                'post_user_city',
                'post_user_telephone',
                'post_user_email',
                'post_date',
                'post_text',
                'post_answer',
                'post_answer_date'
            );
            if(in_array($key,$keys)) {
                $data_array[$key] = $this->registry->main_class->format_striptags($value);
            }
        }
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['post_user_email']))) {
            $this->error = 'edit_incorrect_email';
            return;
        }
        $data_array['post_date'] = $data_array['post_date']." ".sprintf("%02d",$data_array['post_hour']).":".sprintf("%02d",$data_array['post_minute']);
        $data_array['post_date'] = $this->registry->main_class->time_to_unix($data_array['post_date']);
        if($data_array['post_answer'] !== "") {
            if($data_array['post_answer_date'] !== "no") {
                $data_array['post_answer_date'] = $data_array['post_answer_date']." ".sprintf("%02d",$data_array['post_answer_hour']).":".sprintf("%02d",$data_array['post_answer_minute']);
                $data_array['post_answer_date'] = "'".$this->registry->main_class->time_to_unix($data_array['post_answer_date'])."'";
            } else {
                $data_array['post_answer_date'] = "'".$this->registry->main_class->get_time()."'";
            }
            $data_array['post_answer'] = $this->registry->main_class->processing_data($data_array['post_answer']);
        } else {
            $data_array['post_answer_date'] = 'NULL';
            $data_array['post_answer'] = 'NULL';
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                'post_user_name',
                'post_user_country',
                'post_user_city',
                'post_user_telephone',
                'post_user_email',
                'post_text',
            );
            if(in_array($key,$keys)) {
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_guestbook SET post_user_name = ".$data_array['post_user_name'].",post_user_country = ".$data_array['post_user_country'].",post_user_city = ".$data_array['post_user_city'].",post_user_telephone = ".$data_array['post_user_telephone'].",post_show_telephone = '".$data_array['post_show_telephone']."',post_user_email = ".$data_array['post_user_email'].",post_show_email = '".$data_array['post_show_email']."',post_date = '".$data_array['post_date']."',post_text = empty_clob(),post_status = '".$data_array['post_status']."',post_answer = empty_clob(),post_answer_date = ".$data_array['post_answer_date']." WHERE post_id = '".$data_array['post_id']."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $data_array['post_text'] = substr(substr($data_array['post_text'],0,-1),1);
            $result2 = $this->db->UpdateClob(PREFIX.'_guestbook','post_text',$data_array['post_text'],'post_id = '.$data_array['post_id']);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($data_array['post_answer'] != 'NULL') {
                $data_array['post_answer'] = substr(substr($data_array['post_answer'],0,-1),1);
                $result3 = $this->db->UpdateClob(PREFIX.'_guestbook','post_answer',$data_array['post_answer'],'post_id = '.$data_array['post_id']);
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
            $sql = "UPDATE ".PREFIX."_guestbook SET post_user_name = ".$data_array['post_user_name'].",post_user_country = ".$data_array['post_user_country'].",post_user_city = ".$data_array['post_user_city'].",post_user_telephone = ".$data_array['post_user_telephone'].",post_show_telephone = '".$data_array['post_show_telephone']."',post_user_email = ".$data_array['post_user_email'].",post_show_email = '".$data_array['post_show_email']."',post_date = '".$data_array['post_date']."',post_text = ".$data_array['post_text'].",post_status = '".$data_array['post_status']."',post_answer = ".$data_array['post_answer'].",post_answer_date = ".$data_array['post_answer_date']." WHERE post_id = '".$data_array['post_id']."'";
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
            if($data_array['pend'] == 1) {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showpending");
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editpending|".$data_array['post_id']);
            }
            if(($data_array['pend'] == 1 and $data_array['post_status'] > 0) or $data_array['pend'] == 0) {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
                $this->registry->main_class->systemdk_clearcache("modules|guestbook|display");
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|editcurrent|".$data_array['post_id']);
            }
            $this->result = 'edit_ok_'.$temp;
        }
    }


    function guestbook_config() {
        $this->result = false;
        require_once('../modules/guestbook/guestbook_config.inc');
        $this->result['systemdk_guestbook_metakeywords'] = trim(SYSTEMDK_GUESTBOOK_METAKEYWORDS);
        $this->result['systemdk_guestbook_metadescription'] = trim(SYSTEMDK_GUESTBOOK_METADESCRIPTION);
        $this->result['systemdk_guestbook_storynum'] = intval(SYSTEMDK_GUESTBOOK_STORYNUM);
        $this->result['systemdk_guestbook_antispam_ipaddr'] = trim(SYSTEMDK_GUESTBOOK_ANTISPAM_IPADDR);
        $this->result['systemdk_guestbook_antispam_num'] = intval(SYSTEMDK_GUESTBOOK_ANTISPAM_NUM);
        $this->result['systemdk_guestbook_confirm_need'] = trim(SYSTEMDK_GUESTBOOK_CONFIRM_NEED);
        $this->result['systemdk_guestbook_sortorder'] = trim(SYSTEMDK_GUESTBOOK_SORTORDER);
    }


    function guestbook_config_save($array) {
        $this->result = false;
        $this->error = false;
        $keys = array(
            'systemdk_guestbook_metakeywords',
            'systemdk_guestbook_metadescription',
            'systemdk_guestbook_antispam_ipaddr',
            'systemdk_guestbook_confirm_need',
            'systemdk_guestbook_sortorder'
        );
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys2 = array(
                    'systemdk_guestbook_storynum',
                    'systemdk_guestbook_antispam_num'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if((!isset($array['systemdk_guestbook_metakeywords']) or !isset($array['systemdk_guestbook_metadescription']) or !isset($array['systemdk_guestbook_storynum']) or !isset($array['systemdk_guestbook_antispam_ipaddr']) or !isset($array['systemdk_guestbook_antispam_num']) or !isset($array['systemdk_guestbook_confirm_need']) or !isset($array['systemdk_guestbook_sortorder'])) or ($data_array['systemdk_guestbook_storynum'] == 0 or $data_array['systemdk_guestbook_antispam_ipaddr'] == "" or $data_array['systemdk_guestbook_antispam_num'] == 0 or $data_array['systemdk_guestbook_confirm_need'] == "" or $data_array['systemdk_guestbook_sortorder'] == "")) {
            $this->error = 'conf_not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            if(in_array($key,$keys)) {
                $data_array[$key] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($value);
            }
        }
        @chmod("../modules/guestbook/guestbook_config.inc",0777);
        $file = @fopen("../modules/guestbook/guestbook_config.inc","w");
        if(!$file) {
            $this->error = 'conf_no_file';
            return;
        }
        $content = "<?php\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_METAKEYWORDS","'.$data_array['systemdk_guestbook_metakeywords']."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_METADESCRIPTION","'.$data_array['systemdk_guestbook_metadescription']."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_STORYNUM",'.$data_array['systemdk_guestbook_storynum'].");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_ANTISPAM_IPADDR","'.$data_array['systemdk_guestbook_antispam_ipaddr']."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_ANTISPAM_NUM",'.$data_array['systemdk_guestbook_antispam_num'].");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_CONFIRM_NEED","'.$data_array['systemdk_guestbook_confirm_need']."\");\n";
        $content .= 'define("SYSTEMDK_GUESTBOOK_SORTORDER","'.$data_array['systemdk_guestbook_sortorder']."\");\n";
        $writefile = @fwrite($file,$content);
        @fclose($file);
        @chmod("../modules/guestbook/guestbook_config.inc",0604);
        if(!$writefile) {
            $this->error = 'conf_no_write';
            return;
        }
        $this->registry->main_class->systemdk_clearcache("modules|guestbook");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|config");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
        $this->result = 'config_done';
    }
}
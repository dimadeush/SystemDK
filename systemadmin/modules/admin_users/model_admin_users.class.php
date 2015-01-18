<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_users.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class admin_users extends model_base {


    private $model_account;
    private $error;
    private $error_array;
    private $result;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_account = singleton::getinstance('account',$registry);
    }


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index($num_page = false,$temp = false,$admin = false) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        if(!empty($temp) and $temp === 'temp') {
            $user_temp = 1;
        } else {
            $user_temp = 0;
        }
        if($admin) {
            $user_rights = "'main_admin','admin'";
        } else {
            $user_rights = "'user'";
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $where = "WHERE user_temp = '".$user_temp."' and user_rights in (".$user_rights.")";
        $sql = "SELECT count(user_id) as num_rows FROM ".PREFIX."_users ".$where;
        $result = $this->db->Execute($sql);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT user_id,user_name,user_surname,user_login,user_rights,user_email,user_active,user_reg_date FROM ".PREFIX."_users ".$where." order by user_id DESC";
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
            $prefix = 'user';
            if($admin) {
                $prefix = 'admin';
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $user_id = intval($result->fields['0']);
                    $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    $user_active = intval($result->fields['6']);
                    $user_reg_date = intval($result->fields['7']);
                    $user_reg_date = date("d.m.y H:i",$user_reg_date);
                    $user_all[] = array(
                        $prefix."_id" => $user_id,
                        $prefix."_name" => $user_name,
                        $prefix."_surname" => $user_surname,
                        $prefix."_email" => $user_email,
                        $prefix."_login" => $user_login,
                        $prefix."_rights" => $user_rights,
                        $prefix."_active" => $user_active,
                        $prefix."_regdate" => $user_reg_date
                    );
                    $result->MoveNext();
                }
                $this->result[$prefix.'_all'] = $user_all;
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
                    $this->result['total'.$prefix.'s'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result[$prefix.'_all'] = "no";
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


    public function user_add($admin = false) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if($admin and $this->registry->main_class->get_user_rights() !== "main_admin") {
            $this->error = 'add_rights';
            return;
        }
    }


    public function user_add_inbase($post_array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $prefix = 'user';
        if(!empty($post_array['admin'])) {
            $prefix = 'admin';
        }
        if(!empty($post_array)) {
            foreach($post_array as $key => $value) {
                $keys = array(
                    $prefix.'_add_name',
                    $prefix.'_add_surname',
                    $prefix.'_add_website',
                    $prefix.'_add_email',
                    $prefix.'_add_login',
                    $prefix.'_add_password',
                    $prefix.'_add_password2',
                    $prefix.'_add_country',
                    $prefix.'_add_city',
                    $prefix.'_add_address',
                    $prefix.'_add_telephone',
                    $prefix.'_add_icq',
                    $prefix.'_add_skype',
                    $prefix.'_add_gmt',
                    $prefix.'_add_notes',
                    $prefix.'_add_activenotes'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if(isset($post_array[$prefix.'_add_active'])) {
            $data_array[$prefix.'_add_active'] = intval($post_array[$prefix.'_add_active']);
        }
        if(isset($post_array[$prefix.'_add_viewemail'])) {
            $data_array[$prefix.'_add_viewemail'] = intval($post_array[$prefix.'_add_viewemail']);
        }
        if($prefix === 'admin') {
            if($this->registry->main_class->get_user_rights() !== "main_admin") {
                $this->error = 'add_rights';
                return;
            }
        }
        if((!isset($post_array[$prefix.'_add_name']) or !isset($post_array[$prefix.'_add_surname']) or !isset($post_array[$prefix.'_add_website']) or !isset($post_array[$prefix.'_add_email']) or !isset($post_array[$prefix.'_add_login']) or !isset($post_array[$prefix.'_add_password']) or !isset($post_array[$prefix.'_add_password2']) or !isset($post_array[$prefix.'_add_country']) or !isset($post_array[$prefix.'_add_city']) or !isset($post_array[$prefix.'_add_address']) or !isset($post_array[$prefix.'_add_telephone']) or !isset($post_array[$prefix.'_add_icq']) or !isset($post_array[$prefix.'_add_skype']) or !isset($post_array[$prefix.'_add_gmt']) or !isset($post_array[$prefix.'_add_notes']) or !isset($post_array[$prefix.'_add_active']) or !isset($post_array[$prefix.'_add_activenotes']) or !isset($post_array[$prefix.'_add_viewemail'])) or ($data_array[$prefix.'_add_email'] == "" or $data_array[$prefix.'_add_login'] == "" or $data_array[$prefix.'_add_password'] == "" or $data_array[$prefix.'_add_password2'] == "" or $data_array[$prefix.'_add_gmt'] == "")) {
            $this->error = 'add_not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                $prefix.'_add_name',
                $prefix.'_add_surname',
                $prefix.'_add_website',
                $prefix.'_add_country',
                $prefix.'_add_city',
                $prefix.'_add_address',
                $prefix.'_add_telephone',
                $prefix.'_add_icq',
                $prefix.'_add_skype',
                $prefix.'_add_notes',
                $prefix.'_add_activenotes'
            );
            if(in_array($key,$keys)) {
                if($value == "") {
                    $data_array[$key] = 'NULL';
                } else {
                    $value = $this->registry->main_class->format_striptags($value);
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                }
            }
        }
        $data_array[$prefix.'_add_email'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_add_email']);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array[$prefix.'_add_email']))) {
            $this->error = 'add_incorrect_email';
            return;
        }
        $data_array[$prefix.'_add_email'] = $this->registry->main_class->processing_data($data_array[$prefix.'_add_email']);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$data_array[$prefix.'_add_login'])) {
            $this->error = 'add_login';
            return;
        }
        $data_array[$prefix.'_add_login'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_add_login']);
        $data_array[$prefix.'_add_login'] = $this->registry->main_class->processing_data($data_array[$prefix.'_add_login']);
        $data_array[$prefix.'_add_gmt'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_add_gmt']);
        $data_array[$prefix.'_add_gmt'] = $this->registry->main_class->processing_data($data_array[$prefix.'_add_gmt']);
        $data_array[$prefix.'_add_password'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_add_password']);
        $data_array[$prefix.'_add_password'] = $this->registry->main_class->processing_data($data_array[$prefix.'_add_password']);
        $data_array[$prefix.'_add_password'] = substr(substr($data_array[$prefix.'_add_password'],0,-1),1);
        $data_array[$prefix.'_add_password2'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_add_password2']);
        $data_array[$prefix.'_add_password2'] = $this->registry->main_class->processing_data($data_array[$prefix.'_add_password2']);
        $data_array[$prefix.'_add_password2'] = substr(substr($data_array[$prefix.'_add_password2'],0,-1),1);
        if($data_array[$prefix.'_add_password'] !== $data_array[$prefix.'_add_password2']) {
            $this->error = 'add_pass';
            return;
        } else {
            $sql0 = "SELECT count(*) as count FROM ".PREFIX."_users WHERE user_email = ".$data_array[$prefix.'_add_email']." or user_login = ".$data_array[$prefix.'_add_login'];
            $insert_result = $this->db->Execute($sql0);
            if($insert_result) {
                if(isset($insert_result->fields['0'])) {
                    $num_rows = intval($insert_result->fields['0']);
                } else {
                    $num_rows = 0;
                }
                if($num_rows == 0) {
                    $data_array[$prefix.'_add_password'] = crypt($data_array[$prefix.'_add_password'],$this->registry->main_class->salt());
                    $data_array[$prefix.'_add_rights'] = 'user';
                    if($prefix === 'admin') {
                        $data_array[$prefix.'_add_rights'] = 'admin';
                    }
                    $now = $this->registry->main_class->get_time();
                    $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_users_id','user_id');
                    $sql = "INSERT INTO ".PREFIX."_users (".$sequence_array['field_name_string']."user_temp,user_name,user_surname,user_website,user_email,user_login,user_password,user_session_code,user_session_agent,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_skype,user_gmt,user_notes,user_active,user_active_notes,user_reg_date,user_last_login_date,user_view_email,user_activate_key,user_new_password,user_reg_key)  VALUES (".$sequence_array['sequence_value_string']."'0',".$data_array[$prefix.'_add_name'].",".$data_array[$prefix.'_add_surname'].",".$data_array[$prefix.'_add_website'].",".$data_array[$prefix.'_add_email'].",".$data_array[$prefix.'_add_login'].",'".$data_array[$prefix.'_add_password']."',null,null,'".$data_array[$prefix.'_add_rights']."',".$data_array[$prefix.'_add_country'].",".$data_array[$prefix.'_add_city'].",".$data_array[$prefix.'_add_address'].",".$data_array[$prefix.'_add_telephone'].",".$data_array[$prefix.'_add_icq'].",".$data_array[$prefix.'_add_skype'].",".$data_array[$prefix.'_add_gmt'].",".$data_array[$prefix.'_add_notes'].",'".$data_array[$prefix.'_add_active']."',".$data_array[$prefix.'_add_activenotes'].",'".$now."',null,'".$data_array[$prefix.'_add_viewemail']."',null,null,null)";
                    $insert_result = $this->db->Execute($sql);
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    $this->error = 'add_found';
                    return;
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->error = 'add_sql_error';
            $this->error_array = $error;
            return;
        } else {
            if($prefix === 'admin') {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|show");
            } else {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
            }
            $this->result = 'add_ok';
        }
    }


    public function user_status($array) {
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
        if(isset($array['admin'])) {
            if($this->registry->main_class->get_user_rights() !== "main_admin") {
                $this->error = 'status_rights';
                return;
            }
        }
        if(isset($array['get_user_id'])) {
            $user_id = intval($array['get_user_id']);
        } else {
            $user_id = 0;
        }
        if($user_id == 0 and isset($array['post_user_id']) and is_array($array['post_user_id']) and count($array['post_user_id']) > 0) {
            $user_id_is_arr = 1;
            for($i = 0,$size = count($array['post_user_id']);$i < $size;++$i) {
                if($i == 0) {
                    $user_id = "'".intval($array['post_user_id'][$i])."'";
                } else {
                    $user_id .= ",'".intval($array['post_user_id'][$i])."'";
                }
            }
        } else {
            $user_id_is_arr = 0;
            $user_id = "'".$user_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($user_id) or $action == "" or ($user_id == "'0'" and $action != "deletetempall")) {
            $this->error = 'empty_data';
            return;
        }
        $where = "and user_rights in ('user')";
        if(isset($array['admin'])) {
            $where = "and user_rights in ('admin')"; // and user_rights <> 'main_admin'
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_users SET user_active='1' WHERE user_id IN (".$user_id.") and user_temp = '0' ".$where;
            $result = $this->db->Execute($sql);
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_users SET user_active='0' WHERE user_id IN (".$user_id.") and user_temp = '0' ".$where;
            $result = $this->db->Execute($sql);
        } elseif($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_users WHERE user_id IN (".$user_id.") and user_temp = '0' ".$where;
            $result = $this->db->Execute($sql);
        } elseif($action == "deletetemp" and !isset($array['admin'])) {
            $sql = "DELETE FROM ".PREFIX."_users WHERE user_id IN (".$user_id.") and user_temp = '1' ".$where;
            $result = $this->db->Execute($sql);
        } elseif($action == "deletetempall" and !isset($array['admin'])) {
            $sql = "DELETE FROM ".PREFIX."_users WHERE user_temp = '1' ".$where;
            $result = $this->db->Execute($sql);
        } else {
            $this->error = 'empty_data';
            return;
        }
        $this->result = $action;
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
            if($action == "deletetemp" or $action == "deletetempall") {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
            } else {
                $prefix = 'users';
                if(isset($array['admin'])) {
                    $prefix = 'administrators';
                }
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|".$prefix."|show");
                if($user_id_is_arr == 1) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|".$prefix."|edit");
                    if(isset($array['admin'])) {
                        $this->registry->main_class->systemdk_clearcache("systemadmin|edit");
                    }
                    $this->registry->main_class->systemdk_clearcache("modules|account|show");
                    $this->registry->main_class->systemdk_clearcache("modules|account|edit");
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|".$prefix."|edit|".intval($array['get_user_id']));
                    if(isset($array['admin'])) {
                        $this->registry->main_class->systemdk_clearcache("systemadmin|edit|".intval($array['get_user_id']));
                    }
                    $this->registry->main_class->systemdk_clearcache("modules|account|show|".intval($array['get_user_id']));
                    $this->registry->main_class->systemdk_clearcache("modules|account|edit|".intval($array['get_user_id']));
                }
                $this->registry->main_class->systemdk_clearcache("homepage");
            }
        }
    }


    public function user_edit($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['user_id'])) {
            $user_id = intval($array['user_id']);
        }
        if(isset($array['admin'])) {
            if($this->registry->main_class->get_user_rights() !== "main_admin" and isset($array['user_id']) and intval($this->registry->main_class->get_user_id()) != intval($array['user_id'])) {
                $this->error = 'edit_rights';
                return;
            }
        }
        if(!isset($array['user_id']) or $user_id == 0) {
            $this->error = 'empty_data';
            return;
        }
        $only_active = false;
        $result = $this->registry->main_class->get_user_info($user_id,$only_active);
        if($result) {
            if(isset($result['0']) and isset($result['6']) and ((!isset($array['admin']) and $this->registry->main_class->extracting_data($result['6']) === 'user') or (isset($array['admin']) and ($this->registry->main_class->extracting_data($result['6']) === 'admin' or $this->registry->main_class->extracting_data($result['6']) === 'main_admin')))) {
                $row_exist = intval($result['0']);
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
            $this->error = 'not_found';
            return;
        } else {
            $prefix = 'user';
            if(isset($array['admin'])) {
                $prefix = 'admin';
            }
            $user_id = intval($result['0']);
            $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['1']));
            $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['2']));
            $user_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['3']));
            $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['4']));
            $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['5']));
            $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['6']));
            $user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['7']));
            $user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['8']));
            $user_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['9']));
            $user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['10']));
            $user_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['11']));
            $user_skype = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['12']));
            $user_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['13']));
            $user_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['14']));
            $user_active = intval($result['15']);
            $user_activenotes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['16']));
            $user_regdate = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['17']));
            $user_viewemail = intval($result['19']);
            $user_all[] = array(
                $prefix."_id" => $user_id,
                $prefix."_name" => $user_name,
                $prefix."_surname" => $user_surname,
                $prefix."_website" => $user_website,
                $prefix."_email" => $user_email,
                $prefix."_login" => $user_login,
                $prefix."_rights" => $user_rights,
                $prefix."_country" => $user_country,
                $prefix."_city" => $user_city,
                $prefix."_address" => $user_address,
                $prefix."_telephone" => $user_telephone,
                $prefix."_icq" => $user_icq,
                $prefix."_skype" => $user_skype,
                $prefix."_gmt" => $user_gmt,
                $prefix."_notes" => $user_notes,
                $prefix."_active" => $user_active,
                $prefix."_activenotes" => $user_activenotes,
                $prefix."_regdate" => $user_regdate,
                $prefix."_viewemail" => $user_viewemail
            );
            $this->result[$prefix.'_all'] = $user_all;
        }
    }


    public function user_edit_inbase($post_array) {
        $data_array = false;
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $prefix = 'user';
        if(isset($post_array['admin'])) {
            $prefix = 'admin';
        }
        if(isset($post_array[$prefix.'_edit_id'])) {
            $data_array[$prefix.'_edit_id'] = intval($post_array[$prefix.'_edit_id']);
        }
        if(!empty($post_array)) {
            foreach($post_array as $key => $value) {
                $keys = array(
                    $prefix.'_edit_name',
                    $prefix.'_edit_surname',
                    $prefix.'_edit_website',
                    $prefix.'_edit_email',
                    $prefix.'_edit_login',
                    $prefix.'_edit_newpassword',
                    $prefix.'_edit_newpassword2',
                    $prefix.'_edit_country',
                    $prefix.'_edit_city',
                    $prefix.'_edit_address',
                    $prefix.'_edit_telephone',
                    $prefix.'_edit_icq',
                    $prefix.'_edit_skype',
                    $prefix.'_edit_gmt',
                    $prefix.'_edit_notes',
                    $prefix.'_edit_activenotes'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if(isset($post_array[$prefix.'_edit_active'])) {
            $data_array[$prefix.'_edit_active'] = intval($post_array[$prefix.'_edit_active']);
        }
        if(isset($post_array[$prefix.'_edit_viewemail'])) {
            $data_array[$prefix.'_edit_viewemail'] = intval($post_array[$prefix.'_edit_viewemail']);
        }
        if(isset($post_array['admin'])) {
            if($this->registry->main_class->get_user_rights() !== "main_admin" and isset($post_array[$prefix.'_edit_id']) and intval($this->registry->main_class->get_user_id()) != intval($post_array[$prefix.'_edit_id'])) {
                $this->error = 'edit_rights';
                return;
            }
        }
        if((!isset($post_array[$prefix.'_edit_id']) or !isset($post_array[$prefix.'_edit_name']) or !isset($post_array[$prefix.'_edit_surname'])or !isset($post_array[$prefix.'_edit_website']) or !isset($post_array[$prefix.'_edit_email']) or !isset($post_array[$prefix.'_edit_login']) or !isset($post_array[$prefix.'_edit_newpassword']) or !isset($post_array[$prefix.'_edit_newpassword2']) or !isset($post_array[$prefix.'_edit_country']) or !isset($post_array[$prefix.'_edit_city']) or !isset($post_array[$prefix.'_edit_address']) or !isset($post_array[$prefix.'_edit_telephone']) or !isset($post_array[$prefix.'_edit_icq']) or !isset($post_array[$prefix.'_edit_skype']) or !isset($post_array[$prefix.'_edit_gmt']) or !isset($post_array[$prefix.'_edit_notes']) or !isset($post_array[$prefix.'_edit_active']) or !isset($post_array[$prefix.'_edit_activenotes']) or !isset($post_array[$prefix.'_edit_viewemail'])) or ($data_array[$prefix.'_edit_id'] == 0 or $data_array[$prefix.'_edit_email'] == "" or $data_array[$prefix.'_edit_login'] == "" or $data_array[$prefix.'_edit_gmt'] == "")) {
            $this->error = 'edit_not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                $prefix.'_edit_name',
                $prefix.'_edit_surname',
                $prefix.'_edit_website',
                $prefix.'_edit_country',
                $prefix.'_edit_city',
                $prefix.'_edit_address',
                $prefix.'_edit_telephone',
                $prefix.'_edit_icq',
                $prefix.'_edit_skype',
                $prefix.'_edit_notes',
                $prefix.'_edit_activenotes'
            );
            if(in_array($key,$keys)) {
                if($value == "") {
                    $data_array[$key] = 'NULL';
                } else {
                    $value = $this->registry->main_class->format_striptags($value);
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                }
            }
        }
        if(isset($post_array['admin']) and $this->registry->main_class->get_user_id() == $data_array[$prefix.'_edit_id'] and $data_array[$prefix.'_edit_active'] == 0) {
            $data_array[$prefix.'_edit_active'] = 1;
        }
        $data_array[$prefix.'_edit_email'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_edit_email']);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array[$prefix.'_edit_email']))) {
            $this->error = 'edit_incorrect_email';
            return;
        }
        $data_array[$prefix.'_edit_email'] = $this->registry->main_class->processing_data($data_array[$prefix.'_edit_email']);
        if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",$data_array[$prefix.'_edit_login'])) {
            $this->error = 'edit_login';
            return;
        }
        $data_array[$prefix.'_edit_login'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_edit_login']);
        $data_array[$prefix.'_edit_login'] = $this->registry->main_class->processing_data($data_array[$prefix.'_edit_login']);
        $data_array[$prefix.'_edit_gmt'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_edit_gmt']);
        $data_array[$prefix.'_edit_gmt'] = $this->registry->main_class->processing_data($data_array[$prefix.'_edit_gmt']);
        $sql_add = false;
        if(isset($data_array[$prefix.'_edit_newpassword']) and $data_array[$prefix.'_edit_newpassword'] != "") {
            $data_array[$prefix.'_edit_newpassword'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_edit_newpassword']);
            $data_array[$prefix.'_edit_newpassword'] = $this->registry->main_class->processing_data($data_array[$prefix.'_edit_newpassword']);
            $data_array[$prefix.'_edit_newpassword'] = substr(substr($data_array[$prefix.'_edit_newpassword'],0,-1),1);
            $data_array[$prefix.'_edit_newpassword2'] = $this->registry->main_class->format_striptags($data_array[$prefix.'_edit_newpassword2']);
            $data_array[$prefix.'_edit_newpassword2'] = $this->registry->main_class->processing_data($data_array[$prefix.'_edit_newpassword2']);
            $data_array[$prefix.'_edit_newpassword2'] = substr(substr($data_array[$prefix.'_edit_newpassword2'],0,-1),1);
            if($data_array[$prefix.'_edit_newpassword'] !== $data_array[$prefix.'_edit_newpassword2']) {
                $this->error = 'edit_pass';
                return;
            } else {
                $sql_add = "user_password = '".crypt($data_array[$prefix.'_edit_newpassword'],$this->registry->main_class->salt())."',user_session_code = null,user_session_agent = null,";
            }
        }
        $sql0 = "SELECT a.user_login, (SELECT max(b.user_id) FROM ".PREFIX."_users b WHERE b.user_id <> '".$data_array[$prefix.'_edit_id']."' and (b.user_email=".$data_array[$prefix.'_edit_email']." or b.user_login=".$data_array[$prefix.'_edit_login'].")) as num_rows FROM ".PREFIX."_users a WHERE a.user_id = '".$data_array[$prefix.'_edit_id']."'";
        $insert_result = $this->db->Execute($sql0);
        if($insert_result) {
            if(isset($insert_result->fields['1'])) {
                $row_exist = intval($insert_result->fields['1']);
            } else {
                $row_exist = 0;
            }
            $data_array[$prefix.'_old_login'] = trim($insert_result->fields['0']);
            if($row_exist > 0) {
                $this->error = 'edit_found';
                return;
            }
            if($data_array[$prefix.'_old_login'] !== substr(substr($data_array[$prefix.'_edit_login'],0,-1),1) and empty($sql_add)) {
                $sql_add = "user_session_code = null,user_session_agent = null,";
            }
            $where = "user_rights in ('user')";
            if(isset($post_array['admin'])) {
                $this->db->StartTrans();
                $where = "user_rights in ('admin','main_admin')";
            }
            $sql = "UPDATE ".PREFIX."_users SET user_name = ".$data_array[$prefix.'_edit_name'].",user_surname = ".$data_array[$prefix.'_edit_surname'].",user_website = ".$data_array[$prefix.'_edit_website'].",user_email = ".$data_array[$prefix.'_edit_email'].",user_login = ".$data_array[$prefix.'_edit_login'].",".$sql_add."user_country = ".$data_array[$prefix.'_edit_country'].",user_city = ".$data_array[$prefix.'_edit_city'].",user_address = ".$data_array[$prefix.'_edit_address'].",user_telephone = ".$data_array[$prefix.'_edit_telephone'].",user_icq = ".$data_array[$prefix.'_edit_icq'].",user_skype = ".$data_array[$prefix.'_edit_skype'].",user_gmt = ".$data_array[$prefix.'_edit_gmt'].",user_notes = ".$data_array[$prefix.'_edit_notes'].",user_active = '".$data_array[$prefix.'_edit_active']."',user_active_notes = ".$data_array[$prefix.'_edit_activenotes'].",user_view_email = '".$data_array[$prefix.'_edit_viewemail']."' WHERE user_id = '".$data_array[$prefix.'_edit_id']."' and user_temp = '0' and ".$where;
            $insert_result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            } elseif(isset($post_array['admin'])) {
                $data_array[$prefix.'_edit_login'] = substr(substr($data_array[$prefix.'_edit_login'],0,-1),1);
                if($num_result > 0 and $data_array[$prefix.'_old_login'] !== $data_array[$prefix.'_edit_login']) {
                    $clear_langlist = $this->registry->controller_theme->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
                    for($i = 0,$size = sizeof($clear_langlist);$i < $size;++$i) {
                        $sql = "UPDATE ".PREFIX."_main_pages_".$clear_langlist[$i]['name']." SET main_page_author = '".$data_array[$prefix.'_edit_login']."' WHERE main_page_author = '".$data_array[$prefix.'_old_login']."'";
                        $result1 = $this->db->Execute($sql);
                        $num_result1 = $this->db->Affected_Rows();
                        if($result1 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } elseif($num_result1 > 0) {
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|main_pages|show");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|main_pages|edit");
                        }
                        $sql = "UPDATE ".PREFIX."_messages_".$clear_langlist[$i]['name']." SET message_author = '".$data_array[$prefix.'_edit_login']."' WHERE message_author = '".$data_array[$prefix.'_old_login']."'";
                        $result2 = $this->db->Execute($sql);
                        $num_result2 = $this->db->Affected_Rows();
                        if($result2 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } elseif($num_result2 > 0) {
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|messages|show");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|messages|edit");
                        }
                        $sql = "UPDATE ".PREFIX."_news_".$clear_langlist[$i]['name']." SET news_author = '".$data_array[$prefix.'_edit_login']."' WHERE news_author = '".$data_array[$prefix.'_old_login']."'";
                        $result3 = $this->db->Execute($sql);
                        $num_result3 = $this->db->Affected_Rows();
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } elseif($num_result3 > 0) {
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|showcurrent");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|edit");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|modules|news|show|admin");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|modules|news|newsread");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|showprogramm");
                            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|systemadmin|modules|news|editprogramm");
                        }
                        if($result1 === false or $result2 === false or $result3 === false) {
                            $insert_result = false;
                        }
                    }
                }
                if($data_array[$prefix.'_edit_id'] == $this->registry->main_class->get_user_id() and $insert_result !== false and !empty($sql_add)) {
                    $this->registry->main_class->logout();
                }
            }
            if(isset($post_array['admin'])) {
                $this->db->CompleteTrans();
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($insert_result === false) {
            $this->error = 'edit_sql_error';
            $this->error_array = $error;
            return;
        } elseif($insert_result !== false and $num_result == 0) {
            $this->error = 'edit_ok';
            return;
        } else {
            $module = 'users';
            if(isset($post_array['admin'])) {
                $module = 'administrators';
            }
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|".$module."|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|".$module."|edit|".$data_array[$prefix.'_edit_id']);
            $this->registry->main_class->systemdk_clearcache("modules|account|show|".$data_array[$prefix.'_edit_id']);
            $this->registry->main_class->systemdk_clearcache("modules|account|edit|".$data_array[$prefix.'_edit_id']);
            if(isset($post_array['admin'])) {
                $this->registry->main_class->systemdk_clearcache("systemadmin|edit|".$data_array[$prefix.'_edit_id']);
            }
            $this->registry->main_class->systemdk_clearcache("homepage");
            $this->result = 'edit_done';
        }
    }
}
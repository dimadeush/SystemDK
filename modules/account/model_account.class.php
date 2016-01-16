<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class account extends model_base
{


    private $error;
    private $error_array;
    private $result;
    private $user_add_array;
    private $user_data_array;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->process_autodelete();
    }


    public function process_autodelete()
    {
        if (defined('SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS') && intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS) != 0) {
            $systemdk_account_maxactivationdays = $this->registry->main_class->get_time() - (intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS) * 86400);
            $result = $this->db->Execute("DELETE FROM " . PREFIX . "_users WHERE user_temp = '1' and user_reg_date < " . $systemdk_account_maxactivationdays);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
                if ($num_result > 0) {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
                }
            }
        }
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function get_user_info($user_id, $only_active)
    {
        if (intval($user_id) > 0) {
            $systemdk_user_info = 'systemdk_user_info_' . $user_id;
            if (isset($this->registry->$systemdk_user_info)) {
                return $this->registry->$systemdk_user_info;
            }
            $sql_add = false;
            if ($only_active) {
                $sql_add = "and user_active = '1'";
            }
            $sql =
                "SELECT user_id,user_name,user_surname,user_website,user_email,user_login,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_skype,user_gmt,user_notes,user_active,user_active_notes,user_reg_date,user_last_login_date,user_view_email FROM "
                . PREFIX . "_users WHERE user_id = '" . $user_id . "' and user_temp = '0' " . $sql_add;
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
            }
            if (isset($row_exist) && $row_exist > 0) {
                $this->registry->$systemdk_user_info = $result->fields;

                return $result->fields;
            } elseif (isset($row_exist)) {
                return ['0' => 0];
            }
        }

        return false;
    }


    // TODO refactor and made one constant for admin roles and one constant for user roles
    public function check_admin_rights($role)
    {
        if (in_array($role, ['admin', 'main_admin'])) {
            return true;
        }

        return false;
    }


    public function captcha()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (extension_loaded("gd") && (ENTER_CHECK == "yes")) {
            $this->result['enter_check'] = 'yes';
        } else {
            $this->result['enter_check'] = 'no';
        }
    }


    public function account_register($account_register_check = true, $register_rules_accept = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if ($account_register_check) {
            $this->account_register_check();

            return;
        }
        if (defined('SYSTEMDK_ACCOUNT_RULES') && SYSTEMDK_ACCOUNT_RULES == "yes" && !$register_rules_accept) {
            $sql = "SELECT system_page_id, system_page_title, system_page_content FROM " . PREFIX
                   . "_system_pages WHERE system_page_name = 'account_rules' AND system_page_lang = '" . $this->registry->language . "'";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
            }
            if (!isset($row_exist) || $row_exist == 0) {
                $this->error = 'account_rules_not_found';

                return;
            }
            $systempage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $systempage_content = $this->registry->main_class->extracting_data($result->fields['2']);
            $systempage_all[] = [
                "systempage_title"   => $systempage_title,
                "systempage_content" => $systempage_content,
            ];
            $this->result['systempage_all'] = $systempage_all;

            return;
        }
    }


    private function account_register_check()
    {
        if (!defined('SYSTEMDK_ACCOUNT_REGISTRATION') || SYSTEMDK_ACCOUNT_REGISTRATION != 'yes') {
            $this->error = 'register_not_allow';

            return false;
        }
        if (defined('SYSTEMDK_ACCOUNT_MAXUSERS') && SYSTEMDK_ACCOUNT_MAXUSERS > 0) {
            $sql = "SELECT count(*) as count FROM " . PREFIX . "_users";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $num_rows = intval($result->fields['0']);
                } else {
                    $num_rows = 0;
                }
                if ($num_rows >= SYSTEMDK_ACCOUNT_MAXUSERS && $num_rows > 0) {
                    $this->error = 'max_users_limit';

                    return false;
                }
            }
        }

        return true;
    }


    public function account_add_save($post_array, $mail_data = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (empty($data_array) && !empty($mail_data)) {
            if (is_array($mail_data)) {
                $mail_data['content'] = str_replace(
                    "modules_account_reguserlogin", $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data(substr(substr($this->user_add_array['user_add_login'], 0, -1), 1))
                ), $mail_data['content']
                );
                $mail_data['content'] = str_replace(
                    "modules_account_reguserpassword",
                    $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($this->user_add_array['user_add_password'])),
                    $mail_data['content']
                );
                $mail_data['content'] = str_replace("modules_account_regactid", $this->user_add_array['systemdk_act_id'], $mail_data['content']);
                $this->registry->mail->sendmail(
                    $this->registry->main_class->format_htmlspecchars(
                        $this->registry->main_class->extracting_data(substr(substr($this->user_add_array['user_add_email'], 0, -1), 1))
                    ), $mail_data['title'], $mail_data['content']
                );
                if ($this->registry->mail->send_error) {
                    $error[] = ["code" => "", "message" => $this->registry->mail->smtp_msg];
                    $this->db->FailTrans();
                    $this->error_array = $error;
                    $this->error = 'sql_error';
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
                }
            }
            $this->db->CompleteTrans();

            return;
        }
        if (!empty($post_array)) {
            $keys = [
                'user_add_name',
                'user_add_surname',
                'user_add_website',
                'user_add_email',
                'user_add_login',
                'user_add_password',
                'user_add_password2',
                'user_add_country',
                'user_add_city',
                'user_add_address',
                'user_add_telephone',
                'user_add_icq',
                'user_add_skype',
                'user_add_gmt',
                'user_add_notes',
            ];
            foreach ($post_array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if (isset($post_array['user_add_viewemail'])) {
            $data_array['user_add_viewemail'] = intval($post_array['user_add_viewemail']);
        }
        if ((!isset($post_array['user_add_name']) || !isset($post_array['user_add_surname']) || !isset($post_array['user_add_website'])
             || !isset($post_array['user_add_email'])
             || !isset($post_array['user_add_login'])
             || !isset($post_array['user_add_password'])
             || !isset($post_array['user_add_password2'])
             || !isset($post_array['user_add_country'])
             || !isset($post_array['user_add_city'])
             || !isset($post_array['user_add_address'])
             || !isset($post_array['user_add_telephone'])
             || !isset($post_array['user_add_icq'])
             || !isset($post_array['user_add_skype'])
             || !isset($post_array['user_add_gmt'])
             || !isset($post_array['user_add_notes'])
             || !isset($post_array['user_add_viewemail'])
             || (!isset($post_array['captcha']) && extension_loaded("gd") && ENTER_CHECK == "yes"))
            || ($data_array['user_add_email'] == "" || $data_array['user_add_login'] == "" || $data_array['user_add_password'] == ""
                || $data_array['user_add_password2'] == ""
                || $data_array['user_add_gmt'] == ""
                || (extension_loaded("gd") && ENTER_CHECK == "yes"
                    && $this->registry->main_class->format_striptags(
                        $post_array['captcha']
                    ) == ""))
        ) {
            $this->error = 'reg_not_all_data';

            return;
        }
        if (ENTER_CHECK == "yes") {
            if (extension_loaded("gd")
                && (!isset($post_array["captcha"])
                    || (isset($_SESSION["captcha"]) && $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($post_array["captcha"]))
                    || !isset($_SESSION["captcha"]))
            ) {
                if (isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                $this->error = 'reg_check_code';

                return;
            }
            if (isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        $result = $this->account_register_check();
        if (!$result) {
            return;
        }
        $keys = [
            'user_add_name',
            'user_add_surname',
            'user_add_website',
            'user_add_country',
            'user_add_city',
            'user_add_address',
            'user_add_telephone',
            'user_add_icq',
            'user_add_skype',
            'user_add_notes',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                if ($value == "") {
                    $data_array[$key] = 'NULL';
                } else {
                    $value = $this->registry->main_class->format_striptags($value);
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                }
            }
        }
        $data_array['user_add_email'] = $this->registry->main_class->format_striptags($data_array['user_add_email']);
        if (!$this->registry->main_class->check_email($data_array['user_add_email'])) {
            $this->error = 'reg_save_user_email';

            return;
        }
        $data_array['user_add_email'] = $this->registry->main_class->processing_data($data_array['user_add_email']);
        if (preg_match("/[^a-zA-Z�-��-�0-9_-]/", $data_array['user_add_login'])) {
            $this->error = 'reg_login';

            return;
        }
        $keys = [
            'user_add_login',
            'user_add_gmt',
            'user_add_password',
            'user_add_password2',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                $data_array[$key] = $this->registry->main_class->processing_data($value);
            }
        }
        $data_array['user_add_password'] = substr(substr($data_array['user_add_password'], 0, -1), 1);
        $data_array['user_add_password2'] = substr(substr($data_array['user_add_password2'], 0, -1), 1);
        if ($data_array['user_add_password'] !== $data_array['user_add_password2']) {
            $this->error = 'reg_pass';

            return;
        }
        $sql1 = "SELECT count(*) as count FROM " . PREFIX . "_users WHERE user_email = " . $data_array['user_add_email'] . " or user_login = "
                . $data_array['user_add_login'];
        $insert_result = $this->db->Execute($sql1);
        if ($insert_result) {
            if (isset($insert_result->fields['0'])) {
                $num_rows = intval($insert_result->fields['0']);
            } else {
                $num_rows = 0;
            }
            if ($num_rows > 0) {
                $this->error = 'reg_found_user';

                return;
            }
            if (defined('SYSTEMDK_ACCOUNT_ACTIVATION') && SYSTEMDK_ACCOUNT_ACTIVATION == 'no') {
                $data_array['user_add_password'] = crypt($data_array['user_add_password'], $this->registry->main_class->salt());
                $now = $this->registry->main_class->get_time();
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . '_users_id', 'user_id');
                $sql = "INSERT INTO " . PREFIX . "_users (" . $sequence_array['field_name_string']
                       . "user_temp,user_name,user_surname,user_website,user_email,user_login,user_password,user_session_code,user_session_agent,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_skype,user_gmt,user_notes,user_active,user_active_notes,user_reg_date,user_last_login_date,user_view_email,user_activate_key,user_new_password,user_reg_key)  VALUES ("
                       . $sequence_array['sequence_value_string'] . "'0'," . $data_array['user_add_name'] . "," . $data_array['user_add_surname'] . ","
                       . $data_array['user_add_website'] . "," . $data_array['user_add_email'] . "," . $data_array['user_add_login'] . ",'"
                       . $data_array['user_add_password'] . "',NULL,NULL,'user'," . $data_array['user_add_country'] . "," . $data_array['user_add_city'] . ","
                       . $data_array['user_add_address'] . "," . $data_array['user_add_telephone'] . "," . $data_array['user_add_icq'] . ","
                       . $data_array['user_add_skype']
                       . "," . $data_array['user_add_gmt'] . "," . $data_array['user_add_notes'] . ",'1',NULL,'" . $now . "',NULL,'" . $data_array['user_add_viewemail']
                       . "',NULL,NULL,NULL)";
                $insert_result = $this->db->Execute($sql);
                if ($insert_result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                } else {
                    $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
                }
            } elseif (defined('SYSTEMDK_ACCOUNT_ACTIVATION') && SYSTEMDK_ACCOUNT_ACTIVATION == 'yes') {
                $data_array['user_add_password'] = crypt($data_array['user_add_password'], $this->registry->main_class->salt());
                $now = $this->registry->main_class->get_time();
                mt_srand((double)microtime() * 1000000);
                $maxran = 1000000;
                $check_num = mt_rand(0, $maxran);
                $systemdk_act_id = md5($check_num);
                $this->db->StartTrans();
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . '_users_id', 'user_id');
                $sql = "INSERT INTO " . PREFIX . "_users (" . $sequence_array['field_name_string']
                       . "user_temp,user_name,user_surname,user_website,user_email,user_login,user_password,user_session_code,user_session_agent,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_skype,user_gmt,user_notes,user_active,user_active_notes,user_reg_date,user_last_login_date,user_view_email,user_activate_key,user_new_password,user_reg_key)  VALUES ("
                       . $sequence_array['sequence_value_string'] . "'1'," . $data_array['user_add_name'] . "," . $data_array['user_add_surname'] . ","
                       . $data_array['user_add_website'] . "," . $data_array['user_add_email'] . "," . $data_array['user_add_login'] . ",'"
                       . $data_array['user_add_password'] . "',NULL,NULL,'user'," . $data_array['user_add_country'] . "," . $data_array['user_add_city'] . ","
                       . $data_array['user_add_address'] . "," . $data_array['user_add_telephone'] . "," . $data_array['user_add_icq'] . ","
                       . $data_array['user_add_skype']
                       . "," . $data_array['user_add_gmt'] . "," . $data_array['user_add_notes'] . ",'1',NULL,'" . $now . "',NULL,'" . $data_array['user_add_viewemail']
                       . "',NULL,NULL,'" . $systemdk_act_id . "')";
                $insert_result = $this->db->Execute($sql);
                $this->result['insert_result'] = $insert_result;
                if ($insert_result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                } else {
                    $this->result['modules_account_siteurl'] = SITE_URL;
                    $this->user_add_array = [
                        'user_add_name'     => $data_array['user_add_name'],
                        'user_add_login'    => $data_array['user_add_login'],
                        'user_add_password' => $data_array['user_add_password2'],
                        'systemdk_act_id'   => $systemdk_act_id,
                        'user_add_email'    => $data_array['user_add_email'],
                    ];
                    if ($data_array['user_add_name'] != 'NULL') {
                        $this->result['modules_account_regusername'] = $this->registry->main_class->format_htmlspecchars(
                            $this->registry->main_class->extracting_data(substr(substr($data_array['user_add_name'], 0, -1), 1))
                        );
                    } else {
                        $this->result['modules_account_regusername'] = $data_array['user_add_name'];
                    }
                }
                $this->result['need_send_email'] = 'yes';
            } else {
                $this->error = 'register_not_allow';

                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($insert_result === false) {
            $this->error_array = $error;
            $this->error = 'sql_error';

            return;
        }
        if (defined('SYSTEMDK_ACCOUNT_ACTIVATION') && SYSTEMDK_ACCOUNT_ACTIVATION == 'yes') {
            if (defined('SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS') && intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS) != 0) {
                $this->result['modules_account_useractdayslim'] = intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS);
                $this->result['account_message'] = 'add_act_lim_ok';
            } else {
                $this->result['modules_account_useractdayslim'] = "no";
                $this->result['account_message'] = 'add_act_ok';
            }
        } else {
            $this->result['account_message'] = 'add_ok';
        }
    }


    public function account_add_confirm($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['modules_account_confcode'])) {
            $array['act_code'] = $this->registry->main_class->format_striptags(trim($array['modules_account_confcode']));
            $modules_account_conf_code = 1;
        }
        if (isset($array['act_code'])) {
            $act_code = $this->registry->main_class->format_striptags(trim($array['act_code']));
        }
        if (isset($modules_account_conf_code) && $modules_account_conf_code == 1) {
            if (ENTER_CHECK == "yes") {
                if (extension_loaded("gd")
                    && (!isset($array["captcha"])
                        || (isset($_SESSION["captcha"]) && $_SESSION["captcha"] !== $this->registry->main_class->format_striptags(trim($array["captcha"])))
                        || !isset($_SESSION["captcha"]))
                ) {
                    if (isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    $this->error = 'conf_reg_check_code';

                    return;
                }
                if (isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
        }
        if (!isset($array['act_code']) || $act_code == "") {
            $this->error = 'no_act_code';

            return;
        }
        $sql = "SELECT user_id FROM " . PREFIX . "_users WHERE user_temp = '1' and user_reg_key = " . $this->registry->main_class->processing_data($act_code);
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        if (!isset($row_exist) || $row_exist < 1) {
            $this->error = 'no_act_code';

            return;
        } elseif (isset($modules_account_conf_code) && $modules_account_conf_code == 1) {
            $sql = "UPDATE " . PREFIX . "_users SET user_temp = '0',user_reg_key = NULL WHERE user_reg_key = " . $this->registry->main_class->processing_data(
                    $act_code
                );
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            } else {
                $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|showtemp");
            }
            if ($result === false) {
                $this->error = 'add_conf_sql_error';
                $this->error_array = $error;

                return;
            }
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
            $this->result['account_message'] = 'add_ok';

            return;
        } else {
            if (extension_loaded("gd") && (ENTER_CHECK == "yes")) {
                $this->result['enter_check'] = 'yes';
            } else {
                $this->result['enter_check'] = 'no';
            }
            $this->result['modules_account_confcode'] = $act_code;
            $this->result['account_message'] = 'form_add_conf';

            return;
        }
    }


    public function account_lostpassconf($post_array, $mail_data = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (empty($post_array) && !empty($mail_data)) {
            if (is_array($mail_data)) {
                $mail_data['content'] = str_replace("modules_account_reguserlogin", $this->user_data_array['user_login'], $mail_data['content']);
                $mail_data['content'] = str_replace("modules_account_reguserpassword", $this->user_data_array['user_new_password'], $mail_data['content']);
                $mail_data['content'] = str_replace("modules_account_regactid", $this->user_data_array['systemdk_act_id'], $mail_data['content']);
                $this->registry->mail->sendmail($this->user_data_array['user_email'], $mail_data['title'], $mail_data['content']);
                if ($this->registry->mail->send_error) {
                    $error_message = $this->registry->mail->smtp_msg;
                    $error_code = "";
                    $error[] = ["code" => $error_code, "message" => $error_message];
                    $this->db->FailTrans();
                    $this->error_array = $error;
                    $this->error = 'lost_pass_sql_error';
                } else {
                    $this->result['account_message'] = 'send_pass_ok';
                }
            } else {
                $this->db->FailTrans();
                $this->error = 'lost_pass_sql_error';
            }
            $this->db->CompleteTrans();

            return;
        }
        if (isset($post_array['lost_loginoremail'])) {
            $lost_login_or_email = trim($post_array['lost_loginoremail']);
        }
        if (!isset($post_array['lost_loginoremail']) || (!isset($post_array['captcha']) && extension_loaded("gd") && ENTER_CHECK == "yes") || $lost_login_or_email == ""
            || (extension_loaded("gd") && ENTER_CHECK == "yes" && $this->registry->main_class->format_striptags(trim($post_array['captcha'])) == "")
        ) {
            $this->error = 'pass_not_all_data';

            return;
        }
        if (ENTER_CHECK == "yes") {
            if (extension_loaded("gd")
                && (!isset($post_array["captcha"])
                    || (isset($_SESSION["captcha"])
                        && $_SESSION["captcha"]
                           !== $this->registry->main_class->format_striptags(trim($post_array["captcha"])))
                    || !isset($_SESSION["captcha"]))
            ) {
                if (isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                $this->error = 'pass_check_code';

                return;
            }
            if (isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        $lost_login_or_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->format_striptags($lost_login_or_email));
        $lost_login_or_email = $this->registry->main_class->processing_data($lost_login_or_email);
        $sql = "SELECT user_id,user_login,user_email,user_name FROM " . PREFIX . "_users WHERE user_temp = '0' and user_active = '1' and (user_login="
               . $lost_login_or_email . " or user_email=" . $lost_login_or_email . ")";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        if (!isset($row_exist) || $row_exist == 0) {
            $this->error = 'pass_not_found';

            return;
        }
        $user_id = intval($result->fields['0']);
        $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        mt_srand((double)microtime() * 1000000);
        $maxran = 1000000;
        $check_num = mt_rand(0, $maxran);
        $systemdk_act_id = md5($check_num);
        $arr = [
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'v',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'V',
            'X',
            'Y',
            'Z',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0',
            '!',
            '?',
        ];
        $user_new_password = "";
        for ($i = 0; $i < 10; $i++) {
            $index = rand(0, count($arr) - 1);
            $user_new_password .= $arr[$index];
        }
        $user_new_password = substr(
            substr($this->registry->main_class->processing_data($this->registry->main_class->format_striptags($user_new_password), 'need'), 0, -1), 1
        );
        $user_new_password_send = $user_new_password;
        $user_new_password = crypt($user_new_password, $this->registry->main_class->salt());
        $this->db->StartTrans();
        $sql = "UPDATE " . PREFIX . "_users SET user_activate_key = '" . $systemdk_act_id . "',user_new_password = '" . $user_new_password
               . "' WHERE user_temp = '0' and user_active = '1' and user_id='" . $user_id . "'";
        $result = $this->db->Execute($sql);
        $this->result['update_result'] = $result;
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        } elseif ($num_result > 0) {
            $this->result['need_send_email'] = 'yes';
            $this->result['modules_account_siteurl'] = SITE_URL;
            if ($user_name != '') {
                $this->result['modules_account_regusername'] = $user_name;
            } else {
                $this->result['modules_account_regusername'] = 'no';
            }
            $this->user_data_array = [
                'user_login'        => $user_login,
                'user_new_password' => $user_new_password_send,
                'systemdk_act_id'   => $systemdk_act_id,
                'user_email'        => $user_email,
            ];
        }
        if ($result === false) {
            $this->error_array = $error;
            $this->error = 'lost_pass_sql_error';

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'lost_pass_not_update';

            return;
        }
    }


    public function account_newpassconf($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['modules_account_confcode'])) {
            $array['act_code'] = $this->registry->main_class->format_striptags(trim($array['modules_account_confcode']));
            $modules_account_conf_code = 1;
        }
        if (isset($array['act_code'])) {
            $act_code = $this->registry->main_class->format_striptags(trim($array['act_code']));
        }
        if (isset($modules_account_conf_code) && $modules_account_conf_code == 1) {
            if (ENTER_CHECK == "yes") {
                if (extension_loaded("gd")
                    && (!isset($array["captcha"])
                        || (isset($_SESSION["captcha"]) && $_SESSION["captcha"] !== $this->registry->main_class->format_striptags(trim($array["captcha"])))
                        || !isset($_SESSION["captcha"]))
                ) {
                    if (isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    $this->error = 'new_pass_conf_check_code';

                    return;
                }
                if (isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
        }
        if (!isset($array['act_code']) || $act_code == "") {
            $this->error = 'new_pass_conf_no_act_code';

            return;
        }
        $sql = "SELECT user_id,user_new_password FROM " . PREFIX . "_users WHERE user_temp = '0' and user_active = '1' and user_activate_key = "
               . $this->registry->main_class->processing_data($act_code);
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        if (!isset($row_exist) || $row_exist < 1) {
            $this->error = 'new_pass_conf_no_act_code';

            return;
        } elseif (isset($modules_account_conf_code) && $modules_account_conf_code == 1) {
            $user_change_id = intval($result->fields['0']);
            $user_new_password = $result->fields['1'];
            $sql = "UPDATE " . PREFIX . "_users SET user_password = '" . $user_new_password
                   . "',user_session_code = NULL,user_session_agent = NULL,user_activate_key = NULL,user_new_password = NULL WHERE user_temp = '0' and user_active = '1' and user_id = '"
                   . $user_change_id . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result === false) {
                $this->error_array = $error;
                $this->error = 'lost_pass_sql_error';
            } elseif ($result !== false && $num_result == 0) {
                $this->error = 'new_pass_conf_not_update';
            } else {
                $this->result['account_message'] = 'new_pass_conf_ok';
            }

            return;
        }
        $this->result['modules_account_confcode'] = $act_code;
        if (extension_loaded("gd") && (ENTER_CHECK == "yes")) {
            $this->result['enter_check'] = 'yes';
        } else {
            $this->result['enter_check'] = 'no';
        }
    }


    public function mainuser($check = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $user_id = intval($this->registry->main_class->get_user_id());
        if ($check) {
            if (empty($user_id)) {
                $this->registry->main_class->logout();
                $this->error = 'empty_user_id';
            }

            return;
        }
        $sql =
            "SELECT user_id,user_name,user_surname,user_website,user_email,user_login,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_skype,user_gmt,user_notes,user_reg_date,user_view_email FROM "
            . PREFIX . "_users WHERE user_temp = '0' and user_id = '" . $user_id . "' and user_active = '1'";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        if (!isset($row_exist) || $row_exist == 0) {
            $this->error = 'main_user_not_found';

            return;
        }
        $user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $user_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        $user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
        $user_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $user_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
        $user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
        $user_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
        $user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
        $user_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
        $user_skype = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
        $user_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
        $user_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
        $user_regdate = intval($result->fields['15']);
        $user_regdate = date("d.m.y H:i", $user_regdate);
        $user_viewemail = intval($result->fields['16']);
        $user_all[] = [
            "user_name"      => $user_name,
            "user_surname"   => $user_surname,
            "user_website"   => $user_website,
            "user_email"     => $user_email,
            "user_login"     => $user_login,
            "user_rights"    => $user_rights,
            "user_country"   => $user_country,
            "user_city"      => $user_city,
            "user_address"   => $user_address,
            "user_telephone" => $user_telephone,
            "user_icq"       => $user_icq,
            "user_skype"     => $user_skype,
            "user_gmt"       => $user_gmt,
            "user_notes"     => $user_notes,
            "user_regdate"   => $user_regdate,
            "user_viewemail" => $user_viewemail,
        ];
        $this->result['user_all'] = $user_all;
        $this->result['user_name'] = $this->registry->main_class->get_user_name();
    }


    public function account_edit_save($post_array, $check = false)
    {
        if ($check) {
            $this->mainuser($check);

            return;
        }
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($post_array)) {
            $keys = [
                'user_edit_name',
                'user_edit_surname',
                'user_edit_website',
                'user_edit_email',
                'user_edit_newpassword',
                'user_edit_newpassword2',
                'user_edit_country',
                'user_edit_city',
                'user_edit_address',
                'user_edit_telephone',
                'user_edit_icq',
                'user_edit_skype',
                'user_edit_gmt',
                'user_edit_notes',
            ];
            foreach ($post_array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if (isset($post_array['user_edit_viewemail'])) {
            $data_array['user_edit_viewemail'] = intval($post_array['user_edit_viewemail']);
        }
        if ((!isset($post_array['user_edit_name']) || !isset($post_array['user_edit_surname']) || !isset($post_array['user_edit_website'])
             || !isset($post_array['user_edit_email'])
             || !isset($post_array['user_edit_newpassword'])
             || !isset($post_array['user_edit_newpassword2'])
             || !isset($post_array['user_edit_country'])
             || !isset($post_array['user_edit_city'])
             || !isset($post_array['user_edit_address'])
             || !isset($post_array['user_edit_telephone'])
             || !isset($post_array['user_edit_icq'])
             || !isset($post_array['user_edit_skype'])
             || !isset($post_array['user_edit_gmt'])
             || !isset($post_array['user_edit_notes'])
             || !isset($post_array['user_edit_viewemail']))
            || ($data_array['user_edit_email'] == "" || $data_array['user_edit_gmt'] == "")
        ) {
            $this->error = 'edit_not_all_data';

            return;
        }
        $user_edit_id = intval($this->registry->main_class->get_user_id());
        $keys = [
            'user_edit_name',
            'user_edit_surname',
            'user_edit_website',
            'user_edit_country',
            'user_edit_city',
            'user_edit_address',
            'user_edit_telephone',
            'user_edit_icq',
            'user_edit_skype',
            'user_edit_notes',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                if ($value == "") {
                    $data_array[$key] = 'NULL';
                } else {
                    $value = $this->registry->main_class->format_striptags($value);
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                }
            }
        }
        $data_array['user_edit_email'] = $this->registry->main_class->format_striptags($data_array['user_edit_email']);
        if (!$this->registry->main_class->check_email($data_array['user_edit_email'])) {
            $this->error = 'edit_email';

            return;
        }
        $data_array['user_edit_email'] = $this->registry->main_class->processing_data($data_array['user_edit_email']);
        $data_array['user_edit_gmt'] = $this->registry->main_class->format_striptags($data_array['user_edit_gmt']);
        $data_array['user_edit_gmt'] = $this->registry->main_class->processing_data($data_array['user_edit_gmt']);
        if (isset($data_array['user_edit_newpassword']) && $data_array['user_edit_newpassword'] != "") {
            $data_array['user_edit_newpassword'] = $this->registry->main_class->format_striptags($data_array['user_edit_newpassword']);
            $data_array['user_edit_newpassword'] = $this->registry->main_class->processing_data($data_array['user_edit_newpassword']);
            $data_array['user_edit_newpassword'] = substr(substr($data_array['user_edit_newpassword'], 0, -1), 1);
            $data_array['user_edit_newpassword2'] = $this->registry->main_class->format_striptags($data_array['user_edit_newpassword2']);
            $data_array['user_edit_newpassword2'] = $this->registry->main_class->processing_data($data_array['user_edit_newpassword2']);
            $data_array['user_edit_newpassword2'] = substr(substr($data_array['user_edit_newpassword2'], 0, -1), 1);
            if ($data_array['user_edit_newpassword'] !== $data_array['user_edit_newpassword2'] || $data_array['user_edit_newpassword'] == "") {
                $this->error = 'edit_pass';

                return;
            }
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_users WHERE user_id <> '" . $user_edit_id . "' and user_email = " . $data_array['user_edit_email'];
            $insert_result = $this->db->Execute($sql0);
            if ($insert_result) {
                if (isset($insert_result->fields['0'])) {
                    $num_rows = intval($insert_result->fields['0']);
                } else {
                    $num_rows = 0;
                }
                if ($num_rows > 0) {
                    $this->error = 'find_such_user';

                    return;
                }
                $data_array['user_edit_password'] = crypt($data_array['user_edit_newpassword'], $this->registry->main_class->salt());
                $sql = "UPDATE " . PREFIX . "_users SET user_name = " . $data_array['user_edit_name'] . ",user_surname = "
                       . $data_array['user_edit_surname'] . ",user_website = " . $data_array['user_edit_website'] . ",user_email = " . $data_array['user_edit_email']
                       . ",user_password = '" . $data_array['user_edit_password'] . "',user_session_code = null,user_session_agent = null,user_country = "
                       . $data_array['user_edit_country'] . ",user_city = " . $data_array['user_edit_city'] . ",user_address = " . $data_array['user_edit_address']
                       . ",user_telephone = " . $data_array['user_edit_telephone'] . ",user_icq = " . $data_array['user_edit_icq'] . ",user_skype = "
                       . $data_array['user_edit_skype'] . ",user_gmt = " . $data_array['user_edit_gmt'] . ",user_notes = " . $data_array['user_edit_notes']
                       . ",user_view_email = '" . $data_array['user_edit_viewemail'] . "' WHERE user_temp = '0' and user_active = '1' and user_id = '" . $user_edit_id
                       . "'";
                $insert_result = $this->db->Execute($sql);
                $num_result = $this->db->Affected_Rows();
                if ($insert_result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                } else {
                    $this->registry->main_class->logout();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        } else {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_users WHERE user_id <> '" . $user_edit_id . "' and user_email = " . $data_array['user_edit_email'];
            $insert_result = $this->db->Execute($sql0);
            if ($insert_result) {
                if (isset($insert_result->fields['0'])) {
                    $num_rows = intval($insert_result->fields['0']);
                } else {
                    $num_rows = 0;
                }
                if ($num_rows > 0) {
                    $this->error = 'find_such_user';

                    return;
                }
                $sql = "UPDATE " . PREFIX . "_users SET user_name = " . $data_array['user_edit_name'] . ",user_surname = " . $data_array['user_edit_surname']
                       . ",user_website = " . $data_array['user_edit_website'] . ",user_email = " . $data_array['user_edit_email'] . ",user_country = "
                       . $data_array['user_edit_country'] . ",user_city = " . $data_array['user_edit_city'] . ",user_address = " . $data_array['user_edit_address']
                       . ",user_telephone = " . $data_array['user_edit_telephone'] . ",user_icq = " . $data_array['user_edit_icq'] . ",user_skype = "
                       . $data_array['user_edit_skype'] . ",user_gmt = " . $data_array['user_edit_gmt'] . ",user_notes = " . $data_array['user_edit_notes']
                       . ",user_view_email = '" . $data_array['user_edit_viewemail'] . "' WHERE user_temp = '0' and user_active = '1' and user_id = '" . $user_edit_id
                       . "'";
                $insert_result = $this->db->Execute($sql);
                $num_result = $this->db->Affected_Rows();
                if ($insert_result === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($insert_result === false) {
            $this->error = 'edit_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($insert_result !== false && $num_result == 0) {
            $this->error = 'edit_not_updated';

            return;
        }
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|show");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|users|edit|" . $user_edit_id);
        $this->registry->main_class->systemdk_clearcache("modules|account|show|" . $user_edit_id);
        $this->registry->main_class->systemdk_clearcache("modules|account|edit|" . $user_edit_id);
        $this->registry->main_class->systemdk_clearcache("homepage");
        $this->result = 'edit_ok';
    }


    public function account_logout()
    {
        if ($this->registry->main_class->is_user()) {
            $this->registry->main_class->logout();
        }
    }
}
<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_administrator.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class admin_administrator extends model_base
{


    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function mainenter()
    {
        $this->result = false;
        if (isset($_SESSION['systemdk_is_admin'])) {
            unset($_SESSION['systemdk_is_admin']);
        }
        if (extension_loaded("gd") && (ENTER_CHECK == "yes")) {
            $this->result['enter_check'] = "yes";
        } else {
            $this->result['enter_check'] = "no";
        }
    }


    public function index()
    {
        $this->result = false;
        $phpversion = phpversion();
        $query = $this->db->ServerInfo();
        $sqlversion = $query['description'] . ' (' . $query['version'] . ')';
        $register_globals = ini_get('register_globals');
        $safe_mode = ini_get('safe_mode');
        $display_errors = ini_get('display_errors');
        $log_errors = ini_get('log_errors');
        $zlib_compression = ini_get('zlib.output_compression');
        $magic_quotes_gpc = ini_get('magic_quotes_gpc');
        $config_memory_limit = ini_get('memory_limit');
        if (strlen(ini_get('disable_functions')) > 1) {
            $config_disable_functions = ini_get('disable_functions');
        } else {
            $config_disable_functions = "no";
        }
        $config_site_status = SITE_STATUS;
        if (@php_uname()) {
            $config_os_version = php_uname();
        } else {
            $config_os_version = "no";
        }
        $config_maxupload = $this->registry->main_class->format_size(intval(ini_get('upload_max_filesize')) * 1024 * 1024);
        if (DBTYPE == 'mysqli' || DBTYPE == 'pdo' || DBTYPE == 'mysql' || DBTYPE == 'mysqlt' || DBTYPE == 'maxsql') {
            $config_db_size = 0;
            $sql = "SHOW TABLE STATUS FROM " . DB_NAME;
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    while (!$result->EOF) {
                        if (strpos($result->fields['0'], PREFIX . "_") !== false) {
                            if (isset($result->fields['17'])) {
                                $config_db_size += $result->fields['6'] + $result->fields['8']; // for mysql < v5, Data_length + Index_length
                            } else {
                                $config_db_size += $result->fields['5'] + $result->fields['7']; //Data_length + Index_length
                            }
                        }
                        $result->MoveNext();
                    }
                    $config_db_size = $this->registry->main_class->format_size($config_db_size);
                }
            }
            $this->result['config_db_size'] = $config_db_size;
        } else {
            $this->result['config_db_size'] = "no";
        }
        if (@disk_free_space(".")) {
            $config_diskfreespace = $this->registry->main_class->format_size(disk_free_space("."));
        } else {
            $config_diskfreespace = "no";
        }
        $countadminmodules = 0;
        $countmainmodules = 0;
        $dir = dir(__SITE_ADMIN_PATH . '/modules');
        while (false !== ($entry = $dir->read())) {
            if (strpos($entry, ".") === 0) {
                continue;
            } elseif (preg_match("/html/i", $entry)) {
                continue;
            } else {
                $countadminmodules++;
            }
        }
        $dir->close();
        $dir = dir(__SITE_PATH . '/modules');
        while (false !== ($entry = $dir->read())) {
            if (strpos($entry, ".") === 0) {
                continue;
            } elseif (preg_match("/html/i", $entry)) {
                continue;
            } else {
                $countmainmodules++;
            }
        }
        $dir->close();
        $this->result['phpversion'] = $phpversion;
        $this->result['config_os_version'] = $config_os_version;
        $this->result['sqlversion'] = DBTYPE . " " . $sqlversion;
        $this->result['systemdk_version'] = SYSTEMDK_VERSION . " " . SYSTEMDK_DESCRIPTION;
        $this->result['register_globals'] = $register_globals;
        $this->result['safe_mode'] = $safe_mode;
        $this->result['display_errors'] = $display_errors;
        $this->result['log_errors'] = $log_errors;
        $this->result['zlib_compression'] = $zlib_compression;
        $this->result['magic_quotes_gpc'] = $magic_quotes_gpc;
        $this->result['persistency'] = DB_PERSISTENCY;
        $this->result['caching'] = SMARTY_CACHING;
        $this->result['cache_lifetime'] = CACHE_LIFETIME;
        $this->result['gzip'] = GZIP;
        $this->result['enter_check'] = ENTER_CHECK;
        $this->result['countadminmodules'] = $countadminmodules;
        $this->result['countmainmodules'] = $countmainmodules;
        $this->result['config_site_status'] = $config_site_status;
        $this->result['config_diskfreespace'] = $config_diskfreespace;
        $this->result['config_memory_limit'] = $config_memory_limit;
        $this->result['config_maxupload'] = $config_maxupload;
        $this->result['config_disable_functions'] = $config_disable_functions;
        $this->result['systemdk_account_registration'] = SYSTEMDK_ACCOUNT_REGISTRATION;
        $this->result['systemdk_account_activation'] = SYSTEMDK_ACCOUNT_ACTIVATION;
        $this->result['systemdk_account_maxusers'] = SYSTEMDK_ACCOUNT_MAXUSERS;
        $this->result['systemdk_account_maxactivationdays'] = SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS;
        $this->result['systemdk_feedback_receiver'] = SYSTEMDK_FEEDBACK_RECEIVER;
        $this->result['systemdk_ipantispam'] = SYSTEMDK_IPANTISPAM;
    }


    public function mainadmin_edit()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $admin_id = intval($this->registry->main_class->get_user_id());
        if (intval($admin_id) == 0) {
            $this->error = 'admin_id';

            return;
        }
        $result = $this->registry->main_class->get_user_info();
        if ($result) {
            if (isset($result['0'])) {
                $row_exist = intval($result['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $admin_id = intval($result['0']);
                $admin_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['1']));
                $admin_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['2']));
                $admin_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['3']));
                $admin_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['4']));
                $admin_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['7']));
                $admin_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['8']));
                $admin_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['9']));
                $admin_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['10']));
                $admin_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['11']));
                $admin_skype = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['12']));
                $admin_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['13']));
                $admin_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result['14']));
                $admin_viewemail = intval($result['19']);
                $admin_all[] = [
                    "admin_id"        => $admin_id,
                    "admin_name"      => $admin_name,
                    "admin_surname"   => $admin_surname,
                    "admin_website"   => $admin_website,
                    "admin_email"     => $admin_email,
                    "admin_country"   => $admin_country,
                    "admin_city"      => $admin_city,
                    "admin_address"   => $admin_address,
                    "admin_telephone" => $admin_telephone,
                    "admin_icq"       => $admin_icq,
                    "admin_skype"     => $admin_skype,
                    "admin_gmt"       => $admin_gmt,
                    "admin_notes"     => $admin_notes,
                    "admin_viewemail" => $admin_viewemail,
                ];
                $this->result['admin_all'] = $admin_all;
            } else {
                $this->error = 'not_found';

                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
    }


    public function mainadmin_edit_inbase($post_array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $admin_edit_id = intval($this->registry->main_class->get_user_id());
        if ($admin_edit_id == 0) {
            $this->error = 'admin_id';

            return;
        }
        if (!empty($post_array)) {
            $keys = [
                'admin_edit_name',
                'admin_edit_surname',
                'admin_edit_website',
                'admin_edit_email',
                'admin_edit_newpassword',
                'admin_edit_newpassword2',
                'admin_edit_country',
                'admin_edit_city',
                'admin_edit_address',
                'admin_edit_telephone',
                'admin_edit_icq',
                'admin_edit_skype',
                'admin_edit_gmt',
                'admin_edit_notes',
            ];
            foreach ($post_array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if (isset($post_array['admin_edit_viewemail'])) {
            $data_array['admin_edit_viewemail'] = intval($post_array['admin_edit_viewemail']);
        }
        if ((!isset($post_array['admin_edit_name']) || !isset($post_array['admin_edit_surname']) || !isset($post_array['admin_edit_website'])
             || !isset($post_array['admin_edit_email'])
             || !isset($post_array['admin_edit_newpassword'])
             || !isset($post_array['admin_edit_newpassword2'])
             || !isset($post_array['admin_edit_country'])
             || !isset($post_array['admin_edit_city'])
             || !isset($post_array['admin_edit_address'])
             || !isset($post_array['admin_edit_telephone'])
             || !isset($post_array['admin_edit_icq'])
             || !isset($post_array['admin_edit_skype'])
             || !isset($post_array['admin_edit_gmt'])
             || !isset($post_array['admin_edit_notes'])
             || !isset($post_array['admin_edit_viewemail']))
            || ($data_array['admin_edit_email'] == "" || $data_array['admin_edit_gmt'] == "")
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $keys = [
            'admin_edit_name',
            'admin_edit_surname',
            'admin_edit_website',
            'admin_edit_country',
            'admin_edit_city',
            'admin_edit_address',
            'admin_edit_telephone',
            'admin_edit_icq',
            'admin_edit_skype',
            'admin_edit_notes',
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
        $data_array['admin_edit_email'] = $this->registry->main_class->format_striptags($data_array['admin_edit_email']);
        if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['admin_edit_email']))) {
            $this->error = 'incorrect_email';

            return;
        }
        $data_array['admin_edit_email'] = $this->registry->main_class->processing_data($data_array['admin_edit_email']);
        $data_array['admin_edit_gmt'] = $this->registry->main_class->format_striptags($data_array['admin_edit_gmt']);
        $data_array['admin_edit_gmt'] = $this->registry->main_class->processing_data($data_array['admin_edit_gmt']);
        if (!empty($data_array['admin_edit_newpassword'])) {
            $data_array['admin_edit_newpassword'] = $this->registry->main_class->format_striptags($data_array['admin_edit_newpassword']);
            $data_array['admin_edit_newpassword'] = $this->registry->main_class->processing_data($data_array['admin_edit_newpassword']);
            $data_array['admin_edit_newpassword'] = substr(substr($data_array['admin_edit_newpassword'], 0, -1), 1);
            $data_array['admin_edit_newpassword2'] = $this->registry->main_class->format_striptags($data_array['admin_edit_newpassword2']);
            $data_array['admin_edit_newpassword2'] = $this->registry->main_class->processing_data($data_array['admin_edit_newpassword2']);
            $data_array['admin_edit_newpassword2'] = substr(substr($data_array['admin_edit_newpassword2'], 0, -1), 1);
            if ($data_array['admin_edit_newpassword'] !== $data_array['admin_edit_newpassword2'] || $data_array['admin_edit_newpassword'] == "") {
                $this->error = 'add_error_pass';

                return;
            } else {
                $sql0 = "SELECT user_id FROM " . PREFIX . "_users WHERE user_id <> '" . $admin_edit_id . "' AND user_email = " . $data_array['admin_edit_email'];
                $result0 = $this->db->Execute($sql0);
                if ($result0) {
                    if (isset($result0->fields['0'])) {
                        $row_exist = intval($result0->fields['0']);
                    } else {
                        $row_exist = 0;
                    }
                    if ($row_exist == 0) {
                        $data_array['admin_edit_password'] = crypt($data_array['admin_edit_newpassword'], $this->registry->main_class->salt());
                        $sql = "UPDATE " . PREFIX . "_users SET user_name = " . $data_array['admin_edit_name'] . ",user_surname = "
                               . $data_array['admin_edit_surname'] . ",user_website = " . $data_array['admin_edit_website'] . ",user_email = "
                               . $data_array['admin_edit_email'] . ",user_password = '" . $data_array['admin_edit_password'] . "',user_country = "
                               . $data_array['admin_edit_country'] . ",user_city = " . $data_array['admin_edit_city'] . ",user_address = "
                               . $data_array['admin_edit_address'] . ",user_telephone = " . $data_array['admin_edit_telephone'] . ",user_icq = "
                               . $data_array['admin_edit_icq'] . ",user_skype = " . $data_array['admin_edit_skype'] . ",user_gmt = " . $data_array['admin_edit_gmt']
                               . ",user_notes = " . $data_array['admin_edit_notes'] . ",user_view_email = '" . $data_array['admin_edit_viewemail'] . "' WHERE user_id = '"
                               . $admin_edit_id . "'";
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
                        $this->error = 'find_such_admin';

                        return;
                    }
                } else {
                    $insert_result = false;
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            }
        } else {
            $sql0 = "SELECT user_id FROM " . PREFIX . "_users WHERE user_id <> '" . $admin_edit_id . "' AND user_email = " . $data_array['admin_edit_email'] . "";
            $result0 = $this->db->Execute($sql0);
            if ($result0) {
                if (isset($result0->fields['0'])) {
                    $row_exist = intval($result0->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if ($row_exist == 0) {
                    $sql = "UPDATE " . PREFIX . "_users SET user_name = " . $data_array['admin_edit_name'] . ",user_surname = "
                           . $data_array['admin_edit_surname'] . ",user_website = " . $data_array['admin_edit_website'] . ",user_email = "
                           . $data_array['admin_edit_email']
                           . ",user_country = " . $data_array['admin_edit_country'] . ",user_city = " . $data_array['admin_edit_city'] . ",user_address = "
                           . $data_array['admin_edit_address'] . ",user_telephone = " . $data_array['admin_edit_telephone'] . ",user_icq = "
                           . $data_array['admin_edit_icq']
                           . ",user_skype = " . $data_array['admin_edit_skype'] . ",user_gmt = " . $data_array['admin_edit_gmt'] . ",user_notes = "
                           . $data_array['admin_edit_notes'] . ",user_view_email = '" . $data_array['admin_edit_viewemail'] . "' WHERE user_id = '" . $admin_edit_id
                           . "'";
                    $insert_result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if ($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                } else {
                    $this->error = 'find_such_admin';

                    return;
                }
            } else {
                $insert_result = false;
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($insert_result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($insert_result !== false && $num_result == 0) {
            $this->error = 'not_done';

            return;
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|edit|" . $admin_edit_id);
            $this->registry->main_class->systemdk_clearcache("systemadmin|edit|" . $admin_edit_id);
            $this->result = 'edit_ok';
        }
    }


    public function logout()
    {
        $this->registry->main_class->logout();
    }
}
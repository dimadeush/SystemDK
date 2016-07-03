<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_main.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class model_main extends model_base
{


    private $systemdk_is_admin_now = 0;
    private $systemdk_is_user_now = 0;
    private $user_id;
    private $user_login;
    private $user_name;
    private $user_rights;


    public function index()
    {
        $this->registry->my_cache_id = $_SERVER['REQUEST_URI'];
        $this->registry->ip_addr = getenv('REMOTE_ADDR');
        $this->registry->language = LANGUAGE;
        if (isset($this->registry->GET_lang) && trim($this->registry->GET_lang) != ""
            && ((defined('SYSTEMDK_SITE_LANG') && SYSTEMDK_SITE_LANG === "yes")
                || (defined('SYSTEMDK_ADMIN_SITE_LANG') && SYSTEMDK_ADMIN_SITE_LANG === "yes" && defined('__SITE_ADMIN_PART') && __SITE_ADMIN_PART === 'yes'))
        ) {
            $lang = substr($this->format_striptags(trim($this->registry->GET_lang)), 0, 2);
            if (file_exists(__SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $lang) && trim($lang) != ""
                && file_exists(__SITE_PATH . "/includes/data/" . $lang . "_installed.dat")
            ) {
                $site_lang = $lang;
                if ((!isset($this->registry->GET_ilang) || trim(substr($this->format_striptags(trim($this->registry->GET_ilang)), 0, 2)) == "")
                    && (!isset($_SESSION["ilanguage"]) || trim(substr($this->format_striptags(trim($_SESSION["ilanguage"])), 0, 2)) == "")
                    && (
                        (defined('SYSTEMDK_INTERFACE_LANG') && SYSTEMDK_INTERFACE_LANG === "yes")
                        || (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') && SYSTEMDK_ADMIN_INTERFACE_LANG === "yes" && defined('__SITE_ADMIN_PART')
                            && __SITE_ADMIN_PART === 'yes')
                    )
                ) {
                    $_SESSION["ilanguage"] = $lang;
                    $this->registry->language = $lang;
                } elseif ((defined('SYSTEMDK_INTERFACE_LANG') && SYSTEMDK_INTERFACE_LANG === "no" && !defined('__SITE_ADMIN_PART'))
                          || (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') && SYSTEMDK_ADMIN_INTERFACE_LANG === "no" && defined('__SITE_ADMIN_PART')
                              && __SITE_ADMIN_PART === 'yes'
                              && defined('SYSTEMDK_INTERFACE_LANG')
                              && SYSTEMDK_INTERFACE_LANG === "no")
                ) {
                    $this->registry->language = $lang;
                }
            }
        }
        if (!isset($site_lang)) {
            $this->registry->sitelang = $this->registry->language;
        } else {
            $this->registry->sitelang = $site_lang;
        }
        if (isset($this->registry->GET_ilang) && trim($this->registry->GET_ilang) != ""
            && ((defined('SYSTEMDK_INTERFACE_LANG') && SYSTEMDK_INTERFACE_LANG === "yes")
                || (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') && SYSTEMDK_ADMIN_INTERFACE_LANG === "yes" && defined('__SITE_ADMIN_PART') && __SITE_ADMIN_PART === 'yes'))
        ) {
            $i_lang = substr($this->format_striptags(trim($this->registry->GET_ilang)), 0, 2);
            if (file_exists(__SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $i_lang) && trim($i_lang) != "") {
                $_SESSION["ilanguage"] = $i_lang;
                $this->registry->language = $i_lang;
            }
        } elseif (isset($_SESSION["ilanguage"]) && trim($_SESSION["ilanguage"]) != ""
                  && (
                      (defined('SYSTEMDK_INTERFACE_LANG') && SYSTEMDK_INTERFACE_LANG === "yes")
                      || (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') && SYSTEMDK_ADMIN_INTERFACE_LANG === "yes" && defined('__SITE_ADMIN_PART')
                          && __SITE_ADMIN_PART === 'yes')
                  )
        ) {
            $i_lang = substr($this->format_striptags(trim($_SESSION["ilanguage"])), 0, 2);
            if (file_exists(__SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $i_lang) && trim($i_lang) != "") {
                $this->registry->language = $i_lang;
            }
        }
        if ((defined('__SITE_ADMIN_PART') && __SITE_ADMIN_PART === 'yes'
             && !file_exists(__SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $this->registry->language . "/" . ADMIN_DIR))
            || !file_exists(__SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $this->registry->language)
        ) {
            return 'lang_file';
        }
        if (file_exists(__SITE_PATH . "/includes/data/ban_ip.inc")) {
            include_once(__SITE_PATH . "/includes/data/ban_ip.inc");
            $ip_addr = $this->registry->ip_addr;
            $ip_net = explode("|", BAN_IP);
            foreach ($ip_net as $ban_client) {
                if ($ip_addr == $ban_client) {
                    return 'ban_ip';
                }
            }
        }
        if (file_exists(__SITE_PATH . "/install")) {
            return 'find_install';
        }
        if (SITE_STATUS == "off" && !defined('__SITE_ADMIN_PART')) {
            return 'site_off';
        }
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
        if (PHP_VERSION_ID < 50400) {
            return 'version_php';
        }
        foreach ($_GET as $secvalue) {
            if ((preg_match("/UNION/i", $secvalue)) || (preg_match("/OUTFILE/i", $secvalue)) || (preg_match("/FROM/i", $secvalue))
                || (preg_match("/<[^>]*script*\"?[^>]*>/i", $secvalue))
                || (preg_match("/<[^>]*object*\"?[^>]*>/i", $secvalue))
                || (preg_match("/<[^>]*iframe*\"?[^>]*>/i", $secvalue))
                || (preg_match("/<[^>]*applet*\"?[^>]*>/i", $secvalue))
                || (preg_match("/<[^>]*meta*\"?[^>]*>/i", $secvalue))
                || (preg_match("/<[^>]*style*\"?[^>]*>/i", $secvalue))
                || (preg_match("/<[^>]*form*\"?[^>]*>/i", $secvalue))
                || (preg_match("/\([^>]*\"?[^)]*\)/i", $secvalue))
                || (preg_match("/\"/i", $secvalue))
            ) {
                return 'find_tags';
            }
        }
        if (!$this->db->check_connect()) {
            return 'no_db_connect';
        }
        if (!get_magic_quotes_gpc()) {
            $this->registry->get_magic_quotes_gpc = 'off';
        } else {
            $this->registry->get_magic_quotes_gpc = 'on';
        }
        $check_http_host = strpos($_SERVER['HTTP_HOST'], '.');
        if ($check_http_host === false) {
            $this->registry->http_host = '';
        } else {
            $this->registry->http_host = $_SERVER['HTTP_HOST'];
        }
        $this->process_user();
        $this->who_online();
        //$this->modules_account_autodelete();
        //$this->modules_feedback_autodelete();
        //$this->blocks_check_process();
        //$this->main_pages();
        return true;
    }


    public function processing_data($processing_content, $need = 'no')
    {
        if ($need == 'need') {
            $processing_content = $this->db->qstr($processing_content, 0);
        } else {
            $processing_content = $this->db->qstr($processing_content, get_magic_quotes_gpc());
        }

        return $processing_content;
    }


    public function extracting_data($extracting_content)
    {
        $extracting_content = stripslashes(trim($extracting_content));
        if (DBTYPE == 'oci8' || DBTYPE == 'oci805' || DBTYPE == 'oci8po' || DBTYPE == 'odbc_oracle' || DBTYPE == 'oracle' || DBTYPE == 'mssql' || DBTYPE == 'ado_mssql'
            || DBTYPE == 'odbc_mssql'
        ) {
            $extracting_content = str_replace("''", "'", $extracting_content);
        }

        return $extracting_content;
    }


    public function extracting_data2($extracting_content)
    {
        $extracting_content = stripcslashes(trim($extracting_content));
        if (DBTYPE == 'oci8' || DBTYPE == 'oci805' || DBTYPE == 'oci8po' || DBTYPE == 'odbc_oracle' || DBTYPE == 'oracle' || DBTYPE == 'mssql' || DBTYPE == 'ado_mssql'
            || DBTYPE == 'odbc_mssql'
        ) {
            $extracting_content = str_replace("''", "'", $extracting_content);
        }

        return $extracting_content;
    }


    public function checkneed_stripslashes($stripslashes_content)
    {
        if (get_magic_quotes_gpc()) {
            $stripslashes_content = stripslashes($stripslashes_content);
        }

        return $stripslashes_content;
    }


    public function format_htmlspecchars($format_content)
    {
        $format_content = htmlspecialchars($format_content, ENT_QUOTES, SITE_CHARSET);

        return $format_content;
    }


    public function format_striptags($format_content, $allowable_tags = 'no')
    {
        if ($allowable_tags == 'no') {
            $format_content = strip_tags($format_content);
        } else {
            $format_content = strip_tags($format_content, $allowable_tags);
        }

        return $format_content;
    }


    public function format_htmlspecchars_stripslashes_striptags($format_content)
    {
        $format_content = $this->format_htmlspecchars($this->checkneed_stripslashes($this->format_striptags($format_content)));

        return $format_content;
    }


    public function database_close()
    {
        if (DB_PERSISTENCY === 'no') {
            $this->db->Close();
        }
    }


    public function db_process_sequence($sequence, $for_field)
    {
        $array = ['sequence_value' => false, 'sequence_value_string' => false, 'field_name_string' => false];
        if (DBTYPE == 'oci8' || DBTYPE == 'oci805' || DBTYPE == 'oci8po' || DBTYPE == 'odbc_oracle' || DBTYPE == 'oracle') {
            $value = $this->db->GenID($sequence);
            $array = [
                'sequence_value'        => $value,
                'sequence_value_string' => $value . ',',
                'field_name_string'     => $for_field . ',',
            ];
        }

        return $array;
    }


    public function db_get_last_insert_id()
    {
        if (DBTYPE == 'mysqli' || DBTYPE == 'pdo' || DBTYPE == 'mysql' || DBTYPE == 'mysqlt' || DBTYPE == 'maxsql') {
            $result = $this->db->Execute("select last_insert_id() as last_id");
            if ($result) {
                if (isset($result->fields['0'])) {
                    return intval($result->fields['0']);
                }
            }
        }

        return false;
    }


    public function check_db_need_lobs()
    {
        if (DBTYPE == 'oci8' || DBTYPE == 'oci805' || DBTYPE == 'oci8po' || DBTYPE == 'odbc_oracle' || DBTYPE == 'oracle') {
            return 'yes';
        } else {
            return 'no';
        }
    }


    public function check_db_need_from_clause()
    {
        if (DBTYPE == 'mssql' || DBTYPE == 'ado_mssql' || DBTYPE == 'odbc_mssql') {
            return false;
        } else {
            return 'FROM DUAL';
        }
    }


    private function process_user()
    {
        if (isset($this->registry->GET_func) && $this->format_striptags(trim($this->registry->GET_func)) == "account_logout") {
            $this->clear(['user' => 1]);
        } elseif (isset($this->registry->POST_user_login) && isset($this->registry->POST_user_password) && isset($this->registry->POST_func)
                  && $this->format_striptags(trim($this->registry->POST_user_login)) != ""
                  && trim($this->registry->POST_user_password) != ""
                  && ($this->format_striptags(trim($this->registry->POST_func)) == "mainuser"
                      || $this->format_striptags(trim($this->registry->POST_func == "mainadmin")))
        ) {
            if (preg_match("/[^a-zA-Zа-яА-Я0-9_-]/i", $this->format_striptags(trim($this->registry->POST_user_login)))) {
                if (ENTER_CHECK == "yes" && extension_loaded("gd")) {
                    $this->clear(['captcha' => 1, 'user' => 1]);
                } else {
                    $this->clear(['user' => 1]);
                }
                $this->registry->module_account_error = 'login';

                return false;
            }
            $user_login = substr(trim($this->registry->POST_user_login), 0, 25);
            $user_password = substr(trim($this->registry->POST_user_password), 0, 25);
            if (ENTER_CHECK == "yes" && extension_loaded("gd")) {
                if (empty($this->registry->POST_captcha)
                    || (isset($_SESSION["captcha"]) && isset($this->registry->POST_captcha)
                        && $_SESSION["captcha"] !== $this->format_striptags(trim($this->registry->POST_captcha)))
                    || empty($_SESSION["captcha"])
                ) {
                    $this->clear(['captcha' => 1, 'user' => 1]);
                    $this->registry->module_account_error = 'check_code';

                    return false;
                }
                $this->clear(['captcha' => 1]);
            }

            return $this->check_user($user_login, $user_password);
        } elseif ((isset($this->registry->POST_user_login) || isset($this->registry->POST_user_password))
                  && ($this->format_striptags(trim($this->registry->POST_func)) == "mainuser" || $this->format_striptags(trim($this->registry->POST_func == "mainadmin")))
        ) {
            if (ENTER_CHECK == "yes" && extension_loaded("gd")) {
                $this->clear(['captcha' => 1, 'user' => 1]);
            } else {
                $this->clear(['user' => 1]);
            }
            $this->registry->module_account_error = 'user_not_all_data';

            return false;
        } elseif ((!empty($_COOKIE['user_cookie']) || !empty($_SESSION['systemdk_user'])) && !isset($this->registry->POST_user_login)
                  && !isset($this->registry->POST_user_password)
        ) {
            $user_id = false;
            $user_session_code = false;
            if (!empty($_SESSION['systemdk_user']) && is_array($_SESSION['systemdk_user'])) {
                $user_session = $_SESSION['systemdk_user'];
                $user_session = [0 => base64_decode($user_session[0]), 1 => base64_decode($user_session[1])];
                $user_id = $user_session[0];
                $user_session_code = $user_session[1];
            } elseif (!empty($_COOKIE['user_cookie'])) {
                $user_cookie = base64_decode($_COOKIE['user_cookie']);
                $user_cookie = explode(":", $user_cookie);
                if (is_array($user_cookie)) {
                    $user_id = $user_cookie[0];
                    $user_session_code = $user_cookie[1];
                }
            }
            if (empty($user_id) || empty($user_session_code)) {
                $this->clear(['user' => 1]);
                $this->registry->module_account_error = 'empty_user_data';

                return false;
            }
            $user_login = false;
            $user_password = false;

            return $this->check_user($user_login, $user_password, $user_id, $user_session_code);
        }

        return false;
    }


    private function clear(array $clear_params)
    {
        if (!empty($clear_params['captcha'])) {
            if (isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        if (!empty($clear_params['user'])) {
            if (isset($_SESSION['systemdk_is_admin'])) {
                unset($_SESSION['systemdk_is_admin']);
            }
            if (isset($_SESSION['systemdk_user'])) {
                unset($_SESSION['systemdk_user']);
            }
            if (isset($_COOKIE['user_cookie'])) {
                setcookie("user_cookie", "", time(), "/" . SITE_DIR); //$this->registry->http_host
            }
        }
    }


    private function check_user($user_login, $user_password, $user_id = false, $user_session_code = false)
    {
        if (!empty($user_login) && !empty($user_password)) {
            $try_login = true;
            $user_login = $this->format_striptags($user_login);
            $user_login = $this->processing_data($user_login);
            $where1 = "user_login = " . $user_login;
        } else {
            $try_login = false;
            $user_id = intval($user_id);
            $user_session_code = $this->format_striptags($user_session_code);
            $user_session_code = $this->processing_data($user_session_code);
            $user_session_agent = $this->format_striptags($_SERVER['HTTP_USER_AGENT']);
            $user_session_agent = $this->processing_data($user_session_agent);
            $where1 = "user_id = '" . $user_id . "' and user_session_code = " . $user_session_code . " and user_session_agent = " . $user_session_agent;
        }
        $sql = "SELECT user_id, user_name, user_surname, user_login, user_password, user_session_code, user_session_agent, user_rights, user_active, user_activate_key "
               . "FROM " . PREFIX . "_users WHERE user_temp = '0' and " . $where1;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $exist = intval($result->fields['0']);
            } else {
                $exist = 0;
            }
        }
        if (isset($exist) && $exist > 0) {
            if ((($try_login && $this->extracting_data($result->fields['4']) === crypt($user_password, $this->extracting_data($result->fields['4']))
                  && $this->extracting_data($result->fields['4']) != "")
                 || !$try_login)
                && intval($result->fields['8']) == 1
            ) {
                $this->user_rights = $this->extracting_data($result->fields['7']);
                if ($this->user_rights === 'user') {
                    if (!$try_login || ($try_login && !defined('__SITE_ADMIN_PART'))) {
                        $this->systemdk_is_user_now = 1;
                    } else {
                        $this->registry->module_account_error = 'user_not_found';

                        return false;
                    }
                } elseif ($this->user_rights === 'admin' || $this->user_rights === 'main_admin') {
                    $this->systemdk_is_admin_now = 1;
                    $this->systemdk_is_user_now = 1;
                    $_SESSION['systemdk_is_admin'] = 'yes';
                } else {
                    return false;
                }
                $this->user_id = intval($result->fields['0']);
                $this->user_name = $this->format_htmlspecchars($this->extracting_data($result->fields['1']));
                $user_surname = $this->format_htmlspecchars($this->extracting_data($result->fields['2']));
                $this->user_login = $this->extracting_data($result->fields['3']);
                $user_session_code = $this->extracting_data($result->fields['5']);
                $user_session_agent = $this->extracting_data($result->fields['6']);
                if ($user_session_code == '' || $try_login) {
                    $user_session_code = $this->generate_code(15);
                    $user_session_agent = $this->format_striptags($_SERVER['HTTP_USER_AGENT']);
                }
                $user_session_agent = $this->processing_data($user_session_agent);
                $user_cookie = base64_encode("$this->user_id:$user_session_code");
                setcookie("user_cookie", "$user_cookie", time() + intval(SESSION_COOKIE_LIVE), "/" . SITE_DIR); //$this->registry->http_host
                $_SESSION['systemdk_user'] = [
                    0 => base64_encode($this->user_id),
                    1 => base64_encode($user_session_code),
                ];
                if (empty($this->user_name) && !empty($user_surname)) {
                    $this->user_name = $user_surname;
                } elseif (empty($this->user_name) && empty($user_surname)) {
                    $this->user_name = $this->format_htmlspecchars($this->extracting_data($result->fields['3']));
                } elseif (!empty($this->user_name) && !empty($user_surname)) {
                    $this->user_name = $this->user_name . " " . $user_surname;
                }
                $user_activate_key = $result->fields['9'];
                if (!empty($user_activate_key)) {
                    $this->db->Execute(
                        "UPDATE " . PREFIX . "_users SET user_activate_key = NULL,user_new_password = NULL,user_last_login_date = " . $this->get_time()
                        . ",user_session_code = '" . $user_session_code . "',user_session_agent = " . $user_session_agent . " WHERE user_id='" . $this->user_id . "'"
                    );
                } else {
                    $this->db->Execute(
                        "UPDATE " . PREFIX . "_users SET user_last_login_date = " . $this->get_time() . ",user_session_code = '" . $user_session_code
                        . "',user_session_agent = " . $user_session_agent . " WHERE user_id='" . $this->user_id . "'"
                    );
                }

                return true;
            } elseif ((($try_login && $this->extracting_data($result->fields['4']) === crypt($user_password, $this->extracting_data($result->fields['4']))
                        && $this->extracting_data($result->fields['4']) != "")
                       || !$try_login)
                      && intval($result->fields['8']) == 0
            ) {
                $this->clear(['user' => 1]);
                $this->registry->module_account_error = 'user_not_active';

                return false;
            } elseif ($try_login && $this->extracting_data($result->fields['4']) !== crypt($user_password, $this->extracting_data($result->fields['4']))
                      && $this->extracting_data($result->fields['4']) != ""
            ) {
                $this->clear(['user' => 1]);
                $this->registry->module_account_error = 'user_incorrect_pass';

                return false;
            }

            return false;
        } else {
            $this->clear(['user' => 1]);
            $this->registry->module_account_error = 'user_not_found';

            return false;
        }
    }


    private function generate_code($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $max = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $max)];
        }

        return $code;
    }


    public function salt($cost)
    {
        if (!is_numeric($cost) || $cost < 4 || $cost > 31) {
            throw new Exception("cost parameter must be between 4 and 31");
        }
        $rand = [];
        for ($i = 0; $i < 8; $i += 1) {
            $rand[] = pack('S', mt_rand(0, 0xffff));
        }
        $rand[] = substr(microtime(), 2, 6);
        $rand = sha1(implode('', $rand), true);
        $salt_prefix = '$2a$';
        if (PHP_VERSION_ID >= 50307) {
            $salt_prefix = '$2y$';
        }
        $salt = $salt_prefix . sprintf('%02d', $cost) . '$';
        $salt .= strtr(substr(base64_encode($rand), 0, 22), ['+' => '.']);

        return $salt;
    }


    public function logout()
    {
        $this->clear(['user' => 1]);
    }


    public function is_user()
    {
        return $this->systemdk_is_user_now;
    }


    public function is_admin()
    {
        return $this->systemdk_is_admin_now;
    }


    public function get_user_id()
    {
        return $this->user_id;
    }


    public function get_user_rights()
    {
        return $this->user_rights;
    }


    public function get_user_login()
    {
        return $this->user_login;
    }


    public function get_user_name()
    {
        return $this->user_name;
    }


    public function getSiteUrl()
    {
        return SITE_URL . "/" . SITE_DIR;
    }


    public function check_can_gzip()
    {
        if (headers_sent() || connection_aborted() || !function_exists('ob_gzhandler') || ini_get('zlib.output_compression') || GZIP !== 'yes') {
            return 0;
        }
        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
            return "x-gzip";
        }
        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            return "gzip";
        }

        return 0;
    }


    public function path($controller)
    {
        $sql = "SELECT module_custom_name, module_status, module_value_view, (SELECT max(main_menu_name) FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang
               . " WHERE (main_menu_module='" . $controller . "' OR main_menu_submodule='" . $controller
               . "') and main_menu_type not in ('main_page')) as main_menu_name,module_id FROM " . PREFIX . "_modules_" . $this->registry->sitelang
               . " WHERE module_name='" . $controller . "'";
        $result = $this->db->Execute($sql);

        return $result;
    }


    public function process_file_name($file)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $file);
    }


    public function check_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }


    public function set_sitemeta($title = 'no', $description = 'no', $keywords = 'no')
    {
        $site_name = false;
        if ($title != 'no' && trim($title) != '') {
            $title = $this->format_htmlspecchars($this->format_striptags($title));
            $site_name = $title;
        }
        $site_description = false;
        if ($description != 'no' && trim($description) != '') {
            $description = $this->format_htmlspecchars($this->format_striptags($description));
            $site_description = $description;
        }
        $site_keywords = false;
        if ($keywords != 'no' && trim($keywords) != '') {
            $keywords = $this->format_htmlspecchars($this->format_striptags($keywords));
            $site_keywords = $keywords;
        }

        return [
            "site_name"        => $site_name,
            "site_description" => $site_description,
            "site_keywords"    => $site_keywords,
        ];
    }


    public function check_player_entry($content)
    {
        if (defined('SYSTEMDK_PLAYER_RATIO') && SYSTEMDK_PLAYER_RATIO !== '') {
            $content = str_ireplace('class="flowplayer', 'data-ratio="' . $this->float_to_db(SYSTEMDK_PLAYER_RATIO) . '" class="flowplayer', $content);
        }
        if (defined('SYSTEMDK_PLAYER_CONTROLS') && SYSTEMDK_PLAYER_CONTROLS === 'yes') {
            $content = str_ireplace('class="flowplayer', 'class="flowplayer fixed-controls', $content);
            if (stripos($content, 'class="flowplayer') === false) {
                $content = str_ireplace('<video', '<video controls', $content);
            } else {
                $content = str_ireplace('<video', '<video', $content);
            }
        } else {
            $content = str_ireplace('="flowplayer', '="flowplayer', $content);
        }
        if (defined('SYSTEMDK_PLAYER_AUTOPLAY') && SYSTEMDK_PLAYER_AUTOPLAY === 'yes') {
            $content = str_ireplace('<video', '<video autoplay', $content);
        } else {
            $content = str_ireplace('<video', '<video', $content);
        }
        if (defined('SYSTEMDK_PLAYER_AUTOBUFFER') && SYSTEMDK_PLAYER_AUTOBUFFER === 'yes') {
            $content = str_ireplace('<video', '<video preload', $content);
        }
        if (defined('SYSTEMDK_PLAYER_AUDIOAUTOPLAY') && SYSTEMDK_PLAYER_AUDIOAUTOPLAY === 'yes') {
            $content = str_ireplace('<audio', '<audio autoplay', $content);
        } else {
            $content = str_ireplace('<audio', '<audio', $content);
        }
        if (defined('SYSTEMDK_PLAYER_AUDIOAUTOBUFFER') && SYSTEMDK_PLAYER_AUDIOAUTOBUFFER === 'yes') {
            $content = str_ireplace('<audio', '<audio preload', $content);
        }
        if (defined('SYSTEMDK_PLAYER_AUDIOCONTROLS') && SYSTEMDK_PLAYER_AUDIOCONTROLS === 'yes') {
            $content = str_ireplace('<audio', '<audio controls', $content);
        }
        $search_flowplayer = substr_count($content, '="flowplayer');
        $search_html5_video = substr_count($content, '<video');
        $search_html5_audio = substr_count($content, '<audio');
        $systemdk_html5_exist = false;
        if ($search_flowplayer > 0 || $search_html5_video > 0 || $search_html5_audio > 0) {
            $systemdk_html5_exist = true;
        }
        $check_player_entry = 0;
        if ($search_flowplayer > 0) {
            $check_player_entry = 1;
        }

        return [
            "content"              => $content,
            "search_flowplayer"    => $check_player_entry,
            "systemdk_html5_exist" => $systemdk_html5_exist,
        ];
    }


    public function split_content_by_pages($content)
    {
        $content = str_ireplace("<span style=\"display: none;\">&nbsp;</span></div>", "", $content);
        $content = str_ireplace("<div style=\"page-break-after: always;\">", "<div style=\"page-break-after: always\">", $content); // for ckeditor old version
        $content = explode("<div style=\"page-break-after: always\">", $content);

        return $content;
    }


    public function encode_rss_text($text, $rss_encoding)
    {
        if ($rss_encoding == "none" || ($rss_encoding != "UTF-8" && $rss_encoding != "WINDOWS-1251" && $rss_encoding != strtoupper(SITE_CHARSET))) {
            $rss_encoding = @mb_detect_encoding($text, "UTF-8, WINDOWS-1251", true);
        }
        if (!isset($rss_encoding) || $rss_encoding == "" || $rss_encoding === false) {
            $rss_encoding = strtoupper(SITE_CHARSET);
        }
        $trans_t = get_html_translation_table(HTML_ENTITIES);
        $trans_t = array_flip($trans_t);

        return strtr(mb_convert_encoding($this->format_striptags($text), SITE_CHARSET, $rss_encoding), $trans_t);
    }


    public function format_size($size)
    {
        if ($size >= 1073741824) {
            $size = round($size / 1073741824 * 100) / 100;
            $size = $size . " Gb";
        } elseif ($size >= 1048576) {
            $size = round($size / 1048576 * 100) / 100;
            $size = $size . " Mb";
        } elseif ($size >= 1024) {
            $size = round($size / 1024 * 100) / 100;
            $size = $size . " Kb";
        } else {
            $size = $size . " b";
        }

        return $size;
    }


    public function set_locale()
    {
        setlocale(LC_ALL, 'ru_RU.UTF-8');
    }


    public function float_to_db($float_value)
    {
        $float_value = preg_replace("/,/", ".", $float_value);
        $float_value = preg_replace('/\s+/', '', $float_value);
        $float_value = floatval($float_value);
        $locale_info = localeconv();
        if ($locale_info['thousands_sep'] == '.') {
            $locale_info['thousands_sep'] = false;
        }
        if ($locale_info['mon_thousands_sep'] == '.') {
            $locale_info['mon_thousands_sep'] = false;
        }
        $locale_search = [
            $locale_info['decimal_point'],
            $locale_info['mon_decimal_point'],
            $locale_info['thousands_sep'],
            $locale_info['mon_thousands_sep'],
        ];
        $locale_replace = ['.', '.', '', '',];

        return str_replace($locale_search, $locale_replace, $float_value);
    }


    public function get_time()
    {
        $time = time();

        return $time;
    }


    public function time_to_unix($string)
    {
        $format_date = DateTime::createFromFormat('d.m.Y H:i', $string);
        if (is_object($format_date)) {
            $format_date = $format_date->format('U');
        } else {
            $format_date = false;
        }

        return $format_date;
    }


    public function module_is_active($module)
    {
        $module = trim($module);
        $module = $this->format_striptags($module);
        $property = 'module_' . $module . '_status';
        if (isset($this->registry->$property)) {
            return $this->registry->$property;
        }
        $module = $this->processing_data($module);
        $sql = "SELECT module_id FROM " . PREFIX . "_modules_" . $this->registry->sitelang . " WHERE module_status = '1' and module_name=" . $module;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        if (isset($row_exist) && $row_exist > 0) {
            $this->registry->$property = 1;

            return 1;
        } else {
            $this->registry->$property = 0;

            return 0;
        }
    }


    public function block_is_active($block)
    {
        $block = trim($block);
        $block = $this->format_striptags($block);
        $property = 'block_' . $block . '_status';
        if (isset($this->registry->$property)) {
            return $this->registry->$property;
        }
        $block = $this->processing_data($block);
        $sql = "SELECT block_id FROM " . PREFIX . "_blocks_" . $this->registry->sitelang . " WHERE block_status = '1' and block_name=" . $block;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        if (isset($row_exist) && $row_exist > 0) {
            $this->registry->$property = 1;

            return 1;
        } else {
            $this->registry->$property = 0;

            return 0;
        }
    }


    public function block($block_locate)
    {
        $block_locate = trim($block_locate);
        $block_locate = $this->format_striptags($block_locate);
        $block_locate = $this->processing_data($block_locate);
        $block_locate = substr(substr($block_locate, 0, -1), 1);
        $sql = "SELECT block_id, block_name, block_custom_name, block_value_view, block_content, block_url, block_refresh, block_last_refresh FROM " . PREFIX
               . "_blocks_" . $this->registry->sitelang . " WHERE block_arrangements = '" . $block_locate . "' AND block_status = '1' ORDER BY block_priority ASC";
        $result_blocks = $this->db->Execute($sql);
        if ($result_blocks) {
            if (isset($result_blocks->fields['0'])) {
                $exist = intval($result_blocks->fields['0']);
            } else {
                $exist = 0;
            }
        }
        $blocks_alldata = false;
        if (isset($exist) && $exist > 0) {
            while (!$result_blocks->EOF) {
                $block_id = $result_blocks->fields['0'];
                $block_id = intval($block_id);
                $block_name = $this->extracting_data($result_blocks->fields['1']);
                $block_customname = $this->format_htmlspecchars($this->extracting_data($result_blocks->fields['2']));
                $block_valueview = $result_blocks->fields['3'];
                $block_valueview = intval($block_valueview);
                $block_content = $this->extracting_data($result_blocks->fields['4']);
                $block_content = $this->registry->main_class->search_player_entry($block_content);
                $block_url = $this->extracting_data($result_blocks->fields['5']);
                $block_refresh = intval($result_blocks->fields['6']);
                $block_lastrefresh = intval($result_blocks->fields['7']);
                if ($block_valueview == "0") {
                    $blocks_alldata[] = $this->block_render($block_name, $block_customname, $block_content, $block_id, $block_url, $block_refresh, $block_lastrefresh);
                } elseif ($block_valueview == "1" && ($this->is_user() || $this->is_admin())) {
                    $blocks_alldata[] = $this->block_render($block_name, $block_customname, $block_content, $block_id, $block_url, $block_refresh, $block_lastrefresh);
                } elseif ($block_valueview == "2" && $this->is_admin()) {
                    $blocks_alldata[] = $this->block_render($block_name, $block_customname, $block_content, $block_id, $block_url, $block_refresh, $block_lastrefresh);
                } elseif ($block_valueview == "3" && (!$this->is_user() || $this->is_admin())) {
                    $blocks_alldata[] = $this->block_render($block_name, $block_customname, $block_content, $block_id, $block_url, $block_refresh, $block_lastrefresh);
                }
                $result_blocks->MoveNext();
            }
        }

        return $blocks_alldata;
    }


    private function block_render($block_name, $block_customname, $block_content, $block_id, $block_url, $block_refresh, $block_lastrefresh)
    {
        if (empty($block_url)) {
            if (empty($block_name)) {
                $block_alldata = ["block_customname" => $block_customname, "block_content" => $block_content];

                return ($block_alldata);
            } else {
                $block_alldata = $this->block_file_include($block_customname, $block_name);

                return ($block_alldata);
            }
        } else {
            $block_alldata = $this->registry->main_class->block_rss($block_customname, $block_content, $block_url, $block_refresh, $block_lastrefresh, $block_id);

            return ($block_alldata);
        }
    }


    private function block_file_include($block_customname, $block_name)
    {
        clearstatcache();
        $block_name = $this->process_file_name($block_name);
        if (!file_exists(__SITE_PATH . "/blocks/" . $block_name . "/block_controller_" . $block_name . ".class.php")
            || !file_exists(
                __SITE_PATH . "/blocks/" . $block_name . "/block_model_" . $block_name . ".class.php"
            )
        ) {
            $block_content = 'error_file';
        } else {
            include_once(__SITE_PATH . "/blocks/" . $block_name . "/block_controller_" . $block_name . ".class.php");
            include_once(__SITE_PATH . "/blocks/" . $block_name . "/block_model_" . $block_name . ".class.php");
            $class_name = 'block_controller_' . $block_name;
            $block_controller = singleton::getinstance($class_name, $this->registry);
            $block_content = $block_controller->index();
        }
        if (empty($block_content)) {
            $block_content = 'error_content';
        }
        $block_alldata = [
            "block_name"       => $block_name,
            "block_customname" => $block_customname,
            "block_content"    => $block_content,
        ];

        return ($block_alldata);
    }


    private function who_online()
    {
        $ip_addr = $this->registry->ip_addr;
        if ($this->is_admin()) {
            $user_login = $this->get_user_login();
            $who = "admin";
        } elseif ($this->is_user()) {
            $user_login = $this->get_user_login();
            $who = "user";
        } else {
            $user_login = "";
            $who = "guest";
        }
        $past = $this->get_time() - 300;
        $sql = "DELETE FROM " . PREFIX . "_session WHERE session_time < '" . $past . "'";
        $this->db->Execute($sql);
        $user_login = $this->format_striptags($user_login);
        $user_login = $this->processing_data($user_login);
        $sql = "SELECT count(session_time) FROM " . PREFIX . "_session WHERE session_login = " . $user_login . " and session_host_address = '" . $ip_addr . "'";
        $result = $this->db->Execute($sql);
        if ($result) {
            $now = $this->get_time();
            $exist = intval(
                $result->fields['0']
            ); //$result->PO_RecordCount(PREFIX."_session","session_login = ".$user_login." and session_hostaddress = '".$ip_addr."'");
            if ($exist > 0) {
                $sql = "UPDATE " . PREFIX . "_session SET session_login=" . $user_login . ", session_time='" . $now . "', session_host_address = '" . $ip_addr
                       . "', session_who_online='" . $who . "' WHERE session_login = " . $user_login . " and session_host_address = '" . $ip_addr . "'";
                $this->db->Execute($sql);
            } else {
                $sql = "INSERT INTO " . PREFIX . "_session (session_login, session_time, session_host_address, session_who_online) VALUES (" . $user_login . ", '" . $now
                       . "', '" . $ip_addr . "', '" . $who . "')";
                $this->db->Execute($sql);
            }
        }
    }


    public function blocks_check_process()
    {
        $now = $this->get_time();
        $this->db->Execute(
            "UPDATE " . PREFIX . "_blocks_" . $this->registry->sitelang
            . " SET block_status = '0', block_term_expire = NULL WHERE block_term_expire IS NOT NULL and block_term_expire <= " . $now
            . " and block_term_expire_action = 'off'"
        );
        $num_result1 = $this->db->Affected_Rows();
        $this->db->Execute(
            "DELETE FROM " . PREFIX . "_blocks_" . $this->registry->sitelang . " WHERE block_term_expire IS NOT NULL and block_term_expire <= " . $now
            . " and block_term_expire_action = 'delete'"
        );
        $num_result2 = $this->db->Affected_Rows();
        if ($num_result1 > 0 || $num_result2 > 0) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|blocks|edit");
        }
    }


    public function messages_check_process()
    {
        $now = $this->get_time();
        $this->db->Execute(
            "UPDATE " . PREFIX . "_messages_" . $this->registry->sitelang
            . " SET message_status = '0', message_term_expire = NULL, message_term_expire_action = NULL WHERE message_term_expire IS NOT NULL and message_term_expire <= "
            . $now . " and message_term_expire_action = 'off'"
        );
        $num_result1 = $this->db->Affected_Rows();
        $this->db->Execute(
            "DELETE FROM " . PREFIX . "_messages_" . $this->registry->sitelang . " WHERE message_term_expire IS NOT NULL and message_term_expire <= " . $now
            . " and message_term_expire_action = 'delete'"
        );
        $num_result2 = $this->db->Affected_Rows();
        if ($num_result1 > 0 || $num_result2 > 0) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|messages|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|messages|edit");
        }
    }


    public function header()
    {
        singleton::getinstance('main_pages', $this->registry);
    }


    public function messages()
    {
        $sql = "SELECT message_id,message_title,message_content,message_notes,message_date,message_term_expire,message_value_view FROM " . PREFIX
               . "_messages_" . $this->registry->sitelang . " WHERE message_status='1' order by message_date DESC";
        $result_messages = $this->db->Execute($sql);
        if ($result_messages) {
            if (isset($result_messages->fields['0'])) {
                $row_exist = intval($result_messages->fields['0']);
            } else {
                $row_exist = 0;
            }
        }
        $message_alldata = false;
        if (isset($row_exist) && $row_exist > 0) {
            while (!$result_messages->EOF) {
                $message_id = intval($result_messages->fields['0']);
                $message_title = $this->format_htmlspecchars($this->extracting_data($result_messages->fields['1']));
                $message_content = $this->extracting_data($result_messages->fields['2']);
                $message_notes = $this->extracting_data($result_messages->fields['3']);
                if (empty($message_notes)) {
                    $message_notes = "no";
                }
                $message_date = intval($result_messages->fields['4']);
                $message_termexpire = intval($result_messages->fields['5']);
                $message_valueview = intval($result_messages->fields['6']);
                if ($message_termexpire == 0) {
                    $message_termexpire = "unlim";
                    $message_termexpireday = "";
                } else {
                    $message_termexpire = date("d.m.y H:i", $message_termexpire);
                    $message_termexpireday = intval(($message_termexpire - $this->get_time()) / 3600) / 24;
                    $message_termexpireday = substr($message_termexpireday, 0, 1);
                }
                if ($message_valueview == 0) {
                    $message_alldata[] = [
                        "message_id"            => $message_id,
                        "message_title"         => $message_title,
                        "message_content"       => $message_content,
                        "message_notes"         => $message_notes,
                        "message_date"          => $message_date,
                        "message_termexpire"    => $message_termexpire,
                        "message_valueview"     => $message_valueview,
                        "message_termexpireday" => $message_termexpireday,
                    ];
                } elseif ($message_valueview == 1 && ($this->is_user() || $this->is_admin())) {
                    $message_alldata[] = [
                        "message_id"            => $message_id,
                        "message_title"         => $message_title,
                        "message_content"       => $message_content,
                        "message_notes"         => $message_notes,
                        "message_date"          => $message_date,
                        "message_termexpire"    => $message_termexpire,
                        "message_valueview"     => $message_valueview,
                        "message_termexpireday" => $message_termexpireday,
                    ];
                } elseif ($message_valueview == 2 && $this->is_admin()) {
                    $message_alldata[] = [
                        "message_id"            => $message_id,
                        "message_title"         => $message_title,
                        "message_content"       => $message_content,
                        "message_notes"         => $message_notes,
                        "message_date"          => $message_date,
                        "message_termexpire"    => $message_termexpire,
                        "message_valueview"     => $message_valueview,
                        "message_termexpireday" => $message_termexpireday,
                    ];
                } elseif ($message_valueview == 3 && (!$this->is_user() || $this->is_admin())) {
                    $message_alldata[] = [
                        "message_id"            => $message_id,
                        "message_title"         => $message_title,
                        "message_content"       => $message_content,
                        "message_notes"         => $message_notes,
                        "message_date"          => $message_date,
                        "message_termexpire"    => $message_termexpire,
                        "message_valueview"     => $message_valueview,
                        "message_termexpireday" => $message_termexpireday,
                    ];
                }
                $result_messages->MoveNext();
            }
        }

        return $message_alldata;
    }


    public function block_rss($block_url, $block_refresh, $block_lastrefresh, $admin_rss_action)
    {
        if (!$admin_rss_action) {
            $past = $this->get_time() - $block_refresh;
        }
        if ($admin_rss_action || (!$admin_rss_action && $block_lastrefresh < $past)) {
            $url = @parse_url($block_url);
            $fp = @fsockopen($url['host'], 80, $errno, $errstr, 20);
            if (!$fp) {
                return ['rss_link' => 'no'];
            }
            @fputs($fp, "GET " . $url['path'] . "?" . $url['query'] . " HTTP/1.0\r\n");
            @fputs($fp, "HOST: " . $url['host'] . "\r\n\r\n");
            $line = "";
            while (!feof($fp)) {
                $text = @fgets($fp, 300);
                $line .= @chop($text);
                if (!isset($rss_encoding) && preg_match("/encoding/i", $text)) {
                    $rss_encoding = explode("ENCODING=", strtoupper($text));
                    $rss_encoding = $rss_encoding[1];
                    $rss_encoding1 = explode('"', $rss_encoding);
                    if (!isset($rss_encoding1[1])) {
                        $rss_encoding = explode("'", $rss_encoding);
                    } else {
                        $rss_encoding = $rss_encoding1;
                    }
                    $rss_encoding = $this->format_striptags($rss_encoding[1]);
                }
            }
            @fputs($fp, "Connection: close\r\n\r\n");
            @fclose($fp);
            if (!isset($rss_encoding) || $rss_encoding == "") {
                $rss_encoding = "none";
            }
            $item = explode("</item>", $line);
            if (count($item) < 2) {
                return ['rss_link' => 'no'];
            }
            $rsslink = false;
            for ($i = 0; $i < 8; $i++) {
                $item_link = @preg_replace('/.*<link>/i', '', $item[$i]);
                $item_link = @preg_replace('/<\/link>.*/i', '', $item_link);
                $item_link = @str_ireplace('<![CDATA[', '', $item_link);
                $item_link = @str_ireplace(']]>', '', $item_link);
                $item_link = @str_ireplace('&', '&amp;', $item_link);
                $item_title = @preg_replace('/.*<title>/i', '', $item[$i]);
                $item_title = @preg_replace('/<\/title>.*/i', '', $item_title);
                $item_title = @str_ireplace('<![CDATA[', '', $item_title);
                $item_title = @str_ireplace(']]>', '', $item_title);
                $item_description = @preg_replace('/.*<description>/i', '', $item[$i]);
                $item_description = @preg_replace('/<\/description>.*/i', '', $item_description);
                $item_description = @str_ireplace('<![CDATA[', '', $item_description);
                $item_description = @str_ireplace(']]>', '', $item_description);
                $item_image = @preg_replace('/\/>.*/i', '>', $item_description);
                if (strcasecmp($item_description, $item_image) == 0) {
                    $item_image = "";
                }
                $item_description = @preg_replace('/.*\/>/i', '', $item_description);
                $item_date = @preg_replace('/.*<pubDate>/i', '', $item[$i]);
                $item_date = @preg_replace('/<\/pubDate>.*/i', '', $item_date);
                $item_date = @strtotime($item_date);
                if (@$item[$i] == "" && isset($context) && $context != 1) {
                    return ['rss_link' => 'no2'];
                } else {
                    if (strcmp($item_link, $item_title) && $item[$i] != "") {
                        $context = 1;
                        $item_title = $this->encode_rss_text($item_title, $rss_encoding);
                        $item_description = $this->encode_rss_text($item_description, $rss_encoding);
                        $item_link = $this->format_striptags($item_link);
                        $item_image = $this->format_striptags($item_image, '<img>');
                        $item_date = $this->format_striptags($item_date);
                        $rsslink[$i] = [
                            "link"        => $item_link,
                            "title"       => $item_title,
                            "rssdate"     => date("d.m.y H:i", $item_date),
                            "description" => $item_description,
                            "image"       => $item_image,
                        ];
                    }
                }
            }
            if (isset($context)) {
                return ['rss_link' => $rsslink, 'context' => $context];
            }

            return ['rss_link' => $rsslink];
        }

        return false;
    }


    public function update_block_rss(array $params)
    {
        $now = $this->get_time();
        if (!empty($params['case']) && $params['case'] === 'case1') {
            $block_content = $this->processing_data($params['cont']);
            $block_content = substr(substr($block_content, 0, -1), 1);
            $sql = "UPDATE " . PREFIX . "_blocks_" . $this->registry->sitelang . " SET block_content='" . $block_content . "', block_last_refresh='" . $now
                   . "' WHERE block_id='" . $params['block_id'] . "'";
            $this->db->Execute($sql);
            $block_content = $this->extracting_data2($block_content);
            $rss_url = preg_replace("/http:\/\//i", "", $params['block_url']);
            $rss_url = explode("/", $rss_url);

            return ['rss_url' => $rss_url[0], 'block_content' => $block_content];
        } elseif (!empty($params['case']) && $params['case'] === 'case2') {
            $sql = "UPDATE " . PREFIX . "_blocks_" . $this->registry->sitelang . " SET block_content='" . $params['block_content'] . "', block_last_refresh='" . $now
                   . "' WHERE block_id='" . $params['block_id'] . "'";
            $this->db->Execute($sql);

            return true;
        } elseif (!empty($params['case']) && $params['case'] === 'case3') {
            $checkdb_needlobs = $this->check_db_need_lobs();
            if ($checkdb_needlobs == 'yes') {
                $sql = "UPDATE " . PREFIX . "_blocks_" . $this->registry->sitelang . " SET block_content=empty_clob(), block_last_refresh='" . $now . "' WHERE block_id='"
                       . $params['block_id'] . "'";
                $this->db->Execute($sql);
                $this->db->UpdateClob(PREFIX . '_blocks_' . $this->registry->sitelang, 'block_content', $params['block_content'], 'block_id=' . $params['block_id']);
            } else {
                $sql = "UPDATE " . PREFIX . "_blocks_" . $this->registry->sitelang . " SET block_content='" . $params['block_content'] . "', block_last_refresh='" . $now
                       . "' WHERE block_id='" . $params['block_id'] . "'";
                $this->db->Execute($sql);
            }

            return true;
        }

        return false;
    }
}
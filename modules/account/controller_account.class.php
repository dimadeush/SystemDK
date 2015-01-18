<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_account extends controller_base {


    private $model_account;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_account = singleton::getinstance('account',$registry);
    }


    public function process_autoload() {
        $error = false;
        if(!empty($this->registry->module_account_error)) {
            $error = $this->registry->module_account_error;
        }
        if($error === 'login' or $error === 'check_code' or $error === 'user_not_active' or $error === 'user_incorrect_pass' or $error === 'user_not_found' or $error === 'user_not_all_data') {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_MODULEACCOUNTTITLE'));
            $back_link = "user_enter";
            $this->account_view($error,$back_link);
        } elseif($error === 'empty_user_data') {
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
            } else {
                header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
            }
            exit();
        }
        $this->process_action();
    }


    private function process_action() {
        if($this->registry->main_class->is_user() and $this->registry->router->get_action() !== 'index') {
            if(in_array($this->registry->router->get_action(),array(
                "account_register",
                "account_add_save",
                "account_add_confirm",
                "account_lostpass",
                "account_lostpassconf",
                "account_newpassconf"
            ))
            ) {
                $this->registry->router->set_action('account_only_for_unreg');
            } elseif(!in_array($this->registry->router->get_action(),array(
                "mainuser",
                "account_edit",
                "account_edit_save",
                "account_logout"
            ))
            ) {
                $this->registry->router->set_action('mainuser');
            }
        } elseif($this->registry->router->get_action() !== 'index') {
            if(!in_array($this->registry->router->get_action(),array(
                "account_logout",
                "account_register",
                "account_add_save",
                "account_add_confirm",
                "account_lostpass",
                "account_lostpassconf",
                "account_newpassconf"
            ))
            ) {
                $this->registry->router->set_action('account_enter');
            }
        }
    }


    private function account_view($type,$title,$back_link = false,$cache_category = false,$cache = false,$template = false) {
        $prefix = false;
        if(empty($cache_category)) {
            $cache_category = 'modules|account|error';
            $prefix = 'error_';
        }
        if(empty($template)) {
            $template = 'account_messages.html';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        if($template === 'accountenter.html' or $template === 'account_add.html' or $template === 'account_addconf.html' or $template === 'account_lostpass.html' or $template === 'account_lostpassconf.html') {
            if(!empty($title)) {
                $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            }
            $this->configLoad($this->registry->language."/modules/account/account.conf");
            $this->assign("include_center","modules/account/".$template);
        } elseif(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            if(!empty($title)) {
                $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            }
            if(!empty($back_link)) {
                $this->assign("back_link",$back_link);
            }
            if($template === 'account_messages.html') {
                $this->assign("account_message",$prefix.$type);
            }
            $this->assign("include_center","modules/account/".$template);
        }
        $this->display("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function index() {
        if($this->registry->main_class->is_user()) {
            $this->mainuser();
        } else {
            $this->account_enter();
        }
    }


    public function account_enter() {
        $type = false;
        $title = '_MODULEACCOUNTTITLE';
        $back_link = false;
        $cache_category = 'modules|account';
        $cache = 'enter';
        $template = 'accountenter.html';
        $this->model_account->captcha();
        $result = $this->model_account->get_property_value('result');
        $this->assign_array($result);
        $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
    }


    public function account_register() {
        $title = '_MODULEACCOUNTREGISTERTITLE';
        $account_register_check = true;
        $this->model_account->account_register($account_register_check);
        $error = $this->model_account->get_property_value('error');
        if(!empty($error) and ($error === 'register_not_allow' or $error === 'max_users_limit')) {
            $this->account_view($error,$title);
        }
        $register_rules_accept = false;
        if(isset($_GET['rules']) and intval($_GET['rules']) == 1) {
            $register_rules_accept = "yes";
        }
        if(isset($_POST['register_rules_accept']) and $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($_POST['register_rules_accept']) != "") {
            $register_rules_accept = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($_POST['register_rules_accept']);
        }
        if(defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "yes" and !$register_rules_accept) {
            $type = false;
            $back_link = false;
            $cache_category = 'modules|account|register';
            $cache = 'rules';
            $template = 'account_register.html';
            if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
                $account_register_check = false;
                $this->model_account->account_register($account_register_check,$register_rules_accept);
                $error = $this->model_account->get_property_value('error');
                $result = $this->model_account->get_property_value('result');
                if(!empty($error) and $error === 'account_rules_not_found') {
                    $this->account_view($error,$title);
                }
                $this->assign_array($result);
            }
            $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
        }
        if(defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "yes" and $register_rules_accept == 'no') {
            $this->account_view('rules_not_accept',$title);
        } elseif((defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "yes" and $register_rules_accept == 'yes') or (defined('SYSTEMDK_ACCOUNT_RULES') and SYSTEMDK_ACCOUNT_RULES == "no")) {
            $type = false;
            $back_link = false;
            $cache_category = 'modules|account|register';
            $cache = 'displayform';
            $template = 'account_add.html';
            $this->model_account->captcha();
            $result = $this->model_account->get_property_value('result');
            $this->assign_array($result);
            $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
        } else {
            $this->account_view('account_rules_error',$title);
        }
    }


    public function account_add_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
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
                    'user_add_viewemail',
                    'captcha'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        $title = '_MODULEACCOUNTREGISTERTITLE';
        $this->model_account->account_add_save($data_array);
        $error = $this->model_account->get_property_value('error');
        $error_array = $this->model_account->get_property_value('error_array');
        $result = $this->model_account->get_property_value('result');
        if(isset($result['need_send_email']) and $result['need_send_email'] === 'yes') {
            if(isset($result['insert_result']) and $result['insert_result']) {
                $this->configLoad($this->registry->language."/modules/account/account.conf");
                $this->assign_array($result);
                $modules_account_regcontent = $this->fetch("modules/account/account_regmail.html");
                $mail_data = array(
                    'content' => $modules_account_regcontent,
                    'title' => $this->getConfigVars('_ACCOUNTACTIVATIONMAILTITLE')
                );
            } else {
                $mail_data = 'not_send';
            }
            $this->model_account->account_add_save(false,$mail_data);
            $error = $this->model_account->get_property_value('error');
            $error_array = $this->model_account->get_property_value('error_array');
        }
        if(!empty($error) and ($error === 'reg_not_all_data' or $error === 'register_not_allow' or $error === 'max_users_limit' or $error === 'sql_error')) {
            if(!empty($error_array) and is_array($error_array)) {
                $this->assign("errortext",$error_array);
            }
            $this->account_view($error,$title);
        } elseif(!empty($error) and ($error === 'reg_check_code' or $error === 'reg_save_user_email' or $error === 'reg_login' or $error === 'reg_pass' or $error === 'reg_found_user')) {
            $back_link = 'user_reg_form';
            $this->account_view($error,$title,$back_link);
        }
        $this->assign_array($result);
        $back_link = false;
        $cache_category = 'modules|account|register';
        $this->account_view($result['account_message'],$title,$back_link,$cache_category);
    }


    public function account_add_confirm() {
        $data_array = false;
        if(isset($_POST['modules_account_confcode'])) {
            $data_array['modules_account_confcode'] = trim($_POST['modules_account_confcode']);
        }
        if(isset($_GET['act_code'])) {
            $data_array['act_code'] = trim($_GET['act_code']);
        }
        if(isset($_POST["captcha"])) {
            $data_array['captcha'] = trim($_POST["captcha"]);
        }
        $type = false;
        $title = '_MODULEACCOUNTREGISTERCONFTITLE';
        $back_link = false;
        $cache_category = 'modules|account|register';
        $this->model_account->account_add_confirm($data_array);
        $error = $this->model_account->get_property_value('error');
        $error_array = $this->model_account->get_property_value('error_array');
        $result = $this->model_account->get_property_value('result');
        if($error === 'conf_reg_check_code' or $error === 'no_act_code' or $error === 'add_conf_sql_error') {
            if($error === 'conf_reg_check_code') {
                $back_link = 'conf_user_reg';
            }
            if(!empty($error_array) and is_array($error_array)) {
                $this->assign("errortext",$error_array);
            }
            $this->account_view($error,$title,$back_link);
        } elseif($result['account_message'] === 'add_ok') {
            $this->account_view($result['account_message'],$title,$back_link,$cache_category);
        }
        $this->assign_array($result);
        $cache = $result['account_message'];
        $template = 'account_addconf.html';
        $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
    }


    public function account_lostpass() {
        $type = false;
        $title = '_MODULEACCOUNTLOSTPASSTITLE';
        $back_link = false;
        $cache_category = 'modules|account';
        $cache = 'lostpass';
        $template = 'account_lostpass.html';
        $this->model_account->captcha();
        $result = $this->model_account->get_property_value('result');
        $this->assign_array($result);
        $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
    }


    public function account_lostpassconf() {
        $data_array = false;
        if(isset($_POST['lost_loginoremail'])) {
            $data_array['lost_loginoremail'] = trim($_POST['lost_loginoremail']);
        }
        if(isset($_POST['captcha'])) {
            $data_array['captcha'] = trim($_POST['captcha']);
        }
        $title = '_MODULEACCOUNTLOSTPASSTITLE';
        $back_link = false;
        $this->model_account->account_lostpassconf($data_array);
        $error = $this->model_account->get_property_value('error');
        $error_array = $this->model_account->get_property_value('error_array');
        $result = $this->model_account->get_property_value('result');
        if(!empty($result['need_send_email']) and $result['need_send_email'] === 'yes') {
            if($result['update_result']) {
                $this->configLoad($this->registry->language."/modules/account/account.conf");
                $this->assign_array($result);
                $modules_account_content = $this->fetch("modules/account/account_lostpassmail.html");
                $mail_data = array(
                    'content' => $modules_account_content,
                    'title' => $this->getConfigVars('_ACCOUNTLOSTPASSMAILTITLE')
                );
            } else {
                $mail_data = 'not_send';
            }
            $this->model_account->account_lostpassconf(false,$mail_data);
            $error = $this->model_account->get_property_value('error');
            $error_array = $this->model_account->get_property_value('error_array');
            $result = $this->model_account->get_property_value('result');
        }
        if($error === 'pass_not_all_data' or $error === 'pass_check_code' or $error === 'pass_not_found' or $error === 'lost_pass_sql_error' or $error === 'lost_pass_not_update') {
            if(!empty($error_array) and is_array($error_array)) {
                $this->assign("errortext",$error_array);
            }
            if($error === 'pass_check_code') {
                $back_link = 'user_lost_pass';
            } elseif($error === 'pass_not_found' or $error === 'lost_pass_not_update') {
                $back_link = 'lost_pass';
            }
            $this->account_view($error,$title,$back_link);
        }
        $cache_category = 'modules|account|lost_pass';
        $this->account_view($result['account_message'],$title,$back_link,$cache_category);
    }


    public function account_newpassconf() {
        $data_array = false;
        if(isset($_POST['modules_account_confcode'])) {
            $data_array['modules_account_confcode'] = trim($_POST['modules_account_confcode']);
        }
        if(isset($_GET['act_code'])) {
            $data_array['act_code'] = trim($_GET['act_code']);
        }
        if(isset($_POST['captcha'])) {
            $data_array['captcha'] = trim($_POST['captcha']);
        }
        $title = '_MODULEACCOUNTLOSTPASSTITLE';
        $back_link = false;
        $this->model_account->account_newpassconf($data_array);
        $error = $this->model_account->get_property_value('error');
        $error_array = $this->model_account->get_property_value('error_array');
        $result = $this->model_account->get_property_value('result');
        if($error === 'new_pass_conf_check_code' or $error === 'new_pass_conf_no_act_code' or $error === 'lost_pass_sql_error' or $error === 'new_pass_conf_not_update') {
            if(!empty($error_array) and is_array($error_array)) {
                $this->assign("errortext",$error_array);
            }
            if($error === 'new_pass_conf_check_code' or $error === 'new_pass_conf_not_update') {
                $back_link = 'conf_user_reg';
            }
            $this->account_view($error,$title,$back_link);
        } elseif(!empty($result['account_message']) and $result['account_message'] === 'new_pass_conf_ok') {
            $cache_category = 'new_pass';
            $this->account_view($result['account_message'],$title,$back_link,$cache_category);
        }
        $this->assign_array($result);
        $type = false;
        $cache_category = 'modules|account';
        $cache = 'lostpassconf';
        $template = 'account_lostpassconf.html';
        $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
    }


    public function mainuser($edit = false) {
        $check = true;
        $this->model_account->mainuser($check);
        $error = $this->model_account->get_property_value('error');
        if($error === 'empty_user_id') {
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR.$this->registry->sitelang."/account/");
            } else {
                header("Location: /".SITE_DIR."index.php?path=account&lang=".$this->registry->sitelang);
            }
            exit();
        }
        $type = false;
        $back_link = false;
        if($edit) {
            $title = '_MODULEACCOUNTEDITTITLE';
            $group = 'edit';
            $template = 'user_edit.html';
        } else {
            $title = '_MODULEACCOUNTTITLE';
            $group = 'show';
            $template = 'user.html';
        }
        $cache_category = 'modules|account|'.$group;
        $cache = intval($this->registry->main_class->get_user_id());
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_account->mainuser();
            $error = $this->model_account->get_property_value('error');
            $result = $this->model_account->get_property_value('result');
            if($error === 'main_user_not_found') {
                if($edit) {
                    $error = 'edit_user_not_found';
                }
                $this->account_view($error,$title);
            }
            $this->assign_array($result);
        }
        $this->account_view($type,$title,$back_link,$cache_category,$cache,$template);
    }


    public function account_edit() {
        $edit = true;
        $this->mainuser($edit);
    }


    public function account_edit_save() {
        $check = true;
        $data_array = false;
        $this->model_account->account_edit_save($data_array,$check);
        $error = $this->model_account->get_property_value('error');
        if($error === 'empty_user_id') {
            if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR.$this->registry->sitelang."/account/");
            } else {
                header("Location: /".SITE_DIR."index.php?path=account&lang=".$this->registry->sitelang);
            }
            exit();
        }
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
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
                    'user_edit_viewemail'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        $title = '_MODULEACCOUNTEDITTITLE';
        $back_link = false;
        $cache_category = 'modules|account|save';
        $this->model_account->account_edit_save($data_array);
        $error = $this->model_account->get_property_value('error');
        $error_array = $this->model_account->get_property_value('error_array');
        $result = $this->model_account->get_property_value('result');
        if($error === 'edit_not_all_data' or $error === 'edit_email' or $error === 'edit_pass' or $error === 'find_such_user' or $error === 'edit_sql_error' or $error === 'edit_not_updated') {
            if(!empty($error_array) and is_array($error_array)) {
                $this->assign("errortext",$error_array);
            }
            $this->account_view($error,$title);
        }
        $this->account_view($result,$title,$back_link,$cache_category);
    }


    public function account_logout() {
        $type = 'user_exit';
        $title = '_MODULEACCOUNTTITLE';
        $back_link = false;
        $cache_category = 'modules|account|logout';
        $this->model_account->account_logout();
        $this->account_view($type,$title,$back_link,$cache_category);
    }


    public function account_only_for_unreg() {
        $error = 'only_for_unreg';
        $title = '_MODULEACCOUNTTITLE';
        $this->account_view($error,$title);
    }
}
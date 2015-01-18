<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_administrator.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_admin_administrator extends controller_base {


    private $model_admin_administrator;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_administrator = singleton::getinstance('admin_administrator',$registry);
    }


    private function administrator_view($type,$cache_category = false) {
        if(empty($cache_category)) {
            $cache_category = 'error';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->assign("admin_edit",$type);
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/admin_update.html");
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function mainenter() {
        $this->configLoad($this->registry->language."/systemadmin/enter.conf");
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->main_class->set_sitemeta($this->getConfigVars('_ADMINENTERFORM'));
        $this->model_admin_administrator->mainenter();
        $result = $this->model_admin_administrator->get_property_value('result');
        $this->assign_array($result);
        $this->display("systemadmin/enter.html",$this->registry->sitelang."|systemadmin|enter|".$this->registry->language);
    }


    public function enter_error() {
        $error = $this->registry->module_account_error;
        if($error === 'login' or $error === 'check_code' or $error === 'user_not_active' or $error === 'user_incorrect_pass' or $error === 'user_not_found' or $error === 'user_not_all_data') {
            $this->configLoad($this->registry->language."/systemadmin/enter.conf");
            $this->assign("error","error_".$error);
            $this->registry->controller_theme->display_theme_adminheader(true);
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_ADMINENTERFORM'));
            $this->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|".$error."|".$this->registry->language);
            exit();
        } elseif($error === 'empty_user_data') {
            /*if(SYSTEMDK_MODREWRITE === 'yes') {
                header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
            } else {*/
                header("Location: /".SITE_DIR.ADMIN_DIR."index.php?lang=".$this->registry->sitelang);
            //}
            exit();
        }
    }


    public function index() {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|show|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_MENUADMINISTRATION'));
            $this->model_admin_administrator->index();
            $result = $this->model_admin_administrator->get_property_value('result');
            $this->assign_array($result);
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/systeminfo.html");
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|show|".$this->registry->language);
    }


    public function mainadmin() {
        $this->index();
    }


    public function mainadmin_edit() {
        $admin_id = intval($this->registry->main_class->get_user_id());
        if(intval($admin_id) == 0) {
            header("Location: index.php&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        $this->registry->main_class->set_sitemeta($this->getConfigVars('_ADMINISTRATIONEDITPROFILEPAGETITLE'));
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|".$admin_id."|".$this->registry->language)) {
            $this->model_admin_administrator->mainadmin_edit();
            $error = $this->model_admin_administrator->get_property_value('error');
            $error_array = $this->model_admin_administrator->get_property_value('error_array');
            $result = $this->model_admin_administrator->get_property_value('result');
            if($error === 'admin_id' or $error === 'sql_error' or $error === 'not_found') {
                if($error === 'admin_id') {
                    header("Location: index.php&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->administrator_view($error);
            }
            $this->assign_array($result);
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/admin_edit.html");
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|".$admin_id."|".$this->registry->language);
    }


    public function mainadmin_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
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
                    'admin_edit_notes'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if(isset($_POST['admin_edit_viewemail'])) {
            $data_array['admin_edit_viewemail'] = intval($_POST['admin_edit_viewemail']);
        }
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        $this->registry->main_class->set_sitemeta($this->getConfigVars('_ADMINISTRATIONEDITPROFILEPAGETITLE'));
        $this->model_admin_administrator->mainadmin_edit_inbase($data_array);
        $error = $this->model_admin_administrator->get_property_value('error');
        $error_array = $this->model_admin_administrator->get_property_value('error_array');
        $result = $this->model_admin_administrator->get_property_value('result');
        if($error === 'admin_id' or $error === 'not_all_data' or $error === 'incorrect_email' or $error === 'add_error_pass' or $error === 'find_such_admin' or $error === 'sql_error' or $error === 'not_done') {
            if($error === 'admin_id') {
                header("Location: index.php&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->administrator_view($error);
        }
        $cache_category = 'edit';
        $this->administrator_view($result,$cache_category);
    }


    public function logout() {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->model_admin_administrator->logout();
        $this->display("systemadmin/exit.html",$this->registry->sitelang."|systemadmin|exit|".$this->registry->language);
    }
}
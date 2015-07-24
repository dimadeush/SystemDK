<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_users.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class controller_admin_users extends controller_base {


    private $model_admin_users;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_users = singleton::getinstance('admin_users',$registry);
    }


    private function users_view($type,$cache_category = false,$template = false,$title,$temp = false) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|users|error';
        }
        if(empty($template)) {
            $template = 'userupdate.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'userupdate.html') {
                $this->assign("user_update",$type);
            } elseif($template === 'users.html') {
                if($temp != "temp") {
                    $this->assign("adminmodules_users_temp","no");
                } else {
                    $this->assign("adminmodules_users_temp","yes");
                }
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/users/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        if(isset($_GET['temp']) and intval($_GET['temp']) == '1') {
            $temp = "temp";
        } else {
            $temp = "";
        }
        $type = 'show'.$temp.'|'.$num_page;
        $cache_category = 'modules|users';
        if($temp != "temp") {
            $title = '_MENUUSERS';
        } else {
            $title = '_ADMINISTRATIONTEMPUSERSPAGETITLE';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_users->index($num_page,$temp);
            $error = $this->model_admin_users->get_property_value('error');
            $error_array = $this->model_admin_users->get_property_value('error_array');
            $result = $this->model_admin_users->get_property_value('result');
            if($error === 'unknown_page' or $error === 'show_sql_error') {
                if($error === 'unknown_page') {
                    if($temp != "temp") {
                        header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_users&func=index&temp=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->users_view($error.$temp,false,false,$title,$temp);
            }
            $this->assign_array($result);
        }
        $template = 'users.html';
        $this->users_view($type,$cache_category,$template,$title,$temp);
    }


    public function user_add() {
        $this->model_admin_users->user_add();
        $title = '_ADMINISTRATIONADDUSERPAGETITLE';
        $type = 'add';
        $cache_category = 'modules|users';
        $template = 'useradd.html';
        $this->users_view($type,$cache_category,$template,$title);
    }


    public function user_add_inbase() {
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
                    'user_add_activenotes'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if(isset($_POST['user_add_active'])) {
            $data_array['user_add_active'] = intval($_POST['user_add_active']);
        }
        if(isset($_POST['user_add_viewemail'])) {
            $data_array['user_add_viewemail'] = intval($_POST['user_add_viewemail']);
        }
        $title = '_ADMINISTRATIONADDUSERPAGETITLE';
        $this->model_admin_users->user_add_inbase($data_array);
        $error = $this->model_admin_users->get_property_value('error');
        $error_array = $this->model_admin_users->get_property_value('error_array');
        $result = $this->model_admin_users->get_property_value('result');
        if($error === 'add_not_all_data' or $error === 'add_incorrect_email' or $error === 'add_login' or $error === 'add_pass' or $error === 'add_found' or $error === 'add_sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->users_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|users|add';
        $template = false;
        $this->users_view($result,$cache_category,$template,$title);
    }


    public function user_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if(isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if(isset($_GET['user_id'])) {
            $data_array['get_user_id'] = intval($_GET['user_id']);
        }
        if(isset($_POST['user_id'])) {
            $data_array['post_user_id'] = $_POST['user_id'];
        }
        $title = '_MENUUSERS';
        $this->model_admin_users->user_status($data_array);
        $error = $this->model_admin_users->get_property_value('error');
        $error_array = $this->model_admin_users->get_property_value('error_array');
        $result = $this->model_admin_users->get_property_value('result');
        if($error === 'empty_data' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'empty_data') {
                header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->users_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|users|'.$result;
        $template = false;
        $this->users_view($result,$cache_category,$template,$title);
    }


    public function user_edit() {
        $data_array = false;
        if(isset($_GET['user_id'])) {
            $data_array['user_id'] = intval($_GET['user_id']);
        }
        if(!isset($data_array['user_id']) or $data_array['user_id'] == 0) {
            header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $title = '_ADMINISTRATIONEDITUSERPAGETITLE';
        $type = $data_array['user_id'];
        $cache_category = 'modules|users|edit';
        $template = 'useredit.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_users->user_edit($data_array);
            $error = $this->model_admin_users->get_property_value('error');
            $error_array = $this->model_admin_users->get_property_value('error_array');
            $result = $this->model_admin_users->get_property_value('result');
            if($error === 'empty_data' or $error === 'edit_sql_error' or $error === 'not_found') {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_users&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->users_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $this->users_view($type,$cache_category,$template,$title);
    }


    public function user_edit_inbase() {
        $post_array = false;
        if(isset($_POST['user_edit_id'])) {
            $post_array['user_edit_id'] = intval($_POST['user_edit_id']);
        }
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'user_edit_name',
                    'user_edit_surname',
                    'user_edit_website',
                    'user_edit_email',
                    'user_edit_login',
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
                    'user_edit_activenotes'
                );
                if(in_array($key,$keys)) {
                    $post_array[$key] = trim($value);
                }
            }
        }
        if(isset($_POST['user_edit_active'])) {
            $post_array['user_edit_active'] = intval($_POST['user_edit_active']);
        }
        if(isset($_POST['user_edit_viewemail'])) {
            $post_array['user_edit_viewemail'] = intval($_POST['user_edit_viewemail']);
        }
        $title = '_ADMINISTRATIONEDITUSERPAGETITLE';
        $this->model_admin_users->user_edit_inbase($post_array);
        $error = $this->model_admin_users->get_property_value('error');
        $error_array = $this->model_admin_users->get_property_value('error_array');
        $result = $this->model_admin_users->get_property_value('result');
        if($error === 'edit_not_all_data' or $error === 'edit_incorrect_email' or $error === 'edit_login' or $error === 'edit_pass' or $error === 'edit_found' or $error === 'edit_sql_error' or $error === 'edit_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->users_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|users|edit|'.$result;
        $template = false;
        $this->users_view($result,$cache_category,$template,$title);
    }
}
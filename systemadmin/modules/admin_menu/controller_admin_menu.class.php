<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class controller_admin_menu extends controller_base {


    private $model_admin_menu;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_menu = singleton::getinstance('admin_menu',$registry);
    }


    private function menu_view($type,$cache_category = false,$template = false,$title) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|menu|error';
        }
        if(empty($template)) {
            $template = 'menuupdate.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'menuupdate.html') {
                $this->assign("menu_update",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/menu/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        $title = '_ADMINBLOCKMAINNAME';
        $type = 'show';
        $cache_category = 'modules|menu';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_menu->index();
            $error = $this->model_admin_menu->get_property_value('error');
            $error_array = $this->model_admin_menu->get_property_value('error_array');
            $result = $this->model_admin_menu->get_property_value('result');
            if($error === 'show_sql_error') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->menu_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'menu.html';
        $this->menu_view($type,$cache_category,$template,$title);
    }


    public function menu_save() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'mainmenu_id2',
                    'mainmenu_priority',
                    'mainmenu_priority2',
                    'mainmenu_id'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINBLOCKMAINNAME';
        $template = false;
        $this->model_admin_menu->menu_save($data_array);
        $error = $this->model_admin_menu->get_property_value('error');
        $error_array = $this->model_admin_menu->get_property_value('error_array');
        $result = $this->model_admin_menu->get_property_value('result');
        if($error === 'not_all_data' or $error === 'show_sql_error' or $error === 'save_ok') {
            if($error === 'not_all_data') {
                header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->menu_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|menu|range';
        $this->menu_view($result,$cache_category,$template,$title);
    }


    public function menu_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['action'] = trim($_GET['action']);
        }
        if(isset($_GET['menu_id'])) {
            $data_array['menu_id'] = intval($_GET['menu_id']);
        }
        $title = '_ADMINBLOCKMAINNAME';
        $template = false;
        $this->model_admin_menu->menu_status($data_array);
        $error = $this->model_admin_menu->get_property_value('error');
        $error_array = $this->model_admin_menu->get_property_value('error_array');
        $result = $this->model_admin_menu->get_property_value('result');
        if($error === 'not_all_data' or $error === 'unknown_action' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'not_all_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->menu_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|menu|status';
        $this->menu_view($result,$cache_category,$template,$title);
    }


    public function menu_select_type() {
        $title = '_ADMINISTRATIONADDMENUPAGETITLE';
        $type = 'select';
        $cache_category = 'modules|menu';
        $template = 'menuadd.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_menu->menu_select_type();
            $result = $this->model_admin_menu->get_property_value('result');
            $this->assign_array($result);
        }
        $this->menu_view($type,$cache_category,$template,$title);
    }


    public function menu_add() {
        $menu_type = false;
        if(isset($_POST['menu_type'])) {
            $menu_type = trim($_POST['menu_type']);
        }
        $title = '_ADMINISTRATIONADDMENUPAGETITLE';
        $this->model_admin_menu->menu_add($menu_type);
        $error = $this->model_admin_menu->get_property_value('error');
        $error_array = $this->model_admin_menu->get_property_value('error_array');
        $result = $this->model_admin_menu->get_property_value('result');
        if($error === 'not_all_data' or $error === 'add_sql_error' or $error === 'add_no_modules' or $error === 'add_no_pages' or $error === 'unknown_menu_type') {
            if($error === 'not_all_data' or $error === 'unknown_menu_type') {
                header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->menu_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $cache_category = 'modules|menu|add';
        $template = 'menuadd.html';
        $this->menu_view($menu_type,$cache_category,$template,$title);
    }


    public function menu_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'mainmenu_name',
                    'menu_object',
                    'menu_type',
                    'mainmenu_module',
                    'mainmenu_target'
                );
                $keys2 = array(
                    'mainmenu_inmenu'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONADDMENUPAGETITLE';
        $template = false;
        $this->model_admin_menu->menu_add_inbase($data_array);
        $error = $this->model_admin_menu->get_property_value('error');
        $error_array = $this->model_admin_menu->get_property_value('error_array');
        $result = $this->model_admin_menu->get_property_value('result');
        if($error === 'add_not_all_data' or $error === 'add_sql_error' or $error === 'unknown_menu_object') {
            if($error === 'unknown_menu_object') {
                header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->menu_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|menu';
        $this->menu_view($result,$cache_category,$template,$title);
    }


    public function menu_edit() {
        $data_array = false;
        if(isset($_GET['menu_type'])) {
            $data_array['menu_type'] = trim($_GET['menu_type']);
        }
        if(isset($_GET['menu_id'])) {
            $data_array['menu_id'] = intval($_GET['menu_id']);
        }
        $title = '_ADMINISTRATIONEDITMENUPAGETITLE';
        $this->model_admin_menu->menu_edit($data_array);
        $error = $this->model_admin_menu->get_property_value('error');
        $error_array = $this->model_admin_menu->get_property_value('error_array');
        $result = $this->model_admin_menu->get_property_value('result');
        if($error === 'edit_not_all_data' or $error === 'edit_not_found' or $error === 'edit_sql_error' or $error === 'edit_no_modules' or $error === 'edit_no_pages' or $error === 'unknown_menu_type') {
            if($error === 'edit_not_all_data' or $error === 'unknown_menu_type') {
                header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->menu_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $cache_category = 'modules|menu|edit|'.$result['menu_type'];
        $template = 'menuedit.html';
        $this->menu_view($data_array['menu_id'],$cache_category,$template,$title);
    }


    public function menu_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'mainmenu_name',
                    'menu_object',
                    'menu_type',
                    'mainmenu_module',
                    'mainmenu_target'
                );
                $keys2 = array(
                    'mainmenu_id',
                    'mainmenu_inmenu'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONEDITMENUPAGETITLE';
        $template = false;
        $this->model_admin_menu->menu_edit_inbase($data_array);
        $error = $this->model_admin_menu->get_property_value('error');
        $error_array = $this->model_admin_menu->get_property_value('error_array');
        $result = $this->model_admin_menu->get_property_value('result');
        if($error === 'edit_not_all_data' or $error === 'unknown_menu_object' or $error === 'edit_sql_error' or $error === 'edit_ok') {
            if($error === 'unknown_menu_object') {
                header("Location: index.php?path=admin_menu&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->menu_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|menu';
        $this->menu_view($result,$cache_category,$template,$title);
    }
}
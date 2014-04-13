<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class controller_admin_news extends controller_base {


    private $model_admin_news;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_news = singleton::getinstance('admin_news',$registry);
    }


    private function news_view($type,$cache_category = false,$template = false,$title) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|news|error';
        }
        if(empty($template)) {
            $template = 'news_update.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'news_update.html') {
                $this->assign("news_update",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/news/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        $title = '_ADMINISTRATIONNEWSPAGETITLE';
        $this->model_admin_news->index();
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $cache_category = 'modules|news';
        $template = 'news.html';
        $this->news_view('show',$cache_category,$template,$title);
    }


    public function news_current() {
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $data_array['prog'] = intval($_GET['prog']);
            $temp = "program";
        } else {
            $temp = "current";
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if($temp != "program") {
            $title = '_ADMINISTRATIONCURRNEWSPAGETITLE';
        } else {
            $title = '_ADMINISTRATIONPROGNEWSPAGETITLE';
        }
        $cache_category = 'modules|news|show'.$temp;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_news->news_current($data_array);
            $error = $this->model_admin_news->get_property_value('error');
            $error_array = $this->model_admin_news->get_property_value('error_array');
            $result = $this->model_admin_news->get_property_value('result');
            if($error === 'unknown_page' or $error === 'show_sql_error_'.$temp) {
                if($error === 'unknown_page') {
                    if($temp != "program") {
                        header("Location: index.php?path=admin_news&func=news_current&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_news&func=news_current&prog=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->news_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'news_current.html';
        $this->news_view($data_array['num_page'],$cache_category,$template,$title);
    }


    public function news_edit() {
        $data_array['news_id'] = 0;
        if(isset($_GET['news_id'])) {
            $data_array['news_id'] = intval($_GET['news_id']);
        }
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $data_array['prog'] = intval($_GET['prog']);
            $temp = "program";
        } else {
            $temp = "";
        }
        if($temp != "program") {
            $title = '_ADMINISTRATIONEDITCURRNEWSPAGETITLE';
        } else {
            $title = '_ADMINISTRATIONEDITPROGNEWSPAGETITLE';
        }
        $cache_category = 'modules|news|edit'.$temp;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['news_id']."|".$this->registry->language)) {
            $this->model_admin_news->news_edit($data_array);
            $error = $this->model_admin_news->get_property_value('error');
            $error_array = $this->model_admin_news->get_property_value('error_array');
            $result = $this->model_admin_news->get_property_value('result');
            if($error === 'empty_data' or $error === 'edit_sql_error'.$temp or $error === 'edit_not_found'.$temp) {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->news_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'news_edit.html';
        $this->news_view($data_array['news_id'],$cache_category,$template,$title);
    }


    public function news_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'news_author',
                    'news_title',
                    'news_newdate',
                    'delete_picture',
                    'news_short_text',
                    'news_content',
                    'news_notes',
                    'news_termexpireaction',
                    'news_link',
                    'news_images'
                );
                $keys2 = array(
                    'news_id',
                    'news_category_id',
                    'prog',
                    'news_readcounter',
                    'news_valueview',
                    'news_status',
                    'news_termexpire',
                    'news_onhome',
                    'news_hour',
                    'news_minute'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($_POST['news_date'])) {
            if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
                $data_array['news_date'] = trim($_POST['news_date']);
            } else {
                $data_array['news_date'] = intval($_POST['news_date']);
            }
        }
        if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
            $temp = "program";
        } else {
            $temp = "";
        }
        if($temp != "program") {
            $title = '_ADMINISTRATIONEDITCURRNEWSPAGETITLE';
        } else {
            $title = '_ADMINISTRATIONEDITPROGNEWSPAGETITLE';
        }
        $template = false;
        $this->model_admin_news->news_edit_inbase($data_array);
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'edit_not_all_data'.$temp or $error === 'edit_max_size'.$temp or $error === 'edit_not_upload'.$temp or $error === 'edit_not_eq'.$temp or $error === 'edit_not_eq_type'.$temp or $error === 'edit_file'.$temp or $error === 'edit_sql_error'.$temp or $error === 'edit_ok'.$temp) {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|news';
        $this->news_view($result,$cache_category,$template,$title);
    }


    public function news_add() {
        $data_array = false;
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $data_array['prog'] = intval($_GET['prog']);
            $temp = "program";
        } else {
            $temp = "";
        }
        if($temp != "program") {
            $title = '_ADMINISTRATIONADDCURRNEWSPAGETITLE';
        } else {
            $title = '_ADMINISTRATIONADDPROGNEWSPAGETITLE';
        }
        $type = 'add';
        $cache_category = 'modules|news'.$temp;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_news->news_add($data_array);
            $error = $this->model_admin_news->get_property_value('error');
            $error_array = $this->model_admin_news->get_property_value('error_array');
            $result = $this->model_admin_news->get_property_value('result');
            if($error === 'add_sql_error'.$temp) {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->news_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'news_add.html';
        $this->news_view($type,$cache_category,$template,$title);
    }


    public function news_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'news_title',
                    'news_short_text',
                    'news_content',
                    'news_notes',
                    'news_termexpireaction',
                    'news_link',
                    'news_date'
                );
                $keys2 = array(
                    'news_category_id',
                    'news_valueview',
                    'news_status',
                    'news_termexpire',
                    'news_onhome',
                    'prog',
                    'news_hour',
                    'news_minute'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
            $temp = "program";
        } else {
            $temp = "";
        }
        if($temp != "program") {
            $title = '_ADMINISTRATIONADDCURRNEWSPAGETITLE';
        } else {
            $title = '_ADMINISTRATIONADDPROGNEWSPAGETITLE';
        }
        $template = false;
        $this->model_admin_news->news_add_inbase($data_array);
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'add_not_all_data'.$temp or $error === 'add_max_size'.$temp or $error === 'add_not_upload'.$temp or $error === 'add_not_eq'.$temp or $error === 'add_not_eq_type'.$temp or $error === 'add_file'.$temp or $error === 'add_sql_error'.$temp) {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|news';
        $this->news_view($result,$cache_category,$template,$title);
    }


    public function news_category() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $title = '_ADMINISTRATIONCATNEWSPAGETITLE';
        $cache_category = 'modules|news|showcat';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$num_page."|".$this->registry->language)) {
            $this->model_admin_news->news_category($num_page);
            $error = $this->model_admin_news->get_property_value('error');
            $error_array = $this->model_admin_news->get_property_value('error_array');
            $result = $this->model_admin_news->get_property_value('result');
            if($error === 'unknown_page' or $error === 'show_cat_sql_error') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_news&func=news_category&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->news_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'news_categories.html';
        $this->news_view($num_page,$cache_category,$template,$title);
    }


    public function category_add() {
        $title = '_ADMINISTRATIONADDCATNEWSPAGETITLE';
        $type = 'addcat';
        $cache_category = 'modules|news';
        $template = 'category_add.html';
        $this->news_view($type,$cache_category,$template,$title);
    }


    public function category_add_inbase() {
        $category_name = false;
        if(isset($_POST['category_name'])) {
            $category_name = trim($_POST['category_name']);
        }
        $title = '_ADMINISTRATIONADDCATNEWSPAGETITLE';
        $template = false;
        $this->model_admin_news->category_add_inbase($category_name);
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'add_cat_not_all_data' or $error === 'add_cat_sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|news';
        $this->news_view($result,$cache_category,$template,$title);
    }


    public function category_edit() {
        $category_id = 0;
        if(isset($_GET['category_id'])) {
            $category_id = intval($_GET['category_id']);
        }
        $title = '_ADMINISTRATIONEDITCATNEWSPAGETITLE';
        $cache_category = 'modules|news|editcat';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$category_id."|".$this->registry->language)) {
            $this->model_admin_news->category_edit($category_id);
            $error = $this->model_admin_news->get_property_value('error');
            $error_array = $this->model_admin_news->get_property_value('error_array');
            $result = $this->model_admin_news->get_property_value('result');
            if($error === 'empty_data' or $error === 'edit_cat_not_found' or $error === 'edit_cat_sql_error') {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_news&func=news_category&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->news_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'category_edit.html';
        $this->news_view($category_id,$cache_category,$template,$title);
    }


    public function category_edit_inbase() {
        $data_array = false;
        if(isset($_POST['category_name'])) {
            $data_array['category_name'] = trim($_POST['category_name']);
        }
        if(isset($_POST['category_id'])) {
            $data_array['category_id'] = intval($_POST['category_id']);
        }
        $title = '_ADMINISTRATIONEDITCATNEWSPAGETITLE';
        $template = false;
        $this->model_admin_news->category_edit_inbase($data_array);
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'edit_cat_not_all_data' or $error === 'edit_cat_sql_error' or $error === 'edit_cat_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|news';
        $this->news_view($result,$cache_category,$template,$title);
    }


    public function category_delete() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'category_id',
                    'conf'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONDELCATNEWSPAGETITLE';
        $template = false;
        $this->model_admin_news->category_delete($data_array);
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'del_cat_not_all_data' or $error === 'del_cat_sql_error' or $error === 'del_cat_not_found' or $error === 'del_cat_ok') {
            if($error === 'del_cat_not_all_data') {
                header("Location: index.php?path=admin_news&func=news_category&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $cache_category = 'modules|news';
        if($result['news_update'] === 'delete_cat_conf') {
            $cache_category = 'modules|news|'.$data_array['category_id'];
        }
        $this->news_view($result['news_update'],$cache_category,$template,$title);
    }


    public function news_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if(isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if(isset($_GET['news_id'])) {
            $data_array['get_news_id'] = intval($_GET['news_id']);
        }
        if(isset($_POST['news_id'])) {
            $data_array['post_news_id'] = $_POST['news_id'];
        }
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $data_array['prog'] = intval($_GET['prog']);
        }
        if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
            $data_array['prog'] = intval($_POST['prog']);
        }
        $title = '_ADMINISTRATIONNEWSPAGETITLE';
        $template = false;
        $this->model_admin_news->news_status($data_array);
        $error = $this->model_admin_news->get_property_value('error');
        $error_array = $this->model_admin_news->get_property_value('error_array');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'empty_data' or $error === 'unknown_action' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'empty_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|news|status';
        $this->news_view($result,$cache_category,$template,$title);
    }


    public function news_config() {
        $title = '_ADMINISTRATIONCONFNEWSPAGETITLE';
        $type = 'config';
        $cache_category = 'modules|news';
        $template = 'news_config.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_news->news_config();
            $result = $this->model_admin_news->get_property_value('result');
            $this->assign_array($result);
        }
        $this->news_view($type,$cache_category,$template,$title);
    }


    public function news_config_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'systemdk_news_image_path',
                    'systemdk_news_image_position'
                );
                $keys2 = array(
                    'systemdk_news_num_news',
                    'systemdk_news_image_max_size',
                    'systemdk_news_image_max_width',
                    'systemdk_news_image_max_height',
                    'systemdk_news_image_width',
                    'systemdk_news_image_width2'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONCONFNEWSPAGETITLE';
        $template = false;
        $this->model_admin_news->news_config_save($data_array);
        $error = $this->model_admin_news->get_property_value('error');
        $result = $this->model_admin_news->get_property_value('result');
        if($error === 'conf_not_all_data' or $error === 'conf_no_file' or $error === 'conf_no_write') {
            $cache_category = false;
            $this->news_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|news';
        $this->news_view($result,$cache_category,$template,$title);
    }
}
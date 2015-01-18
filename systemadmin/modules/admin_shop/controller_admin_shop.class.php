<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_admin_shop extends controller_base {


    private $model_admin_shop;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_shop = singleton::getinstance('admin_shop',$registry);
    }


    private function shop_view($type,$title,$cache_category = false,$template = false,$cache = false) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|shop|error';
        }
        if(empty($template)) {
            $template = 'shop_messages.html';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->configLoad($this->registry->language."/systemadmin/modules/shop/shop.conf");
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'shop_messages.html') {
                $this->assign("shop_message",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/shop/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function index() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'cat',
                    'categ',
                    'num_page'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(!isset($data_array['cat']) and !isset($data_array['categ'])) {
            $title = '_SHOPHOMETITLE';
            $cache_type = "home";
            $cache = "home";
        } elseif(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['categ']) and $data_array['categ'] != 0) {
                $title = '_SHOPSUBCATEGORIESTITLE';
                $cache_type = "subcateg";
                $cache = "cat".$data_array['cat']."|categ".$data_array['categ'];
            } else {
                $title = '_SHOPCATEGORIESTITLE';
                $cache_type = "categ";
                $cache = "cat".$data_array['cat'];
            }
        } else {
            header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if(isset($data_array['num_page']) and intval($data_array['num_page']) != 0) {
            $data_array['num_page'] = intval($data_array['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        $cache_category = 'modules|shop|'.$cache;
        $template = 'shop_display.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_shop->index($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'empty_data' or $error === 'unknown_page' or $error === 'show_not_found_catalog' or $error === 'show_not_found_categ' or $error === 'show_sql_error_'.$cache_type) {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
                    exit();
                } elseif($error === 'unknown_page') {
                    if($cache_type == 'home') {
                        header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
                    } elseif($cache_type == 'categ') {
                        header("Location: index.php?path=admin_shop&func=index&cat=".$data_array['cat']."&lang=".$this->registry->sitelang);
                    } elseif($cache_type == 'subcateg') {
                        header("Location: index.php?path=admin_shop&func=index&cat=".$data_array['cat']."&categ=".$data_array['categ']."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $this->shop_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function shop_edit() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'cat',
                    'categ',
                    'subcateg'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $cacheid = 'error';
        if(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "subcateg";
                if($data_array['subcateg'] != 0) {
                    $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg'];
                }
            } elseif(isset($data_array['categ'])) {
                $cache = "categ";
                if($data_array['categ'] != 0) {
                    $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ'];
                }
            } else {
                $cache = "cat";
                $cacheid = "cat".$data_array['cat'];
            }
        } else {
            $cache = "cat";
        }
        if($cache == "cat") {
            $title = '_SHOPHOMEEDITTITLE';
        } elseif($cache == "categ") {
            $title = '_SHOPCATEGORIESEDITTITLE';
        } elseif($cache == "subcateg") {
            $title = '_SHOPSUBCATEGORIESEDITTITLE';
        }
        $cache_category = 'modules|shop|edit';
        $template = 'shop_edit.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cacheid."|".$this->registry->language)) {
            $this->model_admin_shop->shop_edit($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'edit_no_'.$cache or $error === 'edit_sql_error_'.$cache or $error === 'edit_not_found_'.$cache) {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $this->shop_view($cacheid,$title,$cache_category,$template);
    }


    public function shop_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'item_name',
                    'item_date',
                    'item_action_img',
                    'item_img',
                    'item_description',
                    'item_meta_title',
                    'item_meta_keywords',
                    'item_meta_description'
                );
                $keys2 = array(
                    'item_id',
                    'item_hour',
                    'item_minute',
                    'item_show',
                    'item_catalog',
                    'item_catalog_old',
                    'item_category',
                    'item_category_old',
                    'depth'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($data_array['depth']) and $data_array['depth'] == 1) {
            $cache = "categ";
            $title = '_SHOPCATEGORIESEDITTITLE';
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "subcateg";
            $title = '_SHOPSUBCATEGORIESEDITTITLE';
        } else {
            $cache = "cat";
            $title = '_SHOPHOMEEDITTITLE';
        }
        $this->model_admin_shop->shop_edit_inbase($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'edit_not_all_data_'.$cache or $error === 'edit_del_img_'.$cache or $error === 'edit_img_max_size_'.$cache or $error === 'edit_img_not_upload_'.$cache or $error === 'edit_img_not_eq_'.$cache or $error === 'edit_img_not_eq_type_'.$cache or $error === 'edit_img_upload_'.$cache or $error === 'edit_sql_error_'.$cache or $error === 'edit_ok_'.$cache) {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_add() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'cat',
                    'categ'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "subcateg";
                $cacheid = "add|cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg";
                $title = '_SHOPSUBCATEGORIESADDTITLE';
            } else {
                $cache = "categ";
                $cacheid = "add|cat".$data_array['cat']."|categ";
                $title = '_SHOPCATEGORIESADDTITLE';
            }
        } else {
            $cache = "cat";
            $cacheid = "add|cat";
            $title = '_SHOPCATALOGSADDTITLE';
        }
        $cache_category = 'modules|shop';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cacheid."|".$this->registry->language)) {
            $this->model_admin_shop->shop_add($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'add_sql_error_'.$cache or $error === 'add_no_catalogs' or $error === 'add_no_categs' or $error === 'add_not_found_catalog' or $error === 'add_not_found_categ') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_add.html';
        $this->shop_view($cacheid,$title,$cache_category,$template);
    }


    public function shop_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'item_name',
                    'item_description',
                    'item_meta_title',
                    'item_meta_keywords',
                    'item_meta_description'
                );
                $keys2 = array(
                    'item_show',
                    'item_catalog',
                    'item_category',
                    'depth'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($data_array['depth']) and $data_array['depth'] == 1) {
            $cache = "categ";
            $title = '_SHOPCATEGORIESADDTITLE';
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "subcateg";
            $title = '_SHOPSUBCATEGORIESADDTITLE';
        } else {
            $cache = "cat";
            $title = '_SHOPCATALOGSADDTITLE';
        }
        $this->model_admin_shop->shop_add_inbase($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'add_not_all_data_'.$cache or $error === 'add_img_max_size_'.$cache or $error === 'add_img_not_upload_'.$cache or $error === 'add_img_not_eq_'.$cache or $error === 'add_img_not_eq_type_'.$cache or $error === 'add_img_upload_'.$cache or $error === 'add_sql_error_'.$cache) {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['action'] = trim($_GET['action']);
        } else {
            $data_array['action'] = "";
        }
        if($data_array['action'] == "" and isset($_POST['action'])) {
            $data_array['action'] = trim($_POST['action']);
        }
        if(isset($_GET['cat'])) {
            $data_array['cat'] = intval($_GET['cat']);
        } else {
            $data_array['cat'] = 0;
        }
        if($data_array['cat'] == 0 and isset($_POST['cat'])) {
            $data_array['cat'] = intval($_POST['cat']);
        }
        if(isset($_GET['categ'])) {
            $data_array['categ'] = intval($_GET['categ']);
        } else {
            $data_array['categ'] = 0;
        }
        if($data_array['categ'] == 0 and isset($_POST['categ'])) {
            $data_array['categ'] = intval($_POST['categ']);
        }
        if(isset($_GET['subcateg'])) {
            $data_array['subcateg'] = intval($_GET['subcateg']);
        } else {
            $data_array['subcateg'] = 0;
        }
        if($data_array['subcateg'] == 0 and isset($_POST['subcateg'])) {
            $data_array['subcateg'] = intval($_POST['subcateg']);
        }
        if(isset($_GET['item_id'])) {
            $data_array['get_item_id'] = intval($_GET['item_id']);
        }
        if(isset($_POST['item_id'])) {
            $data_array['post_item_id'] = $_POST['item_id'];
        }
        $title = '_SHOP';
        $this->model_admin_shop->shop_status($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'status_not_all_data' or $error === 'empty_data' or $error === 'unknown_action' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'empty_data') {
                $action = $this->registry->main_class->format_striptags($data_array['action']);
                if($action == "on1" or $action == "off1" or $action == "delete1") {
                    header("Location: index.php?path=admin_shop&func=index&cat=".$data_array['cat']."&lang=".$this->registry->sitelang);
                } elseif($action == "on2" or $action == "off2" or $action == "delete2") {
                    header("Location: index.php?path=admin_shop&func=index&cat=".$data_array['cat']."&categ=".$data_array['categ']."&lang=".$this->registry->sitelang);
                } elseif($action == "on3" or $action == "off3" or $action == "delete3") {
                    header("Location: index.php?path=admin_shop&func=shop_items&lang=".$this->registry->sitelang);
                } elseif($action == "on4" or $action == "off4" or $action == "delete4") {
                    header("Location: index.php?path=admin_shop&func=shop_items&cat=".$data_array['cat']."&lang=".$this->registry->sitelang);
                } elseif($action == "on5" or $action == "off5" or $action == "delete5") {
                    header("Location: index.php?path=admin_shop&func=shop_items&cat=".$data_array['cat']."&categ=".$data_array['categ']."&lang=".$this->registry->sitelang);
                } elseif($action == "on6" or $action == "off6" or $action == "delete6") {
                    header("Location: index.php?path=admin_shop&func=shop_items&cat=".$data_array['cat']."&categ=".$data_array['categ']."&subcateg=".$data_array['subcateg']."&lang=".$this->registry->sitelang);
                } else {
                    header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
                }
                exit();
            } elseif($error === 'unknown_action') {
                header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop|status';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_items() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'cat',
                    'categ',
                    'subcateg',
                    'num_page'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(!isset($data_array['cat']) and !isset($data_array['categ']) and !isset($data_array['subcateg'])) {
            $cache = "homei";
            $cacheid = "home|items";
            $title = '_SHOPSHOWITEMS';
        } elseif(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and $data_array['subcateg'] != 0) {
                $cache = "subcategi";
                $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']."|items";
                $title = '_SHOPSHOWSUBCATEGITEMS';
            } elseif(isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "categi";
                $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|items";
                $title = '_SHOPSHOWCATEGITEMS';
            } else {
                $cache = "cati";
                $cacheid = "cat".$data_array['cat']."|items";
                $title = '_SHOPSHOWCATITEMS';
            }
        } else {
            header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if(isset($data_array['num_page']) and $data_array['num_page'] != 0) {
            $num_page = $data_array['num_page'];
        } else {
            $num_page = 1;
        }
        $cache_category = 'modules|shop|'.$cacheid;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$num_page."|".$this->registry->language)) {
            $this->model_admin_shop->shop_items($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'empty_data' or $error === 'items_sql_error_'.$cache or $error === 'unknown_page' or $error === 'items_not_found_catalog' or $error === 'items_not_found_categ' or $error === 'items_not_found_subcateg') {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_shop&func=index&lang=".$this->registry->sitelang);
                    exit();
                } elseif($error === 'unknown_page') {
                    if($cache == "homei") {
                        header("Location: index.php?path=admin_shop&func=shop_items&lang=".$this->registry->sitelang);
                    } elseif($cache == "cati") {
                        header("Location: index.php?path=admin_shop&func=shop_items&cat=".$data_array['cat']."&lang=".$this->registry->sitelang);
                    } elseif($cache == "categi") {
                        header("Location: index.php?path=admin_shop&func=shop_items&cat=".$data_array['cat']."&categ=".$data_array['categ']."&lang=".$this->registry->sitelang);
                    } elseif($cache == "subcategi") {
                        header("Location: index.php?path=admin_shop&func=shop_items&cat=".$data_array['cat']."&categ=".$data_array['categ']."&subcateg=".$data_array['subcateg']."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_items_display.html';
        $this->shop_view($num_page,$title,$cache_category,$template);
    }


    public function shop_edit_item() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'item_id',
                    'cat',
                    'categ',
                    'subcateg'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(!isset($data_array['item_id'])) {
            $data_array['item_id'] = 0;
        }
        $check = 0;
        if(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "subcategi";
                if($data_array['subcateg'] != 0) {
                    $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']."|item".$data_array['item_id'];
                    $check = 1;
                }
            } elseif(isset($data_array['categ'])) {
                $cache = "categi";
                if($data_array['categ'] != 0) {
                    $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|item".$data_array['item_id'];
                    $check = 1;
                }
            } else {
                $cache = "cati";
                $cacheid = "cat".$data_array['cat']."|item".$data_array['item_id'];
                $check = 1;
            }
        } else {
            $cache = "homei";
            $cacheid = "home|item".$data_array['item_id'];
            $check = 1;
        }
        $title = '_SHOPITEMEDITTITLE';
        if($data_array['item_id'] == 0 or $check == 0) {
            if($data_array['item_id'] == 0) {
                $error = 'edit_item_no_item';
            } elseif($cache == "cati") {
                $error = 'edit_item_no_cat';
            } elseif($cache == "categi") {
                $error = 'edit_item_no_categ';
            } elseif($cache == "subcategi") {
                $error = 'edit_item_no_subcateg';
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop|edit';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cacheid."|".$this->registry->language)) {
            $this->model_admin_shop->shop_edit_item($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'edit_item_no_item' or $error === 'edit_item_no_cat' or $error === 'edit_item_no_categ' or $error === 'edit_item_no_subcateg' or $error === 'edit_item_sql_error' or $error === 'edit_item_not_found') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_item_edit.html';
        $this->shop_view($cacheid,$title,$cache_category,$template);
    }


    public function shop_editi_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'item_img',
                    'item_action_img',
                    'item_name',
                    'item_price',
                    'item_price_discounted',
                    'item_short_description',
                    'item_description',
                    'item_date',
                    'item_meta_title',
                    'item_meta_keywords',
                    'item_meta_description',
                );
                $keys2 = array(
                    'item_id',
                    'item_home',
                    'item_catalog',
                    'item_catalog_old',
                    'item_category',
                    'item_category_old',
                    'item_subcategory',
                    'item_subcategory_old',
                    'item_position',
                    'item_show',
                    'item_quantity',
                    'item_quantity_unlim',
                    'item_quantity_param',
                    'item_display',
                    'item_display_old',
                    'item_hour',
                    'item_minute',
                    'depth'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_SHOPITEMEDITTITLE';
        $this->model_admin_shop->shop_editi_inbase($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'edit_item_not_all_data' or $error === 'edit_item_del_img' or $error === 'edit_item_img_max_size' or $error === 'edit_item_img_not_upload' or $error === 'edit_item_img_not_eq' or $error === 'edit_item_img_not_eq_type' or $error === 'edit_item_img_upload' or $error === 'edit_item_sql_error' or $error === 'edit_item_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_add_item() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'cat',
                    'categ',
                    'subcateg'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "subcategi";
                if($data_array['subcateg'] != 0) {
                    $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']."|item";
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif(isset($data_array['categ'])) {
                $cache = "categi";
                if($data_array['categ'] != 0) {
                    $cacheid = "cat".$data_array['cat']."|categ".$data_array['categ']."|item";
                    $check = 1;
                } else {
                    $check = 0;
                }
            } else {
                $cache = "cati";
                $cacheid = "cat".$data_array['cat']."|item";
                $check = 1;
            }
        } elseif(isset($data_array['cat']) and $data_array['cat'] == 0) {
            $cache = "cati";
            $check = 0;
        } else {
            $cache = "homei";
            $cacheid = "home|item";
            $check = 1;
        }
        $title = '_SHOPITEMADDTITLE';
        if($check == 0) {
            if($cache == "cati") {
                $error = 'add_item_no_cat';
            } elseif($cache == "categi") {
                $error = 'add_item_no_categ';
            } elseif($cache == "subcategi") {
                $error = 'add_item_no_subcateg';
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop|add';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cacheid."|".$this->registry->language)) {
            $this->model_admin_shop->shop_add_item($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'add_item_no_cat' or $error === 'add_item_no_categ' or $error === 'add_item_no_subcateg' or $error === 'add_item_sql_error' or $error === 'add_item_not_found_catalog' or $error === 'add_item_not_found_categ' or $error === 'add_item_not_found_subcateg') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_item_add.html';
        $this->shop_view($cacheid,$title,$cache_category,$template);
    }


    public function shop_addi_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'item_action_img',
                    'item_name',
                    'item_price',
                    'item_price_discounted',
                    'item_short_description',
                    'item_description',
                    'item_date',
                    'item_meta_title',
                    'item_meta_keywords',
                    'item_meta_description'
                );
                $keys2 = array(
                    'item_home',
                    'item_catalog',
                    'item_category',
                    'item_subcategory',
                    'item_show',
                    'item_quantity',
                    'item_quantity_unlim',
                    'item_quantity_param',
                    'item_display',
                    'item_hour',
                    'item_minute',
                    'depth'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_SHOPITEMADDTITLE';
        $this->model_admin_shop->shop_addi_inbase($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'add_item_not_all_data' or $error === 'add_item_img_max_size' or $error === 'add_item_img_not_upload' or $error === 'add_item_img_not_eq' or $error === 'add_item_img_not_eq_type' or $error === 'add_item_img_upload' or $error === 'add_item_sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_sort() {
        $data_array = false;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'cat',
                    'categ',
                    'subcateg',
                    'items',
                    'num_page'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(!isset($data_array['cat']) and !isset($data_array['categ']) and !isset($data_array['subcateg'])) {
            if(isset($data_array['items']) and $data_array['items'] == 1) {
                $cache = "homei";
                $cacheid = "i|home";
            } else {
                $cache = "home";
                $cacheid = "|home";
            }
            $itemid = 1;
        } elseif(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and isset($data_array['categ']) and $data_array['categ'] != 0 and isset($data_array['items']) and $data_array['items'] == 1) {
                $cache = "subcategi";
                if($data_array['subcateg'] != 0) {
                    $cacheid = "i|cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg'];
                    $itemid = $data_array['subcateg'];
                } else {
                    $itemid = 0;
                }
            } elseif(isset($data_array['categ'])) {
                if(isset($data_array['items']) and $data_array['items'] == 1) {
                    $cache = "categi";
                } else {
                    $cache = "subcateg";
                }
                if($data_array['categ'] != 0) {
                    if(isset($data_array['items']) and $data_array['items'] == 1) {
                        $cacheid = "i|cat".$data_array['cat']."|categ".$data_array['categ'];
                    } else {
                        $cacheid = "|cat".$data_array['cat']."|categ".$data_array['categ'];
                    }
                    $itemid = $data_array['categ'];
                } else {
                    $itemid = 0;
                }
            } else {
                if(isset($data_array['items']) and $data_array['items'] == 1) {
                    $cache = "cati";
                    $cacheid = "i|cat".$data_array['cat'];
                } else {
                    $cache = "categ";
                    $cacheid = "|cat".$data_array['cat'];
                }
                $itemid = $data_array['cat'];
            }
        } else {
            if(isset($data_array['items']) and $data_array['items'] == 1) {
                $cache = "cati";
            } else {
                $cache = "categ";
            }
            $itemid = 0;
        }
        $title = '_SHOPSORTTITLE';
        if($itemid == 0) {
            if($cache == "categ" or $cache == "cati") {
                $error = 'sort_no_cat';
            } elseif($cache == "subcateg" or $cache == "categi") {
                $error = 'sort_no_categ';
            } elseif($cache == "subcategi") {
                $error = 'sort_no_subcateg';
            }
            $this->shop_view($error,$title);
        }
        if(isset($data_array['num_page']) and intval($data_array['num_page']) != 0) {
            $data_array['num_page'] = intval($data_array['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        $cache_category = 'modules|shop|sort'.$cacheid;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_shop->shop_sort($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'sort_no_cat' or $error === 'sort_no_categ' or $error === 'sort_no_subcateg' or $error === 'sort_sql_error' or $error === 'unknown_page' or $error === 'sort_not_found_catalog' or $error === 'sort_not_found_categ' or $error === 'sort_not_found_subcateg') {
                if($error === 'unknown_page') {
                    if($cache == "home") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&lang=".$this->registry->sitelang);
                    } elseif($cache == "categ") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&cat=".$data_array['cat']."&lang=".$this->registry->sitelang);
                    } elseif($cache == "subcateg") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&cat=".$data_array['cat']."&categ=".$data_array['categ']."&lang=".$this->registry->sitelang);
                    } elseif($cache == "homei") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&items=1&lang=".$this->registry->sitelang);
                    } elseif($cache == "cati") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&items=1&cat=".$data_array['cat']."&lang=".$this->registry->sitelang);
                    } elseif($cache == "categi") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&items=1&cat=".$data_array['cat']."&categ=".$data_array['categ']."&lang=".$this->registry->sitelang);
                    } elseif($cache == "subcategi") {
                        header("Location: index.php?path=admin_shop&func=shop_sort&items=1&cat=".$data_array['cat']."&categ=".$data_array['categ']."&subcateg=".$data_array['subcateg']."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_sort.html';
        $this->shop_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function shop_sort_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'cat',
                    'categ',
                    'subcateg',
                    'depth'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($_POST['item_id'])) {
            $data_array['item_id'] = $_POST['item_id'];
        }
        if(isset($_POST['item_position'])) {
            $data_array['item_position'] = $_POST['item_position'];
        }
        $title = '_SHOPSORTTITLE';
        $this->model_admin_shop->shop_sort_save($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'sort_not_all_data' or $error === 'sort_sql_error' or $error === 'sort_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop|sort';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_orders() {
        $data_array['status'] = 0;
        if(isset($_GET['status'])) {
            $data_array['status'] = intval($_GET['status']);
        }
        if(!isset($_GET['status']) and isset($_POST['status'])) {
            $data_array['status'] = intval($_POST['status']);
        }
        $orders_status = 0;
        if(in_array($data_array['status'],array(0,1,2,3,4,5,6,7,8,9))) {
            $orders_status = $data_array['status'];
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        $title = '_SHOPORDERSTITLE';
        $cache_category = 'modules|shop|orders|'.$orders_status;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_shop->shop_orders($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'orders_sql_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_shop&func=shop_orders&status=".$orders_status."&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_orders.html';
        $this->shop_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function shop_orderproc() {
        $data_array['order_id'] = 0;
        if(isset($_GET['order_id'])) {
            $data_array['order_id'] = intval($_GET['order_id']);
        }
        $data_array['edit'] = 0;
        if(isset($_GET['edit'])) {
            $data_array['edit'] = intval($_GET['edit']);
        }
        if($data_array['edit'] > 0) {
            $edit = 'edit';
            $title = '_SHOPEDITORDER';
        } else {
            $edit = false;
            $title = '_SHOPSHOWORDER';
        }
        $cache_category = 'modules|shop|order';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$edit.$data_array['order_id']."|".$this->registry->language)) {
            $this->model_admin_shop->shop_orderproc($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'order_proc_no_order' or $error === 'order_proc_sql_error' or $error === 'order_proc_not_found') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $this->shop_view($edit.$error,$title);
            }
            $this->assign_array($result);
        }
        $template = 'shop_order.html';
        $this->shop_view($edit.$data_array['order_id'],$title,$cache_category,$template);
    }


    public function shop_order_save() {
        $data_array['order_hour'] = 0;
        $data_array['order_minute'] = 0;
        $data_array['ship_hour'] = 0;
        $data_array['ship_minute'] = 0;
        $data_array['order_edit'] = 0;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'order_date',
                    'ship_date',
                    'ship_name',
                    'ship_street',
                    'ship_house',
                    'ship_flat',
                    'ship_city',
                    'ship_state',
                    'ship_postalcode',
                    'ship_country',
                    'ship_telephone',
                    'ship_email',
                    'ship_addinfo',
                    'order_comments'
                );
                $keys2 = array(
                    'order_id',
                    'order_hour',
                    'order_minute',
                    'ship_hour',
                    'ship_minute',
                    'order_status',
                    'order_status_old',
                    'order_delivery',
                    'order_pay',
                    'order_edit'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_SHOPEDITORDER';
        $this->model_admin_shop->shop_order_save($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'order_not_all_data' or $error === 'order_email' or $error === 'order_post_length' or $error === 'unknown_action' or $error === 'order_save_sql_error' or $error === 'order_edit_ok') {
            if($error === 'unknown_action') {
                header("Location: index.php?path=admin_shop&func=shop_orderproc&order_id=".$data_array['order_id']."&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->shop_view($error,$title);
        }
        $cache_category = 'modules|shop|order';
        $this->shop_view($result,$title,$cache_category);
    }


    public function shop_order_status() {
        $data_array['action'] = '';
        if(isset($_GET['action'])) {
            $data_array['action'] = trim($_GET['action']);
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $data_array['action'] = trim($_POST['action']);
        }
        if(!empty($data_array['action'])) {
            $data_array['action'] = $this->registry->main_class->format_striptags($data_array['action']);
        }
        if(isset($_GET['item_id'])) {
            $data_array['get_item_id'] = intval($_GET['item_id']);
        }
        if(isset($_POST['item_id'])) {
            $data_array['post_item_id'] = $_POST['item_id'];
        }
        if(!empty($data_array['action']) and ($data_array['action'] == "delete3" or $data_array['action'] == "on" or $data_array['action'] == "off")) {
            $title = '_SHOPDELIVERY';
        } elseif(!empty($data_array['action']) and ($data_array['action'] == "delete4" or $data_array['action'] == "on2" or $data_array['action'] == "off2")) {
            $title = '_SHOPPAY';
        } else {
            $title = '_SHOPORDERSTITLE';
        }
        $template = false;
        $this->model_admin_shop->shop_order_status($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'order_status_not_all_data' or $error === 'order_status_deliv_items2' or $error === 'order_status_pay_items2' or $error === 'order_status_order_items' or $error === 'order_status_deliv_items' or $error === 'order_status_pay_items' or $error === 'unknown_action' or $error === 'sql_error' or $error === 'not_save') {
            if($error === 'unknown_action') {
                header("Location: index.php?path=admin_shop&func=shop_orders&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache = false;
            if(($error === 'sql_error' or $error === 'not_save')) {
                $cache = 'order_status_'.$data_array['action'].'_'.$error;
            }
            $cache_category = false;
            $this->shop_view($error,$title,$cache_category,$template,$cache);
        }
        $this->assign_array($result);
        $cache_category = 'modules|shop|order_status';
        $cache = $data_array['action'].'_done';
        $this->shop_view($result['shop_message'],$title,$cache_category,$template,$cache);
    }


    public function shop_orderi_edit() {
        $data_array['item_id'] = 0;
        $data_array['order_id'] = 0;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'item_id',
                    'order_id'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_SHOPEDITORDER';
        $cache_category = 'modules|shop|orderi|'.$data_array['order_id'];
        $cache = 'editi'.$data_array['item_id'];
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_shop->shop_orderi_edit($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'no_item' or $error === 'sql_error' or $error === 'not_found_item') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = 'order_item_edit_'.$error;
                $this->shop_view($error,$title,$cache_category,$template,$cache);
            }
            $this->assign_array($result);
        }
        $type = false;
        $template = 'shop_orderi_edit.html';
        $this->shop_view($type,$title,$cache_category,$template,$cache);
    }


    public function shop_orderi_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'order_item_name',
                    'order_item_price'
                );
                $keys2 = array(
                    'order_item_id',
                    'order_id',
                    'order_item_quantity'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_SHOPEDITORDER';
        $template = false;
        $this->model_admin_shop->shop_orderi_save($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error' or $error === 'not_save') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $cache = 'order_item_save_'.$error;
            $this->shop_view($error,$title,$cache_category,$template,$cache);
        }
        $this->assign_array($result);
        $cache_category = 'modules|shop|ordiedit';
        $cache = 'order_item_save_done';
        $this->shop_view($result['shop_message'],$title,$cache_category,$template,$cache);
    }


    public function shop_methods() {
        $data_array['pay'] = 0;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'pay',
                    'num_page'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if(isset($data_array['num_page']) and intval($data_array['num_page']) != 0) {
            $data_array['num_page'] = intval($data_array['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        $type = "pays";
        $title = '_SHOPPAY';
        if($data_array['pay'] == 0) {
            $type = "delivs";
            $title = '_SHOPDELIVERY';
        }
        $cache_category = 'modules|shop|'.$type;
        $cache = $data_array['num_page'];
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_shop->shop_methods($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'sql_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    if($type == "delivs") {
                        header("Location: index.php?path=admin_shop&func=shop_methods&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_shop&func=shop_methods&pay=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = 'methods_'.$type.'_'.$error;
                $this->shop_view($error,$title,$cache_category,$template,$cache);
            }
            $this->assign_array($result);
        }
        $type = false;
        $template = 'shop_methods.html';
        $this->shop_view($type,$title,$cache_category,$template,$cache);
    }


    public function shop_edit_method() {
        $data_array['item_id'] = 0;
        $data_array['pay'] = 0;
        if(!empty($_GET)) {
            foreach($_GET as $key => $value) {
                $keys = array(
                    'item_id',
                    'pay'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $type = 'pay';
        $title = '_SHOPEDITPAY';
        if($data_array['pay'] == 0) {
            $type = 'deliv';
            $title = '_SHOPEDITDELIVERY';
        }
        $cache_category = 'modules|shop|'.$type;
        $cache = 'edit'.$data_array['item_id'];
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_shop->shop_edit_method($data_array);
            $error = $this->model_admin_shop->get_property_value('error');
            $error_array = $this->model_admin_shop->get_property_value('error_array');
            $result = $this->model_admin_shop->get_property_value('result');
            if($error === 'no_item_'.$type or $error === 'sql_error' or $error === 'not_found_'.$type) {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = 'edit_method_'.$type.'_'.$error;
                $this->shop_view($error,$title,$cache_category,$template,$cache);
            }
            $this->assign_array($result);
        }
        $type = false;
        $template = 'shop_method_edit.html';
        $this->shop_view($type,$title,$cache_category,$template,$cache);
    }


    public function shop_method_save() {
        $data_array['pay'] = 0;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'item_name',
                    'item_price'
                );
                $keys2 = array(
                    'item_id',
                    'item_status',
                    'pay'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $type = "pay";
        $title = '_SHOPEDITPAY';
        if($data_array['pay'] == 0) {
            $type = "deliv";
            $title = '_SHOPEDITDELIVERY';
        }
        $template = false;
        $this->model_admin_shop->shop_method_save($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error' or $error === 'not_save') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $cache = 'method_save_'.$type.'_'.$error;
            $this->shop_view($error,$title,$cache_category,$template,$cache);
        }
        $cache_category = 'modules|shop|edit_'.$type;
        $this->shop_view($result,$title,$cache_category,$template);
    }


    public function shop_method_add() {
        $pay = 0;
        if(isset($_GET['pay'])) {
            $pay = intval($_GET['pay']);
        }
        $type = "pay";
        $title = '_SHOPADDPAY';
        if($pay == 0) {
            $type = "deliv";
            $title = '_SHOPADDDELIVERY';
        }
        $cache_category = 'modules|shop|'.$type;
        $cache = 'add';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_shop->shop_method_add($pay);
            $result = $this->model_admin_shop->get_property_value('result');
            $this->assign_array($result);
        }
        $type = false;
        $template = 'shop_method_add.html';
        $this->shop_view($type,$title,$cache_category,$template,$cache);
    }


    public function shop_method_save2() {
        $data_array['pay'] = 0;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'item_name',
                    'item_price'
                );
                $keys2 = array(
                    'item_status',
                    'pay'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $type = "pay";
        $title = '_SHOPADDPAY';
        if($data_array['pay'] == 0) {
            $type = "deliv";
            $title = '_SHOPADDDELIVERY';
        }
        $template = false;
        $this->model_admin_shop->shop_method_save2($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $error_array = $this->model_admin_shop->get_property_value('error_array');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $cache = 'method_save2_'.$type.'_'.$error;
            $this->shop_view($error,$title,$cache_category,$template,$cache);
        }
        $cache_category = 'modules|shop|add_'.$type;
        $this->shop_view($result,$title,$cache_category,$template);
    }


    public function shop_config() {
        $title = '_SHOPCONFIGPAGETITLE';
        $cache_category = 'modules|shop';
        $cache = 'config';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_shop->shop_config();
            $result = $this->model_admin_shop->get_property_value('result');
            $this->assign_array($result);
        }
        $template = 'shop_config.html';
        $type = false;
        $this->shop_view($type,$title,$cache_category,$template,$cache);
    }


    public function shop_config_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'systemdk_shop_catalogs_order',
                    'systemdk_shop_homeitems_order',
                    'systemdk_shop_categories_order',
                    'systemdk_shop_catitems_order',
                    'systemdk_shop_subcategories_order',
                    'systemdk_shop_categitems_order',
                    'systemdk_shop_subcategitems_order',
                    'systemdk_shop_itemimage_path',
                    'systemdk_shop_itemimage_position',
                    'systemdk_shop_image_path',
                    'systemdk_shop_image_position',
                    'systemdk_shop_valuta'
                );
                $keys2 = array(
                    'systemdk_shop_homeitems_perpage',
                    'systemdk_shop_catitems_perpage',
                    'systemdk_shop_categitems_perpage',
                    'systemdk_shop_subcategitems_perpage',
                    'systemdk_shop_itemimage_maxsize',
                    'systemdk_shop_itemimage_maxwidth',
                    'systemdk_shop_itemimage_maxheight',
                    'systemdk_shop_itemimage_width',
                    'systemdk_shop_itemimage_width2',
                    'systemdk_shop_itemimage_width3',
                    'systemdk_shop_itemimage_height3',
                    'systemdk_shop_image_maxsize',
                    'systemdk_shop_image_maxwidth',
                    'systemdk_shop_image_maxheight',
                    'systemdk_shop_image_width',
                    'systemdk_shop_items_columns',
                    'systemdk_shop_items_underparent',
                    'systemdk_shop_antispam_ipaddr',
                    'systemdk_shop_antispam_num'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_SHOPCONFIGPAGETITLE';
        $template = false;
        $this->model_admin_shop->shop_config_save($data_array);
        $error = $this->model_admin_shop->get_property_value('error');
        $result = $this->model_admin_shop->get_property_value('result');
        if($error === 'not_all_data' or $error === 'no_file' or $error === 'no_write') {
            $cache_category = false;
            $cache = 'config_save_'.$error;
            $this->shop_view($error,$title,$cache_category,$template,$cache);
        }
        $cache_category = 'modules|shop';
        $this->shop_view($result,$title,$cache_category,$template);
    }
}
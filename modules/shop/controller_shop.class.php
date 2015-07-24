<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class controller_shop extends controller_base {


    private $model_shop;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_shop = singleton::getinstance('shop',$registry);
    }


    private function shop_view($type,$titles,$cache_category = false,$cache = false,$template = false) {
        if(empty($cache_category)) {
            $cache_category = 'modules|shop|error';
        }
        if(empty($template)) {
            $template = 'shop_messages.html';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->configLoad($this->registry->language."/modules/shop/shop.conf");
            if(!empty($titles)) {
                $this->registry->main_class->set_sitemeta($this->getConfigVars($titles['title']),$this->getConfigVars($titles['description']),$this->getConfigVars($titles['keywords']));
            }
            if($template === 'shop_messages.html') {
                $this->assign("shop_message",$type);
                $this->assign("include_center","modules/shop/".$template);
            }
        } elseif($template === 'shop_cart.html') {
            $this->configLoad($this->registry->language."/modules/shop/shop.conf");
        }
        if($template != 'shop_messages.html') {
            $this->assign("include_center","modules/shop/".$template);
        }
        $this->display("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function shop_header() {
        $data_array = false;
        if(isset($_GET['path']) and trim($_GET['path']) != "") {
            $data_array['path'] = trim($_GET['path']);
        }
        if(isset($_POST['path']) and trim($_POST['path']) != "") {
            $data_array['path'] = trim($_POST['path']);
        }
        if(isset($this->registry->GET_func)) {
            $data_array['func'] = $this->registry->GET_func;
        }
        if(isset($this->registry->POST_func)) {
            $data_array['func'] = $this->registry->POST_func;
        }
        if(isset($_POST['shop_new']) and intval($_POST['shop_new']) != 0) {
            $data_array['shop_new'] = intval($_POST['shop_new']);
        }
        if(isset($_POST['shop_save'])) {
            $data_array['shop_save'] = true;
        }
        if(!empty($_POST) and is_array($_POST)) {
            foreach($_POST as $key => $value) {
                if(is_numeric($key) and is_numeric($value)) {
                    $data_array['shop_items'][$key] = $value;
                }
            }
        }
        $result = $this->model_shop->shop_header($data_array);
        $this->assign_array($result);
    }


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        if($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|'.$cache_user.'|home';
        $cache = $num_page;
        $titles['title'] = '_SHOP_MODULE_METATITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_shop->index($num_page);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if($error === 'categories_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/");
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                $cache_category = false;
                $cache = $cache_user.'_'.$error;
                $this->shop_view($error,$titles,$cache_category,$cache);
            }
        }
        $type = false;
        $template = 'shop_catalogs.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function categs() {
        $data_array['catalog_id'] = 0;
        if(isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|'.$cache_user.'|cat'.$data_array['catalog_id'];
        $cache = $data_array['num_page'];
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_shop->categs($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if($error === 'not_all_data' or $error === 'not_find_catalog' or $error === 'categories_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/categs/".$data_array['catalog_id']);
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&func=categs&cat_id=".$data_array['catalog_id']."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user.'_'.$error;
                $this->shop_view($error,$titles,$cache_category,$cache);
            }
        }
        $type = false;
        $titles = false;
        $template = 'shop_categories.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function subcategs() {
        $data_array['catalog_id'] = 0;
        if(isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        $data_array['category_id'] = 0;
        if(isset($_GET['categ_id'])) {
            $data_array['category_id'] = intval($_GET['categ_id']);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|'.$cache_user.'|cat'.$data_array['catalog_id'].'|categ'.$data_array['category_id'];
        $cache = $data_array['num_page'];
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_shop->subcategs($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if($error === 'not_all_data2' or $error === 'not_find_category' or $error === 'not_find_catalog' or $error === 'categories_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/subcategs/".$data_array['catalog_id']."/".$data_array['category_id']);
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&func=subcategs&cat_id=".$data_array['catalog_id']."&categ_id=".$data_array['category_id']."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user.'_'.$error;
                $this->shop_view($error,$titles,$cache_category,$cache);
            }
        }
        $type = false;
        $titles = false;
        $template = 'shop_subcategories.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function items() {
        $data_array['catalog_id'] = 0;
        if(isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        $data_array['category_id'] = 0;
        if(isset($_GET['categ_id'])) {
            $data_array['category_id'] = intval($_GET['categ_id']);
        }
        $data_array['subcategory_id'] = 0;
        if(isset($_GET['subcateg_id'])) {
            $data_array['subcategory_id'] = intval($_GET['subcateg_id']);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|'.$cache_user.'|cat'.$data_array['catalog_id'].'|categ'.$data_array['category_id']."|sucateg".$data_array['subcategory_id'];
        $cache = $data_array['num_page'];
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_shop->items($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if($error === 'not_all_data3' or $error === 'not_find_subcategory' or $error === 'not_find_category' or $error === 'not_find_catalog' or $error === 'categories_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/items/cat/".$data_array['catalog_id']."/categ/".$data_array['category_id']."/subcateg/".$data_array['subcategory_id']);
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&func=items&cat_id=".$data_array['catalog_id']."&categ_id=".$data_array['category_id']."&subcateg_id=".$data_array['subcategory_id']."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user.'_'.$error;
                $this->shop_view($error,$titles,$cache_category,$cache);
            }
        }
        $type = false;
        $titles = false;
        $template = 'shop_items.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function item() {
        $data_array['item_id'] = 0;
        $cache_id = 0;
        if(isset($_GET['item_id'])) {
            $data_array['item_id'] = intval($_GET['item_id']);
        }
        if(isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        if(isset($_GET['categ_id'])) {
            $data_array['category_id'] = intval($_GET['categ_id']);
        }
        if(isset($_GET['subcateg_id'])) {
            $data_array['subcategory_id'] = intval($_GET['subcateg_id']);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        if(isset($data_array['catalog_id']) and $data_array['catalog_id'] != 0) {
            if(isset($data_array['subcategory_id']) and isset($data_array['category_id']) and $data_array['category_id'] != 0) {
                if($data_array['subcategory_id'] != 0) {
                    $cache_id = "cat".$data_array['catalog_id']."|categ".$data_array['category_id']."|subcateg".$data_array['subcategory_id']."|item".$data_array['item_id'];
                }
            } elseif(isset($data_array['category_id'])) {
                if($data_array['category_id'] != 0) {
                    $cache_id = "cat".$data_array['catalog_id']."|categ".$data_array['category_id']."|item".$data_array['item_id'];
                }
            } else {
                $cache_id = "cat".$data_array['catalog_id']."|item".$data_array['item_id'];
            }
        } elseif(isset($data_array['catalog_id']) and $data_array['catalog_id'] == 0) {
            $cache_id = 0;
        } else {
            $cache_id = "home|item".$data_array['item_id'];
        }
        $cache_category = 'modules|shop|'.$cache_user.'|'.$cache_id;
        $cache = $data_array['num_page'];
        $titles = false;
        if(!$this->isCached("index.html",$this->registry->sitelang.'|'.$cache_category.'|'.$cache.'|'.$this->registry->language)) {
            $this->model_shop->item($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if($error === 'not_all_data4' or $error === 'not_all_data' or $error === 'not_all_data2' or $error === 'not_all_data3' or $error === 'categories_error') {
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user.'_'.$error;
                $this->shop_view($error,$titles,$cache_category,$cache);
            }
            if(isset($result['shop_item_all']) and $result['shop_item_all'] === 'no') {
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
            }
        }
        $type = false;
        $template = 'shop_item.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function cart() {
        if($this->registry->main_class->is_admin()) {
            $cache = "admin";
        } else {
            $cache = "other";
        }
        $this->model_shop->cart();
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);
        $type = false;
        $titles['title'] = '_SHOP_MODULE_CARTTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $cache_category = 'modules|shop|cart|display';
        $template = 'shop_cart.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function checkout() {
        if($this->registry->main_class->is_admin()) {
            $cache = "admin";
        } else {
            $cache = "other";
        }
        $this->model_shop->checkout();
        $error = $this->model_shop->get_property_value('error');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);
        $titles['title'] = '_SHOP_MODULE_PROCESSORDERTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        if($error === 'categories_error') {
            $cache_category = false;
            $cache = 'checkout_'.$cache.'_'.$error;
            $this->shop_view($error,$titles,$cache_category,$cache);
        }
        $type = false;
        $cache_category = 'modules|shop|checkout|display';
        $template = 'shop_cart.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function checkout2() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'shop_ship_name',
                    'shop_ship_telephone',
                    'shop_ship_email',
                    'shop_ship_country',
                    'shop_ship_state',
                    'shop_ship_city',
                    'shop_ship_street',
                    'shop_ship_homenumber',
                    'shop_ship_flatnumber',
                    'shop_ship_postalcode',
                    'shop_ship_addaddrnotes',
                    'shop_order_comments'
                );
                $keys2 = array(
                    'shop_order_delivery',
                    'shop_order_pay'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if($this->registry->main_class->is_admin()) {
            $cache = "admin";
        } else {
            $cache = "other";
        }
        $this->model_shop->checkout2($data_array);
        $error = $this->model_shop->get_property_value('error');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);
        $titles['title'] = '_SHOP_MODULE_PROCESSORDERTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        if($error === 'shop_not_all_data' or $error === 'user_email' or $error === 'categories_error') {
            $cache_category = false;
            $cache = 'checkout2_'.$cache.'_'.$error;
            $this->shop_view($error,$titles,$cache_category,$cache);
        }
        $type = false;
        $cache_category = 'modules|shop|checkout2|display';
        $template = 'shop_cart.html';
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }


    public function insert() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'shop_ship_name',
                    'shop_ship_telephone',
                    'shop_ship_email',
                    'shop_ship_country',
                    'shop_ship_state',
                    'shop_ship_city',
                    'shop_ship_street',
                    'shop_ship_homenumber',
                    'shop_ship_flatnumber',
                    'shop_ship_postalcode',
                    'shop_ship_addaddrnotes',
                    'shop_order_comments',
                    'captcha'
                );
                $keys2 = array(
                    'shop_order_delivery',
                    'shop_order_pay'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if($this->registry->main_class->is_admin()) {
            $cache = "admin";
        } else {
            $cache = "other";
        }
        $titles['title'] = '_SHOP_MODULE_PROCESSORDERTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $this->model_shop->insert($data_array);
        $error = $this->model_shop->get_property_value('error');
        $error_array = $this->model_shop->get_property_value('error_array');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);
        if($error === 'shop_not_all_data2' or $error === 'check_code' or $error === 'post_limit' or $error === 'user_email2' or $error === 'post_length' or $error === 'not_insert') {
            if($error === 'not_insert') {
                $this->assign("errortext",$error_array);
            }
            $cache_category = false;
            $cache = 'insert_'.$cache.'_'.$error;
            $this->shop_view($error,$titles,$cache_category,$cache);
        }
        $type = false;
        $template = 'shop_cart.html';
        if(isset($result['shop_cart_display_items']) and $result['shop_cart_display_items'] === 'no') {
            $cache_category = 'modules|shop|checkout|display';
        } else {
            $cache_category = 'modules|shop';
            $cache = $cache.'_addok';
        }
        $this->shop_view($type,$titles,$cache_category,$cache,$template);
    }
}
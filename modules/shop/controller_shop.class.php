<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_shop extends controller_base
{


    /**
     * @var shop
     */
    private $model_shop;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_shop = singleton::getinstance('shop', $registry);
    }


    private function shop_view($type, $titles, $cache_category = false, $cache = false, $template = false)
    {
        if (empty($cache_category)) {
            $cache_category = 'modules|shop|error';
        }
        if (empty($template)) {
            $template = 'shop_messages.html';
        }
        if (empty($cache)) {
            $cache = $type;
        }
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->configLoad($this->registry->language . "/modules/shop/shop.conf");
            if (!empty($titles)) {
                $this->registry->main_class->set_sitemeta(
                    $this->getConfigVars($titles['title']), $this->getConfigVars($titles['description']), $this->getConfigVars($titles['keywords'])
                );
            }
            if ($template === 'shop_messages.html') {
                $this->assign("shop_message", $type);
                $this->assign("include_center", "modules/shop/" . $template);
            }
        } elseif ($template === 'shop_cart.html') {
            $this->configLoad($this->registry->language . "/modules/shop/shop.conf");
        }
        if ($template != 'shop_messages.html') {
            $this->assign("include_center", "modules/shop/" . $template);
        }
        $this->display("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language);
        exit();
    }


    public function shop_header()
    {
        $data_array = false;
        if (isset($_GET['path']) && trim($_GET['path']) != "") {
            $data_array['path'] = trim($_GET['path']);
        }
        if (isset($_POST['path']) && trim($_POST['path']) != "") {
            $data_array['path'] = trim($_POST['path']);
        }
        if (isset($this->registry->GET_func)) {
            $data_array['func'] = $this->registry->GET_func;
        }
        if (isset($this->registry->POST_func)) {
            $data_array['func'] = $this->registry->POST_func;
        }
        if (isset($_POST['shop_new']) && intval($_POST['shop_new']) != 0) {
            $data_array['shop_new'] = intval($_POST['shop_new']);
        }
        if (!empty($_POST['shop_additional_params']) && is_array($_POST['shop_additional_params'])) {
            foreach ($_POST['shop_additional_params'] as $item_key => $item_data) {
                if (is_numeric($item_key) && is_array($item_data)) {
                    foreach ($item_data as $group_key => $group_data) {
                        if (is_numeric($group_key) && is_array($group_data)) {
                            foreach ($group_data as $key => $value) {
                                if (is_numeric($key) && is_numeric($value)) {
                                    $data_array['shop_additional_params'][intval($item_key)][intval($group_key)][intval($key)] = intval($value);
                                }
                            }
                        }
                    }
                }
            }
        }
        if (isset($_POST['shop_save'])) {
            $data_array['shop_save'] = true;
        }
        if (!empty($_POST) && is_array($_POST)) {
            foreach ($_POST as $key => $value) {
                if (is_numeric($key) && is_numeric($value)) {
                    $data_array['shop_items'][intval($key)] = intval($value);
                }
            }
        }
        $result = $this->model_shop->shop_header($data_array);
        $this->assign_array($result);
    }


    public function index()
    {
        if (isset($_GET['num_page']) && intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|' . $cache_user . '|home';
        $cache = $num_page;
        $titles['title'] = '_SHOP_MODULE_METATITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_shop->index($num_page);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'categories_error' || $error === 'unknown_page') {
                if ($error === 'unknown_page') {
                    if (SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /" . SITE_DIR . $this->registry->sitelang . "/shop/");
                    } else {
                        header("Location: /" . SITE_DIR . "index.php?path=shop&lang=" . $this->registry->sitelang);
                    }
                    exit();
                }
                $cache_category = false;
                $cache = $cache_user . '_' . $error;
                $this->shop_view($error, $titles, $cache_category, $cache);
            }
        }
        $type = false;
        $template = 'shop_catalogs.html';
        $this->shop_view($type, $titles, $cache_category, $cache, $template);
    }


    public function categs()
    {
        $data_array['catalog_id'] = 0;
        if (isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        if (isset($_GET['num_page']) && intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|' . $cache_user . '|cat' . $data_array['catalog_id'];
        $cache = $data_array['num_page'];
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_shop->categs($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'not_all_data' || $error === 'not_find_catalog' || $error === 'categories_error' || $error === 'unknown_page') {
                if ($error === 'unknown_page') {
                    if (SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /" . SITE_DIR . $this->registry->sitelang . "/shop/categs/" . $data_array['catalog_id']);
                    } else {
                        header("Location: /" . SITE_DIR . "index.php?path=shop&func=categs&cat_id=" . $data_array['catalog_id'] . "&lang=" . $this->registry->sitelang);
                    }
                    exit();
                }
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user . '_' . $error;
                $this->shop_view($error, $titles, $cache_category, $cache);
            }
        }
        $type = false;
        $titles = false;
        $template = 'shop_categories.html';
        $this->shop_view($type, $titles, $cache_category, $cache, $template);
    }


    public function subcategs()
    {
        $data_array['catalog_id'] = 0;
        if (isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        $data_array['category_id'] = 0;
        if (isset($_GET['categ_id'])) {
            $data_array['category_id'] = intval($_GET['categ_id']);
        }
        if (isset($_GET['num_page']) && intval($_GET['num_page']) !== 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|' . $cache_user . '|cat' . $data_array['catalog_id'] . '|categ' . $data_array['category_id'];
        $cache = $data_array['num_page'];
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_shop->subcategs($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'not_all_data2' || $error === 'not_find_category' || $error === 'not_find_catalog' || $error === 'categories_error'
                || $error === 'unknown_page'
            ) {
                if ($error === 'unknown_page') {
                    if (SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /" . SITE_DIR . $this->registry->sitelang . "/shop/subcategs/" . $data_array['catalog_id'] . "/" . $data_array['category_id']);
                    } else {
                        header(
                            "Location: /" . SITE_DIR . "index.php?path=shop&func=subcategs&cat_id=" . $data_array['catalog_id'] . "&categ_id="
                            . $data_array['category_id'] . "&lang=" . $this->registry->sitelang
                        );
                    }
                    exit();
                }
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user . '_' . $error;
                $this->shop_view($error, $titles, $cache_category, $cache);
            }
        }
        $type = false;
        $titles = false;
        $template = 'shop_subcategories.html';
        $this->shop_view($type, $titles, $cache_category, $cache, $template);
    }


    public function items()
    {
        $data_array['catalog_id'] = 0;
        if (isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        $data_array['category_id'] = 0;
        if (isset($_GET['categ_id'])) {
            $data_array['category_id'] = intval($_GET['categ_id']);
        }
        $data_array['subcategory_id'] = 0;
        if (isset($_GET['subcateg_id'])) {
            $data_array['subcategory_id'] = intval($_GET['subcateg_id']);
        }
        if (isset($_GET['num_page']) && intval($_GET['num_page']) !== 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $cache_category = 'modules|shop|' . $cache_user . '|cat' . $data_array['catalog_id'] . '|categ' . $data_array['category_id'] . "|sucateg"
                          . $data_array['subcategory_id'];
        $cache = $data_array['num_page'];
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_shop->items($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'not_all_data3' || $error === 'not_find_subcategory' || $error === 'not_find_category' || $error === 'not_find_catalog'
                || $error === 'categories_error'
                || $error === 'unknown_page'
            ) {
                if ($error === 'unknown_page') {
                    if (SYSTEMDK_MODREWRITE === 'yes') {
                        header(
                            "Location: /" . SITE_DIR . $this->registry->sitelang . "/shop/items/cat/" . $data_array['catalog_id'] . "/categ/" . $data_array['category_id']
                            . "/subcateg/" . $data_array['subcategory_id']
                        );
                    } else {
                        header(
                            "Location: /" . SITE_DIR . "index.php?path=shop&func=items&cat_id=" . $data_array['catalog_id'] . "&categ_id=" . $data_array['category_id']
                            . "&subcateg_id=" . $data_array['subcategory_id'] . "&lang=" . $this->registry->sitelang
                        );
                    }
                    exit();
                }
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user . '_' . $error;
                $this->shop_view($error, $titles, $cache_category, $cache);
            }
        }
        $type = false;
        $titles = false;
        $template = 'shop_items.html';
        $this->shop_view($type, $titles, $cache_category, $cache, $template);
    }


    public function item()
    {
        $data_array['item_id'] = 0;
        $cache_id = 0;
        if (isset($_GET['item_id'])) {
            $data_array['item_id'] = intval($_GET['item_id']);
        }
        if (isset($_GET['cat_id'])) {
            $data_array['catalog_id'] = intval($_GET['cat_id']);
        }
        if (isset($_GET['categ_id'])) {
            $data_array['category_id'] = intval($_GET['categ_id']);
        }
        if (isset($_GET['subcateg_id'])) {
            $data_array['subcategory_id'] = intval($_GET['subcateg_id']);
        }
        if (isset($_GET['num_page']) && intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        if (isset($data_array['catalog_id']) && $data_array['catalog_id'] != 0) {
            if (isset($data_array['subcategory_id']) && isset($data_array['category_id']) && $data_array['category_id'] != 0) {
                if ($data_array['subcategory_id'] != 0) {
                    $cache_id = "cat" . $data_array['catalog_id'] . "|categ" . $data_array['category_id'] . "|subcateg" . $data_array['subcategory_id'] . "|item"
                                . $data_array['item_id'];
                }
            } elseif (isset($data_array['category_id'])) {
                if ($data_array['category_id'] != 0) {
                    $cache_id = "cat" . $data_array['catalog_id'] . "|categ" . $data_array['category_id'] . "|item" . $data_array['item_id'];
                }
            } else {
                $cache_id = "cat" . $data_array['catalog_id'] . "|item" . $data_array['item_id'];
            }
        } elseif (isset($data_array['catalog_id']) && $data_array['catalog_id'] == 0) {
            $cache_id = 0;
        } else {
            $cache_id = "home|item" . $data_array['item_id'];
        }
        $cache_category = 'modules|shop|' . $cache_user . '|' . $cache_id;
        $cache = $data_array['num_page'];
        $titles = false;
        if (!$this->isCached("index.html", $this->registry->sitelang . '|' . $cache_category . '|' . $cache . '|' . $this->registry->language)) {
            $this->model_shop->item($data_array);
            $error = $this->model_shop->get_property_value('error');
            $result = $this->model_shop->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'not_all_data4' || $error === 'not_all_data' || $error === 'not_all_data2' || $error === 'not_all_data3' || $error === 'categories_error') {
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
                $cache_category = false;
                $cache = $cache_user . '_' . $error;
                $this->shop_view($error, $titles, $cache_category, $cache);
            }
            if (isset($result['shop_item_all']) && $result['shop_item_all'] === 'no') {
                $titles['title'] = '_SHOP_MODULE_METATITLE';
                $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
            }
        }
        $type = false;
        $template = 'shop_item.html';
        $this->shop_view($type, $titles, $cache_category, $cache, $template);
    }


    public function cart()
    {
        $cache = "other";

        if ($this->registry->main_class->is_admin()) {
            $cache = "admin";
        }

        $titles['title'] = '_SHOP_MODULE_CARTTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $this->model_shop->cart();
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);
        $type = false;
        $cacheCategory = 'modules|shop|cart|display';
        $template = 'shop_cart.html';
        $this->shop_view($type, $titles, $cacheCategory, $cache, $template);
    }


    public function checkout()
    {
        $cache = "other";

        if ($this->registry->main_class->is_admin()) {
            $cache = "admin";
        }

        $titles['title'] = '_SHOP_MODULE_PROCESSORDERTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $this->model_shop->checkout();
        $error = $this->model_shop->get_property_value('error');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);

        if (!empty($error)) {
            $cacheCategory = false;
            $cache = __FUNCTION__ . '_' . $cache . '_' . $error;
            $this->shop_view($error, $titles, $cacheCategory, $cache);
        }

        $type = false;
        $cacheCategory = 'modules|shop|checkout|display';
        $template = 'shop_cart.html';
        $this->shop_view($type, $titles, $cacheCategory, $cache, $template);
    }


    public function checkout2()
    {
        $dataArray = false;
        $keys = [
            'shop_order_delivery'    => 'integer',
            'shop_order_pay'         => 'integer',
            'shop_ship_name'         => 'string',
            'shop_ship_telephone'    => 'string',
            'shop_ship_email'        => 'string',
            'shop_ship_country'      => 'string',
            'shop_ship_state'        => 'string',
            'shop_ship_city'         => 'string',
            'shop_ship_street'       => 'string',
            'shop_ship_homenumber'   => 'string',
            'shop_ship_flatnumber'   => 'string',
            'shop_ship_postalcode'   => 'string',
            'shop_ship_addaddrnotes' => 'string',
            'shop_order_comments'    => 'string',
        ];
        foreach ($keys as $key => $valueType) {

            if (isset($_POST[$key])) {
                $dataArray[$key] = $valueType === 'string' ? trim($_POST[$key]) : intval($_POST[$key]);
            }

        }

        $cache = "other";

        if ($this->registry->main_class->is_admin()) {
            $cache = "admin";
        }

        $titles['title'] = '_SHOP_MODULE_PROCESSORDERTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $this->configLoad($this->registry->sitelang . "/modules/shop/shop.conf");
        $dataArray['payment_error_notify_mail_subject'] = $this->getConfigVars('_SHOP_MODULE_PAYMENT_ERROR_NOTIFY_MAIL_SUBJECT');
        $dataArray['payment_error_notify_mail_content'] = $this->fetch("modules/shop/shop_payment_error_notify_email.html");
        $this->model_shop->checkout2($dataArray);
        $error = $this->model_shop->get_property_value('error');
        $errorArray = $this->model_shop->get_property_value('error_array');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);

        if (!empty($error)) {

            if (!empty($errorArray)) {
                $this->assign("errortext", $errorArray);
            }

            $cacheCategory = false;
            $cache = __FUNCTION__ . '_' . $cache . '_' . $error;
            $this->shop_view($error, $titles, $cacheCategory, $cache);
        }

        $type = false;
        $cacheCategory = 'modules|shop|checkout2|display';
        $template = 'shop_cart.html';
        $this->shop_view($type, $titles, $cacheCategory, $cache, $template);
    }


    /**
     * Create an order
     */
    public function insert()
    {
        $dataArray = false;
        $keys = [
            'shop_order_delivery'    => 'integer',
            'shop_order_pay'         => 'integer',
            'shop_ship_name'         => 'string',
            'shop_ship_telephone'    => 'string',
            'shop_ship_email'        => 'string',
            'shop_ship_country'      => 'string',
            'shop_ship_state'        => 'string',
            'shop_ship_city'         => 'string',
            'shop_ship_street'       => 'string',
            'shop_ship_homenumber'   => 'string',
            'shop_ship_flatnumber'   => 'string',
            'shop_ship_postalcode'   => 'string',
            'shop_ship_addaddrnotes' => 'string',
            'shop_order_comments'    => 'string',
            'captcha'                => 'string',
        ];
        foreach ($keys as $key => $valueType) {
            if (isset($_POST[$key])) {
                $dataArray[$key] = $valueType === 'string' ? trim($_POST[$key]) : intval($_POST[$key]);
            }
        }

        $cache = "other";

        if ($this->registry->main_class->is_admin()) {
            $cache = "admin";
        }

        $titles['title'] = '_SHOP_MODULE_PROCESSORDERTITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $this->configLoad($this->registry->sitelang . "/modules/shop/shop.conf");
        $dataArray['payment_error_notify_mail_subject'] = $this->getConfigVars('_SHOP_MODULE_PAYMENT_ERROR_NOTIFY_MAIL_SUBJECT');
        $dataArray['payment_error_notify_mail_content'] = $this->fetch("modules/shop/shop_payment_error_notify_email.html");
        $dataArray['order_mail_subject'] = $this->getConfigVars('_SHOP_MODULE_ORDER_MAIL_SUBJECT');
        $dataArray['order_mail_content'] = $this->fetch("modules/shop/shop_order_email.html");

        if ($this->model_shop->orderNotifyMail()) {
            $dataArray['notify_order_mail_subject'] = $this->getConfigVars('_SHOP_MODULE_NOTIFY_ORDER_MAIL_SUBJECT');
            $dataArray['notify_order_mail_content'] = $this->fetch("modules/shop/shop_order_notify_email.html");
        }

        $this->model_shop->insert($dataArray);
        $error = $this->model_shop->get_property_value('error');
        $errorArray = $this->model_shop->get_property_value('error_array');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);

        if (!empty($error)) {

            if (!empty($errorArray)) {
                $this->assign("errortext", $errorArray);
            }

            $cacheCategory = false;
            $cache = __FUNCTION__ . '_' . $cache . '_' . $error;
            $this->shop_view($error, $titles, $cacheCategory, $cache);
        }

        $type = false;
        $template = 'shop_cart.html';

        if (isset($result['shop_cart_display_items']) && $result['shop_cart_display_items'] === 'no') {
            $cacheCategory = 'modules|shop|checkout|display';
        } else {
            $cacheCategory = 'modules|shop';
            $cache = $cache . '_' . __FUNCTION__ . '_ok';
        }

        $this->shop_view($type, $titles, $cacheCategory, $cache, $template);
    }


    public function pay_online()
    {
        $this->paymentProcess('payOnline', __FUNCTION__);
    }


    public function pay_done()
    {
        $this->paymentProcess('payDone', __FUNCTION__);
    }


    public function pay_error()
    {
        $this->paymentProcess('payError', __FUNCTION__);
    }


    private function paymentProcess($method, $cache)
    {
        $dataArray = false;
        $keys = [
            'order_id' => 'integer',
            'hash'     => 'string',
        ];
        foreach ($keys as $key => $valueType) {
            if (isset($_GET[$key])) {
                $dataArray[$key] = $valueType === 'string' ? trim($_GET[$key]) : intval($_GET[$key]);
            }
        }

        $titles['title'] = '_SHOP_MODULE_PAY_ONLINE_TITLE';
        $titles['description'] = '_SHOP_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_SHOP_MODULE_METAKEYWORDS';
        $this->configLoad($this->registry->sitelang . "/modules/shop/shop.conf");
        $dataArray['payment_error_notify_mail_subject'] = $this->getConfigVars('_SHOP_MODULE_PAYMENT_ERROR_NOTIFY_MAIL_SUBJECT');
        $dataArray['payment_error_notify_mail_content'] = $this->fetch("modules/shop/shop_payment_error_notify_email.html");
        $this->model_shop->$method($dataArray);
        $error = $this->model_shop->get_property_value('error');
        $errorArray = $this->model_shop->get_property_value('error_array');
        $result = $this->model_shop->get_property_value('result');
        $this->assign_array($result);

        if (!empty($error)) {

            if (!empty($errorArray)) {
                $this->assign("errortext", $errorArray);
            }

            $cacheCategory = false;
            $cache = $cache . '_' . $error;
            $this->shop_view($error, $titles, $cacheCategory, $cache);
        }

        $type = !empty($result['shop_message']) ? $result['shop_message'] : false;
        $cacheCategory = 'modules|shop';
        $template = !empty($result['payment_template']) ? $result['payment_template'] : false;
        $this->shop_view($type, $titles, $cacheCategory, $cache, $template);
    }
}
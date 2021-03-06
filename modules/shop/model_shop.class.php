<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class shop extends model_base
{


    const SYSTEMDK_SHOP_DEFAULT_PAYMENT_DESCRIPTION = 'Make an order on site $site_url. Order number $order_number';

    private $error;
    private $error_array;
    private $result;
    /**
     * @var shop_payment_log
     */
    private $paymentLog;
    private $systemdk_shop_cart;
    private $systemdk_shop_items;
    private $systemdk_shop_total_price;
    private $systemdk_shop_cart_item_additional_params;
    private $systemdk_shop_catalogs_order;
    private $systemdk_shop_homeitems_perpage;
    private $systemdk_shop_homeitems_order;
    private $systemdk_shop_categories_order;
    private $systemdk_shop_itemimage_path;
    private $systemdk_shop_itemimage_position;
    private $systemdk_shop_itemimage_width;
    private $systemdk_shop_itemimage_width2;
    private $systemdk_shop_image_path;
    private $systemdk_shop_image_position;
    private $systemdk_shop_image_width;
    private $systemdk_shop_items_columns;
    private $systemdk_shop_items_underparent;
    private $systemdk_shop_valuta;
    private $systemdk_shop_catitems_perpage;
    private $systemdk_shop_subcategories_order;
    private $systemdk_shop_catitems_order;
    private $systemdk_shop_categitems_perpage;
    private $systemdk_shop_categitems_order;
    private $systemdk_shop_subcategitems_order;
    private $systemdk_shop_subcategitems_perpage;
    private $systemdk_shop_itemimage_width3;
    private $systemdk_shop_itemimage_height3;
    private $shop_cart_items_all;
    private $systemdk_shop_antispam_ipaddr;
    private $systemdk_shop_antispam_num;
    /**
     * @var int
     */
    private $systemdk_shop_order_notify_email;
    /**
     * @var string
     */
    private $systemdk_shop_order_notify_type;
    /**
     * @var string
     */
    private $systemdk_shop_order_notify_custom_mailbox;
    /**
     * @var int
     */
    private $systemdk_shop_log_other_payment_errors = 1; // TODO: Add to shop config
    private $shop_order_delivery_id;
    private $shop_order_pay_id;
    private $itemAdditionalParams = [];


    public function __construct($registry)
    {
        parent::__construct($registry);
        require_once('shop_config.inc');
        $this->systemdk_shop_cart = 'systemdk_shop_cart_' . $this->registry->sitelang;
        $this->systemdk_shop_items = 'systemdk_shop_items_' . $this->registry->sitelang;
        $this->systemdk_shop_total_price = 'systemdk_shop_total_price_' . $this->registry->sitelang;
        $this->systemdk_shop_cart_item_additional_params = 'additional_params';
        $this->systemdk_shop_catalogs_order = SYSTEMDK_SHOP_CATALOGS_ORDER;
        $this->systemdk_shop_homeitems_perpage = SYSTEMDK_SHOP_HOMEITEMS_PERPAGE;
        $this->systemdk_shop_homeitems_order = SYSTEMDK_SHOP_HOMEITEMS_ORDER;
        $this->systemdk_shop_categories_order = SYSTEMDK_SHOP_CATEGORIES_ORDER;
        $this->systemdk_shop_itemimage_path = SYSTEMDK_SHOP_ITEMIMAGE_PATH;
        $this->systemdk_shop_itemimage_position = SYSTEMDK_SHOP_ITEMIMAGE_POSITION;
        $this->systemdk_shop_itemimage_width = SYSTEMDK_SHOP_ITEMIMAGE_WIDTH;
        $this->systemdk_shop_itemimage_width2 = SYSTEMDK_SHOP_ITEMIMAGE_WIDTH2;
        $this->systemdk_shop_image_path = SYSTEMDK_SHOP_IMAGE_PATH;
        $this->systemdk_shop_image_position = SYSTEMDK_SHOP_IMAGE_POSITION;
        $this->systemdk_shop_image_width = SYSTEMDK_SHOP_IMAGE_WIDTH;
        $this->systemdk_shop_items_columns = SYSTEMDK_SHOP_ITEMS_COLUMNS;
        $this->systemdk_shop_items_underparent = SYSTEMDK_SHOP_ITEMS_UNDERPARENT;
        $this->systemdk_shop_valuta = SYSTEMDK_SHOP_VALUTA;
        $this->systemdk_shop_catitems_perpage = SYSTEMDK_SHOP_CATITEMS_PERPAGE;
        $this->systemdk_shop_subcategories_order = SYSTEMDK_SHOP_SUBCATEGORIES_ORDER;
        $this->systemdk_shop_catitems_order = SYSTEMDK_SHOP_CATITEMS_ORDER;
        $this->systemdk_shop_categitems_perpage = SYSTEMDK_SHOP_CATEGITEMS_PERPAGE;
        $this->systemdk_shop_categitems_order = SYSTEMDK_SHOP_CATEGITEMS_ORDER;
        $this->systemdk_shop_subcategitems_order = SYSTEMDK_SHOP_SUBCATEGITEMS_ORDER;
        $this->systemdk_shop_subcategitems_perpage = SYSTEMDK_SHOP_SUBCATEGITEMS_PERPAGE;
        $this->systemdk_shop_itemimage_width3 = SYSTEMDK_SHOP_ITEMIMAGE_WIDTH3;
        $this->systemdk_shop_itemimage_height3 = SYSTEMDK_SHOP_ITEMIMAGE_HEIGHT3;
        $this->systemdk_shop_antispam_ipaddr = SYSTEMDK_SHOP_ANTISPAM_IPADDR;
        $this->systemdk_shop_antispam_num = SYSTEMDK_SHOP_ANTISPAM_NUM;
        $this->systemdk_shop_order_notify_email = SYSTEMDK_SHOP_ORDER_NOTIFY_EMAIL;
        $this->systemdk_shop_order_notify_type = SYSTEMDK_SHOP_ORDER_NOTIFY_TYPE;
        $this->systemdk_shop_order_notify_custom_mailbox = SYSTEMDK_SHOP_ORDER_NOTIFY_CUSTOM_MAILBOX;
        $this->paymentLog = singleton::getinstance('shop_payment_log', $registry);
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function shop_header($data_array)
    {
        if ((isset($data_array['path']) && isset($data_array['func']) && $this->registry->main_class->format_striptags(trim($data_array['path'])) == "shop"
             && $this->registry->main_class->format_striptags(trim($data_array['func'])) == "cart")
        ) {
            if (isset($data_array['shop_new']) && intval($data_array['shop_new']) != 0) {
                $systemdk_shop_new_item = intval($data_array['shop_new']);

                if (!isset($_SESSION[$this->systemdk_shop_cart])) {
                    $_SESSION[$this->systemdk_shop_cart] = [];
                    $_SESSION[$this->systemdk_shop_items] = 0;
                    $_SESSION[$this->systemdk_shop_total_price] = 0.00;
                }

                if (isset($_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item])) {
                    $_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item]['amount']++;
                    //$_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item]++;
                } else {
                    $_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item] = [];
                    $_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item]['amount'] = 1;
                    $_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item][$this->systemdk_shop_cart_item_additional_params] = [];
                    //$_SESSION[$this->systemdk_shop_cart][$systemdk_shop_new_item] = 1;
                }

                if (!empty($data_array['shop_additional_params'][$systemdk_shop_new_item])
                    && $this->checkItemAdditionalParams(
                        $systemdk_shop_new_item, $data_array['shop_additional_params'][$systemdk_shop_new_item]
                    )
                ) {
                    $this->addItemAdditionalParams($systemdk_shop_new_item, $data_array['shop_additional_params'][$systemdk_shop_new_item]);
                }

                $calculate_result = $this->shop_calculate_price($_SESSION[$this->systemdk_shop_cart]);
                $_SESSION[$this->systemdk_shop_total_price] = $calculate_result['2'];
                $_SESSION[$this->systemdk_shop_items] = $calculate_result['1'];
            }

            if (isset($data_array['shop_save'])) {
                foreach ($_SESSION[$this->systemdk_shop_cart] as $item_id => $data) {
                    //(isset($data_array['shop_items'][$item_id]) && intval($data_array['shop_items'][$item_id]) == 0) ||
                    if (!isset($data_array['shop_items'][$item_id]) || intval($data_array['shop_items'][$item_id]) == 0) {
                        unset($_SESSION[$this->systemdk_shop_cart][$item_id]);
                    } else {
                        $previousAmount = $_SESSION[$this->systemdk_shop_cart][$item_id]['amount'];
                        $_SESSION[$this->systemdk_shop_cart][$item_id]['amount'] = intval($data_array['shop_items'][$item_id]);

                        if (!empty($_SESSION[$this->systemdk_shop_cart][$item_id][$this->systemdk_shop_cart_item_additional_params])) {
                            $count_additional_params = count($_SESSION[$this->systemdk_shop_cart][$item_id][$this->systemdk_shop_cart_item_additional_params]);

                            if ($_SESSION[$this->systemdk_shop_cart][$item_id]['amount'] < $count_additional_params) {
                                for ($i = $_SESSION[$this->systemdk_shop_cart][$item_id]['amount'], $size = $count_additional_params; $i < $size; $i++) {
                                    unset($_SESSION[$this->systemdk_shop_cart][$item_id][$this->systemdk_shop_cart_item_additional_params][$i]);
                                }
                            }
                        }

                        if (!empty($data_array['shop_additional_params'][$item_id]) && $previousAmount < $_SESSION[$this->systemdk_shop_cart][$item_id]['amount']
                            && $this->checkItemAdditionalParams(
                                $item_id, $data_array['shop_additional_params'][$item_id]
                            )
                        ) {
                            for ($i = $previousAmount, $size = $_SESSION[$this->systemdk_shop_cart][$item_id]['amount']; $i < $size; $i++) {
                                $this->addItemAdditionalParams($item_id, $data_array['shop_additional_params'][$item_id]);
                            }
                        }
                        //$_SESSION[$this->systemdk_shop_cart][$item_id] = $data_array['shop_items'][$item_id];
                    }
                }
                $calculate_result = $this->shop_calculate_price($_SESSION[$this->systemdk_shop_cart]);
                $_SESSION[$this->systemdk_shop_total_price] = $calculate_result['2'];
                $_SESSION[$this->systemdk_shop_items] = $calculate_result['1'];
            }
        }

        if (!isset($_SESSION[$this->systemdk_shop_items])) {
            $_SESSION[$this->systemdk_shop_items] = 0;
        }

        if (!isset($_SESSION[$this->systemdk_shop_total_price])) {
            $_SESSION[$this->systemdk_shop_total_price] = 0.00;
        }

        $result['shop_carttotal_items'] = $_SESSION[$this->systemdk_shop_items];
        $result['shop_carttotal_price'] = $this->registry->main_class->number_format($_SESSION[$this->systemdk_shop_total_price]);

        if ($this->registry->main_class->is_admin()) {
            $result['shop_carttotal_display'] = "no";
        } else {
            $result['shop_carttotal_display'] = "yes";
        }

        return $result;
    }


    private function shop_calculate_price($cart = 0)
    {
        $systemdk_shop_items = 0;
        $systemdk_shop_price = 0.0;

        if ($cart != 0 && is_array($cart) && count($cart) > 0) {
            $i = 0;
            $item_id = null;
            foreach ($cart as $cart_item_id => $data) {

                if (isset($cart_item_id) && intval($cart_item_id) != 0) {

                    if ($i == 0) {
                        $item_id = "'" . intval($cart_item_id) . "'";
                    } else {
                        $item_id .= ",'" . intval($cart_item_id) . "'";
                    }

                    $i++;
                }
            }

            if (isset($item_id)) {
                $from = "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                        . " c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_categories_"
                        . $this->registry->sitelang . " d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN " . PREFIX
                        . "_catalogs_"
                        . $this->registry->sitelang . " f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX
                        . "_subcategories_" . $this->registry->sitelang
                        . " e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_categories_"
                        . $this->registry->sitelang . " g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX
                        . "_catalogs_" . $this->registry->sitelang . " h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL WHERE a.item_id IN ("
                        . $item_id
                        . ") and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL) or (a.item_category_id is NOT NULL) or (a.item_subcategory_id is NOT NULL)) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
                $sql = "SELECT a.item_id,a.item_price,a.item_show,a.item_price_discounted " . $from;
                $result = $this->db->Execute($sql);

                if ($result) {

                    if (isset($result->fields['0'])) {
                        $row_exist = intval($result->fields['0']);
                    } else {
                        $row_exist = 0;
                    }

                    if ($row_exist > 0) {
                        while (!$result->EOF) {
                            $item_id = intval($result->fields['0']);
                            $systemdk_shop_item_price = floatval($result->fields['1']);
                            $systemdk_shop_item_price_discounted = floatval($result->fields['3']);

                            if (isset($cart[$item_id]['amount'])) {
                                $item_qty = $cart[$item_id]['amount'];
                            } else {
                                $item_qty = 0;
                            }

                            if ($systemdk_shop_item_price_discounted > 0) {
                                $systemdk_shop_price += $systemdk_shop_item_price_discounted * $item_qty;
                            } else {
                                $systemdk_shop_price += $systemdk_shop_item_price * $item_qty;
                            }

                            $systemdk_shop_items += $item_qty;
                            $result->MoveNext();
                        }
                    }
                }
            }
        }

        return [1 => $systemdk_shop_items, 2 => $systemdk_shop_price];
    }


    /**
     * Set in session selected additional params for every item in order
     *
     * @param int   $itemId
     * @param array $additionalParams
     */
    private function addItemAdditionalParams($itemId, $additionalParams)
    {
        $countAdditionalParams = 0;

        if (isset($_SESSION[$this->systemdk_shop_cart][$itemId][$this->systemdk_shop_cart_item_additional_params])) {
            $countAdditionalParams = count($_SESSION[$this->systemdk_shop_cart][$itemId][$this->systemdk_shop_cart_item_additional_params]);
        }

        if (!empty($additionalParams)) {
            $_SESSION[$this->systemdk_shop_cart][$itemId][$this->systemdk_shop_cart_item_additional_params][$countAdditionalParams] = $additionalParams;
        }
    }


    /**
     * Check posted item additional params before save it in session
     *
     * @param int   $itemId
     * @param array $additionalParams
     *
     * @return bool
     */
    private function checkItemAdditionalParams($itemId, $additionalParams)
    {
        if (!is_array($additionalParams)) {
            return false;
        }

        $availableItemAdditionalParams = $this->getItemAdditionalParams($itemId);

        if (empty($availableItemAdditionalParams) || !is_array($availableItemAdditionalParams)) {
            return false;
        }

        foreach ($additionalParams as $groupKey => $groupData) {
            $groupKey = intval($groupKey);

            if (empty($availableItemAdditionalParams['groups'][$groupKey]['group_id'])
                || empty($groupData)
                || !is_array($groupData)
                || (in_array($availableItemAdditionalParams['groups'][$groupKey]['type_id'], [2, 3]) && count($groupData) > 1)
            ) {
                return false;
            }

            foreach ($groupData as $paramId) {
                $paramId = intval($paramId);

                if (empty($availableItemAdditionalParams['params'][$groupKey][$paramId]['param_id'])) {
                    return false;
                }
            }
        }

        return true;
    }


    public function index($num_page = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (intval($num_page) !== 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_shop_homeitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if (empty($this->systemdk_shop_catalogs_order)) {
            $this->systemdk_shop_catalogs_order = 'catalog_position';
        }
        if (empty($this->systemdk_shop_categories_order)) {
            $this->systemdk_shop_categories_order = 'category_position';
        }
        if (empty($this->systemdk_shop_homeitems_order)) {
            $this->systemdk_shop_homeitems_order = 'item_position';
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        $sql = "SELECT a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_date,b.category_id,b.category_name FROM " . PREFIX
               . "_catalogs_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
               . " b ON a.catalog_id=b.cat_catalog_id and (b.category_id is NULL or b.category_show = '1') WHERE a.catalog_show = '1' ORDER BY a."
               . $this->systemdk_shop_catalogs_order . ",b." . $this->systemdk_shop_categories_order;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'categories_error';

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist > 0) {
            while (!$result->EOF) {
                $catalog_id = intval($result->fields['0']);
                $catalog_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                if (!isset($catalog_img) || $catalog_img == "") {
                    $catalog_img = "no";
                }
                $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                //$catalog_date = intval($result->fields['3']);
                //$catalog_date = date("d.m.y H:i",$catalog_date);
                if (isset($result->fields['4']) && intval($result->fields['4']) > 0) {
                    $category_id = intval($result->fields['4']);
                    $category_name = $this->registry->main_class->format_htmlspecchars(
                        $this->registry->main_class->extracting_data($result->fields['5'])
                    );
                    $categories_all[$catalog_id][] = [
                        "category_id"   => $category_id,
                        "category_name" => $category_name,
                    ];
                } else {
                    $categories_all[$catalog_id] = "no";
                }
                $catalogs_all[$catalog_id] = [
                    "catalog_id"     => $catalog_id,
                    "catalog_img"    => $catalog_img,
                    "catalog_name"   => $catalog_name,
                    "categories_all" => $categories_all[$catalog_id],
                ];
                $result->MoveNext();
            }
            $this->result['shop_catalogs_all'] = $catalogs_all;
            $this->result['shop_total_catalogs'] = count($catalogs_all);
            $this->result['systemdk_shop_image_position'] = $this->systemdk_shop_image_position;
            $this->result['systemdk_shop_image_path'] = $this->systemdk_shop_image_path;
            $this->result['systemdk_shop_image_width'] = $this->systemdk_shop_image_width;
            $this->result['systemdk_shop_items_columns'] = $this->systemdk_shop_items_columns;
            $this->result['systemdk_shop_items_underparent'] = $this->systemdk_shop_items_underparent;
            $this->result['systemdk_shop_items_columnsperc'] = intval(100 / $this->systemdk_shop_items_columns);
        } else {
            $this->result['shop_catalogs_all'] = "no";
            $this->result['shop_total_catalogs'] = "0";
        }
        $from = "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN " . PREFIX . "_categories_"
                . $this->registry->sitelang . " d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN "
                . PREFIX
                . "_catalogs_" . $this->registry->sitelang
                . " f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN " . PREFIX . "_subcategories_"
                . $this->registry->sitelang
                . " e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN "
                . PREFIX . "_categories_" . $this->registry->sitelang
                . " g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN " . PREFIX . "_catalogs_"
                . $this->registry->sitelang
                . " h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' WHERE a.item_show = '1' and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL and a.item_display = '3') or (a.item_category_id is NOT NULL and a.item_display = '3') or (a.item_subcategory_id is NOT NULL and a.item_display = '3')) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items " . $from;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql =
            "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id "
            . $from . " ORDER BY a." . $this->systemdk_shop_homeitems_order;
        $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist == 0 && $num_page > 1) {
                $this->error = 'unknown_page';

                return;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if (!isset($item_img) || $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = $this->registry->main_class->number_format($result->fields['3']);
                    $item_price_discounted = $this->registry->main_class->number_format($result->fields['4']);
                    $item_catalog_id = intval($result->fields['5']);
                    if ($item_catalog_id > 0) {
                        $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                    } else {
                        $item_catalog_name = "no";
                    }
                    $item_category_id = intval($result->fields['6']);
                    if ($item_category_id > 0) {
                        $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                        $item_categ_cat_id = intval($result->fields['20']);
                    } else {
                        $item_category_name = "no";
                        $item_categ_cat_id = 0;
                    }
                    $item_subcategory_id = intval($result->fields['7']);
                    if ($item_subcategory_id > 0) {
                        $item_subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['19']));
                        $item_subcateg_categ_id = intval($result->fields['21']);
                        $item_subcateg_cat_id = intval($result->fields['22']);
                    } else {
                        $item_subcategory_name = "no";
                        $item_subcateg_categ_id = 0;
                        $item_subcateg_cat_id = 0;
                    }
                    $item_short_description = $this->registry->main_class->extracting_data($result->fields['8']);
                    $item_quantity = intval($result->fields['9']);
                    $item_quantity_unlim = intval($result->fields['10']);
                    $item_quantity_param = intval($result->fields['11']);
                    $item_display = intval($result->fields['12']);
                    $item_date = intval($result->fields['13']);
                    $item_date = date("d.m.y H:i", $item_date);
                    $items_all[] = [
                        "item_id"                => $item_id,
                        "item_img"               => $item_img,
                        "item_name"              => $item_name,
                        "item_price"             => $item_price,
                        "item_price_discounted"  => $item_price_discounted,
                        "item_catalog_id"        => $item_catalog_id,
                        "item_category_id"       => $item_category_id,
                        "item_subcategory_id"    => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity"          => $item_quantity,
                        "item_quantity_unlim"    => $item_quantity_unlim,
                        "item_quantity_param"    => $item_quantity_param,
                        "item_display"           => $item_display,
                        "item_date"              => $item_date,
                        "item_catalog_name"      => $item_catalog_name,
                        "item_category_name"     => $item_category_name,
                        "item_subcategory_name"  => $item_subcategory_name,
                        "item_categ_cat_id"      => $item_categ_cat_id,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id"   => $item_subcateg_cat_id,
                        "item_additional_params" => $this->getItemAdditionalParams($item_id),
                    ];
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if (isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if ($num_pages > 1) {
                    if ($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for ($i = 1; $i < $num_pages + 1; $i++) {
                        if ($i == $num_page) {
                            $html[] = ["number" => $i, "param1" => "1"];
                        } else {
                            $pagelink = 5;
                            if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "2"];
                            }
                            if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "3"];
                            }
                            if (($i == 1) && ($num_page > $pagelink + 1)) {
                                $html[] = ["number" => $i, "param1" => "4"];
                            }
                        }
                    }
                    if ($num_page < $num_pages) {
                        $nextpage = $num_page + 1;
                        $this->result['nextpage'] = $nextpage;
                    } else {
                        $this->result['nextpage'] = "no";
                    }
                    $this->result['html'] = $html;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['shop_items_all'] = "no";
                $this->result['shop_total_items'] = "0";
                $this->result['html'] = "no";
            }
        } else {
            $this->result['shop_items_all'] = "no";
            $this->result['shop_total_items'] = "0";
            $this->result['html'] = "no";
        }
    }


    /**
     * Get item additional params by item id
     *
     * @param int $itemId
     *
     * @return array|bool
     */
    private function getItemAdditionalParams($itemId)
    {
        if (isset($this->itemAdditionalParams[$itemId])) {
            return $this->itemAdditionalParams[$itemId];
        }

        $this->itemAdditionalParams[$itemId] = false;
        $sql = "SELECT "
               . "e.id as item_param_type_id,"
               . "e.type_name as item_param_type_name,"
               . "d.id as item_param_group_id,"
               . "d.group_name as item_param_group_name,"
               . "c.id as item_param_value_id,"
               . "c.param_value as item_param_value "
               . "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a "
               . "LEFT OUTER JOIN " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " b ON a.item_id = b.item_id "
               . "LEFT OUTER JOIN " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " c ON b.item_param_id=c.id and c.param_status = '1' and "
               . "c.param_group_id IN (SELECT id FROM " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " where group_status = '1') "
               . "LEFT OUTER JOIN " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " d ON c.param_group_id = d.id and d.group_status = '1' "
               . "LEFT OUTER JOIN " . PREFIX . "_s_add_par_g_types e ON d.group_type_id = e.id "
               . "WHERE a.item_id = '" . $itemId . "'";
        $result = $this->db->Execute($sql);
        if ($result) {

            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }

            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $item_additional_param_type_id = intval($result->fields['0']);
                    $item_additional_param_type_name =
                        $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $item_additional_param_group_id = intval($result->fields['2']);
                    $item_additional_param_group_name =
                        $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $item_additional_param_value_id = intval($result->fields['4']);
                    $item_additional_param_value = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));

                    if ($item_additional_param_type_id > 0 && $item_additional_param_group_id > 0 && $item_additional_param_value_id > 0) {
                        $this->itemAdditionalParams[$itemId]['groups'][$item_additional_param_group_id] = [
                            'group_id'   => $item_additional_param_group_id,
                            'group_name' => $item_additional_param_group_name,
                            'type_id'    => $item_additional_param_type_id,
                            'type_name'  => $item_additional_param_type_name,
                        ];
                        $this->itemAdditionalParams[$itemId]['params'][$item_additional_param_group_id][$item_additional_param_value_id] = [
                            'param_id'    => $item_additional_param_value_id,
                            'param_value' => $item_additional_param_value,
                        ];
                    }
                    $result->MoveNext();
                }
            }
        }

        return $this->itemAdditionalParams[$itemId];
    }


    public function categs($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if (!isset($array['catalog_id']) || (isset($array['catalog_id']) && intval($array['catalog_id']) == 0)) {
            $this->error = 'not_all_data';

            return;
        }
        $this->result['shop_catalog_id'] = $catalog_id;
        $num_string_rows = $this->systemdk_shop_catitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if (!isset($this->systemdk_shop_categories_order)) {
            $this->systemdk_shop_categories_order = 'category_position';
        }
        if (!isset($this->systemdk_shop_subcategories_order)) {
            $this->systemdk_shop_subcategories_order = 'subcategory_position';
        }
        if (!isset($this->systemdk_shop_catitems_order)) {
            $this->systemdk_shop_catitems_order = 'item_position';
        }
        $sql =
            "SELECT b.category_id,b.category_img,b.category_name,b.category_date,a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_description,a.catalog_meta_title,a.catalog_meta_keywords,a.catalog_meta_description,c.subcategory_id,c.subcategory_name FROM "
            . PREFIX . "_catalogs_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
            . " b ON a.catalog_id=b.cat_catalog_id and (b.category_id is NULL or b.category_show = '1') LEFT OUTER JOIN " . PREFIX . "_subcategories_"
            . $this->registry->sitelang . " c ON b.category_id=c.subcategory_cat_id and (c.subcategory_id is NULL or c.subcategory_show = '1') WHERE a.catalog_id = "
            . $catalog_id . " and a.catalog_show = '1' ORDER BY b." . $this->systemdk_shop_categories_order . ",c." . $this->systemdk_shop_subcategories_order;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'categories_error';

            return;
        }
        if (isset($result->fields['4'])) {
            $row_exist = intval($result->fields['4']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'not_find_catalog';

            return;
        }
        $catalog_id = intval($result->fields['4']);
        $catalog_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        if (!isset($catalog_img) || $catalog_img == "") {
            $catalog_img = "no";
        }
        $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $catalog_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
        $catalog_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
        $catalog_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
        $catalog_meta_description = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['10'])
        );
        $this->result['shop_catalog_img'] = $catalog_img;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->result['shop_catalog_description'] = $catalog_description;
        $this->registry->main_class->set_sitemeta($catalog_meta_title, $catalog_meta_description, $catalog_meta_keywords);
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist > 0) {
            while (!$result->EOF) {
                $category_id = intval($result->fields['0']);
                $category_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                if (!isset($category_img) || $category_img == "") {
                    $category_img = "no";
                }
                $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                //$category_date = intval($result->fields['3']);
                //$category_date = date("d.m.y H:i",$category_date);
                if (isset($result->fields['11']) && intval($result->fields['11']) > 0) {
                    $subcategory_id = intval($result->fields['11']);
                    $subcategory_name = $this->registry->main_class->format_htmlspecchars(
                        $this->registry->main_class->extracting_data($result->fields['12'])
                    );
                    $subcategories_all[$category_id][] = [
                        "subcategory_id"   => $subcategory_id,
                        "subcategory_name" => $subcategory_name,
                    ];
                } else {
                    $subcategories_all[$category_id] = "no";
                }
                $categories_all[$category_id] = [
                    "category_id"       => $category_id,
                    "category_img"      => $category_img,
                    "category_name"     => $category_name,
                    "subcategories_all" => $subcategories_all[$category_id],
                ];
                $result->MoveNext();
            }
            $this->result['shop_categories_all'] = $categories_all;
            $this->result['shop_total_categories'] = count($categories_all);
            $this->result['systemdk_shop_image_position'] = $this->systemdk_shop_image_position;
            $this->result['systemdk_shop_image_path'] = $this->systemdk_shop_image_path;
            $this->result['systemdk_shop_image_width'] = $this->systemdk_shop_image_width;
            $this->result['systemdk_shop_items_columns'] = $this->systemdk_shop_items_columns;
            $this->result['systemdk_shop_items_underparent'] = $this->systemdk_shop_items_underparent;
            $this->result['systemdk_shop_items_columnsperc'] = intval(100 / $this->systemdk_shop_items_columns);
        } else {
            $this->result['shop_categories_all'] = "no";
            $this->result['shop_total_categories'] = "0";
        }
        $from = "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN "
                . PREFIX
                . "_catalogs_" . $this->registry->sitelang
                . " f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') " . "LEFT OUTER JOIN "
                . PREFIX
                . "_subcategories_" . $this->registry->sitelang
                . " e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN "
                . PREFIX . "_categories_" . $this->registry->sitelang
                . " g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN "
                . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') "
                . "WHERE a.item_show = '1' and ((a.item_catalog_id is NOT NULL and (c.catalog_id is NULL or c.catalog_id = " . $catalog_id
                . ")) or (a.item_category_id is NOT NULL and (d.cat_catalog_id is NULL or d.cat_catalog_id = " . $catalog_id
                . ") and (f.catalog_id is NULL or f.catalog_id = " . $catalog_id
                . ") and (a.item_display = '3' or a.item_display = '2')) or (a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id in (SELECT a1.subcategory_id FROM "
                . PREFIX . "_subcategories_" . $this->registry->sitelang . " a1," . PREFIX . "_categories_" . $this->registry->sitelang
                . " a2 WHERE a1.subcategory_cat_id=a2.category_id and a2.cat_catalog_id = " . $catalog_id . ")) and (g.cat_catalog_id is NULL or g.cat_catalog_id = "
                . $catalog_id . ") and (h.catalog_id is NULL or h.catalog_id = " . $catalog_id
                . ") and (a.item_display = '3' or a.item_display = '2'))) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items " . $from;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql =
            "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id "
            . $from . " ORDER BY a." . $this->systemdk_shop_catitems_order;
        $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist == 0 && $num_page > 1) {
                $this->error = 'unknown_page';

                return;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if (!isset($item_img) || $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = $this->registry->main_class->number_format($result->fields['3']);
                    $item_price_discounted = $this->registry->main_class->number_format($result->fields['4']);
                    $item_catalog_id = intval($result->fields['5']);
                    if ($item_catalog_id > 0) {
                        $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                    } else {
                        $item_catalog_name = "no";
                    }
                    $item_category_id = intval($result->fields['6']);
                    if ($item_category_id > 0) {
                        $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                        $item_categ_cat_id = intval($result->fields['20']);
                    } else {
                        $item_category_name = "no";
                        $item_categ_cat_id = 0;
                    }
                    $item_subcategory_id = intval($result->fields['7']);
                    if ($item_subcategory_id > 0) {
                        $item_subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['19']));
                        $item_subcateg_categ_id = intval($result->fields['21']);
                        $item_subcateg_cat_id = intval($result->fields['22']);
                    } else {
                        $item_subcategory_name = "no";
                        $item_subcateg_categ_id = 0;
                        $item_subcateg_cat_id = 0;
                    }
                    $item_short_description = $this->registry->main_class->extracting_data($result->fields['8']);
                    $item_quantity = intval($result->fields['9']);
                    $item_quantity_unlim = intval($result->fields['10']);
                    $item_quantity_param = intval($result->fields['11']);
                    $item_display = intval($result->fields['12']);
                    $item_date = intval($result->fields['13']);
                    $item_date = date("d.m.y H:i", $item_date);
                    $items_all[] = [
                        "item_id"                => $item_id,
                        "item_img"               => $item_img,
                        "item_name"              => $item_name,
                        "item_price"             => $item_price,
                        "item_price_discounted"  => $item_price_discounted,
                        "item_catalog_id"        => $item_catalog_id,
                        "item_category_id"       => $item_category_id,
                        "item_subcategory_id"    => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity"          => $item_quantity,
                        "item_quantity_unlim"    => $item_quantity_unlim,
                        "item_quantity_param"    => $item_quantity_param,
                        "item_display"           => $item_display,
                        "item_date"              => $item_date,
                        "item_catalog_name"      => $item_catalog_name,
                        "item_category_name"     => $item_category_name,
                        "item_subcategory_name"  => $item_subcategory_name,
                        "item_categ_cat_id"      => $item_categ_cat_id,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id"   => $item_subcateg_cat_id,
                        "item_additional_params" => $this->getItemAdditionalParams($item_id),
                    ];
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if (isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if ($num_pages > 1) {
                    if ($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for ($i = 1; $i < $num_pages + 1; $i++) {
                        if ($i == $num_page) {
                            $html[] = ["number" => $i, "param1" => "1"];
                        } else {
                            $pagelink = 5;
                            if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "2"];
                            }
                            if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "3"];
                            }
                            if (($i == 1) && ($num_page > $pagelink + 1)) {
                                $html[] = ["number" => $i, "param1" => "4"];
                            }
                        }
                    }
                    if ($num_page < $num_pages) {
                        $nextpage = $num_page + 1;
                        $this->result['nextpage'] = $nextpage;
                    } else {
                        $this->result['nextpage'] = "no";
                    }
                    $this->result['html'] = $html;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['shop_items_all'] = "no";
                $this->result['shop_total_items'] = "0";
                $this->result['html'] = "no";
            }
        } else {
            $this->result['shop_items_all'] = "no";
            $this->result['shop_total_items'] = "0";
            $this->result['html'] = "no";
        }
    }


    public function subcategs($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if (isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if (isset($array['num_page']) && intval($array['num_page']) !== 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if (!isset($array['catalog_id']) || !isset($array['category_id']) || (isset($array['catalog_id']) && intval($array['catalog_id']) == 0)
            || (isset($array['category_id']) && intval($array['category_id']) == 0)
        ) {
            $this->error = 'not_all_data2';

            return;
        }
        $this->result['shop_catalog_id'] = $catalog_id;
        $this->result['shop_category_id'] = $category_id;
        $num_string_rows = $this->systemdk_shop_categitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if (!isset($this->systemdk_shop_subcategories_order)) {
            $this->systemdk_shop_subcategories_order = 'subcategory_position';
        }
        if (!isset($this->systemdk_shop_categitems_order)) {
            $this->systemdk_shop_categitems_order = 'item_position';
        }
        $sql =
            "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_date,b.category_id,b.category_img,b.category_name,b.category_description,b.category_meta_title,b.category_meta_keywords,b.category_meta_description,a.catalog_id,a.catalog_name FROM "
            . PREFIX . "_catalogs_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
            . " b ON a.catalog_id=b.cat_catalog_id and b.category_id = " . $category_id . " and b.category_show = '1' LEFT OUTER JOIN " . PREFIX . "_subcategories_"
            . $this->registry->sitelang . " c ON b.category_id=c.subcategory_cat_id and (c.subcategory_id is NULL or c.subcategory_show = '1') WHERE a.catalog_id = "
            . $catalog_id . " and a.catalog_show = '1' ORDER BY c." . $this->systemdk_shop_subcategories_order;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'categories_error';

            return;
        }
        if (isset($result->fields['4']) && intval($result->fields['4']) > 0) {
            $row_exist = intval($result->fields['4']);
        } else {
            $row_exist = 0;
            $cache = "category";
        }
        if (isset($result->fields['11']) && intval($result->fields['11']) > 0) {
            $row_exist_b = intval($result->fields['11']);
        } else {
            $row_exist_b = 0;
            $cache = "catalog";
        }
        if ($row_exist < 1 || $row_exist_b < 1) {
            $this->error = 'not_find_' . $cache;

            return;
        }
        $category_id = intval($result->fields['4']);
        $category_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        if (!isset($category_img) || $category_img == "") {
            $category_img = "no";
        }
        $category_name = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['6'])
        );
        $category_description = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['7'])
        );
        $category_meta_title = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['8'])
        );
        $category_meta_keywords = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['9'])
        );
        $category_meta_description = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['10'])
        );
        $catalog_name = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['12'])
        );
        $this->result['shop_category_img'] = $category_img;
        $this->result['shop_category_name'] = $category_name;
        $this->result['shop_category_description'] = $category_description;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->registry->main_class->set_sitemeta($category_meta_title, $category_meta_description, $category_meta_keywords);
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist > 0) {
            while (!$result->EOF) {
                $subcategory_id = intval($result->fields['0']);
                $subcategory_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                if (!isset($subcategory_img) || $subcategory_img == "") {
                    $subcategory_img = "no";
                }
                $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                //$subcategory_date = intval($result->fields['3']);
                //$subcategory_date = date("d.m.y H:i",$subcategory_date);
                $subcategories_all[] = [
                    "subcategory_id"   => $subcategory_id,
                    "subcategory_img"  => $subcategory_img,
                    "subcategory_name" => $subcategory_name,
                ];
                $result->MoveNext();
            }
            $this->result['shop_subcategories_all'] = $subcategories_all;
            $this->result['shop_total_subcategories'] = count($subcategories_all);
            $this->result['systemdk_shop_image_position'] = $this->systemdk_shop_image_position;
            $this->result['systemdk_shop_image_path'] = $this->systemdk_shop_image_path;
            $this->result['systemdk_shop_image_width'] = $this->systemdk_shop_image_width;
            $this->result['systemdk_shop_items_columns'] = $this->systemdk_shop_items_columns;
            $this->result['systemdk_shop_items_columnsperc'] = intval(100 / $this->systemdk_shop_items_columns);
        } else {
            $this->result['shop_subcategories_all'] = "no";
            $this->result['shop_total_subcategories'] = "0";
        }
        $from = "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a " . "LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') LEFT OUTER JOIN "
                . PREFIX . "_categories_" . $this->registry->sitelang
                . " g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') LEFT OUTER JOIN "
                . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') "
                . "WHERE a.item_show = '1' and ((a.item_category_id is NOT NULL and (d.cat_catalog_id is NULL or d.cat_catalog_id = " . $catalog_id
                . ")  and (d.category_id is NULL or d.category_id = " . $category_id . ") and (f.catalog_id is NULL or f.catalog_id = " . $catalog_id
                . ")) or (a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id in (SELECT a1.subcategory_id FROM " . PREFIX
                . "_subcategories_"
                . $this->registry->sitelang . " a1," . PREFIX . "_categories_" . $this->registry->sitelang
                . " a2 WHERE a1.subcategory_cat_id=a2.category_id and a2.cat_catalog_id = " . $catalog_id . " and a2.category_id = " . $category_id
                . ")) and (g.cat_catalog_id is NULL or g.cat_catalog_id = " . $catalog_id . ") and (g.category_id is NULL or g.category_id = " . $category_id
                . ") and (h.catalog_id is NULL or h.catalog_id = " . $catalog_id
                . ") and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1'))) and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items " . $from;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql =
            "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id "
            . $from . " ORDER BY a." . $this->systemdk_shop_categitems_order;
        $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist == 0 && $num_page > 1) {
                $this->error = 'unknown_page';

                return;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if (!isset($item_img) || $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = $this->registry->main_class->number_format($result->fields['3']);
                    $item_price_discounted = $this->registry->main_class->number_format($result->fields['4']);
                    $item_catalog_id = intval($result->fields['5']);
                    $item_category_id = intval($result->fields['6']);
                    if ($item_category_id > 0) {
                        $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                        $item_categ_cat_id = intval($result->fields['18']);
                    } else {
                        $item_category_name = "no";
                        $item_categ_cat_id = 0;
                    }
                    $item_subcategory_id = intval($result->fields['7']);
                    if ($item_subcategory_id > 0) {
                        $item_subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                        $item_subcateg_categ_id = intval($result->fields['19']);
                        $item_subcateg_cat_id = intval($result->fields['20']);
                    } else {
                        $item_subcategory_name = "no";
                        $item_subcateg_categ_id = 0;
                        $item_subcateg_cat_id = 0;
                    }
                    $item_short_description = $this->registry->main_class->extracting_data($result->fields['8']);
                    $item_quantity = intval($result->fields['9']);
                    $item_quantity_unlim = intval($result->fields['10']);
                    $item_quantity_param = intval($result->fields['11']);
                    $item_display = intval($result->fields['12']);
                    $item_date = intval($result->fields['13']);
                    $item_date = date("d.m.y H:i", $item_date);
                    $items_all[] = [
                        "item_id"                => $item_id,
                        "item_img"               => $item_img,
                        "item_name"              => $item_name,
                        "item_price"             => $item_price,
                        "item_price_discounted"  => $item_price_discounted,
                        "item_catalog_id"        => $item_catalog_id,
                        "item_category_id"       => $item_category_id,
                        "item_subcategory_id"    => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity"          => $item_quantity,
                        "item_quantity_unlim"    => $item_quantity_unlim,
                        "item_quantity_param"    => $item_quantity_param,
                        "item_display"           => $item_display,
                        "item_date"              => $item_date,
                        "item_category_name"     => $item_category_name,
                        "item_subcategory_name"  => $item_subcategory_name,
                        "item_categ_cat_id"      => $item_categ_cat_id,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id"   => $item_subcateg_cat_id,
                        "item_additional_params" => $this->getItemAdditionalParams($item_id),
                    ];
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if (isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if ($num_pages > 1) {
                    if ($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for ($i = 1; $i < $num_pages + 1; $i++) {
                        if ($i == $num_page) {
                            $html[] = ["number" => $i, "param1" => "1"];
                        } else {
                            $pagelink = 5;
                            if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "2"];
                            }
                            if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "3"];
                            }
                            if (($i == 1) && ($num_page > $pagelink + 1)) {
                                $html[] = ["number" => $i, "param1" => "4"];
                            }
                        }
                    }
                    if ($num_page < $num_pages) {
                        $nextpage = $num_page + 1;
                        $this->result['nextpage'] = $nextpage;
                    } else {
                        $this->result['nextpage'] = "no";
                    }
                    $this->result['html'] = $html;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['shop_items_all'] = "no";
                $this->result['shop_total_items'] = "0";
                $this->result['html'] = "no";
            }
        } else {
            $this->result['shop_items_all'] = "no";
            $this->result['shop_total_items'] = "0";
            $this->result['html'] = "no";
        }
    }


    public function items($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if (isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if (isset($array['subcategory_id'])) {
            $subcategory_id = intval($array['subcategory_id']);
        }
        if (isset($array['num_page']) && intval($array['num_page']) !== 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if (!isset($array['catalog_id']) || !isset($array['category_id']) || !isset($array['subcategory_id'])
            || (isset($array['catalog_id'])
                && intval(
                       $array['catalog_id']
                   ) == 0)
            || (isset($array['category_id']) && intval($array['category_id']) == 0)
            || (isset($array['subcategory_id']) && intval($array['subcategory_id']) == 0)
        ) {
            $this->error = 'not_all_data3';

            return;
        }
        $this->result['shop_catalog_id'] = $catalog_id;
        $this->result['shop_category_id'] = $category_id;
        $this->result['shop_subcategory_id'] = $subcategory_id;
        $num_string_rows = $this->systemdk_shop_subcategitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if (!isset($this->systemdk_shop_subcategitems_order)) {
            $this->systemdk_shop_subcategitems_order = 'item_position';
        }
        $sql =
            "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_description,c.subcategory_meta_title,c.subcategory_meta_keywords,c.subcategory_meta_description,b.category_id,b.category_name,a.catalog_id,a.catalog_name FROM "
            . PREFIX . "_catalogs_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
            . " b ON a.catalog_id=b.cat_catalog_id AND b.category_id = " . $category_id . " and b.category_show = '1' LEFT OUTER JOIN " . PREFIX . "_subcategories_"
            . $this->registry->sitelang . " c ON b.category_id=c.subcategory_cat_id and c.subcategory_id = " . $subcategory_id
            . " and c.subcategory_show = '1' WHERE a.catalog_id = " . $catalog_id . " and a.catalog_show = '1'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'categories_error';

            return;
        }
        if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
            $cache = "subcategory";
        }
        if (isset($result->fields['7']) && intval($result->fields['7']) > 0) {
            $row_exist_b = intval($result->fields['7']);
        } else {
            $row_exist_b = 0;
            $cache = "category";
        }
        if (isset($result->fields['9']) && intval($result->fields['9']) > 0) {
            $row_exist_c = intval($result->fields['9']);
        } else {
            $row_exist_c = 0;
            $cache = "catalog";
        }
        if ($row_exist < 1 || $row_exist_b < 1 || $row_exist_c < 1) {
            $this->error = 'not_find_' . $cache;

            return;
        }
        $subcategory_id = intval($result->fields['0']);
        $subcategory_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        if (!isset($subcategory_img) || $subcategory_img == "") {
            $subcategory_img = "no";
        }
        $subcategory_name = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['2'])
        );
        $subcategory_description = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['3'])
        );
        $subcategory_meta_title = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['4'])
        );
        $subcategory_meta_keywords = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['5'])
        );
        $subcategory_meta_description = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['6'])
        );
        $category_name = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['8'])
        );
        $catalog_name = $this->registry->main_class->format_htmlspecchars(
            $this->registry->main_class->extracting_data($result->fields['10'])
        );
        $this->result['shop_subcategory_img'] = $subcategory_img;
        $this->result['shop_subcategory_name'] = $subcategory_name;
        $this->result['shop_subcategory_description'] = $subcategory_description;
        $this->result['shop_category_name'] = $category_name;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->registry->main_class->set_sitemeta($subcategory_meta_title, $subcategory_meta_description, $subcategory_meta_keywords);
        $from = "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a " . "LEFT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_categories_"
                . $this->registry->sitelang . " g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_catalogs_"
                . $this->registry->sitelang . " h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL "
                . "WHERE a.item_show = '1' and ((a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id = " . $subcategory_id
                . ") and (g.cat_catalog_id is NULL or g.cat_catalog_id = " . $catalog_id . ") and (g.category_id is NULL or g.category_id = " . $category_id
                . ") and (h.catalog_id is NULL or h.catalog_id = " . $catalog_id
                . "))) and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items " . $from;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql =
            "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,e.subcategory_id,e.subcategory_name,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id "
            . $from . " ORDER BY a." . $this->systemdk_shop_subcategitems_order;
        $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist == 0 && $num_page > 1) {
                $this->error = 'unknown_page';

                return;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if (!isset($item_img) || $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = $this->registry->main_class->number_format($result->fields['3']);
                    $item_price_discounted = $this->registry->main_class->number_format($result->fields['4']);
                    $item_catalog_id = intval($result->fields['5']);
                    $item_category_id = intval($result->fields['6']);
                    $item_subcategory_id = intval($result->fields['7']);
                    if ($item_subcategory_id > 0) {
                        $item_subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                        $item_subcateg_categ_id = intval($result->fields['16']);
                        $item_subcateg_cat_id = intval($result->fields['17']);
                    } else {
                        $item_subcategory_name = "no";
                        $item_subcateg_categ_id = 0;
                        $item_subcateg_cat_id = 0;
                    }
                    $item_short_description = $this->registry->main_class->extracting_data($result->fields['8']);
                    $item_quantity = intval($result->fields['9']);
                    $item_quantity_unlim = intval($result->fields['10']);
                    $item_quantity_param = intval($result->fields['11']);
                    $item_display = intval($result->fields['12']);
                    $item_date = intval($result->fields['13']);
                    $item_date = date("d.m.y H:i", $item_date);
                    $items_all[] = [
                        "item_id"                => $item_id,
                        "item_img"               => $item_img,
                        "item_name"              => $item_name,
                        "item_price"             => $item_price,
                        "item_price_discounted"  => $item_price_discounted,
                        "item_catalog_id"        => $item_catalog_id,
                        "item_category_id"       => $item_category_id,
                        "item_subcategory_id"    => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity"          => $item_quantity,
                        "item_quantity_unlim"    => $item_quantity_unlim,
                        "item_quantity_param"    => $item_quantity_param,
                        "item_display"           => $item_display,
                        "item_date"              => $item_date,
                        "item_subcategory_name"  => $item_subcategory_name,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id"   => $item_subcateg_cat_id,
                        "item_additional_params" => $this->getItemAdditionalParams($item_id),
                    ];
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if (isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if ($num_pages > 1) {
                    if ($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for ($i = 1; $i < $num_pages + 1; $i++) {
                        if ($i == $num_page) {
                            $html[] = ["number" => $i, "param1" => "1"];
                        } else {
                            $pagelink = 5;
                            if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "2"];
                            }
                            if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                                $html[] = ["number" => $i, "param1" => "3"];
                            }
                            if (($i == 1) && ($num_page > $pagelink + 1)) {
                                $html[] = ["number" => $i, "param1" => "4"];
                            }
                        }
                    }
                    if ($num_page < $num_pages) {
                        $nextpage = $num_page + 1;
                        $this->result['nextpage'] = $nextpage;
                    } else {
                        $this->result['nextpage'] = "no";
                    }
                    $this->result['html'] = $html;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['shop_items_all'] = "no";
                $this->result['shop_total_items'] = "0";
                $this->result['html'] = "no";
            }
        } else {
            $this->result['shop_items_all'] = "no";
            $this->result['shop_total_items'] = "0";
            $this->result['html'] = "no";
        }
    }


    public function item($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $item_id = 0;
        if (isset($array['item_id'])) {
            $item_id = intval($array['item_id']);
        }
        if (isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if (isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if (isset($array['subcategory_id'])) {
            $subcategory_id = intval($array['subcategory_id']);
        }
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        if (isset($catalog_id) && $catalog_id != 0) {
            if (isset($subcategory_id) && isset($category_id) && $category_id != 0) {
                $cache = "subcateg";
                if ($subcategory_id != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif (isset($category_id)) {
                $cache = "categ";
                if ($category_id != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
                $subcategory_id = 0;
            } else {
                $cache = "cat";
                $check = 1;
                $category_id = 0;
                $subcategory_id = 0;
            }
        } elseif (isset($catalog_id) && $catalog_id == 0) {
            $cache = "cat";
            $check = 0;
            $category_id = 0;
            $subcategory_id = 0;
        } else {
            $cache = "home";
            if (!isset($item_id)) {
                $item_id = 0;
            }
            $check = 1;
            $catalog_id = 0;
            $category_id = 0;
            $subcategory_id = 0;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if ($item_id == 0 || $check == 0) {
            if ($item_id == 0) {
                $cache_err = "not_all_data4";
            } elseif ($cache == "cat") {
                $cache_err = "not_all_data";
            } elseif ($cache == "categ") {
                $cache_err = "not_all_data2";
            } elseif ($cache == "subcateg") {
                $cache_err = "not_all_data3";
            }
            $this->error = $cache_err;

            return;
        }
        if ($cache === 'subcateg') {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,e.catalog_id,e.catalog_name,d.category_id,d.category_name,c.subcategory_id,c.subcategory_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a INNER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " c ON c.subcategory_id=a.item_subcategory_id and c.subcategory_id = " . $subcategory_id . " INNER JOIN " . PREFIX . "_categories_"
                . $this->registry->sitelang . " d ON d.category_id=c.subcategory_cat_id and d.category_id = " . $category_id . " INNER JOIN " . PREFIX . "_catalogs_"
                . $this->registry->sitelang . " e ON e.catalog_id=d.cat_catalog_id and e.catalog_id = " . $catalog_id . " WHERE a.item_id = " . $item_id
                . " and c.subcategory_show='1' and d.category_show = '1' and e.catalog_show='1' and a.item_show = '1'";
        } elseif ($cache === 'categ') {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,e.catalog_id,e.catalog_name,d.category_id,d.category_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a INNER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " d ON d.category_id=a.item_category_id and d.category_id = " . $category_id . " INNER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " e ON e.catalog_id=d.cat_catalog_id and e.catalog_id = " . $catalog_id . " WHERE a.item_id = " . $item_id
                . " and d.category_show = '1' and e.catalog_show='1' and a.item_show = '1'";
        } elseif ($cache === 'cat') {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,e.catalog_id,e.catalog_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a INNER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " e ON e.catalog_id=a.item_catalog_id and e.catalog_id = " . $catalog_id . " WHERE a.item_id = " . $item_id
                . " and e.catalog_show='1' and a.item_show = '1'";
        } else {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a WHERE a.item_id = " . $item_id . " and a.item_show = '1'";
        }
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'categories_error';

            return;
        }
        $this->result['shop_item_id'] = $item_id;
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->result['shop_item_all'] = "no";

            return;
        }
        $item_id = intval($result->fields['0']);
        $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        if (!isset($item_img) || $item_img == "") {
            $item_img = "no";
        }
        $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $item_price = $this->registry->main_class->number_format($result->fields['3']);
        $item_price_discounted = $this->registry->main_class->number_format($result->fields['4']);
        $item_short_description = $this->registry->main_class->extracting_data($result->fields['5']);
        $item_description = $this->registry->main_class->extracting_data($result->fields['6']);
        $item_quantity = intval($result->fields['7']);
        $item_quantity_unlim = intval($result->fields['8']);
        $item_quantity_param = intval($result->fields['9']);
        //$item_date = intval($result->fields['10']);
        //$item_date = date("d.m.y H:i",$item_date);
        $item_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
        $item_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
        $item_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
        if ($cache === 'home') {
            $catalog_id = "no";
            $catalog_name = "no";
            $category_id = "no";
            $category_name = "no";
            $subcategory_id = "no";
            $subcategory_name = "no";
        } elseif ($cache === 'cat') {
            $catalog_id = intval($result->fields['14']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
            $category_id = "no";
            $category_name = "no";
            $subcategory_id = "no";
            $subcategory_name = "no";
        } elseif ($cache === 'categ') {
            $catalog_id = intval($result->fields['14']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
            $category_id = intval($result->fields['16']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
            $subcategory_id = "no";
            $subcategory_name = "no";
        } elseif ($cache === 'subcateg') {
            $catalog_id = intval($result->fields['14']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
            $category_id = intval($result->fields['16']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
            $subcategory_id = intval($result->fields['18']);
            $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['19']));
        }
        $this->registry->main_class->set_sitemeta($item_meta_title, $item_meta_description, $item_meta_keywords);
        $item_description = $this->registry->main_class->split_content_by_pages($item_description);
        $count_num_pages = count($item_description);
        if ($num_page > $count_num_pages) {
            $num_page = 1;
        }
        $item_description = $item_description[$num_page - 1];
        $item_description = $this->registry->main_class->search_player_entry($item_description);
        if ($count_num_pages > 1) {
            if ($num_page < $count_num_pages) {
                $next_page = $num_page + 1;
            } else {
                $next_page = "no";
            }
            if ($num_page > 1) {
                $prev_page = $num_page - 1;
            } else {
                $prev_page = "no";
            }
            $pages_menu[] = [
                "current_page"    => $num_page,
                "next_page"       => $next_page,
                "prev_page"       => $prev_page,
                "count_num_pages" => $count_num_pages,
            ];
            $this->result['pages_menu'] = $pages_menu;
        } else {
            $this->result['pages_menu'] = "no";
        }
        $item_all[] = [
            "item_id"                => $item_id,
            "item_img"               => $item_img,
            "item_name"              => $item_name,
            "item_price"             => $item_price,
            "item_price_discounted"  => $item_price_discounted,
            "item_short_description" => $item_short_description,
            "item_description"       => $item_description,
            "item_quantity"          => $item_quantity,
            "item_quantity_unlim"    => $item_quantity_unlim,
            "item_quantity_param"    => $item_quantity_param,
            "item_additional_params" => $this->getItemAdditionalParams($item_id),
        ];
        $this->result['shop_item_all'] = $item_all;
        $this->result['shop_item_name'] = $item_name;
        $this->result['shop_subcategory_name'] = $subcategory_name;
        $this->result['shop_subcategory_id'] = $subcategory_id;
        $this->result['shop_category_name'] = $category_name;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->result['shop_category_id'] = $category_id;
        $this->result['shop_catalog_id'] = $catalog_id;
        $this->result['systemdk_shop_image_position'] = $this->systemdk_shop_itemimage_position;
        $this->result['systemdk_shop_image_path'] = $this->systemdk_shop_itemimage_path;
        $this->result['systemdk_shop_image_width'] = $this->systemdk_shop_itemimage_width2;
    }


    public function cart()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;

        if (isset($_SESSION[$this->systemdk_shop_cart])) { // && (array_count_values($_SESSION[$this->systemdk_shop_cart]))
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart]);
            $this->getTermsAndConditions();
        } else {
            $this->result['shop_cart_display_items'] = "no";
        }
    }


    private function shop_display_cart_items($cart, $shop_cart_allowchange = 1, $shop_cart_allowimages = 1)
    {
        $this->result['shop_cart_allowimages'] = intval($shop_cart_allowimages);
        $this->result['shop_cart_allowchange'] = intval($shop_cart_allowchange);
        $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
        $this->result['systemdk_shop_itemimage_width3'] = $this->systemdk_shop_itemimage_width3;
        $this->result['systemdk_shop_itemimage_height3'] = $this->systemdk_shop_itemimage_height3;
        $this->shop_cart_items_all = $this->shop_get_items_details($cart);
        if (!$this->shop_cart_items_all) {
            $this->shop_cart_items_all = "no";
        }
        $this->result['shop_cart_display_items'] = $this->shop_cart_items_all;
    }


    private function shop_get_items_details($cart = 0)
    {
        if (!isset($cart) || $cart == 0 || !is_array($cart) || count($cart) == 0) {
            return false;
        }

        $i = 0;
        foreach ($cart as $cart_item_id => $data) {

            if (isset($cart_item_id) && intval($cart_item_id) != 0) {

                if ($i == 0) {
                    $item_id = "'" . intval($cart_item_id) . "'";
                } else {

                    if (!isset($item_id)) {
                        $item_id = false;
                    }

                    $item_id .= ",'" . intval($cart_item_id) . "'";
                }

                $i++;
            }
        }

        if (!isset($item_id)) {
            return false;
        }

        $from = "FROM " . PREFIX . "_items_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_categories_"
                . $this->registry->sitelang . " g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_catalogs_"
                . $this->registry->sitelang . " h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL WHERE a.item_id IN (" . $item_id
                . ") and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL) or (a.item_category_id is NOT NULL) or (a.item_subcategory_id is NOT NULL)) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql =
            "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id,a.item_display "
            . $from;
        $result = $this->db->Execute($sql);

        if (!$result) {
            return false;
        }

        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }

        if ($row_exist < 1) {
            return false;
        }

        while (!$result->EOF) {
            $item_id = intval($result->fields['0']);
            $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));

            if (!isset($item_img) || $item_img == "") {
                $item_img = "no";
            }

            $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
            $item_price = floatval($result->fields['3']);
            $item_price_discounted = floatval($result->fields['4']);

            if ($item_price_discounted > 0) {
                $item_price = $item_price_discounted;
            }

            $item_show = intval($result->fields['5']);
            $item_quantity = intval($result->fields['6']);
            $item_quantity_unlim = intval($result->fields['7']);
            $item_catalog_id = intval($result->fields['8']);
            $item_category_id = intval($result->fields['9']);

            if ($item_category_id > 0) {
                $item_categ_cat_id = intval($result->fields['11']);
            } else {
                $item_categ_cat_id = "no";
            }

            $item_subcategory_id = intval($result->fields['10']);

            if ($item_subcategory_id > 0) {
                $item_subcateg_categ_id = intval($result->fields['12']);
                $item_subcateg_cat_id = intval($result->fields['13']);
            } else {
                $item_subcateg_categ_id = "no";
                $item_subcateg_cat_id = "no";
            }

            $item_display = intval($result->fields['14']);

            if (isset($cart[$item_id]['amount'])) {
                $item_qty = $cart[$item_id]['amount'];
            } else {
                $item_qty = 0;
            }

            $selectedAdditionalParams = false;
            $item_additional_params = $this->getItemAdditionalParams($item_id);

            if (!empty($cart[$item_id][$this->systemdk_shop_cart_item_additional_params]) && !empty($item_additional_params)) {
                $selectedAdditionalParams =
                    $this->processSelectedAdditionalParams($cart[$item_id][$this->systemdk_shop_cart_item_additional_params], $item_additional_params);
            }

            $items_all[] = [
                "item_id"                    => $item_id,
                "item_img"                   => $item_img,
                "item_name"                  => $item_name,
                "item_price"                 => $this->registry->main_class->number_format($item_price),
                "item_qty"                   => $item_qty,
                "item_total_price"           => $this->registry->main_class->number_format(floatval($item_price * $item_qty)),
                "item_show"                  => $item_show,
                "item_quantity"              => $item_quantity,
                "item_quantity_unlim"        => $item_quantity_unlim,
                "item_display"               => $item_display,
                "item_catalog_id"            => $item_catalog_id,
                "item_category_id"           => $item_category_id,
                "item_categ_cat_id"          => $item_categ_cat_id,
                "item_subcategory_id"        => $item_subcategory_id,
                "item_subcateg_categ_id"     => $item_subcateg_categ_id,
                "item_subcateg_cat_id"       => $item_subcateg_cat_id,
                "item_additional_params"     => $item_additional_params,
                "selected_additional_params" => $selectedAdditionalParams,
            ];
            $result->MoveNext();
        }

        return $items_all;
    }


    /**
     * @param array $selectedItemAdditionalParams
     * @param array $availableItemAdditionalParams
     *
     * @return array
     */
    private function processSelectedAdditionalParams($selectedItemAdditionalParams, $availableItemAdditionalParams)
    {
        if (!is_array($selectedItemAdditionalParams) || !is_array($availableItemAdditionalParams)) {
            return false;
        }

        foreach ($selectedItemAdditionalParams as &$iterationData) {
            foreach ($iterationData as $groupKey => &$groupData) {

                if (empty($availableItemAdditionalParams['groups'][$groupKey])) {
                    unset($iterationData[$groupKey]);
                    continue;
                }

                $groupData = [
                    'group_data' => $groupData,
                    'group_name' => isset($availableItemAdditionalParams['groups'][$groupKey]['group_name'])
                        ? $availableItemAdditionalParams['groups'][$groupKey]['group_name'] : false,
                    'group_id'   => isset($availableItemAdditionalParams['groups'][$groupKey]['group_id'])
                        ? $availableItemAdditionalParams['groups'][$groupKey]['group_id'] : false,
                ];
                foreach ($groupData['group_data'] as $paramKey => &$paramData) {

                    if (empty($availableItemAdditionalParams['params'][$groupKey][$paramData])) {
                        unset($groupData['group_data'][$paramKey]);

                        if (count($groupData['group_data']) < 1) {
                            unset($iterationData[$groupKey]);
                        }

                        continue;
                    }

                    $paramData = [
                        'param_id'    => $paramData,
                        'param_value' => isset($availableItemAdditionalParams['params'][$groupKey][$paramData]['param_value'])
                            ? $availableItemAdditionalParams['params'][$groupKey][$paramData]['param_value'] : false,
                    ];
                }
            }
        }

        return $selectedItemAdditionalParams;
    }


    public function checkout()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;

        if (isset($_SESSION[$this->systemdk_shop_cart])) { //&& (array_count_values($_SESSION[$this->systemdk_shop_cart]))
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart], 0, 0);
            $this->getTermsAndConditions();
            $this->shop_user_deliv_pay(1);
        } else {
            $this->result['shop_cart_display_items'] = "no";
        }
    }


    private function shop_user_deliv_pay($check_user = 0)
    {
        if ($check_user == 1) {

            if ($this->registry->main_class->is_user()) {
                $row = $this->registry->main_class->get_user_info();

                if (!empty($row) && intval($row['0']) > 0) {
                    $shop_user_id = intval($row['0']);
                    $shop_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                    $shop_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                    $shop_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['10']));
                    $shop_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['7']));
                    $shop_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['8']));
                    $shop_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));

                    if (empty($shop_user_name) && !empty($shop_user_surname)) {
                        $shop_user_name = $shop_user_surname;
                    } elseif (empty($shop_user_name) && empty($shop_user_surname)) {
                        $shop_user_name = $this->registry->main_class->extracting_data($row['5']);
                    } elseif (!empty($shop_user_name) && !empty($shop_user_surname)) {
                        $shop_user_name = $shop_user_name . " " . $shop_user_surname;
                    }

                    $this->result['modules_shop_username'] = $shop_user_name;
                    $this->result['modules_shop_userid'] = $shop_user_id;
                    $this->result['modules_shop_usertelephone'] = $shop_user_telephone;
                    $this->result['modules_shop_usercountry'] = $shop_user_country;
                    $this->result['modules_shop_usercity'] = $shop_user_city;
                    $this->result['modules_shop_useremail'] = $shop_user_email;
                }

            }

        }

        $sql = "SELECT delivery_id,delivery_name,delivery_price,null as payment_id,null as payment_name,0.00 as payment_commission,null as payment_system_id,"
               . "null as system_class,null as system_integ_type FROM " . PREFIX . "_delivery_" . $this->registry->sitelang
               . " WHERE delivery_status = '1' UNION ALL "
               . "SELECT null as delivery_id,null as delivery_name,0.00 as delivery_price,a.id as payment_id,a.payment_name as payment_name,"
               . "a.payment_commission as payment_commission,b.id as payment_system_id, b.system_class as system_class,"
               . "b.system_integ_type as system_integ_type FROM " . PREFIX . "_s_payment_methods_" . $this->registry->sitelang . " a LEFT OUTER JOIN "
               . PREFIX . "_s_payment_systems b ON a.payment_system_id=b.id and a.payment_system_id IS NOT NULL and b.system_status = '1' "
               . "WHERE a.payment_status = '1' ORDER by delivery_id,payment_id ASC";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->error = 'categories_error';

            return;
        }

        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }

        if (isset($result->fields['3'])) {
            $row_exist2 = intval($result->fields['3']);
        } else {
            $row_exist2 = 0;
        }

        if ($row_exist > 0 || $row_exist2 > 0) {
            $calculateData['order_amount'] = $_SESSION[$this->systemdk_shop_total_price];
            while (!$result->EOF) {

                if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
                    $delivery_id = intval($result->fields['0']);
                    $delivery_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $delivery_price = floatval($result->fields['2']);

                    if (isset($this->shop_order_delivery_id) && $this->shop_order_delivery_id > 0 && $this->shop_order_delivery_id == $delivery_id) {
                        $this->result['shop_final_delivery_name'] = $delivery_name;
                        $calculateData['selected_delivery_method_price'] = $delivery_price;
                        $this->result['shop_final_delivery_price'] = $this->registry->main_class->number_format($delivery_price);
                    }

                    $delivery_all[] = [
                        "delivery_id"    => $delivery_id,
                        "delivery_name"  => $delivery_name,
                        "delivery_price" => $this->registry->main_class->number_format($delivery_price),
                    ];
                }

                if (isset($result->fields['3']) && intval($result->fields['3']) > 0) {
                    $payment_id = intval($result->fields['3']);
                    $payment_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $payment_commission = floatval($result->fields['5']);
                    $paymentSystemId = intval($result->fields['6']);
                    $systemClass = $this->registry->main_class->extracting_data($result->fields['7']);
                    $systemIntegType = $this->registry->main_class->extracting_data($result->fields['8']);

                    if (isset($this->shop_order_pay_id) && $this->shop_order_pay_id > 0 && $this->shop_order_pay_id == $payment_id) {
                        $this->result['shop_final_pay_name'] = $payment_name;
                        $calculateData['selected_payment_method_commission'] = $payment_commission;
                        $this->result['shop_final_pay_price'] = $this->registry->main_class->number_format($payment_commission);

                        if ($paymentSystemId > 0) {
                            $calculateData['payment_system_data'] = [
                                'system_class'            => $systemClass,
                                'system_id'               => $paymentSystemId,
                                'system_integration_type' => $systemIntegType,
                            ];
                        }
                    }

                    $pay_all[] = [
                        "pay_id"    => $payment_id,
                        "pay_name"  => $payment_name,
                        "pay_price" => $this->registry->main_class->number_format($payment_commission),
                    ];
                }

                $result->MoveNext();
            }
            $amount = $this->calculatePayableAmount($calculateData);
            $amount ? $this->result['shop_supposed_invoice_amount'] = $amount : null;
            $this->result['shop_delivery_all'] = $delivery_all;
            $this->result['shop_pay_all'] = $pay_all;
        } else {
            $this->result['shop_delivery_all'] = "no";
            $this->result['shop_pay_all'] = "no";
        }
    }


    /**
     * Calculate final amount(with delivery price and payment method commission) without converting currency
     *
     * @param array $data
     *
     * @return string|bool
     */
    public function calculatePayableAmount($data)
    {
        if(empty($data['order_amount'])) {
            $error[] = ["code" => 0, "message" => 'Empty order_amount for calculation'];
            $this->error = 'system_error';
            $this->error_array = $error;

            return false;
        }

        if (!isset($data['selected_delivery_method_price']) || !isset($data['selected_payment_method_commission'])) {
            return false; // calculation not needed
        }

        if (!empty($data['payment_system_data'])) {
            $this->registry->paymentSystemData = [
                'shop_process_type'                => 'calculate',
                'shop_system_id'                   => $data['payment_system_data']['system_id'],
                'shop_system_integration_type'     => $data['payment_system_data']['system_integration_type'],
                'shop_currency'                    => $this->systemdk_shop_valuta,
                'shop_order_notify_custom_mailbox' => $this->systemdk_shop_order_notify_custom_mailbox,
            ];

            if (!empty($this->registry->shopPaymentErrorNotifyMailData)) {
                $this->registry->paymentSystemData = array_merge($this->registry->paymentSystemData, $this->registry->shopPaymentErrorNotifyMailData);
            }

            try {
                /**
                 * @var shop_portmone $paymentSystem
                 */
                $paymentSystem = singleton::getinstance($data['payment_system_data']['system_class'], $this->registry);
                $invoiceAmount =
                    $paymentSystem->calculateInvoice($data['order_amount'], $data['selected_delivery_method_price'], $data['selected_payment_method_commission'], false);
            } catch (system_exception $exception) {
                $error[] = ["code" => $exception->getCode(), "message" => $exception->getMessage()];
                $this->error = 'system_error';
                $this->error_array = $error;

                return false;
            }
        } else {
            $invoiceAmount =
                $data['order_amount'] + $data['selected_delivery_method_price'] + ($data['order_amount'] * $data['selected_payment_method_commission'] / 100);
        }

        return $this->registry->main_class->number_format($invoiceAmount);
    }


    public function checkout2($data)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $keys = [
            'shop_order_delivery'               => 'integer',
            'shop_order_pay'                    => 'integer',
            'shop_ship_name'                    => 'string',
            'shop_ship_telephone'               => 'string',
            'shop_ship_email'                   => 'string',
            'shop_ship_country'                 => 'string',
            'shop_ship_state'                   => 'string',
            'shop_ship_city'                    => 'string',
            'shop_ship_street'                  => 'string',
            'shop_ship_homenumber'              => 'string',
            'shop_ship_flatnumber'              => 'string',
            'shop_ship_postalcode'              => 'string',
            'shop_ship_addaddrnotes'            => 'string',
            'shop_order_comments'               => 'string',
            'payment_error_notify_mail_subject' => 'html',
            'payment_error_notify_mail_content' => 'html',
        ];
        foreach ($keys as $key => $valueType) {

            if (isset($data[$key])) {

                if ($valueType === 'integer') {
                    $dataArray[$key] = intval($data[$key]);
                } elseif ($valueType === 'html') {
                    $dataArray[$key] = $data[$key];
                } else {
                    $dataArray[$key] = trim($data[$key]);
                }

            }

        }

        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;

        if ((!isset($dataArray['shop_ship_name'])
             || !isset($dataArray['shop_ship_telephone'])
             || !isset($dataArray['shop_ship_email'])
             || !isset($dataArray['shop_ship_country'])
             || !isset($dataArray['shop_ship_state'])
             || !isset($dataArray['shop_ship_city'])
             || !isset($dataArray['shop_ship_street'])
             || !isset($dataArray['shop_ship_homenumber'])
             || !isset($dataArray['shop_ship_flatnumber'])
             || !isset($dataArray['shop_ship_postalcode'])
             || !isset($dataArray['shop_ship_addaddrnotes'])
             || !isset($dataArray['shop_order_delivery'])
             || !isset($dataArray['shop_order_pay'])
             || !isset($dataArray['shop_order_comments']))
            || ($dataArray['shop_ship_name'] == ""
                || $dataArray['shop_ship_telephone'] == ""
                || $dataArray['shop_ship_email'] == ""
                || $dataArray['shop_order_delivery'] == 0
                || $dataArray['shop_order_pay'] == 0)
        ) {
            $this->error = 'shop_not_all_data';

            return;
        }

        if (!isset($_SESSION[$this->systemdk_shop_cart])) { //&& (array_count_values($_SESSION[$this->systemdk_shop_cart]))
            $this->result['shop_cart_display_items'] = "no";

            return;
        }

        if (isset($dataArray['payment_error_notify_mail_subject']) && isset($dataArray['payment_error_notify_mail_content'])) {
            $this->registry->shopPaymentErrorNotifyMailData = [
                'shop_payment_error_notify_mail_subject' => $dataArray['payment_error_notify_mail_subject'],
                'shop_payment_error_notify_mail_content' => $dataArray['payment_error_notify_mail_content'],
            ];
        }

        $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart], 0, 0);
        $this->getTermsAndConditions();
        $this->shop_order_delivery_id = $dataArray['shop_order_delivery'];
        $this->shop_order_pay_id = $dataArray['shop_order_pay'];
        $this->shop_user_deliv_pay(1);

        if (!empty($this->error)) {
            return;
        }

        if (extension_loaded("gd") && (ENTER_CHECK == "yes")) {
            $this->result['enter_check'] = "yes";
        } else {
            $this->result['enter_check'] = "no";
        }

        foreach ($dataArray as $key => $value) {
            $dataArray[$key] = $this->registry->main_class->format_striptags($value);
        }

        if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($dataArray['shop_ship_email']))) {
            $this->error = 'user_email';

            return;
        }

        $this->result['shop_cart_checkout_confirm'] = 1;
        $this->result['modules_shop_username'] = $dataArray['shop_ship_name'];
        $this->result['modules_shop_usertelephone'] = $dataArray['shop_ship_telephone'];
        $this->result['modules_shop_useremail'] = $dataArray['shop_ship_email'];
        $this->result['modules_shop_usercountry'] = $dataArray['shop_ship_country'];
        $this->result['modules_shop_userstate'] = $dataArray['shop_ship_state'];
        $this->result['modules_shop_usercity'] = $dataArray['shop_ship_city'];
        $this->result['modules_shop_userstreet'] = $dataArray['shop_ship_street'];
        $this->result['modules_shop_userhomenumber'] = $dataArray['shop_ship_homenumber'];
        $this->result['modules_shop_userflatnumber'] = $dataArray['shop_ship_flatnumber'];
        $this->result['modules_shop_userpostalcode'] = $dataArray['shop_ship_postalcode'];
        $this->result['modules_shop_useraddaddrnotes'] = $dataArray['shop_ship_addaddrnotes'];
        $this->result['modules_shop_userdelivery'] = $dataArray['shop_order_delivery'];
        $this->result['modules_shop_userpay'] = $dataArray['shop_order_pay'];
        $this->result['modules_shop_usercomments'] = $dataArray['shop_order_comments'];
    }


    /**
     * Create an order
     *
     * @param array|bool $data
     */
    public function insert($data)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        $keys = [
            'shop_order_delivery'               => 'integer',
            'shop_order_pay'                    => 'integer',
            'shop_ship_name'                    => 'string',
            'shop_ship_telephone'               => 'string',
            'shop_ship_email'                   => 'string',
            'shop_ship_country'                 => 'string',
            'shop_ship_state'                   => 'string',
            'shop_ship_city'                    => 'string',
            'shop_ship_street'                  => 'string',
            'shop_ship_homenumber'              => 'string',
            'shop_ship_flatnumber'              => 'string',
            'shop_ship_postalcode'              => 'string',
            'shop_ship_addaddrnotes'            => 'string',
            'shop_order_comments'               => 'string',
            'payment_error_notify_mail_subject' => 'html',
            'payment_error_notify_mail_content' => 'html',
            'order_mail_subject'                => 'html',
            'order_mail_content'                => 'html',
            'notify_order_mail_subject'         => 'html',
            'notify_order_mail_content'         => 'html',
        ];
        foreach ($keys as $key => $valueType) {

            if (isset($data[$key])) {

                if ($valueType === 'integer') {
                    $dataArray[$key] = intval($data[$key]);
                } elseif ($valueType === 'html') {
                    $dataArray[$key] = $data[$key];
                    $mailData[$key] = $data[$key];
                } else {
                    $dataArray[$key] = trim($data[$key]);
                }

            }

        }

        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;

        if ((!isset($dataArray['shop_ship_name'])
             || !isset($dataArray['shop_ship_telephone'])
             || !isset($dataArray['shop_ship_email'])
             || !isset($dataArray['shop_ship_country'])
             || !isset($dataArray['shop_ship_state'])
             || !isset($dataArray['shop_ship_city'])
             || !isset($dataArray['shop_ship_street'])
             || !isset($dataArray['shop_ship_homenumber'])
             || !isset($dataArray['shop_ship_flatnumber'])
             || !isset($dataArray['shop_ship_postalcode'])
             || !isset($dataArray['shop_ship_addaddrnotes'])
             || !isset($dataArray['shop_order_delivery'])
             || !isset($dataArray['shop_order_pay'])
             || !isset($dataArray['shop_order_comments']))
            || ($dataArray['shop_ship_name'] == ""
                || $dataArray['shop_ship_telephone'] == ""
                || $dataArray['shop_ship_email'] == ""
                || $dataArray['shop_order_delivery'] == 0
                || $dataArray['shop_order_pay'] == 0)
        ) {
            $this->error = 'shop_not_all_data2';

            return;
        }

        if (ENTER_CHECK == "yes") {

            if (extension_loaded("gd")
                && ((!isset($data["captcha"]))
                    || (isset($_SESSION["captcha"])
                        && $_SESSION["captcha"] !== $this->registry->main_class->format_striptags(trim($data["captcha"])))
                    || (!isset($_SESSION["captcha"])))
            ) {

                if (isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }

                $this->error = 'check_code';

                return;
            }

            if (isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }

        }

        if (isset($this->systemdk_shop_antispam_ipaddr) && intval($this->systemdk_shop_antispam_ipaddr) == 1) {
            $post_date_check = $this->registry->main_class->get_time() - 86400;
            $sql = "SELECT count(*) FROM " . PREFIX . "_orders_" . $this->registry->sitelang . " WHERE order_ip = '" . $this->registry->ip_addr
                   . "' and order_date > " . $post_date_check;
            $result = $this->db->Execute($sql);

            if ($result) {

                if (isset($result->fields['0'])) {
                    $num_rows = intval($result->fields['0']);
                } else {
                    $num_rows = 0;
                }

                if ($num_rows >= intval($this->systemdk_shop_antispam_num) && intval($this->systemdk_shop_antispam_num) > 0) {
                    $this->error = 'post_limit';

                    return;
                }
            }
        }

        if (!isset($_SESSION[$this->systemdk_shop_cart])) { //&& (array_count_values($_SESSION[$this->systemdk_shop_cart]))
            $this->result['shop_cart_display_items'] = 'no';

            return;
        }

        if (isset($dataArray['payment_error_notify_mail_subject']) && isset($dataArray['payment_error_notify_mail_content'])) {
            $this->registry->shopPaymentErrorNotifyMailData = [
                'shop_payment_error_notify_mail_subject' => $dataArray['payment_error_notify_mail_subject'],
                'shop_payment_error_notify_mail_content' => $dataArray['payment_error_notify_mail_content'],
            ];
        }

        $shop_user_id = 'NULL';

        if ($this->registry->main_class->is_user()) {
            $row = $this->registry->main_class->get_user_info();

            if (!empty($row) && intval($row['0']) > 0) {
                $shop_user_id = "'" . intval($row['0']) . "'";
            }

        }

        $order_amount = $this->registry->main_class->float_to_db($_SESSION[$this->systemdk_shop_total_price]);
        $order_date = $this->registry->main_class->get_time();
        $order_status = 1;
        $dataArray['shop_ship_name'] = $this->registry->main_class->format_striptags($dataArray['shop_ship_name']);
        $mailData['ship_name'] = $dataArray['shop_ship_name'];
        $dataArray['shop_ship_name'] = $this->registry->main_class->processing_data($dataArray['shop_ship_name']);
        $dataArray['shop_ship_telephone'] = $this->registry->main_class->format_striptags($dataArray['shop_ship_telephone']);
        $dataArray['shop_ship_telephone'] = $this->registry->main_class->processing_data($dataArray['shop_ship_telephone']);
        $dataArray['shop_ship_email'] = $this->registry->main_class->format_striptags($dataArray['shop_ship_email']);

        if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($dataArray['shop_ship_email']))) {
            $this->error = 'user_email2';

            return;
        }

        $mailData['ship_email'] = $dataArray['shop_ship_email'];
        $dataArray['shop_ship_email'] = $this->registry->main_class->processing_data($dataArray['shop_ship_email']);
        $shop_ship_addaddrnotes_length = strlen($dataArray['shop_ship_addaddrnotes']);
        $shop_order_comments_length = strlen($dataArray['shop_order_comments']);
        $keys = [
            'shop_ship_country',
            'shop_ship_state',
            'shop_ship_city',
            'shop_ship_street',
            'shop_ship_homenumber',
            'shop_ship_flatnumber',
            'shop_ship_postalcode',
            'shop_ship_addaddrnotes',
            'shop_order_comments',
        ];
        foreach ($dataArray as $key => $value) {

            if (in_array($key, $keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                $dataArray[$key] = 'NULL';

                if (!empty($value)) {
                    $dataArray[$key] = $this->registry->main_class->processing_data($value);
                }
            }

        }

        if ($shop_ship_addaddrnotes_length > 255 || $shop_order_comments_length > 255) {
            $this->error = 'post_length';

            return;
        }

        $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart], 0, 0);

        if ($this->shop_cart_items_all == "no") {
            $this->result['shop_cart_display_items'] = 'no';

            return;
        }

        $this->shop_order_delivery_id = $dataArray['shop_order_delivery'];
        $this->shop_order_pay_id = $dataArray['shop_order_pay'];
        $this->shop_user_deliv_pay(0);

        if (!empty($this->error)) {
            return;
        }

        $order_ip = $this->registry->main_class->processing_data($this->registry->main_class->format_striptags($this->registry->ip_addr));
        $hash = $this->registry->main_class->generate_code(35);
        $mailData['hash'] = $hash;
        $orderHash = $this->registry->main_class->processing_data($hash);
        $this->db->StartTrans();
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . '_orders_id_' . $this->registry->sitelang, 'order_id');
        $order_id = $sequence_array['sequence_value'];
        $sql = "INSERT INTO " . PREFIX . "_orders_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
               . "order_customer_id,order_amount,order_date,ship_date,order_status,ship_name,ship_street,ship_house,ship_flat,ship_city,ship_state,ship_postal_code,ship_country,ship_telephone,ship_email,ship_add_info,order_delivery,order_pay,order_comments,order_ip,order_hash,invoice_date,invoice_amount,invoice_currency_code,invoice_exch_rate,invoice_paid,invoice_paid_date) VALUES ("
               . $sequence_array['sequence_value_string'] . "" . $shop_user_id . ",'" . $order_amount . "','" . $order_date . "',NULL,'" . $order_status . "',"
               . $dataArray['shop_ship_name'] . "," . $dataArray['shop_ship_street'] . "," . $dataArray['shop_ship_homenumber'] . ","
               . $dataArray['shop_ship_flatnumber'] . "," . $dataArray['shop_ship_city'] . "," . $dataArray['shop_ship_state'] . ","
               . $dataArray['shop_ship_postalcode'] . "," . $dataArray['shop_ship_country'] . "," . $dataArray['shop_ship_telephone'] . ","
               . $dataArray['shop_ship_email'] . "," . $dataArray['shop_ship_addaddrnotes'] . ",'" . $dataArray['shop_order_delivery'] . "','"
               . $dataArray['shop_order_pay'] . "'," . $dataArray['shop_order_comments'] . "," . $order_ip . "," . $orderHash . ","
               . "NULL,NULL,NULL,NULL,'0',NULL)";
        $insert_result = $this->db->Execute($sql);

        if ($insert_result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        } else {

            if (empty($order_id)) {
                $order_id = $this->registry->main_class->db_get_last_insert_id();
            }

        }

        $items_info = $this->shop_cart_items_all;
        for ($i = 0, $size = count($items_info); $i < $size; ++$i) //foreach($_SESSION[$this->systemdk_shop_cart] as $item_id => $qty)
        {
            $item_id = intval($items_info[$i]['item_id']);
            $order_item_name = $this->registry->main_class->processing_data($items_info[$i]['item_name']);
            $order_item_price = $this->registry->main_class->float_to_db($items_info[$i]['item_price']);
            $order_item_quantity = intval($items_info[$i]['item_qty']);
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . '_order_items_id_' . $this->registry->sitelang, 'order_item_id');
            $sql = "INSERT INTO " . PREFIX . "_order_items_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "order_id,item_id,order_item_name,order_item_price,order_item_quantity) VALUES (" . $sequence_array['sequence_value_string'] . "'" . $order_id
                   . "','" . $item_id . "'," . $order_item_name . ",'" . $order_item_price . "','" . $order_item_quantity . "')";
            $insert_result2 = $this->db->Execute($sql);

            if ($insert_result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $insert_result = false;
            }

            if (!empty($items_info[$i]['selected_additional_params']) && is_array($items_info[$i]['selected_additional_params'])
                && count(
                       $items_info[$i]['selected_additional_params']
                   ) > 0
            ) {
                $orderSuborderId = 1;
                foreach ($items_info[$i]['selected_additional_params'] as $iterationKey => $iterationData) {
                    foreach ($iterationData as $groupKey => $groupData) {
                        $orderItemParamGroupId = $groupData['group_id'];
                        foreach ($groupData['group_data'] as $paramData) {
                            $orderItemParamId = $paramData['param_id'];
                            /**
                             * we use .._s_order_i_param_id_.. and not {tablename}_id.. because in oracle sequence name should be no more then 30 symbols
                             * and differ from table name
                             */
                            $sequence_array =
                                $this->registry->main_class->db_process_sequence(PREFIX . '_s_order_i_param_id_' . $this->registry->sitelang, 'id');
                            $sql = "INSERT INTO " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " "
                                   . "(" . $sequence_array['field_name_string']
                                   . "order_id,order_item_id,order_suborder_id,order_item_param_group_id,order_item_param_id) "
                                   . "VALUES (" . $sequence_array['sequence_value_string'] . "'" . $order_id . "','" . $item_id . "'," . $orderSuborderId . ",'"
                                   . $orderItemParamGroupId . "','" . $orderItemParamId . "')";
                            $insertOrderItemParamResult = $this->db->Execute($sql);

                            if ($insertOrderItemParamResult === false) {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $error[] = ["code" => $error_code, "message" => $error_message];
                                $insert_result = false;
                            }

                        }
                    }
                    $orderSuborderId++;
                }
            }
            $item_quantity = intval($items_info[$i]['item_quantity']);
            $item_quantity_unlim = intval($items_info[$i]['item_quantity_unlim']);

            if ($item_quantity_unlim == 0) {
                $item_quantity = intval($item_quantity - $order_item_quantity);

                if ($item_quantity < 0) {
                    $item_quantity = 0;
                }

                $sql = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_quantity = '" . $item_quantity . "' WHERE item_id = "
                       . $item_id;
                $insert_result3 = $this->db->Execute($sql);

                if ($insert_result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                    $insert_result = false;
                } else {
                    $item_catalog_id = intval($items_info[$i]['item_catalog_id']);
                    $item_category_id = intval($items_info[$i]['item_category_id']);
                    $item_subcategory_id = intval($items_info[$i]['item_subcategory_id']);

                    if ($item_catalog_id > 0) {
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $item_catalog_id);
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $item_catalog_id);

                        if (intval($items_info[$i]['item_display']) == 3) {
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                        }

                    } elseif ($item_category_id > 0) {
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_categ_cat_id']) . "|categ" . $item_category_id
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_categ_cat_id']) . "|categ" . $item_category_id
                        );

                        if (intval($items_info[$i]['item_display']) == 3) {
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_categ_cat_id'])
                            );
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_categ_cat_id'])
                            );
                        } elseif (intval($items_info[$i]['item_display']) == 2) {
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_categ_cat_id'])
                            );
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_categ_cat_id'])
                            );
                        }

                    } elseif ($item_subcategory_id > 0) {
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_subcateg_cat_id']) . "|categ" . intval(
                                $items_info[$i]['item_subcateg_categ_id']
                            ) . "|subcateg" . $item_subcategory_id
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_subcateg_cat_id']) . "|categ" . intval(
                                $items_info[$i]['item_subcateg_categ_id']
                            ) . "|subcateg" . $item_subcategory_id
                        );

                        if (intval($items_info[$i]['item_display']) == 3) {
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_subcateg_cat_id'])
                            );
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_subcateg_cat_id'])
                            );
                        } elseif (intval($items_info[$i]['item_display']) == 2) {
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_subcateg_cat_id'])
                            );
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_subcateg_cat_id'])
                            );
                        } elseif (intval($items_info[$i]['item_display']) == 1) {
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|other|cat" . intval($items_info[$i]['item_subcateg_cat_id']) . "|categ" . intval(
                                    $items_info[$i]['item_subcateg_categ_id']
                                )
                            );
                            $this->registry->main_class->clearCache(
                                null, $this->registry->sitelang . "|modules|shop|admin|cat" . intval($items_info[$i]['item_subcateg_cat_id']) . "|categ" . intval(
                                    $items_info[$i]['item_subcateg_categ_id']
                                )
                            );
                        }

                    } elseif ($item_catalog_id == 0 && $item_category_id == 0 && $item_subcategory_id == 0) {
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                    }

                }
            }
        }
        $this->db->CompleteTrans();

        if ($insert_result === false) {
            $this->error = 'not_insert';
            $this->error_array = $error;

            return;
        }

        unset($_SESSION[$this->systemdk_shop_total_price]);
        unset($_SESSION[$this->systemdk_shop_items]);
        unset($_SESSION[$this->systemdk_shop_cart]);
        $this->result['systemdk_shop_order_id'] = $order_id;
        $mailData['order_id'] = $order_id;
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders|0");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders|1");
        $this->processOrderNotifyMail($dataArray, $order_id);

        if (isset($this->registry->paymentSystemData['shop_system_id']) && $this->registry->paymentSystemData['shop_system_id'] > 0) {
            $this->processOrderMail($mailData);
            $this->result['shop_invoice_url'] = $this->getInvoiceUrl($order_id, $mailData['hash']);
        }

        $this->result['shop_message'] = "add_ok";
    }


    public function payOnline($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $keys = [
            'order_id'                          => 'integer',
            'hash'                              => 'string',
            'payment_error_notify_mail_subject' => 'string',
            'payment_error_notify_mail_content' => 'html',
        ];
        foreach ($keys as $key => $valueType) {

            if (isset($array[$key])) {

                if ($valueType === 'integer') {
                    $dataArray[$key] = intval($array[$key]);
                } elseif ($valueType === 'html') {
                    $dataArray[$key] = $array[$key];
                } else {
                    $dataArray[$key] = trim($this->registry->main_class->format_striptags($array[$key]));
                }

            }

        }
        $errorData = [
            'request_url'  => $this->registry->main_class->get_site_url() . 'index.php?path=shop',
            'method'       => 'pay_online',
            'order_number' => empty($dataArray['order_id']) ? 0 : $dataArray['order_id'],
            'request_data' => [
                'order_id' => empty($dataArray['order_id']) ? 0 : $dataArray['order_id'],
                'hash'     => empty($dataArray['hash']) ? '' : empty($dataArray['hash']),
            ],
        ];
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;

        if (empty($dataArray['order_id']) || empty($dataArray['hash']) || $dataArray['order_id'] < 1 || !isset($dataArray['payment_error_notify_mail_subject'])
            || !isset($dataArray['payment_error_notify_mail_content'])
        ) {
            $this->error = 'pay_empty_data';
            $errorData['error_data'] = 'Empty payment data in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $dataArray['hash'] = $this->registry->main_class->processing_data($dataArray['hash']);
        $sql =
            "SELECT a.order_id,a.order_amount,a.ship_name,a.order_status,a.order_hash,b.delivery_name,b.delivery_price,c.payment_name,c.payment_commission,d.id as system_id,d.system_class,d.system_integ_type FROM "
            . PREFIX . "_orders_" . $this->registry->sitelang . " a, "
            . PREFIX . "_delivery_" . $this->registry->sitelang . " b, "
            . PREFIX . "_s_payment_methods_" . $this->registry->sitelang . " c, "
            . PREFIX . "_s_payment_systems d "
            . "WHERE "
            . "a.order_delivery = b.delivery_id and b.delivery_status = '1' and "
            . "a.order_pay = c.id and c.payment_status = '1' and c.payment_system_id IS NOT NULL and "
            . "c.payment_system_id = d.id and d.system_status = '1' and "
            . "a.order_id = '" . $dataArray['order_id'] . "' and a.order_hash = " . $dataArray['hash'];
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->error = 'system_error';
            $errorData['error_data'] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {
            $this->error = 'pay_order_not_found';
            $errorData['error_data'] = 'Order not found in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $orderId = intval($result->fields['0']);
        $orderAmount = floatval($result->fields['1']);
        $shipName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $orderStatus = intval($result->fields['3']);
        $orderHash = $this->registry->main_class->extracting_data($result->fields['4']);
        $deliveryName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $deliveryAmount = floatval($result->fields['6']);
        $paymentName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
        $paymentCommission = floatval($result->fields['8']);
        $systemId = intval($result->fields['9']);
        $errorData['system_id'] = $systemId;
        $systemClass = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
        $systemIntegType = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));

        if ($orderStatus > 5) {
            $this->error = 'order_can_not_be_paid';
            $errorData['error_data'] = 'Order with status ' . $orderStatus . ' not payable in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        if (!class_exists($systemClass)) {
            $this->error = 'payment_system_not_found';
            $errorData['error_data'] = 'Payment system class ' . $systemClass . ' not found in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $this->registry->paymentSystemData = [
            'shop_process_type'                      => 'invoicing',
            'shop_system_id'                         => $systemId,
            'shop_system_integration_type'           => $systemIntegType,
            'shop_order_id'                          => $orderId,
            'shop_order_hash'                        => $orderHash,
            'shop_currency'                          => $this->systemdk_shop_valuta,
            'shop_order_notify_custom_mailbox'       => $this->systemdk_shop_order_notify_custom_mailbox,
            'shop_payment_error_notify_mail_subject' => $dataArray['payment_error_notify_mail_subject'],
            'shop_payment_error_notify_mail_content' => $dataArray['payment_error_notify_mail_content'],
        ];

        try {
            /**
             * @var shop_portmone $paymentSystem
             */
            $paymentSystem = singleton::getinstance($systemClass, $this->registry);
            $invoiceAmount = $paymentSystem->calculateInvoice($orderAmount, $deliveryAmount, $paymentCommission);
            $paymentSystem->createInvoice();
            $this->result = [
                'payment_template'      => $paymentSystem->getTemplate(),
                'payment_form_data'     => $paymentSystem->getFormData(),
                'order_additional_data' => [
                    'invoice_life_time'        => $paymentSystem->getInvoiceLifeTime(),
                    'order_ship_name'          => $shipName,
                    'order_amount'             => $this->registry->main_class->number_format($orderAmount),
                    'shop_currency_code'       => $this->systemdk_shop_valuta,
                    'order_delivery_name'      => $deliveryName,
                    'order_delivery_amount'    => $this->registry->main_class->number_format($deliveryAmount),
                    'order_payment_name'       => $paymentName,
                    'order_payment_commission' => $this->registry->main_class->number_format($paymentCommission),
                    'invoice_exchange_rate'    => $this->registry->main_class->number_format($paymentSystem->getInvoiceExchangeRate()),
                    'invoice_amount'           => $this->registry->main_class->number_format($invoiceAmount),
                    'invoice_currency_code'    => $paymentSystem->getInvoiceCurrencyCode(),
                ],
            ];
            $this->getTermsAndConditions();
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|" . $orderId);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|edit" . $orderId);
        } catch (system_exception $exception) {
            $error[] = ["code" => $exception->getCode(), "message" => $exception->getMessage()];
            $this->error = 'create_invoice_error';
            $this->error_array = $error;

            return;
        }
    }


    public function payDone($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $keys = [
            'order_id'                          => 'integer',
            'hash'                              => 'string',
            'payment_error_notify_mail_subject' => 'string',
            'payment_error_notify_mail_content' => 'html',
        ];
        foreach ($keys as $key => $valueType) {

            if (isset($array[$key])) {

                if ($valueType === 'integer') {
                    $dataArray[$key] = intval($array[$key]);
                } elseif ($valueType === 'html') {
                    $dataArray[$key] = $array[$key];
                } else {
                    $dataArray[$key] = trim($this->registry->main_class->format_striptags($array[$key]));
                }

            }

        }
        $errorData = [
            'request_url'  => $this->registry->main_class->get_site_url() . 'index.php?path=shop',
            'method'       => 'pay_done',
            'order_number' => empty($dataArray['order_id']) ? 0 : $dataArray['order_id'],
            'request_data' => [
                'order_id' => empty($dataArray['order_id']) ? 0 : $dataArray['order_id'],
                'hash'     => empty($dataArray['hash']) ? '' : $dataArray['hash'],
            ],
        ];
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;

        if (empty($dataArray['order_id']) || empty($dataArray['hash']) || $dataArray['order_id'] < 1 || !isset($dataArray['payment_error_notify_mail_subject'])
            || !isset($dataArray['payment_error_notify_mail_content'])
        ) {
            $this->error = 'paid_empty_data';
            $errorData['error_data'] = 'Empty payment data in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $dataArray['hash'] = $this->registry->main_class->processing_data($dataArray['hash']);
        $sql =
            "SELECT a.order_id,a.order_status,a.invoice_date,a.invoice_amount,c.id as system_id,c.system_class,c.system_integ_type FROM "
            . PREFIX . "_orders_" . $this->registry->sitelang . " a, "
            . PREFIX . "_s_payment_methods_" . $this->registry->sitelang . " b, "
            . PREFIX . "_s_payment_systems c "
            . "WHERE "
            . "a.order_pay = b.id and b.payment_system_id IS NOT NULL and "
            . "b.payment_system_id = c.id and c.system_status = '1' and "
            . "a.order_id = '" . $dataArray['order_id'] . "' and a.order_hash = " . $dataArray['hash'];
        $result = $this->db->Execute($sql);

        if (!$result) {
            $this->error = 'paid_system_error';
            $errorData['error_data'] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {
            $this->error = 'paid_order_not_found';
            $errorData['error_data'] = 'Order not found in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $orderId = intval($result->fields['0']);
        $orderStatus = intval($result->fields['1']);
        $invoiceTimestamp = intval($result->fields['2']);
        $invoiceAmount = floatval($result->fields['3']);
        $systemId = intval($result->fields['4']);
        $errorData['system_id'] = $systemId;
        $systemClass = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $systemIntegType = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $now = $this->registry->main_class->get_time();

        if ($now > ($invoiceTimestamp + (shop_payment::INVOICE_LIFE_TIME * 60))) {
            $this->error = 'invoice_not_actual';
            $errorData['error_data'] =
                'Invoice with max pay date ' . date("d.m.y H:i", $invoiceTimestamp + (shop_payment::INVOICE_LIFE_TIME * 60)) . ' not actual because now '
                . date("d.m.y H:i", $now) . ' in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        if ($orderStatus > 5) {
            $this->error = 'order_not_payable';
            $errorData['error_data'] = 'Order with status ' . $orderStatus . ' not payable in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        if (!class_exists($systemClass)) {
            $this->error = 'paid_payment_system_not_found';
            $errorData['error_data'] = 'Payment system class ' . $systemClass . ' not found in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $this->registry->paymentSystemData = [
            'shop_process_type'                      => 'check_payment',
            'shop_system_id'                         => $systemId,
            'shop_system_integration_type'           => $systemIntegType,
            'shop_order_id'                          => $orderId,
            'shop_currency'                          => $this->systemdk_shop_valuta,
            'shop_order_notify_custom_mailbox'       => $this->systemdk_shop_order_notify_custom_mailbox,
            'shop_payment_error_notify_mail_subject' => $dataArray['payment_error_notify_mail_subject'],
            'shop_payment_error_notify_mail_content' => $dataArray['payment_error_notify_mail_content'],
        ];

        try {
            /**
             * @var shop_portmone $paymentSystem
             */
            $paymentSystem = singleton::getinstance($systemClass, $this->registry);
            $result = $paymentSystem->isPaid($invoiceAmount);

            if (!$result) {
                $this->error = 'not_paid_or_incorrect_amount';

                return;
            }
        } catch (system_exception $exception) {
            $error[] = ["code" => $exception->getCode(), "message" => $exception->getMessage()];
            $this->error = 'paid_check_payment_error';
            $this->error_array = $error;

            return;
        }

        for ($i = 0; $i <= 5; $i++) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders|" . $i);
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|" . $orderId);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|edit" . $orderId);
        $this->result['shop_message'] = 'order_paid';
    }


    public function payError($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $keys = [
            'order_id'                          => 'integer',
            'hash'                              => 'string',
            'payment_error_notify_mail_subject' => 'string',
            'payment_error_notify_mail_content' => 'html',
        ];
        foreach ($keys as $key => $valueType) {

            if (isset($array[$key])) {

                if ($valueType === 'integer') {
                    $dataArray[$key] = intval($array[$key]);
                } elseif ($valueType === 'html') {
                    $dataArray[$key] = $array[$key];
                } else {
                    $dataArray[$key] = trim($this->registry->main_class->format_striptags($array[$key]));
                }

            }

        }
        $errorData = [
            'request_url'  => $this->registry->main_class->get_site_url() . 'index.php?path=shop',
            'method'       => 'pay_error',
            'order_number' => empty($dataArray['order_id']) ? 0 : $dataArray['order_id'],
            'request_data' => [
                'order_id' => empty($dataArray['order_id']) ? 0 : $dataArray['order_id'],
                'hash'     => empty($dataArray['hash']) ? '' : $dataArray['hash'],
            ],
        ];
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        $this->error = 'external_payment_error';

        if (empty($dataArray['order_id']) || empty($dataArray['hash']) || $dataArray['order_id'] < 1 || !isset($dataArray['payment_error_notify_mail_subject'])
            || !isset($dataArray['payment_error_notify_mail_content'])
        ) {
            $errorData['error_data'] = 'Error while processing payment on Payment system side; Empty payment data in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $dataArray['hash'] = $this->registry->main_class->processing_data($dataArray['hash']);
        $sql =
            "SELECT a.order_id,a.order_status,a.invoice_date,a.invoice_amount,c.id as system_id,c.system_class,c.system_integ_type FROM "
            . PREFIX . "_orders_" . $this->registry->sitelang . " a, "
            . PREFIX . "_s_payment_methods_" . $this->registry->sitelang . " b, "
            . PREFIX . "_s_payment_systems c "
            . "WHERE "
            . "a.order_pay = b.id and b.payment_system_id IS NOT NULL and "
            . "b.payment_system_id = c.id and c.system_status = '1' and "
            . "a.order_id = '" . $dataArray['order_id'] . "' and a.order_hash = " . $dataArray['hash'];
        $result = $this->db->Execute($sql);

        if (!$result) {
            $errorData['error_data'] = ["code" => $this->db->ErrorNo(), "message" => 'Error while processing payment on Payment system side; ' . $this->db->ErrorMsg()];
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {
            $errorData['error_data'] = 'Error while processing payment on Payment system side; Order not found in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $orderId = intval($result->fields['0']);
        $systemId = intval($result->fields['4']);
        $errorData['system_id'] = $systemId;
        $systemClass = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $systemIntegType = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));

        if (!class_exists($systemClass)) {
            $errorData['error_data'] =
                'Error while processing payment on Payment system side; Payment system class ' . $systemClass . ' not found in class:' . __CLASS__ . ', line:' . __LINE__;
            $this->saveErrorToPaymentLog($errorData);

            return;
        }

        $this->registry->paymentSystemData = [
            'shop_process_type'                      => 'check_payment',
            'shop_system_id'                         => $systemId,
            'shop_system_integration_type'           => $systemIntegType,
            'shop_order_id'                          => $orderId,
            'shop_currency'                          => $this->systemdk_shop_valuta,
            'shop_order_notify_custom_mailbox'       => $this->systemdk_shop_order_notify_custom_mailbox,
            'shop_payment_error_notify_mail_subject' => $dataArray['payment_error_notify_mail_subject'],
            'shop_payment_error_notify_mail_content' => $dataArray['payment_error_notify_mail_content'],
        ];

        try {
            /**
             * @var shop_portmone $paymentSystem
             */
            $paymentSystem = singleton::getinstance($systemClass, $this->registry);
            $paymentSystem->ExternalPaymentError();
        } catch (system_exception $exception) {

            return;
        }
    }


    /**
     * Save error data which is not defined to specific payment system to payment log. Other logs in model_shop_payment.class.php
     *
     * @param array $params
     *
     * @return bool
     */
    private function saveErrorToPaymentLog($params)
    {
        if (!$this->systemdk_shop_log_other_payment_errors) {
            return true;
        }

        $this->paymentLog->clear();
        $data = [];

        if (!empty($params)) {
            $data = array_merge($data, $params);
        }

        $data['process_status'] = 'exception';
        $result = $this->paymentLog->save($data);

        return $result;
    }


    /**
     * Send new order notification email to admin if necessary
     *
     * @param array $dataArray
     * @param int   $orderId
     *
     * @return bool
     */
    private function processOrderNotifyMail($dataArray, $orderId)
    {
        if (!$this->orderNotifyMail()) {
            return true;
        }

        if (empty($dataArray['notify_order_mail_content']) || empty($dataArray['notify_order_mail_subject'])) {
            return false;
        }

        $dataArray['notify_order_mail_content'] = str_replace("modules_shop_order_id", $orderId, $dataArray['notify_order_mail_content']);
        $dataArray['notify_order_mail_content'] = str_replace(
            "module_shop_order_url",
            $this->registry->main_class->get_site_url() . 'systemadmin/index.php?path=admin_shop&func=shop_orderproc&order_id=' . $orderId . '&lang='
            . $this->registry->sitelang,
            $dataArray['notify_order_mail_content']
        );
        $dataArray['notify_order_mail_content'] =
            str_replace("modules_shop_site_url", $this->registry->main_class->get_site_url(), $dataArray['notify_order_mail_content']);
        $this->registry->mail->from = ADMIN_EMAIL;
        $receiverEmail = ADMIN_EMAIL;

        if ($this->systemdk_shop_order_notify_type == 'custom' && !empty($this->systemdk_shop_order_notify_custom_mailbox)) {
            $receiverEmail = $this->systemdk_shop_order_notify_custom_mailbox;
        }

        $this->registry->mail->sendmail($receiverEmail, $dataArray['notify_order_mail_subject'] . $orderId, $dataArray['notify_order_mail_content']);

        if ($this->registry->mail->send_error) {
            return false;
        }

        return true;
    }


    /**
     * Send email to user with url link to invoice
     *
     * @param array $data
     *
     * @return bool
     */
    private function processOrderMail($data)
    {
        if (empty($data['order_mail_content']) || empty($data['order_mail_subject'])) {
            return false;
        }

        $data['order_mail_content'] = str_replace("modules_shop_ship_name", $data['ship_name'], $data['order_mail_content']);
        $data['order_mail_content'] = str_replace("modules_shop_site_url", $this->registry->main_class->get_site_url(), $data['order_mail_content']);
        $data['order_mail_content'] = str_replace("modules_shop_order_id", $data['order_id'], $data['order_mail_content']);
        $data['order_mail_content'] = str_replace("modules_shop_order_url", $this->getInvoiceUrl($data['order_id'], $data['hash']), $data['order_mail_content']);
        $data['order_mail_content'] = str_replace("modules_shop_invoice_life_time", shop_payment::INVOICE_LIFE_TIME, $data['order_mail_content']);
        $this->registry->mail->from = ADMIN_EMAIL;
        $this->registry->mail->sendmail($data['ship_email'], $data['order_mail_subject'] . $data['order_id'], $data['order_mail_content']);

        if ($this->registry->mail->send_error) {
            return false;
        }

        return true;
    }


    /**
     * Get invoice url
     *
     * @param int    $orderId
     * @param string $orderHash
     *
     * @return string
     */
    private function getInvoiceUrl($orderId, $orderHash)
    {
        $url = $this->registry->main_class->get_site_url() . 'index.php?path=shop&func=pay_online&order_id=' . $orderId . '&order_hash=' . $orderHash
               . '&lang=' . $this->registry->sitelang;

        if ($this->registry->main_class->get_mode_rewrite()) {
            $url = $this->registry->main_class->get_site_url() . $this->registry->sitelang . '/shop/pay_online/' . $orderId . '/' . $orderHash;
        }

        return $url;
    }


    /**
     * Check if need send notify mail about new order
     *
     * @return bool
     */
    public function orderNotifyMail()
    {
        if ($this->systemdk_shop_order_notify_email) {
            return true;
        }

        return false;
    }


    /**
     * Get shop terms and conditions data
     */
    private function getTermsAndConditions()
    {
        $sql = "SELECT system_page_id,system_page_title,system_page_content FROM " . PREFIX . "_system_pages "
               . "WHERE system_page_name = 'shop_terms_and_conditions' AND system_page_lang = '" . $this->registry->language . "'";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'system_error';

            return;
        }

        $rowExist = 0;

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        }

        if ($rowExist < 1) {

            return;
        }

        $this->result['shop_terms_and_conditions_data'] = [
            'title'   => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
            'content' => $this->registry->main_class->extracting_data($result->fields['2']),
        ];
        $this->result['jquery_ui'] = "yes";
        $this->result['jquery'] = "yes";
    }
}
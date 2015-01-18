<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class shop extends model_base {


    private $error;
    private $error_array;
    private $result;
    private $systemdk_shop_cart;
    private $systemdk_shop_items;
    private $systemdk_shop_total_price;
    private $systemdk_shop_catalogs_order;
    private $systemdk_shop_homeitems_perpage;
    private $systemdk_shop_homeitems_order;
    private $systemdk_shop_categories_order;
    private $systemdk_shop_itemimage_path;
    private $systemdk_shop_itemimage_position;
    private $systemdk_shop_itemimage_width;
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
    private $shop_order_delivery_id;
    private $shop_order_pay_id;


    public function __construct($registry) {
        parent::__construct($registry);
        require_once('shop_config.inc');
        $this->systemdk_shop_cart = 'systemdk_shop_cart_'.$this->registry->sitelang;
        $this->systemdk_shop_items = 'systemdk_shop_items_'.$this->registry->sitelang;
        $this->systemdk_shop_total_price = 'systemdk_shop_total_price_'.$this->registry->sitelang;
        $this->systemdk_shop_catalogs_order = SYSTEMDK_SHOP_CATALOGS_ORDER;
        $this->systemdk_shop_homeitems_perpage = SYSTEMDK_SHOP_HOMEITEMS_PERPAGE;
        $this->systemdk_shop_homeitems_order = SYSTEMDK_SHOP_HOMEITEMS_ORDER;
        $this->systemdk_shop_categories_order = SYSTEMDK_SHOP_CATEGORIES_ORDER;
        $this->systemdk_shop_itemimage_path = SYSTEMDK_SHOP_ITEMIMAGE_PATH;
        $this->systemdk_shop_itemimage_position = SYSTEMDK_SHOP_ITEMIMAGE_POSITION;
        $this->systemdk_shop_itemimage_width = SYSTEMDK_SHOP_ITEMIMAGE_WIDTH;
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
    }


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function shop_header($data_array) {
        $systemdk_shop_cart = 'systemdk_shop_cart_'.$this->registry->sitelang;
        $systemdk_shop_items = 'systemdk_shop_items_'.$this->registry->sitelang;
        $systemdk_shop_total_price = 'systemdk_shop_total_price_'.$this->registry->sitelang;
        if((isset($data_array['path']) and isset($data_array['func']) and $this->registry->main_class->format_striptags(trim($data_array['path'])) == "shop" and $this->registry->main_class->format_striptags(trim($data_array['func'])) == "cart")) {
            if(isset($data_array['shop_new']) and intval($data_array['shop_new']) != 0) {
                $systemdk_shop_new_item = intval($data_array['shop_new']);
                if(!isset($_SESSION[$systemdk_shop_cart])) {
                    $_SESSION[$systemdk_shop_cart] = array();
                    $_SESSION[$systemdk_shop_items] = 0;
                    $_SESSION[$systemdk_shop_total_price] = 0.00;
                }
                if(isset($_SESSION[$systemdk_shop_cart][$systemdk_shop_new_item])) {
                    $_SESSION[$systemdk_shop_cart][$systemdk_shop_new_item]++;
                } else {
                    $_SESSION[$systemdk_shop_cart][$systemdk_shop_new_item] = 1;
                }
                $calculate_result = $this->shop_calculate_price($_SESSION[$systemdk_shop_cart]);
                $_SESSION[$systemdk_shop_total_price] = $calculate_result['2'];
                $_SESSION[$systemdk_shop_items] = $calculate_result['1'];
            }
            if(isset($data_array['shop_save'])) {
                foreach($_SESSION[$systemdk_shop_cart] as $item_id => $qty) {
                    if((isset($data_array['shop_items'][$item_id]) and intval($data_array['shop_items'][$item_id]) == 0) or !isset($data_array['shop_items'][$item_id]) or intval($data_array['shop_items'][$item_id]) == 0) {
                        unset($_SESSION[$systemdk_shop_cart][$item_id]);
                    } else {
                        $_SESSION[$systemdk_shop_cart][$item_id] = $data_array['shop_items'][$item_id];
                    }
                }
                $calculate_result = $this->shop_calculate_price($_SESSION[$systemdk_shop_cart]);
                $_SESSION[$systemdk_shop_total_price] = $calculate_result['2'];
                $_SESSION[$systemdk_shop_items] = $calculate_result['1'];
            }
        }
        if(!isset($_SESSION[$systemdk_shop_items])) {
            $_SESSION[$systemdk_shop_items] = 0;
        }
        if(!isset($_SESSION[$systemdk_shop_total_price])) {
            $_SESSION[$systemdk_shop_total_price] = 0.00;
        }
        $result['shop_carttotal_items'] = $_SESSION[$systemdk_shop_items];
        $result['shop_carttotal_price'] = number_format($_SESSION[$systemdk_shop_total_price],2,'.',' ');
        if($this->registry->main_class->is_admin()) {
            $result['shop_carttotal_display'] = "no";
        } else {
            $result['shop_carttotal_display'] = "yes";
        }
        return $result;
    }


    private function shop_calculate_price($cart = 0) {
        $systemdk_shop_items = 0;
        $systemdk_shop_price = 0.0;
        if($cart != 0 and is_array($cart) and count($cart) > 0) {
            $i = 0;
            $item_id = null;
            foreach($cart as $cart_item_id => $qty) {
                if(isset($cart_item_id) and intval($cart_item_id) != 0) {
                    if($i == 0) {
                        $item_id = "'".intval($cart_item_id)."'";
                    } else {
                        $item_id .= ",'".intval($cart_item_id)."'";
                    }
                    $i++;
                }
            }
            if(isset($item_id)) {
                $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL WHERE a.item_id IN (".$item_id.") and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL) or (a.item_category_id is NOT NULL) or (a.item_subcategory_id is NOT NULL)) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
                $sql = "SELECT a.item_id,a.item_price,a.item_show,a.item_price_discounted ".$from;
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $row_exist = intval($result->fields['0']);
                    } else {
                        $row_exist = 0;
                    }
                    if($row_exist > 0) {
                        while(!$result->EOF) {
                            $item_id = intval($result->fields['0']);
                            $systemdk_shop_item_price = floatval($result->fields['1']);
                            $systemdk_shop_item_price_discounted = floatval($result->fields['3']);
                            if(isset($cart[$item_id])) {
                                $item_qty = $cart[$item_id];
                            } else {
                                $item_qty = 0;
                            }
                            if($systemdk_shop_item_price_discounted > 0) {
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
        return array(1 => $systemdk_shop_items,2 => $systemdk_shop_price);
    }


    public function index($num_page = false) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(intval($num_page) !== 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_shop_homeitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if(empty($this->systemdk_shop_catalogs_order)) {
            $this->systemdk_shop_catalogs_order = 'catalog_position';
        }
        if(empty($this->systemdk_shop_categories_order)) {
            $this->systemdk_shop_categories_order = 'category_position';
        }
        if(empty($this->systemdk_shop_homeitems_order)) {
            $this->systemdk_shop_homeitems_order = 'item_position';
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        $sql = "SELECT a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_date,b.category_id,b.category_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and (b.category_id is NULL or b.category_show = '1') WHERE a.catalog_show = '1' ORDER BY a.".$this->systemdk_shop_catalogs_order.",b.".$this->systemdk_shop_categories_order;
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'categories_error';
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $catalog_id = intval($result->fields['0']);
                $catalog_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                if(!isset($catalog_img) or $catalog_img == "") {
                    $catalog_img = "no";
                }
                $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                //$catalog_date = intval($result->fields['3']);
                //$catalog_date = date("d.m.y H:i",$catalog_date);
                if(isset($result->fields['4']) and intval($result->fields['4']) > 0) {
                    $category_id = intval($result->fields['4']);
                    $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    $categories_all[$catalog_id][] = array(
                        "category_id" => $category_id,
                        "category_name" => $category_name
                    );
                } else {
                    $categories_all[$catalog_id] = "no";
                }
                $catalogs_all[$catalog_id] = array(
                    "catalog_id" => $catalog_id,
                    "catalog_img" => $catalog_img,
                    "catalog_name" => $catalog_name,
                    "categories_all" => $categories_all[$catalog_id]
                );
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
        $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' WHERE a.item_show = '1' and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL and a.item_display = '3') or (a.item_category_id is NOT NULL and a.item_display = '3') or (a.item_subcategory_id is NOT NULL and a.item_display = '3')) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items ".$from;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_homeitems_order;
        $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist == 0 and $num_page > 1) {
                $this->error = 'unknown_page';
                return;
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if(!isset($item_img) or $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = number_format($result->fields['3'],2,'.',' ');
                    $item_price_discounted = number_format($result->fields['4'],2,'.',' ');
                    $item_catalog_id = intval($result->fields['5']);
                    if($item_catalog_id > 0) {
                        $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                    } else {
                        $item_catalog_name = "no";
                    }
                    $item_category_id = intval($result->fields['6']);
                    if($item_category_id > 0) {
                        $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                        $item_categ_cat_id = intval($result->fields['20']);
                    } else {
                        $item_category_name = "no";
                        $item_categ_cat_id = 0;
                    }
                    $item_subcategory_id = intval($result->fields['7']);
                    if($item_subcategory_id > 0) {
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
                    $item_date = date("d.m.y H:i",$item_date);
                    $items_all[] = array(
                        "item_id" => $item_id,
                        "item_img" => $item_img,
                        "item_name" => $item_name,
                        "item_price" => $item_price,
                        "item_price_discounted" => $item_price_discounted,
                        "item_catalog_id" => $item_catalog_id,
                        "item_category_id" => $item_category_id,
                        "item_subcategory_id" => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity" => $item_quantity,
                        "item_quantity_unlim" => $item_quantity_unlim,
                        "item_quantity_param" => $item_quantity_param,
                        "item_display" => $item_display,
                        "item_date" => $item_date,
                        "item_catalog_name" => $item_catalog_name,
                        "item_category_name" => $item_category_name,
                        "item_subcategory_name" => $item_subcategory_name,
                        "item_categ_cat_id" => $item_categ_cat_id,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id" => $item_subcateg_cat_id
                    );
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if(isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if($num_pages > 1) {
                    if($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for($i = 1;$i < $num_pages + 1;$i++) {
                        if($i == $num_page) {
                            $html[] = array("number" => $i,"param1" => "1");
                        } else {
                            $pagelink = 5;
                            if(($i > $num_page) and ($i < $num_page + $pagelink) or ($i < $num_page) and ($i > $num_page - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "2");
                            }
                            if(($i == $num_pages) and ($num_page < $num_pages - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "3");
                            }
                            if(($i == 1) and ($num_page > $pagelink + 1)) {
                                $html[] = array("number" => $i,"param1" => "4");
                            }
                        }
                    }
                    if($num_page < $num_pages) {
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


    public function categs($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if(isset($array['num_page']) and intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if(!isset($array['catalog_id']) or (isset($array['catalog_id']) and intval($array['catalog_id']) == 0)) {
            $this->error = 'not_all_data';
            return;
        }
        $this->result['shop_catalog_id'] = $catalog_id;
        $num_string_rows = $this->systemdk_shop_catitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!isset($this->systemdk_shop_categories_order)) {
            $this->systemdk_shop_categories_order = 'category_position';
        }
        if(!isset($this->systemdk_shop_subcategories_order)) {
            $this->systemdk_shop_subcategories_order = 'subcategory_position';
        }
        if(!isset($this->systemdk_shop_catitems_order)) {
            $this->systemdk_shop_catitems_order = 'item_position';
        }
        $sql = "SELECT b.category_id,b.category_img,b.category_name,b.category_date,a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_description,a.catalog_meta_title,a.catalog_meta_keywords,a.catalog_meta_description,c.subcategory_id,c.subcategory_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and (b.category_id is NULL or b.category_show = '1') LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and (c.subcategory_id is NULL or c.subcategory_show = '1') WHERE a.catalog_id = ".$catalog_id." and a.catalog_show = '1' ORDER BY b.".$this->systemdk_shop_categories_order.",c.".$this->systemdk_shop_subcategories_order;
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'categories_error';
            return;
        }
        if(isset($result->fields['4'])) {
            $row_exist = intval($result->fields['4']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->error = 'not_find_catalog';
            return;
        }
        $catalog_id = intval($result->fields['4']);
        $catalog_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        if(!isset($catalog_img) or $catalog_img == "") {
            $catalog_img = "no";
        }
        $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $catalog_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
        $catalog_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
        $catalog_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
        $catalog_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
        $this->result['shop_catalog_img'] = $catalog_img;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->result['shop_catalog_description'] = $catalog_description;
        $this->registry->main_class->set_sitemeta($catalog_meta_title,$catalog_meta_description,$catalog_meta_keywords);
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $category_id = intval($result->fields['0']);
                $category_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                if(!isset($category_img) or $category_img == "") {
                    $category_img = "no";
                }
                $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                //$category_date = intval($result->fields['3']);
                //$category_date = date("d.m.y H:i",$category_date);
                if(isset($result->fields['11']) and intval($result->fields['11']) > 0) {
                    $subcategory_id = intval($result->fields['11']);
                    $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                    $subcategories_all[$category_id][] = array(
                        "subcategory_id" => $subcategory_id,
                        "subcategory_name" => $subcategory_name
                    );
                } else {
                    $subcategories_all[$category_id] = "no";
                }
                $categories_all[$category_id] = array(
                    "category_id" => $category_id,
                    "category_img" => $category_img,
                    "category_name" => $category_name,
                    "subcategories_all" => $subcategories_all[$category_id]
                );
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
        $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') "."WHERE a.item_show = '1' and ((a.item_catalog_id is NOT NULL and (c.catalog_id is NULL or c.catalog_id = ".$catalog_id.")) or (a.item_category_id is NOT NULL and (d.cat_catalog_id is NULL or d.cat_catalog_id = ".$catalog_id.") and (f.catalog_id is NULL or f.catalog_id = ".$catalog_id.") and (a.item_display = '3' or a.item_display = '2')) or (a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id in (SELECT a1.subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." a1,".PREFIX."_categories_".$this->registry->sitelang." a2 WHERE a1.subcategory_cat_id=a2.category_id and a2.cat_catalog_id = ".$catalog_id.")) and (g.cat_catalog_id is NULL or g.cat_catalog_id = ".$catalog_id.") and (h.catalog_id is NULL or h.catalog_id = ".$catalog_id.") and (a.item_display = '3' or a.item_display = '2'))) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items ".$from;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_catitems_order;
        $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist == 0 and $num_page > 1) {
                $this->error = 'unknown_page';
                return;
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if(!isset($item_img) or $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = number_format($result->fields['3'],2,'.',' ');
                    $item_price_discounted = number_format($result->fields['4'],2,'.',' ');
                    $item_catalog_id = intval($result->fields['5']);
                    if($item_catalog_id > 0) {
                        $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                    } else {
                        $item_catalog_name = "no";
                    }
                    $item_category_id = intval($result->fields['6']);
                    if($item_category_id > 0) {
                        $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                        $item_categ_cat_id = intval($result->fields['20']);
                    } else {
                        $item_category_name = "no";
                        $item_categ_cat_id = 0;
                    }
                    $item_subcategory_id = intval($result->fields['7']);
                    if($item_subcategory_id > 0) {
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
                    $item_date = date("d.m.y H:i",$item_date);
                    $items_all[] = array(
                        "item_id" => $item_id,
                        "item_img" => $item_img,
                        "item_name" => $item_name,
                        "item_price" => $item_price,
                        "item_price_discounted" => $item_price_discounted,
                        "item_catalog_id" => $item_catalog_id,
                        "item_category_id" => $item_category_id,
                        "item_subcategory_id" => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity" => $item_quantity,
                        "item_quantity_unlim" => $item_quantity_unlim,
                        "item_quantity_param" => $item_quantity_param,
                        "item_display" => $item_display,
                        "item_date" => $item_date,
                        "item_catalog_name" => $item_catalog_name,
                        "item_category_name" => $item_category_name,
                        "item_subcategory_name" => $item_subcategory_name,
                        "item_categ_cat_id" => $item_categ_cat_id,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id" => $item_subcateg_cat_id
                    );
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if(isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if($num_pages > 1) {
                    if($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for($i = 1;$i < $num_pages + 1;$i++) {
                        if($i == $num_page) {
                            $html[] = array("number" => $i,"param1" => "1");
                        } else {
                            $pagelink = 5;
                            if(($i > $num_page) and ($i < $num_page + $pagelink) or ($i < $num_page) and ($i > $num_page - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "2");
                            }
                            if(($i == $num_pages) and ($num_page < $num_pages - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "3");
                            }
                            if(($i == 1) and ($num_page > $pagelink + 1)) {
                                $html[] = array("number" => $i,"param1" => "4");
                            }
                        }
                    }
                    if($num_page < $num_pages) {
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


    public function subcategs($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if(isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if(isset($array['num_page']) and intval($array['num_page']) !== 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if(!isset($array['catalog_id']) or !isset($array['category_id']) or (isset($array['catalog_id']) and intval($array['catalog_id']) == 0) or (isset($array['category_id']) and intval($array['category_id']) == 0)) {
            $this->error = 'not_all_data2';
            return;
        }
        $this->result['shop_catalog_id'] = $catalog_id;
        $this->result['shop_category_id'] = $category_id;
        $num_string_rows = $this->systemdk_shop_categitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!isset($this->systemdk_shop_subcategories_order)) {
            $this->systemdk_shop_subcategories_order = 'subcategory_position';
        }
        if(!isset($this->systemdk_shop_categitems_order)) {
            $this->systemdk_shop_categitems_order = 'item_position';
        }
        $sql = "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_date,b.category_id,b.category_img,b.category_name,b.category_description,b.category_meta_title,b.category_meta_keywords,b.category_meta_description,a.catalog_id,a.catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and b.category_id = ".$category_id." and b.category_show = '1' LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and (c.subcategory_id is NULL or c.subcategory_show = '1') WHERE a.catalog_id = ".$catalog_id." and a.catalog_show = '1' ORDER BY c.".$this->systemdk_shop_subcategories_order;
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'categories_error';
            return;
        }
        if(isset($result->fields['4']) and intval($result->fields['4']) > 0) {
            $row_exist = intval($result->fields['4']);
        } else {
            $row_exist = 0;
            $cache = "category";
        }
        if(isset($result->fields['11']) and intval($result->fields['11']) > 0) {
            $row_exist_b = intval($result->fields['11']);
        } else {
            $row_exist_b = 0;
            $cache = "catalog";
        }
        if($row_exist < 1 or $row_exist_b < 1) {
            $this->error = 'not_find_'.$cache;
            return;
        }
        $category_id = intval($result->fields['4']);
        $category_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        if(!isset($category_img) or $category_img == "") {
            $category_img = "no";
        }
        $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $category_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
        $category_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
        $category_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
        $category_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
        $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
        $this->result['shop_category_img'] = $category_img;
        $this->result['shop_category_name'] = $category_name;
        $this->result['shop_category_description'] = $category_description;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->registry->main_class->set_sitemeta($category_meta_title,$category_meta_description,$category_meta_keywords);
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $subcategory_id = intval($result->fields['0']);
                $subcategory_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                if(!isset($subcategory_img) or $subcategory_img == "") {
                    $subcategory_img = "no";
                }
                $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                //$subcategory_date = intval($result->fields['3']);
                //$subcategory_date = date("d.m.y H:i",$subcategory_date);
                $subcategories_all[] = array(
                    "subcategory_id" => $subcategory_id,
                    "subcategory_img" => $subcategory_img,
                    "subcategory_name" => $subcategory_name
                );
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
        $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') "."WHERE a.item_show = '1' and ((a.item_category_id is NOT NULL and (d.cat_catalog_id is NULL or d.cat_catalog_id = ".$catalog_id.")  and (d.category_id is NULL or d.category_id = ".$category_id.") and (f.catalog_id is NULL or f.catalog_id = ".$catalog_id.")) or (a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id in (SELECT a1.subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." a1,".PREFIX."_categories_".$this->registry->sitelang." a2 WHERE a1.subcategory_cat_id=a2.category_id and a2.cat_catalog_id = ".$catalog_id." and a2.category_id = ".$category_id.")) and (g.cat_catalog_id is NULL or g.cat_catalog_id = ".$catalog_id.") and (g.category_id is NULL or g.category_id = ".$category_id.") and (h.catalog_id is NULL or h.catalog_id = ".$catalog_id.") and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1'))) and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items ".$from;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_categitems_order;
        $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist == 0 and $num_page > 1) {
                $this->error = 'unknown_page';
                return;
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if(!isset($item_img) or $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = number_format($result->fields['3'],2,'.',' ');
                    $item_price_discounted = number_format($result->fields['4'],2,'.',' ');
                    $item_catalog_id = intval($result->fields['5']);
                    $item_category_id = intval($result->fields['6']);
                    if($item_category_id > 0) {
                        $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                        $item_categ_cat_id = intval($result->fields['18']);
                    } else {
                        $item_category_name = "no";
                        $item_categ_cat_id = 0;
                    }
                    $item_subcategory_id = intval($result->fields['7']);
                    if($item_subcategory_id > 0) {
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
                    $item_date = date("d.m.y H:i",$item_date);
                    $items_all[] = array(
                        "item_id" => $item_id,
                        "item_img" => $item_img,
                        "item_name" => $item_name,
                        "item_price" => $item_price,
                        "item_price_discounted" => $item_price_discounted,
                        "item_catalog_id" => $item_catalog_id,
                        "item_category_id" => $item_category_id,
                        "item_subcategory_id" => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity" => $item_quantity,
                        "item_quantity_unlim" => $item_quantity_unlim,
                        "item_quantity_param" => $item_quantity_param,
                        "item_display" => $item_display,
                        "item_date" => $item_date,
                        "item_category_name" => $item_category_name,
                        "item_subcategory_name" => $item_subcategory_name,
                        "item_categ_cat_id" => $item_categ_cat_id,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id" => $item_subcateg_cat_id
                    );
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if(isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if($num_pages > 1) {
                    if($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for($i = 1;$i < $num_pages + 1;$i++) {
                        if($i == $num_page) {
                            $html[] = array("number" => $i,"param1" => "1");
                        } else {
                            $pagelink = 5;
                            if(($i > $num_page) and ($i < $num_page + $pagelink) or ($i < $num_page) and ($i > $num_page - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "2");
                            }
                            if(($i == $num_pages) and ($num_page < $num_pages - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "3");
                            }
                            if(($i == 1) and ($num_page > $pagelink + 1)) {
                                $html[] = array("number" => $i,"param1" => "4");
                            }
                        }
                    }
                    if($num_page < $num_pages) {
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


    public function items($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if(isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if(isset($array['subcategory_id'])) {
            $subcategory_id = intval($array['subcategory_id']);
        }
        if(isset($array['num_page']) and intval($array['num_page']) !== 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if(!isset($array['catalog_id']) or !isset($array['category_id']) or !isset($array['subcategory_id']) or (isset($array['catalog_id']) and intval($array['catalog_id']) == 0) or (isset($array['category_id']) and intval($array['category_id']) == 0) or (isset($array['subcategory_id']) and intval($array['subcategory_id']) == 0)) {
            $this->error = 'not_all_data3';
            return;
        }
        $this->result['shop_catalog_id'] = $catalog_id;
        $this->result['shop_category_id'] = $category_id;
        $this->result['shop_subcategory_id'] = $subcategory_id;
        $num_string_rows = $this->systemdk_shop_subcategitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!isset($this->systemdk_shop_subcategitems_order)) {
            $this->systemdk_shop_subcategitems_order = 'item_position';
        }
        $sql = "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_description,c.subcategory_meta_title,c.subcategory_meta_keywords,c.subcategory_meta_description,b.category_id,b.category_name,a.catalog_id,a.catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and b.category_id = ".$category_id." and b.category_show = '1' LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and c.subcategory_id = ".$subcategory_id." and c.subcategory_show = '1' WHERE a.catalog_id = ".$catalog_id." and a.catalog_show = '1'";
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'categories_error';
            return;
        }
        if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
            $cache = "subcategory";
        }
        if(isset($result->fields['7']) and intval($result->fields['7']) > 0) {
            $row_exist_b = intval($result->fields['7']);
        } else {
            $row_exist_b = 0;
            $cache = "category";
        }
        if(isset($result->fields['9']) and intval($result->fields['9']) > 0) {
            $row_exist_c = intval($result->fields['9']);
        } else {
            $row_exist_c = 0;
            $cache = "catalog";
        }
        if($row_exist < 1 or $row_exist_b < 1 or $row_exist_c < 1) {
            $this->error = 'not_find_'.$cache;
            return;
        }
        $subcategory_id = intval($result->fields['0']);
        $subcategory_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        if(!isset($subcategory_img) or $subcategory_img == "") {
            $subcategory_img = "no";
        }
        $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $subcategory_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        $subcategory_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
        $subcategory_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $subcategory_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
        $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
        $this->result['shop_subcategory_img'] = $subcategory_img;
        $this->result['shop_subcategory_name'] = $subcategory_name;
        $this->result['shop_subcategory_description'] = $subcategory_description;
        $this->result['shop_category_name'] = $category_name;
        $this->result['shop_catalog_name'] = $catalog_name;
        $this->registry->main_class->set_sitemeta($subcategory_meta_title,$subcategory_meta_description,$subcategory_meta_keywords);
        $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL "."WHERE a.item_show = '1' and ((a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id = ".$subcategory_id.") and (g.cat_catalog_id is NULL or g.cat_catalog_id = ".$catalog_id.") and (g.category_id is NULL or g.category_id = ".$category_id.") and (h.catalog_id is NULL or h.catalog_id = ".$catalog_id."))) and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT count(*) as count_items ".$from;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $num_rows = intval($result->fields['0']);
            }
        }
        $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,e.subcategory_id,e.subcategory_name,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_subcategitems_order;
        $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist == 0 and $num_page > 1) {
                $this->error = 'unknown_page';
                return;
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $item_id = intval($result->fields['0']);
                    $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    if(!isset($item_img) or $item_img == "") {
                        $item_img = "no";
                    }
                    $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $item_price = number_format($result->fields['3'],2,'.',' ');
                    $item_price_discounted = number_format($result->fields['4'],2,'.',' ');
                    $item_catalog_id = intval($result->fields['5']);
                    $item_category_id = intval($result->fields['6']);
                    $item_subcategory_id = intval($result->fields['7']);
                    if($item_subcategory_id > 0) {
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
                    $item_date = date("d.m.y H:i",$item_date);
                    $items_all[] = array(
                        "item_id" => $item_id,
                        "item_img" => $item_img,
                        "item_name" => $item_name,
                        "item_price" => $item_price,
                        "item_price_discounted" => $item_price_discounted,
                        "item_catalog_id" => $item_catalog_id,
                        "item_category_id" => $item_category_id,
                        "item_subcategory_id" => $item_subcategory_id,
                        "item_short_description" => $item_short_description,
                        "item_quantity" => $item_quantity,
                        "item_quantity_unlim" => $item_quantity_unlim,
                        "item_quantity_param" => $item_quantity_param,
                        "item_display" => $item_display,
                        "item_date" => $item_date,
                        "item_subcategory_name" => $item_subcategory_name,
                        "item_subcateg_categ_id" => $item_subcateg_categ_id,
                        "item_subcateg_cat_id" => $item_subcateg_cat_id
                    );
                    $result->MoveNext();
                }
                $this->result['shop_items_all'] = $items_all;
                $this->result['shop_total_items'] = $num_rows;
                $this->result['systemdk_shop_itemimage_position'] = $this->systemdk_shop_itemimage_position;
                $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
                $this->result['systemdk_shop_itemimage_width'] = $this->systemdk_shop_itemimage_width;
                if(isset($num_rows)) {
                    $num_pages = @ceil($num_rows / $num_string_rows);
                } else {
                    $num_pages = 0;
                }
                if($num_pages > 1) {
                    if($num_page > 1) {
                        $prevpage = $num_page - 1;
                        $this->result['prevpage'] = $prevpage;
                    } else {
                        $this->result['prevpage'] = "no";
                    }
                    for($i = 1;$i < $num_pages + 1;$i++) {
                        if($i == $num_page) {
                            $html[] = array("number" => $i,"param1" => "1");
                        } else {
                            $pagelink = 5;
                            if(($i > $num_page) and ($i < $num_page + $pagelink) or ($i < $num_page) and ($i > $num_page - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "2");
                            }
                            if(($i == $num_pages) and ($num_page < $num_pages - $pagelink)) {
                                $html[] = array("number" => $i,"param1" => "3");
                            }
                            if(($i == 1) and ($num_page > $pagelink + 1)) {
                                $html[] = array("number" => $i,"param1" => "4");
                            }
                        }
                    }
                    if($num_page < $num_pages) {
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


    public function item($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $item_id = 0;
        if(isset($array['item_id'])) {
            $item_id = intval($array['item_id']);
        }
        if(isset($array['catalog_id'])) {
            $catalog_id = intval($array['catalog_id']);
        }
        if(isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if(isset($array['subcategory_id'])) {
            $subcategory_id = intval($array['subcategory_id']);
        }
        if(isset($array['num_page']) and intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        if(isset($catalog_id) and $catalog_id != 0) {
            if(isset($subcategory_id) and isset($category_id) and $category_id != 0) {
                $cache = "subcateg";
                if($subcategory_id != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif(isset($category_id)) {
                $cache = "categ";
                if($category_id != 0) {
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
        } elseif(isset($catalog_id) and $catalog_id == 0) {
            $cache = "cat";
            $check = 0;
            $category_id = 0;
            $subcategory_id = 0;
        } else {
            $cache = "home";
            if(!isset($item_id)) {
                $item_id = 0;
            }
            $check = 1;
            $catalog_id = 0;
            $category_id = 0;
            $subcategory_id = 0;
        }
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if($item_id == 0 or $check == 0) {
            if($item_id == 0) {
                $cache_err = "not_all_data4";
            } elseif($cache == "cat") {
                $cache_err = "not_all_data";
            } elseif($cache == "categ") {
                $cache_err = "not_all_data2";
            } elseif($cache == "subcateg") {
                $cache_err = "not_all_data3";
            }
            $this->error = $cache_err;
            return;
        }
        if($cache === 'subcateg') {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,e.catalog_id,e.catalog_name,d.category_id,d.category_name,c.subcategory_id,c.subcategory_name FROM ".PREFIX."_items_".$this->registry->sitelang." a INNER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON c.subcategory_id=a.item_subcategory_id and c.subcategory_id = ".$subcategory_id." INNER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON d.category_id=c.subcategory_cat_id and d.category_id = ".$category_id." INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." e ON e.catalog_id=d.cat_catalog_id and e.catalog_id = ".$catalog_id." WHERE a.item_id = ".$item_id." and c.subcategory_show='1' and d.category_show = '1' and e.catalog_show='1' and a.item_show = '1'";
        } elseif($cache === 'categ') {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,e.catalog_id,e.catalog_name,d.category_id,d.category_name FROM ".PREFIX."_items_".$this->registry->sitelang." a INNER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON d.category_id=a.item_category_id and d.category_id = ".$category_id." INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." e ON e.catalog_id=d.cat_catalog_id and e.catalog_id = ".$catalog_id." WHERE a.item_id = ".$item_id." and d.category_show = '1' and e.catalog_show='1' and a.item_show = '1'";
        } elseif($cache === 'cat') {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,e.catalog_id,e.catalog_name FROM ".PREFIX."_items_".$this->registry->sitelang." a INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." e ON e.catalog_id=a.item_catalog_id and e.catalog_id = ".$catalog_id." WHERE a.item_id = ".$item_id." and e.catalog_show='1' and a.item_show = '1'";
        } else {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_short_description,a.item_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description FROM ".PREFIX."_items_".$this->registry->sitelang." a WHERE a.item_id = ".$item_id." and a.item_show = '1'";
        }
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'categories_error';
            return;
        }
        $this->result['shop_item_id'] = $item_id;
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->result['shop_item_all'] = "no";
            return;
        }
        $item_id = intval($result->fields['0']);
        $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        if(!isset($item_img) or $item_img == "") {
            $item_img = "no";
        }
        $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        $item_price = number_format($result->fields['3'],2,'.',' ');
        $item_price_discounted = number_format($result->fields['4'],2,'.',' ');
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
        if($cache === 'home') {
            $catalog_id = "no";
            $catalog_name = "no";
            $category_id = "no";
            $category_name = "no";
            $subcategory_id = "no";
            $subcategory_name = "no";
        } elseif($cache === 'cat') {
            $catalog_id = intval($result->fields['14']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
            $category_id = "no";
            $category_name = "no";
            $subcategory_id = "no";
            $subcategory_name = "no";
        } elseif($cache === 'categ') {
            $catalog_id = intval($result->fields['14']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
            $category_id = intval($result->fields['16']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
            $subcategory_id = "no";
            $subcategory_name = "no";
        } elseif($cache === 'subcateg') {
            $catalog_id = intval($result->fields['14']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
            $category_id = intval($result->fields['16']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
            $subcategory_id = intval($result->fields['18']);
            $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['19']));
        }
        $this->registry->main_class->set_sitemeta($item_meta_title,$item_meta_description,$item_meta_keywords);
        $item_description = str_replace("<span style=\"display: none;\">&nbsp;</span></div>","",$item_description);
        $item_description = explode("<div style=\"page-break-after: always;\">",$item_description);
        $count_num_pages = count($item_description);
        if($num_page > $count_num_pages) {
            $num_page = 1;
        }
        $item_description = $item_description[$num_page - 1];
        $item_description = $this->registry->main_class->search_player_entry($item_description);
        if($count_num_pages > 1) {
            if($num_page < $count_num_pages) {
                $next_page = $num_page + 1;
            } else {
                $next_page = "no";
            }
            if($num_page > 1) {
                $prev_page = $num_page - 1;
            } else {
                $prev_page = "no";
            }
            $pages_menu[] = array(
                "current_page" => $num_page,
                "next_page" => $next_page,
                "prev_page" => $prev_page,
                "count_num_pages" => $count_num_pages
            );
            $this->result['pages_menu'] = $pages_menu;
        } else {
            $this->result['pages_menu'] = "no";
        }
        $item_all[] = array(
            "item_id" => $item_id,
            "item_img" => $item_img,
            "item_name" => $item_name,
            "item_price" => $item_price,
            "item_price_discounted" => $item_price_discounted,
            "item_short_description" => $item_short_description,
            "item_description" => $item_description,
            "item_quantity" => $item_quantity,
            "item_quantity_unlim" => $item_quantity_unlim,
            "item_quantity_param" => $item_quantity_param
        );
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
        $this->result['systemdk_shop_image_width'] = $this->systemdk_shop_itemimage_width;
    }


    public function cart() {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart]);
        } else {
            $this->result['shop_cart_display_items'] = "no";
        }
    }


    private function shop_display_cart_items($cart,$shop_cart_allowchange = 1,$shop_cart_allowimages = 1) {
        $this->result['shop_cart_allowimages'] = intval($shop_cart_allowimages);
        $this->result['shop_cart_allowchange'] = intval($shop_cart_allowchange);
        $this->result['systemdk_shop_itemimage_path'] = $this->systemdk_shop_itemimage_path;
        $this->result['systemdk_shop_itemimage_width3'] = $this->systemdk_shop_itemimage_width3;
        $this->result['systemdk_shop_itemimage_height3'] = $this->systemdk_shop_itemimage_height3;
        $this->shop_cart_items_all = $this->shop_get_items_details($cart);
        if(!$this->shop_cart_items_all) {
            $this->shop_cart_items_all = "no";
        }
        /*foreach ($cart as $cart_item_id => $qty)
         {
           $shop_cart_item = $this->shop_get_items_details($cart_item_id);
           $item_id = $shop_cart_item['0'];
           $item_img = $shop_cart_item['1'];
           $item_name = $shop_cart_item['2'];
           $item_price = $shop_cart_item['3'];
           $shop_cart_item_all[] = array("item_id"=>$item_id, "item_img"=>$item_img, "item_name"=>$item_name, "item_price"=>$item_price, "item_qty"=>$qty, "item_total_price"=>number_format($item_price*$qty,2));
         }*/
        $this->result['shop_cart_display_items'] = $this->shop_cart_items_all;
    }


    private function shop_get_items_details($cart = 0) {
        if(!isset($cart) or $cart == 0 or !is_array($cart) or count($cart) == 0) {
            return false;
        }
        $i = 0;
        foreach($cart as $cart_item_id => $qty) {
            if(isset($cart_item_id) and intval($cart_item_id) != 0) {
                if($i == 0) {
                    $item_id = "'".intval($cart_item_id)."'";
                } else {
                    if(!isset($item_id)) {
                        $item_id = false;
                    }
                    $item_id .= ",'".intval($cart_item_id)."'";
                }
                $i++;
            }
        }
        if(!isset($item_id)) {
            return false;
        }
        $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL WHERE a.item_id IN (".$item_id.") and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL) or (a.item_category_id is NOT NULL) or (a.item_subcategory_id is NOT NULL)) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
        $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id,a.item_display ".$from;
        $result = $this->db->Execute($sql);
        if(!$result) {
            return false;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            return false;
        }
        while(!$result->EOF) {
            $item_id = intval($result->fields['0']);
            $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            if(!isset($item_img) or $item_img == "") {
                $item_img = "no";
            }
            $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
            $item_price = floatval($result->fields['3']);
            $item_price_discounted = floatval($result->fields['4']);
            if($item_price_discounted > 0) {
                $item_price = $item_price_discounted;
            }
            $item_show = intval($result->fields['5']);
            $item_quantity = intval($result->fields['6']);
            $item_quantity_unlim = intval($result->fields['7']);
            $item_catalog_id = intval($result->fields['8']);
            $item_category_id = intval($result->fields['9']);
            if($item_category_id > 0) {
                $item_categ_cat_id = intval($result->fields['11']);
            } else {
                $item_categ_cat_id = "no";
            }
            $item_subcategory_id = intval($result->fields['10']);
            if($item_subcategory_id > 0) {
                $item_subcateg_categ_id = intval($result->fields['12']);
                $item_subcateg_cat_id = intval($result->fields['13']);
            } else {
                $item_subcateg_categ_id = "no";
                $item_subcateg_cat_id = "no";
            }
            $item_display = intval($result->fields['14']);
            if(isset($cart[$item_id])) {
                $item_qty = $cart[$item_id];
            } else {
                $item_qty = 0;
            }
            $items_all[] = array(
                "item_id" => $item_id,
                "item_img" => $item_img,
                "item_name" => $item_name,
                "item_price" => number_format($item_price,2,'.',' '),
                "item_qty" => $item_qty,
                "item_total_price" => number_format(floatval(($item_price * $item_qty)),2,'.',' '),
                "item_show" => $item_show,
                "item_quantity" => $item_quantity,
                "item_quantity_unlim" => $item_quantity_unlim,
                "item_display" => $item_display,
                "item_catalog_id" => $item_catalog_id,
                "item_category_id" => $item_category_id,
                "item_categ_cat_id" => $item_categ_cat_id,
                "item_subcategory_id" => $item_subcategory_id,
                "item_subcateg_categ_id" => $item_subcateg_categ_id,
                "item_subcateg_cat_id" => $item_subcateg_cat_id
            );
            $result->MoveNext();
        }
        return $items_all;
    }


    public function checkout() {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart],0,0);
            $this->shop_user_deliv_pay(1);
        } else {
            $this->result['shop_cart_display_items'] = "no";
        }
    }


    private function shop_user_deliv_pay($check_user = 0) {
        if($check_user == 1) {
            if($this->registry->main_class->is_user()) {
                $row = $this->registry->main_class->get_user_info();
                if(!empty($row) and intval($row['0']) > 0) {
                    $shop_user_id = intval($row['0']);
                    $shop_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                    $shop_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                    $shop_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['10']));
                    $shop_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['7']));
                    $shop_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['8']));
                    $shop_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                    if(empty($shop_user_name) and !empty($shop_user_surname)) {
                        $shop_user_name = $shop_user_surname;
                    } elseif(empty($shop_user_name) and empty($shop_user_surname)) {
                        $shop_user_name = $this->registry->main_class->extracting_data($row['5']);
                    } elseif(!empty($shop_user_name) and !empty($shop_user_surname)) {
                        $shop_user_name = $shop_user_name." ".$shop_user_surname;
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
        $sql = "SELECT delivery_id,delivery_name,delivery_price,null as pay_id,null as pay_name,null as pay_price FROM ".PREFIX."_delivery_".$this->registry->sitelang." WHERE delivery_status = '1' UNION ALL SELECT null as delivery_id,null as delivery_name,null as delivery_price,pay_id,pay_name,pay_price FROM ".PREFIX."_pay_".$this->registry->sitelang." WHERE pay_status = '1' ORDER by delivery_id,pay_id ASC";
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'categories_error';
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if(isset($result->fields['3'])) {
            $row_exist2 = intval($result->fields['3']);
        } else {
            $row_exist2 = 0;
        }
        if($row_exist > 0 or $row_exist2 > 0) {
            while(!$result->EOF) {
                if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
                    $delivery_id = intval($result->fields['0']);
                    $delivery_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $delivery_price = number_format($result->fields['2'],2,'.',' ');
                    if(isset($this->shop_order_delivery_id) and $this->shop_order_delivery_id > 0 and $this->shop_order_delivery_id == $delivery_id) {
                        $this->result['shop_final_delivery_name'] = $delivery_name;
                        $this->result['shop_final_delivery_price'] = $delivery_price;
                    }
                    $delivery_all[] = array(
                        "delivery_id" => $delivery_id,
                        "delivery_name" => $delivery_name,
                        "delivery_price" => $delivery_price
                    );
                }
                if(isset($result->fields['3']) and intval($result->fields['3']) > 0) {
                    $pay_id = intval($result->fields['3']);
                    $pay_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $pay_price = floatval($result->fields['5']);
                    if(isset($this->shop_order_pay_id) and $this->shop_order_pay_id > 0 and $this->shop_order_pay_id == $pay_id) {
                        $this->result['shop_final_pay_name'] = $pay_name;
                        $this->result['shop_final_pay_price'] = $pay_price;
                    }
                    $pay_all[] = array("pay_id" => $pay_id,"pay_name" => $pay_name,"pay_price" => $pay_price);
                }
                $result->MoveNext();
            }
            $this->result['shop_delivery_all'] = $delivery_all;
            $this->result['shop_pay_all'] = $pay_all;
        } else {
            $this->result['shop_delivery_all'] = "no";
            $this->result['shop_pay_all'] = "no";
        }
    }


    public function checkout2($post_array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($post_array)) {
            foreach($post_array as $key => $value) {
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
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if((!isset($post_array['shop_ship_name']) or !isset($post_array['shop_ship_telephone']) or !isset($post_array['shop_ship_email']) or !isset($post_array['shop_ship_country']) or !isset($post_array['shop_ship_state']) or !isset($post_array['shop_ship_city']) or !isset($post_array['shop_ship_street']) or !isset($post_array['shop_ship_homenumber']) or !isset($post_array['shop_ship_flatnumber']) or !isset($post_array['shop_ship_postalcode']) or !isset($post_array['shop_ship_addaddrnotes']) or !isset($post_array['shop_order_delivery']) or !isset($post_array['shop_order_pay']) or !isset($post_array['shop_order_comments'])) or ($data_array['shop_ship_name'] == "" or $data_array['shop_ship_telephone'] == "" or $data_array['shop_ship_email'] == "" or $data_array['shop_order_delivery'] == 0 or $data_array['shop_order_pay'] == 0)) {
            $this->error = 'shop_not_all_data';
            return;
        }
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart],0,0);
            $this->shop_order_delivery_id = $data_array['shop_order_delivery'];
            $this->shop_order_pay_id = $data_array['shop_order_pay'];
            $this->shop_user_deliv_pay(1);
            if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
                $this->result['enter_check'] = "yes";
            } else {
                $this->result['enter_check'] = "no";
            }
            $data_array['shop_ship_name'] = $this->registry->main_class->format_striptags($data_array['shop_ship_name']);
            $data_array['shop_ship_telephone'] = $this->registry->main_class->format_striptags($data_array['shop_ship_telephone']);
            $data_array['shop_ship_email'] = $this->registry->main_class->format_striptags($data_array['shop_ship_email']);
            if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['shop_ship_email']))) {
                $this->error = 'user_email';
                return;
            }
            $data_array['shop_ship_country'] = $this->registry->main_class->format_striptags($data_array['shop_ship_country']);
            $data_array['shop_ship_state'] = $this->registry->main_class->format_striptags($data_array['shop_ship_state']);
            $data_array['shop_ship_city'] = $this->registry->main_class->format_striptags($data_array['shop_ship_city']);
            $data_array['shop_ship_street'] = $this->registry->main_class->format_striptags($data_array['shop_ship_street']);
            $data_array['shop_ship_homenumber'] = $this->registry->main_class->format_striptags($data_array['shop_ship_homenumber']);
            $data_array['shop_ship_flatnumber'] = $this->registry->main_class->format_striptags($data_array['shop_ship_flatnumber']);
            $data_array['shop_ship_postalcode'] = $this->registry->main_class->format_striptags($data_array['shop_ship_postalcode']);
            $data_array['shop_ship_addaddrnotes'] = $this->registry->main_class->format_striptags($data_array['shop_ship_addaddrnotes']);
            $data_array['shop_order_comments'] = $this->registry->main_class->format_striptags($data_array['shop_order_comments']);
            $this->result['shop_cart_checkout_confirm'] = 1;
            $this->result['modules_shop_username'] = $data_array['shop_ship_name'];
            $this->result['modules_shop_usertelephone'] = $data_array['shop_ship_telephone'];
            $this->result['modules_shop_useremail'] = $data_array['shop_ship_email'];
            $this->result['modules_shop_usercountry'] = $data_array['shop_ship_country'];
            $this->result['modules_shop_userstate'] = $data_array['shop_ship_state'];
            $this->result['modules_shop_usercity'] = $data_array['shop_ship_city'];
            $this->result['modules_shop_userstreet'] = $data_array['shop_ship_street'];
            $this->result['modules_shop_userhomenumber'] = $data_array['shop_ship_homenumber'];
            $this->result['modules_shop_userflatnumber'] = $data_array['shop_ship_flatnumber'];
            $this->result['modules_shop_userpostalcode'] = $data_array['shop_ship_postalcode'];
            $this->result['modules_shop_useraddaddrnotes'] = $data_array['shop_ship_addaddrnotes'];
            $this->result['modules_shop_userdelivery'] = $data_array['shop_order_delivery'];
            $this->result['modules_shop_userpay'] = $data_array['shop_order_pay'];
            $this->result['modules_shop_usercomments'] = $data_array['shop_order_comments'];
        } else {
            $this->result['shop_cart_display_items'] = "no";
        }
    }


    public function insert($post_array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($post_array)) {
            foreach($post_array as $key => $value) {
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
        $this->result['systemdk_shop_valuta'] = $this->systemdk_shop_valuta;
        if((!isset($post_array['shop_ship_name']) or !isset($post_array['shop_ship_telephone']) or !isset($post_array['shop_ship_email']) or !isset($post_array['shop_ship_country']) or !isset($post_array['shop_ship_state']) or !isset($post_array['shop_ship_city']) or !isset($post_array['shop_ship_street']) or !isset($post_array['shop_ship_homenumber']) or !isset($post_array['shop_ship_flatnumber']) or !isset($post_array['shop_ship_postalcode']) or !isset($post_array['shop_ship_addaddrnotes']) or !isset($post_array['shop_order_delivery']) or !isset($post_array['shop_order_pay']) or !isset($post_array['shop_order_comments'])) or ($data_array['shop_ship_name'] == "" or $data_array['shop_ship_telephone'] == "" or $data_array['shop_ship_email'] == "" or $data_array['shop_order_delivery'] == 0 or $data_array['shop_order_pay'] == 0)) {
            $this->error = 'shop_not_all_data2';
            return;
        }
        if(ENTER_CHECK == "yes") {
            if(extension_loaded("gd") and ((!isset($post_array["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags(trim($post_array["captcha"]))) or (!isset($_SESSION["captcha"])))) {
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                $this->error = 'check_code';
                return;
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        if(isset($this->systemdk_shop_antispam_ipaddr) and intval($this->systemdk_shop_antispam_ipaddr) == 1) {
            $post_date_check = $this->registry->main_class->get_time() - 86400;
            $sql = "SELECT count(*) FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_ip = '".$this->registry->ip_addr."' and order_date > ".$post_date_check;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $num_rows = intval($result->fields['0']);
                } else {
                    $num_rows = 0;
                }
                if($num_rows >= intval($this->systemdk_shop_antispam_num) and intval($this->systemdk_shop_antispam_num) > 0) {
                    $this->error = 'post_limit';
                    return;
                }
            }
        }
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            if($this->registry->main_class->is_user()) {
                $row = $this->registry->main_class->get_user_info();
                if(!empty($row) and intval($row['0']) > 0) {
                    $shop_user_id = "'".intval($row['0'])."'";
                } else {
                    $shop_user_id = 'NULL';
                }
            } else {
                $shop_user_id = 'NULL';
            }
            $order_amount = $this->registry->main_class->float_to_db($_SESSION[$this->systemdk_shop_total_price]);
            $order_date = $this->registry->main_class->get_time();
            $order_status = 1;
            $data_array['shop_ship_name'] = $this->registry->main_class->format_striptags($data_array['shop_ship_name']);
            $data_array['shop_ship_name'] = $this->registry->main_class->processing_data($data_array['shop_ship_name']);
            $data_array['shop_ship_telephone'] = $this->registry->main_class->format_striptags($data_array['shop_ship_telephone']);
            $data_array['shop_ship_telephone'] = $this->registry->main_class->processing_data($data_array['shop_ship_telephone']);
            $data_array['shop_ship_email'] = $this->registry->main_class->format_striptags($data_array['shop_ship_email']);
            if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['shop_ship_email']))) {
                $this->error = 'user_email2';
                return;
            }
            $data_array['shop_ship_email'] = $this->registry->main_class->processing_data($data_array['shop_ship_email']);
            $shop_ship_addaddrnotes_length = strlen($data_array['shop_ship_addaddrnotes']);
            $shop_order_comments_length = strlen($data_array['shop_order_comments']);
            foreach($data_array as $key => $value) {
                $keys = array(
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
                if(in_array($key,$keys)) {
                    $value = $this->registry->main_class->format_striptags($value);
                    if($value != "") {
                        $data_array[$key] = $this->registry->main_class->processing_data($value);
                    } else {
                        $data_array[$key] = 'NULL';
                    }
                }
            }
            if($shop_ship_addaddrnotes_length > 255 or $shop_order_comments_length > 255) {
                $this->error = 'post_length';
                return;
            }
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart],0,0);
            if($this->shop_cart_items_all == "no") {
                $this->result['shop_cart_display_items'] = 'no';
                return;
            }
            $this->shop_order_delivery_id = $data_array['shop_order_delivery'];
            $this->shop_order_pay_id = $data_array['shop_order_pay'];
            $this->shop_user_deliv_pay(0);
            $order_ip = $this->registry->main_class->processing_data($this->registry->main_class->format_striptags($this->registry->ip_addr));
            $this->db->StartTrans();
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_orders_id_'.$this->registry->sitelang,'order_id');
            $order_id = $sequence_array['sequence_value'];
            $sql = "INSERT INTO ".PREFIX."_orders_".$this->registry->sitelang."(".$sequence_array['field_name_string']."order_customer_id,order_amount,order_date,ship_date,order_status,ship_name,ship_street,ship_house,ship_flat,ship_city,ship_state,ship_postal_code,ship_country,ship_telephone,ship_email,ship_add_info,order_delivery,order_pay,order_comments,order_ip) VALUES (".$sequence_array['sequence_value_string']."".$shop_user_id.",'".$order_amount."','".$order_date."',NULL,'".$order_status."',".$data_array['shop_ship_name'].",".$data_array['shop_ship_street'].",".$data_array['shop_ship_homenumber'].",".$data_array['shop_ship_flatnumber'].",".$data_array['shop_ship_city'].",".$data_array['shop_ship_state'].",".$data_array['shop_ship_postalcode'].",".$data_array['shop_ship_country'].",".$data_array['shop_ship_telephone'].",".$data_array['shop_ship_email'].",".$data_array['shop_ship_addaddrnotes'].",'".$data_array['shop_order_delivery']."','".$data_array['shop_order_pay']."',".$data_array['shop_order_comments'].",".$order_ip.")";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            } else {
                if(empty($order_id)) {
                    $order_id = $this->registry->main_class->db_get_last_insert_id();
                }
            }
            $items_info = $this->shop_cart_items_all;
            for($i = 0,$size = count($items_info);$i < $size;++$i) //foreach($_SESSION[$this->systemdk_shop_cart] as $item_id => $qty)
            {
                $item_id = intval($items_info[$i]['item_id']);
                $order_item_name = $this->registry->main_class->processing_data($items_info[$i]['item_name']);
                $order_item_price = $this->registry->main_class->float_to_db($items_info[$i]['item_price']);
                $order_item_quantity = intval($items_info[$i]['item_qty']);
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_order_items_id_'.$this->registry->sitelang,'order_item_id');
                $sql = "INSERT INTO ".PREFIX."_order_items_".$this->registry->sitelang." (".$sequence_array['field_name_string']."order_id,item_id,order_item_name,order_item_price,order_item_quantity) VALUES (".$sequence_array['sequence_value_string']."'".$order_id."','".$item_id."',".$order_item_name.",'".$order_item_price."','".$order_item_quantity."')";
                $insert_result2 = $this->db->Execute($sql);
                if($insert_result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                    $insert_result = false;
                }
                $item_quantity = intval($items_info[$i]['item_quantity']);
                $item_quantity_unlim = intval($items_info[$i]['item_quantity_unlim']);
                if($item_quantity_unlim == 0) {
                    $item_quantity = intval($item_quantity - $order_item_quantity);
                    if($item_quantity < 0) {
                        $item_quantity = 0;
                    }
                    $sql = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_quantity = '".$item_quantity."' WHERE item_id = ".$item_id;
                    $insert_result3 = $this->db->Execute($sql);
                    if($insert_result3 === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $insert_result = false;
                    } else {
                        $item_catalog_id = intval($items_info[$i]['item_catalog_id']);
                        $item_category_id = intval($items_info[$i]['item_category_id']);
                        $item_subcategory_id = intval($items_info[$i]['item_subcategory_id']);
                        if($item_catalog_id > 0) {
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$item_catalog_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$item_catalog_id);
                            if(intval($items_info[$i]['item_display']) == 3) {
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                            }
                        } elseif($item_category_id > 0) {
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_categ_cat_id'])."|categ".$item_category_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_categ_cat_id'])."|categ".$item_category_id);
                            if(intval($items_info[$i]['item_display']) == 3) {
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_categ_cat_id']));
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_categ_cat_id']));
                            } elseif(intval($items_info[$i]['item_display']) == 2) {
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_categ_cat_id']));
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_categ_cat_id']));
                            }
                        } elseif($item_subcategory_id > 0) {
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_subcateg_cat_id'])."|categ".intval($items_info[$i]['item_subcateg_categ_id'])."|subcateg".$item_subcategory_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_subcateg_cat_id'])."|categ".intval($items_info[$i]['item_subcateg_categ_id'])."|subcateg".$item_subcategory_id);
                            if(intval($items_info[$i]['item_display']) == 3) {
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_subcateg_cat_id']));
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_subcateg_cat_id']));
                            } elseif(intval($items_info[$i]['item_display']) == 2) {
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_subcateg_cat_id']));
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_subcateg_cat_id']));
                            } elseif(intval($items_info[$i]['item_display']) == 1) {
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".intval($items_info[$i]['item_subcateg_cat_id'])."|categ".intval($items_info[$i]['item_subcateg_categ_id']));
                                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".intval($items_info[$i]['item_subcateg_cat_id'])."|categ".intval($items_info[$i]['item_subcateg_categ_id']));
                            }
                        } elseif($item_catalog_id == 0 and $item_category_id == 0 and $item_subcategory_id == 0) {
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                        }
                    }
                }
            }
            $this->db->CompleteTrans();
            if($insert_result === false) {
                $this->error = 'not_insert';
                $this->error_array = $error;
                return;
            }
            unset($_SESSION[$this->systemdk_shop_total_price]);
            unset($_SESSION[$this->systemdk_shop_items]);
            unset($_SESSION[$this->systemdk_shop_cart]);
            $this->result['systemdk_shop_order_id'] = $order_id;
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|0");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|1");
            $this->result['shop_message'] = "add_ok";
        } else {
            $this->result['shop_cart_display_items'] = 'no';
        }
    }
}
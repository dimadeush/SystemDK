<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class shop extends model_base {


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


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_shop_homeitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(!isset($this->systemdk_shop_catalogs_order)) {
            $this->systemdk_shop_catalogs_order = 'catalog_position';
        }
        if(!isset($this->systemdk_shop_categories_order)) {
            $this->systemdk_shop_categories_order = 'category_position';
        }
        if(!isset($this->systemdk_shop_homeitems_order)) {
            $this->systemdk_shop_homeitems_order = 'item_position';
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|home|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
            $sql = "SELECT a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_date,b.category_id,b.category_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and (b.category_id is NULL or b.category_show = '1') WHERE a.catalog_show = '1' ORDER BY a.".$this->systemdk_shop_catalogs_order.",b.".$this->systemdk_shop_categories_order;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $catalog_id = intval($result->fields['0']);
                        $catalog_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        if(!isset($catalog_img) or $catalog_img == "") {
                            $catalog_img = "no";
                        }
                        $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        $catalog_date = intval($result->fields['3']);
                        $catalog_date = date("d.m.y H:i",$catalog_date);
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
                    $this->registry->main_class->assign("shop_catalogs_all",$catalogs_all);
                    $this->registry->main_class->assign("shop_total_catalogs",count($catalogs_all));
                    $this->registry->main_class->assign("systemdk_shop_image_position",$this->systemdk_shop_image_position);
                    $this->registry->main_class->assign("systemdk_shop_image_path",$this->systemdk_shop_image_path);
                    $this->registry->main_class->assign("systemdk_shop_image_width",$this->systemdk_shop_image_width);
                    $this->registry->main_class->assign("systemdk_shop_items_columns",$this->systemdk_shop_items_columns);
                    $this->registry->main_class->assign("systemdk_shop_items_underparent",$this->systemdk_shop_items_underparent);
                    $this->registry->main_class->assign("systemdk_shop_items_columnsperc",intval(100 / $this->systemdk_shop_items_columns));
                } else {
                    $this->registry->main_class->assign("shop_catalogs_all","no");
                    $this->registry->main_class->assign("shop_total_catalogs","0");
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                    $this->registry->main_class->assign("shop_message","categorieserror");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language);
                exit();
            }
            $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and a.item_display = '3' WHERE a.item_show = '1' and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL and a.item_display = '3') or (a.item_category_id is NOT NULL and a.item_display = '3') or (a.item_subcategory_id is NOT NULL and a.item_display = '3')) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
            $sql = "SELECT count(*) as count_items ".$from;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows2 = intval($result->fields['0']);
                }
            }
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_homeitems_order;
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/");
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
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
                    $this->registry->main_class->assign("shop_items_all",$items_all);
                    $this->registry->main_class->assign("shop_total_items",$numrows2);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_position",$this->systemdk_shop_itemimage_position);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_path",$this->systemdk_shop_itemimage_path);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_width",$this->systemdk_shop_itemimage_width);
                    if(isset($numrows2)) {
                        $num_pages = @ceil($numrows2 / $num_string_rows);
                    } else {
                        $num_pages = 0;
                    }
                    if($num_pages > 1) {
                        if($num_page > 1) {
                            $prevpage = $num_page - 1;
                            $this->registry->main_class->assign("prevpage",$prevpage);
                        } else {
                            $this->registry->main_class->assign("prevpage","no");
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
                            $this->registry->main_class->assign("nextpage",$nextpage);
                        } else {
                            $this->registry->main_class->assign("nextpage","no");
                        }
                        $this->registry->main_class->assign("html",$html);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("shop_items_all","no");
                    $this->registry->main_class->assign("shop_total_items","0");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->assign("shop_items_all","no");
                $this->registry->main_class->assign("shop_total_items","0");
                $this->registry->main_class->assign("html","no");
            }
            $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'),$this->registry->main_class->getConfigVars('_SHOP_MODULE_METADESCRIPTION'),$this->registry->main_class->getConfigVars('_SHOP_MODULE_METAKEYWORDS'));
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_catalogs.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|home|".$num_page."|".$this->registry->language);
    }


    public function categs() {
        if(isset($_GET['cat_id'])) {
            $catalog_id = intval($_GET['cat_id']);
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if(!isset($_GET['cat_id']) or (isset($_GET['cat_id']) and intval($_GET['cat_id']) == 0)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                $this->registry->main_class->assign("shop_message","notalldata");
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|notalldata|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->assign("shop_catalog_id",$catalog_id);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_shop_catitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(!isset($this->systemdk_shop_categories_order)) {
            $this->systemdk_shop_categories_order = 'category_position';
        }
        if(!isset($this->systemdk_shop_subcategories_order)) {
            $this->systemdk_shop_subcategories_order = 'subcategory_position';
        }
        if(!isset($this->systemdk_shop_catitems_order)) {
            $this->systemdk_shop_catitems_order = 'item_position';
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|cat".$catalog_id."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT b.category_id,b.category_img,b.category_name,b.category_date,a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_description,a.catalog_meta_title,a.catalog_meta_keywords,a.catalog_meta_description,c.subcategory_id,c.subcategory_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and (b.category_id is NULL or b.category_show = '1') LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and (c.subcategory_id is NULL or c.subcategory_show = '1') WHERE a.catalog_id = ".$catalog_id." and a.catalog_show = '1' ORDER BY b.".$this->systemdk_shop_categories_order.",c.".$this->systemdk_shop_subcategories_order;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['4'])) {
                    $numrows = intval($result->fields['4']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
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
                    $this->registry->main_class->assign("shop_catalog_img",$catalog_img);
                    $this->registry->main_class->assign("shop_catalog_name",$catalog_name);
                    $this->registry->main_class->assign("shop_catalog_description",$catalog_description);
                    $this->registry->main_class->set_sitemeta($catalog_meta_title,$catalog_meta_description,$catalog_meta_keywords);
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        while(!$result->EOF) {
                            $category_id = intval($result->fields['0']);
                            $category_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                            if(!isset($category_img) or $category_img == "") {
                                $category_img = "no";
                            }
                            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                            $category_date = intval($result->fields['3']);
                            $category_date = date("d.m.y H:i",$category_date);
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
                        $this->registry->main_class->assign("shop_categories_all",$categories_all);
                        $this->registry->main_class->assign("shop_total_categories",count($categories_all));
                        $this->registry->main_class->assign("systemdk_shop_image_position",$this->systemdk_shop_image_position);
                        $this->registry->main_class->assign("systemdk_shop_image_path",$this->systemdk_shop_image_path);
                        $this->registry->main_class->assign("systemdk_shop_image_width",$this->systemdk_shop_image_width);
                        $this->registry->main_class->assign("systemdk_shop_items_columns",$this->systemdk_shop_items_columns);
                        $this->registry->main_class->assign("systemdk_shop_items_underparent",$this->systemdk_shop_items_underparent);
                        $this->registry->main_class->assign("systemdk_shop_items_columnsperc",intval(100 / $this->systemdk_shop_items_columns));
                    } else {
                        $this->registry->main_class->assign("shop_categories_all","no");
                        $this->registry->main_class->assign("shop_total_categories","0");
                    }
                } else {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|notfindcatalog|".$this->registry->language)) {
                        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                        $this->registry->main_class->assign("shop_message","notfindcatalog");
                        $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|notfindcatalog|".$this->registry->language);
                    exit();
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                    $this->registry->main_class->assign("shop_message","categorieserror");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language);
                exit();
            }
            $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2') "."WHERE a.item_show = '1' and ((a.item_catalog_id is NOT NULL and (c.catalog_id is NULL or c.catalog_id = ".$catalog_id.")) or (a.item_category_id is NOT NULL and (d.cat_catalog_id is NULL or d.cat_catalog_id = ".$catalog_id.") and (f.catalog_id is NULL or f.catalog_id = ".$catalog_id.") and (a.item_display = '3' or a.item_display = '2')) or (a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id in (SELECT a1.subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." a1,".PREFIX."_categories_".$this->registry->sitelang." a2 WHERE a1.subcategory_cat_id=a2.category_id and a2.cat_catalog_id = ".$catalog_id.")) and (g.cat_catalog_id is NULL or g.cat_catalog_id = ".$catalog_id.") and (h.catalog_id is NULL or h.catalog_id = ".$catalog_id.") and (a.item_display = '3' or a.item_display = '2'))) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
            $sql = "SELECT count(*) as count_items ".$from;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows2 = intval($result->fields['0']);
                }
            }
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_catitems_order;
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/categs/".$catalog_id);
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&func=categs&cat_id=".$catalog_id."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
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
                    $this->registry->main_class->assign("shop_items_all",$items_all);
                    $this->registry->main_class->assign("shop_total_items",$numrows2);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_position",$this->systemdk_shop_itemimage_position);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_path",$this->systemdk_shop_itemimage_path);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_width",$this->systemdk_shop_itemimage_width);
                    if(isset($numrows2)) {
                        $num_pages = @ceil($numrows2 / $num_string_rows);
                    } else {
                        $num_pages = 0;
                    }
                    if($num_pages > 1) {
                        if($num_page > 1) {
                            $prevpage = $num_page - 1;
                            $this->registry->main_class->assign("prevpage",$prevpage);
                        } else {
                            $this->registry->main_class->assign("prevpage","no");
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
                            $this->registry->main_class->assign("nextpage",$nextpage);
                        } else {
                            $this->registry->main_class->assign("nextpage","no");
                        }
                        $this->registry->main_class->assign("html",$html);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("shop_items_all","no");
                    $this->registry->main_class->assign("shop_total_items","0");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->assign("shop_items_all","no");
                $this->registry->main_class->assign("shop_total_items","0");
                $this->registry->main_class->assign("html","no");
            }
            $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_categories.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|cat".$catalog_id."|".$num_page."|".$this->registry->language);
    }


    public function subcategs() {
        if(isset($_GET['cat_id'])) {
            $catalog_id = intval($_GET['cat_id']);
        }
        if(isset($_GET['categ_id'])) {
            $category_id = intval($_GET['categ_id']);
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if(!isset($_GET['cat_id']) or !isset($_GET['categ_id']) or (isset($_GET['cat_id']) and intval($_GET['cat_id']) == 0) or (isset($_GET['categ_id']) and intval($_GET['categ_id']) == 0)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|notalldata2|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                $this->registry->main_class->assign("shop_message","notalldata2");
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|notalldata2|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->assign("shop_catalog_id",$catalog_id);
            $this->registry->main_class->assign("shop_category_id",$category_id);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_shop_categitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(!isset($this->systemdk_shop_subcategories_order)) {
            $this->systemdk_shop_subcategories_order = 'subcategory_position';
        }
        if(!isset($this->systemdk_shop_categitems_order)) {
            $this->systemdk_shop_categitems_order = 'item_position';
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|cat".$catalog_id."|categ".$category_id."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_date,b.category_id,b.category_img,b.category_name,b.category_description,b.category_meta_title,b.category_meta_keywords,b.category_meta_description,a.catalog_id,a.catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and b.category_id = ".$category_id." and b.category_show = '1' LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and (c.subcategory_id is NULL or c.subcategory_show = '1') WHERE a.catalog_id = ".$catalog_id." and a.catalog_show = '1' ORDER BY c.".$this->systemdk_shop_subcategories_order;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['4']) and intval($result->fields['4']) > 0) {
                    $numrows = intval($result->fields['4']);
                } else {
                    $numrows = 0;
                    $cache = "category";
                }
                if(isset($result->fields['11']) and intval($result->fields['11']) > 0) {
                    $numrowsb = intval($result->fields['11']);
                } else {
                    $numrowsb = 0;
                    $cache = "catalog";
                }
                if($numrows > 0 and $numrowsb > 0) {
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
                    $this->registry->main_class->assign("shop_category_img",$category_img);
                    $this->registry->main_class->assign("shop_category_name",$category_name);
                    $this->registry->main_class->assign("shop_category_description",$category_description);
                    $this->registry->main_class->assign("shop_catalog_name",$catalog_name);
                    $this->registry->main_class->set_sitemeta($category_meta_title,$category_meta_description,$category_meta_keywords);
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        while(!$result->EOF) {
                            $subcategory_id = intval($result->fields['0']);
                            $subcategory_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                            if(!isset($subcategory_img) or $subcategory_img == "") {
                                $subcategory_img = "no";
                            }
                            $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                            $subcategory_date = intval($result->fields['3']);
                            $subcategory_date = date("d.m.y H:i",$subcategory_date);
                            $subcategories_all[] = array(
                                "subcategory_id" => $subcategory_id,
                                "subcategory_img" => $subcategory_img,
                                "subcategory_name" => $subcategory_name
                            );
                            $result->MoveNext();
                        }
                        $this->registry->main_class->assign("shop_subcategories_all",$subcategories_all);
                        $this->registry->main_class->assign("shop_total_subcategories",count($subcategories_all));
                        $this->registry->main_class->assign("systemdk_shop_image_position",$this->systemdk_shop_image_position);
                        $this->registry->main_class->assign("systemdk_shop_image_path",$this->systemdk_shop_image_path);
                        $this->registry->main_class->assign("systemdk_shop_image_width",$this->systemdk_shop_image_width);
                        $this->registry->main_class->assign("systemdk_shop_items_columns",$this->systemdk_shop_items_columns);
                        $this->registry->main_class->assign("systemdk_shop_items_columnsperc",intval(100 / $this->systemdk_shop_items_columns));
                    } else {
                        $this->registry->main_class->assign("shop_subcategories_all","no");
                        $this->registry->main_class->assign("shop_total_subcategories","0");
                    }
                } else {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|notfind".$cache."|".$this->registry->language)) {
                        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                        $this->registry->main_class->assign("shop_message","notfind".$cache);
                        $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|notfind".$cache."|".$this->registry->language);
                    exit();
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                    $this->registry->main_class->assign("shop_message","categorieserror");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language);
                exit();
            }
            $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1') "."WHERE a.item_show = '1' and ((a.item_category_id is NOT NULL and (d.cat_catalog_id is NULL or d.cat_catalog_id = ".$catalog_id.")  and (d.category_id is NULL or d.category_id = ".$category_id.") and (f.catalog_id is NULL or f.catalog_id = ".$catalog_id.")) or (a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id in (SELECT a1.subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." a1,".PREFIX."_categories_".$this->registry->sitelang." a2 WHERE a1.subcategory_cat_id=a2.category_id and a2.cat_catalog_id = ".$catalog_id." and a2.category_id = ".$category_id.")) and (g.cat_catalog_id is NULL or g.cat_catalog_id = ".$catalog_id.") and (g.category_id is NULL or g.category_id = ".$category_id.") and (h.catalog_id is NULL or h.catalog_id = ".$catalog_id.") and (a.item_display = '3' or a.item_display = '2' or a.item_display = '1'))) and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
            $sql = "SELECT count(*) as count_items ".$from;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows2 = intval($result->fields['0']);
                }
            }
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,d.category_id,d.category_name,e.subcategory_id,e.subcategory_name,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_categitems_order;
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/subcategs/".$catalog_id."/".$category_id);
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&func=subcategs&cat_id=".$catalog_id."&categ_id=".$category_id."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
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
                    $this->registry->main_class->assign("shop_items_all",$items_all);
                    $this->registry->main_class->assign("shop_total_items",$numrows2);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_position",$this->systemdk_shop_itemimage_position);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_path",$this->systemdk_shop_itemimage_path);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_width",$this->systemdk_shop_itemimage_width);
                    if(isset($numrows2)) {
                        $num_pages = @ceil($numrows2 / $num_string_rows);
                    } else {
                        $num_pages = 0;
                    }
                    if($num_pages > 1) {
                        if($num_page > 1) {
                            $prevpage = $num_page - 1;
                            $this->registry->main_class->assign("prevpage",$prevpage);
                        } else {
                            $this->registry->main_class->assign("prevpage","no");
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
                            $this->registry->main_class->assign("nextpage",$nextpage);
                        } else {
                            $this->registry->main_class->assign("nextpage","no");
                        }
                        $this->registry->main_class->assign("html",$html);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("shop_items_all","no");
                    $this->registry->main_class->assign("shop_total_items","0");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->assign("shop_items_all","no");
                $this->registry->main_class->assign("shop_total_items","0");
                $this->registry->main_class->assign("html","no");
            }
            $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_subcategories.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|cat".$catalog_id."|categ".$category_id."|".$num_page."|".$this->registry->language);
    }


    public function items() {
        if(isset($_GET['cat_id'])) {
            $catalog_id = intval($_GET['cat_id']);
        }
        if(isset($_GET['categ_id'])) {
            $category_id = intval($_GET['categ_id']);
        }
        if(isset($_GET['subcateg_id'])) {
            $subcategory_id = intval($_GET['subcateg_id']);
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if(!isset($_GET['cat_id']) or !isset($_GET['categ_id']) or !isset($_GET['subcateg_id']) or (isset($_GET['cat_id']) and intval($_GET['cat_id']) == 0) or (isset($_GET['categ_id']) and intval($_GET['categ_id']) == 0) or (isset($_GET['subcateg_id']) and intval($_GET['subcateg_id']) == 0)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|notalldata3|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                $this->registry->main_class->assign("shop_message","notalldata3");
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|notalldata3|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->assign("shop_catalog_id",$catalog_id);
            $this->registry->main_class->assign("shop_category_id",$category_id);
            $this->registry->main_class->assign("shop_subcategory_id",$subcategory_id);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_shop_subcategitems_perpage;
        $offset = ($num_page - 1) * $num_string_rows;
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(!isset($this->systemdk_shop_subcategitems_order)) {
            $this->systemdk_shop_subcategitems_order = 'item_position';
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|cat".$catalog_id."|categ".$category_id."|sucateg".$subcategory_id."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_description,c.subcategory_meta_title,c.subcategory_meta_keywords,c.subcategory_meta_description,b.category_id,b.category_name,a.catalog_id,a.catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and b.category_id = ".$category_id." and b.category_show = '1' LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and c.subcategory_id = ".$subcategory_id." and c.subcategory_show = '1' WHERE a.catalog_id = ".$catalog_id." and a.catalog_show = '1'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                    $cache = "subcategory";
                }
                if(isset($result->fields['7']) and intval($result->fields['7']) > 0) {
                    $numrowsb = intval($result->fields['7']);
                } else {
                    $numrowsb = 0;
                    $cache = "category";
                }
                if(isset($result->fields['9']) and intval($result->fields['9']) > 0) {
                    $numrowsc = intval($result->fields['9']);
                } else {
                    $numrowsc = 0;
                    $cache = "catalog";
                }
                if($numrows > 0 and $numrowsb > 0 and $numrowsc > 0) {
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
                    $this->registry->main_class->assign("shop_subcategory_img",$subcategory_img);
                    $this->registry->main_class->assign("shop_subcategory_name",$subcategory_name);
                    $this->registry->main_class->assign("shop_subcategory_description",$subcategory_description);
                    $this->registry->main_class->assign("shop_category_name",$category_name);
                    $this->registry->main_class->assign("shop_catalog_name",$catalog_name);
                    $this->registry->main_class->set_sitemeta($subcategory_meta_title,$subcategory_meta_description,$subcategory_meta_keywords);
                } else {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|notfind".$cache."|".$this->registry->language)) {
                        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                        $this->registry->main_class->assign("shop_message","notfind".$cache);
                        $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|notfind".$cache."|".$this->registry->language);
                    exit();
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                    $this->registry->main_class->assign("shop_message","categorieserror");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language);
                exit();
            }
            $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL "."WHERE a.item_show = '1' and ((a.item_subcategory_id is NOT NULL and (e.subcategory_id is NULL or e.subcategory_id = ".$subcategory_id.") and (g.cat_catalog_id is NULL or g.cat_catalog_id = ".$catalog_id.") and (g.category_id is NULL or g.category_id = ".$category_id.") and (h.catalog_id is NULL or h.catalog_id = ".$catalog_id."))) and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
            $sql = "SELECT count(*) as count_items ".$from;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows2 = intval($result->fields['0']);
                }
            }
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,e.subcategory_id,e.subcategory_name,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id ".$from." ORDER BY a.".$this->systemdk_shop_subcategitems_order;
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if(SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/shop/items/cat/".$catalog_id."/categ/".$category_id."/subcateg/".$subcategory_id);
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=shop&func=items&cat_id=".$catalog_id."&categ_id=".$category_id."&subcateg_id=".$subcategory_id."&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
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
                    $this->registry->main_class->assign("shop_items_all",$items_all);
                    $this->registry->main_class->assign("shop_total_items",$numrows2);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_position",$this->systemdk_shop_itemimage_position);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_path",$this->systemdk_shop_itemimage_path);
                    $this->registry->main_class->assign("systemdk_shop_itemimage_width",$this->systemdk_shop_itemimage_width);
                    if(isset($numrows2)) {
                        $num_pages = @ceil($numrows2 / $num_string_rows);
                    } else {
                        $num_pages = 0;
                    }
                    if($num_pages > 1) {
                        if($num_page > 1) {
                            $prevpage = $num_page - 1;
                            $this->registry->main_class->assign("prevpage",$prevpage);
                        } else {
                            $this->registry->main_class->assign("prevpage","no");
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
                            $this->registry->main_class->assign("nextpage",$nextpage);
                        } else {
                            $this->registry->main_class->assign("nextpage","no");
                        }
                        $this->registry->main_class->assign("html",$html);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("shop_items_all","no");
                    $this->registry->main_class->assign("shop_total_items","0");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->assign("shop_items_all","no");
                $this->registry->main_class->assign("shop_total_items","0");
                $this->registry->main_class->assign("html","no");
            }
            $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_items.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|cat".$catalog_id."|categ".$category_id."|subcateg".$subcategory_id."|".$num_page."|".$this->registry->language);
    }


    public function item() {
        if(isset($_GET['item_id'])) {
            $item_id = intval($_GET['item_id']);
        } else {
            $item_id = 0;
        }
        if(isset($_GET['cat_id'])) {
            $catalog_id = intval($_GET['cat_id']);
        }
        if(isset($_GET['categ_id'])) {
            $category_id = intval($_GET['categ_id']);
        }
        if(isset($_GET['subcateg_id'])) {
            $subcategory_id = intval($_GET['subcateg_id']);
        }
        if(isset($catalog_id) and $catalog_id != 0) {
            if(isset($subcategory_id) and isset($category_id) and $category_id != 0) {
                $cache = "subcateg";
                if($subcategory_id != 0) {
                    $cacheid = "cat".$catalog_id."|categ".$category_id."|subcateg".$subcategory_id."|item".$item_id;
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif(isset($category_id)) {
                $cache = "categ";
                if($category_id != 0) {
                    $cacheid = "cat".$catalog_id."|categ".$category_id."|item".$item_id;
                    $check = 1;
                } else {
                    $check = 0;
                }
                $subcategory_id = 0;
            } else {
                $cache = "cat";
                $cacheid = "cat".$catalog_id."|item".$item_id;
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
            $cacheid = "home|item".$item_id;
            if(!isset($item_id)) {
                $item_id = 0;
            }
            $check = 1;
            $catalog_id = 0;
            $category_id = 0;
            $subcategory_id = 0;
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if($item_id == 0 or $check == 0) {
            if($item_id == 0) {
                $cache_err = "notalldata4";
            } elseif($cache == "cat") {
                $cache_err = "notalldata";
            } elseif($cache == "categ") {
                $cache_err = "notalldata2";
            } elseif($cache == "subcateg") {
                $cache_err = "notalldata3";
            }
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|".$cache_err."|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                $this->registry->main_class->assign("shop_message",$cache_err);
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|".$cache_err."|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->assign("shop_item_id",$item_id);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|".$cacheid."|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
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
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
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
                    $item_date = intval($result->fields['10']);
                    $item_date = date("d.m.y H:i",$item_date);
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
                        $this->registry->main_class->assign("pages_menu",$pages_menu);
                    } else {
                        $this->registry->main_class->assign("pages_menu","no");
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
                    $this->registry->main_class->assign("shop_item_all",$item_all);
                    $this->registry->main_class->assign("shop_item_name",$item_name);
                    $this->registry->main_class->assign("shop_subcategory_name",$subcategory_name);
                    $this->registry->main_class->assign("shop_subcategory_id",$subcategory_id);
                    $this->registry->main_class->assign("shop_category_name",$category_name);
                    $this->registry->main_class->assign("shop_catalog_name",$catalog_name);
                    $this->registry->main_class->assign("shop_category_id",$category_id);
                    $this->registry->main_class->assign("shop_catalog_id",$catalog_id);
                    $this->registry->main_class->assign("systemdk_shop_image_position",$this->systemdk_shop_itemimage_position);
                    $this->registry->main_class->assign("systemdk_shop_image_path",$this->systemdk_shop_itemimage_path);
                    $this->registry->main_class->assign("systemdk_shop_image_width",$this->systemdk_shop_itemimage_width);
                } else {
                    $this->registry->main_class->assign("shop_item_all","no");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                }
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                    $this->registry->main_class->assign("shop_message","categorieserror");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_item.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|".$cachuser."|".$cacheid."|".$num_page."|".$this->registry->language);
    }


    public function cart() {
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart]);
        } else {
            $this->registry->main_class->assign("shop_cart_display_items","no");
        }
        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_CARTTITLE'));
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_cart.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|cart|display|".$cachuser."|".$this->registry->language);
    }


    private function shop_display_cart_items($cart,$shop_cart_allowchange = 1,$shop_cart_allowimages = 1) {
        $this->registry->main_class->assign("shop_cart_allowimages",intval($shop_cart_allowimages));
        $this->registry->main_class->assign("shop_cart_allowchange",intval($shop_cart_allowchange));
        $this->registry->main_class->assign("systemdk_shop_itemimage_path",$this->systemdk_shop_itemimage_path);
        $this->registry->main_class->assign("systemdk_shop_itemimage_width3",$this->systemdk_shop_itemimage_width3);
        $this->registry->main_class->assign("systemdk_shop_itemimage_height3",$this->systemdk_shop_itemimage_height3);
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
        $this->registry->main_class->assign("shop_cart_display_items",$this->shop_cart_items_all);
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
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
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
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function checkout() {
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart],0,0);
            $this->shop_user_deliv_pay(1);
        } else {
            $this->registry->main_class->assign("shop_cart_display_items","no");
        }
        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_cart.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|checkout|display|".$cachuser."|".$this->registry->language);
    }


    private function shop_user_deliv_pay($check_user = 0) {
        if($check_user == 1) {
            if($this->registry->main_class->is_user_in_mainfunc()) {
                $row = $this->registry->main_class->get_user_info($this->registry->user);
                if(isset($row) and $row != "") {
                    $shop_user_id = intval($row['0']);
                    $shop_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                    $shop_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                    $shop_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['11']));
                    $shop_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['8']));
                    $shop_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['9']));
                    $shop_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                    if((!isset($shop_user_name) or $shop_user_name == "") and (isset($shop_user_surname) and $shop_user_surname != "")) {
                        $shop_user_name = $shop_user_surname;
                    } elseif((!isset($shop_user_name) or $shop_user_name == "") and (!isset($shop_user_surname) or $shop_user_surname == "")) {
                        $shop_user_name = $this->registry->main_class->extracting_data($row['5']);
                    } elseif(isset($shop_user_name) and $shop_user_name !== "" and isset($shop_user_surname) and $shop_user_surname !== "") {
                        $shop_user_name = $shop_user_name." ".$shop_user_surname;
                    }
                    $this->registry->main_class->assign("modules_shop_username",$shop_user_name);
                    $this->registry->main_class->assign("modules_shop_userid",$shop_user_id);
                    $this->registry->main_class->assign("modules_shop_usertelephone",$shop_user_telephone);
                    $this->registry->main_class->assign("modules_shop_usercountry",$shop_user_country);
                    $this->registry->main_class->assign("modules_shop_usercity",$shop_user_city);
                    $this->registry->main_class->assign("modules_shop_useremail",$shop_user_email);
                }
            }
        }
        $sql = "SELECT delivery_id,delivery_name,delivery_price,null as pay_id,null as pay_name,null as pay_price FROM ".PREFIX."_delivery_".$this->registry->sitelang." WHERE delivery_status = '1' UNION ALL SELECT null as delivery_id,null as delivery_name,null as delivery_price,pay_id,pay_name,pay_price FROM ".PREFIX."_pay_".$this->registry->sitelang." WHERE pay_status = '1' ORDER by delivery_id,pay_id ASC";
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if(isset($result->fields['3'])) {
                $numrows2 = intval($result->fields['3']);
            } else {
                $numrows2 = 0;
            }
            if($numrows > 0 or $numrows2 > 0) {
                while(!$result->EOF) {
                    if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
                        $delivery_id = intval($result->fields['0']);
                        $delivery_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $delivery_price = number_format($result->fields['2'],2,'.',' ');
                        if(isset($this->shop_order_delivery_id) and $this->shop_order_delivery_id > 0 and $this->shop_order_delivery_id == $delivery_id) {
                            $this->registry->main_class->assign("shop_final_delivery_name",$delivery_name);
                            $this->registry->main_class->assign("shop_final_delivery_price",$delivery_price);
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
                            $this->registry->main_class->assign("shop_final_pay_name",$pay_name);
                            $this->registry->main_class->assign("shop_final_pay_price",$pay_price);
                        }
                        $pay_all[] = array("pay_id" => $pay_id,"pay_name" => $pay_name,"pay_price" => $pay_price);
                    }
                    $result->MoveNext();
                }
                $this->registry->main_class->assign("shop_delivery_all",$delivery_all);
                $this->registry->main_class->assign("shop_pay_all",$pay_all);
            } else {
                $this->registry->main_class->assign("shop_delivery_all","no");
                $this->registry->main_class->assign("shop_pay_all","no");
            }
        } else {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_METATITLE'));
                $this->registry->main_class->assign("shop_message","categorieserror");
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|categories|".$this->registry->language);
            exit();
        }
    }


    public function checkout2() {
        if(isset($_POST['shop_ship_name'])) {
            $shop_ship_name = trim($_POST['shop_ship_name']);
        }
        if(isset($_POST['shop_ship_telephone'])) {
            $shop_ship_telephone = trim($_POST['shop_ship_telephone']);
        }
        if(isset($_POST['shop_ship_email'])) {
            $shop_ship_email = trim($_POST['shop_ship_email']);
        }
        if(isset($_POST['shop_ship_country'])) {
            $shop_ship_country = trim($_POST['shop_ship_country']);
        }
        if(isset($_POST['shop_ship_state'])) {
            $shop_ship_state = trim($_POST['shop_ship_state']);
        }
        if(isset($_POST['shop_ship_city'])) {
            $shop_ship_city = trim($_POST['shop_ship_city']);
        }
        if(isset($_POST['shop_ship_street'])) {
            $shop_ship_street = trim($_POST['shop_ship_street']);
        }
        if(isset($_POST['shop_ship_homenumber'])) {
            $shop_ship_homenumber = trim($_POST['shop_ship_homenumber']);
        }
        if(isset($_POST['shop_ship_flatnumber'])) {
            $shop_ship_flatnumber = trim($_POST['shop_ship_flatnumber']);
        }
        if(isset($_POST['shop_ship_postalcode'])) {
            $shop_ship_postalcode = trim($_POST['shop_ship_postalcode']);
        }
        if(isset($_POST['shop_ship_addaddrnotes'])) {
            $shop_ship_addaddrnotes = trim($_POST['shop_ship_addaddrnotes']);
        }
        if(isset($_POST['shop_order_delivery'])) {
            $shop_order_delivery = intval($_POST['shop_order_delivery']);
        }
        if(isset($_POST['shop_order_pay'])) {
            $shop_order_pay = intval($_POST['shop_order_pay']);
        }
        if(isset($_POST['shop_order_comments'])) {
            $shop_order_comments = trim($_POST['shop_order_comments']);
        }
        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
        if((!isset($_POST['shop_ship_name']) or !isset($_POST['shop_ship_telephone']) or !isset($_POST['shop_ship_email']) or !isset($_POST['shop_ship_country']) or !isset($_POST['shop_ship_state']) or  !isset($_POST['shop_ship_city']) or !isset($_POST['shop_ship_street']) or !isset($_POST['shop_ship_homenumber']) or !isset($_POST['shop_ship_flatnumber']) or !isset($_POST['shop_ship_postalcode']) or !isset($_POST['shop_ship_addaddrnotes']) or !isset($_POST['shop_order_delivery']) or !isset($_POST['shop_order_pay']) or !isset($_POST['shop_order_comments'])) or ($shop_ship_name == "" or $shop_ship_telephone == "" or $shop_ship_email == "" or $shop_order_delivery == 0 or $shop_order_pay == 0)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|shopnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("shop_message","shopnotalldata");
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|shopnotalldata|".$this->registry->language);
            exit();
        }
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart],0,0);
            $this->shop_order_delivery_id = $shop_order_delivery;
            $this->shop_order_pay_id = $shop_order_pay;
            $this->shop_user_deliv_pay(1);
            if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
                $this->registry->main_class->assign("enter_check","yes");
            } else {
                $this->registry->main_class->assign("enter_check","no");
            }
            $shop_ship_name = $this->registry->main_class->format_striptags($shop_ship_name);
            $shop_ship_telephone = $this->registry->main_class->format_striptags($shop_ship_telephone);
            $shop_ship_email = $this->registry->main_class->format_striptags($shop_ship_email);
            if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($shop_ship_email))) {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|useremail|".$this->registry->language)) {
                    $this->registry->main_class->assign("shop_message","useremail");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|useremail|".$this->registry->language);
                exit();
            }
            $shop_ship_country = $this->registry->main_class->format_striptags($shop_ship_country);
            $shop_ship_state = $this->registry->main_class->format_striptags($shop_ship_state);
            $shop_ship_city = $this->registry->main_class->format_striptags($shop_ship_city);
            $shop_ship_street = $this->registry->main_class->format_striptags($shop_ship_street);
            $shop_ship_homenumber = $this->registry->main_class->format_striptags($shop_ship_homenumber);
            $shop_ship_flatnumber = $this->registry->main_class->format_striptags($shop_ship_flatnumber);
            $shop_ship_postalcode = $this->registry->main_class->format_striptags($shop_ship_postalcode);
            $shop_ship_addaddrnotes = $this->registry->main_class->format_striptags($shop_ship_addaddrnotes);
            $shop_order_comments = $this->registry->main_class->format_striptags($shop_order_comments);
            $this->registry->main_class->assign("shop_cart_checkout_confirm",1);
            $this->registry->main_class->assign("modules_shop_username",$shop_ship_name);
            $this->registry->main_class->assign("modules_shop_usertelephone",$shop_ship_telephone);
            $this->registry->main_class->assign("modules_shop_useremail",$shop_ship_email);
            $this->registry->main_class->assign("modules_shop_usercountry",$shop_ship_country);
            $this->registry->main_class->assign("modules_shop_userstate",$shop_ship_state);
            $this->registry->main_class->assign("modules_shop_usercity",$shop_ship_city);
            $this->registry->main_class->assign("modules_shop_userstreet",$shop_ship_street);
            $this->registry->main_class->assign("modules_shop_userhomenumber",$shop_ship_homenumber);
            $this->registry->main_class->assign("modules_shop_userflatnumber",$shop_ship_flatnumber);
            $this->registry->main_class->assign("modules_shop_userpostalcode",$shop_ship_postalcode);
            $this->registry->main_class->assign("modules_shop_useraddaddrnotes",$shop_ship_addaddrnotes);
            $this->registry->main_class->assign("modules_shop_userdelivery",$shop_order_delivery);
            $this->registry->main_class->assign("modules_shop_userpay",$shop_order_pay);
            $this->registry->main_class->assign("modules_shop_usercomments",$shop_order_comments);
        } else {
            $this->registry->main_class->assign("shop_cart_display_items","no");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/shop/shop_cart.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|checkout2|display|".$cachuser."|".$this->registry->language);
    }


    public function insert() {
        if(isset($_POST['shop_ship_name'])) {
            $shop_ship_name = trim($_POST['shop_ship_name']);
        }
        if(isset($_POST['shop_ship_telephone'])) {
            $shop_ship_telephone = trim($_POST['shop_ship_telephone']);
        }
        if(isset($_POST['shop_ship_email'])) {
            $shop_ship_email = trim($_POST['shop_ship_email']);
        }
        if(isset($_POST['shop_ship_country'])) {
            $shop_ship_country = trim($_POST['shop_ship_country']);
        }
        if(isset($_POST['shop_ship_state'])) {
            $shop_ship_state = trim($_POST['shop_ship_state']);
        }
        if(isset($_POST['shop_ship_city'])) {
            $shop_ship_city = trim($_POST['shop_ship_city']);
        }
        if(isset($_POST['shop_ship_street'])) {
            $shop_ship_street = trim($_POST['shop_ship_street']);
        }
        if(isset($_POST['shop_ship_homenumber'])) {
            $shop_ship_homenumber = trim($_POST['shop_ship_homenumber']);
        }
        if(isset($_POST['shop_ship_flatnumber'])) {
            $shop_ship_flatnumber = trim($_POST['shop_ship_flatnumber']);
        }
        if(isset($_POST['shop_ship_postalcode'])) {
            $shop_ship_postalcode = trim($_POST['shop_ship_postalcode']);
        }
        if(isset($_POST['shop_ship_addaddrnotes'])) {
            $shop_ship_addaddrnotes = trim($_POST['shop_ship_addaddrnotes']);
        }
        if(isset($_POST['shop_order_delivery'])) {
            $shop_order_delivery = intval($_POST['shop_order_delivery']);
        }
        if(isset($_POST['shop_order_pay'])) {
            $shop_order_pay = intval($_POST['shop_order_pay']);
        }
        if(isset($_POST['shop_order_comments'])) {
            $shop_order_comments = trim($_POST['shop_order_comments']);
        }
        $this->registry->main_class->assign("systemdk_shop_valuta",$this->systemdk_shop_valuta);
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if((!isset($_POST['shop_ship_name']) or !isset($_POST['shop_ship_telephone']) or !isset($_POST['shop_ship_email']) or !isset($_POST['shop_ship_country']) or !isset($_POST['shop_ship_state']) or  !isset($_POST['shop_ship_city']) or !isset($_POST['shop_ship_street']) or !isset($_POST['shop_ship_homenumber']) or !isset($_POST['shop_ship_flatnumber']) or !isset($_POST['shop_ship_postalcode']) or !isset($_POST['shop_ship_addaddrnotes']) or !isset($_POST['shop_order_delivery']) or !isset($_POST['shop_order_pay']) or !isset($_POST['shop_order_comments'])) or ($shop_ship_name == "" or $shop_ship_telephone == "" or $shop_ship_email == "" or $shop_order_delivery == 0 or $shop_order_pay == 0)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|shopnotalldata2|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                $this->registry->main_class->assign("shop_message","shopnotalldata2");
                $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|shopnotalldata2|".$this->registry->language);
            exit();
        }
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(ENTER_CHECK == "yes") {
            if(extension_loaded("gd") and session_id() == "") {
                session_start();
            }
            if(extension_loaded("gd") and ((!isset($_POST["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|checkkode|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                    $this->registry->main_class->assign("shop_message","checkkode");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|checkkode|".$this->registry->language);
                exit();
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        if(isset($this->systemdk_shop_antispam_ipaddr) and intval($this->systemdk_shop_antispam_ipaddr) == 1) {
            $post_date_check = $this->registry->main_class->gettime() - 86400;
            $sql = "SELECT count(*) FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_ip = '".$this->registry->ip_addr."' and order_date > ".$post_date_check;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows >= intval($this->systemdk_shop_antispam_num) and intval($this->systemdk_shop_antispam_num) > 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|postlimit|".$this->registry->language)) {
                        $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                        $this->registry->main_class->assign("shop_message","postlimit");
                        $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|postlimit|".$this->registry->language);
                    exit();
                }
            }
        }
        if(isset($_SESSION[$this->systemdk_shop_cart]) and (array_count_values($_SESSION[$this->systemdk_shop_cart]))) {
            if($this->registry->main_class->is_user_in_mainfunc()) {
                $row = $this->registry->main_class->get_user_info($this->registry->user);
                if(isset($row) and $row != "") {
                    $shop_user_id = "'".intval($row['0'])."'";
                } else {
                    $shop_user_id = 'NULL';
                }
            } else {
                $shop_user_id = 'NULL';
            }
            $order_amount = $this->registry->main_class->float2db($_SESSION[$this->systemdk_shop_total_price]); //number_format($_SESSION[$this->systemdk_shop_total_price],2);
            $order_date = $this->registry->main_class->gettime();
            $order_status = 1;
            $shop_ship_name = $this->registry->main_class->format_striptags($shop_ship_name);
            $shop_ship_name = $this->registry->main_class->processing_data($shop_ship_name);
            $shop_ship_telephone = $this->registry->main_class->format_striptags($shop_ship_telephone);
            $shop_ship_telephone = $this->registry->main_class->processing_data($shop_ship_telephone);
            $shop_ship_email = $this->registry->main_class->format_striptags($shop_ship_email);
            if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($shop_ship_email))) {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|useremail2|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                    $this->registry->main_class->assign("shop_message","useremail2");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|useremail2|".$this->registry->language);
                exit();
            }
            $shop_ship_email = $this->registry->main_class->processing_data($shop_ship_email);
            $shop_ship_country = $this->registry->main_class->format_striptags($shop_ship_country);
            if($shop_ship_country != "") {
                $shop_ship_country = $this->registry->main_class->processing_data($shop_ship_country);
            } else {
                $shop_ship_country = 'NULL';
            }
            $shop_ship_state = $this->registry->main_class->format_striptags($shop_ship_state);
            if($shop_ship_state != "") {
                $shop_ship_state = $this->registry->main_class->processing_data($shop_ship_state);
            } else {
                $shop_ship_state = 'NULL';
            }
            $shop_ship_city = $this->registry->main_class->format_striptags($shop_ship_city);
            if($shop_ship_city != "") {
                $shop_ship_city = $this->registry->main_class->processing_data($shop_ship_city);
            } else {
                $shop_ship_city = 'NULL';
            }
            $shop_ship_street = $this->registry->main_class->format_striptags($shop_ship_street);
            if($shop_ship_street != "") {
                $shop_ship_street = $this->registry->main_class->processing_data($shop_ship_street);
            } else {
                $shop_ship_street = 'NULL';
            }
            $shop_ship_homenumber = $this->registry->main_class->format_striptags($shop_ship_homenumber);
            if($shop_ship_homenumber != "") {
                $shop_ship_homenumber = $this->registry->main_class->processing_data($shop_ship_homenumber);
            } else {
                $shop_ship_homenumber = 'NULL';
            }
            $shop_ship_flatnumber = $this->registry->main_class->format_striptags($shop_ship_flatnumber);
            if($shop_ship_flatnumber != "") {
                $shop_ship_flatnumber = $this->registry->main_class->processing_data($shop_ship_flatnumber);
            } else {
                $shop_ship_flatnumber = 'NULL';
            }
            $shop_ship_postalcode = $this->registry->main_class->format_striptags($shop_ship_postalcode);
            if($shop_ship_postalcode != "") {
                $shop_ship_postalcode = $this->registry->main_class->processing_data($shop_ship_postalcode);
            } else {
                $shop_ship_postalcode = 'NULL';
            }
            $shop_ship_addaddrnotes = $this->registry->main_class->format_striptags($shop_ship_addaddrnotes);
            $shop_ship_addaddrnotes_length = strlen($shop_ship_addaddrnotes);
            if($shop_ship_addaddrnotes != "") {
                $shop_ship_addaddrnotes = $this->registry->main_class->processing_data($shop_ship_addaddrnotes);
            } else {
                $shop_ship_addaddrnotes = 'NULL';
            }
            $shop_order_comments = $this->registry->main_class->format_striptags($shop_order_comments);
            $shop_order_comments_length = strlen($shop_order_comments);
            if($shop_order_comments != "") {
                $shop_order_comments = $this->registry->main_class->processing_data($shop_order_comments);
            } else {
                $shop_order_comments = 'NULL';
            }
            if($shop_ship_addaddrnotes_length > 255 or $shop_order_comments_length > 255) {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|postlength|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                    $this->registry->main_class->assign("shop_message","postlength");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|postlength|".$this->registry->language);
                exit();
            }
            $this->shop_display_cart_items($_SESSION[$this->systemdk_shop_cart],0,0);
            if($this->shop_cart_items_all == "no") {
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                $this->registry->main_class->assign("shop_cart_display_items","no");
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|checkout|display|".$cachuser."|".$this->registry->language)) {
                    $this->registry->main_class->assign("include_center","modules/shop/shop_cart.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|checkout|display|".$cachuser."|".$this->registry->language);
                exit();
            }
            $this->shop_order_delivery_id = $shop_order_delivery;
            $this->shop_order_pay_id = $shop_order_pay;
            $this->shop_user_deliv_pay(0);
            $order_ip = $this->registry->main_class->processing_data($this->registry->main_class->format_striptags($this->registry->ip_addr));
            $this->db->StartTrans();
            $order_id = $this->db->GenID(PREFIX."_orders_id_".$this->registry->sitelang);
            $sql = "INSERT INTO ".PREFIX."_orders_".$this->registry->sitelang."(order_id,order_customer_id,order_amount,order_date,ship_date,order_status,ship_name,ship_street,ship_house,ship_flat,ship_city,ship_state,ship_postalcode,ship_country,ship_telephone,ship_email,ship_addinfo,order_delivery,order_pay,order_comments,order_ip) VALUES ('".$order_id."',".$shop_user_id.",'".$order_amount."','".$order_date."',NULL,'".$order_status."',".$shop_ship_name.",".$shop_ship_street.",".$shop_ship_homenumber.",".$shop_ship_flatnumber.",".$shop_ship_city.",".$shop_ship_state.",".$shop_ship_postalcode.",".$shop_ship_country.",".$shop_ship_telephone.",".$shop_ship_email.",".$shop_ship_addaddrnotes.",'".$shop_order_delivery."','".$shop_order_pay."',".$shop_order_comments.",".$order_ip.")";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $items_info = $this->shop_cart_items_all;
            for($i = 0,$size = count($items_info);$i < $size;++$i) //foreach($_SESSION[$this->systemdk_shop_cart] as $item_id => $qty)
            {
                $item_id = intval($items_info[$i]['item_id']);
                $order_item_name = $this->registry->main_class->processing_data($items_info[$i]['item_name']);
                $order_item_price = $this->registry->main_class->float2db($items_info[$i]['item_price']);
                $order_item_quantity = intval($items_info[$i]['item_qty']);
                $sql = "INSERT INTO ".PREFIX."_order_items_".$this->registry->sitelang." (order_item_id,order_id,item_id,order_item_name,order_item_price,order_item_quantity) VALUES ('".$this->db->GenID(PREFIX."_order_items_id_".$this->registry->sitelang)."','".$order_id."','".$item_id."',".$order_item_name.",'".$order_item_price."','".$order_item_quantity."')";
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
                $this->registry->main_class->assign("errortext",$error);
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|error|sqlerror|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                    $this->registry->main_class->assign("shop_message","notinsert");
                    $this->registry->main_class->assign("include_center","modules/shop/shop_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|error|sqlerror|".$this->registry->language);
                exit();
            } else {
                unset($_SESSION[$this->systemdk_shop_total_price]);
                unset($_SESSION[$this->systemdk_shop_items]);
                unset($_SESSION[$this->systemdk_shop_cart]);
                $this->registry->main_class->assign("systemdk_shop_order_id",$order_id);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|0");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|1");
                $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
                $this->registry->main_class->assign("shop_message","addok");
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|addok|".$this->registry->language)) {
                    $this->registry->main_class->assign("include_center","modules/shop/shop_cart.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|addok|".$this->registry->language);
            }
        } else {
            $this->registry->main_class->configLoad($this->registry->language."/modules/shop/shop.conf");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_SHOP_MODULE_PROCESSORDERTITLE'));
            $this->registry->main_class->assign("shop_cart_display_items","no");
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|shop|checkout|display|".$cachuser."|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center","modules/shop/shop_cart.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|shop|checkout|display|".$cachuser."|".$this->registry->language);
        }
    }
}
?>
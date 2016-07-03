<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class admin_shop extends model_base
{


    private $img_array;
    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
                'num_page',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (!isset($data_array['cat']) && !isset($data_array['categ'])) {
            $cache = "home";
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
        } elseif (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['categ']) && $data_array['categ'] != 0) {
                $cache = "subcateg";
            } else {
                $cache = "categ";
                $data_array['categ'] = 0;
            }
        } else {
            $this->error = 'empty_data';

            return;
        }
        if (isset($data_array['num_page']) && intval($data_array['num_page']) != 0) {
            $num_page = intval($data_array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if ($cache == "home") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang;
            $sql = "SELECT catalog_id,catalog_name,catalog_date,catalog_show FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " order by catalog_date ASC";
        } elseif ($cache == "categ") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE cat_catalog_id = " . $data_array['cat'];
            $sql = "SELECT a.category_id,a.category_name,a.category_date,a.category_show,c.catalog_id,c.catalog_name FROM " . PREFIX . "_categories_"
                   . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                   . " c ON a.cat_catalog_id=c.catalog_id WHERE c.catalog_id = " . $data_array['cat'] . " order by a.category_date ASC";
        } elseif ($cache == "subcateg") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id = " . $data_array['categ'];
            $sql = "SELECT a.subcategory_id,a.subcategory_name,a.subcategory_date,a.subcategory_show,c.category_id,c.category_name,d.catalog_id,d.catalog_name FROM "
                   . PREFIX . "_subcategories_" . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                   . " c ON a.subcategory_cat_id=c.category_id RIGHT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                   . " d ON c.cat_catalog_id=d.catalog_id WHERE c.category_id = " . $data_array['categ'] . " and d.catalog_id = " . $data_array['cat']
                   . " order by a.subcategory_date ASC";
        }
        $result = $this->db->Execute($sql0);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'show_sql_error_' . $cache;
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if ($cache == "categ" && isset($result->fields['4']) && intval($result->fields['4']) > 0) {
            $catalog_id = intval($result->fields['4']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['5'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
        } elseif ($cache == "subcateg" && isset($result->fields['4']) && intval($result->fields['4']) > 0) {
            $category_id = intval($result->fields['4']);
            $category_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['5'])
            );
            $catalog_id = intval($result->fields['6']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['7'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
        } elseif ($cache != "home") {
            if ($cache == "categ") {
                $this->error = 'show_not_found_catalog';
            } elseif ($cache == "subcateg") {
                $this->error = 'show_not_found_categ';
            }

            return;
        }
        if ($row_exist > 0) {
            while (!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_date = date("d.m.Y H:i", intval($result->fields['2']));
                $item_show = intval($result->fields['3']);
                $items_all[] = [
                    "item_id"   => $item_id,
                    "item_name" => $item_name,
                    "item_date" => $item_date,
                    "item_show" => $item_show,
                ];
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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
                $this->result['totalitems'] = $num_rows;
                $this->result['num_pages'] = $num_pages;
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $this->result['items_all'] = "no";
            $this->result['html'] = "no";
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
    }


    public function shop_edit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
                'subcateg',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['subcateg']) && isset($data_array['categ']) && $data_array['categ'] != 0) {
                $cache = "subcateg";
                if ($data_array['subcateg'] != 0) {
                    $itemid = $data_array['subcateg'];
                } else {
                    $itemid = 0;
                }
            } elseif (isset($data_array['categ'])) {
                $cache = "categ";
                if ($data_array['categ'] != 0) {
                    $itemid = $data_array['categ'];
                } else {
                    $itemid = 0;
                }
            } else {
                $cache = "cat";
                $itemid = $data_array['cat'];
                $data_array['categ'] = 0;
            }
        } else {
            $cache = "cat";
            $itemid = 0;
        }
        if ($itemid == 0) {
            $this->error = 'edit_no_' . $cache;

            return;
        }
        if ($cache == "cat") {
            $sql =
                "SELECT a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_description,a.catalog_position,a.catalog_show,a.catalog_date,a.catalog_meta_title,a.catalog_meta_keywords,a.catalog_meta_description FROM "
                . PREFIX . "_catalogs_" . $this->registry->sitelang . " a WHERE a.catalog_id = " . $itemid;
        } elseif ($cache == "categ") {
            $sql =
                "SELECT b.category_id,b.category_img,b.category_name,b.category_description,b.category_position,b.category_show,b.category_date,b.category_meta_title,b.category_meta_keywords,b.category_meta_description,b.cat_catalog_id,a.catalog_id,a.catalog_name FROM "
                . PREFIX . "_catalogs_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " b ON a.catalog_id=b.cat_catalog_id and a.catalog_id = " . $data_array['cat'] . " and b.category_id = " . $itemid
                . " order by (case WHEN b.category_id IS NULL THEN 0 ELSE b.category_id END) DESC,a.catalog_name ASC";
        } elseif ($cache == "subcateg") {
            $sql =
                "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_description,c.subcategory_position,c.subcategory_show,c.subcategory_date,c.subcategory_meta_title,c.subcategory_meta_keywords,c.subcategory_meta_description,c.subcategory_cat_id,a.catalog_id,a.catalog_name,b.category_id,b.category_name FROM "
                . PREFIX . "_catalogs_" . $this->registry->sitelang . " a INNER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " b ON a.catalog_id=b.cat_catalog_id and a.catalog_id = " . $data_array['cat'] . " LEFT OUTER JOIN " . PREFIX . "_subcategories_"
                . $this->registry->sitelang . " c ON b.category_id=c.subcategory_cat_id and c.subcategory_id = " . $itemid . " and b.category_id = "
                . $data_array['categ'] . " order by (case WHEN c.subcategory_id IS NULL THEN 0 ELSE c.subcategory_id END) DESC,b.category_name ASC";
        }
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_sql_error_' . $cache;
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'edit_not_found_' . $cache;

            return;
        }
        require_once('../modules/shop/shop_config.inc');
        while (!$result->EOF) {
            if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
                $item_id = intval($result->fields['0']);
                $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $item_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $item_position = intval($result->fields['4']);
                $item_show = intval($result->fields['5']);
                $item_date = intval($result->fields['6']);
                $item_date2 = date("d.m.Y", $item_date);
                $item_hour = date("H", $item_date);
                $item_minute = date("i", $item_date);
                $item_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $item_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $item_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                if ($cache == "categ" || $cache == "subcateg") {
                    if ($cache != "subcateg") {
                        $in_catalog_id = intval($result->fields['10']);
                    } else {
                        $in_catalog_id = intval($result->fields['11']);
                    }
                    $item_catalog_id = intval($result->fields['11']);
                    $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                } else {
                    $in_catalog_id = "no";
                    $item_catalog_id = "no";
                    $item_catalog_name = "no";
                }
                if ($cache == "subcateg") {
                    $in_category_id = intval($result->fields['10']);
                    $item_category_id = intval($result->fields['13']);
                    $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                } else {
                    $in_category_id = "no";
                    $item_category_id = "no";
                    $item_category_name = "no";
                }
                $item_all[] = [
                    "item_id"               => $item_id,
                    "item_img"              => $item_img,
                    "item_imgpath"          => SYSTEMDK_SHOP_IMAGE_PATH,
                    "item_name"             => $item_name,
                    "item_description"      => $item_description,
                    "item_position"         => $item_position,
                    "item_show"             => $item_show,
                    "item_date"             => $item_date2,
                    "item_hour"             => $item_hour,
                    "item_minute"           => $item_minute,
                    "item_meta_title"       => $item_meta_title,
                    "item_meta_keywords"    => $item_meta_keywords,
                    "item_meta_description" => $item_meta_description,
                    "in_catalog_id"         => $in_catalog_id,
                    "item_catalog_id"       => $item_catalog_id,
                    "item_catalog_name"     => $item_catalog_name,
                    "in_category_id"        => $in_category_id,
                    "item_category_id"      => $item_category_id,
                    "item_category_name"    => $item_category_name,
                ];
            }
            if (($cache == "categ" || $cache == "subcateg") && isset($result->fields['11']) && intval($result->fields['11']) > 0) {
                $catalog_id = intval($result->fields['11']);
                $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $catalog_all_now = ["catalog_id" => $catalog_id, "catalog_name" => $catalog_name];
                if (!isset($catalogs_all) || (isset($catalogs_all) && !in_array($catalog_all_now, $catalogs_all))) {
                    $catalogs_all[] = ["catalog_id" => $catalog_id, "catalog_name" => $catalog_name];
                }
            }
            if ($cache == "subcateg" && isset($result->fields['13']) && intval($result->fields['13']) > 0) {
                $category_id = intval($result->fields['13']);
                $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                $categories_all[] = ["category_id" => $category_id, "category_name" => $category_name];
            }
            $result->MoveNext();
        }
        $this->result['item_all'] = $item_all;
        $this->result['adminmodules_shop_item_id'] = $item_id;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
        if (isset($catalogs_all)) {
            $this->result['catalogs_all'] = $catalogs_all;
        }
        if (isset($categories_all)) {
            $this->result['categories_all'] = $categories_all;
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['include_jquery'] = 'yes';
        $this->result['include_jquery_data'] = "dateinput";
    }


    public function shop_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'item_name',
                'item_date',
                'item_action_img',
                'item_img',
                'item_description',
                'item_meta_title',
                'item_meta_keywords',
                'item_meta_description',
            ];
            $keys2 = [
                'item_id',
                'item_hour',
                'item_minute',
                'item_show',
                'item_catalog',
                'item_catalog_old',
                'item_category',
                'item_category_old',
                'depth',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (!isset($array['item_hour'])) {
            $data_array['item_hour'] = 0;
        }
        if (!isset($array['item_minute'])) {
            $data_array['item_minute'] = 0;
        }
        if (isset($data_array['depth']) && $data_array['depth'] == 1) {
            $cache = "categ";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 2) {
            $cache = "subcateg";
        } else {
            $cache = "cat";
        }
        if ((!isset($array['item_id']) || !isset($array['item_name']) || !isset($array['item_date']) || !isset($array['item_hour']) || !isset($array['item_minute'])
             || !isset($array['item_action_img'])
             || !isset($array['item_description'])
             || !isset($array['item_show'])
             || !isset($array['item_meta_title'])
             || !isset($array['item_meta_keywords'])
             || !isset($array['item_meta_description']))
            || ($data_array['item_id'] == 0 || $data_array['item_name'] == "" || $data_array['item_date'] == ""
                || ($cache == "categ"
                    && (!isset($data_array['item_catalog']) || !isset($data_array['item_catalog_old']) || $data_array['item_catalog'] == 0
                        || $data_array['item_catalog_old'] == 0))
                || ($cache == "subcateg"
                    && (!isset($data_array['item_category']) || !isset($data_array['item_category_old']) || $data_array['item_category'] == 0
                        || $data_array['item_category_old'] == 0)))
        ) {
            $this->error = 'edit_not_all_data_' . $cache;

            return;
        }
        $data_array['item_action_img'] = $this->registry->main_class->format_striptags($data_array['item_action_img']);
        require_once('../modules/shop/shop_config.inc');
        if ($data_array['item_action_img'] == 'del') {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            if (@unlink("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $data_array['item_img']) || !is_file("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $data_array['item_img'])) {
                $data_array['item_img'] = 'NULL';
            } else {
                $this->error = 'edit_del_img_' . $cache;

                return;
            }
        } elseif ($data_array['item_action_img'] == 'new_file') {
            if (isset($_FILES['item_img']['name']) && trim($_FILES['item_img']['name']) !== '') {
                if (is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if ($_FILES['item_img']['size'] > SYSTEMDK_SHOP_IMAGE_MAXSIZE) {
                        $this->error = 'edit_img_max_size_' . $cache;

                        return;
                    }
                    if (($_FILES['item_img']['type'] == "image/gif") || ($_FILES['item_img']['type'] == "image/jpg") || ($_FILES['item_img']['type'] == "image/jpeg")
                        || ($_FILES['item_img']['type'] == "image/pjpeg")
                        || ($_FILES['item_img']['type'] == "image/png")
                        || ($_FILES['item_img']['type'] == "image/bmp")
                    ) {
                        $this->registry->main_class->set_locale();
                        if (basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $data_array['item_img'] = date("dmY", time()) . "_" . basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $data_array['item_img'] = "";
                        }
                        if (file_exists($data_array['item_img']) && $data_array['item_img'] != "") {
                            @unlink("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $data_array['item_img']);
                        }
                        if ($data_array['item_img'] == ""
                            || !@move_uploaded_file(
                                $_FILES['item_img']['tmp_name'], "../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $data_array['item_img']
                            )
                        ) {
                            $this->error = 'edit_img_not_upload_' . $cache;

                            return;
                        }
                        $item_size2 = @getimagesize("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $data_array['item_img']);
                        if (($item_size2[0] > SYSTEMDK_SHOP_IMAGE_MAXWIDTH) || ($item_size2[1] > SYSTEMDK_SHOP_IMAGE_MAXHEIGHT)) {
                            @unlink("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $data_array['item_img']);
                            $this->error = 'edit_img_not_eq_' . $cache;

                            return;
                        }
                    } else {
                        $this->error = 'edit_img_not_eq_type_' . $cache;

                        return;
                    }
                } else {
                    $this->error = 'edit_img_upload_' . $cache;

                    return;
                }
                $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img'], 'need');
            } else {
                $data_array['item_img'] = 'NULL';
            }
        } else {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img']);
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        $data_array['item_date'] = $this->registry->main_class->format_striptags($data_array['item_date']);
        $data_array['item_date'] = $data_array['item_date'] . " " . sprintf("%02d", $data_array['item_hour']) . ":" . sprintf("%02d", $data_array['item_minute']);
        $data_array['item_date'] = $this->registry->main_class->time_to_unix($data_array['item_date']);
        $keys = [
            'item_description',
            'item_meta_title',
            'item_meta_keywords',
            'item_meta_description',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                if ($value != "") {
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                } else {
                    $data_array[$key] = 'NULL';
                }
            }
        }
        if ($cache == "cat") {
            $sql = "UPDATE " . PREFIX . "_catalogs_" . $this->registry->sitelang . " SET catalog_img = " . $data_array['item_img'] . ",catalog_name = "
                   . $data_array['item_name'] . ",catalog_description = " . $data_array['item_description'] . ",catalog_show = '" . $data_array['item_show']
                   . "',catalog_date = '" . $data_array['item_date'] . "',catalog_meta_title = " . $data_array['item_meta_title'] . ",catalog_meta_keywords = "
                   . $data_array['item_meta_keywords'] . ",catalog_meta_description = " . $data_array['item_meta_description'] . " WHERE catalog_id = "
                   . $data_array['item_id'];
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        } elseif ($cache == "categ") {
            $this->db->StartTrans();
            $sql_add = false;
            if ($data_array['item_catalog'] != $data_array['item_catalog_old']) {
                $sql0 = "SELECT category_position FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE cat_catalog_id = "
                        . $data_array['item_catalog_old'] . " and category_id = " . $data_array['item_id'];
                $result0 = $this->db->Execute($sql0);
                if ($result0) {
                    if (isset($result0->fields['0'])) {
                        $data_array['item_position'] = intval($result0->fields['0']);
                        $sql1 = "UPDATE " . PREFIX . "_categories_" . $this->registry->sitelang
                                . " SET category_position = category_position-1 WHERE category_position > 0 and cat_catalog_id = " . $data_array['item_catalog_old']
                                . " and category_position > " . $data_array['item_position'];
                        $result1 = $this->db->Execute($sql1);
                        if ($result1 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        }
                        unset($data_array['item_position']);
                        $sql2 = "SELECT max(category_position)+1 FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE cat_catalog_id = "
                                . $data_array['item_catalog'];
                        $result2 = $this->db->Execute($sql2);
                        if ($result2) {
                            if (isset($result2->fields['0'])) {
                                $data_array['item_position'] = intval($result2->fields['0']);
                            }
                        }
                        if (!isset($data_array['item_position'])) {
                            $data_array['item_position'] = 1;
                        }
                        if ($result2 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        }
                        $sql_add = ",category_position = '" . $data_array['item_position'] . "'";
                    } else {
                        $this->db->CompleteTrans();
                        $this->error = 'edit_sql_error_' . $cache;

                        return;
                    }
                }
                if ($result0 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            }
            $sql = "UPDATE " . PREFIX . "_categories_" . $this->registry->sitelang . " SET category_img = " . $data_array['item_img'] . ",category_name = "
                   . $data_array['item_name'] . ",cat_catalog_id = " . $data_array['item_catalog'] . ",category_description = " . $data_array['item_description']
                   . $sql_add
                   . ",category_show = '" . $data_array['item_show'] . "',category_date = '" . $data_array['item_date'] . "',category_meta_title = "
                   . $data_array['item_meta_title'] . ",category_meta_keywords = " . $data_array['item_meta_keywords'] . ",category_meta_description = "
                   . $data_array['item_meta_description'] . " WHERE category_id = " . $data_array['item_id'];
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ((isset($result0) && $result0 === false) || (isset($result1) && $result1 === false) || (isset($result2) && $result2 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } elseif ($cache == "subcateg") {
            $this->db->StartTrans();
            $sql_add = false;
            if ($data_array['item_category'] != $data_array['item_category_old']) {
                $sql0 = "SELECT subcategory_position FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id = "
                        . $data_array['item_category_old'] . " and subcategory_id = " . $data_array['item_id'];
                $result0 = $this->db->Execute($sql0);
                if ($result0) {
                    if (isset($result0->fields['0'])) {
                        $data_array['item_position'] = intval($result0->fields['0']);
                        $sql1 = "UPDATE " . PREFIX . "_subcategories_" . $this->registry->sitelang
                                . " SET subcategory_position = subcategory_position-1 WHERE subcategory_position > 0 and subcategory_cat_id = "
                                . $data_array['item_category_old'] . " and subcategory_position > " . $data_array['item_position'];
                        $result1 = $this->db->Execute($sql1);
                        if ($result1 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        }
                        unset($data_array['item_position']);
                        $sql2 = "SELECT max(subcategory_position)+1 FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id = "
                                . $data_array['item_category'];
                        $result2 = $this->db->Execute($sql2);
                        if ($result2) {
                            if (isset($result2->fields['0'])) {
                                $data_array['item_position'] = intval($result2->fields['0']);
                            }
                        }
                        if (!isset($data_array['item_position'])) {
                            $data_array['item_position'] = 1;
                        }
                        if ($result2 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = ["code" => $error_code, "message" => $error_message];
                        }
                        $sql_add = ",subcategory_position = '" . $data_array['item_position'] . "'";
                    } else {
                        $this->db->CompleteTrans();
                        $this->error = 'edit_sql_error_' . $cache;

                        return;
                    }
                }
                if ($result0 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            }
            $sql = "UPDATE " . PREFIX . "_subcategories_" . $this->registry->sitelang . " SET subcategory_img = " . $data_array['item_img'] . ",subcategory_name = "
                   . $data_array['item_name'] . ",subcategory_cat_id = " . $data_array['item_category'] . ",subcategory_description = " . $data_array['item_description']
                   . $sql_add . ",subcategory_show = '" . $data_array['item_show'] . "',subcategory_date = '" . $data_array['item_date'] . "',subcategory_meta_title = "
                   . $data_array['item_meta_title'] . ",subcategory_meta_keywords = " . $data_array['item_meta_keywords'] . ",subcategory_meta_description = "
                   . $data_array['item_meta_description'] . " WHERE subcategory_id = " . $data_array['item_id'];
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ((isset($result0) && $result0 === false) || (isset($result1) && $result1 === false) || (isset($result2) && $result2 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        }
        if ($result === false) {
            $this->error = 'edit_sql_error_' . $cache;
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_ok_' . $cache;

            return;
        } else {
            if ($cache == "cat") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|add");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_id']);
            } elseif ($cache == "categ") {
                if ($data_array['item_catalog'] != $data_array['item_catalog_old']) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|add|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                if (intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                }
            } elseif ($cache == "subcateg") {
                if ($data_array['item_category'] != $data_array['item_category_old']) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                }
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                    . "|subcateg" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category']
                          . "|subcateg" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category_old']
                          . "|subcateg" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                if (intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                }
            }
            $this->result = 'edit_done_' . $cache;
        }
    }


    public function shop_add($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['categ']) && $data_array['categ'] != 0) {
                $cache = "subcateg";
            } else {
                $data_array['categ'] = 0;
                $cache = "categ";
            }
        } else {
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $cache = "cat";
        }
        if ($cache == "categ") {
            $sql = "SELECT catalog_id,catalog_name FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " order by catalog_name ASC";
        } elseif ($cache == "subcateg") {
            $sql = "SELECT a.category_id,a.category_name,b.catalog_id,b.catalog_name FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " a INNER JOIN "
                   . PREFIX . "_catalogs_" . $this->registry->sitelang . " b ON a.cat_catalog_id=b.catalog_id WHERE b.catalog_id = " . $data_array['cat']
                   . " order by a.category_name ASC";
        } else {
            $sql = "no";
        }
        if ($sql !== "no") {
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'add_sql_error_' . $cache;
                $this->error_array = $error;

                return;
            }
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist < 1) {
                if ($cache == "categ") {
                    $this->error = 'add_no_catalogs';
                } elseif ($cache == "subcateg") {
                    $this->error = 'add_no_categs';
                }

                return;
            }
            while (!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_all[] = ["item_id" => $item_id, "item_name" => $item_name];
                if ($item_id == $data_array['cat'] && $cache == "categ") {
                    $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                }
                if (isset($result->fields['2']) && !isset($catalog_name) && $cache == "subcateg") {
                    $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                }
                if ($item_id == $data_array['categ'] && !isset($category_name) && $cache == "subcateg") {
                    $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                }
                $result->MoveNext();
            }
            $this->result['item_all'] = $item_all;
            if (isset($catalog_name)) {
                $this->result['adminmodules_shop_cat_name'] = $catalog_name;
            }
            if (isset($category_name)) {
                $this->result['adminmodules_shop_categ_name'] = $category_name;
            }
            if ((!isset($catalog_name) && ($cache == "categ" || $cache == "subcateg")) || (!isset($category_name) && $cache == "subcateg")) {
                if ($cache == "categ") {
                    $this->error = 'add_not_found_catalog';
                } elseif ($cache == "subcateg") {
                    $this->error = 'add_not_found_categ';
                }

                return;
            }
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
    }


    public function shop_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'item_name',
                'item_description',
                'item_meta_title',
                'item_meta_keywords',
                'item_meta_description',
            ];
            $keys2 = [
                'item_show',
                'item_catalog',
                'item_category',
                'depth',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['depth']) && $data_array['depth'] == 1) {
            $cache = "categ";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 2) {
            $cache = "subcateg";
        } else {
            $cache = "cat";
        }
        if ((!isset($array['item_name']) || !isset($array['item_description']) || !isset($array['item_show']) || !isset($array['item_meta_title'])
             || !isset($array['item_meta_keywords'])
             || !isset($array['item_meta_description']))
            || ($data_array['item_name'] == "" || ($cache == "categ" && (!isset($data_array['item_catalog']) || $data_array['item_catalog'] == 0))
                || ($cache == "subcateg" && (!isset($data_array['item_category']) || $data_array['item_category'] == 0)))
        ) {
            $this->error = 'add_not_all_data_' . $cache;

            return;
        }
        require_once('../modules/shop/shop_config.inc');
        if ($this->registry->main_class->format_striptags($_FILES['item_img']['name']) != "") {
            if (isset($_FILES['item_img']['name']) && trim($_FILES['item_img']['name']) !== '') {
                if (is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if ($_FILES['item_img']['size'] > SYSTEMDK_SHOP_IMAGE_MAXSIZE) {
                        $this->error = 'add_img_max_size_' . $cache;

                        return;
                    }
                    if (($_FILES['item_img']['type'] == "image/gif") || ($_FILES['item_img']['type'] == "image/jpg") || ($_FILES['item_img']['type'] == "image/jpeg")
                        || ($_FILES['item_img']['type'] == "image/pjpeg")
                        || ($_FILES['item_img']['type'] == "image/png")
                        || ($_FILES['item_img']['type'] == "image/bmp")
                    ) {
                        $this->registry->main_class->set_locale();
                        if (basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $item_img = date("dmY", time()) . "_" . basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $item_img = "";
                        }
                        if (file_exists($item_img) && $item_img != "") {
                            @unlink("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $item_img);
                        }
                        if ($item_img == "" || !@move_uploaded_file($_FILES['item_img']['tmp_name'], "../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $item_img)) {
                            $this->error = 'add_img_not_upload_' . $cache;

                            return;
                        }
                        $item_size2 = @getimagesize("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $item_img);
                        if (($item_size2[0] > SYSTEMDK_SHOP_IMAGE_MAXWIDTH) || ($item_size2[1] > SYSTEMDK_SHOP_IMAGE_MAXHEIGHT)) {
                            @unlink("../" . SYSTEMDK_SHOP_IMAGE_PATH . "/" . $item_img);
                            $this->error = 'add_img_not_eq_' . $cache;

                            return;
                        }
                    } else {
                        $this->error = 'add_img_not_eq_type_' . $cache;

                        return;
                    }
                } else {
                    $this->error = 'add_img_upload_' . $cache;

                    return;
                }
                $item_img = $this->registry->main_class->processing_data($item_img, 'need');
            } else {
                $item_img = 'NULL';
            }
        } else {
            $item_img = 'NULL';
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        $item_date = $this->registry->main_class->get_time();
        $keys = [
            'item_description',
            'item_meta_title',
            'item_meta_keywords',
            'item_meta_description',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $value = $this->registry->main_class->format_striptags($value);
                if ($value != "") {
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                } else {
                    $data_array[$key] = 'NULL';
                }
            }
        }
        $this->db->StartTrans();
        if ($cache == "cat") {
            $sql = "SELECT max(catalog_position) FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang;
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $item_position = intval($result->fields['0']) + 1;
                }
            }
            if (!isset($item_position)) {
                $item_position = 1;
            }
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_catalogs_id_" . $this->registry->sitelang, 'catalog_id');
            $sql = "INSERT INTO " . PREFIX . "_catalogs_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "catalog_img,catalog_name,catalog_description,catalog_position,catalog_show,catalog_date,catalog_meta_title,catalog_meta_keywords,catalog_meta_description) VALUES ("
                   . $sequence_array['sequence_value_string'] . "" . $item_img . "," . $data_array['item_name'] . "," . $data_array['item_description'] . ",'"
                   . $item_position . "','" . $data_array['item_show'] . "','" . $item_date . "'," . $data_array['item_meta_title'] . ","
                   . $data_array['item_meta_keywords']
                   . "," . $data_array['item_meta_description'] . ")";
        } elseif ($cache == "categ") {
            $sql = "SELECT max(category_position) FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE cat_catalog_id = "
                   . $data_array['item_catalog'];
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $item_position = intval($result->fields['0']) + 1;
                }
            }
            if (!isset($item_position)) {
                $item_position = 1;
            }
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_categories_id_" . $this->registry->sitelang, 'category_id');
            $sql = "INSERT INTO " . PREFIX . "_categories_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "category_img,category_name,cat_catalog_id,category_description,category_position,category_show,category_date,category_meta_title,category_meta_keywords,category_meta_description) VALUES ("
                   . $sequence_array['sequence_value_string'] . "" . $item_img . "," . $data_array['item_name'] . "," . $data_array['item_catalog'] . ","
                   . $data_array['item_description'] . ",'" . $item_position . "','" . $data_array['item_show'] . "','" . $item_date . "',"
                   . $data_array['item_meta_title']
                   . "," . $data_array['item_meta_keywords'] . "," . $data_array['item_meta_description'] . ")";
        } elseif ($cache == "subcateg") {
            $sql = "SELECT max(subcategory_position) FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id = "
                   . $data_array['item_category'];
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $item_position = intval($result->fields['0']) + 1;
                }
            }
            if (!isset($item_position)) {
                $item_position = 1;
            }
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_subcategories_id_" . $this->registry->sitelang, 'subcategory_id');
            $sql = "INSERT INTO " . PREFIX . "_subcategories_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "subcategory_img,subcategory_name,subcategory_cat_id,subcategory_description,subcategory_position,subcategory_show,subcategory_date,subcategory_meta_title,subcategory_meta_keywords,subcategory_meta_description) VALUES ("
                   . $sequence_array['sequence_value_string'] . "" . $item_img . "," . $data_array['item_name'] . "," . $data_array['item_category'] . ","
                   . $data_array['item_description'] . ",'" . $item_position . "','" . $data_array['item_show'] . "','" . $item_date . "',"
                   . $data_array['item_meta_title']
                   . "," . $data_array['item_meta_keywords'] . "," . $data_array['item_meta_description'] . ")";
        }
        if ($result) {
            $result = $this->db->Execute($sql);
        }
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        $this->db->CompleteTrans();
        if ($result === false) {
            $this->error = 'add_sql_error_' . $cache;
            $this->error_array = $error;

            return;
        } else {
            if ($cache == "cat") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|add");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            } elseif ($cache == "categ") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|add|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                if (intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                }
            } elseif ($cache == "subcateg") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                if (intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                }
            }
            $this->result = 'add_done_' . $cache;
        }
    }


    public function shop_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['action'])) {
            $action = trim($array['action']);
        } else {
            $action = "";
        }
        if (isset($array['cat'])) {
            $cat = intval($array['cat']);
        } else {
            $cat = 0;
        }
        if (isset($array['categ'])) {
            $categ = intval($array['categ']);
        } else {
            $categ = 0;
        }
        if (isset($array['subcateg'])) {
            $subcateg = intval($array['subcateg']);
        } else {
            $subcateg = 0;
        }
        if (isset($array['get_item_id'])) {
            $item_id = intval($array['get_item_id']);
        } else {
            $item_id = 0;
        }
        if ($item_id == 0 && isset($array['post_item_id']) && is_array($array['post_item_id']) && count($array['post_item_id']) > 0) {
            //$item_id_is_arr = 1;
            for ($i = 0, $size = count($array['post_item_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $item_id = "'" . intval($array['post_item_id'][$i]) . "'";
                } else {
                    $item_id .= ",'" . intval($array['post_item_id'][$i]) . "'";
                }
            }
        } else {
            //$item_id_is_arr = 0;
            $item_id = "'" . $item_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if ((($action == "on1" || $action == "off1" || $action == "delete1") && $cat == 0)
            || (($action == "on2" || $action == "off2" || $action == "delete2")
                && ($cat == 0 || $categ == 0))
            || (($action == "on4" || $action == "off4" || $action == "delete4") && $cat == 0)
            || (($action == "on5" || $action == "off5" || $action == "delete5") && ($cat == 0 || $categ == 0))
            || (($action == "on6" || $action == "off6" || $action == "delete6") && ($cat == 0 || $categ == 0 || $subcateg == 0))
        ) {
            $this->error = 'status_not_all_data';

            return;
        }
        if (!isset($item_id) || $action == "" || $item_id == "'0'") {
            $this->error = 'empty_data';

            return;
        }
        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_catalogs_" . $this->registry->sitelang . " SET catalog_show = '1' WHERE catalog_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "on";
        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_catalogs_" . $this->registry->sitelang . " SET catalog_show = '0' WHERE catalog_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "off";
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id, $action);
            $from = PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_subcategory_id IN (SELECT subcategory_id FROM " . PREFIX
                    . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (SELECT category_id FROM " . PREFIX . "_categories_"
                    . $this->registry->sitelang . " where cat_catalog_id IN (" . $item_id . "))) or item_category_id IN (SELECT category_id FROM " . PREFIX
                    . "_categories_"
                    . $this->registry->sitelang . " WHERE cat_catalog_id IN (" . $item_id . ")) or item_catalog_id IN (" . $item_id . ")";
                    
            $sql = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " WHERE item_id IN (SELECT item_id FROM " . $from . ")";
            $resultAdditionalParams = $this->db->Execute($sql);
            
            if ($resultAdditionalParams === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            
            $sql0 = "DELETE FROM " . $from;
            $result0 = $this->db->Execute($sql0);
            if ($result0 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql1 = "DELETE FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (SELECT category_id FROM " . PREFIX
                    . "_categories_" . $this->registry->sitelang . " where cat_catalog_id IN (" . $item_id . "))";
            $result1 = $this->db->Execute($sql1);
            if ($result1 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql2 = "DELETE FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE cat_catalog_id IN (" . $item_id . ")";
            $result2 = $this->db->Execute($sql2);
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql3 = "SELECT catalog_id FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " WHERE catalog_id IN (" . $item_id
                    . ") ORDER BY catalog_position ASC";
            $result3 = $this->db->Execute($sql3);
            if ($result3) {
                if (isset($result3->fields['0'])) {
                    while (!$result3->EOF) {
                        $catalog_id = intval($result3->fields['0']);
                        $sql4 = "SELECT catalog_position FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " WHERE catalog_id = " . $catalog_id;
                        $result4 = $this->db->Execute($sql4);
                        if ($result4) {
                            if (isset($result4->fields['0'])) {
                                $catalog_position = intval($result4->fields['0']);
                                $sql5 = "UPDATE " . PREFIX . "_catalogs_" . $this->registry->sitelang
                                        . " SET catalog_position=catalog_position-1 WHERE catalog_position > 0 and catalog_position > " . $catalog_position;
                                $result5 = $this->db->Execute($sql5);
                                if ($result5 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                }
                            }
                        } else {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                        }
                        $result3->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql = "DELETE FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " WHERE catalog_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($resultAdditionalParams === false || $result0 === false || $result1 === false || $result2 === false || $result3 === false || (isset($result4) && $result4 === false)
                || (isset($result5)
                    && $result5 === false)
                || !empty($this->error_array)
            ) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete";
        } elseif ($action == "on1") {
            $sql = "UPDATE " . PREFIX . "_categories_" . $this->registry->sitelang . " SET category_show = '1' WHERE category_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "on1";
        } elseif ($action == "off1") {
            $sql = "UPDATE " . PREFIX . "_categories_" . $this->registry->sitelang . " SET category_show = '0' WHERE category_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "off1";
        } elseif ($action == "delete1") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id, $action);
            $from = PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_subcategory_id IN (SELECT subcategory_id FROM " . PREFIX
                    . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (" . $item_id . ")) or item_category_id IN (" . $item_id . ")";
            $sql = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " WHERE item_id IN (SELECT item_id FROM " . $from . ")";
            $resultAdditionalParams = $this->db->Execute($sql);

            if ($resultAdditionalParams === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }

            $sql0 = "DELETE FROM " . $from;
            $result0 = $this->db->Execute($sql0);
            if ($result0 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql1 = "DELETE FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (" . $item_id . ")";
            $result1 = $this->db->Execute($sql1);
            if ($result1 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql2 = "SELECT category_id,cat_catalog_id FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE category_id IN (" . $item_id
                    . ") ORDER BY category_position ASC";
            $result2 = $this->db->Execute($sql2);
            if ($result2) {
                if (isset($result2->fields['0'])) {
                    while (!$result2->EOF) {
                        $category_id = intval($result2->fields['0']);
                        $cat_catalog_id = intval($result2->fields['1']);
                        $sql3 = "SELECT category_position FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE category_id = " . $category_id;
                        $result3 = $this->db->Execute($sql3);
                        if ($result3) {
                            if (isset($result3->fields['0'])) {
                                $category_position = intval($result3->fields['0']);
                                $sql4 = "UPDATE " . PREFIX . "_categories_" . $this->registry->sitelang
                                        . " SET category_position=category_position-1 WHERE category_position > 0 and cat_catalog_id = " . $cat_catalog_id
                                        . " and category_position > " . $category_position;
                                $result4 = $this->db->Execute($sql4);
                                if ($result4 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                }
                            }
                        } else {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                        }
                        $result2->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql = "DELETE FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE category_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($resultAdditionalParams === false || $result0 === false || $result1 === false || $result2 === false || (isset($result3) && $result3 === false) || (isset($result4) && $result4 === false)
                || !empty($this->error_array)
            ) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete1";
        } elseif ($action == "on2") {
            $sql = "UPDATE " . PREFIX . "_subcategories_" . $this->registry->sitelang . " SET subcategory_show = '1' WHERE subcategory_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "on2";
        } elseif ($action == "off2") {
            $sql = "UPDATE " . PREFIX . "_subcategories_" . $this->registry->sitelang . " SET subcategory_show = '0' WHERE subcategory_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "off2";
        } elseif ($action == "delete2") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id, $action);
            $from = PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_subcategory_id IN (" . $item_id . ")";
            $sql = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " WHERE item_id IN (SELECT item_id FROM " . $from . ")";
            $resultAdditionalParams = $this->db->Execute($sql);

            if ($resultAdditionalParams === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }

            $sql0 = "DELETE FROM " . $from;
            $result0 = $this->db->Execute($sql0);
            if ($result0 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql1 = "SELECT subcategory_id,subcategory_cat_id FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_id IN (" . $item_id
                    . ") ORDER BY subcategory_position ASC";
            $result1 = $this->db->Execute($sql1);
            if ($result1) {
                if (isset($result1->fields['0'])) {
                    while (!$result1->EOF) {
                        $subcategory_id = intval($result1->fields['0']);
                        $subcategory_cat_id = intval($result1->fields['1']);
                        $sql2 = "SELECT subcategory_position FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_id = "
                                . $subcategory_id;
                        $result2 = $this->db->Execute($sql2);
                        if ($result2) {
                            if (isset($result2->fields['0'])) {
                                $subcategory_position = intval($result2->fields['0']);
                                $sql3 = "UPDATE " . PREFIX . "_subcategories_" . $this->registry->sitelang
                                        . " SET subcategory_position=subcategory_position-1 WHERE subcategory_position > 0 and subcategory_cat_id = "
                                        . $subcategory_cat_id
                                        . " and subcategory_position > " . $subcategory_position;
                                $result3 = $this->db->Execute($sql3);
                                if ($result3 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                }
                            }
                        } else {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                        }
                        $result1->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql = "DELETE FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($resultAdditionalParams === false || $result0 === false || $result1 === false || (isset($result2) && $result2 === false)
                || (isset($result3)
                    && $result3 === false)
                || !empty($this->error_array)
            ) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete2";
        } elseif ($action == "on3" || $action == "on4" || $action == "on5" || $action == "on6") {
            $sql = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_show = '1' WHERE item_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "on_items";
        } elseif ($action == "off3" || $action == "off4" || $action == "off5" || $action == "off6") {
            $sql = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_show = '0' WHERE item_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            $shop_message = "off_items";
        } elseif ($action == "delete3" || $action == "delete4" || $action == "delete5" || $action == "delete6") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id, $action);
            if ($action == "delete3") {
                $sql1 = "SELECT item_id FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id IN (" . $item_id . ") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if ($result1) {
                    if (isset($result1->fields['0'])) {
                        while (!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $sql2 = "SELECT item_position FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id = " . $sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if ($result2) {
                                if (isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang
                                            . " SET item_position=item_position-1 WHERE item_position > 0 and item_catalog_id is null and item_category_id is null and item_subcategory_id is null and item_position > "
                                            . $item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if ($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                }
            } elseif ($action == "delete4") {
                $sql1 = "SELECT item_id,item_catalog_id FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id IN (" . $item_id
                        . ") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if ($result1) {
                    if (isset($result1->fields['0'])) {
                        while (!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $item_catalog_id = intval($result1->fields['1']);
                            $sql2 = "SELECT item_position FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id = " . $sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if ($result2) {
                                if (isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang
                                            . " SET item_position=item_position-1 WHERE item_position > 0 and item_catalog_id = " . $item_catalog_id
                                            . " and item_position > "
                                            . $item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if ($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                }
            } elseif ($action == "delete5") {
                $sql1 = "SELECT item_id,item_category_id FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id IN (" . $item_id
                        . ") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if ($result1) {
                    if (isset($result1->fields['0'])) {
                        while (!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $item_category_id = intval($result1->fields['1']);
                            $sql2 = "SELECT item_position FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id = " . $sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if ($result2) {
                                if (isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang
                                            . " SET item_position=item_position-1 WHERE item_position > 0 and item_category_id = " . $item_category_id
                                            . " and item_position > " . $item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if ($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                }
            } elseif ($action == "delete6") {
                $sql1 = "SELECT item_id,item_subcategory_id FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id IN (" . $item_id
                        . ") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if ($result1) {
                    if (isset($result1->fields['0'])) {
                        while (!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $item_subcategory_id = intval($result1->fields['1']);
                            $sql2 = "SELECT item_position FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id = " . $sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if ($result2) {
                                if (isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang
                                            . " SET item_position=item_position-1 WHERE item_position > 0 and item_subcategory_id = " . $item_subcategory_id
                                            . " and item_position > " . $item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if ($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = ["code" => $error_code, "message" => $error_message];
                }
            }
            $from = PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id IN (" . $item_id . ")";
            $sql = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " WHERE item_id IN (SELECT item_id FROM " . $from . ")";
            $resultAdditionalParams = $this->db->Execute($sql);

            if ($resultAdditionalParams === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }

            $sql = "DELETE FROM " . $from;
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($resultAdditionalParams === false || $result1 === false || (isset($result2) && $result2 === false) || (isset($result3) && $result3 === false) || !empty($this->error_array)) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete_items";
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($action != "delete" && $action != "delete1" && $action != "delete2" && $action != "delete3" && $action != "delete4" && $action != "delete5"
            && $action != "delete6"
        ) {
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($result === false) {
            $this->error = 'status_sql_error';

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'status_ok';

            return;
        } else {
            if ($action == "on" || $action == "off" || $action == "delete") {
                if ($action == "delete") {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } else {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|add");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin");
            } elseif ($action == "on1" || $action == "off1" || $action == "delete1") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $cat);
                if ($action == "delete1") {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $cat);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $cat);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|add|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home"); // |cat".$cat
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home"); // |cat".$cat
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $cat);
            } elseif ($action == "on2" || $action == "off2" || $action == "delete2") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $cat . "|categ" . $categ);
                if ($action == "delete2") {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $cat . "|categ" . $categ);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $cat . "|categ" . $categ);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $cat . "|categ" . $categ);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $cat);
                //$this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat."|categ".$categ);
                //$this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat."|categ".$categ);
            } elseif ($action == "on3" || $action == "off3" || $action == "delete3") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home|items");
                if ($action == "delete3") {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|home");
            } elseif ($action == "on4" || $action == "off4" || $action == "delete4") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $cat . "|items");
                if ($action == "delete4") {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $cat);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $cat);
            } elseif ($action == "on5" || $action == "off5" || $action == "delete5") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $cat . "|categ" . $categ . "|items");
                if ($action == "delete5") {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $cat . "|categ" . $categ);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $cat . "|categ" . $categ);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $cat);
            } elseif ($action == "on6" || $action == "off6" || $action == "delete6") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $cat . "|categ" . $categ . "|subcateg" . $subcateg . "|items"
                );
                if ($action == "delete6") {
                    $this->registry->main_class->clearCache(
                        null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $cat . "|categ" . $categ . "|subcateg" . $subcateg
                    );
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $cat . "|categ" . $categ . "|subcateg" . $subcateg
                );
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $cat);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $cat);
            }
            $this->result = $shop_message;
        }
    }


    private function shop_delete_images($item_id = "", $action = "")
    {
        require_once('../modules/shop/shop_config.inc');
        if ($action == "delete") {
            $sql0 = "SELECT item_id,item_img FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_subcategory_id IN (SELECT subcategory_id FROM "
                    . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (SELECT category_id FROM " . PREFIX . "_categories_"
                    . $this->registry->sitelang . " where cat_catalog_id IN (" . $item_id . "))) or item_category_id IN (SELECT category_id FROM " . PREFIX
                    . "_categories_"
                    . $this->registry->sitelang . " WHERE cat_catalog_id IN (" . $item_id . ")) or item_catalog_id IN (" . $item_id . ")";
            $this->shop_array_images($sql0, SYSTEMDK_SHOP_ITEMIMAGE_PATH);
            $sql1 = "SELECT subcategory_id,subcategory_img FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang
                    . " WHERE subcategory_cat_id IN (SELECT category_id FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " where cat_catalog_id IN ("
                    . $item_id
                    . "))";
            $this->shop_array_images($sql1, SYSTEMDK_SHOP_IMAGE_PATH);
            $sql2 = "SELECT category_id,category_img FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE cat_catalog_id IN (" . $item_id . ")";
            $this->shop_array_images($sql2, SYSTEMDK_SHOP_IMAGE_PATH);
            $sql = "SELECT catalog_id,catalog_img FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " WHERE catalog_id IN (" . $item_id . ")";
            $this->shop_array_images($sql, SYSTEMDK_SHOP_IMAGE_PATH);
        } elseif ($action == "delete1") {
            $sql0 = "SELECT item_id,item_img FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_subcategory_id IN (SELECT subcategory_id FROM "
                    . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (" . $item_id . ")) or item_category_id IN (" . $item_id
                    . ")";
            $this->shop_array_images($sql0, SYSTEMDK_SHOP_ITEMIMAGE_PATH);
            $sql1 = "SELECT subcategory_id,subcategory_img FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_cat_id IN (" . $item_id
                    . ")";
            $this->shop_array_images($sql1, SYSTEMDK_SHOP_IMAGE_PATH);
            $sql = "SELECT category_id,category_img FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE category_id IN (" . $item_id . ")";
            $this->shop_array_images($sql, SYSTEMDK_SHOP_IMAGE_PATH);
        } elseif ($action == "delete2") {
            $sql0 = "SELECT item_id,item_img FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_subcategory_id IN (" . $item_id . ")";
            $this->shop_array_images($sql0, SYSTEMDK_SHOP_ITEMIMAGE_PATH);
            $sql = "SELECT subcategory_id,subcategory_img FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " WHERE subcategory_id IN (" . $item_id . ")";
            $this->shop_array_images($sql, SYSTEMDK_SHOP_IMAGE_PATH);
        } elseif ($action == "delete3" || $action == "delete4" || $action == "delete5" || $action == "delete6") {
            $sql = "SELECT item_id,item_img FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE item_id IN (" . $item_id . ")";
            $this->shop_array_images($sql, SYSTEMDK_SHOP_ITEMIMAGE_PATH);
        }
    }


    private function shop_array_images($sql, $image_path)
    {
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $this->img_array[] = ["image_path" => $image_path, "img" => $img];
                    $result->MoveNext();
                }
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $this->error_array[] = ["code" => $error_code, "message" => $error_message];
        }
    }


    private function shop_delete_img_process()
    {
        if (isset($this->img_array) && is_array($this->img_array) && count($this->img_array) > 0) {
            for ($i = 0, $size = count($this->img_array); $i < $size; ++$i) {
                @unlink("../" . $this->img_array[$i]['image_path'] . "/" . $this->img_array[$i]['img']);
            }
        }
    }


    public function shop_items($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
                'subcateg',
                'num_page',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (!isset($data_array['cat']) && !isset($data_array['categ']) && !isset($data_array['subcateg'])) {
            $cache = "homei";
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
        } elseif (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['subcateg']) && $data_array['subcateg'] != 0) {
                $cache = "subcategi";
            } elseif (isset($data_array['categ']) && $data_array['categ'] != 0) {
                $cache = "categi";
                $data_array['subcateg'] = 0;
            } else {
                $cache = "cati";
                $data_array['categ'] = 0;
                $data_array['subcateg'] = 0;
            }
        } else {
            $this->error = 'empty_data';

            return;
        }
        if (isset($data_array['num_page']) && $data_array['num_page'] != 0) {
            $num_page = $data_array['num_page'];
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if ($cache == "homei") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang
                    . " b WHERE b.item_catalog_id is NULL and b.item_category_id is NULL and b.item_subcategory_id is NULL";
            $sql = "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date FROM " . PREFIX
                   . "_items_" . $this->registry->sitelang
                   . " a WHERE a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL order by a.item_date ASC";
        } elseif ($cache == "cati") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang . " e," . PREFIX . "_catalogs_" . $this->registry->sitelang
                    . " f WHERE f.catalog_id=e.item_catalog_id and e.item_catalog_id = " . $data_array['cat'];
            $sql =
                "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,b.catalog_id,b.catalog_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " b on a.item_catalog_id=b.catalog_id WHERE b.catalog_id = " . $data_array['cat'] . " order by a.item_date ASC";
        } elseif ($cache == "categi") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang . " e," . PREFIX . "_categories_" . $this->registry->sitelang . " f,"
                    . PREFIX . "_catalogs_" . $this->registry->sitelang
                    . " g WHERE f.category_id=e.item_category_id and g.catalog_id=f.cat_catalog_id and e.item_category_id = " . $data_array['categ']
                    . " and g.catalog_id = "
                    . $data_array['cat'];
            $sql =
                "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,b.category_id,b.category_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " b on a.item_category_id=b.category_id INNER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " c on b.cat_catalog_id=c.catalog_id WHERE b.category_id = " . $data_array['categ'] . " and c.catalog_id = " . $data_array['cat']
                . " order by a.item_date ASC";
        } elseif ($cache == "subcategi") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang . " e," . PREFIX . "_subcategories_" . $this->registry->sitelang . " f,"
                    . PREFIX . "_categories_" . $this->registry->sitelang . " g," . PREFIX . "_catalogs_" . $this->registry->sitelang
                    . " h WHERE f.subcategory_id=e.item_subcategory_id and g.category_id=f.subcategory_cat_id and h.catalog_id=g.cat_catalog_id and e.item_subcategory_id = "
                    . $data_array['subcateg'] . " and g.category_id = " . $data_array['categ'] . " and h.catalog_id = " . $data_array['cat'];
            $sql =
                "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,d.catalog_id,d.catalog_name,c.category_id,c.category_name,b.subcategory_id,b.subcategory_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " b on a.item_subcategory_id=b.subcategory_id INNER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " c on b.subcategory_cat_id=c.category_id INNER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " d on c.cat_catalog_id=d.catalog_id WHERE b.subcategory_id = " . $data_array['subcateg'] . " and c.category_id = " . $data_array['categ']
                . " and d.catalog_id = " . $data_array['cat'] . " order by a.item_date ASC";
        }
        $result = $this->db->Execute($sql0);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'items_sql_error_' . $cache;
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if ($cache == "cati" && isset($result->fields['8']) && intval($result->fields['8']) > 0) {
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['9'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
        } elseif ($cache == "categi" && isset($result->fields['10']) && intval($result->fields['10']) > 0) {
            $category_id = intval($result->fields['10']);
            $category_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['11'])
            );
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['9'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
        } elseif ($cache == "subcategi" && isset($result->fields['12']) && intval($result->fields['12']) > 0) {
            $subcategory_id = intval($result->fields['12']);
            $subcategory_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['13'])
            );
            $category_id = intval($result->fields['10']);
            $category_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['11'])
            );
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['9'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
            $this->result['adminmodules_shop_subcategory_id'] = $subcategory_id;
            $this->result['adminmodules_shop_subcategory_name'] = $subcategory_name;
        } elseif ($cache != "homei") {
            if ($cache == "cati") {
                $this->error = 'items_not_found_catalog';
            } elseif ($cache == "categi") {
                $this->error = 'items_not_found_categ';
            } elseif ($cache == "subcategi") {
                $this->error = 'items_not_found_subcateg';
            }

            return;
        }
        if ($row_exist > 0) {
            while (!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_show = intval($result->fields['2']);
                $item_quantity = intval($result->fields['3']);
                $item_quantity_unlim = intval($result->fields['4']);
                $item_quantity_param = intval($result->fields['5']);
                $item_display = intval($result->fields['6']);
                $item_date = date("d.m.Y H:i", intval($result->fields['7']));
                $items_all[] = [
                    "item_id"             => $item_id,
                    "item_name"           => $item_name,
                    "item_show"           => $item_show,
                    "item_quantity"       => $item_quantity,
                    "item_quantity_unlim" => $item_quantity_unlim,
                    "item_quantity_param" => $item_quantity_param,
                    "item_display"        => $item_display,
                    "item_date"           => $item_date,
                ];
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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
                $this->result['totalitems'] = $num_rows;
                $this->result['num_pages'] = $num_pages;
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $this->result['items_all'] = "no";
            $this->result['html'] = "no";
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
        $this->result['adminmodules_shop_subcateg'] = $data_array['subcateg'];
    }


    public function shop_edit_item($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'item_id',
                'cat',
                'categ',
                'subcateg',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (!isset($data_array['item_id'])) {
            $data_array['item_id'] = 0;
        }
        if (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['subcateg']) && isset($data_array['categ']) && $data_array['categ'] != 0) {
                $cache = "subcategi";
                if ($data_array['subcateg'] != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif (isset($data_array['categ'])) {
                $cache = "categi";
                if ($data_array['categ'] != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
                $data_array['subcateg'] = 0;
            } else {
                $cache = "cati";
                $check = 1;
                $data_array['categ'] = 0;
                $data_array['subcateg'] = 0;
            }
        } else {
            $cache = "homei";
            $check = 1;
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
        }
        if ($data_array['item_id'] == 0 || $check == 0) {
            if ($data_array['item_id'] == 0) {
                $this->error = 'edit_item_no_item';
            } elseif ($cache == "cati") {
                $this->error = 'edit_item_no_cat';
            } elseif ($cache == "categi") {
                $this->error = 'edit_item_no_categ';
            } elseif ($cache == "subcategi") {
                $this->error = 'edit_item_no_subcateg';
            }

            return;
        }
        if ($cache == "homei") {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a," . PREFIX . "_catalogs_" . $this->registry->sitelang . " b LEFT OUTER JOIN " . PREFIX
                . "_categories_" . $this->registry->sitelang . " c ON b.catalog_id=c.cat_catalog_id LEFT OUTER JOIN " . PREFIX . "_subcategories_"
                . $this->registry->sitelang . " d ON c.category_id=d.subcategory_cat_id WHERE a.item_id = " . $data_array['item_id']
                . " and a.item_catalog_id IS NULL and a.item_category_id IS NULL and a.item_subcategory_id IS NULL order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        } elseif ($cache == "cati") {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM "
                . PREFIX . "_catalogs_" . $this->registry->sitelang . " b LEFT OUTER JOIN " . PREFIX . "_items_" . $this->registry->sitelang
                . " a ON b.catalog_id=a.item_catalog_id and b.catalog_id = " . $data_array['cat'] . " and a.item_id = " . $data_array['item_id'] . " LEFT OUTER JOIN "
                . PREFIX . "_categories_" . $this->registry->sitelang . " c ON b.catalog_id=c.cat_catalog_id and b.catalog_id = " . $data_array['cat']
                . " LEFT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " d ON  c.category_id=d.subcategory_cat_id order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        } elseif ($cache == "categi") {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM "
                . PREFIX . "_catalogs_" . $this->registry->sitelang . " b LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " c ON b.catalog_id=c.cat_catalog_id and b.catalog_id = " . $data_array['cat'] . " LEFT OUTER JOIN " . PREFIX . "_items_" . $this->registry->sitelang
                . " a ON c.category_id=a.item_category_id and a.item_id = " . $data_array['item_id'] . " and c.category_id = " . $data_array['categ']
                . " LEFT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " d ON d.subcategory_cat_id=c.category_id order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        } elseif ($cache == "subcategi") {
            $sql =
                "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM "
                . PREFIX . "_catalogs_" . $this->registry->sitelang . " b LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " c ON b.catalog_id=c.cat_catalog_id and b.catalog_id = " . $data_array['cat'] . " LEFT OUTER JOIN " . PREFIX . "_subcategories_"
                . $this->registry->sitelang . " d ON c.category_id=d.subcategory_cat_id LEFT OUTER JOIN " . PREFIX . "_items_" . $this->registry->sitelang
                . " a ON d.subcategory_id=a.item_subcategory_id and a.item_id = " . $data_array['item_id'] . " and d.subcategory_id = " . $data_array['subcateg']
                . " and c.category_id = " . $data_array['categ']
                . " order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        }
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_item_sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'edit_item_not_found';

            return;
        }
        require_once('../modules/shop/shop_config.inc');
        while (!$result->EOF) {
            if (isset($result->fields['0']) && intval($result->fields['0']) > 0 && !isset($item_all)) {
                $item_id = intval($result->fields['0']);
                $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $item_price = floatval($result->fields['3']);
                $item_price_discounted = floatval($result->fields['4']);
                $item_catalog_id = intval($result->fields['5']);
                $item_category_id = intval($result->fields['6']);
                $item_subcategory_id = intval($result->fields['7']);
                $item_short_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $item_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $item_position = intval($result->fields['25']);
                $item_show = intval($result->fields['10']);
                $item_quantity = intval($result->fields['11']);
                $item_quantity_unlim = intval($result->fields['12']);
                $item_quantity_param = intval($result->fields['13']);
                $item_display = intval($result->fields['14']);
                $item_date = intval($result->fields['15']);
                $item_date2 = date("d.m.Y", $item_date);
                $item_hour = date("H", $item_date);
                $item_minute = date("i", $item_date);
                $item_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
                $item_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                $item_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['18']));
                if ($cache == "cati" || $cache == "categi" || $cache == "subcategi") {
                    $in_catalog_id = intval($result->fields['19']);
                    $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['20']));
                } else {
                    $in_catalog_id = "no";
                    $item_catalog_name = "no";
                }
                if ($cache == "categi" || $cache == "subcategi") {
                    $in_category_id = intval($result->fields['21']);
                    $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['22']));
                } else {
                    $in_category_id = "no";
                    $item_category_name = "no";
                }
                if ($cache == "subcategi") {
                    $in_subcategory_id = intval($result->fields['23']);
                    $item_subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['24']));
                } else {
                    $in_subcategory_id = "no";
                    $item_subcategory_name = "no";
                }
                $item_all[] = [
                    "item_id"                => $item_id,
                    "item_img"               => $item_img,
                    "item_imgpath"           => SYSTEMDK_SHOP_ITEMIMAGE_PATH,
                    "item_name"              => $item_name,
                    "item_price"             => $item_price,
                    "item_price_discounted"  => $item_price_discounted,
                    "item_catalog_id"        => $item_catalog_id,
                    "item_category_id"       => $item_category_id,
                    "item_subcategory_id"    => $item_subcategory_id,
                    "item_short_description" => $item_short_description,
                    "item_description"       => $item_description,
                    "item_show"              => $item_show,
                    "item_quantity"          => $item_quantity,
                    "item_quantity_unlim"    => $item_quantity_unlim,
                    "item_quantity_param"    => $item_quantity_param,
                    "item_display"           => $item_display,
                    "item_date"              => $item_date2,
                    "item_hour"              => $item_hour,
                    "item_minute"            => $item_minute,
                    "item_meta_title"        => $item_meta_title,
                    "item_meta_keywords"     => $item_meta_keywords,
                    "item_meta_description"  => $item_meta_description,
                    "in_catalog_id"          => $in_catalog_id,
                    "item_catalog_name"      => $item_catalog_name,
                    "in_category_id"         => $in_category_id,
                    "item_category_name"     => $item_category_name,
                    "in_subcategory_id"      => $in_subcategory_id,
                    "item_subcategory_name"  => $item_subcategory_name,
                    "item_position"          => $item_position,
                ];
            }
            if (isset($result->fields['19']) && intval($result->fields['19']) > 0) {
                $catalog_id = intval($result->fields['19']);
                $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['20']));
                $catalog_all_now = ["catalog_id" => $catalog_id, "catalog_name" => $catalog_name];
                if (!isset($catalogs_all) || (isset($catalogs_all) && !in_array($catalog_all_now, $catalogs_all))) {
                    $catalogs_all[] = ["catalog_id" => $catalog_id, "catalog_name" => $catalog_name];
                }
            }
            if (isset($result->fields['21']) && intval($result->fields['21']) > 0) {
                $category_id = intval($result->fields['21']);
                $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['22']));
                $category_all_now = ["category_id" => $category_id, "category_name" => $category_name];
                if (!isset($categories_all) || (isset($categories_all) && !in_array($category_all_now, $categories_all))) {
                    $categories_all[] = [
                        "category_id"   => $category_id,
                        "category_name" => $category_name,
                    ];
                }
            }
            if (isset($result->fields['23']) && intval($result->fields['23']) > 0) {
                $subcategory_id = intval($result->fields['23']);
                $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['24']));
                $subcategories_all[] = [
                    "subcategory_id"   => $subcategory_id,
                    "subcategory_name" => $subcategory_name,
                ];
            }
            $result->MoveNext();
        }
        $this->result['item_all'] = $item_all;
        $additionalParams = $this->getAdditionalParams($item_id);

        if($additionalParams === false && !empty($this->error)) {
            return;
        }

        $this->result['addon_multiselect'] = true;
        $this->result['adminmodules_shop_additional_params'] = $additionalParams;
        $this->result['adminmodules_shop_item_id'] = $item_id;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
        $this->result['adminmodules_shop_subcateg'] = $data_array['subcateg'];
        if (isset($catalogs_all)) {
            $this->result['catalogs_all'] = $catalogs_all;
        }
        if (isset($categories_all)) {
            $this->result['categories_all'] = $categories_all;
        }
        if (isset($subcategories_all)) {
            $this->result['subcategories_all'] = $subcategories_all;
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['include_jquery'] = "yes";
        $this->result['include_jquery_data'] = "dateinput";
    }


    /**
     * @param int $itemId
     *
     * @return bool|array
     */
    private function getAdditionalParams($itemId = 0)
    {
        $itemId = intval($itemId);
        $additionalParams = false;
        $addSqlPart = false;

        if ($itemId > 0) {
            $addSqlPart = ",c.item_id";
        }

        $sql = "SELECT a.id as group_id,a.group_name,a.group_status,b.id as param_id,b.param_value,b.param_status" . $addSqlPart . " FROM "
               . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " a LEFT OUTER JOIN "
               . PREFIX . "_s_add_params_" . $this->registry->sitelang . " b ON a.id=b.param_group_id ";

        if ($itemId > 0) {
            $sql .= "LEFT OUTER JOIN " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " c ON b.id=c.item_param_id and c.item_id='" . $itemId . "' ";
        }

        $sql .= "ORDER BY a.id,b.param_value ASC";

        $result = $this->db->Execute($sql);

        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = ($itemId > 0 ) ? 'edit_item_sql_error' : 'add_item_sql_error';
            $this->error_array = $error;

            return false;
        }

        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }

        if ($row_exist > 0) {
            while (!$result->EOF) {
                $groupId = intval($result->fields['0']);
                $groupName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $groupStatus = intval($result->fields['2']);
                $paramId = intval($result->fields['3']);
                $paramValue = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $paramStatus = intval($result->fields['5']);
                $paramSelected = 0;

                if ($itemId > 0 && isset($result->fields['6']) && intval($result->fields['6']) > 0) {
                    $paramSelected = 1;
                }

                $additionalParams['groups'][$groupId] = [
                    'group_id'     => $groupId,
                    'group_name'   => $groupName,
                    'group_status' => $groupStatus,
                ];

                if ($paramId > 0) {
                    $additionalParams['params'][$groupId][$paramId] = [
                        'param_id'       => $paramId,
                        'param_value'    => $paramValue,
                        'param_status'   => $paramStatus,
                        'param_selected' => $paramSelected,
                    ];
                }

                $result->MoveNext();
            }
        }

        return $additionalParams;
    }
    
    
    private function setItemAdditionalParams($itemId, $additionalParams, $type = 'add')
    {
        $sql = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " WHERE item_id='" . $itemId . "'";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = $type . '_item_sql_error';
            $this->error_array = $error;

            return false;
        }
        
        if (!empty($additionalParams) && is_array($additionalParams)) {
            foreach($additionalParams as $key => $value) {
                $sql = "INSERT INTO " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " (item_id,item_param_id) VALUES ('" . $itemId . "','" . $value . "')";
                $result = $this->db->Execute($sql);
                
                if (!$result) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                    $this->error = $type . '_item_sql_error';
                    $this->error_array = $error;

                    return false;
                }
            }
        }
        
        return true;
    }


    public function shop_editi_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
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
            ];
            $keys2 = [
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
                'depth',
            ];
            $keys3 = [
                'item_additional_params',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
                if (in_array($key, $keys3) && is_array($array[$key])) {
                    foreach ($array[$key] as $param_key => $param_value) {
                        $data_array[$key][intval($param_key)] = intval($param_value);
                    }
                }
            }
        }
        if (isset($data_array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($data_array['item_price']);
        }
        if (isset($data_array['item_price_discounted'])) {
            $data_array['item_price_discounted'] = $this->registry->main_class->float_to_db($data_array['item_price_discounted']);
        }
        if (!isset($data_array['item_hour'])) {
            $data_array['item_hour'] = 0;
        }
        if (!isset($data_array['item_minute'])) {
            $data_array['item_minute'] = 0;
        }
        if(!isset($data_array['item_additional_params'])) {
            $data_array['item_additional_params'] = false;
        }
        if (isset($data_array['depth']) && $data_array['depth'] == 1) {
            $cache = "cati";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 2) {
            $cache = "categi";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 3) {
            $cache = "subcategi";
        } else {
            $cache = "homei";
        }
        if ((!isset($array['item_id']) || !isset($array['item_action_img']) || !isset($array['item_name']) || !isset($array['item_price'])
             || !isset($array['item_price_discounted'])
             || !isset($array['item_home'])
             || !isset($array['item_catalog'])
             || !isset($array['item_category'])
             || !isset($array['item_subcategory'])
             || !isset($array['item_short_description'])
             || !isset($array['item_description'])
             || !isset($array['item_position'])
             || !isset($array['item_show'])
             || !isset($array['item_quantity'])
             || !isset($array['item_quantity_unlim'])
             || !isset($array['item_quantity_param'])
             || !isset($array['item_display'])
             || !isset($array['item_display_old'])
             || !isset($array['item_date'])
             || !isset($array['item_hour'])
             || !isset($array['item_minute'])
             || !isset($array['item_meta_title'])
             || !isset($array['item_meta_keywords'])
             || !isset($array['item_meta_description']))
            || ($data_array['item_id'] == 0 || $data_array['item_name'] == "" || $data_array['item_short_description'] == "" || $data_array['item_description'] == ""
                || $data_array['item_date'] == ""
                || ($cache == "cati"
                    && (!isset($data_array['item_catalog']) || !isset($data_array['item_catalog_old']) || $data_array['item_catalog'] == 0
                        || $data_array['item_catalog_old'] == 0))
                || ($cache == "categi"
                    && (!isset($data_array['item_category']) || !isset($data_array['item_category_old']) || !isset($data_array['item_catalog_old'])
                        || $data_array['item_category'] == 0
                        || $data_array['item_category_old'] == 0
                        || $data_array['item_catalog_old'] == 0))
                || ($cache == "subcategi"
                    && (!isset($data_array['item_subcategory']) || !isset($data_array['item_subcategory_old'])
                        || !isset($data_array['item_category_old'])
                        || !isset($data_array['item_catalog_old'])
                        || $data_array['item_subcategory'] == 0
                        || $data_array['item_subcategory_old'] == 0
                        || $data_array['item_category_old'] == 0
                        || $data_array['item_catalog_old'] == 0)))
        ) {
            $this->error = 'edit_item_not_all_data';

            return;
        }
        $data_array['item_action_img'] = $this->registry->main_class->format_striptags($data_array['item_action_img']);
        require_once('../modules/shop/shop_config.inc');
        if ($data_array['item_action_img'] == 'del') {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            if (@unlink("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $data_array['item_img'])
                || !is_file(
                    "../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $data_array['item_img']
                )
            ) {
                $data_array['item_img'] = 'NULL';
            } else {
                $this->error = 'edit_item_del_img';

                return;
            }
        } elseif ($data_array['item_action_img'] == 'new_file') {
            if (isset($_FILES['item_img']['name']) && trim($_FILES['item_img']['name']) !== '') {
                if (is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if ($_FILES['item_img']['size'] > SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE) {
                        $this->error = 'edit_item_img_max_size';

                        return;
                    }
                    if (($_FILES['item_img']['type'] == "image/gif") || ($_FILES['item_img']['type'] == "image/jpg") || ($_FILES['item_img']['type'] == "image/jpeg")
                        || ($_FILES['item_img']['type'] == "image/pjpeg")
                        || ($_FILES['item_img']['type'] == "image/png")
                        || ($_FILES['item_img']['type'] == "image/bmp")
                    ) {
                        $this->registry->main_class->set_locale();
                        if (basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $data_array['item_img'] = date("dmY", time()) . "_" . basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $data_array['item_img'] = "";
                        }
                        if (file_exists($data_array['item_img']) && $data_array['item_img'] != "") {
                            @unlink("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $data_array['item_img']);
                        }
                        if ($data_array['item_img'] == ""
                            || !@move_uploaded_file(
                                $_FILES['item_img']['tmp_name'], "../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $data_array['item_img']
                            )
                        ) {
                            $this->error = 'edit_item_img_not_upload';

                            return;
                        }
                        $item_size2 = @getimagesize("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $data_array['item_img']);
                        if (($item_size2[0] > SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH) || ($item_size2[1] > SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT)) {
                            @unlink("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $data_array['item_img']);
                            $this->error = 'edit_item_img_not_eq';

                            return;
                        }
                    } else {
                        $this->error = 'edit_item_img_not_eq_type';

                        return;
                    }
                } else {
                    $this->error = 'edit_item_img_upload';

                    return;
                }
                $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img'], 'need');
            } else {
                $data_array['item_img'] = 'NULL';
            }
        } else {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img']);
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        if (intval($data_array['item_price_discounted']) == 0) {
            $data_array['item_price_discounted'] = 'NULL';
        } else {
            $data_array['item_price_discounted'] = "'" . $data_array['item_price_discounted'] . "'";
        }
        if ($cache == "homei") {
            if ($data_array['item_catalog'] > 0) {
                $item_catalog_id = "'" . $data_array['item_catalog'] . "'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position']
                          . " and item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
                $where2 = "item_catalog_id = " . $data_array['item_catalog'];
            } elseif ($data_array['item_category'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = "'" . $data_array['item_category'] . "'";
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position']
                          . " and item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
                $where2 = "item_category_id = " . $data_array['item_category'];
            } elseif ($data_array['item_subcategory'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'" . $data_array['item_subcategory'] . "'";
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position']
                          . " and item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
                $where2 = "item_subcategory_id = " . $data_array['item_subcategory'];
            } else {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
            }
        } elseif ($cache == "cati") {
            if ($data_array['item_home'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_catalog_id = "
                          . $data_array['item_catalog_old'];
                $where2 = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
            } elseif ($data_array['item_category'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = "'" . $data_array['item_category'] . "'";
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_catalog_id = "
                          . $data_array['item_catalog_old'];
                $where2 = "item_category_id = " . $data_array['item_category'];
            } elseif ($data_array['item_subcategory'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'" . $data_array['item_subcategory'] . "'";
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_catalog_id = "
                          . $data_array['item_catalog_old'];
                $where2 = "item_subcategory_id = " . $data_array['item_subcategory'];
            } else {
                $item_catalog_id = "'" . $data_array['item_catalog'] . "'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                if ($data_array['item_catalog'] != $data_array['item_catalog_old']) {
                    $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_catalog_id = " . $data_array['item_catalog_old'];
                    $where2 = "item_catalog_id = " . $data_array['item_catalog'];
                }
            }
        } elseif ($cache == "categi") {
            if ($data_array['item_home'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_category_id = "
                          . $data_array['item_category_old'];
                $where2 = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
            } elseif ($data_array['item_catalog'] > 0) {
                $item_catalog_id = "'" . $data_array['item_catalog'] . "'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_category_id = "
                          . $data_array['item_category_old'];
                $where2 = "item_catalog_id = " . $data_array['item_catalog'];
            } elseif ($data_array['item_subcategory'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'" . $data_array['item_subcategory'] . "'";
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_category_id = "
                          . $data_array['item_category_old'];
                $where2 = "item_subcategory_id = " . $data_array['item_subcategory'];
            } else {
                $item_catalog_id = 'NULL';
                $item_category_id = "'" . $data_array['item_category'] . "'";
                $item_subcategory_id = 'NULL';
                if ($data_array['item_category'] != $data_array['item_category_old']) {
                    $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_category_id = " . $data_array['item_category_old'];
                    $where2 = "item_category_id = " . $data_array['item_category'];
                }
            }
        } elseif ($cache == "subcategi") {
            if ($data_array['item_home'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_subcategory_id = "
                          . $data_array['item_subcategory_old'];
                $where2 = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
            } elseif ($data_array['item_catalog'] > 0) {
                $item_catalog_id = "'" . $data_array['item_catalog'] . "'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_subcategory_id = "
                          . $data_array['item_subcategory_old'];
                $where2 = "item_catalog_id = " . $data_array['item_catalog'];
            } elseif ($data_array['item_category'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = "'" . $data_array['item_category'] . "'";
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_subcategory_id = "
                          . $data_array['item_subcategory_old'];
                $where2 = "item_category_id = " . $data_array['item_category'];
            } else {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'" . $data_array['item_subcategory'] . "'";
                if ($data_array['item_subcategory'] != $data_array['item_subcategory_old']) {
                    $where1 = "item_position > 0 and item_position > " . $data_array['item_position'] . " and item_subcategory_id = "
                              . $data_array['item_subcategory_old'];
                    $where2 = "item_subcategory_id = " . $data_array['item_subcategory'];
                }
            }
        }
        $data_array['item_meta_title'] = $this->registry->main_class->format_striptags($data_array['item_meta_title']);
        $data_array['item_meta_keywords'] = $this->registry->main_class->format_striptags($data_array['item_meta_keywords']);
        $data_array['item_meta_description'] = $this->registry->main_class->format_striptags($data_array['item_meta_description']);
        $keys = [
            'item_short_description',
            'item_description',
            'item_meta_title',
            'item_meta_keywords',
            'item_meta_description',
        ];
        $keys2 = [
            'item_quantity',
            'item_quantity_param',
            'item_display',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                if ($value != "") {
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                } else {
                    $data_array[$key] = 'NULL';
                }
            }
            if (in_array($key, $keys2)) {
                if ($value == 0) {
                    $data_array[$key] = 'NULL';
                } else {
                    $data_array[$key] = "'" . $value . "'";
                }
            }
        }
        $data_array['item_date'] = $this->registry->main_class->format_striptags($data_array['item_date']);
        $data_array['item_date'] = $data_array['item_date'] . " " . sprintf("%02d", $data_array['item_hour']) . ":" . sprintf("%02d", $data_array['item_minute']);
        $data_array['item_date'] = $this->registry->main_class->time_to_unix($data_array['item_date']);
        $this->db->StartTrans();
        if (isset($where1) && isset($where2)) {
            $result4 = $this->db->Execute("UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_position=item_position-1 WHERE " . $where1);
            if ($result4 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql2 = "SELECT max(item_position)+1 FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE " . $where2;
            $result5 = $this->db->Execute($sql2);
            if ($result5) {
                if (isset($result5->fields['0'])) {
                    $data_array['item_position'] = intval($result5->fields['0']);
                } else {
                    $data_array['item_position'] = 1;
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $sql = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_img = " . $data_array['item_img'] . ",item_name = "
                   . $data_array['item_name'] . ",item_price = '" . $data_array['item_price'] . "',item_price_discounted = " . $data_array['item_price_discounted']
                   . ",item_catalog_id = " . $item_catalog_id . ",item_category_id = " . $item_category_id . ",item_subcategory_id = " . $item_subcategory_id
                   . ",item_short_description = empty_clob(),item_description = empty_clob(),item_position = '" . $data_array['item_position'] . "',item_show = '"
                   . $data_array['item_show'] . "',item_quantity = " . $data_array['item_quantity'] . ",item_quantity_unlim = '" . $data_array['item_quantity_unlim']
                   . "',item_quantity_param = " . $data_array['item_quantity_param'] . ",item_display = " . $data_array['item_display'] . ",item_date = '"
                   . $data_array['item_date'] . "',item_meta_title = " . $data_array['item_meta_title'] . ",item_meta_keywords = " . $data_array['item_meta_keywords']
                   . ",item_meta_description = " . $data_array['item_meta_description'] . " WHERE item_id = " . $data_array['item_id'];
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $data_array['item_short_description'] = substr(substr($data_array['item_short_description'], 0, -1), 1);
            $result2 = $this->db->UpdateClob(
                PREFIX . '_items_' . $this->registry->sitelang, 'item_short_description', $data_array['item_short_description'], 'item_id=' . $data_array['item_id']
            );
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $data_array['item_description'] = substr(substr($data_array['item_description'], 0, -1), 1);
            $result3 = $this->db->UpdateClob(
                PREFIX . '_items_' . $this->registry->sitelang, 'item_description', $data_array['item_description'], 'item_id=' . $data_array['item_id']
            );
            if ($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result2 === false || $result3 === false || (isset($result4) && $result4 === false) || (isset($result5) && $result5 === false)) {
                $result = false;
            }
        } else {
            $sql = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_img = " . $data_array['item_img'] . ",item_name = "
                   . $data_array['item_name'] . ",item_price = '" . $data_array['item_price'] . "',item_price_discounted = " . $data_array['item_price_discounted']
                   . ",item_catalog_id = " . $item_catalog_id . ",item_category_id = " . $item_category_id . ",item_subcategory_id = " . $item_subcategory_id
                   . ",item_short_description = " . $data_array['item_short_description'] . ",item_description = " . $data_array['item_description']
                   . ",item_position = '"
                   . $data_array['item_position'] . "',item_show = '" . $data_array['item_show'] . "',item_quantity = " . $data_array['item_quantity']
                   . ",item_quantity_unlim = '" . $data_array['item_quantity_unlim'] . "',item_quantity_param = " . $data_array['item_quantity_param']
                   . ",item_display = "
                   . $data_array['item_display'] . ",item_date = '" . $data_array['item_date'] . "',item_meta_title = " . $data_array['item_meta_title']
                   . ",item_meta_keywords = " . $data_array['item_meta_keywords'] . ",item_meta_description = " . $data_array['item_meta_description']
                   . " WHERE item_id = "
                   . $data_array['item_id'];
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ((isset($result4) && $result4 === false) || (isset($result5) && $result5 === false)) {
                $result = false;
            }
        }
        
        if ($result && isset($data_array['item_additional_params'])) {
            $result = $this->setItemAdditionalParams($data_array['item_id'], $data_array['item_additional_params'], 'edit');
            
            if ($result) {
                $num_result = 1;
            }
        }
        
        $this->db->CompleteTrans();
        if ($result === false) {
            $this->error = 'edit_item_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_item_ok';

            return;
        } else {
            if ($cache == "homei") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home|items");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|home|item" . $data_array['item_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
                if ($data_array['item_catalog'] > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|items");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_category'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_category'], 2);
                    if (isset($data_array['item_catalog']['0'])) {
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                                  . "|items"
                        );
                        $this->registry->main_class->clearCache(
                            null,
                            $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                    }
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_subcategory'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_subcategory'], 3);
                    if (isset($data_array['item_catalog']['0']) && isset($data_array['item_catalog']['1'])) {
                        $data_array['item_category'] = $data_array['item_catalog']['0'];
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory'] . "|items"
                        );
                        $this->registry->main_class->clearCache(
                            null,
                            $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                            . "|subcateg" . $data_array['item_subcategory']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory']
                        );
                    }
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
            } elseif ($cache == "cati") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog_old'] . "|items");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|items");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                if ($data_array['item_display'] == "'3'" || $data_array['item_display_old'] == 3) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                }
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog_old'] . "|item" . $data_array['item_id']
                );
                if ($data_array['item_home'] > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home|items");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_category'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_category'], 2);
                    if (isset($data_array['item_catalog']['0'])) {
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                                  . "|items"
                        );
                        $this->registry->main_class->clearCache(
                            null,
                            $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                    }
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_subcategory'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_subcategory'], 3);
                    if (isset($data_array['item_catalog']['0']) && isset($data_array['item_catalog']['1'])) {
                        $data_array['item_category'] = $data_array['item_catalog']['0'];
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory'] . "|items"
                        );
                        $this->registry->main_class->clearCache(
                            null,
                            $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                            . "|subcateg" . $data_array['item_subcategory']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory']
                        );
                    }
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
            } elseif ($cache == "categi") {
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old'] . "|items"
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category'] . "|items"
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                          . "|item" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category']
                );
                if ($data_array['item_display'] == "'3'" || $data_array['item_display_old'] == 3) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old']);
                } elseif ($data_array['item_display'] == "'2'" || $data_array['item_display_old'] == 2) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old']);
                }
                if ($data_array['item_home'] > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home|items");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_catalog'] > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|items");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_subcategory'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_subcategory'], 3);
                    if (isset($data_array['item_catalog']['0']) && isset($data_array['item_catalog']['1'])) {
                        $data_array['item_category'] = $data_array['item_catalog']['0'];
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory'] . "|items"
                        );
                        $this->registry->main_class->clearCache(
                            null,
                            $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                            . "|subcateg" . $data_array['item_subcategory']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']['1'] . "|categ" . $data_array['item_category']
                                  . "|subcateg" . $data_array['item_subcategory']
                        );
                    }
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
            } elseif ($cache == "subcategi") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                          . "|subcateg" . $data_array['item_subcategory_old'] . "|items"
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                          . "|subcateg" . $data_array['item_subcategory'] . "|items"
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                    . "|subcateg" . $data_array['item_subcategory_old']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                    . "|subcateg" . $data_array['item_subcategory']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                          . "|subcateg" . $data_array['item_subcategory_old'] . "|item" . $data_array['item_id']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old'] . "|subcateg"
                    . $data_array['item_subcategory_old']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old'] . "|subcateg"
                    . $data_array['item_subcategory_old']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old'] . "|subcateg"
                    . $data_array['item_subcategory']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old'] . "|subcateg"
                    . $data_array['item_subcategory']
                );
                if ($data_array['item_display'] == "'3'" || $data_array['item_display_old'] == 3) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old']);
                } elseif ($data_array['item_display'] == "'2'" || $data_array['item_display_old'] == 2) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old']);
                } elseif ($data_array['item_display'] == "'1'" || $data_array['item_display_old'] == 1) {
                    $this->registry->main_class->clearCache(
                        null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                    );
                    $this->registry->main_class->clearCache(
                        null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog_old'] . "|categ" . $data_array['item_category_old']
                    );
                }
                if ($data_array['item_home'] > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home|items");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_catalog'] > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|items");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                } elseif ($data_array['item_category'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_category'], 2);
                    if (isset($data_array['item_catalog']['0'])) {
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                                  . "|items"
                        );
                        $this->registry->main_class->clearCache(
                            null,
                            $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                        $this->registry->main_class->clearCache(
                            null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']['0'] . "|categ" . $data_array['item_category']
                        );
                    }
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
                }
            }
            $this->result = 'edit_item_done';
        }
    }


    private function shop_getdepthinfo($shop_depth_id, $depth = 0)
    {
        $shop_depth_id = intval($shop_depth_id);
        $depth = intval($depth);
        if ($shop_depth_id != 0 && $depth != 0 && ($depth == 2 || $depth == 3)) {
            if ($depth == 2) {
                $sql = "SELECT cat_catalog_id FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " WHERE category_id=" . $shop_depth_id;
            } elseif ($depth == 3) {
                $sql = "SELECT b.subcategory_cat_id,a.cat_catalog_id FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " b," . PREFIX . "_categories_"
                       . $this->registry->sitelang . " a WHERE b.subcategory_cat_id=a.category_id and b.subcategory_id=" . $shop_depth_id;
            }
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
                    $depthinfo = $result->fields;
                }
            }
        }
        if (isset($depthinfo)) {
            return $depthinfo;
        } else {
            return 0;
        }
    }


    public function shop_add_item($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
                'subcateg',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['subcateg']) && isset($data_array['categ']) && $data_array['categ'] != 0) {
                $cache = "subcategi";
                if ($data_array['subcateg'] != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif (isset($data_array['categ'])) {
                $cache = "categi";
                if ($data_array['categ'] != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
                $data_array['subcateg'] = 0;
            } else {
                $cache = "cati";
                $check = 1;
                $data_array['categ'] = 0;
                $data_array['subcateg'] = 0;
            }
        } elseif (isset($data_array['cat']) && $data_array['cat'] == 0) {
            $cache = "cati";
            $check = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
        } else {
            $cache = "homei";
            $check = 1;
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
        }
        if ($check == 0) {
            if ($cache == "cati") {
                $this->error = 'add_item_no_cat';
            } elseif ($cache == "categi") {
                $this->error = 'add_item_no_categ';
            } elseif ($cache == "subcategi") {
                $this->error = 'add_item_no_subcateg';
            }

            return;
        }
        if ($cache == "cati" || $cache == "categi" || $cache == "subcategi") {
            if ($cache == "cati") {
                $sql = "SELECT a.catalog_id,a.catalog_name FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " a order by a.catalog_name ASC";
            } elseif ($cache == "categi") {
                $sql = "SELECT a.catalog_id,a.catalog_name,b.category_id,b.category_name FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " a," . PREFIX
                       . "_categories_" . $this->registry->sitelang . " b WHERE a.catalog_id=b.cat_catalog_id and a.catalog_id = " . $data_array['cat']
                       . " order by b.category_name ASC";
            } elseif ($cache == "subcategi") {
                $sql = "SELECT a.catalog_id,a.catalog_name,b.category_id,b.category_name,c.subcategory_id,c.subcategory_name FROM " . PREFIX . "_catalogs_"
                       . $this->registry->sitelang . " a," . PREFIX . "_categories_" . $this->registry->sitelang . " b," . PREFIX . "_subcategories_"
                       . $this->registry->sitelang . " c WHERE a.catalog_id=b.cat_catalog_id and a.catalog_id = " . $data_array['cat']
                       . " and b.category_id=c.subcategory_cat_id and b.category_id = " . $data_array['categ'] . " order by c.subcategory_name ASC";
            }
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'add_item_sql_error';
                $this->error_array = $error;

                return;
            }
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist < 1) {
                if ($cache == "cati") {
                    $this->error = 'add_item_not_found_catalog';
                } elseif ($cache == "categi") {
                    $this->error = 'add_item_not_found_categ';
                } elseif ($cache == "subcategi") {
                    $this->error = 'add_item_not_found_subcateg';
                }

                return;
            }
            while (!$result->EOF) {
                if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
                    $catalog_id = intval($result->fields['0']);
                    $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $catalog_all_now = ["catalog_id" => $catalog_id, "catalog_name" => $catalog_name];
                    if (!isset($catalogs_all) || (isset($catalogs_all) && !in_array($catalog_all_now, $catalogs_all))) {
                        $catalogs_all[] = [
                            "catalog_id"   => $catalog_id,
                            "catalog_name" => $catalog_name,
                        ];
                    }
                    if ($catalog_id == $data_array['cat']) {
                        $in_catalog_name = $catalog_name;
                    }
                }
                if (($cache == "categi" || $cache == "subcategi") && isset($result->fields['2']) && intval($result->fields['2']) > 0) {
                    $category_id = intval($result->fields['2']);
                    $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $category_all_now = [
                        "category_id"   => $category_id,
                        "category_name" => $category_name,
                    ];
                    if (!isset($categories_all) || (isset($categories_all) && !in_array($category_all_now, $categories_all))) {
                        $categories_all[] = [
                            "category_id"   => $category_id,
                            "category_name" => $category_name,
                        ];
                    }
                    if ($category_id == $data_array['categ']) {
                        $in_category_name = $category_name;
                    }
                }
                if ($cache == "subcategi" && isset($result->fields['4']) && intval($result->fields['4']) > 0) {
                    $subcategory_id = intval($result->fields['4']);
                    $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    $subcategories_all[] = [
                        "subcategory_id"   => $subcategory_id,
                        "subcategory_name" => $subcategory_name,
                    ];
                    if ($subcategory_id == $data_array['subcateg']) {
                        $in_subcategory_name = $subcategory_name;
                    }
                }
                $result->MoveNext();
            }
            if ((!isset($in_catalog_name) && $cache == "cati") || (!isset($in_category_name) && $cache == "categi")
                || (!isset($in_subcategory_name)
                    && $cache == "subcategi")
            ) {
                if ($cache == "cati") {
                    $this->error = 'add_item_not_found_catalog';
                } elseif ($cache == "categi") {
                    $this->error = 'add_item_not_found_categ';
                } elseif ($cache == "subcategi") {
                    $this->error = 'add_item_not_found_subcateg';
                }

                return;
            }
            $this->result['adminmodules_shop_cat'] = $data_array['cat'];
            $this->result['adminmodules_shop_categ'] = $data_array['categ'];
            $this->result['adminmodules_shop_subcateg'] = $data_array['subcateg'];
            $this->result['adminmodules_shop_cat_name'] = $in_catalog_name;
            if (isset($in_category_name)) {
                $this->result['adminmodules_shop_categ_name'] = $in_category_name;
            }
            if (isset($in_subcategory_name)) {
                $this->result['adminmodules_shop_subcateg_name'] = $in_subcategory_name;
            }
            if (isset($catalogs_all)) {
                $this->result['catalogs_all'] = $catalogs_all;
            }
            if (isset($categories_all)) {
                $this->result['categories_all'] = $categories_all;
            }
            if (isset($subcategories_all)) {
                $this->result['subcategories_all'] = $subcategories_all;
            }
        }
        $additionalParams = $this->getAdditionalParams();

        if($additionalParams === false && !empty($this->error)) {
            return;
        }

        $this->result['addon_multiselect'] = true;
        $this->result['adminmodules_shop_additional_params'] = $additionalParams;
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['include_jquery'] = "yes";
        $this->result['include_jquery_data'] = "dateinput";
    }


    // TODO Check oracle insert id and refactor all models where using $check_db_need_lobs
    public function shop_addi_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
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
            ];
            $keys2 = [
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
                'depth',
            ];
            $keys3 = [
                'item_additional_params',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
                if (in_array($key, $keys3) && is_array($array[$key])) {
                    foreach ($array[$key] as $param_key => $param_value) {
                        $data_array[$key][intval($param_key)] = intval($param_value);
                    }
                }
            }
        }
        if (isset($array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($array['item_price']);
        }
        if (isset($array['item_price_discounted'])) {
            $data_array['item_price_discounted'] = $this->registry->main_class->float_to_db($array['item_price_discounted']);
        }
        if (!isset($array['item_hour'])) {
            $data_array['item_hour'] = 0;
        }
        if (!isset($array['item_minute'])) {
            $data_array['item_minute'] = 0;
        }
        if(!isset($data_array['item_additional_params'])) {
            $data_array['item_additional_params'] = false;
        }
        if (isset($data_array['depth']) && $data_array['depth'] == 1) {
            $cache = "cati";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 2) {
            $cache = "categi";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 3) {
            $cache = "subcategi";
        } else {
            $cache = "homei";
        }
        if ((!isset($array['item_action_img']) || !isset($array['item_name']) || !isset($array['item_price']) || !isset($array['item_price_discounted'])
             || (!isset($array['item_home']) && $cache == "homei")
             || (!isset($array['item_catalog']) && $cache == "cati")
             || ((!isset($array['item_catalog']) || !isset($array['item_category'])) && $cache == "categi")
             || ((!isset($array['item_catalog']) || !isset($array['item_category']) || !isset($array['item_subcategory'])) && $cache == "subcategi")
             || !isset($array['item_short_description'])
             || !isset($array['item_description'])
             || !isset($array['item_show'])
             || !isset($array['item_quantity'])
             || !isset($array['item_quantity_unlim'])
             || !isset($array['item_quantity_param'])
             || !isset($array['item_display'])
             || !isset($array['item_date'])
             || !isset($array['item_hour'])
             || !isset($array['item_minute'])
             || !isset($array['item_meta_title'])
             || !isset($array['item_meta_keywords'])
             || !isset($array['item_meta_description']))
            || ($data_array['item_name'] == "" || $data_array['item_short_description'] == "" || $data_array['item_description'] == "" || $data_array['item_date'] == ""
                || ($cache == "homei" && $data_array['item_home'] == 0)
                || ($cache == "cati" && (!isset($data_array['item_catalog']) || $data_array['item_catalog'] == 0))
                || ($cache == "categi"
                    && (!isset($data_array['item_category']) || !isset($data_array['item_catalog']) || $data_array['item_category'] == 0
                        || $data_array['item_catalog'] == 0))
                || ($cache == "subcategi"
                    && (!isset($data_array['item_subcategory']) || !isset($data_array['item_category']) || !isset($data_array['item_catalog'])
                        || $data_array['item_subcategory'] == 0
                        || $data_array['item_category'] == 0
                        || $data_array['item_catalog'] == 0)))
        ) {
            $this->error = 'add_item_not_all_data';

            return;
        }
        $data_array['item_action_img'] = $this->registry->main_class->format_striptags($data_array['item_action_img']);
        require_once('../modules/shop/shop_config.inc');
        if ($data_array['item_action_img'] == 'new_file') {
            if (isset($_FILES['item_img']['name']) && trim($_FILES['item_img']['name']) !== '') {
                if (is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if ($_FILES['item_img']['size'] > SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE) {
                        $this->error = 'add_item_img_max_size';

                        return;
                    }
                    if (($_FILES['item_img']['type'] == "image/gif") || ($_FILES['item_img']['type'] == "image/jpg") || ($_FILES['item_img']['type'] == "image/jpeg")
                        || ($_FILES['item_img']['type'] == "image/pjpeg")
                        || ($_FILES['item_img']['type'] == "image/png")
                        || ($_FILES['item_img']['type'] == "image/bmp")
                    ) {
                        $this->registry->main_class->set_locale();
                        if (basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $item_img = date("dmY", time()) . "_" . basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $item_img = "";
                        }
                        if (file_exists($item_img) && $item_img != "") {
                            @unlink("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $item_img);
                        }
                        if ($item_img == "" || !@move_uploaded_file($_FILES['item_img']['tmp_name'], "../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $item_img)) {
                            $this->error = 'add_item_img_not_upload';

                            return;
                        }
                        $item_size2 = @getimagesize("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $item_img);
                        if (($item_size2[0] > SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH) || ($item_size2[1] > SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT)) {
                            @unlink("../" . SYSTEMDK_SHOP_ITEMIMAGE_PATH . "/" . $item_img);
                            $this->error = 'add_item_img_not_eq';

                            return;
                        }
                    } else {
                        $this->error = 'add_item_img_not_eq_type';

                        return;
                    }
                } else {
                    $this->error = 'add_item_img_upload';

                    return;
                }
                $item_img = $this->registry->main_class->processing_data($item_img, 'need');
            } else {
                $item_img = 'NULL';
            }
        } else {
            $item_img = 'NULL';
        }
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_meta_title'] = $this->registry->main_class->format_striptags($data_array['item_meta_title']);
        $data_array['item_meta_keywords'] = $this->registry->main_class->format_striptags($data_array['item_meta_keywords']);
        $data_array['item_meta_description'] = $this->registry->main_class->format_striptags($data_array['item_meta_description']);
        if (intval($data_array['item_price_discounted']) == 0) {
            $data_array['item_price_discounted'] = 'NULL';
        } else {
            $data_array['item_price_discounted'] = "'" . $data_array['item_price_discounted'] . "'";
        }
        $keys = [
            'item_short_description',
            'item_description',
            'item_meta_title',
            'item_meta_keywords',
            'item_meta_description',
        ];
        $keys2 = [
            'item_quantity',
            'item_quantity_param',
            'item_display',
        ];
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                if ($value != "") {
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                } else {
                    $data_array[$key] = 'NULL';
                }
            }
            if (in_array($key, $keys2)) {
                if ($value == 0) {
                    $data_array[$key] = 'NULL';
                } else {
                    $data_array[$key] = "'" . $value . "'";
                }
            }
        }
        if ($cache == "homei") {
            $item_catalog_id = 'NULL';
            $item_category_id = 'NULL';
            $item_subcategory_id = 'NULL';
        } elseif ($cache == "cati") {
            $item_catalog_id = "'" . $data_array['item_catalog'] . "'";
            $item_category_id = 'NULL';
            $item_subcategory_id = 'NULL';
        } elseif ($cache == "categi") {
            $item_catalog_id = 'NULL';
            $item_category_id = "'" . $data_array['item_category'] . "'";
            $item_subcategory_id = 'NULL';
        } elseif ($cache == "subcategi") {
            $item_catalog_id = 'NULL';
            $item_category_id = 'NULL';
            $item_subcategory_id = "'" . $data_array['item_subcategory'] . "'";
        }
        $data_array['item_date'] = $this->registry->main_class->format_striptags($data_array['item_date']);
        $data_array['item_date'] = $data_array['item_date'] . " " . sprintf("%02d", $data_array['item_hour']) . ":" . sprintf("%02d", $data_array['item_minute']);
        $data_array['item_date'] = $this->registry->main_class->time_to_unix($data_array['item_date']);
        if ($cache == "homei") {
            $where = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
        } elseif ($cache == "cati") {
            $where = "item_catalog_id = " . $item_catalog_id;
        } elseif ($cache == "categi") {
            $where = "item_category_id = " . $item_category_id;
        } elseif ($cache == "subcategi") {
            $where = "item_subcategory_id = " . $item_subcategory_id;
        }
        $sql = "SELECT max(item_position)+1 FROM " . PREFIX . "_items_" . $this->registry->sitelang . " WHERE " . $where;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $item_position = intval($result->fields['0']);
            }
        }
        if (!isset($item_position)) {
            $item_position = 1;
        }
        $this->db->StartTrans();
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_items_id_" . $this->registry->sitelang, 'item_id');
        $itemId = $sequence_array['sequence_value_string'];
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $sql = "INSERT INTO " . PREFIX . "_items_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "item_img,item_name,item_price,item_price_discounted,item_catalog_id,item_category_id,item_subcategory_id,item_short_description,item_description,item_position,item_show,item_quantity,item_quantity_unlim,item_quantity_param,item_display,item_date,item_meta_title,item_meta_keywords,item_meta_description) VALUES ("
                   . $sequence_array['sequence_value_string'] . "" . $item_img . "," . $data_array['item_name'] . ",'" . $data_array['item_price'] . "',"
                   . $data_array['item_price_discounted'] . "," . $item_catalog_id . "," . $item_category_id . "," . $item_subcategory_id . ",empty_clob(),empty_clob(),'"
                   . $item_position . "','" . $data_array['item_show'] . "'," . $data_array['item_quantity'] . ",'" . $data_array['item_quantity_unlim'] . "',"
                   . $data_array['item_quantity_param'] . "," . $data_array['item_display'] . ",'" . $data_array['item_date'] . "'," . $data_array['item_meta_title']
                   . ","
                   . $data_array['item_meta_keywords'] . "," . $data_array['item_meta_description'] . ")";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            } else {
                if (empty($itemId)) {
                    $itemId = $this->registry->main_class->db_get_last_insert_id();
                }
            }
            $data_array['item_short_description'] = substr(substr($data_array['item_short_description'], 0, -1), 1);
            $result2 = $this->db->UpdateClob(
                PREFIX . '_items_' . $this->registry->sitelang, 'item_short_description', $data_array['item_short_description'],
                'item_id=' . $sequence_array['sequence_value']
            );
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $data_array['item_description'] = substr(substr($data_array['item_description'], 0, -1), 1);
            $result3 = $this->db->UpdateClob(
                PREFIX . '_items_' . $this->registry->sitelang, 'item_description', $data_array['item_description'], 'item_id=' . $sequence_array['sequence_value']
            );
            if ($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result2 === false || $result3 === false) {
                $result = false;
            }
        } else {
            $sql = "INSERT INTO " . PREFIX . "_items_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "item_img,item_name,item_price,item_price_discounted,item_catalog_id,item_category_id,item_subcategory_id,item_short_description,item_description,item_position,item_show,item_quantity,item_quantity_unlim,item_quantity_param,item_display,item_date,item_meta_title,item_meta_keywords,item_meta_description) VALUES ("
                   . $sequence_array['sequence_value_string'] . "" . $item_img . "," . $data_array['item_name'] . ",'" . $data_array['item_price'] . "',"
                   . $data_array['item_price_discounted'] . "," . $item_catalog_id . "," . $item_category_id . "," . $item_subcategory_id . ","
                   . $data_array['item_short_description'] . "," . $data_array['item_description'] . ",'" . $item_position . "','" . $data_array['item_show'] . "',"
                   . $data_array['item_quantity'] . ",'" . $data_array['item_quantity_unlim'] . "'," . $data_array['item_quantity_param'] . ","
                   . $data_array['item_display']
                   . ",'" . $data_array['item_date'] . "'," . $data_array['item_meta_title'] . "," . $data_array['item_meta_keywords'] . ","
                   . $data_array['item_meta_description'] . ")";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            } else {
                if (empty($itemId)) {
                    $itemId = $this->registry->main_class->db_get_last_insert_id();
                }
            }
        }
        
        if ($result && isset($data_array['item_additional_params'])) {
            $result = $this->setItemAdditionalParams($itemId, $data_array['item_additional_params'], 'add');
        }
        
        $this->db->CompleteTrans();
        
        if ($result === false) {
            $this->error = 'add_item_sql_error';
            $this->error_array = $error;

            return;
        }
        if ($cache == "homei") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|home|items");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
        } elseif ($cache == "cati") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|items");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog']);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
            if ($data_array['item_display'] == "'3'") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            }
        } elseif ($cache == "categi") {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category'] . "|items"
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
            );
            if ($data_array['item_display'] == "'3'") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
            } elseif ($data_array['item_display'] == "'2'") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
            }
        } elseif ($cache == "subcategi") {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category'] . "|subcateg"
                      . $data_array['item_subcategory'] . "|items"
            );
            $this->registry->main_class->clearCache(
                null,
                $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category'] . "|subcateg"
                . $data_array['item_subcategory']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category'] . "|subcateg"
                      . $data_array['item_subcategory']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category'] . "|subcateg"
                      . $data_array['item_subcategory']
            );
            if ($data_array['item_display'] == "'3'") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
            } elseif ($data_array['item_display'] == "'2'") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog']);
            } elseif ($data_array['item_display'] == "'1'") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['item_catalog'] . "|categ" . $data_array['item_category']
                );
            }
        }
        $this->result = 'edit_item_done';
    }


    public function shop_sort($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
                'subcateg',
                'items',
                'num_page',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (!isset($data_array['cat']) && !isset($data_array['categ']) && !isset($data_array['subcateg'])) {
            if (isset($data_array['items']) && $data_array['items'] == 1) {
                $cache = "homei";
            } else {
                $cache = "home";
            }
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
            $itemid = 1;
        } elseif (isset($data_array['cat']) && $data_array['cat'] != 0) {
            if (isset($data_array['subcateg']) && isset($data_array['categ']) && $data_array['categ'] != 0 && isset($data_array['items']) && $data_array['items'] == 1) {
                $cache = "subcategi";
                if ($data_array['subcateg'] != 0) {
                    $itemid = $data_array['subcateg'];
                } else {
                    $itemid = 0;
                }
            } elseif (isset($data_array['categ'])) {
                if (isset($data_array['items']) && $data_array['items'] == 1) {
                    $cache = "categi";
                } else {
                    $cache = "subcateg";
                }
                if ($data_array['categ'] != 0) {
                    $itemid = $data_array['categ'];
                    $data_array['subcateg'] = 0;
                } else {
                    $itemid = 0;
                }
            } else {
                if (isset($data_array['items']) && $data_array['items'] == 1) {
                    $cache = "cati";
                } else {
                    $cache = "categ";
                }
                $itemid = $data_array['cat'];
                $data_array['categ'] = 0;
                $data_array['subcateg'] = 0;
            }
        } else {
            if (isset($data_array['items']) && $data_array['items'] == 1) {
                $cache = "cati";
            } else {
                $cache = "categ";
            }
            $itemid = 0;
        }
        if ($itemid == 0) {
            if ($cache == "categ" || $cache == "cati") {
                $this->error = 'sort_no_cat';
            } elseif ($cache == "subcateg" || $cache == "categi") {
                $this->error = 'sort_no_categ';
            } elseif ($cache == "subcategi") {
                $this->error = 'sort_no_subcateg';
            }

            return;
        }
        if (isset($data_array['num_page']) && intval($data_array['num_page']) != 0) {
            $num_page = intval($data_array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if ($cache == "home") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang . " b";
            $sql = "SELECT a.catalog_id,a.catalog_name,a.catalog_position,a.catalog_show FROM " . PREFIX . "_catalogs_" . $this->registry->sitelang
                   . " a order by a.catalog_position ASC";
        } elseif ($cache == "categ") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_categories_" . $this->registry->sitelang . " b WHERE b.cat_catalog_id = " . $data_array['cat'];
            $sql = "SELECT a.category_id,a.category_name,a.category_position,a.category_show,c.catalog_id,c.catalog_name FROM " . PREFIX . "_categories_"
                   . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                   . " c ON a.cat_catalog_id=c.catalog_id WHERE c.catalog_id = " . $data_array['cat'] . " order by a.category_position ASC";
        } elseif ($cache == "subcateg") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_subcategories_" . $this->registry->sitelang . " b WHERE b.subcategory_cat_id = " . $data_array['categ'];
            $sql = "SELECT a.subcategory_id,a.subcategory_name,a.subcategory_position,a.subcategory_show,c.category_id,c.category_name,d.catalog_id,d.catalog_name FROM "
                   . PREFIX . "_subcategories_" . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                   . " c ON c.category_id=a.subcategory_cat_id RIGHT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                   . " d ON d.catalog_id=c.cat_catalog_id WHERE c.category_id = " . $data_array['categ'] . " and d.catalog_id = " . $data_array['cat']
                   . " order by a.subcategory_position ASC";
        } elseif ($cache == "homei") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang
                    . " b WHERE b.item_catalog_id is NULL and b.item_category_id is NULL and b.item_subcategory_id is NULL";
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show FROM " . PREFIX . "_items_" . $this->registry->sitelang
                   . " a WHERE a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL order by a.item_position ASC";
        } elseif ($cache == "cati") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang . " e," . PREFIX . "_catalogs_" . $this->registry->sitelang
                    . " f WHERE f.catalog_id=e.item_catalog_id and e.item_catalog_id = " . $data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show,b.catalog_id,b.catalog_name FROM " . PREFIX . "_items_" . $this->registry->sitelang
                   . " a RIGHT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang . " b on a.item_catalog_id=b.catalog_id WHERE b.catalog_id = "
                   . $data_array['cat'] . " order by a.item_position ASC";
        } elseif ($cache == "categi") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang . " e," . PREFIX . "_categories_" . $this->registry->sitelang . " f,"
                    . PREFIX . "_catalogs_" . $this->registry->sitelang
                    . " g WHERE f.category_id=e.item_category_id and g.catalog_id=f.cat_catalog_id and e.item_category_id = " . $data_array['categ']
                    . " and g.catalog_id = "
                    . $data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show,b.category_id,b.category_name,c.catalog_id,c.catalog_name FROM " . PREFIX . "_items_"
                   . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                   . " b on a.item_category_id=b.category_id INNER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                   . " c on b.cat_catalog_id=c.catalog_id WHERE b.category_id = " . $data_array['categ'] . " and c.catalog_id = " . $data_array['cat']
                   . " order by a.item_position ASC";
        } elseif ($cache == "subcategi") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_items_" . $this->registry->sitelang . " e," . PREFIX . "_subcategories_" . $this->registry->sitelang . " f,"
                    . PREFIX . "_categories_" . $this->registry->sitelang . " g," . PREFIX . "_catalogs_" . $this->registry->sitelang
                    . " h WHERE f.subcategory_id=e.item_subcategory_id and g.category_id=f.subcategory_cat_id and h.catalog_id=g.cat_catalog_id and e.item_subcategory_id = "
                    . $data_array['subcateg'] . " and g.category_id = " . $data_array['categ'] . " and h.catalog_id = " . $data_array['cat'];
            $sql =
                "SELECT a.item_id,a.item_name,a.item_position,a.item_show,b.subcategory_id,b.subcategory_name,c.category_id,c.category_name,d.catalog_id,d.catalog_name FROM "
                . PREFIX . "_items_" . $this->registry->sitelang . " a RIGHT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
                . " b on a.item_subcategory_id=b.subcategory_id INNER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
                . " c on b.subcategory_cat_id=c.category_id INNER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
                . " d on c.cat_catalog_id=d.catalog_id WHERE b.subcategory_id = " . $data_array['subcateg'] . " and c.category_id = " . $data_array['categ']
                . " and d.catalog_id = " . $data_array['cat'] . " order by a.item_position ASC";
        }
        $result = $this->db->Execute($sql0);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sort_sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if (($cache == "categ" || $cache == "cati") && isset($result->fields['4']) && intval($result->fields['4']) > 0) {
            $catalog_id = intval($result->fields['4']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['5'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
        } elseif (($cache == "subcateg" || $cache == "categi") && isset($result->fields['4']) && intval($result->fields['4']) > 0) {
            $category_id = intval($result->fields['4']);
            $category_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['5'])
            );
            $catalog_id = intval($result->fields['6']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['7'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
        } elseif ($cache == "subcategi" && isset($result->fields['4']) && intval($result->fields['4']) > 0) {
            $subcategory_id = intval($result->fields['4']);
            $subcategory_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['5'])
            );
            $category_id = intval($result->fields['6']);
            $category_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['7'])
            );
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['9'])
            );
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
            $this->result['adminmodules_shop_subcategory_id'] = $subcategory_id;
            $this->result['adminmodules_shop_subcategory_name'] = $subcategory_name;
        } elseif ($cache != "home" && $cache != "homei") {
            if ($cache == "categ" || $cache == "cati") {
                $this->error = 'sort_not_found_catalog';
            } elseif ($cache == "subcateg" || $cache == "categi") {
                $this->error = 'sort_not_found_categ';
            } elseif ($cache == "subcategi") {
                $this->error = 'sort_not_found_subcateg';
            }

            return;
        }
        if ($row_exist > 0) {
            while (!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_position = intval($result->fields['2']);
                $item_show = intval($result->fields['3']);
                $items_all[] = [
                    "item_id"       => $item_id,
                    "item_name"     => $item_name,
                    "item_position" => $item_position,
                    "item_show"     => $item_show,
                ];
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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
                $this->result['totalitems'] = $num_rows;
                $this->result['num_pages'] = $num_pages;
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $this->result['items_all'] = "no";
            $this->result['html'] = "no";
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
        $this->result['adminmodules_shop_subcateg'] = $data_array['subcateg'];
    }


    public function shop_sort_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'cat',
                'categ',
                'subcateg',
                'depth',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['depth']) && $data_array['depth'] == 1) {
            $cache = "categ";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 2) {
            $cache = "subcateg";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 3) {
            $cache = "homei";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 4) {
            $cache = "cati";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 5) {
            $cache = "categi";
        } elseif (isset($data_array['depth']) && $data_array['depth'] == 6) {
            $cache = "subcategi";
        } else {
            $cache = "home";
        }
        if (isset($array['item_id']) && is_array($array['item_id']) && count($array['item_id']) > 0 && isset($array['item_position']) && is_array($array['item_position'])
            && count($array['item_position']) > 0
        ) {
            //$item_id_is_arr = 1;
            $item_id = false;
            for ($i = 0, $size = count($array['item_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $item_id = " WHEN " . intval($array['item_id'][$i]) . " THEN " . intval($array['item_position'][$i]);
                } else {
                    $item_id .= " WHEN " . intval($array['item_id'][$i]) . " THEN " . intval($array['item_position'][$i]);
                }
            }
        } else {
            //$item_id_is_arr = 0;
        }
        if ((!isset($array['item_id']) || !isset($array['item_position']))
            || ((($cache == "cati" || $cache == "categ")
                 && (!isset($data_array['cat'])
                     || $data_array['cat'] == 0))
                || (($cache == "categi" || $cache == "subcateg")
                    && (!isset($data_array['categ']) || !isset($data_array['cat']) || $data_array['categ'] == 0
                        || $data_array['cat'] == 0))
                || ($cache == "subcategi"
                    && (!isset($data_array['subcateg']) || !isset($data_array['categ']) || !isset($data_array['cat'])
                        || $data_array['subcateg'] == 0
                        || $data_array['categ'] == 0
                        || $data_array['cat'] == 0)))
        ) {
            $this->error = 'sort_not_all_data';

            return;
        }
        if ($cache == "home") {
            $sql = "UPDATE " . PREFIX . "_catalogs_" . $this->registry->sitelang . " SET catalog_position = (CASE catalog_id " . $item_id . " ELSE catalog_position END)";
        } elseif ($cache == "categ") {
            $sql = "UPDATE " . PREFIX . "_categories_" . $this->registry->sitelang . " SET category_position = (CASE category_id " . $item_id
                   . " ELSE category_position END)";
        } elseif ($cache == "subcateg") {
            $sql = "UPDATE " . PREFIX . "_subcategories_" . $this->registry->sitelang . " SET subcategory_position = (CASE subcategory_id " . $item_id
                   . " ELSE subcategory_position END)";
        } elseif ($cache == "homei" || $cache == "cati" || $cache == "categi" || $cache == "subcategi") {
            $sql = "UPDATE " . PREFIX . "_items_" . $this->registry->sitelang . " SET item_position = (CASE item_id " . $item_id . " ELSE item_position END)";
        }
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($result === false) {
            $this->error = 'sort_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'sort_ok';

            return;
        }
        require_once('../modules/shop/shop_config.inc');
        if ($cache == "home") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|home");
            if (SYSTEMDK_SHOP_CATALOGS_ORDER == "catalog_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            }
        } elseif ($cache == "categ") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['cat']);
            if (SYSTEMDK_SHOP_CATEGORIES_ORDER == "category_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat']);
                if (SYSTEMDK_SHOP_ITEMS_UNDERPARENT > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
                }
            }
        } elseif ($cache == "subcateg") {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|sort|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
            );
            if (SYSTEMDK_SHOP_SUBCATEGORIES_ORDER == "subcategory_position") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
                );
                if (SYSTEMDK_SHOP_ITEMS_UNDERPARENT > 0) {
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat']);
                    $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat']);
                }
            }
        } elseif ($cache == "homei") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|home");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|home");
            if (SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            }
        } elseif ($cache == "cati") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['cat']);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['cat']);
            if (SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            }
            if (SYSTEMDK_SHOP_CATITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat']);
            }
        } elseif ($cache == "categi") {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
            );
            if (SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            }
            if (SYSTEMDK_SHOP_CATITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat']);
            }
            if (SYSTEMDK_SHOP_CATEGITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
                );
            }
        } elseif ($cache == "subcategi") {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|sorti|cat" . $data_array['cat'] . "|categ" . $data_array['categ'] . "|subcateg"
                      . $data_array['subcateg']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|shop|edit|cat" . $data_array['cat'] . "|categ" . $data_array['categ'] . "|subcateg"
                      . $data_array['subcateg']
            );
            if (SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|home");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|home");
            }
            if (SYSTEMDK_SHOP_CATITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat']);
            }
            if (SYSTEMDK_SHOP_CATEGITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat'] . "|categ" . $data_array['categ']
                );
            }
            if (SYSTEMDK_SHOP_SUBCATEGITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|modules|shop|other|cat" . $data_array['cat'] . "|categ" . $data_array['categ'] . "|subcateg" . $data_array['subcateg']
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|modules|shop|admin|cat" . $data_array['cat'] . "|categ" . $data_array['categ'] . "|subcateg" . $data_array['subcateg']
                );
            }
        }
        $this->result = 'sort_done';
    }


    public function shop_orders($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $status = 0;
        if (isset($array['status'])) {
            $status = intval($array['status']);
        }
        $orders_status = 0;
        if (in_array($status, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9])) {
            $orders_status = $status;
        }
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if ($orders_status == 0) {
            $where = "";
        } else {
            $where = "WHERE order_status = '" . $orders_status . "'";
        }
        $sql = "SELECT count(*) FROM " . PREFIX . "_orders_" . $this->registry->sitelang . " " . $where;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT order_id,order_date,ship_date,order_status,order_customer_id FROM " . PREFIX . "_orders_" . $this->registry->sitelang . " " . $where
                   . " order by order_date DESC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'orders_sql_error';
            $this->error_array = $error;

            return;
        }
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
                $order_id = intval($result->fields['0']);
                $order_date = date("d.m.Y H:i", intval($result->fields['1']));
                if (intval($result->fields['2']) == 0) {
                    $ship_date = "no";
                } else {
                    $ship_date = date("d.m.Y H:i", intval($result->fields['2']));
                }
                $order_status = intval($result->fields['3']);
                $order_customer_id = intval($result->fields['4']);
                if ($order_customer_id == 0) {
                    $order_customer_id = "no";
                }
                $orders_all[] = [
                    "order_id"          => $order_id,
                    "order_date"        => $order_date,
                    "ship_date"         => $ship_date,
                    "order_status"      => $order_status,
                    "order_customer_id" => $order_customer_id,
                ];
                $result->MoveNext();
            }
            $this->result['orders_all'] = $orders_all;
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
                $this->result['totalorders'] = $num_rows;
                $this->result['num_pages'] = $num_pages;
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $this->result['orders_all'] = "no";
            $this->result['html'] = "no";
        }
        $this->result['adminmodules_shop_status'] = $orders_status;
    }


    public function shop_orderproc($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $order_id = 0;
        if (isset($array['order_id'])) {
            $order_id = intval($array['order_id']);
        }
        $edit = '';
        if (isset($array['edit'])) {
            if (intval($array['edit']) > 0) {
                $edit = 'edit';
            }
        }
        if ($order_id == 0) {
            $this->error = 'order_proc_no_order';

            return;
        }
        $sql =
            "SELECT a.order_id,a.order_customer_id,a.order_amount,a.order_date,a.ship_date,a.order_status,a.ship_name,a.ship_street,a.ship_house,a.ship_flat,a.ship_city,a.ship_state,a.ship_postal_code,a.ship_country,a.ship_telephone,a.ship_email,a.ship_add_info,a.order_delivery,a.order_pay,a.order_comments,a.order_ip,b.order_item_id,b.item_id,b.order_item_name,b.order_item_price,b.order_item_quantity,null,null,null,null,null,null,e.user_login,e.user_name,e.user_surname,e.user_rights,f.delivery_price,g.pay_price FROM "
            . PREFIX . "_orders_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_order_items_" . $this->registry->sitelang
            . " b ON a.order_id=b.order_id LEFT OUTER JOIN " . PREFIX . "_users e ON a.order_customer_id=e.user_id LEFT OUTER JOIN " . PREFIX . "_delivery_"
            . $this->registry->sitelang . " f ON a.order_delivery=f.delivery_id LEFT OUTER JOIN " . PREFIX . "_pay_" . $this->registry->sitelang
            . " g ON a.order_pay=g.pay_id WHERE a.order_id = " . $order_id
            . " UNION ALL SELECT null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,c.delivery_id,c.delivery_name,c.delivery_price,null,null,null,null,null,null,null,null,null FROM "
            . PREFIX . "_delivery_" . $this->registry->sitelang
            . " c UNION ALL SELECT null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,d.pay_id,d.pay_name,d.pay_price,null,null,null,null,null,null FROM "
            . PREFIX . "_pay_" . $this->registry->sitelang . " d";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'order_proc_sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'order_proc_not_found';

            return;
        }
        while (!$result->EOF) {
            if (!isset($order_all) && intval($result->fields['0']) > 0) {
                $order_id = intval($result->fields['0']);
                $order_customer_id = intval($result->fields['1']);
                $order_customer_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['32']));
                $order_customer_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['33']));
                $order_customer_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['34']));
                $order_customer_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['35']));
                $user_type = false;
                if ($order_customer_id > 0) {
                    $user_type = 'user';
                    $model_account = singleton::getinstance('account', $this->registry);
                    if (is_object($model_account)) {
                        if ($model_account->check_admin_rights($order_customer_rights)) {
                            $user_type = 'admin';
                        }
                    }
                }
                $order_amount = floatval($result->fields['2']);
                $order_amount2 = $order_amount + floatval($result->fields['36']) + (($order_amount * floatval($result->fields['37'])) / 100);
                $order_date = intval($result->fields['3']);
                $order_date2 = date("d.m.Y", $order_date);
                $order_hour = date("H", $order_date);
                $order_minute = date("i", $order_date);
                if (intval($result->fields['4']) == 0) {
                    $ship_date2 = "no";
                    $ship_hour = "no";
                    $ship_minute = "no";
                } else {
                    $ship_date = intval($result->fields['4']);
                    $ship_date2 = date("d.m.Y", $ship_date);
                    $ship_hour = date("H", $ship_date);
                    $ship_minute = date("i", $ship_date);
                }
                $order_status = intval($result->fields['5']);
                $ship_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $ship_street = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $ship_house = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $ship_flat = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $ship_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                $ship_state = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                $ship_postalcode = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $ship_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                $ship_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                $ship_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                $ship_addinfo = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
                $order_delivery = intval($result->fields['17']);
                $order_pay = intval($result->fields['18']);
                $order_comments = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['19']));
                $order_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['20']));
                $order_all[] = [
                    "order_id"                      => $order_id,
                    "order_customer_id"             => $order_customer_id,
                    "order_amount"                  => number_format($order_amount, 2, '.', ' '),
                    "order_amount2"                 => number_format($order_amount2, 2, '.', ' '),
                    "order_date"                    => $order_date2,
                    "order_hour"                    => $order_hour,
                    "order_minute"                  => $order_minute,
                    "ship_date"                     => $ship_date2,
                    "ship_hour"                     => $ship_hour,
                    "ship_minute"                   => $ship_minute,
                    "order_status"                  => $order_status,
                    "ship_name"                     => $ship_name,
                    "ship_street"                   => $ship_street,
                    "ship_house"                    => $ship_house,
                    "ship_flat"                     => $ship_flat,
                    "ship_city"                     => $ship_city,
                    "ship_state"                    => $ship_state,
                    "ship_postalcode"               => $ship_postalcode,
                    "ship_country"                  => $ship_country,
                    "ship_telephone"                => $ship_telephone,
                    "ship_email"                    => $ship_email,
                    "ship_addinfo"                  => $ship_addinfo,
                    "order_delivery"                => $order_delivery,
                    "order_pay"                     => $order_pay,
                    "order_comments"                => $order_comments,
                    "order_customer_login"          => $order_customer_login,
                    "order_customer_name"           => $order_customer_name,
                    "order_customer_surname"        => $order_customer_surname,
                    "order_customer_type"           => $user_type,
                    "order_ip"                      => $order_ip,
                    "order_items_additional_params" => $this->getOrderItemsAdditionalParams($order_id),
                ];
            }
            if (isset($result->fields['21']) && intval($result->fields['21']) > 0) {
                $order_item_id = intval($result->fields['21']);
                $item_id = intval($result->fields['22']);
                $order_item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['23']));
                $order_item_price = number_format(floatval($result->fields['24']), 2, '.', ' ');
                $order_item_quantity = intval($result->fields['25']);
                $order_items_all_now = [
                    "order_item_id"       => $order_item_id,
                    "item_id"             => $item_id,
                    "order_item_name"     => $order_item_name,
                    "order_item_price"    => $order_item_price,
                    "order_item_quantity" => $order_item_quantity,
                ];
                if (!isset($order_items_all) || (isset($order_items_all) && !in_array($order_items_all_now, $order_items_all))) {
                    $order_items_all[] = [
                        "order_item_id"       => $order_item_id,
                        "item_id"             => $item_id,
                        "order_item_name"     => $order_item_name,
                        "order_item_price"    => $order_item_price,
                        "order_item_quantity" => $order_item_quantity,
                    ];
                }
            }
            if (isset($result->fields['26']) && intval($result->fields['26']) > 0) {
                $delivery_id = intval($result->fields['26']);
                $delivery_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['27']));
                $delivery_price = number_format(floatval($result->fields['28']), 2, '.', ' ');
                $delivery_all[] = [
                    "delivery_id"    => $delivery_id,
                    "delivery_name"  => $delivery_name,
                    "delivery_price" => $delivery_price,
                ];
            }
            if (isset($result->fields['29']) && intval($result->fields['29']) > 0) {
                $pay_id = intval($result->fields['29']);
                $pay_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['30']));
                $pay_price = floatval($result->fields['31']);
                $pay_all[] = ["pay_id" => $pay_id, "pay_name" => $pay_name, "pay_price" => $pay_price];
            }
            $result->MoveNext();
        }
        if (isset($order_items_all)) {
            $this->result['order_items_all'] = $order_items_all;
        } else {
            $this->result['order_items_all'] = "no";
        }
        if (isset($delivery_all)) {
            $this->result['delivery_all'] = $delivery_all;
        } else {
            $this->result['delivery_all'] = "no";
        }
        if (isset($pay_all)) {
            $this->result['pay_all'] = $pay_all;
        } else {
            $this->result['pay_all'] = "no";
        }
        $this->result['order_all'] = $order_all;
        $this->result['adminmodules_shop_orderedit'] = $edit;
        $this->result['adminmodules_shop_orderid'] = $order_id;
        $this->result['include_jquery'] = "yes";
        $this->result['include_jquery_data'] = "dateinput";
    }
    
    
    private function getOrderItemsAdditionalParams($orderId)
    {
        $data = false;
        $sql = "SELECT a.id,a.order_item_id,a.order_suborder_id,a.order_item_param_group_id,b.group_name,a.order_item_param_id,c.param_value "
                . "FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " a "
                . "LEFT OUTER JOIN " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " b ON a.order_item_param_group_id=b.id "
                . "LEFT OUTER JOIN " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " c ON a.order_item_param_id=c.id "
                . "WHERE a.order_id='" . $orderId . "' "
                . "ORDER BY a.order_suborder_id,b.id,c.param_value ASC";
        $result = $this->db->Execute($sql);
        
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'order_proc_sql_error';
            $this->error_array = $error;

            return $data;
        }
        
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        
        if ($row_exist < 1) {

            return $data;
        }
        
        while (!$result->EOF) {
            $orderItemId = intval($result->fields['1']);
            $orderSuborderId = intval($result->fields['2']);
            $orderItemParamGroupId = intval($result->fields['3']);
            $orderItemParamGroupName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
            $orderItemParamId = intval($result->fields['5']);
            $orderItemParamValue = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
            $data[$orderItemId][$orderSuborderId]['groups'][$orderItemParamGroupId] = [
                'group_id' => $orderItemParamGroupId,
                'group_name' => $orderItemParamGroupName,
            ];
            $data[$orderItemId][$orderSuborderId]['groups_data'][$orderItemParamGroupId][$orderItemParamId] = [
                'param_id' => $orderItemParamId,
                'param_value' => $orderItemParamValue,
            ];
            $result->MoveNext();
        }
        
        return $data;
    }


    public function shop_order_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['order_hour'] = 0;
        $data_array['order_minute'] = 0;
        $data_array['ship_hour'] = 0;
        $data_array['ship_minute'] = 0;
        $data_array['order_edit'] = 0;
        if (!empty($array)) {
            $keys = [
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
                'order_comments',
            ];
            $keys2 = [
                'order_id',
                'order_hour',
                'order_minute',
                'ship_hour',
                'ship_minute',
                'order_status',
                'order_status_old',
                'order_delivery',
                'order_pay',
                'order_edit',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if ((!isset($array['order_id']) || ($data_array['order_edit'] == 0 && (!isset($array['order_status']) || !isset($array['order_status_old'])))
             || ($data_array['order_edit'] == 1
                 && (!isset($array['order_status']) || !isset($array['order_status_old']) || !isset($array['order_date'])
                     || !isset($array['order_hour'])
                     || !isset($array['order_minute'])
                     || !isset($array['ship_date'])
                     || !isset($array['ship_hour'])
                     || !isset($array['ship_minute'])
                     || !isset($array['ship_name'])
                     || !isset($array['ship_street'])
                     || !isset($array['ship_house'])
                     || !isset($array['ship_flat'])
                     || !isset($array['ship_city'])
                     || !isset($array['ship_state'])
                     || !isset($array['ship_postalcode'])
                     || !isset($array['ship_country'])
                     || !isset($array['ship_telephone'])
                     || !isset($array['ship_email'])
                     || !isset($array['ship_addinfo'])
                     || !isset($array['order_delivery'])
                     || !isset($array['order_pay'])
                     || !isset($array['order_comments']))))
            || ($data_array['order_id'] == 0 || ($data_array['order_edit'] == 0 && ($data_array['order_status'] == 0 || $data_array['order_status_old'] == 0))
                || ($data_array['order_edit'] == 1
                    && ($data_array['order_date'] == "" || $data_array['order_status'] == 0 || $data_array['order_status_old'] == 0
                        || $data_array['ship_name'] == ""
                        || $data_array['ship_telephone'] == ""
                        || $data_array['ship_email'] == ""
                        || $data_array['order_delivery'] == 0
                        || $data_array['order_pay'] == 0)))
        ) {
            $this->error = 'order_not_all_data';

            return;
        }
        if ($data_array['order_edit'] == 0) {
            $sql = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET order_status =  '" . $data_array['order_status'] . "' WHERE order_id = "
                   . $data_array['order_id'];
            if ($data_array['order_status'] == 7 || $data_array['order_status'] == 8) {
                $data_array['ship_date'] = "'" . $this->registry->main_class->get_time() . "'";
                $sql2 = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET ship_date =  " . $data_array['ship_date'] . " WHERE order_id = "
                        . $data_array['order_id'] . " and ship_date is NULL";
            }
        } elseif ($data_array['order_edit'] == 1) {
            $data_array['order_date'] = $this->registry->main_class->format_striptags($data_array['order_date']);
            $data_array['order_date'] = $data_array['order_date'] . " " . sprintf("%02d", $data_array['order_hour']) . ":" . sprintf("%02d", $data_array['order_minute']);
            $data_array['order_date'] = $this->registry->main_class->time_to_unix($data_array['order_date']);
            if ($data_array['order_status'] == 7 || $data_array['order_status'] == 8) {
                $data_array['ship_date'] = $this->registry->main_class->format_striptags($data_array['ship_date']);
                if ($data_array['ship_date'] == "") {
                    $data_array['ship_date'] = "'" . $this->registry->main_class->get_time() . "'";
                } else {
                    $data_array['ship_date'] = $data_array['ship_date'] . " " . sprintf("%02d", $data_array['ship_hour']) . ":" . sprintf(
                            "%02d", $data_array['ship_minute']
                        );
                    $data_array['ship_date'] = "'" . $this->registry->main_class->time_to_unix($data_array['ship_date']) . "'";
                }
            } else {
                $data_array['ship_date'] = 'NULL';
            }
            $data_array['ship_name'] = $this->registry->main_class->format_striptags($data_array['ship_name']);
            $data_array['ship_name'] = $this->registry->main_class->processing_data($data_array['ship_name']);
            $data_array['ship_telephone'] = $this->registry->main_class->format_striptags($data_array['ship_telephone']);
            $data_array['ship_telephone'] = $this->registry->main_class->processing_data($data_array['ship_telephone']);
            $data_array['ship_email'] = $this->registry->main_class->format_striptags($data_array['ship_email']);
            if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['ship_email']))) {
                $this->error = 'order_email';

                return;
            }
            $data_array['ship_email'] = $this->registry->main_class->processing_data($data_array['ship_email']);
            $ship_addinfo_length = strlen($data_array['ship_addinfo']);
            $order_comments_length = strlen($data_array['order_comments']);
            $keys = [
                'ship_country',
                'ship_state',
                'ship_city',
                'ship_street',
                'ship_house',
                'ship_flat',
                'ship_postalcode',
                'ship_addinfo',
                'order_comments',
            ];
            foreach ($data_array as $key => $value) {
                if (in_array($key, $keys)) {
                    $value = $this->registry->main_class->format_striptags($value);
                    if ($value != "") {
                        $data_array[$key] = $this->registry->main_class->processing_data($value);
                    } else {
                        $data_array[$key] = 'NULL';
                    }
                }
            }
            if ($ship_addinfo_length > 255 || $order_comments_length > 255) {
                $this->error = 'order_post_length';

                return;
            }
            $sql = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET order_amount = (SELECT sum(order_item_price*order_item_quantity) FROM " . PREFIX
                   . "_order_items_" . $this->registry->sitelang . " WHERE order_id = " . $data_array['order_id'] . "),order_date = '" . $data_array['order_date']
                   . "',ship_date = " . $data_array['ship_date'] . ",order_status = '" . $data_array['order_status'] . "',ship_name = " . $data_array['ship_name']
                   . ",ship_telephone = " . $data_array['ship_telephone'] . ",ship_email = " . $data_array['ship_email'] . ",ship_country = "
                   . $data_array['ship_country']
                   . ",ship_state = " . $data_array['ship_state'] . ",ship_city = " . $data_array['ship_city'] . ",ship_street = " . $data_array['ship_street']
                   . ",ship_house = " . $data_array['ship_house'] . ",ship_flat = " . $data_array['ship_flat'] . ",ship_postal_code = " . $data_array['ship_postalcode']
                   . ",ship_add_info = " . $data_array['ship_addinfo'] . ",order_delivery = '" . $data_array['order_delivery'] . "',order_pay = '"
                   . $data_array['order_pay']
                   . "',order_comments = " . $data_array['order_comments'] . " WHERE order_id = " . $data_array['order_id'];
        } else {
            $this->error = 'unknown_action';

            return;
        }

        $this->db->StartTrans();
        $result = true;

        if (isset($sql2)) {
            $result = $this->db->Execute($sql2);
        }

        if ($result) {
            $result = $this->db->Execute($sql);
        }

        if ($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }

        $this->db->CompleteTrans();

        if ($result === false) {
            $this->error = 'order_save_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'order_edit_ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders|0");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders|" . $data_array['order_status']);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders|" . $data_array['order_status_old']);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|" . $data_array['order_id']);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|edit" . $data_array['order_id']);
        $this->result = 'order_edit_done';
    }


    public function shop_order_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = '';
        if (isset($array['action'])) {
            $action = trim($array['action']);
        }
        if (isset($array['get_item_id'])) {
            $item_id = intval($array['get_item_id']);
        } else {
            $item_id = 0;
        }
        if ($item_id == 0 && isset($array['post_item_id']) && is_array($array['post_item_id']) && count($array['post_item_id']) > 0) {
            $item_id_is_arr = 1;
            for ($i = 0, $size = count($array['post_item_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $item_id = "'" . intval($array['post_item_id'][$i]) . "'";
                } else {
                    $item_id .= ",'" . intval($array['post_item_id'][$i]) . "'";
                }
            }
        } else {
            $item_id_is_arr = 0;
            $item_id = "'" . $item_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if ($action == "" || $item_id == "'0'") {
            $this->error = 'order_status_not_all_data';

            return;
        }
        if (in_array($action, [1, 2, 3, 4, 5, 6, 7, 8, 9])) {
            if ($action == 7 || $action == 8) {
                $ship_date = "'" . $this->registry->main_class->get_time() . "'";
                $sql = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET order_status = '" . $action . "',ship_date = " . $ship_date
                       . " WHERE order_id IN (" . $item_id . ")";
            } else {
                $sql = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET order_status = '" . $action . "',ship_date = NULL WHERE order_id IN ("
                       . $item_id . ")";
            }
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $this->result['shop_message'] = "order_status";
        } elseif ($action == "on" || $action == "off") {
            $this->db->StartTrans();
            if ($action == "on") {
                $delivery_status = 1;
                $this->result['shop_message'] = "order_status_deliv_status_on";
            } else {
                $delivery_status = 0;
                $this->result['shop_message'] = "order_status_deliv_status_off";
                $sql1 = "SELECT count(delivery_id) as count_rows FROM " . PREFIX . "_delivery_" . $this->registry->sitelang
                        . " WHERE delivery_status = '1'";
                $result1 = $this->db->Execute($sql1);
                if ($result1) {
                    if (isset($result1->fields['0'])) {
                        if ($item_id_is_arr == 0) {
                            $num_rows = intval($result1->fields['0']);
                        } else {
                            $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                        }
                    } else {
                        $num_rows = 0;
                    }
                    if ($num_rows == 0 || ($num_rows < 2 && $item_id_is_arr == 0) || ($num_rows < 1 && $item_id_is_arr == 1)) {
                        $this->error = 'order_status_deliv_items2';

                        return;
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            }
            $sql = "UPDATE " . PREFIX . "_delivery_" . $this->registry->sitelang . " SET delivery_status = '" . $delivery_status . "' WHERE delivery_id IN ("
                   . $item_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result === false || (isset($result1) && $result1 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } elseif ($action == "on2" || $action == "off2") {
            $this->db->StartTrans();
            if ($action == "on2") {
                $pay_status = 1;
                $this->result['shop_message'] = "order_status_pay_status_on";
            } else {
                $pay_status = 0;
                $this->result['shop_message'] = "order_status_pay_status_off";
                $sql1 = "SELECT count(pay_id) as count_rows FROM " . PREFIX . "_pay_" . $this->registry->sitelang . " WHERE pay_status = '1'";
                $result1 = $this->db->Execute($sql1);
                if ($result1) {
                    if (isset($result1->fields['0'])) {
                        if ($item_id_is_arr == 0) {
                            $num_rows = intval($result1->fields['0']);
                        } else {
                            $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                        }
                    } else {
                        $num_rows = 0;
                    }
                    if ($num_rows == 0 || ($num_rows < 2 && $item_id_is_arr == 0) || ($num_rows < 1 && $item_id_is_arr == 1)) {
                        $this->error = 'order_status_pay_items2';

                        return;
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            }
            $sql = "UPDATE " . PREFIX . "_pay_" . $this->registry->sitelang . " SET pay_status = '" . $pay_status . "' WHERE pay_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result === false || (isset($result1) && $result1 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM " . PREFIX . "_order_items_" . $this->registry->sitelang . " WHERE order_id IN (" . $item_id . ")";
            $result1 = $this->db->Execute($sql1);
            if ($result1 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $sql = "DELETE FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " WHERE order_id IN (" . $item_id . ")";
            $resultAdditionalParams = $this->db->Execute($sql);
            
            if ($resultAdditionalParams === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            
            $sql = "DELETE FROM " . PREFIX . "_orders_" . $this->registry->sitelang . " WHERE order_id IN (" . $item_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result1 === false || $resultAdditionalParams === false) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_order_delete";
        } elseif ($action == "delete2") {
            $this->db->StartTrans();
            $sql1 = "SELECT count(order_item_id) as count_rows,max(order_id) FROM " . PREFIX . "_order_items_" . $this->registry->sitelang
                    . " WHERE order_id = (SELECT DISTINCT b.order_id FROM " . PREFIX . "_order_items_" . $this->registry->sitelang . " b WHERE b.order_item_id IN ("
                    . $item_id . "))";
            $result1 = $this->db->Execute($sql1);
            if ($result1) {
                if (isset($result1->fields['0'])) {
                    if ($item_id_is_arr == 0) {
                        $num_rows = intval($result1->fields['0']);
                    } else {
                        $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                    }
                    $order_id = intval($result1->fields['1']);
                } else {
                    $num_rows = 0;
                }
                if ($num_rows == 0 || ($num_rows < 2 && $item_id_is_arr == 0) || ($num_rows < 1 && $item_id_is_arr == 1)) {
                    $this->error = 'order_status_order_items';

                    return;
                }
                
                $from = PREFIX . "_order_items_" . $this->registry->sitelang . " WHERE order_item_id IN (" . $item_id . ")";
                $sql = "DELETE FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " WHERE order_item_id IN (SELECT item_id FROM " . $from . ")";
                $resultAdditionalParams = $this->db->Execute($sql);
                
                if ($resultAdditionalParams === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                
                $sql = "DELETE FROM " . $from;
                $result = $this->db->Execute($sql);
                if ($result) {
                    $num_result = $this->db->Affected_Rows();
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $sql2 = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET order_amount = (SELECT sum(b.order_item_price*b.order_item_quantity) FROM "
                        . PREFIX . "_order_items_" . $this->registry->sitelang . " b WHERE b.order_id = " . $order_id . ") WHERE order_id = " . $order_id;
                $result2 = $this->db->Execute($sql2);
                if ($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result1 === false || (isset($resultAdditionalParams) && $resultAdditionalParams === false) || (isset($result2) && $result2 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_item_delete";
        } elseif ($action == "delete3") {
            $this->db->StartTrans();
            $sql1 = "SELECT count(delivery_id) as count_rows FROM " . PREFIX . "_delivery_" . $this->registry->sitelang;
            $result1 = $this->db->Execute($sql1);
            if ($result1) {
                if (isset($result1->fields['0'])) {
                    if ($item_id_is_arr == 0) {
                        $num_rows = intval($result1->fields['0']);
                    } else {
                        $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                    }
                } else {
                    $num_rows = 0;
                }
                if ($num_rows == 0 || ($num_rows < 2 && $item_id_is_arr == 0) || ($num_rows < 1 && $item_id_is_arr == 1)) {
                    $this->error = 'order_status_deliv_items';

                    return;
                }
                $sql2 = "DELETE FROM " . PREFIX . "_order_items_" . $this->registry->sitelang . " WHERE order_id IN (SELECT order_id FROM " . PREFIX . "_orders_"
                        . $this->registry->sitelang . " WHERE order_delivery IN (" . $item_id . "))";
                $result2 = $this->db->Execute($sql2);
                if ($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $sql3 = "DELETE FROM " . PREFIX . "_orders_" . $this->registry->sitelang . " WHERE order_delivery IN (" . $item_id . ")";
                $result3 = $this->db->Execute($sql3);
                if ($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $sql = "DELETE FROM " . PREFIX . "_delivery_" . $this->registry->sitelang . " WHERE delivery_id IN (" . $item_id . ")";
                $result = $this->db->Execute($sql);
                if ($result) {
                    $num_result = $this->db->Affected_Rows();
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result1 === false || (isset($result2) && $result2 === false) || (isset($result3) && $result3 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_deliv_delete";
        } elseif ($action == "delete4") {
            $this->db->StartTrans();
            $sql1 = "SELECT count(pay_id) as count_rows FROM " . PREFIX . "_pay_" . $this->registry->sitelang;
            $result1 = $this->db->Execute($sql1);
            if ($result1) {
                if (isset($result1->fields['0'])) {
                    if ($item_id_is_arr == 0) {
                        $num_rows = intval($result1->fields['0']);
                    } else {
                        $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                    }
                } else {
                    $num_rows = 0;
                }
                if ($num_rows == 0 || ($num_rows < 2 && $item_id_is_arr == 0) || ($num_rows < 1 && $item_id_is_arr == 1)) {
                    $this->error = 'order_status_pay_items';

                    return;
                }
                $sql2 = "DELETE FROM " . PREFIX . "_order_items_" . $this->registry->sitelang . " WHERE order_id IN (SELECT order_id FROM " . PREFIX . "_orders_"
                        . $this->registry->sitelang . " WHERE order_pay IN (" . $item_id . "))";
                $result2 = $this->db->Execute($sql2);
                if ($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $sql3 = "DELETE FROM " . PREFIX . "_orders_" . $this->registry->sitelang . " WHERE order_pay IN (" . $item_id . ")";
                $result3 = $this->db->Execute($sql3);
                if ($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $sql = "DELETE FROM " . PREFIX . "_pay_" . $this->registry->sitelang . " WHERE pay_id IN (" . $item_id . ")";
                $result = $this->db->Execute($sql);
                if ($result) {
                    $num_result = $this->db->Affected_Rows();
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($result1 === false || (isset($result2) && $result2 === false) || (isset($result3) && $result3 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_pay_delete";
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_save';

            return;
        }
        if (in_array($action, [1, 2, 3, 4, 5, 6, 7, 8, 9]) || $action == "delete") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order");
            if ($action == "delete") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
            }
        } elseif ($action == "delete3" || $action == "on" || $action == "off") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|delivs");
            if ($item_id_is_arr == 0) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|deliv|edit" . intval($array['get_item_id']));
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|deliv");
            }
            if ($action == "delete3") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
            }
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|admin");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|other");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|admin");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|other");
        } elseif ($action == "delete4" || $action == "on2" || $action == "off2") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|pays");
            if ($item_id_is_arr == 0) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|pay|edit" . intval($array['get_item_id']));
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|pay");
            }
            if ($action == "delete4") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orders");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi");
            }
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|admin");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|other");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|admin");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|other");
        } else {
            $this->result['adminmodules_shop_order_id'] = $order_id;
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|" . $order_id);
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|edit" . $order_id);
            if ($item_id_is_arr == 0) {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi|" . $order_id . "|editi" . intval($array['get_item_id'])
                );
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi|" . $order_id);
            }
        }
    }


    public function shop_orderi_edit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['item_id'] = 0;
        $data_array['order_id'] = 0;
        if (!empty($array)) {
            $keys = [
                'item_id',
                'order_id',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if ($data_array['item_id'] == 0 || $data_array['order_id'] == 0) {
            $this->error = 'no_item';

            return;
        }
        $sql =
            "SELECT a.order_item_id,a.order_id,a.item_id,a.order_item_name,a.order_item_price,a.order_item_quantity,b.item_catalog_id,b.item_category_id,b.item_subcategory_id,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id FROM "
            . PREFIX . "_order_items_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_items_" . $this->registry->sitelang
            . " b ON a.item_id = b.item_id LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
            . " c ON b.item_catalog_id = c.catalog_id and b.item_catalog_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_categories_" . $this->registry->sitelang
            . " d ON b.item_category_id = d.category_id and b.item_category_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_catalogs_" . $this->registry->sitelang
            . " f ON d.cat_catalog_id=f.catalog_id and b.item_category_id is NOT NULL " . "LEFT OUTER JOIN " . PREFIX . "_subcategories_" . $this->registry->sitelang
            . " e ON b.item_subcategory_id = e.subcategory_id and b.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_categories_"
            . $this->registry->sitelang . " g ON e.subcategory_cat_id=g.category_id and b.item_subcategory_id is NOT NULL LEFT OUTER JOIN " . PREFIX . "_catalogs_"
            . $this->registry->sitelang . " h ON g.cat_catalog_id=h.catalog_id and b.item_subcategory_id is NOT NULL WHERE a.order_item_id = " . $data_array['item_id']
            . " and a.order_id = " . $data_array['order_id'];
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'not_found_item';

            return;
        }
        while (!$result->EOF) {
            $order_item_id = intval($result->fields['0']);
            $order_id = intval($result->fields['1']);
            $item_id2 = intval($result->fields['2']);
            $order_item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
            $order_item_price = floatval($result->fields['4']);
            $order_item_quantity = intval($result->fields['5']);
            $item_catalog_id = intval($result->fields['6']);
            $item_category_id = intval($result->fields['7']);
            if ($item_category_id > 0) {
                $item_categ_cat_id = intval($result->fields['9']);
            } else {
                $item_categ_cat_id = "no";
            }
            $item_subcategory_id = intval($result->fields['8']);
            if ($item_subcategory_id > 0) {
                $item_subcateg_categ_id = intval($result->fields['10']);
                $item_subcateg_cat_id = intval($result->fields['11']);
            } else {
                $item_subcateg_categ_id = "no";
                $item_subcateg_cat_id = "no";
            }
            $order_item_all[] = [
                "order_item_id"          => $order_item_id,
                "order_id"               => $order_id,
                "item_id"                => $item_id2,
                "order_item_name"        => $order_item_name,
                "order_item_price"       => number_format(floatval($order_item_price), 2, '.', ' '),
                "order_item_quantity"    => $order_item_quantity,
                "item_catalog_id"        => $item_catalog_id,
                "item_category_id"       => $item_category_id,
                "item_categ_cat_id"      => $item_categ_cat_id,
                "item_subcategory_id"    => $item_subcategory_id,
                "item_subcateg_categ_id" => $item_subcateg_categ_id,
                "item_subcateg_cat_id"   => $item_subcateg_cat_id,
            ];
            $result->MoveNext();
        }
        $this->result['order_item_all'] = $order_item_all;
    }


    public function shop_orderi_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'order_item_name',
                'order_item_price',
            ];
            $keys2 = [
                'order_item_id',
                'order_id',
                'order_item_quantity',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['order_item_price'])) {
            $data_array['order_item_price'] = $this->registry->main_class->float_to_db($data_array['order_item_price']);
        }
        if ((!isset($array['order_item_id']) || !isset($array['order_id']) || !isset($array['order_item_name']) || !isset($array['order_item_price'])
             || !isset($array['order_item_quantity']))
            || ($data_array['order_item_id'] == 0 || $data_array['order_id'] == 0 || $data_array['order_item_name'] == "" || $data_array['order_item_quantity'] == 0)
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['order_item_name'] = $this->registry->main_class->format_striptags($data_array['order_item_name']);
        $data_array['order_item_name'] = $this->registry->main_class->processing_data($data_array['order_item_name']);
        $this->db->StartTrans();
        $sql = "UPDATE " . PREFIX . "_order_items_" . $this->registry->sitelang . " SET order_item_name = " . $data_array['order_item_name'] . ",order_item_price = '"
               . $data_array['order_item_price'] . "',order_item_quantity = '" . $data_array['order_item_quantity'] . "' WHERE order_item_id = "
               . $data_array['order_item_id'];
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        $sql2 = "UPDATE " . PREFIX . "_orders_" . $this->registry->sitelang . " SET order_amount = (SELECT sum(b.order_item_price*b.order_item_quantity) FROM "
                . PREFIX . "_order_items_" . $this->registry->sitelang . " b WHERE b.order_id = " . $data_array['order_id'] . ") WHERE order_id = "
                . $data_array['order_id'];
        $result2 = $this->db->Execute($sql2);
        if ($result2 === false) {
            $result = false;
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        $this->db->CompleteTrans();
        if ($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_save';

            return;
        }
        $this->result['adminmodules_shop_order_id'] = $data_array['order_id'];
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|" . $data_array['order_id']);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order|edit" . $data_array['order_id']);
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|shop|orderi|" . $data_array['order_id'] . "|editi" . $data_array['order_item_id']
        );
        $this->result['shop_message'] = "order_item_save_done";
    }


    public function shop_methods($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['pay'] = 0;
        if (!empty($array)) {
            $keys = [
                'pay',
                'num_page',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $type = "pays";
        if ($data_array['pay'] == 0) {
            $type = "delivs";
        }
        if (isset($data_array['num_page']) && $data_array['num_page'] != 0) {
            $num_page = $data_array['num_page'];
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if ($type == "delivs") {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_delivery_" . $this->registry->sitelang;
            $sql = "SELECT a.delivery_id,a.delivery_name,a.delivery_price,a.delivery_status FROM " . PREFIX . "_delivery_" . $this->registry->sitelang . " a";
        } else {
            $sql0 = "SELECT count(*) FROM " . PREFIX . "_pay_" . $this->registry->sitelang;
            $sql = "SELECT a.pay_id,a.pay_name,a.pay_price,a.pay_status FROM " . PREFIX . "_pay_" . $this->registry->sitelang . " a";
        }
        $result = $this->db->Execute($sql0);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
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
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_price = number_format(floatval($result->fields['2']), 2, '.', ' ');
                $item_status = intval($result->fields['3']);
                $items_all[] = [
                    "item_id"     => $item_id,
                    "item_name"   => $item_name,
                    "item_price"  => $item_price,
                    "item_status" => $item_status,
                ];
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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
                $this->result['totalitems'] = $num_rows;
                $this->result['num_pages'] = $num_pages;
            } else {
                $this->result['html'] = "no";
            }
        } else {
            $this->result['items_all'] = "no";
            $this->result['html'] = "no";
        }
        $this->result['adminmodules_shop_method'] = $type;
    }


    public function shop_edit_method($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['item_id'] = 0;
        $data_array['pay'] = 0;
        if (!empty($array)) {
            $keys = [
                'item_id',
                'pay',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $type = 'pay';
        if ($data_array['pay'] == 0) {
            $type = 'deliv';
        }
        if ($data_array['item_id'] == 0) {
            $this->error = 'no_item_' . $type;

            return;
        }
        if ($type == 'deliv') {
            $sql = "SELECT delivery_id,delivery_name,delivery_price,delivery_status FROM " . PREFIX . "_delivery_" . $this->registry->sitelang . " WHERE delivery_id = "
                   . $data_array['item_id'];
        } else {
            $sql = "SELECT pay_id,pay_name,pay_price,pay_status FROM " . PREFIX . "_pay_" . $this->registry->sitelang . " WHERE pay_id = " . $data_array['item_id'];
        }
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'not_found_' . $type;

            return;
        }
        while (!$result->EOF) {
            $item_id = intval($result->fields['0']);
            $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $item_price = floatval($result->fields['2']);
            $item_status = intval($result->fields['3']);
            $item_all[] = [
                "item_id"     => $item_id,
                "item_name"   => $item_name,
                "item_price"  => $item_price,
                "item_status" => $item_status,
            ];
            $result->MoveNext();
        }
        $this->result['item_all'] = $item_all;
        $this->result['adminmodules_shop_method'] = $type;
    }


    public function shop_method_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['pay'] = 0;
        if (!empty($array)) {
            $keys = [
                'item_name',
                'item_price',
            ];
            $keys2 = [
                'item_id',
                'item_status',
                'pay',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($data_array['item_price']);
        }
        $type = "pay";
        if ($data_array['pay'] == 0) {
            $type = "deliv";
        }
        if ((!isset($array['item_id']) || !isset($array['item_name']) || !isset($array['item_price']) || !isset($array['item_status']))
            || ($data_array['item_id'] == 0
                || $data_array['item_name'] == "")
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        if ($type == "deliv") {
            $sql = "UPDATE " . PREFIX . "_delivery_" . $this->registry->sitelang . " SET delivery_name = " . $data_array['item_name'] . ",delivery_price = '"
                   . $data_array['item_price'] . "',delivery_status = '" . $data_array['item_status'] . "' WHERE delivery_id = " . $data_array['item_id'];
        } else {
            $sql = "UPDATE " . PREFIX . "_pay_" . $this->registry->sitelang . " SET pay_name = " . $data_array['item_name'] . ",pay_price = '" . $data_array['item_price']
                   . "',pay_status = '" . $data_array['item_status'] . "' WHERE pay_id = " . $data_array['item_id'];
        }
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_save';

            return;
        }
        if ($type == "deliv") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|delivs");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|deliv|edit" . $data_array['item_id']);
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|pays");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|pay|edit" . $data_array['item_id']);
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|admin");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|other");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|admin");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|other");
        $this->result = $type . '_edit_ok';
    }


    public function shop_method_add($pay)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $pay = intval($pay);
        $type = "pay";
        if ($pay == 0) {
            $type = "deliv";
        }
        $this->result['adminmodules_shop_method'] = $type;
    }


    public function shop_method_save2($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['pay'] = 0;
        if (!empty($array)) {
            $keys = [
                'item_name',
                'item_price',
            ];
            $keys2 = [
                'item_status',
                'pay',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (isset($data_array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($data_array['item_price']);
        }
        $type = "pay";
        if ($data_array['pay'] == 0) {
            $type = "deliv";
        }
        if ((!isset($array['item_name']) || !isset($array['item_price']) || !isset($array['item_status'])) || $data_array['item_name'] == "") {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        if ($type == "deliv") {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_delivery_id_" . $this->registry->sitelang, 'delivery_id');
            $sql = "INSERT INTO " . PREFIX . "_delivery_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "delivery_name,delivery_price,delivery_status) VALUES (" . $sequence_array['sequence_value_string'] . "" . $data_array['item_name'] . ",'"
                   . $data_array['item_price'] . "','" . $data_array['item_status'] . "')";
        } else {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_pay_id_" . $this->registry->sitelang, 'pay_id');
            $sql = "INSERT INTO " . PREFIX . "_pay_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "pay_name,pay_price,pay_status) VALUES (" . $sequence_array['sequence_value_string'] . "" . $data_array['item_name'] . ",'"
                   . $data_array['item_price']
                   . "','" . $data_array['item_status'] . "')";
        }
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if ($type == "deliv") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|delivs");
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|pays");
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|order");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|admin");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout|display|other");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|admin");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop|checkout2|display|other");
        $this->result = $type . '_add_done';
    }


    public function shop_config()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        require_once('../modules/shop/shop_config.inc');
        $this->result['systemdk_shop_catalogs_order'] = trim(SYSTEMDK_SHOP_CATALOGS_ORDER);
        $this->result['systemdk_shop_homeitems_perpage'] = intval(SYSTEMDK_SHOP_HOMEITEMS_PERPAGE);
        $this->result['systemdk_shop_homeitems_order'] = trim(SYSTEMDK_SHOP_HOMEITEMS_ORDER);
        $this->result['systemdk_shop_categories_order'] = trim(SYSTEMDK_SHOP_CATEGORIES_ORDER);
        $this->result['systemdk_shop_catitems_perpage'] = intval(SYSTEMDK_SHOP_CATITEMS_PERPAGE);
        $this->result['systemdk_shop_catitems_order'] = trim(SYSTEMDK_SHOP_CATITEMS_ORDER);
        $this->result['systemdk_shop_subcategories_order'] = trim(SYSTEMDK_SHOP_SUBCATEGORIES_ORDER);
        $this->result['systemdk_shop_categitems_perpage'] = intval(SYSTEMDK_SHOP_CATEGITEMS_PERPAGE);
        $this->result['systemdk_shop_categitems_order'] = trim(SYSTEMDK_SHOP_CATEGITEMS_ORDER);
        $this->result['systemdk_shop_subcategitems_perpage'] = intval(SYSTEMDK_SHOP_SUBCATEGITEMS_PERPAGE);
        $this->result['systemdk_shop_subcategitems_order'] = trim(SYSTEMDK_SHOP_SUBCATEGITEMS_ORDER);
        $this->result['systemdk_shop_itemimage_path'] = trim(SYSTEMDK_SHOP_ITEMIMAGE_PATH);
        $this->result['systemdk_shop_itemimage_position'] = trim(SYSTEMDK_SHOP_ITEMIMAGE_POSITION);
        $this->result['systemdk_shop_itemimage_maxsize'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE);
        $this->result['systemdk_shop_itemimage_maxwidth'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH);
        $this->result['systemdk_shop_itemimage_maxheight'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT);
        $this->result['systemdk_shop_itemimage_width'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_WIDTH);
        $this->result['systemdk_shop_itemimage_width2'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_WIDTH2);
        $this->result['systemdk_shop_itemimage_width3'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_WIDTH3);
        $this->result['systemdk_shop_itemimage_height3'] = intval(SYSTEMDK_SHOP_ITEMIMAGE_HEIGHT3);
        $this->result['systemdk_shop_image_path'] = trim(SYSTEMDK_SHOP_IMAGE_PATH);
        $this->result['systemdk_shop_image_position'] = trim(SYSTEMDK_SHOP_IMAGE_POSITION);
        $this->result['systemdk_shop_image_maxsize'] = intval(SYSTEMDK_SHOP_IMAGE_MAXSIZE);
        $this->result['systemdk_shop_image_maxwidth'] = intval(SYSTEMDK_SHOP_IMAGE_MAXWIDTH);
        $this->result['systemdk_shop_image_maxheight'] = intval(SYSTEMDK_SHOP_IMAGE_MAXHEIGHT);
        $this->result['systemdk_shop_image_width'] = intval(SYSTEMDK_SHOP_IMAGE_WIDTH);
        $this->result['systemdk_shop_items_columns'] = intval(SYSTEMDK_SHOP_ITEMS_COLUMNS);
        $this->result['systemdk_shop_items_underparent'] = intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT);
        $this->result['systemdk_shop_valuta'] = trim(SYSTEMDK_SHOP_VALUTA);
        $this->result['systemdk_shop_antispam_ipaddr'] = intval(SYSTEMDK_SHOP_ANTISPAM_IPADDR);
        $this->result['systemdk_shop_antispam_num'] = intval(SYSTEMDK_SHOP_ANTISPAM_NUM);
        $this->result['systemdk_shop_order_notify_email'] = intval(SYSTEMDK_SHOP_ORDER_NOTIFY_EMAIL);
        $this->result['systemdk_shop_order_notify_type'] = trim(SYSTEMDK_SHOP_ORDER_NOTIFY_TYPE);
        $this->result['systemdk_shop_order_notify_custom_mailbox'] = trim(SYSTEMDK_SHOP_ORDER_NOTIFY_CUSTOM_MAILBOX);
    }


    function shop_config_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
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
                'systemdk_shop_valuta',
                'systemdk_shop_order_notify_type',
                'systemdk_shop_order_notify_custom_mailbox',
            ];
            $keys2 = [
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
                'systemdk_shop_antispam_num',
                'systemdk_shop_order_notify_email',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if ((!isset($array['systemdk_shop_catalogs_order']) || !isset($array['systemdk_shop_homeitems_perpage']) || !isset($array['systemdk_shop_homeitems_order'])
             || !isset($array['systemdk_shop_categories_order'])
             || !isset($array['systemdk_shop_catitems_perpage'])
             || !isset($array['systemdk_shop_catitems_order'])
             || !isset($array['systemdk_shop_subcategories_order'])
             || !isset($array['systemdk_shop_categitems_perpage'])
             || !isset($array['systemdk_shop_categitems_order'])
             || !isset($array['systemdk_shop_subcategitems_perpage'])
             || !isset($array['systemdk_shop_subcategitems_order'])
             || !isset($array['systemdk_shop_itemimage_path'])
             || !isset($array['systemdk_shop_itemimage_position'])
             || !isset($array['systemdk_shop_itemimage_maxsize'])
             || !isset($array['systemdk_shop_itemimage_maxwidth'])
             || !isset($array['systemdk_shop_itemimage_maxheight'])
             || !isset($array['systemdk_shop_itemimage_width'])
             || !isset($array['systemdk_shop_itemimage_width2'])
             || !isset($array['systemdk_shop_itemimage_width3'])
             || !isset($array['systemdk_shop_itemimage_height3'])
             || !isset($array['systemdk_shop_image_path'])
             || !isset($array['systemdk_shop_image_position'])
             || !isset($array['systemdk_shop_image_maxsize'])
             || !isset($array['systemdk_shop_image_maxwidth'])
             || !isset($array['systemdk_shop_image_maxheight'])
             || !isset($array['systemdk_shop_image_width'])
             || !isset($array['systemdk_shop_items_columns'])
             || !isset($array['systemdk_shop_items_underparent'])
             || !isset($array['systemdk_shop_valuta'])
             || !isset($array['systemdk_shop_antispam_ipaddr'])
             || !isset($array['systemdk_shop_antispam_num'])
             || !isset($array['systemdk_shop_order_notify_email'])
             || !isset($array['systemdk_shop_order_notify_type'])
             || !isset($array['systemdk_shop_order_notify_custom_mailbox']))
            || ($data_array['systemdk_shop_catalogs_order'] == "" || $data_array['systemdk_shop_homeitems_perpage'] == 0
                || $data_array['systemdk_shop_homeitems_order'] == ""
                || $data_array['systemdk_shop_categories_order'] == ""
                || $data_array['systemdk_shop_catitems_perpage'] == 0
                || $data_array['systemdk_shop_catitems_order'] == ""
                || $data_array['systemdk_shop_subcategories_order'] == ""
                || $data_array['systemdk_shop_categitems_perpage'] == 0
                || $data_array['systemdk_shop_categitems_order'] == ""
                || $data_array['systemdk_shop_subcategitems_perpage'] == 0
                || $data_array['systemdk_shop_subcategitems_order'] == ""
                || $data_array['systemdk_shop_itemimage_path'] == ""
                || $data_array['systemdk_shop_itemimage_position'] == ""
                || $data_array['systemdk_shop_itemimage_maxsize'] == 0
                || $data_array['systemdk_shop_itemimage_maxwidth'] == 0
                || $data_array['systemdk_shop_itemimage_maxheight'] == 0
                || $data_array['systemdk_shop_itemimage_width'] == 0
                || $data_array['systemdk_shop_itemimage_width2'] == 0
                || $data_array['systemdk_shop_itemimage_width3'] == 0
                || $data_array['systemdk_shop_itemimage_height3'] == 0
                || $data_array['systemdk_shop_image_path'] == ""
                || $data_array['systemdk_shop_image_position'] == ""
                || $data_array['systemdk_shop_image_maxsize'] == 0
                || $data_array['systemdk_shop_image_maxwidth'] == 0
                || $data_array['systemdk_shop_image_maxheight'] == 0
                || $data_array['systemdk_shop_image_width'] == 0
                || $data_array['systemdk_shop_items_columns'] == 0
                || $data_array['systemdk_shop_valuta'] == ""
                || $data_array['systemdk_shop_order_notify_type'] == "")
        ) {
            $this->error = 'not_all_data';

            return;
        }
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $data_array[$key] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($value);
            }
        }
        @chmod("../modules/shop/shop_config.inc", 0777);
        $file = @fopen("../modules/shop/shop_config.inc", "w");
        if (!$file) {
            $this->error = 'no_file';

            return;
        }
        $content = "<?php\n";
        $content .= 'define("SYSTEMDK_SHOP_CATALOGS_ORDER","' . $data_array['systemdk_shop_catalogs_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_HOMEITEMS_PERPAGE",' . $data_array['systemdk_shop_homeitems_perpage'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_HOMEITEMS_ORDER","' . $data_array['systemdk_shop_homeitems_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATEGORIES_ORDER","' . $data_array['systemdk_shop_categories_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATITEMS_PERPAGE",' . $data_array['systemdk_shop_catitems_perpage'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATITEMS_ORDER","' . $data_array['systemdk_shop_catitems_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_SUBCATEGORIES_ORDER","' . $data_array['systemdk_shop_subcategories_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATEGITEMS_PERPAGE",' . $data_array['systemdk_shop_categitems_perpage'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATEGITEMS_ORDER","' . $data_array['systemdk_shop_categitems_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_SUBCATEGITEMS_PERPAGE",' . $data_array['systemdk_shop_subcategitems_perpage'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_SUBCATEGITEMS_ORDER","' . $data_array['systemdk_shop_subcategitems_order'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_PATH","' . $data_array['systemdk_shop_itemimage_path'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_POSITION","' . $data_array['systemdk_shop_itemimage_position'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE",' . $data_array['systemdk_shop_itemimage_maxsize'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH",' . $data_array['systemdk_shop_itemimage_maxwidth'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT",' . $data_array['systemdk_shop_itemimage_maxheight'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_WIDTH",' . $data_array['systemdk_shop_itemimage_width'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_WIDTH2",' . $data_array['systemdk_shop_itemimage_width2'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_WIDTH3",' . $data_array['systemdk_shop_itemimage_width3'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_HEIGHT3",' . $data_array['systemdk_shop_itemimage_height3'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_PATH","' . $data_array['systemdk_shop_image_path'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_POSITION","' . $data_array['systemdk_shop_image_position'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_MAXSIZE",' . $data_array['systemdk_shop_image_maxsize'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_MAXWIDTH",' . $data_array['systemdk_shop_image_maxwidth'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_MAXHEIGHT",' . $data_array['systemdk_shop_image_maxheight'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_WIDTH",' . $data_array['systemdk_shop_image_width'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMS_COLUMNS",' . $data_array['systemdk_shop_items_columns'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMS_UNDERPARENT",' . $data_array['systemdk_shop_items_underparent'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_VALUTA","' . $data_array['systemdk_shop_valuta'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ANTISPAM_IPADDR",' . $data_array['systemdk_shop_antispam_ipaddr'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ANTISPAM_NUM",' . $data_array['systemdk_shop_antispam_num'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ORDER_NOTIFY_EMAIL",' . $data_array['systemdk_shop_order_notify_email'] . ");\n";
        $content .= 'define("SYSTEMDK_SHOP_ORDER_NOTIFY_TYPE","' . $data_array['systemdk_shop_order_notify_type'] . "\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ORDER_NOTIFY_CUSTOM_MAILBOX","' . $data_array['systemdk_shop_order_notify_custom_mailbox'] . "\");\n";
        $write_file = @fwrite($file, $content);
        @fclose($file);
        @chmod("../modules/shop/shop_config.inc", 0604);
        if (!$write_file) {
            $this->error = 'no_write';

            return;
        }
        $this->registry->main_class->systemdk_clearcache("modules|shop");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|shop");
        $this->result = 'config_ok';
    }


    public function additionalParams($array, $entity)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $numPage = 1;

        if ($entity === 'group_params') {
            $groupId = 0;
        }

        if (isset($array['num_page']) && intval($array['num_page']) > 0) {
            $numPage = intval($array['num_page']);
        }

        if ($entity === 'group_params' && isset($array['group_id']) && intval($array['group_id']) > 0) {
            $groupId = intval($array['group_id']);
        }

        if ($entity === 'group_params' && $groupId < 1) {
            $this->error = 'empty_data';

            return;
        }

        $numStringRows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($numPage - 1) * $numStringRows;

        if ($entity === 'groups') {
            $sql = "SELECT count(*) FROM " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang;
        } else {
            $sql = "SELECT count(*) FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " WHERE param_group_id='" . $groupId . "'";
        }

        $result = $this->db->Execute($sql);

        if ($result) {
            $numRows = intval($result->fields['0']);

            if ($entity === 'groups') {
                $sql = "SELECT a.id,a.group_name,a.group_type_id,a.group_status, b.type_name,b.type_description FROM "
                       . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " a,"
                       . PREFIX . "_s_add_par_g_types b WHERE a.group_type_id=b.id ORDER BY a.id ASC";
            } else {
                $sql = "SELECT a.id,a.group_name,b.id,b.param_value,b.param_status FROM "
                       . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " a LEFT OUTER JOIN "
                       . PREFIX . "_s_add_params_" . $this->registry->sitelang . " b ON a.id=b.param_group_id WHERE a.id='" . $groupId
                       . "' ORDER BY b.id ASC";
            }

            $result = $this->db->SelectLimit($sql, $numStringRows, $offset);
        }

        if (!$result) {
            $error[] = ['code' => $this->db->ErrorNo(), 'message' => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        if (isset($result->fields['0'])) {
            $rowExist = intval($result->fields['0']);
        } else {
            $rowExist = 0;
        }

        if ($rowExist == 0 && $numPage > 1) {
            $this->error = 'unknown_page';

            return;
        }

        if ($rowExist < 1 && $entity === 'group_params') {
            $this->error = 'group_not_found';

            return;
        } elseif ($rowExist < 1) {
            $this->result['additional_param_' . $entity] = 'no_' . $entity;
            $this->result['pages_menu'] = 'no';

            return;
        }

        while (!$result->EOF) {

            if ($entity === 'groups') {
                $id = intval($result->fields['0']);
                $groupName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $groupTypeId = intval($result->fields['2']);
                $groupStatus = intval($result->fields['3']);
                $groupTypeName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $groupTypeDescription = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                $data[] = [
                    'id'                     => $id,
                    'group_name'             => $groupName,
                    'group_type_id'          => $groupTypeId,
                    'group_status'           => $groupStatus,
                    'group_type_name'        => $groupTypeName,
                    'group_type_description' => $groupTypeDescription,
                ];
            } else {
                $groupId = intval($result->fields['0']);
                $groupName = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $id = intval($result->fields['2']);
                $paramValue = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $paramStatus = intval($result->fields['4']);

                if (empty($this->result['additional_params_group_id'])) {
                    $this->result['additional_params_group_id'] = $groupId;
                }

                if (empty($this->result['additional_params_group_name'])) {
                    $this->result['additional_params_group_name'] = $groupName;
                }

                if ($id > 0) {
                    $data[] = [
                        'id'             => $id,
                        'param_group_id' => $groupId,
                        'param_value'    => $paramValue,
                        'param_status'   => $paramStatus,
                    ];
                }
            }
            $result->MoveNext();
        }

        if (empty($data) && $entity === 'group_params') {
            $this->result['additional_param_' . $entity] = 'no_' . $entity;
            $this->result['pages_menu'] = 'no';

            return;
        } else {
            $this->result['additional_param_' . $entity] = $data;
        }

        if (isset($numRows)) {
            $numPages = @ceil($numRows / $numStringRows);
        } else {
            $numPages = 0;
        }

        if ($numPages > 1) {

            if ($numPage > 1) {
                $prevpage = $numPage - 1;
                $this->result['prevpage'] = $prevpage;
            } else {
                $this->result['prevpage'] = 'no';
            }

            for ($i = 1; $i < $numPages + 1; $i++) {

                if ($i == $numPage) {
                    $html[] = ["number" => $i, "param1" => "1"];
                } else {
                    $pagelink = 5;

                    if (($i > $numPage) && ($i < $numPage + $pagelink) || ($i < $numPage) && ($i > $numPage - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "2"];
                    }

                    if (($i == $numPages) && ($numPage < $numPages - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "3"];
                    }

                    if (($i == 1) && ($numPage > $pagelink + 1)) {
                        $html[] = ["number" => $i, "param1" => "4"];
                    }
                }
            }

            if ($numPage < $numPages) {
                $nextpage = $numPage + 1;
                $this->result['nextpage'] = $nextpage;
            } else {
                $this->result['nextpage'] = 'no';
            }

            $this->result['pages_menu'] = $html;
            $this->result['total_additional_param_' . $entity] = $numRows;
            $this->result['num_pages'] = $numPages;
        } else {
            $this->result['pages_menu'] = 'no';
        }
    }


    public function additionalParamsAdd($array, $entity)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if ($entity === 'param_add') {
            $groupId = 0;
        }

        if ($entity === 'param_add' && isset($array['group_id']) && intval($array['group_id']) > 0) {
            $groupId = intval($array['group_id']);
        }

        if ($entity === 'param_add' && $groupId < 1) {
            $this->error = 'empty_data';

            return;
        }

        if ($entity == 'group_add') {
            $sql = "SELECT id,type_name,type_description FROM " . PREFIX . "_s_add_par_g_types ORDER BY id ASC";
        } else {
            $sql = "SELECT id,group_name FROM " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " WHERE id ='" . $groupId . "'";
        }

        $result = $this->db->Execute($sql);

        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }

        if ($row_exist < 1) {
            $this->error = ($entity == 'group_add') ? 'empty_group_types' : 'group_not_found';

            return;
        }

        if ($entity == 'group_add') {
            while (!$result->EOF) {
                $id = intval($result->fields['0']);
                $type_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $type_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $group_types_all[] = [
                    "id"               => $id,
                    "type_name"        => $type_name,
                    "type_description" => $type_description,
                ];
                $result->MoveNext();
            }
            $this->result['item_additional_params_group_types_all'] = $group_types_all;
        } else {
            $this->result = [
                'item_additional_params_group_id'   => intval($result->fields['0']),
                'item_additional_params_group_name' => $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['1'])
                ),
            ];
        }
    }


    public function additionalParamsGroupAddInDb($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if (!empty($array)) {
            $keys = [
                'item_additional_param_group_name',
            ];
            $keys2 = [
                'item_additional_param_group_status',
                'item_additional_param_group_type_id',
            ];
            foreach ($array as $key => $value) {

                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }

                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }

        if (empty($data_array['item_additional_param_group_name']) || !isset($data_array['item_additional_param_group_status'])
            || empty($data_array['item_additional_param_group_type_id'])
        ) {
            $this->error = 'not_all_data';

            return;
        }

        $data_array['item_additional_param_group_name'] = $this->registry->main_class->processing_data($data_array['item_additional_param_group_name']);
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_s_add_par_groups_id_" . $this->registry->sitelang, 'id');
        $sql = "INSERT INTO " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
               . "group_name,group_type_id,group_status) VALUES (" . $sequence_array['sequence_value_string'] . $data_array['item_additional_param_group_name'] . ",'"
               . $data_array['item_additional_param_group_type_id'] . "','" . $data_array['item_additional_param_group_status'] . "')";
        $result = $this->db->Execute($sql);

        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop|additional_param_groups");
        $this->result['shop_message'] = 'addition_params_group_add_ok';
    }


    public function additionalParamsParamAddInDb($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if (!empty($array)) {
            $keys = [
                'item_additional_param_value',
            ];
            $keys2 = [
                'item_additional_param_group_id',
                'item_additional_param_status',
            ];
            foreach ($array as $key => $value) {

                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }

                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }

        if (empty($data_array['item_additional_param_value']) || !isset($data_array['item_additional_param_status'])
            || empty($data_array['item_additional_param_group_id'])
        ) {
            $this->error = 'not_all_data';

            return;
        }

        $data_array['item_additional_param_value'] = $this->registry->main_class->processing_data($data_array['item_additional_param_value']);
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_s_add_params_id_" . $this->registry->sitelang, 'id');
        $sql = "INSERT INTO " . PREFIX . "_s_add_params_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
               . "param_value,param_group_id,param_status) VALUES (" . $sequence_array['sequence_value_string'] . $data_array['item_additional_param_value'] . ",'"
               . $data_array['item_additional_param_group_id'] . "','" . $data_array['item_additional_param_status'] . "')";
        $result = $this->db->Execute($sql);

        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop");
        $this->result = [
            'shop_message'                    => 'addition_params_param_added',
            'item_additional_params_group_id' => $data_array['item_additional_param_group_id'],
        ];
    }


    public function additionalParamsGroupEdit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if (isset($array['group_id'])) {
            $array['group_id'] = intval($array['group_id']);
        }

        if (empty($array['group_id']) || $array['group_id'] < 1) {
            $this->error = 'empty_data';

            return;
        }

        $sql = "SELECT a.id,a.type_name,a.type_description,b.id,b.group_name,b.group_type_id,b.group_status FROM " . PREFIX . "_s_add_par_g_types a LEFT OUTER JOIN "
               . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " b ON a.id=b.group_type_id and b.id='" . $array['group_id'] . "' ORDER BY a.id ASC";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        $row_exist1 = 0;
        $row_exist2 = 0;

        if (isset($result->fields['0'])) {
            $row_exist1 = intval($result->fields['0']);
        }

        if (isset($result->fields['3'])) {
            $row_exist2 = intval($result->fields['3']);
        }

        if ($row_exist1 < 1 && $row_exist2 < 1) {

            if ($row_exist1 < 1) {
                $this->error = 'empty_group_types';
            } else {
                $this->error = 'group_not_found';
            }

            return;
        }

        while (!$result->EOF) {

            if (empty($this->result['item_additional_param_group_data']) && intval($result->fields['3']) > 0) {
                $group_id = intval($result->fields['3']);
                $group_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                $group_type_id = intval($result->fields['5']);
                $group_status = intval($result->fields['6']);
                $this->result['item_additional_param_group_data'] = [
                    "id"            => $group_id,
                    "group_name"    => $group_name,
                    "group_type_id" => $group_type_id,
                    "group_status"  => $group_status,
                ];
            }

            if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
                $id = intval($result->fields['0']);
                $type_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $type_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $this->result['item_additional_params_group_types_all'][] = [
                    "id"               => $id,
                    "type_name"        => $type_name,
                    "type_description" => $type_description,
                ];
            }

            $result->MoveNext();
        }

        if (empty($this->result['item_additional_param_group_data'])) {
            $this->error = 'group_not_found';

            return;
        }

        if (empty($this->result['item_additional_params_group_types_all'])) {
            $this->error = 'empty_group_types';

            return;
        }

        $this->result['item_additional_param_group_edit'] = true;
    }


    public function additionalParamsParamEdit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if (isset($array['group_id'])) {
            $array['group_id'] = intval($array['group_id']);
        }

        if (isset($array['param_id'])) {
            $array['param_id'] = intval($array['param_id']);
        }

        if (empty($array['group_id']) || empty($array['param_id']) || $array['group_id'] < 1 || $array['param_id'] < 1) {
            $this->error = 'empty_data';

            return;
        }

        $sql = "SELECT a.id,a.group_name,b.id,b.param_value,b.param_status FROM " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " a "
               . "," . PREFIX . "_s_add_params_" . $this->registry->sitelang . " b WHERE  a.id=b.param_group_id and a.id = '" . $array['group_id'] . "' and "
               . "b.id ='" . $array['param_id'] . "'";
        $result = $this->db->Execute($sql);

        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }

        $row_exist = 0;

        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        }

        if ($row_exist < 1) {
            $this->error = 'param_not_found';

            return;
        }

        $this->result = [
            'item_additional_params_group_id'   => intval($result->fields['0']),
            'item_additional_params_group_name' => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1'])),
            'item_additional_param_data'        => [
                'param_id'     => intval($result->fields['2']),
                'param_value'  => $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3'])),
                'param_status' => intval($result->fields['4']),
            ],
            'additional_param_edit_mode'        => true,
        ];
    }


    public function additionalParamsGroupEditInDb($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if (!empty($array)) {
            $keys = [
                'item_additional_param_group_name',
            ];
            $keys2 = [
                'item_additional_param_group_id',
                'item_additional_param_group_type_id',
                'item_additional_param_group_status',
            ];
            foreach ($array as $key => $value) {

                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }

                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }

        if (empty($data_array['item_additional_param_group_name'])
            || empty($data_array['item_additional_param_group_id'])
            || empty($data_array['item_additional_param_group_type_id'])
            || !isset($data_array['item_additional_param_group_status'])
        ) {
            $this->error = 'not_all_data';

            return;
        }

        $data_array['item_additional_param_group_name'] = $this->registry->main_class->processing_data($data_array['item_additional_param_group_name']);
        $sql = "UPDATE " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " SET group_name = " . $data_array['item_additional_param_group_name']
               . ", group_type_id = '" . $data_array['item_additional_param_group_type_id'] . "',group_status = '" . $data_array['item_additional_param_group_status']
               . "' WHERE id = '" . $data_array['item_additional_param_group_id'] . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();

        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_save';

            return;
        }

        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop");
        $this->result['shop_message'] = 'group_edit_done';
    }


    public function additionalParamsParamEditInDb($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;

        if (!empty($array)) {
            $keys = [
                'item_additional_param_value',
            ];
            $keys2 = [
                'item_additional_param_group_id',
                'item_additional_param_id',
                'item_additional_param_status',
            ];
            foreach ($array as $key => $value) {

                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }

                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }

        if (empty($data_array['item_additional_param_value'])
            || empty($data_array['item_additional_param_group_id'])
            || empty($data_array['item_additional_param_id'])
            || !isset($data_array['item_additional_param_status'])
        ) {
            $this->error = 'not_all_data';

            return;
        }

        $data_array['item_additional_param_value'] = $this->registry->main_class->processing_data($data_array['item_additional_param_value']);
        $sql = "UPDATE " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " SET param_value = " . $data_array['item_additional_param_value']
               . ",param_status = '" . $data_array['item_additional_param_status'] . "' "
               . "WHERE id = '" . $data_array['item_additional_param_id'] . "' and param_group_id = '" . $data_array['item_additional_param_group_id'] . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();

        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'not_save';

            return;
        }

        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop");
        $this->result = [
            'shop_message'                    => 'addition_params_param_edited',
            'item_additional_params_group_id' => $data_array['item_additional_param_group_id'],
        ];
    }


    public function additionalParamsGroupStatus($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = "";

        if (isset($array['get_action'])) {
            $action = trim($array['get_action']);
        }

        if (!isset($array['get_action']) && isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }

        $groupId = 0;

        if (isset($array['get_group_id'])) {
            $groupId = intval($array['get_group_id']);
        }

        if ($groupId == 0 && isset($array['post_group_id']) && is_array($array['post_group_id']) && count($array['post_group_id']) > 0) {
            for ($i = 0, $size = count($array['post_group_id']); $i < $size; ++$i) {
                $groupsIds[$i] = intval($array['post_group_id'][$i]);

                if ($i == 0) {
                    $groupId = "'" . intval($array['post_group_id'][$i]) . "'";
                } else {
                    $groupId .= ",'" . intval($array['post_group_id'][$i]) . "'";
                }

            }
        } else {
            $groupsIds[0] = $groupId;
            $groupId = "'" . $groupId . "'";
        }

        $action = $this->registry->main_class->format_striptags($action);

        if (!isset($groupId) || $action == "" || $groupId == "'0'") {
            $this->error = 'empty_data';

            return;
        }

        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " SET group_status = '1' WHERE id in (" . $groupId . ")";
            $result = $this->db->Execute($sql);

            if ($result) {
                $numResult = $this->db->Affected_Rows();
            }

        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " SET group_status = '0' WHERE id IN (" . $groupId . ")";
            $result = $this->db->Execute($sql);

            if ($result) {
                $numResult = $this->db->Affected_Rows();
            }

        } elseif ($action == "confirm_delete") {
            $sql = "SELECT (SELECT count(a.id) FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " a WHERE a.param_group_id IN (" . $groupId . ")) "
                   . "as total_params,"
                   . "(SELECT count(*) FROM (SELECT b.order_id FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " b "
                   . "WHERE b.order_item_param_group_id IN (" . $groupId . ") GROUP BY b.order_id) as data2) as total_orders, "
                   . "(SELECT count(*) FROM (SELECT c.item_id FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " c "
                   . "WHERE c.item_param_id IN (SELECT id FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " WHERE param_group_id IN (" . $groupId . ")) "
                   . "GROUP BY c.item_id) as data3) as total_items " . $this->registry->main_class->check_db_need_from_clause();
            $result = $this->db->Execute($sql);

            if (!$result) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }

            $totalParams = 0;
            $totalOrders = 0;
            $totalItems = 0;

            if (isset($result->fields['0']) || isset($result->fields['1']) || isset($result->fields['2'])) {
                while (!$result->EOF) {

                    if (!empty($result->fields['0']) && intval($result->fields['0']) > 0) {
                        $totalParams = intval($result->fields['0']);
                    }

                    if (!empty($result->fields['1']) && intval($result->fields['1']) > 0) {
                        $totalOrders = intval($result->fields['1']);
                    }

                    if (!empty($result->fields['2']) && intval($result->fields['2']) > 0) {
                        $totalItems = intval($result->fields['2']);
                    }

                    $result->MoveNext();
                }
            }

            $this->result = [
                'shop_message'                       => 'group_confirm_delete',
                'total_additional_params'            => $totalParams,
                'total_order_item_additional_params' => $totalOrders,
                'total_item_additional_params'       => $totalItems,
                'delete_groups_ids'                  => $groupsIds,
            ];

            return;
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " WHERE order_item_param_group_id IN (" . $groupId . ")";
            $result1 = $this->db->Execute($sql1);

            if ($result1 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }

            $sql2 = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . "  WHERE item_param_id IN "
                    . "(SELECT id FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " WHERE param_group_id IN (" . $groupId . "))";
            $result2 = $this->db->Execute($sql2);

            if ($result2 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }

            $sql3 = "DELETE FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " WHERE param_group_id IN (" . $groupId . ")";
            $result3 = $this->db->Execute($sql3);

            if ($result3 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }

            $sql4 = "DELETE FROM " . PREFIX . "_s_add_par_groups_" . $this->registry->sitelang . " WHERE id IN (" . $groupId . ")";
            $result = $this->db->Execute($sql4);

            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            } else {
                $numResult = $this->db->Affected_Rows();
            }

            if (in_array(false, [$result1, $result2, $result3])) {
                $result = false;
            }

            $this->db->CompleteTrans();
        } else {
            $this->error = 'unknown_action';

            return;
        }

        if ($result === false) {

            if ($action != "delete") {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }

            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $numResult == 0) {
            $this->error = 'not_save';

            return;
        }

        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop");
        $this->result['shop_message'] = 'param_group_' . $action;
    }


    public function additionalParamsParamStatus($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = "";

        if (isset($array['get_action'])) {
            $action = trim($array['get_action']);
        }

        if (!isset($array['get_action']) && isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }

        $key = 'param_id';
        $paramId = 0;
        $groupId = 0;

        if (isset($array['group_id']) && intval($array['group_id']) > 0) {
            $groupId = intval($array['group_id']);
        }

        if (isset($array['get_' . $key])) {
            $paramId = intval($array['get_' . $key]);
        }

        if ($paramId == 0 && isset($array['post_' . $key]) && is_array($array['post_' . $key]) && count($array['post_' . $key]) > 0) {
            for ($i = 0, $size = count($array['post_' . $key]); $i < $size; ++$i) {
                $paramsIds[$i] = intval($array['post_' . $key][$i]);

                if ($i == 0) {
                    $paramId = "'" . intval($array['post_' . $key][$i]) . "'";
                } else {
                    $paramId .= ",'" . intval($array['post_' . $key][$i]) . "'";
                }

            }
        } else {
            $paramsIds[0] = $paramId;
            $paramId = "'" . $paramId . "'";
        }

        $action = $this->registry->main_class->format_striptags($action);

        if (!isset($paramId) || $action == "" || $paramId == "'0'" || empty($groupId)) {
            $this->error = 'empty_data';

            return;
        }

        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " SET param_status = '1' WHERE id in (" . $paramId . ")";
            $result = $this->db->Execute($sql);

            if ($result) {
                $numResult = $this->db->Affected_Rows();
            }

        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " SET param_status = '0' WHERE id IN (" . $paramId . ")";
            $result = $this->db->Execute($sql);

            if ($result) {
                $numResult = $this->db->Affected_Rows();
            }

        } elseif ($action == "confirm_delete") {
            $sql = "SELECT (SELECT count(a.id) FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " a "
                   . "WHERE a.param_group_id = '" . $groupId . "' and a.id IN (" . $paramId . ")) as total_params,"
                   . "(SELECT count(*) FROM (SELECT b.order_id FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " b WHERE "
                   . "b.order_item_param_group_id='" . $groupId . "' and b.order_item_param_id IN (" . $paramId . ") GROUP BY b.order_id) as data2) as total_orders,"
                   . "(SELECT count(*) FROM (SELECT c.item_id FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . " c "
                   . "WHERE c.item_param_id IN (SELECT id FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " "
                   . "WHERE param_group_id='" . $groupId . "' and id IN (" . $paramId . ")) "
                   . "GROUP BY c.item_id) as data3) as total_items " . $this->registry->main_class->check_db_need_from_clause();
            $result = $this->db->Execute($sql);

            if (!$result) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }

            $totalParams = 0;
            $totalOrders = 0;
            $totalItems = 0;

            if (isset($result->fields['0']) || isset($result->fields['1']) || isset($result->fields['2'])) {
                while (!$result->EOF) {

                    if (!empty($result->fields['0']) && intval($result->fields['0']) > 0) {
                        $totalParams = intval($result->fields['0']);
                    }

                    if (!empty($result->fields['1']) && intval($result->fields['1']) > 0) {
                        $totalOrders = intval($result->fields['1']);
                    }

                    if (!empty($result->fields['2']) && intval($result->fields['2']) > 0) {
                        $totalItems = intval($result->fields['2']);
                    }

                    $result->MoveNext();
                }
            }

            $this->result = [
                'shop_message'                       => 'additional_params_confirm_delete',
                'total_additional_params'            => $totalParams,
                'total_order_item_additional_params' => $totalOrders,
                'total_item_additional_params'       => $totalItems,
                'delete_params_ids'                  => $paramsIds,
                'delete_group_id'                    => $groupId,
            ];

            return;
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM " . PREFIX . "_s_order_i_params_" . $this->registry->sitelang . " WHERE "
                    . "order_item_param_group_id='" . $groupId . "' and order_item_param_id IN (" . $paramId . ")";
            $result1 = $this->db->Execute($sql1);

            if ($result1 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }

            $sql2 = "DELETE FROM " . PREFIX . "_s_item_add_params_" . $this->registry->sitelang . "  WHERE item_param_id IN ("
                    . "SELECT id FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " WHERE param_group_id='" . $groupId . "' and id IN (" . $paramId . ")"
                    . ")";
            $result2 = $this->db->Execute($sql2);

            if ($result2 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }

            $sql3 = "DELETE FROM " . PREFIX . "_s_add_params_" . $this->registry->sitelang . " WHERE param_group_id='" . $groupId . "' and id IN (" . $paramId . ")";
            $result = $this->db->Execute($sql3);

            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            } else {
                $numResult = $this->db->Affected_Rows();
            }

            if (in_array(false, [$result1, $result2])) {
                $result = false;
            }

            $this->db->CompleteTrans();
        } else {
            $this->error = 'unknown_action';

            return;
        }

        if ($result === false) {

            if ($action != "delete") {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }

            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $numResult == 0) {
            $this->error = 'not_save';

            return;
        }

        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|shop");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|shop");
        $this->result = [
            'shop_message'                    => 'param_' . $action,
            'item_additional_params_group_id' => $groupId,
        ];
    }
}
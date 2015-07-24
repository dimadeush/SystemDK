<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class admin_shop extends model_base {


    private $img_array;
    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
            $cache = "home";
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
        } elseif(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "subcateg";
            } else {
                $cache = "categ";
                $data_array['categ'] = 0;
            }
        } else {
            $this->error = 'empty_data';
            return;
        }
        if(isset($data_array['num_page']) and intval($data_array['num_page']) != 0) {
            $num_page = intval($data_array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if($cache == "home") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_catalogs_".$this->registry->sitelang;
            $sql = "SELECT catalog_id,catalog_name,catalog_date,catalog_show FROM ".PREFIX."_catalogs_".$this->registry->sitelang." order by catalog_date ASC";
        } elseif($cache == "categ") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.category_id,a.category_name,a.category_date,a.category_show,c.catalog_id,c.catalog_name FROM ".PREFIX."_categories_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.cat_catalog_id=c.catalog_id WHERE c.catalog_id = ".$data_array['cat']." order by a.category_date ASC";
        } elseif($cache == "subcateg") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id = ".$data_array['categ'];
            $sql = "SELECT a.subcategory_id,a.subcategory_name,a.subcategory_date,a.subcategory_show,c.category_id,c.category_name,d.catalog_id,d.catalog_name FROM ".PREFIX."_subcategories_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c ON a.subcategory_cat_id=c.category_id RIGHT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." d ON c.cat_catalog_id=d.catalog_id WHERE c.category_id = ".$data_array['categ']." and d.catalog_id = ".$data_array['cat']." order by a.subcategory_date ASC";
        }
        $result = $this->db->Execute($sql0);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        }
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'show_sql_error_'.$cache;
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist == 0 and $num_page > 1) {
            $this->error = 'unknown_page';
            return;
        }
        if($cache == "categ" and isset($result->fields['4']) and intval($result->fields['4']) > 0) {
            $catalog_id = intval($result->fields['4']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
        } elseif($cache == "subcateg" and isset($result->fields['4']) and intval($result->fields['4']) > 0) {
            $category_id = intval($result->fields['4']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            $catalog_id = intval($result->fields['6']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
        } elseif($cache != "home") {
            if($cache == "categ") {
                $this->error = 'show_not_found_catalog';
            } elseif($cache == "subcateg") {
                $this->error = 'show_not_found_categ';
            }
            return;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_date = date("d.m.Y H:i",intval($result->fields['2']));
                $item_show = intval($result->fields['3']);
                $items_all[] = array(
                    "item_id" => $item_id,
                    "item_name" => $item_name,
                    "item_date" => $item_date,
                    "item_show" => $item_show
                );
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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


    public function shop_edit($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
                $cache = "subcateg";
                if($data_array['subcateg'] != 0) {
                    $itemid = $data_array['subcateg'];
                } else {
                    $itemid = 0;
                }
            } elseif(isset($data_array['categ'])) {
                $cache = "categ";
                if($data_array['categ'] != 0) {
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
        if($itemid == 0) {
            $this->error = 'edit_no_'.$cache;
            return;
        }
        if($cache == "cat") {
            $sql = "SELECT a.catalog_id,a.catalog_img,a.catalog_name,a.catalog_description,a.catalog_position,a.catalog_show,a.catalog_date,a.catalog_meta_title,a.catalog_meta_keywords,a.catalog_meta_description FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a WHERE a.catalog_id = ".$itemid;
        } elseif($cache == "categ") {
            $sql = "SELECT b.category_id,b.category_img,b.category_name,b.category_description,b.category_position,b.category_show,b.category_date,b.category_meta_title,b.category_meta_keywords,b.category_meta_description,b.cat_catalog_id,a.catalog_id,a.catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and a.catalog_id = ".$data_array['cat']." and b.category_id = ".$itemid." order by (case WHEN b.category_id IS NULL THEN 0 ELSE b.category_id END) DESC,a.catalog_name ASC";
        } elseif($cache == "subcateg") {
            $sql = "SELECT c.subcategory_id,c.subcategory_img,c.subcategory_name,c.subcategory_description,c.subcategory_position,c.subcategory_show,c.subcategory_date,c.subcategory_meta_title,c.subcategory_meta_keywords,c.subcategory_meta_description,c.subcategory_cat_id,a.catalog_id,a.catalog_name,b.category_id,b.category_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a INNER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b ON a.catalog_id=b.cat_catalog_id and a.catalog_id = ".$data_array['cat']." LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." c ON b.category_id=c.subcategory_cat_id and c.subcategory_id = ".$itemid." and b.category_id = ".$data_array['categ']." order by (case WHEN c.subcategory_id IS NULL THEN 0 ELSE c.subcategory_id END) DESC,b.category_name ASC";
        }
        $result = $this->db->Execute($sql);
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_sql_error_'.$cache;
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->error = 'edit_not_found_'.$cache;
            return;
        }
        require_once('../modules/shop/shop_config.inc');
        while(!$result->EOF) {
            if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
                $item_id = intval($result->fields['0']);
                $item_img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $item_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $item_position = intval($result->fields['4']);
                $item_show = intval($result->fields['5']);
                $item_date = intval($result->fields['6']);
                $item_date2 = date("d.m.Y",$item_date);
                $item_hour = date("H",$item_date);
                $item_minute = date("i",$item_date);
                $item_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                $item_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                $item_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                if($cache == "categ" or $cache == "subcateg") {
                    if($cache != "subcateg") {
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
                if($cache == "subcateg") {
                    $in_category_id = intval($result->fields['10']);
                    $item_category_id = intval($result->fields['13']);
                    $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                } else {
                    $in_category_id = "no";
                    $item_category_id = "no";
                    $item_category_name = "no";
                }
                $item_all[] = array(
                    "item_id" => $item_id,
                    "item_img" => $item_img,
                    "item_imgpath" => SYSTEMDK_SHOP_IMAGE_PATH,
                    "item_name" => $item_name,
                    "item_description" => $item_description,
                    "item_position" => $item_position,
                    "item_show" => $item_show,
                    "item_date" => $item_date2,
                    "item_hour" => $item_hour,
                    "item_minute" => $item_minute,
                    "item_meta_title" => $item_meta_title,
                    "item_meta_keywords" => $item_meta_keywords,
                    "item_meta_description" => $item_meta_description,
                    "in_catalog_id" => $in_catalog_id,
                    "item_catalog_id" => $item_catalog_id,
                    "item_catalog_name" => $item_catalog_name,
                    "in_category_id" => $in_category_id,
                    "item_category_id" => $item_category_id,
                    "item_category_name" => $item_category_name
                );
            }
            if(($cache == "categ" or $cache == "subcateg") and isset($result->fields['11']) and intval($result->fields['11']) > 0) {
                $catalog_id = intval($result->fields['11']);
                $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                $catalog_all_now = array("catalog_id" => $catalog_id,"catalog_name" => $catalog_name);
                if(!isset($catalogs_all) or (isset($catalogs_all) and !in_array($catalog_all_now,$catalogs_all))) {
                    $catalogs_all[] = array("catalog_id" => $catalog_id,"catalog_name" => $catalog_name);
                }
            }
            if($cache == "subcateg" and isset($result->fields['13']) and intval($result->fields['13']) > 0) {
                $category_id = intval($result->fields['13']);
                $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['14']));
                $categories_all[] = array("category_id" => $category_id,"category_name" => $category_name);
            }
            $result->MoveNext();
        }
        $this->result['item_all'] = $item_all;
        $this->result['adminmodules_shop_item_id'] = $item_id;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
        if(isset($catalogs_all)) {
            $this->result['catalogs_all'] = $catalogs_all;
        }
        if(isset($categories_all)) {
            $this->result['categories_all'] = $categories_all;
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['include_jquery'] = 'yes';
        $this->result['include_jquery_data'] = "dateinput";
    }


    public function shop_edit_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(!isset($array['item_hour'])) {
            $data_array['item_hour'] = 0;
        }
        if(!isset($array['item_minute'])) {
            $data_array['item_minute'] = 0;
        }
        if(isset($data_array['depth']) and $data_array['depth'] == 1) {
            $cache = "categ";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "subcateg";
        } else {
            $cache = "cat";
        }
        if((!isset($array['item_id']) or !isset($array['item_name']) or !isset($array['item_date']) or !isset($array['item_hour']) or !isset($array['item_minute']) or !isset($array['item_action_img']) or !isset($array['item_description']) or !isset($array['item_show']) or !isset($array['item_meta_title']) or !isset($array['item_meta_keywords']) or !isset($array['item_meta_description'])) or ($data_array['item_id'] == 0 or $data_array['item_name'] == "" or $data_array['item_date'] == "" or ($cache == "categ" and (!isset($data_array['item_catalog']) or !isset($data_array['item_catalog_old']) or $data_array['item_catalog'] == 0 or $data_array['item_catalog_old'] == 0)) or ($cache == "subcateg" and (!isset($data_array['item_category']) or !isset($data_array['item_category_old']) or $data_array['item_category'] == 0 or $data_array['item_category_old'] == 0)))) {
            $this->error = 'edit_not_all_data_'.$cache;
            return;
        }
        $data_array['item_action_img'] = $this->registry->main_class->format_striptags($data_array['item_action_img']);
        require_once('../modules/shop/shop_config.inc');
        if($data_array['item_action_img'] == 'del') {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            if(@unlink("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$data_array['item_img']) or !is_file("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$data_array['item_img'])) {
                $data_array['item_img'] = 'NULL';
            } else {
                $this->error = 'edit_del_img_'.$cache;
                return;
            }
        } elseif($data_array['item_action_img'] == 'new_file') {
            if(isset($_FILES['item_img']['name']) and trim($_FILES['item_img']['name']) !== '') {
                if(is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if($_FILES['item_img']['size'] > SYSTEMDK_SHOP_IMAGE_MAXSIZE) {
                        $this->error = 'edit_img_max_size_'.$cache;
                        return;
                    }
                    if(($_FILES['item_img']['type'] == "image/gif") or ($_FILES['item_img']['type'] == "image/jpg") or ($_FILES['item_img']['type'] == "image/jpeg") or ($_FILES['item_img']['type'] == "image/pjpeg") or ($_FILES['item_img']['type'] == "image/png") or ($_FILES['item_img']['type'] == "image/bmp")) {
                        $this->registry->main_class->set_locale();
                        if(basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $data_array['item_img'] = date("dmY",time())."_".basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $data_array['item_img'] = "";
                        }
                        if(file_exists($data_array['item_img']) and $data_array['item_img'] != "") {
                            @unlink("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$data_array['item_img']);
                        }
                        if($data_array['item_img'] == "" or !@move_uploaded_file($_FILES['item_img']['tmp_name'],"../".SYSTEMDK_SHOP_IMAGE_PATH."/".$data_array['item_img'])) {
                            $this->error = 'edit_img_not_upload_'.$cache;
                            return;
                        }
                        $item_size2 = @getimagesize("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$data_array['item_img']);
                        if(($item_size2[0] > SYSTEMDK_SHOP_IMAGE_MAXWIDTH) or ($item_size2[1] > SYSTEMDK_SHOP_IMAGE_MAXHEIGHT)) {
                            @unlink("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$data_array['item_img']);
                            $this->error = 'edit_img_not_eq_'.$cache;
                            return;
                        }
                    } else {
                        $this->error = 'edit_img_not_eq_type_'.$cache;
                        return;
                    }
                } else {
                    $this->error = 'edit_img_upload_'.$cache;
                    return;
                }
                $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img'],'need');
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
        $data_array['item_date'] = $data_array['item_date']." ".sprintf("%02d",$data_array['item_hour']).":".sprintf("%02d",$data_array['item_minute']);
        $data_array['item_date'] = $this->registry->main_class->time_to_unix($data_array['item_date']);
        foreach($data_array as $key => $value) {
            $keys = array(
                'item_description',
                'item_meta_title',
                'item_meta_keywords',
                'item_meta_description'
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
        if($cache == "cat") {
            $sql = "UPDATE ".PREFIX."_catalogs_".$this->registry->sitelang." SET catalog_img = ".$data_array['item_img'].",catalog_name = ".$data_array['item_name'].",catalog_description = ".$data_array['item_description'].",catalog_show = '".$data_array['item_show']."',catalog_date = '".$data_array['item_date']."',catalog_meta_title = ".$data_array['item_meta_title'].",catalog_meta_keywords = ".$data_array['item_meta_keywords'].",catalog_meta_description = ".$data_array['item_meta_description']." WHERE catalog_id = ".$data_array['item_id'];
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        } elseif($cache == "categ") {
            $this->db->StartTrans();
            $sql_add = false;
            if($data_array['item_catalog'] != $data_array['item_catalog_old']) {
                $sql0 = "SELECT category_position FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id = ".$data_array['item_catalog_old']." and category_id = ".$data_array['item_id'];
                $result0 = $this->db->Execute($sql0);
                if($result0) {
                    if(isset($result0->fields['0'])) {
                        $data_array['item_position'] = intval($result0->fields['0']);
                        $sql1 = "UPDATE ".PREFIX."_categories_".$this->registry->sitelang." SET category_position = category_position-1 WHERE category_position > 0 and cat_catalog_id = ".$data_array['item_catalog_old']." and category_position > ".$data_array['item_position'];
                        $result1 = $this->db->Execute($sql1);
                        if($result1 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        unset($data_array['item_position']);
                        $sql2 = "SELECT max(category_position)+1 FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id = ".$data_array['item_catalog'];
                        $result2 = $this->db->Execute($sql2);
                        if($result2) {
                            if(isset($result2->fields['0'])) {
                                $data_array['item_position'] = intval($result2->fields['0']);
                            }
                        }
                        if(!isset($data_array['item_position'])) {
                            $data_array['item_position'] = 1;
                        }
                        if($result2 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $sql_add = ",category_position = '".$data_array['item_position']."'";
                    } else {
                        $this->db->CompleteTrans();
                        $this->error = 'edit_sql_error_'.$cache;
                        return;
                    }
                }
                if($result0 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
            $sql = "UPDATE ".PREFIX."_categories_".$this->registry->sitelang." SET category_img = ".$data_array['item_img'].",category_name = ".$data_array['item_name'].",cat_catalog_id = ".$data_array['item_catalog'].",category_description = ".$data_array['item_description'].$sql_add.",category_show = '".$data_array['item_show']."',category_date = '".$data_array['item_date']."',category_meta_title = ".$data_array['item_meta_title'].",category_meta_keywords = ".$data_array['item_meta_keywords'].",category_meta_description = ".$data_array['item_meta_description']." WHERE category_id = ".$data_array['item_id'];
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if((isset($result0) and $result0 === false) or (isset($result1) and $result1 === false) or (isset($result2) and $result2 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } elseif($cache == "subcateg") {
            $this->db->StartTrans();
            $sql_add = false;
            if($data_array['item_category'] != $data_array['item_category_old']) {
                $sql0 = "SELECT subcategory_position FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id = ".$data_array['item_category_old']." and subcategory_id = ".$data_array['item_id'];
                $result0 = $this->db->Execute($sql0);
                if($result0) {
                    if(isset($result0->fields['0'])) {
                        $data_array['item_position'] = intval($result0->fields['0']);
                        $sql1 = "UPDATE ".PREFIX."_subcategories_".$this->registry->sitelang." SET subcategory_position = subcategory_position-1 WHERE subcategory_position > 0 and subcategory_cat_id = ".$data_array['item_category_old']." and subcategory_position > ".$data_array['item_position'];
                        $result1 = $this->db->Execute($sql1);
                        if($result1 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        unset($data_array['item_position']);
                        $sql2 = "SELECT max(subcategory_position)+1 FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id = ".$data_array['item_category'];
                        $result2 = $this->db->Execute($sql2);
                        if($result2) {
                            if(isset($result2->fields['0'])) {
                                $data_array['item_position'] = intval($result2->fields['0']);
                            }
                        }
                        if(!isset($data_array['item_position'])) {
                            $data_array['item_position'] = 1;
                        }
                        if($result2 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $sql_add = ",subcategory_position = '".$data_array['item_position']."'";
                    } else {
                        $this->db->CompleteTrans();
                        $this->error = 'edit_sql_error_'.$cache;
                        return;
                    }
                }
                if($result0 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
            $sql = "UPDATE ".PREFIX."_subcategories_".$this->registry->sitelang." SET subcategory_img = ".$data_array['item_img'].",subcategory_name = ".$data_array['item_name'].",subcategory_cat_id = ".$data_array['item_category'].",subcategory_description = ".$data_array['item_description'].$sql_add.",subcategory_show = '".$data_array['item_show']."',subcategory_date = '".$data_array['item_date']."',subcategory_meta_title = ".$data_array['item_meta_title'].",subcategory_meta_keywords = ".$data_array['item_meta_keywords'].",subcategory_meta_description = ".$data_array['item_meta_description']." WHERE subcategory_id = ".$data_array['item_id'];
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if((isset($result0) and $result0 === false) or (isset($result1) and $result1 === false) or (isset($result2) and $result2 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        }
        if($result === false) {
            $this->error = 'edit_sql_error_'.$cache;
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'edit_ok_'.$cache;
            return;
        } else {
            if($cache == "cat") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|add");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_id']);
            } elseif($cache == "categ") {
                if($data_array['item_catalog'] != $data_array['item_catalog_old']) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']."|categ".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog_old']."|categ".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|add|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                if(intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                }
            } elseif($cache == "subcateg") {
                if($data_array['item_category'] != $data_array['item_category_old']) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category']."|subcateg".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                if(intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                }
            }
            $this->result = 'edit_done_'.$cache;
        }
    }


    public function shop_add($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
            } else {
                $data_array['categ'] = 0;
                $cache = "categ";
            }
        } else {
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $cache = "cat";
        }
        if($cache == "categ") {
            $sql = "SELECT catalog_id,catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." order by catalog_name ASC";
        } elseif($cache == "subcateg") {
            $sql = "SELECT a.category_id,a.category_name,b.catalog_id,b.catalog_name FROM ".PREFIX."_categories_".$this->registry->sitelang." a INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." b ON a.cat_catalog_id=b.catalog_id WHERE b.catalog_id = ".$data_array['cat']." order by a.category_name ASC";
        } else {
            $sql = "no";
        }
        if($sql !== "no") {
            $result = $this->db->Execute($sql);
            if(!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->error = 'add_sql_error_'.$cache;
                $this->error_array = $error;
                return;
            }
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist < 1) {
                if($cache == "categ") {
                    $this->error = 'add_no_catalogs';
                } elseif($cache == "subcateg") {
                    $this->error = 'add_no_categs';
                }
                return;
            }
            while(!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_all[] = array("item_id" => $item_id,"item_name" => $item_name);
                if($item_id == $data_array['cat'] and $cache == "categ") {
                    $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                }
                if(isset($result->fields['2']) and !isset($catalog_name) and $cache == "subcateg") {
                    $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                }
                if($item_id == $data_array['categ'] and !isset($category_name) and $cache == "subcateg") {
                    $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                }
                $result->MoveNext();
            }
            $this->result['item_all'] = $item_all;
            if(isset($catalog_name)) {
                $this->result['adminmodules_shop_cat_name'] = $catalog_name;
            }
            if(isset($category_name)) {
                $this->result['adminmodules_shop_categ_name'] = $category_name;
            }
            if((!isset($catalog_name) and ($cache == "categ" or $cache == "subcateg")) or (!isset($category_name) and $cache == "subcateg")) {
                if($cache == "categ") {
                    $this->error = 'add_not_found_catalog';
                } elseif($cache == "subcateg") {
                    $this->error = 'add_not_found_categ';
                }
                return;
            }
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
    }


    public function shop_add_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "subcateg";
        } else {
            $cache = "cat";
        }
        if((!isset($array['item_name']) or !isset($array['item_description']) or !isset($array['item_show']) or !isset($array['item_meta_title']) or !isset($array['item_meta_keywords']) or !isset($array['item_meta_description'])) or ($data_array['item_name'] == "" or ($cache == "categ" and (!isset($data_array['item_catalog']) or $data_array['item_catalog'] == 0)) or ($cache == "subcateg" and (!isset($data_array['item_category']) or $data_array['item_category'] == 0)))) {
            $this->error = 'add_not_all_data_'.$cache;
            return;
        }
        require_once('../modules/shop/shop_config.inc');
        if($this->registry->main_class->format_striptags($_FILES['item_img']['name']) != "") {
            if(isset($_FILES['item_img']['name']) and trim($_FILES['item_img']['name']) !== '') {
                if(is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if($_FILES['item_img']['size'] > SYSTEMDK_SHOP_IMAGE_MAXSIZE) {
                        $this->error = 'add_img_max_size_'.$cache;
                        return;
                    }
                    if(($_FILES['item_img']['type'] == "image/gif") or ($_FILES['item_img']['type'] == "image/jpg") or ($_FILES['item_img']['type'] == "image/jpeg") or ($_FILES['item_img']['type'] == "image/pjpeg") or ($_FILES['item_img']['type'] == "image/png") or ($_FILES['item_img']['type'] == "image/bmp")) {
                        $this->registry->main_class->set_locale();
                        if(basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $item_img = date("dmY",time())."_".basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $item_img = "";
                        }
                        if(file_exists($item_img) and $item_img != "") {
                            @unlink("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$item_img);
                        }
                        if($item_img == "" or !@move_uploaded_file($_FILES['item_img']['tmp_name'],"../".SYSTEMDK_SHOP_IMAGE_PATH."/".$item_img)) {
                            $this->error = 'add_img_not_upload_'.$cache;
                            return;
                        }
                        $item_size2 = @getimagesize("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$item_img);
                        if(($item_size2[0] > SYSTEMDK_SHOP_IMAGE_MAXWIDTH) or ($item_size2[1] > SYSTEMDK_SHOP_IMAGE_MAXHEIGHT)) {
                            @unlink("../".SYSTEMDK_SHOP_IMAGE_PATH."/".$item_img);
                            $this->error = 'add_img_not_eq_'.$cache;
                            return;
                        }
                    } else {
                        $this->error = 'add_img_not_eq_type_'.$cache;
                        return;
                    }
                } else {
                    $this->error = 'add_img_upload_'.$cache;
                    return;
                }
                $item_img = $this->registry->main_class->processing_data($item_img,'need');
            } else {
                $item_img = 'NULL';
            }
        } else {
            $item_img = 'NULL';
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        $item_date = $this->registry->main_class->get_time();
        foreach($data_array as $key => $value) {
            $keys = array(
                'item_description',
                'item_meta_title',
                'item_meta_keywords',
                'item_meta_description'
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
        $this->db->StartTrans();
        if($cache == "cat") {
            $sql = "SELECT max(catalog_position) FROM ".PREFIX."_catalogs_".$this->registry->sitelang;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $item_position = intval($result->fields['0']) + 1;
                }
            }
            if(!isset($item_position)) {
                $item_position = 1;
            }
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_catalogs_id_".$this->registry->sitelang,'catalog_id');
            $sql = "INSERT INTO ".PREFIX."_catalogs_".$this->registry->sitelang." (".$sequence_array['field_name_string']."catalog_img,catalog_name,catalog_description,catalog_position,catalog_show,catalog_date,catalog_meta_title,catalog_meta_keywords,catalog_meta_description) VALUES (".$sequence_array['sequence_value_string']."".$item_img.",".$data_array['item_name'].",".$data_array['item_description'].",'".$item_position."','".$data_array['item_show']."','".$item_date."',".$data_array['item_meta_title'].",".$data_array['item_meta_keywords'].",".$data_array['item_meta_description'].")";
        } elseif($cache == "categ") {
            $sql = "SELECT max(category_position) FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id = ".$data_array['item_catalog'];
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $item_position = intval($result->fields['0']) + 1;
                }
            }
            if(!isset($item_position)) {
                $item_position = 1;
            }
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_categories_id_".$this->registry->sitelang,'category_id');
            $sql = "INSERT INTO ".PREFIX."_categories_".$this->registry->sitelang." (".$sequence_array['field_name_string']."category_img,category_name,cat_catalog_id,category_description,category_position,category_show,category_date,category_meta_title,category_meta_keywords,category_meta_description) VALUES (".$sequence_array['sequence_value_string']."".$item_img.",".$data_array['item_name'].",".$data_array['item_catalog'].",".$data_array['item_description'].",'".$item_position."','".$data_array['item_show']."','".$item_date."',".$data_array['item_meta_title'].",".$data_array['item_meta_keywords'].",".$data_array['item_meta_description'].")";
        } elseif($cache == "subcateg") {
            $sql = "SELECT max(subcategory_position) FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id = ".$data_array['item_category'];
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $item_position = intval($result->fields['0']) + 1;
                }
            }
            if(!isset($item_position)) {
                $item_position = 1;
            }
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_subcategories_id_".$this->registry->sitelang,'subcategory_id');
            $sql = "INSERT INTO ".PREFIX."_subcategories_".$this->registry->sitelang." (".$sequence_array['field_name_string']."subcategory_img,subcategory_name,subcategory_cat_id,subcategory_description,subcategory_position,subcategory_show,subcategory_date,subcategory_meta_title,subcategory_meta_keywords,subcategory_meta_description) VALUES (".$sequence_array['sequence_value_string']."".$item_img.",".$data_array['item_name'].",".$data_array['item_category'].",".$data_array['item_description'].",'".$item_position."','".$data_array['item_show']."','".$item_date."',".$data_array['item_meta_title'].",".$data_array['item_meta_keywords'].",".$data_array['item_meta_description'].")";
        }
        if($result) {
            $result = $this->db->Execute($sql);
        }
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $this->db->CompleteTrans();
        if($result === false) {
            $this->error = 'add_sql_error_'.$cache;
            $this->error_array = $error;
            return;
        } else {
            if($cache == "cat") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|add");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            } elseif($cache == "categ") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|add|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                if(intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                }
            } elseif($cache == "subcateg") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                if(intval(SYSTEMDK_SHOP_ITEMS_UNDERPARENT) > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                }
            }
            $this->result = 'add_done_'.$cache;
        }
    }


    public function shop_status($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(isset($array['action'])) {
            $action = trim($array['action']);
        } else {
            $action = "";
        }
        if(isset($array['cat'])) {
            $cat = intval($array['cat']);
        } else {
            $cat = 0;
        }
        if(isset($array['categ'])) {
            $categ = intval($array['categ']);
        } else {
            $categ = 0;
        }
        if(isset($array['subcateg'])) {
            $subcateg = intval($array['subcateg']);
        } else {
            $subcateg = 0;
        }
        if(isset($array['get_item_id'])) {
            $item_id = intval($array['get_item_id']);
        } else {
            $item_id = 0;
        }
        if($item_id == 0 and isset($array['post_item_id']) and is_array($array['post_item_id']) and count($array['post_item_id']) > 0) {
            //$item_id_is_arr = 1;
            for($i = 0,$size = count($array['post_item_id']);$i < $size;++$i) {
                if($i == 0) {
                    $item_id = "'".intval($array['post_item_id'][$i])."'";
                } else {
                    $item_id .= ",'".intval($array['post_item_id'][$i])."'";
                }
            }
        } else {
            //$item_id_is_arr = 0;
            $item_id = "'".$item_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if((($action == "on1" or $action == "off1" or $action == "delete1") and $cat == 0) or (($action == "on2" or $action == "off2" or $action == "delete2") and ($cat == 0 or $categ == 0)) or (($action == "on4" or $action == "off4" or $action == "delete4") and $cat == 0) or (($action == "on5" or $action == "off5" or $action == "delete5") and ($cat == 0 or $categ == 0)) or (($action == "on6" or $action == "off6" or $action == "delete6") and ($cat == 0 or $categ == 0 or $subcateg == 0))) {
            $this->error = 'status_not_all_data';
            return;
        }
        if(!isset($item_id) or $action == "" or $item_id == "'0'") {
            $this->error = 'empty_data';
            return;
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_catalogs_".$this->registry->sitelang." SET catalog_show = '1' WHERE catalog_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "on";
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_catalogs_".$this->registry->sitelang." SET catalog_show = '0' WHERE catalog_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "off";
        } elseif($action == "delete") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id,$action);
            $sql0 = "DELETE FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_subcategory_id IN (SELECT subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (SELECT category_id FROM ".PREFIX."_categories_".$this->registry->sitelang." where cat_catalog_id IN (".$item_id."))) or item_category_id IN (SELECT category_id FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id IN (".$item_id.")) or item_catalog_id IN (".$item_id.")";
            $result0 = $this->db->Execute($sql0);
            if($result0 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql1 = "DELETE FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (SELECT category_id FROM ".PREFIX."_categories_".$this->registry->sitelang." where cat_catalog_id IN (".$item_id."))";
            $result1 = $this->db->Execute($sql1);
            if($result1 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql2 = "DELETE FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id IN (".$item_id.")";
            $result2 = $this->db->Execute($sql2);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql3 = "SELECT catalog_id FROM ".PREFIX."_catalogs_".$this->registry->sitelang." WHERE catalog_id IN (".$item_id.") ORDER BY catalog_position ASC";
            $result3 = $this->db->Execute($sql3);
            if($result3) {
                if(isset($result3->fields['0'])) {
                    while(!$result3->EOF) {
                        $catalog_id = intval($result3->fields['0']);
                        $sql4 = "SELECT catalog_position FROM ".PREFIX."_catalogs_".$this->registry->sitelang." WHERE catalog_id = ".$catalog_id;
                        $result4 = $this->db->Execute($sql4);
                        if($result4) {
                            if(isset($result4->fields['0'])) {
                                $catalog_position = intval($result4->fields['0']);
                                $sql5 = "UPDATE ".PREFIX."_catalogs_".$this->registry->sitelang." SET catalog_position=catalog_position-1 WHERE catalog_position > 0 and catalog_position > ".$catalog_position;
                                $result5 = $this->db->Execute($sql5);
                                if($result5 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                }
                            }
                        } else {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result3->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql = "DELETE FROM ".PREFIX."_catalogs_".$this->registry->sitelang." WHERE catalog_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result0 === false or $result1 === false or $result2 === false or $result3 === false or (isset($result4) and $result4 === false) or (isset($result5) and $result5 === false) or !empty($this->error_array)) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete";
        } elseif($action == "on1") {
            $sql = "UPDATE ".PREFIX."_categories_".$this->registry->sitelang." SET category_show = '1' WHERE category_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "on1";
        } elseif($action == "off1") {
            $sql = "UPDATE ".PREFIX."_categories_".$this->registry->sitelang." SET category_show = '0' WHERE category_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "off1";
        } elseif($action == "delete1") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id,$action);
            $sql0 = "DELETE FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_subcategory_id IN (SELECT subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (".$item_id.")) or item_category_id IN (".$item_id.")";
            $result0 = $this->db->Execute($sql0);
            if($result0 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql1 = "DELETE FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (".$item_id.")";
            $result1 = $this->db->Execute($sql1);
            if($result1 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql2 = "SELECT category_id,cat_catalog_id FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE category_id IN (".$item_id.") ORDER BY category_position ASC";
            $result2 = $this->db->Execute($sql2);
            if($result2) {
                if(isset($result2->fields['0'])) {
                    while(!$result2->EOF) {
                        $category_id = intval($result2->fields['0']);
                        $cat_catalog_id = intval($result2->fields['1']);
                        $sql3 = "SELECT category_position FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE category_id = ".$category_id;
                        $result3 = $this->db->Execute($sql3);
                        if($result3) {
                            if(isset($result3->fields['0'])) {
                                $category_position = intval($result3->fields['0']);
                                $sql4 = "UPDATE ".PREFIX."_categories_".$this->registry->sitelang." SET category_position=category_position-1 WHERE category_position > 0 and cat_catalog_id = ".$cat_catalog_id." and category_position > ".$category_position;
                                $result4 = $this->db->Execute($sql4);
                                if($result4 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                }
                            }
                        } else {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result2->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql = "DELETE FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE category_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result0 === false or $result1 === false or $result2 === false or (isset($result3) and $result3 === false) or (isset($result4) and $result4 === false) or !empty($this->error_array)) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete1";
        } elseif($action == "on2") {
            $sql = "UPDATE ".PREFIX."_subcategories_".$this->registry->sitelang." SET subcategory_show = '1' WHERE subcategory_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "on2";
        } elseif($action == "off2") {
            $sql = "UPDATE ".PREFIX."_subcategories_".$this->registry->sitelang." SET subcategory_show = '0' WHERE subcategory_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "off2";
        } elseif($action == "delete2") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id,$action);
            $sql0 = "DELETE FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_subcategory_id IN (".$item_id.")";
            $result0 = $this->db->Execute($sql0);
            if($result0 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql1 = "SELECT subcategory_id,subcategory_cat_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_id IN (".$item_id.") ORDER BY subcategory_position ASC";
            $result1 = $this->db->Execute($sql1);
            if($result1) {
                if(isset($result1->fields['0'])) {
                    while(!$result1->EOF) {
                        $subcategory_id = intval($result1->fields['0']);
                        $subcategory_cat_id = intval($result1->fields['1']);
                        $sql2 = "SELECT subcategory_position FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_id = ".$subcategory_id;
                        $result2 = $this->db->Execute($sql2);
                        if($result2) {
                            if(isset($result2->fields['0'])) {
                                $subcategory_position = intval($result2->fields['0']);
                                $sql3 = "UPDATE ".PREFIX."_subcategories_".$this->registry->sitelang." SET subcategory_position=subcategory_position-1 WHERE subcategory_position > 0 and subcategory_cat_id = ".$subcategory_cat_id." and subcategory_position > ".$subcategory_position;
                                $result3 = $this->db->Execute($sql3);
                                if($result3 === false) {
                                    $error_message = $this->db->ErrorMsg();
                                    $error_code = $this->db->ErrorNo();
                                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                }
                            }
                        } else {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $result1->MoveNext();
                    }
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql = "DELETE FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result0 === false or $result1 === false or (isset($result2) and $result2 === false) or (isset($result3) and $result3 === false) or !empty($this->error_array)) {
                $result = false;
            } else {
                $this->shop_delete_img_process();
            }
            $this->db->CompleteTrans();
            $shop_message = "delete2";
        } elseif($action == "on3" or $action == "on4" or $action == "on5" or $action == "on6") {
            $sql = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_show = '1' WHERE item_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "on_items";
        } elseif($action == "off3" or $action == "off4" or $action == "off5" or $action == "off6") {
            $sql = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_show = '0' WHERE item_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            $shop_message = "off_items";
        } elseif($action == "delete3" or $action == "delete4" or $action == "delete5" or $action == "delete6") {
            $this->db->StartTrans();
            $this->shop_delete_images($item_id,$action);
            if($action == "delete3") {
                $sql1 = "SELECT item_id FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id IN (".$item_id.") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if($result1) {
                    if(isset($result1->fields['0'])) {
                        while(!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $sql2 = "SELECT item_position FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id = ".$sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if($result2) {
                                if(isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_position=item_position-1 WHERE item_position > 0 and item_catalog_id is null and item_category_id is null and item_subcategory_id is null and item_position > ".$item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                }
            } elseif($action == "delete4") {
                $sql1 = "SELECT item_id,item_catalog_id FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id IN (".$item_id.") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if($result1) {
                    if(isset($result1->fields['0'])) {
                        while(!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $item_catalog_id = intval($result1->fields['1']);
                            $sql2 = "SELECT item_position FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id = ".$sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if($result2) {
                                if(isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_position=item_position-1 WHERE item_position > 0 and item_catalog_id = ".$item_catalog_id." and item_position > ".$item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                }
            } elseif($action == "delete5") {
                $sql1 = "SELECT item_id,item_category_id FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id IN (".$item_id.") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if($result1) {
                    if(isset($result1->fields['0'])) {
                        while(!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $item_category_id = intval($result1->fields['1']);
                            $sql2 = "SELECT item_position FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id = ".$sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if($result2) {
                                if(isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_position=item_position-1 WHERE item_position > 0 and item_category_id = ".$item_category_id." and item_position > ".$item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                }
            } elseif($action == "delete6") {
                $sql1 = "SELECT item_id,item_subcategory_id FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id IN (".$item_id.") ORDER BY item_position ASC";
                $result1 = $this->db->Execute($sql1);
                if($result1) {
                    if(isset($result1->fields['0'])) {
                        while(!$result1->EOF) {
                            $sort_item_id = intval($result1->fields['0']);
                            $item_subcategory_id = intval($result1->fields['1']);
                            $sql2 = "SELECT item_position FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id = ".$sort_item_id;
                            $result2 = $this->db->Execute($sql2);
                            if($result2) {
                                if(isset($result2->fields['0'])) {
                                    $item_position = intval($result2->fields['0']);
                                    $sql3 = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_position=item_position-1 WHERE item_position > 0 and item_subcategory_id = ".$item_subcategory_id." and item_position > ".$item_position;
                                    $result3 = $this->db->Execute($sql3);
                                    if($result3 === false) {
                                        $error_message = $this->db->ErrorMsg();
                                        $error_code = $this->db->ErrorNo();
                                        $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                                    }
                                }
                            } else {
                                $error_message = $this->db->ErrorMsg();
                                $error_code = $this->db->ErrorNo();
                                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                            }
                            $result1->MoveNext();
                        }
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $this->error_array[] = array("code" => $error_code,"message" => $error_message);
                }
            }
            $sql = "DELETE FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result1 === false or (isset($result2) and $result2 === false) or (isset($result3) and $result3 === false) or !empty($this->error_array)) {
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
        if($action != "delete" and $action != "delete1" and $action != "delete2" and $action != "delete3" and $action != "delete4" and $action != "delete5" and $action != "delete6") {
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $this->error_array[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->error = 'status_sql_error';
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'status_ok';
            return;
        } else {
            if($action == "on" or $action == "off" or $action == "delete") {
                if($action == "delete") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } else {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|add");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin");
            } elseif($action == "on1" or $action == "off1" or $action == "delete1") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$cat);
                if($action == "delete1") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$cat);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$cat);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|add|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home"); // |cat".$cat
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home"); // |cat".$cat
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat);
            } elseif($action == "on2" or $action == "off2" or $action == "delete2") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$cat."|categ".$categ);
                if($action == "delete2") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$cat."|categ".$categ);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$cat."|categ".$categ);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$cat."|categ".$categ);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat);
                //$this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat."|categ".$categ);
                //$this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat."|categ".$categ);
            } elseif($action == "on3" or $action == "off3" or $action == "delete3") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home|items");
                if($action == "delete3") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|home");
            } elseif($action == "on4" or $action == "off4" or $action == "delete4") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$cat."|items");
                if($action == "delete4") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$cat);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$cat);
            } elseif($action == "on5" or $action == "off5" or $action == "delete5") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$cat."|categ".$categ."|items");
                if($action == "delete5") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$cat."|categ".$categ);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$cat."|categ".$categ);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat);
            } elseif($action == "on6" or $action == "off6" or $action == "delete6") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$cat."|categ".$categ."|subcateg".$subcateg."|items");
                if($action == "delete6") {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$cat."|categ".$categ."|subcateg".$subcateg);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$cat."|categ".$categ."|subcateg".$subcateg);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$cat);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$cat);
            }
            $this->result = $shop_message;
        }
    }


    private function shop_delete_images($item_id = "",$action = "") {
        require_once('../modules/shop/shop_config.inc');
        if($action == "delete") {
            $sql0 = "SELECT item_id,item_img FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_subcategory_id IN (SELECT subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (SELECT category_id FROM ".PREFIX."_categories_".$this->registry->sitelang." where cat_catalog_id IN (".$item_id."))) or item_category_id IN (SELECT category_id FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id IN (".$item_id.")) or item_catalog_id IN (".$item_id.")";
            $this->shop_array_images($sql0,SYSTEMDK_SHOP_ITEMIMAGE_PATH);
            $sql1 = "SELECT subcategory_id,subcategory_img FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (SELECT category_id FROM ".PREFIX."_categories_".$this->registry->sitelang." where cat_catalog_id IN (".$item_id."))";
            $this->shop_array_images($sql1,SYSTEMDK_SHOP_IMAGE_PATH);
            $sql2 = "SELECT category_id,category_img FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE cat_catalog_id IN (".$item_id.")";
            $this->shop_array_images($sql2,SYSTEMDK_SHOP_IMAGE_PATH);
            $sql = "SELECT catalog_id,catalog_img FROM ".PREFIX."_catalogs_".$this->registry->sitelang." WHERE catalog_id IN (".$item_id.")";
            $this->shop_array_images($sql,SYSTEMDK_SHOP_IMAGE_PATH);
        } elseif($action == "delete1") {
            $sql0 = "SELECT item_id,item_img FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_subcategory_id IN (SELECT subcategory_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (".$item_id.")) or item_category_id IN (".$item_id.")";
            $this->shop_array_images($sql0,SYSTEMDK_SHOP_ITEMIMAGE_PATH);
            $sql1 = "SELECT subcategory_id,subcategory_img FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_cat_id IN (".$item_id.")";
            $this->shop_array_images($sql1,SYSTEMDK_SHOP_IMAGE_PATH);
            $sql = "SELECT category_id,category_img FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE category_id IN (".$item_id.")";
            $this->shop_array_images($sql,SYSTEMDK_SHOP_IMAGE_PATH);
        } elseif($action == "delete2") {
            $sql0 = "SELECT item_id,item_img FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_subcategory_id IN (".$item_id.")";
            $this->shop_array_images($sql0,SYSTEMDK_SHOP_ITEMIMAGE_PATH);
            $sql = "SELECT subcategory_id,subcategory_img FROM ".PREFIX."_subcategories_".$this->registry->sitelang." WHERE subcategory_id IN (".$item_id.")";
            $this->shop_array_images($sql,SYSTEMDK_SHOP_IMAGE_PATH);
        } elseif($action == "delete3" or $action == "delete4" or $action == "delete5" or $action == "delete6") {
            $sql = "SELECT item_id,item_img FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE item_id IN (".$item_id.")";
            $this->shop_array_images($sql,SYSTEMDK_SHOP_ITEMIMAGE_PATH);
        }
    }


    private function shop_array_images($sql,$image_path) {
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist > 0) {
                while(!$result->EOF) {
                    $img = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $this->img_array[] = array("image_path" => $image_path,"img" => $img);
                    $result->MoveNext();
                }
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $this->error_array[] = array("code" => $error_code,"message" => $error_message);
        }
    }


    private function shop_delete_img_process() {
        if(isset($this->img_array) and is_array($this->img_array) and count($this->img_array) > 0) {
            for($i = 0,$size = count($this->img_array);$i < $size;++$i) {
                @unlink("../".$this->img_array[$i]['image_path']."/".$this->img_array[$i]['img']);
            }
        }
    }


    public function shop_items($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
        } elseif(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and $data_array['subcateg'] != 0) {
                $cache = "subcategi";
            } elseif(isset($data_array['categ']) and $data_array['categ'] != 0) {
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
        if(isset($data_array['num_page']) and $data_array['num_page'] != 0) {
            $num_page = $data_array['num_page'];
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if($cache == "homei") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." b WHERE b.item_catalog_id is NULL and b.item_category_id is NULL and b.item_subcategory_id is NULL";
            $sql = "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date FROM ".PREFIX."_items_".$this->registry->sitelang." a WHERE a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL order by a.item_date ASC";
        } elseif($cache == "cati") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." e,".PREFIX."_catalogs_".$this->registry->sitelang." f WHERE f.catalog_id=e.item_catalog_id and e.item_catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,b.catalog_id,b.catalog_name FROM ".PREFIX."_items_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." b on a.item_catalog_id=b.catalog_id WHERE b.catalog_id = ".$data_array['cat']." order by a.item_date ASC";
        } elseif($cache == "categi") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." e,".PREFIX."_categories_".$this->registry->sitelang." f,".PREFIX."_catalogs_".$this->registry->sitelang." g WHERE f.category_id=e.item_category_id and g.catalog_id=f.cat_catalog_id and e.item_category_id = ".$data_array['categ']." and g.catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,c.catalog_id,c.catalog_name,b.category_id,b.category_name FROM ".PREFIX."_items_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b on a.item_category_id=b.category_id INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c on b.cat_catalog_id=c.catalog_id WHERE b.category_id = ".$data_array['categ']." and c.catalog_id = ".$data_array['cat']." order by a.item_date ASC";
        } elseif($cache == "subcategi") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." e,".PREFIX."_subcategories_".$this->registry->sitelang." f,".PREFIX."_categories_".$this->registry->sitelang." g,".PREFIX."_catalogs_".$this->registry->sitelang." h WHERE f.subcategory_id=e.item_subcategory_id and g.category_id=f.subcategory_cat_id and h.catalog_id=g.cat_catalog_id and e.item_subcategory_id = ".$data_array['subcateg']." and g.category_id = ".$data_array['categ']." and h.catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,d.catalog_id,d.catalog_name,c.category_id,c.category_name,b.subcategory_id,b.subcategory_name FROM ".PREFIX."_items_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." b on a.item_subcategory_id=b.subcategory_id INNER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c on b.subcategory_cat_id=c.category_id INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." d on c.cat_catalog_id=d.catalog_id WHERE b.subcategory_id = ".$data_array['subcateg']." and c.category_id = ".$data_array['categ']." and d.catalog_id = ".$data_array['cat']." order by a.item_date ASC";
        }
        $result = $this->db->Execute($sql0);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        }
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'items_sql_error_'.$cache;
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist == 0 and $num_page > 1) {
            $this->error = 'unknown_page';
            return;
        }
        if($cache == "cati" and isset($result->fields['8']) and intval($result->fields['8']) > 0) {
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
        } elseif($cache == "categi" and isset($result->fields['10']) and intval($result->fields['10']) > 0) {
            $category_id = intval($result->fields['10']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
        } elseif($cache == "subcategi" and isset($result->fields['12']) and intval($result->fields['12']) > 0) {
            $subcategory_id = intval($result->fields['12']);
            $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
            $category_id = intval($result->fields['10']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
            $this->result['adminmodules_shop_subcategory_id'] = $subcategory_id;
            $this->result['adminmodules_shop_subcategory_name'] = $subcategory_name;
        } elseif($cache != "homei") {
            if($cache == "cati") {
                $this->error = 'items_not_found_catalog';
            } elseif($cache == "categi") {
                $this->error = 'items_not_found_categ';
            } elseif($cache == "subcategi") {
                $this->error = 'items_not_found_subcateg';
            }
            return;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_show = intval($result->fields['2']);
                $item_quantity = intval($result->fields['3']);
                $item_quantity_unlim = intval($result->fields['4']);
                $item_quantity_param = intval($result->fields['5']);
                $item_display = intval($result->fields['6']);
                $item_date = date("d.m.Y H:i",intval($result->fields['7']));
                $items_all[] = array(
                    "item_id" => $item_id,
                    "item_name" => $item_name,
                    "item_show" => $item_show,
                    "item_quantity" => $item_quantity,
                    "item_quantity_unlim" => $item_quantity_unlim,
                    "item_quantity_param" => $item_quantity_param,
                    "item_display" => $item_display,
                    "item_date" => $item_date
                );
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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


    public function shop_edit_item($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and isset($data_array['categ']) and $data_array['categ'] != 0) {
                $cache = "subcategi";
                if($data_array['subcateg'] != 0) {
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif(isset($data_array['categ'])) {
                $cache = "categi";
                if($data_array['categ'] != 0) {
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
        if($data_array['item_id'] == 0 or $check == 0) {
            if($data_array['item_id'] == 0) {
                $this->error = 'edit_item_no_item';
            } elseif($cache == "cati") {
                $this->error = 'edit_item_no_cat';
            } elseif($cache == "categi") {
                $this->error = 'edit_item_no_categ';
            } elseif($cache == "subcategi") {
                $this->error = 'edit_item_no_subcateg';
            }
            return;
        }
        if($cache == "homei") {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM ".PREFIX."_items_".$this->registry->sitelang." a,".PREFIX."_catalogs_".$this->registry->sitelang." b LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c ON b.catalog_id=c.cat_catalog_id LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." d ON c.category_id=d.subcategory_cat_id WHERE a.item_id = ".$data_array['item_id']." and a.item_catalog_id IS NULL and a.item_category_id IS NULL and a.item_subcategory_id IS NULL order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        } elseif($cache == "cati") {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM ".PREFIX."_catalogs_".$this->registry->sitelang." b LEFT OUTER JOIN ".PREFIX."_items_".$this->registry->sitelang." a ON b.catalog_id=a.item_catalog_id and b.catalog_id = ".$data_array['cat']." and a.item_id = ".$data_array['item_id']." LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c ON b.catalog_id=c.cat_catalog_id and b.catalog_id = ".$data_array['cat']." LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." d ON  c.category_id=d.subcategory_cat_id order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        } elseif($cache == "categi") {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM ".PREFIX."_catalogs_".$this->registry->sitelang." b LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c ON b.catalog_id=c.cat_catalog_id and b.catalog_id = ".$data_array['cat']." LEFT OUTER JOIN ".PREFIX."_items_".$this->registry->sitelang." a ON c.category_id=a.item_category_id and a.item_id = ".$data_array['item_id']." and c.category_id = ".$data_array['categ']." LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." d ON d.subcategory_cat_id=c.category_id order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        } elseif($cache == "subcategi") {
            $sql = "SELECT a.item_id,a.item_img,a.item_name,a.item_price,a.item_price_discounted,a.item_catalog_id,a.item_category_id,a.item_subcategory_id,a.item_short_description,a.item_description,a.item_show,a.item_quantity,a.item_quantity_unlim,a.item_quantity_param,a.item_display,a.item_date,a.item_meta_title,a.item_meta_keywords,a.item_meta_description,b.catalog_id,b.catalog_name,c.category_id,c.category_name,d.subcategory_id,d.subcategory_name,a.item_position FROM ".PREFIX."_catalogs_".$this->registry->sitelang." b LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c ON b.catalog_id=c.cat_catalog_id and b.catalog_id = ".$data_array['cat']." LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." d ON c.category_id=d.subcategory_cat_id LEFT OUTER JOIN ".PREFIX."_items_".$this->registry->sitelang." a ON d.subcategory_id=a.item_subcategory_id and a.item_id = ".$data_array['item_id']." and d.subcategory_id = ".$data_array['subcateg']." and c.category_id = ".$data_array['categ']." order by (case WHEN a.item_id IS NULL THEN 0 ELSE a.item_id END) DESC,b.catalog_name ASC,c.category_name ASC,d.subcategory_name ASC";
        }
        $result = $this->db->Execute($sql);
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'edit_item_sql_error';
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->error = 'edit_item_not_found';
            return;
        }
        require_once('../modules/shop/shop_config.inc');
        while(!$result->EOF) {
            if(isset($result->fields['0']) and intval($result->fields['0']) > 0 and !isset($item_all)) {
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
                $item_date2 = date("d.m.Y",$item_date);
                $item_hour = date("H",$item_date);
                $item_minute = date("i",$item_date);
                $item_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
                $item_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['17']));
                $item_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['18']));
                if($cache == "cati" or $cache == "categi" or $cache == "subcategi") {
                    $in_catalog_id = intval($result->fields['19']);
                    $item_catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['20']));
                } else {
                    $in_catalog_id = "no";
                    $item_catalog_name = "no";
                }
                if($cache == "categi" or $cache == "subcategi") {
                    $in_category_id = intval($result->fields['21']);
                    $item_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['22']));
                } else {
                    $in_category_id = "no";
                    $item_category_name = "no";
                }
                if($cache == "subcategi") {
                    $in_subcategory_id = intval($result->fields['23']);
                    $item_subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['24']));
                } else {
                    $in_subcategory_id = "no";
                    $item_subcategory_name = "no";
                }
                $item_all[] = array(
                    "item_id" => $item_id,
                    "item_img" => $item_img,
                    "item_imgpath" => SYSTEMDK_SHOP_ITEMIMAGE_PATH,
                    "item_name" => $item_name,
                    "item_price" => $item_price,
                    "item_price_discounted" => $item_price_discounted,
                    "item_catalog_id" => $item_catalog_id,
                    "item_category_id" => $item_category_id,
                    "item_subcategory_id" => $item_subcategory_id,
                    "item_short_description" => $item_short_description,
                    "item_description" => $item_description,
                    "item_show" => $item_show,
                    "item_quantity" => $item_quantity,
                    "item_quantity_unlim" => $item_quantity_unlim,
                    "item_quantity_param" => $item_quantity_param,
                    "item_display" => $item_display,
                    "item_date" => $item_date2,
                    "item_hour" => $item_hour,
                    "item_minute" => $item_minute,
                    "item_meta_title" => $item_meta_title,
                    "item_meta_keywords" => $item_meta_keywords,
                    "item_meta_description" => $item_meta_description,
                    "in_catalog_id" => $in_catalog_id,
                    "item_catalog_name" => $item_catalog_name,
                    "in_category_id" => $in_category_id,
                    "item_category_name" => $item_category_name,
                    "in_subcategory_id" => $in_subcategory_id,
                    "item_subcategory_name" => $item_subcategory_name,
                    "item_position" => $item_position
                );
            }
            if(isset($result->fields['19']) and intval($result->fields['19']) > 0) {
                $catalog_id = intval($result->fields['19']);
                $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['20']));
                $catalog_all_now = array("catalog_id" => $catalog_id,"catalog_name" => $catalog_name);
                if(!isset($catalogs_all) or (isset($catalogs_all) and !in_array($catalog_all_now,$catalogs_all))) {
                    $catalogs_all[] = array("catalog_id" => $catalog_id,"catalog_name" => $catalog_name);
                }
            }
            if(isset($result->fields['21']) and intval($result->fields['21']) > 0) {
                $category_id = intval($result->fields['21']);
                $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['22']));
                $category_all_now = array("category_id" => $category_id,"category_name" => $category_name);
                if(!isset($categories_all) or (isset($categories_all) and !in_array($category_all_now,$categories_all))) {
                    $categories_all[] = array(
                        "category_id" => $category_id,
                        "category_name" => $category_name
                    );
                }
            }
            if(isset($result->fields['23']) and intval($result->fields['23']) > 0) {
                $subcategory_id = intval($result->fields['23']);
                $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['24']));
                $subcategories_all[] = array(
                    "subcategory_id" => $subcategory_id,
                    "subcategory_name" => $subcategory_name
                );
            }
            $result->MoveNext();
        }
        $this->result['item_all'] = $item_all;
        $this->result['adminmodules_shop_item_id'] = $item_id;
        $this->result['adminmodules_shop_cat'] = $data_array['cat'];
        $this->result['adminmodules_shop_categ'] = $data_array['categ'];
        $this->result['adminmodules_shop_subcateg'] = $data_array['subcateg'];
        if(isset($catalogs_all)) {
            $this->result['catalogs_all'] = $catalogs_all;
        }
        if(isset($categories_all)) {
            $this->result['categories_all'] = $categories_all;
        }
        if(isset($subcategories_all)) {
            $this->result['subcategories_all'] = $subcategories_all;
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['include_jquery'] = "yes";
        $this->result['include_jquery_data'] = "dateinput";
    }


    public function shop_editi_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($data_array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($data_array['item_price']);
        }
        if(isset($data_array['item_price_discounted'])) {
            $data_array['item_price_discounted'] = $this->registry->main_class->float_to_db($data_array['item_price_discounted']);
        }
        if(!isset($data_array['item_hour'])) {
            $data_array['item_hour'] = 0;
        }
        if(!isset($data_array['item_minute'])) {
            $data_array['item_minute'] = 0;
        }
        if(isset($data_array['depth']) and $data_array['depth'] == 1) {
            $cache = "cati";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "categi";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 3) {
            $cache = "subcategi";
        } else {
            $cache = "homei";
        }
        if((!isset($array['item_id']) or !isset($array['item_action_img']) or !isset($array['item_name']) or !isset($array['item_price']) or !isset($array['item_price_discounted']) or !isset($array['item_home']) or !isset($array['item_catalog']) or !isset($array['item_category']) or !isset($array['item_subcategory']) or !isset($array['item_short_description']) or !isset($array['item_description']) or !isset($array['item_position']) or !isset($array['item_show']) or !isset($array['item_quantity']) or !isset($array['item_quantity_unlim']) or !isset($array['item_quantity_param']) or !isset($array['item_display']) or !isset($array['item_display_old']) or !isset($array['item_date']) or !isset($array['item_hour']) or !isset($array['item_minute']) or !isset($array['item_meta_title']) or !isset($array['item_meta_keywords']) or !isset($array['item_meta_description'])) or ($data_array['item_id'] == 0 or $data_array['item_name'] == "" or $data_array['item_short_description'] == "" or $data_array['item_description'] == "" or $data_array['item_date'] == "" or ($cache == "cati" and (!isset($data_array['item_catalog']) or !isset($data_array['item_catalog_old']) or $data_array['item_catalog'] == 0 or $data_array['item_catalog_old'] == 0)) or ($cache == "categi" and (!isset($data_array['item_category']) or !isset($data_array['item_category_old']) or !isset($data_array['item_catalog_old']) or $data_array['item_category'] == 0 or $data_array['item_category_old'] == 0 or $data_array['item_catalog_old'] == 0)) or ($cache == "subcategi" and (!isset($data_array['item_subcategory']) or !isset($data_array['item_subcategory_old']) or !isset($data_array['item_category_old']) or !isset($data_array['item_catalog_old']) or $data_array['item_subcategory'] == 0 or $data_array['item_subcategory_old'] == 0 or $data_array['item_category_old'] == 0 or $data_array['item_catalog_old'] == 0)))) {
            $this->error = 'edit_item_not_all_data';
            return;
        }
        $data_array['item_action_img'] = $this->registry->main_class->format_striptags($data_array['item_action_img']);
        require_once('../modules/shop/shop_config.inc');
        if($data_array['item_action_img'] == 'del') {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            if(@unlink("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$data_array['item_img']) or !is_file("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$data_array['item_img'])) {
                $data_array['item_img'] = 'NULL';
            } else {
                $this->error = 'edit_item_del_img';
                return;
            }
        } elseif($data_array['item_action_img'] == 'new_file') {
            if(isset($_FILES['item_img']['name']) and trim($_FILES['item_img']['name']) !== '') {
                if(is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if($_FILES['item_img']['size'] > SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE) {
                        $this->error = 'edit_item_img_max_size';
                        return;
                    }
                    if(($_FILES['item_img']['type'] == "image/gif") or ($_FILES['item_img']['type'] == "image/jpg") or ($_FILES['item_img']['type'] == "image/jpeg") or ($_FILES['item_img']['type'] == "image/pjpeg") or ($_FILES['item_img']['type'] == "image/png") or ($_FILES['item_img']['type'] == "image/bmp")) {
                        $this->registry->main_class->set_locale();
                        if(basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $data_array['item_img'] = date("dmY",time())."_".basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $data_array['item_img'] = "";
                        }
                        if(file_exists($data_array['item_img']) and $data_array['item_img'] != "") {
                            @unlink("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$data_array['item_img']);
                        }
                        if($data_array['item_img'] == "" or !@move_uploaded_file($_FILES['item_img']['tmp_name'],"../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$data_array['item_img'])) {
                            $this->error = 'edit_item_img_not_upload';
                            return;
                        }
                        $item_size2 = @getimagesize("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$data_array['item_img']);
                        if(($item_size2[0] > SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH) or ($item_size2[1] > SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT)) {
                            @unlink("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$data_array['item_img']);
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
                $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img'],'need');
            } else {
                $data_array['item_img'] = 'NULL';
            }
        } else {
            $data_array['item_img'] = $this->registry->main_class->format_striptags($data_array['item_img']);
            $data_array['item_img'] = $this->registry->main_class->processing_data($data_array['item_img']);
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        if(intval($data_array['item_price_discounted']) == 0) {
            $data_array['item_price_discounted'] = 'NULL';
        } else {
            $data_array['item_price_discounted'] = "'".$data_array['item_price_discounted']."'";
        }
        if($cache == "homei") {
            if($data_array['item_catalog'] > 0) {
                $item_catalog_id = "'".$data_array['item_catalog']."'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
                $where2 = "item_catalog_id = ".$data_array['item_catalog'];
            } elseif($data_array['item_category'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = "'".$data_array['item_category']."'";
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
                $where2 = "item_category_id = ".$data_array['item_category'];
            } elseif($data_array['item_subcategory'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'".$data_array['item_subcategory']."'";
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
                $where2 = "item_subcategory_id = ".$data_array['item_subcategory'];
            } else {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
            }
        } elseif($cache == "cati") {
            if($data_array['item_home'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id = ".$data_array['item_catalog_old'];
                $where2 = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
            } elseif($data_array['item_category'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = "'".$data_array['item_category']."'";
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id = ".$data_array['item_catalog_old'];
                $where2 = "item_category_id = ".$data_array['item_category'];
            } elseif($data_array['item_subcategory'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'".$data_array['item_subcategory']."'";
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id = ".$data_array['item_catalog_old'];
                $where2 = "item_subcategory_id = ".$data_array['item_subcategory'];
            } else {
                $item_catalog_id = "'".$data_array['item_catalog']."'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                if($data_array['item_catalog'] != $data_array['item_catalog_old']) {
                    $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_catalog_id = ".$data_array['item_catalog_old'];
                    $where2 = "item_catalog_id = ".$data_array['item_catalog'];
                }
            }
        } elseif($cache == "categi") {
            if($data_array['item_home'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_category_id = ".$data_array['item_category_old'];
                $where2 = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
            } elseif($data_array['item_catalog'] > 0) {
                $item_catalog_id = "'".$data_array['item_catalog']."'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_category_id = ".$data_array['item_category_old'];
                $where2 = "item_catalog_id = ".$data_array['item_catalog'];
            } elseif($data_array['item_subcategory'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'".$data_array['item_subcategory']."'";
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_category_id = ".$data_array['item_category_old'];
                $where2 = "item_subcategory_id = ".$data_array['item_subcategory'];
            } else {
                $item_catalog_id = 'NULL';
                $item_category_id = "'".$data_array['item_category']."'";
                $item_subcategory_id = 'NULL';
                if($data_array['item_category'] != $data_array['item_category_old']) {
                    $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_category_id = ".$data_array['item_category_old'];
                    $where2 = "item_category_id = ".$data_array['item_category'];
                }
            }
        } elseif($cache == "subcategi") {
            if($data_array['item_home'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_subcategory_id = ".$data_array['item_subcategory_old'];
                $where2 = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
            } elseif($data_array['item_catalog'] > 0) {
                $item_catalog_id = "'".$data_array['item_catalog']."'";
                $item_category_id = 'NULL';
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_subcategory_id = ".$data_array['item_subcategory_old'];
                $where2 = "item_catalog_id = ".$data_array['item_catalog'];
            } elseif($data_array['item_category'] > 0) {
                $item_catalog_id = 'NULL';
                $item_category_id = "'".$data_array['item_category']."'";
                $item_subcategory_id = 'NULL';
                $data_array['item_display'] = 'NULL';
                $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_subcategory_id = ".$data_array['item_subcategory_old'];
                $where2 = "item_category_id = ".$data_array['item_category'];
            } else {
                $item_catalog_id = 'NULL';
                $item_category_id = 'NULL';
                $item_subcategory_id = "'".$data_array['item_subcategory']."'";
                if($data_array['item_subcategory'] != $data_array['item_subcategory_old']) {
                    $where1 = "item_position > 0 and item_position > ".$data_array['item_position']." and item_subcategory_id = ".$data_array['item_subcategory_old'];
                    $where2 = "item_subcategory_id = ".$data_array['item_subcategory'];
                }
            }
        }
        $data_array['item_meta_title'] = $this->registry->main_class->format_striptags($data_array['item_meta_title']);
        $data_array['item_meta_keywords'] = $this->registry->main_class->format_striptags($data_array['item_meta_keywords']);
        $data_array['item_meta_description'] = $this->registry->main_class->format_striptags($data_array['item_meta_description']);
        foreach($data_array as $key => $value) {
            $keys = array(
                'item_short_description',
                'item_description',
                'item_meta_title',
                'item_meta_keywords',
                'item_meta_description'
            );
            $keys2 = array(
                'item_quantity',
                'item_quantity_param',
                'item_display'
            );
            if(in_array($key,$keys)) {
                if($value != "") {
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                } else {
                    $data_array[$key] = 'NULL';
                }
            }
            if(in_array($key,$keys2)) {
                if($value == 0) {
                    $data_array[$key] = 'NULL';
                } else {
                    $data_array[$key] = "'".$value."'";
                }
            }
        }
        $data_array['item_date'] = $this->registry->main_class->format_striptags($data_array['item_date']);
        $data_array['item_date'] = $data_array['item_date']." ".sprintf("%02d",$data_array['item_hour']).":".sprintf("%02d",$data_array['item_minute']);
        $data_array['item_date'] = $this->registry->main_class->time_to_unix($data_array['item_date']);
        $this->db->StartTrans();
        if(isset($where1) and isset($where2)) {
            $result4 = $this->db->Execute("UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_position=item_position-1 WHERE ".$where1);
            if($result4 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql2 = "SELECT max(item_position)+1 FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE ".$where2;
            $result5 = $this->db->Execute($sql2);
            if($result5) {
                if(isset($result5->fields['0'])) {
                    $data_array['item_position'] = intval($result5->fields['0']);
                } else {
                    $data_array['item_position'] = 1;
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $sql = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_img = ".$data_array['item_img'].",item_name = ".$data_array['item_name'].",item_price = '".$data_array['item_price']."',item_price_discounted = ".$data_array['item_price_discounted'].",item_catalog_id = ".$item_catalog_id.",item_category_id = ".$item_category_id.",item_subcategory_id = ".$item_subcategory_id.",item_short_description = empty_clob(),item_description = empty_clob(),item_position = '".$data_array['item_position']."',item_show = '".$data_array['item_show']."',item_quantity = ".$data_array['item_quantity'].",item_quantity_unlim = '".$data_array['item_quantity_unlim']."',item_quantity_param = ".$data_array['item_quantity_param'].",item_display = ".$data_array['item_display'].",item_date = '".$data_array['item_date']."',item_meta_title = ".$data_array['item_meta_title'].",item_meta_keywords = ".$data_array['item_meta_keywords'].",item_meta_description = ".$data_array['item_meta_description']." WHERE item_id = ".$data_array['item_id'];
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $data_array['item_short_description'] = substr(substr($data_array['item_short_description'],0,-1),1);
            $result2 = $this->db->UpdateClob(PREFIX.'_items_'.$this->registry->sitelang,'item_short_description',$data_array['item_short_description'],'item_id='.$data_array['item_id']);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $data_array['item_description'] = substr(substr($data_array['item_description'],0,-1),1);
            $result3 = $this->db->UpdateClob(PREFIX.'_items_'.$this->registry->sitelang,'item_description',$data_array['item_description'],'item_id='.$data_array['item_id']);
            if($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result2 === false or $result3 === false or (isset($result4) and $result4 === false) or (isset($result5) and $result5 === false)) {
                $result = false;
            }
        } else {
            $sql = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_img = ".$data_array['item_img'].",item_name = ".$data_array['item_name'].",item_price = '".$data_array['item_price']."',item_price_discounted = ".$data_array['item_price_discounted'].",item_catalog_id = ".$item_catalog_id.",item_category_id = ".$item_category_id.",item_subcategory_id = ".$item_subcategory_id.",item_short_description = ".$data_array['item_short_description'].",item_description = ".$data_array['item_description'].",item_position = '".$data_array['item_position']."',item_show = '".$data_array['item_show']."',item_quantity = ".$data_array['item_quantity'].",item_quantity_unlim = '".$data_array['item_quantity_unlim']."',item_quantity_param = ".$data_array['item_quantity_param'].",item_display = ".$data_array['item_display'].",item_date = '".$data_array['item_date']."',item_meta_title = ".$data_array['item_meta_title'].",item_meta_keywords = ".$data_array['item_meta_keywords'].",item_meta_description = ".$data_array['item_meta_description']." WHERE item_id = ".$data_array['item_id'];
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if((isset($result4) and $result4 === false) or (isset($result5) and $result5 === false)) {
                $result = false;
            }
        }
        $this->db->CompleteTrans();
        if($result === false) {
            $this->error = 'edit_item_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'edit_item_ok';
            return;
        } else {
            if($cache == "homei") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|home|item".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
                if($data_array['item_catalog'] > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|items");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_category'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_category'],2);
                    if(isset($data_array['item_catalog']['0'])) {
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']."|items");
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                    }
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_subcategory'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_subcategory'],3);
                    if(isset($data_array['item_catalog']['0']) and isset($data_array['item_catalog']['1'])) {
                        $data_array['item_category'] = $data_array['item_catalog']['0'];
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']."|items");
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                    }
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
            } elseif($cache == "cati") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog_old']."|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                if($data_array['item_display'] == "'3'" or $data_array['item_display_old'] == 3) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                }
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog_old']."|item".$data_array['item_id']);
                if($data_array['item_home'] > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home|items");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_category'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_category'],2);
                    if(isset($data_array['item_catalog']['0'])) {
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']."|items");
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                    }
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_subcategory'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_subcategory'],3);
                    if(isset($data_array['item_catalog']['0']) and isset($data_array['item_catalog']['1'])) {
                        $data_array['item_category'] = $data_array['item_catalog']['0'];
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']."|items");
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                    }
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
            } elseif($cache == "categi") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category']."|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|item".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category']);
                if($data_array['item_display'] == "'3'" or $data_array['item_display_old'] == 3) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']);
                } elseif($data_array['item_display'] == "'2'" or $data_array['item_display_old'] == 2) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']);
                }
                if($data_array['item_home'] > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home|items");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_catalog'] > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|items");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_subcategory'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_subcategory'],3);
                    if(isset($data_array['item_catalog']['0']) and isset($data_array['item_catalog']['1'])) {
                        $data_array['item_category'] = $data_array['item_catalog']['0'];
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']."|items");
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']['1']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
                    }
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
            } elseif($cache == "subcategi") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory_old']."|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory']."|items");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory_old']."|item".$data_array['item_id']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory_old']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']."|subcateg".$data_array['item_subcategory']);
                if($data_array['item_display'] == "'3'" or $data_array['item_display_old'] == 3) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']);
                } elseif($data_array['item_display'] == "'2'" or $data_array['item_display_old'] == 2) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']);
                } elseif($data_array['item_display'] == "'1'" or $data_array['item_display_old'] == 1) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog_old']."|categ".$data_array['item_category_old']);
                }
                if($data_array['item_home'] > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home|items");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_catalog'] > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|items");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                } elseif($data_array['item_category'] > 0) {
                    $data_array['item_catalog'] = $this->shop_getdepthinfo($data_array['item_category'],2);
                    if(isset($data_array['item_catalog']['0'])) {
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']."|items");
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']['0']."|categ".$data_array['item_category']);
                    }
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
                }
            }
            $this->result = 'edit_item_done';
        }
    }


    private function shop_getdepthinfo($shop_depth_id,$depth = 0) {
        $shop_depth_id = intval($shop_depth_id);
        $depth = intval($depth);
        if($shop_depth_id != 0 and $depth != 0 and ($depth == 2 or $depth == 3)) {
            if($depth == 2) {
                $sql = "SELECT cat_catalog_id FROM ".PREFIX."_categories_".$this->registry->sitelang." WHERE category_id=".$shop_depth_id;
            } elseif($depth == 3) {
                $sql = "SELECT b.subcategory_cat_id,a.cat_catalog_id FROM ".PREFIX."_subcategories_".$this->registry->sitelang." b,".PREFIX."_categories_".$this->registry->sitelang." a WHERE b.subcategory_cat_id=a.category_id and b.subcategory_id=".$shop_depth_id;
            }
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
                    $depthinfo = $result->fields;
                }
            }
        }
        if(isset($depthinfo)) {
            return $depthinfo;
        } else {
            return 0;
        }
    }


    public function shop_add_item($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
                    $check = 1;
                } else {
                    $check = 0;
                }
            } elseif(isset($data_array['categ'])) {
                $cache = "categi";
                if($data_array['categ'] != 0) {
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
        } elseif(isset($data_array['cat']) and $data_array['cat'] == 0) {
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
        if($check == 0) {
            if($cache == "cati") {
                $this->error = 'add_item_no_cat';
            } elseif($cache == "categi") {
                $this->error = 'add_item_no_categ';
            } elseif($cache == "subcategi") {
                $this->error = 'add_item_no_subcateg';
            }
            return;
        }
        if($cache == "cati" or $cache == "categi" or $cache == "subcategi") {
            if($cache == "cati") {
                $sql = "SELECT a.catalog_id,a.catalog_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a order by a.catalog_name ASC";
            } elseif($cache == "categi") {
                $sql = "SELECT a.catalog_id,a.catalog_name,b.category_id,b.category_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a,".PREFIX."_categories_".$this->registry->sitelang." b WHERE a.catalog_id=b.cat_catalog_id and a.catalog_id = ".$data_array['cat']." order by b.category_name ASC";
            } elseif($cache == "subcategi") {
                $sql = "SELECT a.catalog_id,a.catalog_name,b.category_id,b.category_name,c.subcategory_id,c.subcategory_name FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a,".PREFIX."_categories_".$this->registry->sitelang." b,".PREFIX."_subcategories_".$this->registry->sitelang." c WHERE a.catalog_id=b.cat_catalog_id and a.catalog_id = ".$data_array['cat']." and b.category_id=c.subcategory_cat_id and b.category_id = ".$data_array['categ']." order by c.subcategory_name ASC";
            }
            $result = $this->db->Execute($sql);
            if(!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->error = 'add_item_sql_error';
                $this->error_array = $error;
                return;
            }
            if(isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if($row_exist < 1) {
                if($cache == "cati") {
                    $this->error = 'add_item_not_found_catalog';
                } elseif($cache == "categi") {
                    $this->error = 'add_item_not_found_categ';
                } elseif($cache == "subcategi") {
                    $this->error = 'add_item_not_found_subcateg';
                }
                return;
            }
            while(!$result->EOF) {
                if(isset($result->fields['0']) and intval($result->fields['0']) > 0) {
                    $catalog_id = intval($result->fields['0']);
                    $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $catalog_all_now = array("catalog_id" => $catalog_id,"catalog_name" => $catalog_name);
                    if(!isset($catalogs_all) or (isset($catalogs_all) and !in_array($catalog_all_now,$catalogs_all))) {
                        $catalogs_all[] = array(
                            "catalog_id" => $catalog_id,
                            "catalog_name" => $catalog_name
                        );
                    }
                    if($catalog_id == $data_array['cat']) {
                        $in_catalog_name = $catalog_name;
                    }
                }
                if(($cache == "categi" or $cache == "subcategi") and isset($result->fields['2']) and intval($result->fields['2']) > 0) {
                    $category_id = intval($result->fields['2']);
                    $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $category_all_now = array(
                        "category_id" => $category_id,
                        "category_name" => $category_name
                    );
                    if(!isset($categories_all) or (isset($categories_all) and !in_array($category_all_now,$categories_all))) {
                        $categories_all[] = array(
                            "category_id" => $category_id,
                            "category_name" => $category_name
                        );
                    }
                    if($category_id == $data_array['categ']) {
                        $in_category_name = $category_name;
                    }
                }
                if($cache == "subcategi" and isset($result->fields['4']) and intval($result->fields['4']) > 0) {
                    $subcategory_id = intval($result->fields['4']);
                    $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    $subcategories_all[] = array(
                        "subcategory_id" => $subcategory_id,
                        "subcategory_name" => $subcategory_name
                    );
                    if($subcategory_id == $data_array['subcateg']) {
                        $in_subcategory_name = $subcategory_name;
                    }
                }
                $result->MoveNext();
            }
            if((!isset($in_catalog_name) and $cache == "cati") or (!isset($in_category_name) and $cache == "categi") or (!isset($in_subcategory_name) and $cache == "subcategi")) {
                if($cache == "cati") {
                    $this->error = 'add_item_not_found_catalog';
                } elseif($cache == "categi") {
                    $this->error = 'add_item_not_found_categ';
                } elseif($cache == "subcategi") {
                    $this->error = 'add_item_not_found_subcateg';
                }
                return;
            }
            $this->result['adminmodules_shop_cat'] = $data_array['cat'];
            $this->result['adminmodules_shop_categ'] = $data_array['categ'];
            $this->result['adminmodules_shop_subcateg'] = $data_array['subcateg'];
            $this->result['adminmodules_shop_cat_name'] = $in_catalog_name;
            if(isset($in_category_name)) {
                $this->result['adminmodules_shop_categ_name'] = $in_category_name;
            }
            if(isset($in_subcategory_name)) {
                $this->result['adminmodules_shop_subcateg_name'] = $in_subcategory_name;
            }
            if(isset($catalogs_all)) {
                $this->result['catalogs_all'] = $catalogs_all;
            }
            if(isset($categories_all)) {
                $this->result['categories_all'] = $categories_all;
            }
            if(isset($subcategories_all)) {
                $this->result['subcategories_all'] = $subcategories_all;
            }
        }
        $this->result['adminmodules_shop_depth'] = $cache;
        $this->result['include_jquery'] = "yes";
        $this->result['include_jquery_data'] = "dateinput";
    }


    // TODO Check oracle insert id and refactor all models where using $check_db_need_lobs
    public function shop_addi_inbase($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($array['item_price']);
        }
        if(isset($array['item_price_discounted'])) {
            $data_array['item_price_discounted'] = $this->registry->main_class->float_to_db($array['item_price_discounted']);
        }
        if(!isset($array['item_hour'])) {
            $data_array['item_hour'] = 0;
        }
        if(!isset($array['item_minute'])) {
            $data_array['item_minute'] = 0;
        }
        if(isset($data_array['depth']) and $data_array['depth'] == 1) {
            $cache = "cati";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "categi";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 3) {
            $cache = "subcategi";
        } else {
            $cache = "homei";
        }
        if((!isset($array['item_action_img']) or !isset($array['item_name']) or !isset($array['item_price']) or !isset($array['item_price_discounted']) or (!isset($array['item_home']) and $cache == "homei") or (!isset($array['item_catalog']) and $cache == "cati") or ((!isset($array['item_catalog']) or !isset($array['item_category'])) and $cache == "categi") or ((!isset($array['item_catalog']) or !isset($array['item_category']) or !isset($array['item_subcategory'])) and $cache == "subcategi") or !isset($array['item_short_description']) or !isset($array['item_description']) or !isset($array['item_show']) or !isset($array['item_quantity']) or !isset($array['item_quantity_unlim']) or !isset($array['item_quantity_param']) or !isset($array['item_display']) or !isset($array['item_date']) or !isset($array['item_hour']) or !isset($array['item_minute']) or !isset($array['item_meta_title']) or !isset($array['item_meta_keywords']) or !isset($array['item_meta_description'])) or ($data_array['item_name'] == "" or $data_array['item_short_description'] == "" or $data_array['item_description'] == "" or $data_array['item_date'] == "" or ($cache == "homei" and $data_array['item_home'] == 0) or ($cache == "cati" and (!isset($data_array['item_catalog']) or $data_array['item_catalog'] == 0)) or ($cache == "categi" and (!isset($data_array['item_category']) or !isset($data_array['item_catalog']) or $data_array['item_category'] == 0 or $data_array['item_catalog'] == 0)) or ($cache == "subcategi" and (!isset($data_array['item_subcategory']) or !isset($data_array['item_category']) or !isset($data_array['item_catalog']) or $data_array['item_subcategory'] == 0 or $data_array['item_category'] == 0 or $data_array['item_catalog'] == 0)))) {
            $this->error = 'add_item_not_all_data';
            return;
        }
        $data_array['item_action_img'] = $this->registry->main_class->format_striptags($data_array['item_action_img']);
        require_once('../modules/shop/shop_config.inc');
        if($data_array['item_action_img'] == 'new_file') {
            if(isset($_FILES['item_img']['name']) and trim($_FILES['item_img']['name']) !== '') {
                if(is_uploaded_file($_FILES['item_img']['tmp_name'])) {
                    if($_FILES['item_img']['size'] > SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE) {
                        $this->error = 'add_item_img_max_size';
                        return;
                    }
                    if(($_FILES['item_img']['type'] == "image/gif") or ($_FILES['item_img']['type'] == "image/jpg") or ($_FILES['item_img']['type'] == "image/jpeg") or ($_FILES['item_img']['type'] == "image/pjpeg") or ($_FILES['item_img']['type'] == "image/png") or ($_FILES['item_img']['type'] == "image/bmp")) {
                        $this->registry->main_class->set_locale();
                        if(basename($this->registry->main_class->format_striptags($_FILES['item_img']['name'])) != "") {
                            $item_img = date("dmY",time())."_".basename($this->registry->main_class->format_striptags($_FILES['item_img']['name']));
                        } else {
                            $item_img = "";
                        }
                        if(file_exists($item_img) and $item_img != "") {
                            @unlink("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$item_img);
                        }
                        if($item_img == "" or !@move_uploaded_file($_FILES['item_img']['tmp_name'],"../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$item_img)) {
                            $this->error = 'add_item_img_not_upload';
                            return;
                        }
                        $item_size2 = @getimagesize("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$item_img);
                        if(($item_size2[0] > SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH) or ($item_size2[1] > SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT)) {
                            @unlink("../".SYSTEMDK_SHOP_ITEMIMAGE_PATH."/".$item_img);
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
                $item_img = $this->registry->main_class->processing_data($item_img,'need');
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
        if(intval($data_array['item_price_discounted']) == 0) {
            $data_array['item_price_discounted'] = 'NULL';
        } else {
            $data_array['item_price_discounted'] = "'".$data_array['item_price_discounted']."'";
        }
        foreach($data_array as $key => $value) {
            $keys = array(
                'item_short_description',
                'item_description',
                'item_meta_title',
                'item_meta_keywords',
                'item_meta_description'
            );
            $keys2 = array(
                'item_quantity',
                'item_quantity_param',
                'item_display'
            );
            if(in_array($key,$keys)) {
                if($value != "") {
                    $data_array[$key] = $this->registry->main_class->processing_data($value);
                } else {
                    $data_array[$key] = 'NULL';
                }
            }
            if(in_array($key,$keys2)) {
                if($value == 0) {
                    $data_array[$key] = 'NULL';
                } else {
                    $data_array[$key] = "'".$value."'";
                }
            }
        }
        if($cache == "homei") {
            $item_catalog_id = 'NULL';
            $item_category_id = 'NULL';
            $item_subcategory_id = 'NULL';
        } elseif($cache == "cati") {
            $item_catalog_id = "'".$data_array['item_catalog']."'";
            $item_category_id = 'NULL';
            $item_subcategory_id = 'NULL';
        } elseif($cache == "categi") {
            $item_catalog_id = 'NULL';
            $item_category_id = "'".$data_array['item_category']."'";
            $item_subcategory_id = 'NULL';
        } elseif($cache == "subcategi") {
            $item_catalog_id = 'NULL';
            $item_category_id = 'NULL';
            $item_subcategory_id = "'".$data_array['item_subcategory']."'";
        }
        $data_array['item_date'] = $this->registry->main_class->format_striptags($data_array['item_date']);
        $data_array['item_date'] = $data_array['item_date']." ".sprintf("%02d",$data_array['item_hour']).":".sprintf("%02d",$data_array['item_minute']);
        $data_array['item_date'] = $this->registry->main_class->time_to_unix($data_array['item_date']);
        if($cache == "homei") {
            $where = "item_catalog_id is NULL and item_category_id is NULL and item_subcategory_id is NULL";
        } elseif($cache == "cati") {
            $where = "item_catalog_id = ".$item_catalog_id;
        } elseif($cache == "categi") {
            $where = "item_category_id = ".$item_category_id;
        } elseif($cache == "subcategi") {
            $where = "item_subcategory_id = ".$item_subcategory_id;
        }
        $sql = "SELECT max(item_position)+1 FROM ".PREFIX."_items_".$this->registry->sitelang." WHERE ".$where;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $item_position = intval($result->fields['0']);
            }
        }
        if(!isset($item_position)) {
            $item_position = 1;
        }
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_items_id_".$this->registry->sitelang,'item_id');
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_items_".$this->registry->sitelang." (".$sequence_array['field_name_string']."item_img,item_name,item_price,item_price_discounted,item_catalog_id,item_category_id,item_subcategory_id,item_short_description,item_description,item_position,item_show,item_quantity,item_quantity_unlim,item_quantity_param,item_display,item_date,item_meta_title,item_meta_keywords,item_meta_description) VALUES (".$sequence_array['sequence_value_string']."".$item_img.",".$data_array['item_name'].",'".$data_array['item_price']."',".$data_array['item_price_discounted'].",".$item_catalog_id.",".$item_category_id.",".$item_subcategory_id.",empty_clob(),empty_clob(),'".$item_position."','".$data_array['item_show']."',".$data_array['item_quantity'].",'".$data_array['item_quantity_unlim']."',".$data_array['item_quantity_param'].",".$data_array['item_display'].",'".$data_array['item_date']."',".$data_array['item_meta_title'].",".$data_array['item_meta_keywords'].",".$data_array['item_meta_description'].")";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $data_array['item_short_description'] = substr(substr($data_array['item_short_description'],0,-1),1);
            $result2 = $this->db->UpdateClob(PREFIX.'_items_'.$this->registry->sitelang,'item_short_description',$data_array['item_short_description'],'item_id='.$sequence_array['sequence_value']);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $data_array['item_description'] = substr(substr($data_array['item_description'],0,-1),1);
            $result3 = $this->db->UpdateClob(PREFIX.'_items_'.$this->registry->sitelang,'item_description',$data_array['item_description'],'item_id='.$sequence_array['sequence_value']);
            if($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->db->CompleteTrans();
            if($result2 === false or $result3 === false) {
                $result = false;
            }
        } else {
            $sql = "INSERT INTO ".PREFIX."_items_".$this->registry->sitelang." (".$sequence_array['field_name_string']."item_img,item_name,item_price,item_price_discounted,item_catalog_id,item_category_id,item_subcategory_id,item_short_description,item_description,item_position,item_show,item_quantity,item_quantity_unlim,item_quantity_param,item_display,item_date,item_meta_title,item_meta_keywords,item_meta_description) VALUES (".$sequence_array['sequence_value_string']."".$item_img.",".$data_array['item_name'].",'".$data_array['item_price']."',".$data_array['item_price_discounted'].",".$item_catalog_id.",".$item_category_id.",".$item_subcategory_id.",".$data_array['item_short_description'].",".$data_array['item_description'].",'".$item_position."','".$data_array['item_show']."',".$data_array['item_quantity'].",'".$data_array['item_quantity_unlim']."',".$data_array['item_quantity_param'].",".$data_array['item_display'].",'".$data_array['item_date']."',".$data_array['item_meta_title'].",".$data_array['item_meta_keywords'].",".$data_array['item_meta_description'].")";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->error = 'add_item_sql_error';
            $this->error_array = $error;
            return;
        }
        if($cache == "homei") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|home|items");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
        } elseif($cache == "cati") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|items");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
            if($data_array['item_display'] == "'3'") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            }
        } elseif($cache == "categi") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|categ".$data_array['item_category']."|items");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
            if($data_array['item_display'] == "'3'") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
            } elseif($data_array['item_display'] == "'2'") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
            }
        } elseif($cache == "subcategi") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|cat".$data_array['item_catalog']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']."|items");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['item_catalog']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']."|categ".$data_array['item_category']."|subcateg".$data_array['item_subcategory']);
            if($data_array['item_display'] == "'3'") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
            } elseif($data_array['item_display'] == "'2'") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']);
            } elseif($data_array['item_display'] == "'1'") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['item_catalog']."|categ".$data_array['item_category']);
            }
        }
        $this->result = 'edit_item_done';
    }


    public function shop_sort($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
            } else {
                $cache = "home";
            }
            $data_array['cat'] = 0;
            $data_array['categ'] = 0;
            $data_array['subcateg'] = 0;
            $itemid = 1;
        } elseif(isset($data_array['cat']) and $data_array['cat'] != 0) {
            if(isset($data_array['subcateg']) and isset($data_array['categ']) and $data_array['categ'] != 0 and isset($data_array['items']) and $data_array['items'] == 1) {
                $cache = "subcategi";
                if($data_array['subcateg'] != 0) {
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
                    $itemid = $data_array['categ'];
                    $data_array['subcateg'] = 0;
                } else {
                    $itemid = 0;
                }
            } else {
                if(isset($data_array['items']) and $data_array['items'] == 1) {
                    $cache = "cati";
                } else {
                    $cache = "categ";
                }
                $itemid = $data_array['cat'];
                $data_array['categ'] = 0;
                $data_array['subcateg'] = 0;
            }
        } else {
            if(isset($data_array['items']) and $data_array['items'] == 1) {
                $cache = "cati";
            } else {
                $cache = "categ";
            }
            $itemid = 0;
        }
        if($itemid == 0) {
            if($cache == "categ" or $cache == "cati") {
                $this->error = 'sort_no_cat';
            } elseif($cache == "subcateg" or $cache == "categi") {
                $this->error = 'sort_no_categ';
            } elseif($cache == "subcategi") {
                $this->error = 'sort_no_subcateg';
            }
            return;
        }
        if(isset($data_array['num_page']) and intval($data_array['num_page']) != 0) {
            $num_page = intval($data_array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if($cache == "home") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_catalogs_".$this->registry->sitelang." b";
            $sql = "SELECT a.catalog_id,a.catalog_name,a.catalog_position,a.catalog_show FROM ".PREFIX."_catalogs_".$this->registry->sitelang." a order by a.catalog_position ASC";
        } elseif($cache == "categ") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_categories_".$this->registry->sitelang." b WHERE b.cat_catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.category_id,a.category_name,a.category_position,a.category_show,c.catalog_id,c.catalog_name FROM ".PREFIX."_categories_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.cat_catalog_id=c.catalog_id WHERE c.catalog_id = ".$data_array['cat']." order by a.category_position ASC";
        } elseif($cache == "subcateg") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_subcategories_".$this->registry->sitelang." b WHERE b.subcategory_cat_id = ".$data_array['categ'];
            $sql = "SELECT a.subcategory_id,a.subcategory_name,a.subcategory_position,a.subcategory_show,c.category_id,c.category_name,d.catalog_id,d.catalog_name FROM ".PREFIX."_subcategories_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c ON c.category_id=a.subcategory_cat_id RIGHT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." d ON d.catalog_id=c.cat_catalog_id WHERE c.category_id = ".$data_array['categ']." and d.catalog_id = ".$data_array['cat']." order by a.subcategory_position ASC";
        } elseif($cache == "homei") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." b WHERE b.item_catalog_id is NULL and b.item_category_id is NULL and b.item_subcategory_id is NULL";
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show FROM ".PREFIX."_items_".$this->registry->sitelang." a WHERE a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL order by a.item_position ASC";
        } elseif($cache == "cati") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." e,".PREFIX."_catalogs_".$this->registry->sitelang." f WHERE f.catalog_id=e.item_catalog_id and e.item_catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show,b.catalog_id,b.catalog_name FROM ".PREFIX."_items_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." b on a.item_catalog_id=b.catalog_id WHERE b.catalog_id = ".$data_array['cat']." order by a.item_position ASC";
        } elseif($cache == "categi") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." e,".PREFIX."_categories_".$this->registry->sitelang." f,".PREFIX."_catalogs_".$this->registry->sitelang." g WHERE f.category_id=e.item_category_id and g.catalog_id=f.cat_catalog_id and e.item_category_id = ".$data_array['categ']." and g.catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show,b.category_id,b.category_name,c.catalog_id,c.catalog_name FROM ".PREFIX."_items_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." b on a.item_category_id=b.category_id INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c on b.cat_catalog_id=c.catalog_id WHERE b.category_id = ".$data_array['categ']." and c.catalog_id = ".$data_array['cat']." order by a.item_position ASC";
        } elseif($cache == "subcategi") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_items_".$this->registry->sitelang." e,".PREFIX."_subcategories_".$this->registry->sitelang." f,".PREFIX."_categories_".$this->registry->sitelang." g,".PREFIX."_catalogs_".$this->registry->sitelang." h WHERE f.subcategory_id=e.item_subcategory_id and g.category_id=f.subcategory_cat_id and h.catalog_id=g.cat_catalog_id and e.item_subcategory_id = ".$data_array['subcateg']." and g.category_id = ".$data_array['categ']." and h.catalog_id = ".$data_array['cat'];
            $sql = "SELECT a.item_id,a.item_name,a.item_position,a.item_show,b.subcategory_id,b.subcategory_name,c.category_id,c.category_name,d.catalog_id,d.catalog_name FROM ".PREFIX."_items_".$this->registry->sitelang." a RIGHT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." b on a.item_subcategory_id=b.subcategory_id INNER JOIN ".PREFIX."_categories_".$this->registry->sitelang." c on b.subcategory_cat_id=c.category_id INNER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." d on c.cat_catalog_id=d.catalog_id WHERE b.subcategory_id = ".$data_array['subcateg']." and c.category_id = ".$data_array['categ']." and d.catalog_id = ".$data_array['cat']." order by a.item_position ASC";
        }
        $result = $this->db->Execute($sql0);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        }
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'sort_sql_error';
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist == 0 and $num_page > 1) {
            $this->error = 'unknown_page';
            return;
        }
        if(($cache == "categ" or $cache == "cati") and isset($result->fields['4']) and intval($result->fields['4']) > 0) {
            $catalog_id = intval($result->fields['4']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
        } elseif(($cache == "subcateg" or $cache == "categi") and isset($result->fields['4']) and intval($result->fields['4']) > 0) {
            $category_id = intval($result->fields['4']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            $catalog_id = intval($result->fields['6']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
        } elseif($cache == "subcategi" and isset($result->fields['4']) and intval($result->fields['4']) > 0) {
            $subcategory_id = intval($result->fields['4']);
            $subcategory_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            $category_id = intval($result->fields['6']);
            $category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
            $catalog_id = intval($result->fields['8']);
            $catalog_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
            $this->result['adminmodules_shop_catalog_id'] = $catalog_id;
            $this->result['adminmodules_shop_catalog_name'] = $catalog_name;
            $this->result['adminmodules_shop_category_id'] = $category_id;
            $this->result['adminmodules_shop_category_name'] = $category_name;
            $this->result['adminmodules_shop_subcategory_id'] = $subcategory_id;
            $this->result['adminmodules_shop_subcategory_name'] = $subcategory_name;
        } elseif($cache != "home" and $cache != "homei") {
            if($cache == "categ" or $cache == "cati") {
                $this->error = 'sort_not_found_catalog';
            } elseif($cache == "subcateg" or $cache == "categi") {
                $this->error = 'sort_not_found_categ';
            } elseif($cache == "subcategi") {
                $this->error = 'sort_not_found_subcateg';
            }
            return;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $item_id = intval($result->fields['0']);
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_position = intval($result->fields['2']);
                $item_show = intval($result->fields['3']);
                $items_all[] = array(
                    "item_id" => $item_id,
                    "item_name" => $item_name,
                    "item_position" => $item_position,
                    "item_show" => $item_show
                );
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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


    public function shop_sort_save($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($data_array['depth']) and $data_array['depth'] == 1) {
            $cache = "categ";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 2) {
            $cache = "subcateg";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 3) {
            $cache = "homei";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 4) {
            $cache = "cati";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 5) {
            $cache = "categi";
        } elseif(isset($data_array['depth']) and $data_array['depth'] == 6) {
            $cache = "subcategi";
        } else {
            $cache = "home";
        }
        if(isset($array['item_id']) and is_array($array['item_id']) and count($array['item_id']) > 0 and isset($array['item_position']) and is_array($array['item_position']) and count($array['item_position']) > 0) {
            //$item_id_is_arr = 1;
            $item_id = false;
            for($i = 0,$size = count($array['item_id']);$i < $size;++$i) {
                if($i == 0) {
                    $item_id = " WHEN ".intval($array['item_id'][$i])." THEN ".intval($array['item_position'][$i]);
                } else {
                    $item_id .= " WHEN ".intval($array['item_id'][$i])." THEN ".intval($array['item_position'][$i]);
                }
            }
        } else {
            //$item_id_is_arr = 0;
        }
        if((!isset($array['item_id']) or !isset($array['item_position'])) or ((($cache == "cati" or $cache == "categ") and (!isset($data_array['cat']) or $data_array['cat'] == 0)) or (($cache == "categi" or $cache == "subcateg") and (!isset($data_array['categ']) or !isset($data_array['cat']) or $data_array['categ'] == 0 or $data_array['cat'] == 0)) or ($cache == "subcategi" and (!isset($data_array['subcateg']) or !isset($data_array['categ']) or !isset($data_array['cat']) or $data_array['subcateg'] == 0 or $data_array['categ'] == 0 or $data_array['cat'] == 0)))) {
            $this->error = 'sort_not_all_data';
            return;
        }
        if($cache == "home") {
            $sql = "UPDATE ".PREFIX."_catalogs_".$this->registry->sitelang." SET catalog_position = (CASE catalog_id ".$item_id." ELSE catalog_position END)";
        } elseif($cache == "categ") {
            $sql = "UPDATE ".PREFIX."_categories_".$this->registry->sitelang." SET category_position = (CASE category_id ".$item_id." ELSE category_position END)";
        } elseif($cache == "subcateg") {
            $sql = "UPDATE ".PREFIX."_subcategories_".$this->registry->sitelang." SET subcategory_position = (CASE subcategory_id ".$item_id." ELSE subcategory_position END)";
        } elseif($cache == "homei" or $cache == "cati" or $cache == "categi" or $cache == "subcategi") {
            $sql = "UPDATE ".PREFIX."_items_".$this->registry->sitelang." SET item_position = (CASE item_id ".$item_id." ELSE item_position END)";
        }
        $result = $this->db->Execute($sql);
        if($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'sort_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'sort_ok';
            return;
        }
        require_once('../modules/shop/shop_config.inc');
        if($cache == "home") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|home");
            if(SYSTEMDK_SHOP_CATALOGS_ORDER == "catalog_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            }
        } elseif($cache == "categ") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['cat']);
            if(SYSTEMDK_SHOP_CATEGORIES_ORDER == "category_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']);
                if(SYSTEMDK_SHOP_ITEMS_UNDERPARENT > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
                }
            }
        } elseif($cache == "subcateg") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sort|cat".$data_array['cat']."|categ".$data_array['categ']);
            if(SYSTEMDK_SHOP_SUBCATEGORIES_ORDER == "subcategory_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']."|categ".$data_array['categ']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']."|categ".$data_array['categ']);
                if(SYSTEMDK_SHOP_ITEMS_UNDERPARENT > 0) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']);
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']);
                }
            }
        } elseif($cache == "homei") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|home");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|home");
            if(SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            }
        } elseif($cache == "cati") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['cat']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['cat']);
            if(SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            }
            if(SYSTEMDK_SHOP_CATITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']);
            }
        } elseif($cache == "categi") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['cat']."|categ".$data_array['categ']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['cat']."|categ".$data_array['categ']);
            if(SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            }
            if(SYSTEMDK_SHOP_CATITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']);
            }
            if(SYSTEMDK_SHOP_CATEGITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']."|categ".$data_array['categ']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']."|categ".$data_array['categ']);
            }
        } elseif($cache == "subcategi") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|sorti|cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|edit|cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']);
            if(SYSTEMDK_SHOP_HOMEITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|home");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|home");
            }
            if(SYSTEMDK_SHOP_CATITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']);
            }
            if(SYSTEMDK_SHOP_CATEGITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']."|categ".$data_array['categ']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']."|categ".$data_array['categ']);
            }
            if(SYSTEMDK_SHOP_SUBCATEGITEMS_ORDER == "item_position") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|other|cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|admin|cat".$data_array['cat']."|categ".$data_array['categ']."|subcateg".$data_array['subcateg']);
            }
        }
        $this->result = 'sort_done';
    }


    public function shop_orders($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $status = 0;
        if(isset($array['status'])) {
            $status = intval($array['status']);
        }
        $orders_status = 0;
        if(in_array($status,array(0,1,2,3,4,5,6,7,8,9))) {
            $orders_status = $status;
        }
        if(isset($array['num_page']) and intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if($orders_status == 0) {
            $where = "";
        } else {
            $where = "WHERE order_status = '".$orders_status."'";
        }
        $sql = "SELECT count(*) FROM ".PREFIX."_orders_".$this->registry->sitelang." ".$where;
        $result = $this->db->Execute($sql);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT order_id,order_date,ship_date,order_status,order_customer_id FROM ".PREFIX."_orders_".$this->registry->sitelang." ".$where." order by order_date DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        }
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'orders_sql_error';
            $this->error_array = $error;
            return;
        }
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
                $order_id = intval($result->fields['0']);
                $order_date = date("d.m.Y H:i",intval($result->fields['1']));
                if(intval($result->fields['2']) == 0) {
                    $ship_date = "no";
                } else {
                    $ship_date = date("d.m.Y H:i",intval($result->fields['2']));
                }
                $order_status = intval($result->fields['3']);
                $order_customer_id = intval($result->fields['4']);
                if($order_customer_id == 0) {
                    $order_customer_id = "no";
                }
                $orders_all[] = array(
                    "order_id" => $order_id,
                    "order_date" => $order_date,
                    "ship_date" => $ship_date,
                    "order_status" => $order_status,
                    "order_customer_id" => $order_customer_id
                );
                $result->MoveNext();
            }
            $this->result['orders_all'] = $orders_all;
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


    public function shop_orderproc($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $order_id = 0;
        if(isset($array['order_id'])) {
            $order_id = intval($array['order_id']);
        }
        $edit = '';
        if(isset($array['edit'])) {
            if(intval($array['edit']) > 0) {
                $edit = 'edit';
            }
        }
        if($order_id == 0) {
            $this->error = 'order_proc_no_order';
            return;
        }
        $sql = "SELECT a.order_id,a.order_customer_id,a.order_amount,a.order_date,a.ship_date,a.order_status,a.ship_name,a.ship_street,a.ship_house,a.ship_flat,a.ship_city,a.ship_state,a.ship_postal_code,a.ship_country,a.ship_telephone,a.ship_email,a.ship_add_info,a.order_delivery,a.order_pay,a.order_comments,a.order_ip,b.order_item_id,b.item_id,b.order_item_name,b.order_item_price,b.order_item_quantity,null,null,null,null,null,null,e.user_login,e.user_name,e.user_surname,e.user_rights,f.delivery_price,g.pay_price FROM ".PREFIX."_orders_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_order_items_".$this->registry->sitelang." b ON a.order_id=b.order_id LEFT OUTER JOIN ".PREFIX."_users e ON a.order_customer_id=e.user_id LEFT OUTER JOIN ".PREFIX."_delivery_".$this->registry->sitelang." f ON a.order_delivery=f.delivery_id LEFT OUTER JOIN ".PREFIX."_pay_".$this->registry->sitelang." g ON a.order_pay=g.pay_id WHERE a.order_id = ".$order_id." UNION ALL SELECT null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,c.delivery_id,c.delivery_name,c.delivery_price,null,null,null,null,null,null,null,null,null FROM ".PREFIX."_delivery_".$this->registry->sitelang." c UNION ALL SELECT null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,d.pay_id,d.pay_name,d.pay_price,null,null,null,null,null,null FROM ".PREFIX."_pay_".$this->registry->sitelang." d";
        $result = $this->db->Execute($sql);
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'order_proc_sql_error';
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->error = 'order_proc_not_found';
            return;
        }
        while(!$result->EOF) {
            if(!isset($order_all) and intval($result->fields['0']) > 0) {
                $order_id = intval($result->fields['0']);
                $order_customer_id = intval($result->fields['1']);
                $order_customer_login = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['32']));
                $order_customer_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['33']));
                $order_customer_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['34']));
                $order_customer_rights = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['35']));
                $user_type = false;
                if($order_customer_id > 0) {
                    $user_type = 'user';
                    $model_account = singleton::getinstance('account',$this->registry);
                    if(is_object($model_account)) {
                        if($model_account->check_admin_rights($order_customer_rights)) {
                            $user_type = 'admin';
                        }
                    }
                }
                $order_amount = floatval($result->fields['2']);
                $order_amount2 = $order_amount + floatval($result->fields['36']) + (($order_amount * floatval($result->fields['37'])) / 100);
                $order_date = intval($result->fields['3']);
                $order_date2 = date("d.m.Y",$order_date);
                $order_hour = date("H",$order_date);
                $order_minute = date("i",$order_date);
                if(intval($result->fields['4']) == 0) {
                    $ship_date2 = "no";
                    $ship_hour = "no";
                    $ship_minute = "no";
                } else {
                    $ship_date = intval($result->fields['4']);
                    $ship_date2 = date("d.m.Y",$ship_date);
                    $ship_hour = date("H",$ship_date);
                    $ship_minute = date("i",$ship_date);
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
                $order_all[] = array(
                    "order_id" => $order_id,
                    "order_customer_id" => $order_customer_id,
                    "order_amount" => number_format($order_amount,2,'.',' '),
                    "order_amount2" => number_format($order_amount2,2,'.',' '),
                    "order_date" => $order_date2,
                    "order_hour" => $order_hour,
                    "order_minute" => $order_minute,
                    "ship_date" => $ship_date2,
                    "ship_hour" => $ship_hour,
                    "ship_minute" => $ship_minute,
                    "order_status" => $order_status,
                    "ship_name" => $ship_name,
                    "ship_street" => $ship_street,
                    "ship_house" => $ship_house,
                    "ship_flat" => $ship_flat,
                    "ship_city" => $ship_city,
                    "ship_state" => $ship_state,
                    "ship_postalcode" => $ship_postalcode,
                    "ship_country" => $ship_country,
                    "ship_telephone" => $ship_telephone,
                    "ship_email" => $ship_email,
                    "ship_addinfo" => $ship_addinfo,
                    "order_delivery" => $order_delivery,
                    "order_pay" => $order_pay,
                    "order_comments" => $order_comments,
                    "order_customer_login" => $order_customer_login,
                    "order_customer_name" => $order_customer_name,
                    "order_customer_surname" => $order_customer_surname,
                    "order_customer_type" => $user_type,
                    "order_ip" => $order_ip
                );
            }
            if(isset($result->fields['21']) and intval($result->fields['21']) > 0) {
                $order_item_id = intval($result->fields['21']);
                $item_id = intval($result->fields['22']);
                $order_item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['23']));
                $order_item_price = number_format(floatval($result->fields['24']),2,'.',' ');
                $order_item_quantity = intval($result->fields['25']);
                $order_items_all_now = array(
                    "order_item_id" => $order_item_id,
                    "item_id" => $item_id,
                    "order_item_name" => $order_item_name,
                    "order_item_price" => $order_item_price,
                    "order_item_quantity" => $order_item_quantity
                );
                if(!isset($order_items_all) or (isset($order_items_all) and !in_array($order_items_all_now,$order_items_all))) {
                    $order_items_all[] = array(
                        "order_item_id" => $order_item_id,
                        "item_id" => $item_id,
                        "order_item_name" => $order_item_name,
                        "order_item_price" => $order_item_price,
                        "order_item_quantity" => $order_item_quantity
                    );
                }
            }
            if(isset($result->fields['26']) and intval($result->fields['26']) > 0) {
                $delivery_id = intval($result->fields['26']);
                $delivery_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['27']));
                $delivery_price = number_format(floatval($result->fields['28']),2,'.',' ');
                $delivery_all[] = array(
                    "delivery_id" => $delivery_id,
                    "delivery_name" => $delivery_name,
                    "delivery_price" => $delivery_price
                );
            }
            if(isset($result->fields['29']) and intval($result->fields['29']) > 0) {
                $pay_id = intval($result->fields['29']);
                $pay_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['30']));
                $pay_price = floatval($result->fields['31']);
                $pay_all[] = array("pay_id" => $pay_id,"pay_name" => $pay_name,"pay_price" => $pay_price);
            }
            $result->MoveNext();
        }
        if(isset($order_items_all)) {
            $this->result['order_items_all'] = $order_items_all;
        } else {
            $this->result['order_items_all'] = "no";
        }
        if(isset($delivery_all)) {
            $this->result['delivery_all'] = $delivery_all;
        } else {
            $this->result['delivery_all'] = "no";
        }
        if(isset($pay_all)) {
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


    public function shop_order_save($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['order_hour'] = 0;
        $data_array['order_minute'] = 0;
        $data_array['ship_hour'] = 0;
        $data_array['ship_minute'] = 0;
        $data_array['order_edit'] = 0;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if((!isset($array['order_id']) or ($data_array['order_edit'] == 0 and (!isset($array['order_status']) or !isset($array['order_status_old']))) or ($data_array['order_edit'] == 1 and (!isset($array['order_status']) or !isset($array['order_status_old']) or !isset($array['order_date']) or !isset($array['order_hour']) or !isset($array['order_minute']) or !isset($array['ship_date']) or !isset($array['ship_hour']) or !isset($array['ship_minute']) or !isset($array['ship_name']) or !isset($array['ship_street']) or !isset($array['ship_house']) or !isset($array['ship_flat']) or !isset($array['ship_city']) or !isset($array['ship_state']) or !isset($array['ship_postalcode']) or !isset($array['ship_country']) or !isset($array['ship_telephone']) or !isset($array['ship_email']) or !isset($array['ship_addinfo']) or !isset($array['order_delivery']) or !isset($array['order_pay']) or !isset($array['order_comments'])))) or ($data_array['order_id'] == 0 or ($data_array['order_edit'] == 0 and ($data_array['order_status'] == 0 or $data_array['order_status_old'] == 0)) or ($data_array['order_edit'] == 1 and ($data_array['order_date'] == "" or $data_array['order_status'] == 0 or $data_array['order_status_old'] == 0 or $data_array['ship_name'] == "" or $data_array['ship_telephone'] == "" or $data_array['ship_email'] == "" or $data_array['order_delivery'] == 0 or $data_array['order_pay'] == 0)))) {
            $this->error = 'order_not_all_data';
            return;
        }
        if($data_array['order_edit'] == 0) {
            $sql = "UPDATE ".PREFIX."_orders_".$this->registry->sitelang." SET order_status =  '".$data_array['order_status']."' WHERE order_id = ".$data_array['order_id'];
        } elseif($data_array['order_edit'] == 1) {
            $data_array['order_date'] = $this->registry->main_class->format_striptags($data_array['order_date']);
            $data_array['order_date'] = $data_array['order_date']." ".sprintf("%02d",$data_array['order_hour']).":".sprintf("%02d",$data_array['order_minute']);
            $data_array['order_date'] = $this->registry->main_class->time_to_unix($data_array['order_date']);
            if($data_array['order_status'] == 7 or $data_array['order_status'] == 8) {
                $data_array['ship_date'] = $this->registry->main_class->format_striptags($data_array['ship_date']);
                if($data_array['ship_date'] == "") {
                    $data_array['ship_date'] = "'".$this->registry->main_class->get_time()."'";
                } else {
                    $data_array['ship_date'] = $data_array['ship_date']." ".sprintf("%02d",$data_array['ship_hour']).":".sprintf("%02d",$data_array['ship_minute']);
                    $data_array['ship_date'] = "'".$this->registry->main_class->time_to_unix($data_array['ship_date'])."'";
                }
            } else {
                $data_array['ship_date'] = 'NULL';
            }
            $data_array['ship_name'] = $this->registry->main_class->format_striptags($data_array['ship_name']);
            $data_array['ship_name'] = $this->registry->main_class->processing_data($data_array['ship_name']);
            $data_array['ship_telephone'] = $this->registry->main_class->format_striptags($data_array['ship_telephone']);
            $data_array['ship_telephone'] = $this->registry->main_class->processing_data($data_array['ship_telephone']);
            $data_array['ship_email'] = $this->registry->main_class->format_striptags($data_array['ship_email']);
            if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['ship_email']))) {
                $this->error = 'order_email';
                return;
            }
            $data_array['ship_email'] = $this->registry->main_class->processing_data($data_array['ship_email']);
            $ship_addinfo_length = strlen($data_array['ship_addinfo']);
            $order_comments_length = strlen($data_array['order_comments']);
            foreach($data_array as $key => $value) {
                $keys = array(
                    'ship_country',
                    'ship_state',
                    'ship_city',
                    'ship_street',
                    'ship_house',
                    'ship_flat',
                    'ship_postalcode',
                    'ship_addinfo',
                    'order_comments'
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
            if($ship_addinfo_length > 255 or $order_comments_length > 255) {
                $this->error = 'order_post_length';
                return;
            }
            $sql = "UPDATE ".PREFIX."_orders_".$this->registry->sitelang." SET order_amount = (SELECT sum(order_item_price*order_item_quantity) FROM ".PREFIX."_order_items_".$this->registry->sitelang." WHERE order_id = ".$data_array['order_id']."),order_date = '".$data_array['order_date']."',ship_date = ".$data_array['ship_date'].",order_status = '".$data_array['order_status']."',ship_name = ".$data_array['ship_name'].",ship_telephone = ".$data_array['ship_telephone'].",ship_email = ".$data_array['ship_email'].",ship_country = ".$data_array['ship_country'].",ship_state = ".$data_array['ship_state'].",ship_city = ".$data_array['ship_city'].",ship_street = ".$data_array['ship_street'].",ship_house = ".$data_array['ship_house'].",ship_flat = ".$data_array['ship_flat'].",ship_postal_code = ".$data_array['ship_postalcode'].",ship_add_info = ".$data_array['ship_addinfo'].",order_delivery = '".$data_array['order_delivery']."',order_pay = '".$data_array['order_pay']."',order_comments = ".$data_array['order_comments']." WHERE order_id = ".$data_array['order_id'];
        } else {
            $this->error = 'unknown_action';
            return;
        }
        $result = $this->db->Execute($sql);
        if($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'order_save_sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'order_edit_ok';
            return;
        }
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|0");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|".$data_array['order_status']);
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders|".$data_array['order_status_old']);
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order|".$data_array['order_id']);
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order|edit".$data_array['order_id']);
        $this->result = 'order_edit_done';
    }


    public function shop_order_status($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = '';
        if(isset($array['action'])) {
            $action = trim($array['action']);
        }
        if(isset($array['get_item_id'])) {
            $item_id = intval($array['get_item_id']);
        } else {
            $item_id = 0;
        }
        if($item_id == 0 and isset($array['post_item_id']) and is_array($array['post_item_id']) and count($array['post_item_id']) > 0) {
            $item_id_is_arr = 1;
            for($i = 0,$size = count($array['post_item_id']);$i < $size;++$i) {
                if($i == 0) {
                    $item_id = "'".intval($array['post_item_id'][$i])."'";
                } else {
                    $item_id .= ",'".intval($array['post_item_id'][$i])."'";
                }
            }
        } else {
            $item_id_is_arr = 0;
            $item_id = "'".$item_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if($action == "" or $item_id == "'0'") {
            $this->error = 'order_status_not_all_data';
            return;
        }
        if(in_array($action,array(1,2,3,4,5,6,7,8,9))) {
            if($action == 7 or $action == 8) {
                $ship_date = "'".$this->registry->main_class->get_time()."'";
                $sql = "UPDATE ".PREFIX."_orders_".$this->registry->sitelang." SET order_status = '".$action."',ship_date = ".$ship_date." WHERE order_id IN (".$item_id.")";
            } else {
                $sql = "UPDATE ".PREFIX."_orders_".$this->registry->sitelang." SET order_status = '".$action."',ship_date = NULL WHERE order_id IN (".$item_id.")";
            }
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $this->result['shop_message'] = "order_status";
        } elseif($action == "on" or $action == "off") {
            $this->db->StartTrans();
            if($action == "on") {
                $delivery_status = 1;
                $this->result['shop_message'] = "order_status_deliv_status_on";
            } else {
                $delivery_status = 0;
                $this->result['shop_message'] = "order_status_deliv_status_off";
                $sql1 = "SELECT count(delivery_id) as count_rows FROM ".PREFIX."_delivery_".$this->registry->sitelang." WHERE delivery_status = '1'";
                $result1 = $this->db->Execute($sql1);
                if($result1) {
                    if(isset($result1->fields['0'])) {
                        if($item_id_is_arr == 0) {
                            $num_rows = intval($result1->fields['0']);
                        } else {
                            $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                        }
                    } else {
                        $num_rows = 0;
                    }
                    if($num_rows == 0 or ($num_rows < 2 and $item_id_is_arr == 0) or ($num_rows < 1 and $item_id_is_arr == 1)) {
                        $this->error = 'order_status_deliv_items2';
                        return;
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
            $sql = "UPDATE ".PREFIX."_delivery_".$this->registry->sitelang." SET delivery_status = '".$delivery_status."' WHERE delivery_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result === false or (isset($result1) and $result1 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } elseif($action == "on2" or $action == "off2") {
            $this->db->StartTrans();
            if($action == "on2") {
                $pay_status = 1;
                $this->result['shop_message'] = "order_status_pay_status_on";
            } else {
                $pay_status = 0;
                $this->result['shop_message'] = "order_status_pay_status_off";
                $sql1 = "SELECT count(pay_id) as count_rows FROM ".PREFIX."_pay_".$this->registry->sitelang." WHERE pay_status = '1'";
                $result1 = $this->db->Execute($sql1);
                if($result1) {
                    if(isset($result1->fields['0'])) {
                        if($item_id_is_arr == 0) {
                            $num_rows = intval($result1->fields['0']);
                        } else {
                            $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                        }
                    } else {
                        $num_rows = 0;
                    }
                    if($num_rows == 0 or ($num_rows < 2 and $item_id_is_arr == 0) or ($num_rows < 1 and $item_id_is_arr == 1)) {
                        $this->error = 'order_status_pay_items2';
                        return;
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
            $sql = "UPDATE ".PREFIX."_pay_".$this->registry->sitelang." SET pay_status = '".$pay_status."' WHERE pay_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result === false or (isset($result1) and $result1 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } elseif($action == "delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM ".PREFIX."_order_items_".$this->registry->sitelang." WHERE order_id IN (".$item_id.")";
            $result1 = $this->db->Execute($sql1);
            if($result1 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql = "DELETE FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_id IN (".$item_id.")";
            $result = $this->db->Execute($sql);
            if($result) {
                $num_result = $this->db->Affected_Rows();
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result1 === false) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_order_delete";
        } elseif($action == "delete2") {
            $this->db->StartTrans();
            $sql1 = "SELECT count(order_item_id) as count_rows,max(order_id) FROM ".PREFIX."_order_items_".$this->registry->sitelang." WHERE order_id = (SELECT DISTINCT b.order_id FROM ".PREFIX."_order_items_".$this->registry->sitelang." b WHERE b.order_item_id IN (".$item_id."))";
            $result1 = $this->db->Execute($sql1);
            if($result1) {
                if(isset($result1->fields['0'])) {
                    if($item_id_is_arr == 0) {
                        $num_rows = intval($result1->fields['0']);
                    } else {
                        $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                    }
                    $order_id = intval($result1->fields['1']);
                } else {
                    $num_rows = 0;
                }
                if($num_rows == 0 or ($num_rows < 2 and $item_id_is_arr == 0) or ($num_rows < 1 and $item_id_is_arr == 1)) {
                    $this->error = 'order_status_order_items';
                    return;
                }
                $sql = "DELETE FROM ".PREFIX."_order_items_".$this->registry->sitelang." WHERE order_item_id IN (".$item_id.")";
                $result = $this->db->Execute($sql);
                if($result) {
                    $num_result = $this->db->Affected_Rows();
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $sql2 = "UPDATE ".PREFIX."_orders_".$this->registry->sitelang." SET order_amount = (SELECT sum(b.order_item_price*b.order_item_quantity) FROM ".PREFIX."_order_items_".$this->registry->sitelang." b WHERE b.order_id = ".$order_id.") WHERE order_id = ".$order_id;
                $result2 = $this->db->Execute($sql2);
                if($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result1 === false or (isset($result2) and $result2 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_item_delete";
        } elseif($action == "delete3") {
            $this->db->StartTrans();
            $sql1 = "SELECT count(delivery_id) as count_rows FROM ".PREFIX."_delivery_".$this->registry->sitelang;
            $result1 = $this->db->Execute($sql1);
            if($result1) {
                if(isset($result1->fields['0'])) {
                    if($item_id_is_arr == 0) {
                        $num_rows = intval($result1->fields['0']);
                    } else {
                        $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                    }
                } else {
                    $num_rows = 0;
                }
                if($num_rows == 0 or ($num_rows < 2 and $item_id_is_arr == 0) or ($num_rows < 1 and $item_id_is_arr == 1)) {
                    $this->error = 'order_status_deliv_items';
                    return;
                }
                $sql2 = "DELETE FROM ".PREFIX."_order_items_".$this->registry->sitelang." WHERE order_id IN (SELECT order_id FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_delivery IN (".$item_id."))";
                $result2 = $this->db->Execute($sql2);
                if($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $sql3 = "DELETE FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_delivery IN (".$item_id.")";
                $result3 = $this->db->Execute($sql3);
                if($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $sql = "DELETE FROM ".PREFIX."_delivery_".$this->registry->sitelang." WHERE delivery_id IN (".$item_id.")";
                $result = $this->db->Execute($sql);
                if($result) {
                    $num_result = $this->db->Affected_Rows();
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result1 === false or (isset($result2) and $result2 === false) or (isset($result3) and $result3 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_deliv_delete";
        } elseif($action == "delete4") {
            $this->db->StartTrans();
            $sql1 = "SELECT count(pay_id) as count_rows FROM ".PREFIX."_pay_".$this->registry->sitelang;
            $result1 = $this->db->Execute($sql1);
            if($result1) {
                if(isset($result1->fields['0'])) {
                    if($item_id_is_arr == 0) {
                        $num_rows = intval($result1->fields['0']);
                    } else {
                        $num_rows = intval($result1->fields['0']) - intval(count($array['post_item_id']));
                    }
                } else {
                    $num_rows = 0;
                }
                if($num_rows == 0 or ($num_rows < 2 and $item_id_is_arr == 0) or ($num_rows < 1 and $item_id_is_arr == 1)) {
                    $this->error = 'order_status_pay_items';
                    return;
                }
                $sql2 = "DELETE FROM ".PREFIX."_order_items_".$this->registry->sitelang." WHERE order_id IN (SELECT order_id FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_pay IN (".$item_id."))";
                $result2 = $this->db->Execute($sql2);
                if($result2 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $sql3 = "DELETE FROM ".PREFIX."_orders_".$this->registry->sitelang." WHERE order_pay IN (".$item_id.")";
                $result3 = $this->db->Execute($sql3);
                if($result3 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $sql = "DELETE FROM ".PREFIX."_pay_".$this->registry->sitelang." WHERE pay_id IN (".$item_id.")";
                $result = $this->db->Execute($sql);
                if($result) {
                    $num_result = $this->db->Affected_Rows();
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result1 === false or (isset($result2) and $result2 === false) or (isset($result3) and $result3 === false)) {
                $result = false;
            }
            $this->db->CompleteTrans();
            $this->result['shop_message'] = "order_status_pay_delete";
        } else {
            $this->error = 'unknown_action';
            return;
        }
        if($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'not_save';
            return;
        }
        if(in_array($action,array(1,2,3,4,5,6,7,8,9)) or $action == "delete") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order");
            if($action == "delete") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
            }
        } elseif($action == "delete3" or $action == "on" or $action == "off") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|delivs");
            if($item_id_is_arr == 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|deliv|edit".intval($array['get_item_id']));
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|deliv");
            }
            if($action == "delete3") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
            }
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|admin");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|other");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|admin");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|other");
        } elseif($action == "delete4" or $action == "on2" or $action == "off2") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|pays");
            if($item_id_is_arr == 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|pay|edit".intval($array['get_item_id']));
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|pay");
            }
            if($action == "delete4") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orders");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi");
            }
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|admin");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|other");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|admin");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|other");
        } else {
            $this->result['adminmodules_shop_order_id'] = $order_id;
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order|".$order_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order|edit".$order_id);
            if($item_id_is_arr == 0) {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi|".$order_id."|editi".intval($array['get_item_id']));
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi|".$order_id);
            }
        }
    }


    public function shop_orderi_edit($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['item_id'] = 0;
        $data_array['order_id'] = 0;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'item_id',
                    'order_id'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if($data_array['item_id'] == 0 or $data_array['order_id'] == 0) {
            $this->error = 'no_item';
            return;
        }
        $sql = "SELECT a.order_item_id,a.order_id,a.item_id,a.order_item_name,a.order_item_price,a.order_item_quantity,b.item_catalog_id,b.item_category_id,b.item_subcategory_id,f.catalog_id as categ_cat_id,g.category_id as subcateg_categ_id,h.catalog_id as subcateg_cat_id FROM ".PREFIX."_order_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_items_".$this->registry->sitelang." b ON a.item_id = b.item_id LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON b.item_catalog_id = c.catalog_id and b.item_catalog_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON b.item_category_id = d.category_id and b.item_category_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and b.item_category_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON b.item_subcategory_id = e.subcategory_id and b.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and b.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and b.item_subcategory_id is NOT NULL WHERE a.order_item_id = ".$data_array['item_id']." and a.order_id = ".$data_array['order_id'];
        $result = $this->db->Execute($sql);
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->error = 'not_found_item';
            return;
        }
        while(!$result->EOF) {
            $order_item_id = intval($result->fields['0']);
            $order_id = intval($result->fields['1']);
            $item_id2 = intval($result->fields['2']);
            $order_item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
            $order_item_price = floatval($result->fields['4']);
            $order_item_quantity = intval($result->fields['5']);
            $item_catalog_id = intval($result->fields['6']);
            $item_category_id = intval($result->fields['7']);
            if($item_category_id > 0) {
                $item_categ_cat_id = intval($result->fields['9']);
            } else {
                $item_categ_cat_id = "no";
            }
            $item_subcategory_id = intval($result->fields['8']);
            if($item_subcategory_id > 0) {
                $item_subcateg_categ_id = intval($result->fields['10']);
                $item_subcateg_cat_id = intval($result->fields['11']);
            } else {
                $item_subcateg_categ_id = "no";
                $item_subcateg_cat_id = "no";
            }
            $order_item_all[] = array(
                "order_item_id" => $order_item_id,
                "order_id" => $order_id,
                "item_id" => $item_id2,
                "order_item_name" => $order_item_name,
                "order_item_price" => number_format(floatval($order_item_price),2,'.',' '),
                "order_item_quantity" => $order_item_quantity,
                "item_catalog_id" => $item_catalog_id,
                "item_category_id" => $item_category_id,
                "item_categ_cat_id" => $item_categ_cat_id,
                "item_subcategory_id" => $item_subcategory_id,
                "item_subcateg_categ_id" => $item_subcateg_categ_id,
                "item_subcateg_cat_id" => $item_subcateg_cat_id
            );
            $result->MoveNext();
        }
        $this->result['order_item_all'] = $order_item_all;
    }


    public function shop_orderi_save($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($data_array['order_item_price'])) {
            $data_array['order_item_price'] = $this->registry->main_class->float_to_db($data_array['order_item_price']);
        }
        if((!isset($array['order_item_id']) or !isset($array['order_id']) or !isset($array['order_item_name']) or !isset($array['order_item_price']) or !isset($array['order_item_quantity'])) or ($data_array['order_item_id'] == 0 or $data_array['order_id'] == 0 or $data_array['order_item_name'] == "" or $data_array['order_item_quantity'] == 0)) {
            $this->error = 'not_all_data';
            return;
        }
        $data_array['order_item_name'] = $this->registry->main_class->format_striptags($data_array['order_item_name']);
        $data_array['order_item_name'] = $this->registry->main_class->processing_data($data_array['order_item_name']);
        $this->db->StartTrans();
        $sql = "UPDATE ".PREFIX."_order_items_".$this->registry->sitelang." SET order_item_name = ".$data_array['order_item_name'].",order_item_price = '".$data_array['order_item_price']."',order_item_quantity = '".$data_array['order_item_quantity']."' WHERE order_item_id = ".$data_array['order_item_id'];
        $result = $this->db->Execute($sql);
        if($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $sql2 = "UPDATE ".PREFIX."_orders_".$this->registry->sitelang." SET order_amount = (SELECT sum(b.order_item_price*b.order_item_quantity) FROM ".PREFIX."_order_items_".$this->registry->sitelang." b WHERE b.order_id = ".$data_array['order_id'].") WHERE order_id = ".$data_array['order_id'];
        $result2 = $this->db->Execute($sql2);
        if($result2 === false) {
            $result = false;
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        $this->db->CompleteTrans();
        if($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'not_save';
            return;
        }
        $this->result['adminmodules_shop_order_id'] = $data_array['order_id'];
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order|".$data_array['order_id']);
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order|edit".$data_array['order_id']);
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|orderi|".$data_array['order_id']."|editi".$data_array['order_item_id']);
        $this->result['shop_message'] = "order_item_save_done";
    }


    public function shop_methods($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['pay'] = 0;
        if(!empty($array)) {
            foreach($array as $key => $value) {
                $keys = array(
                    'pay',
                    'num_page'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $type = "pays";
        if($data_array['pay'] == 0) {
            $type = "delivs";
        }
        if(isset($data_array['num_page']) and $data_array['num_page'] != 0) {
            $num_page = $data_array['num_page'];
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if($type == "delivs") {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_delivery_".$this->registry->sitelang;
            $sql = "SELECT a.delivery_id,a.delivery_name,a.delivery_price,a.delivery_status FROM ".PREFIX."_delivery_".$this->registry->sitelang." a";
        } else {
            $sql0 = "SELECT count(*) FROM ".PREFIX."_pay_".$this->registry->sitelang;
            $sql = "SELECT a.pay_id,a.pay_name,a.pay_price,a.pay_status FROM ".PREFIX."_pay_".$this->registry->sitelang." a";
        }
        $result = $this->db->Execute($sql0);
        if($result) {
            $num_rows = intval($result->fields['0']);
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
        }
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        }
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
                $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $item_price = number_format(floatval($result->fields['2']),2,'.',' ');
                $item_status = intval($result->fields['3']);
                $items_all[] = array(
                    "item_id" => $item_id,
                    "item_name" => $item_name,
                    "item_price" => $item_price,
                    "item_status" => $item_status
                );
                $result->MoveNext();
            }
            $this->result['items_all'] = $items_all;
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


    public function shop_edit_method($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['item_id'] = 0;
        $data_array['pay'] = 0;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if($data_array['pay'] == 0) {
            $type = 'deliv';
        }
        if($data_array['item_id'] == 0) {
            $this->error = 'no_item_'.$type;
            return;
        }
        if($type == 'deliv') {
            $sql = "SELECT delivery_id,delivery_name,delivery_price,delivery_status FROM ".PREFIX."_delivery_".$this->registry->sitelang." WHERE delivery_id = ".$data_array['item_id'];
        } else {
            $sql = "SELECT pay_id,pay_name,pay_price,pay_status FROM ".PREFIX."_pay_".$this->registry->sitelang." WHERE pay_id = ".$data_array['item_id'];
        }
        $result = $this->db->Execute($sql);
        if(!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist < 1) {
            $this->error = 'not_found_'.$type;
            return;
        }
        while(!$result->EOF) {
            $item_id = intval($result->fields['0']);
            $item_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $item_price = floatval($result->fields['2']);
            $item_status = intval($result->fields['3']);
            $item_all[] = array(
                "item_id" => $item_id,
                "item_name" => $item_name,
                "item_price" => $item_price,
                "item_status" => $item_status
            );
            $result->MoveNext();
        }
        $this->result['item_all'] = $item_all;
        $this->result['adminmodules_shop_method'] = $type;
    }


    public function shop_method_save($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['pay'] = 0;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($data_array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($data_array['item_price']);
        }
        $type = "pay";
        if($data_array['pay'] == 0) {
            $type = "deliv";
        }
        if((!isset($array['item_id']) or !isset($array['item_name']) or !isset($array['item_price']) or !isset($array['item_status'])) or ($data_array['item_id'] == 0 or $data_array['item_name'] == "")) {
            $this->error = 'not_all_data';
            return;
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        if($type == "deliv") {
            $sql = "UPDATE ".PREFIX."_delivery_".$this->registry->sitelang." SET delivery_name = ".$data_array['item_name'].",delivery_price = '".$data_array['item_price']."',delivery_status = '".$data_array['item_status']."' WHERE delivery_id = ".$data_array['item_id'];
        } else {
            $sql = "UPDATE ".PREFIX."_pay_".$this->registry->sitelang." SET pay_name = ".$data_array['item_name'].",pay_price = '".$data_array['item_price']."',pay_status = '".$data_array['item_status']."' WHERE pay_id = ".$data_array['item_id'];
        }
        $result = $this->db->Execute($sql);
        if($result) {
            $num_result = $this->db->Affected_Rows();
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
        }
        if($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        } elseif($result !== false and $num_result == 0) {
            $this->error = 'not_save';
            return;
        }
        if($type == "deliv") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|delivs");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|deliv|edit".$data_array['item_id']);
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|pays");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|pay|edit".$data_array['item_id']);
        }
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|admin");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|other");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|admin");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|other");
        $this->result = $type.'_edit_ok';
    }


    public function shop_method_add($pay) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $pay = intval($pay);
        $type = "pay";
        if($pay == 0) {
            $type = "deliv";
        }
        $this->result['adminmodules_shop_method'] = $type;
    }


    public function shop_method_save2($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $data_array['pay'] = 0;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if(isset($data_array['item_price'])) {
            $data_array['item_price'] = $this->registry->main_class->float_to_db($data_array['item_price']);
        }
        $type = "pay";
        if($data_array['pay'] == 0) {
            $type = "deliv";
        }
        if((!isset($array['item_name']) or !isset($array['item_price']) or !isset($array['item_status'])) or $data_array['item_name'] == "") {
            $this->error = 'not_all_data';
            return;
        }
        $data_array['item_name'] = $this->registry->main_class->format_striptags($data_array['item_name']);
        $data_array['item_name'] = $this->registry->main_class->processing_data($data_array['item_name']);
        if($type == "deliv") {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_delivery_id_".$this->registry->sitelang,'delivery_id');
            $sql = "INSERT INTO ".PREFIX."_delivery_".$this->registry->sitelang." (".$sequence_array['field_name_string']."delivery_name,delivery_price,delivery_status) VALUES (".$sequence_array['sequence_value_string']."".$data_array['item_name'].",'".$data_array['item_price']."','".$data_array['item_status']."')";
        } else {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX."_pay_id_".$this->registry->sitelang,'pay_id');
            $sql = "INSERT INTO ".PREFIX."_pay_".$this->registry->sitelang." (".$sequence_array['field_name_string']."pay_name,pay_price,pay_status) VALUES (".$sequence_array['sequence_value_string']."".$data_array['item_name'].",'".$data_array['item_price']."','".$data_array['item_status']."')";
        }
        $result = $this->db->Execute($sql);
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->error = 'sql_error';
            $this->error_array = $error;
            return;
        }
        if($type == "deliv") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|delivs");
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|pays");
        }
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|shop|order");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|admin");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout|display|other");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|admin");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|shop|checkout2|display|other");
        $this->result = $type.'_add_done';
    }


    public function shop_config() {
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
    }


    function shop_config_save($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if(!empty($array)) {
            foreach($array as $key => $value) {
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
        if((!isset($array['systemdk_shop_catalogs_order']) or !isset($array['systemdk_shop_homeitems_perpage']) or !isset($array['systemdk_shop_homeitems_order']) or !isset($array['systemdk_shop_categories_order']) or !isset($array['systemdk_shop_catitems_perpage']) or !isset($array['systemdk_shop_catitems_order']) or !isset($array['systemdk_shop_subcategories_order']) or !isset($array['systemdk_shop_categitems_perpage']) or !isset($array['systemdk_shop_categitems_order']) or !isset($array['systemdk_shop_subcategitems_perpage']) or !isset($array['systemdk_shop_subcategitems_order']) or !isset($array['systemdk_shop_itemimage_path']) or !isset($array['systemdk_shop_itemimage_position']) or !isset($array['systemdk_shop_itemimage_maxsize']) or !isset($array['systemdk_shop_itemimage_maxwidth']) or !isset($array['systemdk_shop_itemimage_maxheight']) or !isset($array['systemdk_shop_itemimage_width']) or !isset($array['systemdk_shop_itemimage_width2']) or !isset($array['systemdk_shop_itemimage_width3']) or !isset($array['systemdk_shop_itemimage_height3']) or !isset($array['systemdk_shop_image_path']) or !isset($array['systemdk_shop_image_position']) or !isset($array['systemdk_shop_image_maxsize']) or !isset($array['systemdk_shop_image_maxwidth']) or !isset($array['systemdk_shop_image_maxheight']) or !isset($array['systemdk_shop_image_width']) or !isset($array['systemdk_shop_items_columns']) or !isset($array['systemdk_shop_items_underparent']) or !isset($array['systemdk_shop_valuta']) or !isset($array['systemdk_shop_antispam_ipaddr']) or !isset($array['systemdk_shop_antispam_num'])) or ($data_array['systemdk_shop_catalogs_order'] == "" or $data_array['systemdk_shop_homeitems_perpage'] == 0 or $data_array['systemdk_shop_homeitems_order'] == "" or $data_array['systemdk_shop_categories_order'] == "" or $data_array['systemdk_shop_catitems_perpage'] == 0 or $data_array['systemdk_shop_catitems_order'] == "" or $data_array['systemdk_shop_subcategories_order'] == "" or $data_array['systemdk_shop_categitems_perpage'] == 0 or $data_array['systemdk_shop_categitems_order'] == "" or $data_array['systemdk_shop_subcategitems_perpage'] == 0 or $data_array['systemdk_shop_subcategitems_order'] == "" or $data_array['systemdk_shop_itemimage_path'] == "" or $data_array['systemdk_shop_itemimage_position'] == "" or $data_array['systemdk_shop_itemimage_maxsize'] == 0 or $data_array['systemdk_shop_itemimage_maxwidth'] == 0 or $data_array['systemdk_shop_itemimage_maxheight'] == 0 or $data_array['systemdk_shop_itemimage_width'] == 0 or $data_array['systemdk_shop_itemimage_width2'] == 0 or $data_array['systemdk_shop_itemimage_width3'] == 0 or $data_array['systemdk_shop_itemimage_height3'] == 0 or $data_array['systemdk_shop_image_path'] == "" or $data_array['systemdk_shop_image_position'] == "" or $data_array['systemdk_shop_image_maxsize'] == 0 or $data_array['systemdk_shop_image_maxwidth'] == 0 or $data_array['systemdk_shop_image_maxheight'] == 0 or $data_array['systemdk_shop_image_width'] == 0 or $data_array['systemdk_shop_items_columns'] == 0 or $data_array['systemdk_shop_valuta'] == "")) {
            $this->error = 'not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            if(in_array($key,$keys)) {
                $data_array[$key] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($value);
            }
        }
        @chmod("../modules/shop/shop_config.inc",0777);
        $file = @fopen("../modules/shop/shop_config.inc","w");
        if(!$file) {
            $this->error = 'no_file';
            return;
        }
        $content = "<?php\n";
        $content .= 'define("SYSTEMDK_SHOP_CATALOGS_ORDER","'.$data_array['systemdk_shop_catalogs_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_HOMEITEMS_PERPAGE",'.$data_array['systemdk_shop_homeitems_perpage'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_HOMEITEMS_ORDER","'.$data_array['systemdk_shop_homeitems_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATEGORIES_ORDER","'.$data_array['systemdk_shop_categories_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATITEMS_PERPAGE",'.$data_array['systemdk_shop_catitems_perpage'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATITEMS_ORDER","'.$data_array['systemdk_shop_catitems_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_SUBCATEGORIES_ORDER","'.$data_array['systemdk_shop_subcategories_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATEGITEMS_PERPAGE",'.$data_array['systemdk_shop_categitems_perpage'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_CATEGITEMS_ORDER","'.$data_array['systemdk_shop_categitems_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_SUBCATEGITEMS_PERPAGE",'.$data_array['systemdk_shop_subcategitems_perpage'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_SUBCATEGITEMS_ORDER","'.$data_array['systemdk_shop_subcategitems_order']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_PATH","'.$data_array['systemdk_shop_itemimage_path']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_POSITION","'.$data_array['systemdk_shop_itemimage_position']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_MAXSIZE",'.$data_array['systemdk_shop_itemimage_maxsize'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_MAXWIDTH",'.$data_array['systemdk_shop_itemimage_maxwidth'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_MAXHEIGHT",'.$data_array['systemdk_shop_itemimage_maxheight'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_WIDTH",'.$data_array['systemdk_shop_itemimage_width'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_WIDTH2",'.$data_array['systemdk_shop_itemimage_width2'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_WIDTH3",'.$data_array['systemdk_shop_itemimage_width3'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMIMAGE_HEIGHT3",'.$data_array['systemdk_shop_itemimage_height3'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_PATH","'.$data_array['systemdk_shop_image_path']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_POSITION","'.$data_array['systemdk_shop_image_position']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_MAXSIZE",'.$data_array['systemdk_shop_image_maxsize'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_MAXWIDTH",'.$data_array['systemdk_shop_image_maxwidth'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_MAXHEIGHT",'.$data_array['systemdk_shop_image_maxheight'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_IMAGE_WIDTH",'.$data_array['systemdk_shop_image_width'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMS_COLUMNS",'.$data_array['systemdk_shop_items_columns'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ITEMS_UNDERPARENT",'.$data_array['systemdk_shop_items_underparent'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_VALUTA","'.$data_array['systemdk_shop_valuta']."\");\n";
        $content .= 'define("SYSTEMDK_SHOP_ANTISPAM_IPADDR",'.$data_array['systemdk_shop_antispam_ipaddr'].");\n";
        $content .= 'define("SYSTEMDK_SHOP_ANTISPAM_NUM",'.$data_array['systemdk_shop_antispam_num'].");\n";
        $write_file = @fwrite($file,$content);
        @fclose($file);
        @chmod("../modules/shop/shop_config.inc",0604);
        if(!$write_file) {
            $this->error = 'no_write';
            return;
        }
        $this->registry->main_class->systemdk_clearcache("modules|shop");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|shop");
        $this->result = 'config_ok';
    }
}
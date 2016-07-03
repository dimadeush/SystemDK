<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class news extends model_base
{


    private $error;
    private $error_array;
    private $result;
    public $num_news;
    public $image_path;
    public $image_position;
    public $image_max_size;
    public $image_max_width;
    public $image_max_height;
    public $image_width;
    public $image_width2;


    public function __construct($registry)
    {
        parent::__construct($registry);
        require_once('news_config.inc');
        $this->num_news = MODULE_NEWS_NUM_NEWS;
        $this->image_path = MODULE_NEWS_IMAGE_PATH;
        $this->image_position = MODULE_NEWS_IMAGE_POSITION;
        $this->image_max_size = MODULE_NEWS_IMAGE_MAX_SIZE;
        $this->image_max_width = MODULE_NEWS_IMAGE_MAX_WIDTH;
        $this->image_max_height = MODULE_NEWS_IMAGE_MAX_HEIGHT;
        $this->image_width = MODULE_NEWS_IMAGE_WIDTH;
        $this->image_width2 = MODULE_NEWS_IMAGE_WIDTH2;
        $this->process_auto();
    }


    public function process_auto()
    {
        $sql1 = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang . " SET news_auto = '0' WHERE news_auto = '1' and news_date <= "
                . $this->registry->main_class->get_time();
        $this->db->Execute($sql1);
        $num_result1 = $this->db->Affected_Rows();
        if ($num_result1 > 0) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showprogramm");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogramm");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show");
        }
        $sql2 = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang
                . " SET news_status='0', news_term_expire=NULL, news_term_expire_action=NULL WHERE news_auto = '0' and news_term_expire <= '"
                . $this->registry->main_class->get_time() . "' AND news_term_expire_action = 'off'";
        $this->db->Execute($sql2);
        $num_result2 = $this->db->Affected_Rows();
        $sql3 = "DELETE FROM " . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_auto = '0' and news_term_expire <= '"
                . $this->registry->main_class->get_time() . "' AND news_term_expire_action = 'delete'";
        $this->db->Execute($sql3);
        $num_result3 = $this->db->Affected_Rows();
        if ($num_result2 > 0 || $num_result3 > 0) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit");
        }
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($num_page = false, $cat_id = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        if (intval($cat_id) != 0) {
            $cat_id = intval($cat_id);
            $input = "a.news_category_id = '" . $cat_id . "'";
        } else {
            $cat_id = 'no';
            $input = "a.news_on_home = '1'";
        }
        $sql_add = "a.news_value_view = '0'";
        if ($this->registry->main_class->is_admin()) {
            $sql_add .= " or a.news_value_view = '1' or a.news_value_view = '3' or a.news_value_view = '2'";
        } elseif ($this->registry->main_class->is_user()) {
            $sql_add .= " or a.news_value_view = '1'";
        } else {
            $sql_add .= " or a.news_value_view = '3'";
        }
        $num_news = $this->num_news;
        $offset = ($num_page - 1) * $num_news;
        $sql = "SELECT count(a.news_id) FROM " . PREFIX . "_news_" . $this->registry->sitelang . " a WHERE " . $input . " and a.news_status = '1' and (" . $sql_add
               . ") and a.news_auto = '0'";
        $result = $this->db->Execute($sql);
        if ($result) {
            $total_news = intval($result->fields['0']);
            $sql =
                "SELECT a.news_id,a.news_author,a.news_category_id,a.news_title,a.news_date,a.news_images,a.news_short_text,a.news_read_counter,a.news_notes,a.news_value_view,a.news_term_expire,a.news_term_expire_action,a.news_link, b.news_category_name FROM "
                . PREFIX . "_news_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_news_categories_" . $this->registry->sitelang
                . " b on a.news_category_id=b.news_category_id WHERE " . $input . " and a.news_status='1' and (" . $sql_add
                . ") and a.news_auto = '0' ORDER BY a.news_date DESC"; //LIMIT $offset, $num_news
            $result = $this->db->SelectLimit($sql, $num_news, $offset);
        }
        if (!$result) {
            $this->result['news_all'] = "no";
            $this->result['html'] = "no";

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'incorrect_page';

            return;
        }
        if ($row_exist < 1) {
            $this->result['news_all'] = "no";
            $this->result['html'] = "no";

            return;
        }
        while (!$result->EOF) {
            $news_id = intval($result->fields['0']);
            $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $news_category_id = intval($result->fields['2']);
            if (empty($news_category_id)) {
                $news_category_id = "no";
            }
            $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
            $news_date = intval($result->fields['4']);
            $news_date = date("d.m.y H:i", $news_date);
            $news_images = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            if (empty($news_images)) {
                $news_images = "no";
            }
            $news_short_text = $this->registry->main_class->extracting_data($result->fields['6']);
            $news_readcounter = intval($result->fields['7']);
            $news_notes = $this->registry->main_class->extracting_data($result->fields['8']);
            if (empty($news_notes)) {
                $news_notes = "no";
            }
            $news_valueview = intval($result->fields['9']);
            if ($news_valueview == "0") {
                $news_valueview = "all";
            } elseif ($news_valueview == "1") {
                $news_valueview = "users&admins";
            } elseif ($news_valueview == "2") {
                $news_valueview = "admins";
            } elseif ($news_valueview == "3") {
                $news_valueview = "guests";
            } else {
                $news_valueview = "no";
            }
            $news_termexpire = intval($result->fields['10']);
            if ($news_termexpire !== 0) {
                $news_termexpire = date("d.m.y H:i", $news_termexpire);
            } else {
                $news_termexpire = "no";
            }
            $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
            $news_link = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
            if (empty($news_link)) {
                $news_link = "no";
            }
            if ($news_category_id > 0) {
                $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                if (empty($news_category_name)) {
                    $news_category_name = "no";
                }
            } else {
                $news_category_name = "no";
            }
            $news_all[] = [
                "news_id"               => $news_id,
                "news_author"           => $news_author,
                "news_category_id"      => $news_category_id,
                "news_title"            => $news_title,
                "news_date"             => $news_date,
                "news_images"           => $news_images,
                "news_short_text"       => $news_short_text,
                "news_readcounter"      => $news_readcounter,
                "news_notes"            => $news_notes,
                "news_valueview"        => $news_valueview,
                "news_termexpire"       => $news_termexpire,
                "news_termexpireaction" => $news_termexpireaction,
                "news_category_name"    => $news_category_name,
                "news_link"             => $news_link,
            ];
            $result->MoveNext();
        }
        $this->result['news_all'] = $news_all;
        $this->result['totalnews'] = $total_news;
        if (isset($total_news)) {
            $num_pages = @ceil($total_news / $num_news);
        } else {
            $num_pages = 0;
        }
        if ($num_pages > 1) {
            if ($num_page > 1) {
                $prev_page = $num_page - 1;
                $this->result['prevpage'] = $prev_page;
            } else {
                $this->result['prevpage'] = 'no';
            }
            for ($i = 1; $i < $num_pages + 1; $i++) {
                if ($i == $num_page) {
                    $html[] = ["number" => $i, "param1" => "1"];
                } else {
                    $pagelink = 8;
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
                $next_page = $num_page + 1;
                $this->result['nextpage'] = $next_page;
            } else {
                $this->result['nextpage'] = 'no';
            }
            $this->result['html'] = $html;
            $this->result['num_pages'] = $num_pages;
        } else {
            $this->result['html'] = 'no';
        }
        $this->result['page_title'] = "module|news";
        $this->result['image_position'] = $this->image_position;
        $this->result['image_path'] = $this->image_path;
        $this->result['image_width'] = $this->image_width;
        $this->result['news_category_id'] = $cat_id;
    }


    public function newsread($news_id, $num_page = false, $only_counter = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['page_title'] = "module|news";
        if (intval($news_id) > 0) {
            $news_id = intval($news_id);
        } else {
            $this->error = 'notselectnews';
            $this->result['news_all'] = $this->error;

            return;
        }
        if (intval($num_page) > 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        if ($only_counter) {
            if ($num_page == 1) {
                $sql0 = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang . " SET news_read_counter=news_read_counter+1 WHERE news_auto = '0' and news_id = '"
                        . $news_id . "'";
                $this->db->Execute($sql0);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit|" . $news_id);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|admin");
            }

            return;
        }
        $sql_add = "a.news_value_view = '0'";
        if ($this->registry->main_class->is_admin()) {
            $sql_add .= " or a.news_value_view = '1' or a.news_value_view = '3' or a.news_value_view = '2'";
        } elseif ($this->registry->main_class->is_user()) {
            $sql_add .= " or a.news_value_view = '1'";
        } else {
            $sql_add .= " or a.news_value_view = '3'";
        }
        $sql =
            "SELECT a.news_id,a.news_author,a.news_category_id,a.news_title,a.news_date,a.news_images,a.news_short_text,a.news_content,a.news_read_counter,a.news_notes,a.news_value_view,a.news_status,a.news_term_expire,a.news_term_expire_action,a.news_on_home,a.news_link, b.news_category_name FROM "
            . PREFIX . "_news_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_news_categories_" . $this->registry->sitelang
            . " b on a.news_category_id=b.news_category_id WHERE a.news_id='" . $news_id . "' and news_auto = '0' and (" . $sql_add . ")";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'notfindnews';
            $this->result['news_all'] = $this->error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'notfindnews';
            $this->result['news_all'] = $this->error;

            return;
        }
        $news_id = intval($result->fields['0']);
        $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $news_category_id = intval($result->fields['2']);
        if (empty($news_category_id)) {
            $news_category_id = "no";
        }
        $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        $news_date = intval($result->fields['4']);
        $news_date = date("d.m.y H:i", $news_date);
        $news_images = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        if (empty($news_images)) {
            $news_images = "no";
        }
        $news_short_text = $this->registry->main_class->extracting_data($result->fields['6']);
        $news_content = $this->registry->main_class->extracting_data($result->fields['7']);
        $news_readcounter = intval($result->fields['8']);
        $news_notes = $this->registry->main_class->extracting_data($result->fields['9']);
        if (empty($news_notes)) {
            $news_notes = "no";
        }
        $news_status = intval($result->fields['11']);
        if ($news_status == 0) {
            $this->error = 'notactivnews';
            $this->result['news_all'] = $this->error;

            return;
        }
        $news_content = $this->registry->main_class->split_content_by_pages($news_content);
        $count_num_pages = count($news_content);
        if ($num_page > $count_num_pages) {
            $num_page = 1;
        }
        $news_content = $news_content[$num_page - 1];
        $news_content = $this->registry->main_class->search_player_entry($news_content);
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
            $this->result['pages_menu'][] = [
                "current_page"    => $num_page,
                "next_page"       => $next_page,
                "prev_page"       => $prev_page,
                "count_num_pages" => $count_num_pages,
            ];
        } else {
            $this->result['pages_menu'] = 'no';
        }
        $news_valueview = intval($result->fields['10']);
        if ($news_valueview == "0") {
            $news_valueview = "all";
        } elseif ($news_valueview == "1") {
            $news_valueview = "users&admins";
        } elseif ($news_valueview == "2") {
            $news_valueview = "admins";
        } elseif ($news_valueview == "3") {
            $news_valueview = "guests";
        } else {
            $news_valueview = "no";
        }
        $news_termexpire = intval($result->fields['12']);
        if ($news_termexpire !== 0) {
            $news_termexpire = date("d.m.y H:i", $news_termexpire);
        } else {
            $news_termexpire = "no";
        }
        $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
        $news_onhome = intval($result->fields['14']);
        $news_link = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
        if (empty($news_link)) {
            $news_link = "no";
        }
        if ($news_category_id > 0) {
            $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
            if ($news_category_name == "") {
                $news_category_name = "no";
            }
        } else {
            $news_category_name = "no";
        }
        $news_all['1'] = [
            "news_id"               => $news_id,
            "news_author"           => $news_author,
            "news_category_id"      => $news_category_id,
            "news_title"            => $news_title,
            "news_date"             => $news_date,
            "news_images"           => $news_images,
            "news_short_text"       => $news_short_text,
            "news_content"          => $news_content,
            "news_readcounter"      => $news_readcounter,
            "news_notes"            => $news_notes,
            "news_valueview"        => $news_valueview,
            "news_termexpire"       => $news_termexpire,
            "news_termexpireaction" => $news_termexpireaction,
            "news_category_name"    => $news_category_name,
            "news_onhome"           => $news_onhome,
            "news_link"             => $news_link,
        ];
        $this->result['news_all'] = $news_all;
        $this->result['image_position'] = $this->image_position;
        $this->result['image_path'] = $this->image_path;
        $this->result['image_width2'] = $this->image_width2;
        $this->result['page_title'] = 'module|news';
    }
}
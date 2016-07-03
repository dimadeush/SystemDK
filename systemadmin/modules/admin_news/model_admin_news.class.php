<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class admin_news extends model_base
{


    /**
     * @var news
     */
    private $model_news;
    private $error;
    private $error_array;
    private $result;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_news = singleton::getinstance('news', $registry);
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $sql = "SELECT (SELECT count(*) FROM " . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_auto = '0') as count1, (SELECT count(*) FROM "
               . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_auto = '1') as count2 " . $this->registry->main_class->check_db_need_from_clause();
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $count_news = intval($result->fields['0']);
            } else {
                $count_news = 0;
            }
            if (isset($result->fields['1'])) {
                $count_wait_news = intval($result->fields['1']);
            } else {
                $count_wait_news = 0;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->result['modules_news_num_news'] = $count_news;
        $this->result['modules_news_num_programmnews'] = $count_wait_news;
    }


    public function news_current($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
            $temp = "program";
            $news_auto = '1';
            $sql_order = "ASC";
        } else {
            $temp = "current";
            $news_auto = '0';
            $sql_order = "DESC";
        }
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM " . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_auto = '" . $news_auto . "'";
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql =
                "SELECT a.news_id,a.news_author,a.news_category_id,a.news_title,a.news_date,a.news_value_view,a.news_status,a.news_term_expire,a.news_term_expire_action,a.news_on_home,b.news_category_name FROM "
                . PREFIX . "_news_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_news_categories_" . $this->registry->sitelang
                . " b on a.news_category_id = b.news_category_id WHERE a.news_auto = '" . $news_auto . "' order by a.news_date " . $sql_order;
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
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
                    $news_id = intval($result->fields['0']);
                    $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $news_category_id = intval($result->fields['2']);
                    if ($news_category_id != 0 && isset($result->fields['10']) && trim($result->fields['10']) != "") {
                        $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                    } else {
                        $news_category_name = "no";
                    }
                    $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $news_date = intval($result->fields['4']);
                    $news_date = date("d.m.Y H:i", $news_date);
                    $news_valueview = intval($result->fields['5']);
                    $news_status = intval($result->fields['6']);
                    $news_termexpire = intval($result->fields['7']);
                    if (!isset($news_termexpire) || $news_termexpire == "" || $news_termexpire == 0) {
                        $news_termexpire = "unlim";
                    } else {
                        $news_termexpire = "notunlim";
                    }
                    $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                    $news_onhome = intval($result->fields['9']);
                    $news_all[] = [
                        "news_id"               => $news_id,
                        "news_author"           => $news_author,
                        "news_category_id"      => $news_category_id,
                        "news_category_name"    => $news_category_name,
                        "news_title"            => $news_title,
                        "news_date"             => $news_date,
                        "news_valueview"        => $news_valueview,
                        "news_status"           => $news_status,
                        "news_termexpire"       => $news_termexpire,
                        "news_termexpireaction" => $news_termexpireaction,
                        "news_onhome"           => $news_onhome,
                    ];
                    $result->MoveNext();
                }
                $this->result['news_all'] = $news_all;
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
                    $this->result['totalnews'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['news_all'] = "no";
                $this->result['html'] = "no";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'show_sql_error_' . $temp;
            $this->error_array = $error;

            return;
        }
        if ($temp != "program") {
            $this->result['adminmodules_news_program'] = "no";
        } else {
            $this->result['adminmodules_news_program'] = "yes";
        }
    }


    public function news_edit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['news_id'])) {
            $news_id = intval($array['news_id']);
        }
        if (!isset($array['news_id']) || $news_id == 0) {
            $this->error = 'empty_data';

            return;
        }
        if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
            $temp = "program";
            $this->result['include_jquery'] = 'yes';
            $this->result['include_jquery_data'] = 'dateinput';
        } else {
            $temp = "";
        }
        $sql =
            "SELECT news_id,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_read_counter,news_notes,news_value_view,news_status,news_term_expire,news_term_expire_action,news_on_home,news_link FROM "
            . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_id = '" . $news_id . "'";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                $news_id = intval($result->fields['0']);
                $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $news_category_id = intval($result->fields['2']);
                $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $news_date = intval($result->fields['4']);
                if ($temp == "program") {
                    $news_date2 = date("d.m.Y", $news_date);
                } else {
                    $news_date2 = date("d.m.Y H:i", $news_date);
                }
                $news_hour = date("H", $news_date);
                $news_minute = date("i", $news_date);
                $news_images = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                if (!isset($news_images) || $news_images == "" || $news_images == 0) {
                    $news_images = "no";
                }
                $news_short_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                $news_content = $this->registry->main_class->extracting_data($result->fields['7']);
                $news_readcounter = intval($result->fields['8']);
                $news_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                $news_valueview = intval($result->fields['10']);
                $news_status = intval($result->fields['11']);
                $news_termexpire = intval($result->fields['12']);
                if (!empty($news_termexpire)) {
                    $news_termexpireday = intval((($news_termexpire - $news_date) / 3600) / 24);
                    if ($news_termexpireday < 1) {
                        $news_termexpireday = 1;
                    }
                    $news_termexpire = date("d.m.Y H:i", $news_termexpire);
                } else {
                    $news_termexpire = "";
                    $news_termexpireday = "";
                }
                $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                $news_onhome = intval($result->fields['14']);
                $news_link = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                $sql2 = "SELECT news_category_id,news_category_name FROM " . PREFIX . "_news_categories_" . $this->registry->sitelang;
                $result2 = $this->db->Execute($sql2);
                if ($result2) {
                    if (isset($result2->fields['0'])) {
                        $row_exist = intval($result2->fields['0']);
                    } else {
                        $row_exist = 0;
                    }
                    if ($row_exist > 0) {
                        while (!$result2->EOF) {
                            $news_category_id2 = intval($result2->fields['0']);
                            $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result2->fields['1']));
                            $category_all[] = [
                                "news_category_id"   => $news_category_id2,
                                "news_category_name" => $news_category_name,
                            ];
                            $result2->MoveNext();
                        }
                        $this->result['category_all'] = $category_all;
                    } else {
                        $this->result['category_all'] = "no";
                    }
                } else {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                    $this->error = 'edit_sql_error' . $temp;
                    $this->error_array = $error;

                    return;
                }
                require_once('../modules/news/news_config.inc');
                $news_all[] = [
                    "news_id"               => $news_id,
                    "news_author"           => $news_author,
                    "news_category_id"      => $news_category_id,
                    "news_title"            => $news_title,
                    "news_date"             => $news_date,
                    "news_hour"             => $news_hour,
                    "news_minute"           => $news_minute,
                    "news_date2"            => $news_date2,
                    "news_images"           => $news_images,
                    "news_short_text"       => $news_short_text,
                    "news_content"          => $news_content,
                    "news_readcounter"      => $news_readcounter,
                    "news_notes"            => $news_notes,
                    "news_valueview"        => $news_valueview,
                    "news_status"           => $news_status,
                    "news_termexpire"       => $news_termexpire,
                    "news_termexpireday"    => $news_termexpireday,
                    "news_termexpireaction" => $news_termexpireaction,
                    "news_onhome"           => $news_onhome,
                    "news_link"             => $news_link,
                    "image_path"            => MODULE_NEWS_IMAGE_PATH,
                ];
                $this->result['news_all'] = $news_all;
            } else {
                $this->error = 'edit_not_found' . $temp;

                return;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_sql_error' . $temp;
            $this->error_array = $error;

            return;
        }
        if ($temp != "program") {
            $this->result['adminmodules_news_programm'] = "no";
        } else {
            $this->result['adminmodules_news_programm'] = "yes";
        }
    }


    public function news_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'news_author',
                'news_title',
                'news_newdate',
                'delete_picture',
                'news_short_text',
                'news_content',
                'news_notes',
                'news_termexpireaction',
                'news_link',
                'news_images',
            ];
            $keys2 = [
                'news_id',
                'news_category_id',
                'prog',
                'news_readcounter',
                'news_valueview',
                'news_status',
                'news_termexpire',
                'news_onhome',
                'news_hour',
                'news_minute',
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
        if (isset($array['news_date'])) {
            if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
                $data_array['news_date'] = trim($array['news_date']);
            } else {
                $data_array['news_date'] = intval($array['news_date']);
            }
        }
        if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
            $temp = "program";
        } else {
            $temp = "";
        }
        if ((!isset($array['news_id']) || !isset($array['news_author']) || !isset($array['news_category_id']) || !isset($array['news_title'])
             || !isset($array['news_date'])
             || !isset($array['news_newdate'])
             || !isset($array['delete_picture'])
             || !isset($array['news_short_text'])
             || !isset($array['news_content'])
             || !isset($array['news_readcounter'])
             || !isset($array['news_notes'])
             || !isset($array['news_valueview'])
             || !isset($array['news_status'])
             || !isset($array['news_termexpire'])
             || !isset($array['news_termexpireaction'])
             || !isset($array['news_onhome'])
             || !isset($array['news_link']))
            || ($data_array['news_id'] == 0 || $data_array['news_author'] == "" || $data_array['news_title'] == ""
                || ((isset($array['prog'])
                     && intval($array['prog']) != 0
                     && intval($array['prog']) == 1
                     && $data_array['news_date'] == "")
                    || (!isset($array['prog']) && $data_array['news_date'] == 0))
                || $data_array['news_newdate'] == ""
                || $data_array['delete_picture'] == ""
                || $data_array['news_short_text'] == ""
                || $data_array['news_content'] == "")
        ) {
            $this->error = 'edit_not_all_data' . $temp;

            return;
        }
        if ($temp != "program" && isset($data_array['news_newdate']) && $data_array['news_newdate'] == 'yes') {
            $data_array['news_date'] = $this->registry->main_class->get_time();
        } elseif ($temp == "program") {
            $data_array['news_date'] = $this->registry->main_class->format_striptags($data_array['news_date']);
            if (isset($array['news_hour'])) {
                $data_array['news_hour'] = intval($array['news_hour']);
            } else {
                $data_array['news_hour'] = 0;
            }
            if (isset($array['news_minute'])) {
                $data_array['news_minute'] = intval($array['news_minute']);
            } else {
                $data_array['news_minute'] = 0;
            }
            $data_array['news_date'] = $data_array['news_date'] . " " . sprintf("%02d", $data_array['news_hour']) . ":" . sprintf("%02d", $data_array['news_minute']);
            $data_array['news_date'] = $this->registry->main_class->time_to_unix($data_array['news_date']);
        }
        if ($data_array['news_termexpire'] == 0) {
            $data_array['news_termexpire'] = 'NULL';
            $data_array['news_termexpireaction'] = 'NULL';
        } else {
            //if($temp == "program") {
            $data_array['news_termexpire'] = $data_array['news_date'] + ($data_array['news_termexpire'] * 86400);
            /*} else {
                $data_array['news_termexpire'] = $this->registry->main_class->get_time() + ($data_array['news_termexpire'] * 86400);
            }*/
            $data_array['news_termexpire'] = "'" . $data_array['news_termexpire'] . "'";
            $data_array['news_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['news_termexpireaction']);
            $data_array['news_termexpireaction'] = $this->registry->main_class->processing_data($data_array['news_termexpireaction']);
        }
        if ($data_array['news_notes'] == "") {
            $data_array['news_notes'] = 'NULL';
        } else {
            $data_array['news_notes'] = $this->registry->main_class->processing_data($data_array['news_notes']);
        }
        if ($data_array['news_link'] == "") {
            $data_array['news_link'] = 'NULL';
        } else {
            $data_array['news_link'] = $this->registry->main_class->format_striptags($data_array['news_link']);
            $data_array['news_link'] = $this->registry->main_class->processing_data($data_array['news_link']);
        }
        $data_array['news_author'] = $this->registry->main_class->format_striptags($data_array['news_author']);
        $data_array['news_author'] = $this->registry->main_class->processing_data($data_array['news_author']);
        $data_array['news_title'] = $this->registry->main_class->format_striptags($data_array['news_title']);
        $data_array['news_title'] = $this->registry->main_class->processing_data($data_array['news_title']);
        $data_array['news_short_text'] = $this->registry->main_class->processing_data($data_array['news_short_text']);
        $data_array['news_short_text'] = substr(substr($data_array['news_short_text'], 0, -1), 1);
        $data_array['news_content'] = $this->registry->main_class->processing_data($data_array['news_content']);
        $data_array['news_content'] = substr(substr($data_array['news_content'], 0, -1), 1);
        require_once('../modules/news/news_config.inc');
        if ($data_array['delete_picture'] == 'yes') {
            $data_array['news_images'] = $array['news_images'];
            $data_array['news_images'] = $this->registry->main_class->format_striptags($data_array['news_images']);
            @unlink("../" . MODULE_NEWS_IMAGE_PATH . "/" . $data_array['news_images']);
            $data_array['news_images'] = 'NULL';
        } elseif ($data_array['delete_picture'] == 'new_file') {
            if (isset($_FILES['news_images']['name']) && trim($_FILES['news_images']['name']) !== '') {
                if (is_uploaded_file($_FILES['news_images']['tmp_name'])) {
                    if ($_FILES['news_images']['size'] > MODULE_NEWS_IMAGE_MAX_SIZE) {
                        $this->error = 'edit_max_size' . $temp;

                        return;
                    }
                    if (($_FILES['news_images']['type'] == "image/gif") || ($_FILES['news_images']['type'] == "image/pjpeg")
                        || ($_FILES['news_images']['type'] == "image/jpeg")
                        || ($_FILES['news_images']['type'] == "image/png")
                        || ($_FILES['news_images']['type'] == "image/bmp")
                    ) {
                        $file_name = $_FILES['news_images']['name'];
                        $data_kod = time();
                        $data_array['news_images'] = $data_kod . "_" . $file_name;
                        $result_file = @move_uploaded_file(
                            $_FILES['news_images']['tmp_name'], "../" . MODULE_NEWS_IMAGE_PATH . "/" . $data_array['news_images']
                        );
                        if (!$result_file) {
                            $this->error = 'edit_not_upload' . $temp;

                            return;
                        }
                        $size2 = @getimagesize("../" . MODULE_NEWS_IMAGE_PATH . "/" . $data_array['news_images']);
                        if (($size2[0] > MODULE_NEWS_IMAGE_MAX_WIDTH) || ($size2[1] > MODULE_NEWS_IMAGE_MAX_HEIGHT)) {
                            @unlink("../" . MODULE_NEWS_IMAGE_PATH . "/" . $data_array['news_images']);
                            $this->error = 'edit_not_eq' . $temp;

                            return;
                        }
                    } else {
                        $this->error = 'edit_not_eq_type' . $temp;

                        return;
                    }
                } else {
                    $this->error = 'edit_file' . $temp;

                    return;
                }
                $data_array['news_images'] = "'" . $data_array['news_images'] . "'";
            } else {
                $data_array['news_images'] = 'NULL';
            }
        } else {
            $data_array['news_images'] = trim($array['news_images']);
            $data_array['news_images'] = $this->registry->main_class->format_striptags($data_array['news_images']);
            $data_array['news_images'] = $this->registry->main_class->processing_data($data_array['news_images']);
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang . " SET news_author = " . $data_array['news_author'] . ",news_category_id = '"
                   . $data_array['news_category_id'] . "',news_title = " . $data_array['news_title'] . ",news_date = '" . $data_array['news_date'] . "',news_images = "
                   . $data_array['news_images'] . ",news_short_text = empty_clob(),news_content = empty_clob(),news_read_counter = '" . $data_array['news_readcounter']
                   . "',news_notes = empty_clob(),news_value_view = '" . $data_array['news_valueview'] . "',news_status = '" . $data_array['news_status']
                   . "',news_term_expire = " . $data_array['news_termexpire'] . ",news_term_expire_action = " . $data_array['news_termexpireaction'] . ",news_on_home = '"
                   . $data_array['news_onhome'] . "',news_link = " . $data_array['news_link'] . " WHERE news_id = '" . $data_array['news_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $result2 = $this->db->UpdateClob(
                PREFIX . '_news_' . $this->registry->sitelang, 'news_short_text', $data_array['news_short_text'], 'news_id = ' . $data_array['news_id']
            );
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $result3 = $this->db->UpdateClob(
                PREFIX . '_news_' . $this->registry->sitelang, 'news_content', $data_array['news_content'], 'news_id = ' . $data_array['news_id']
            );
            if ($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($data_array['news_notes'] != 'NULL') {
                $data_array['news_notes'] = substr(substr($data_array['news_notes'], 0, -1), 1);
                $result4 = $this->db->UpdateClob(
                    PREFIX . '_news_' . $this->registry->sitelang, 'news_notes', $data_array['news_notes'], 'news_id = ' . $data_array['news_id']
                );
                if ($result4 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $result4 = true;
            }
            $this->db->CompleteTrans();
            if ($result2 === false || $result3 === false || $result4 === false) {
                $result = false;
            }
        } else {
            $sql = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang . " SET news_author = " . $data_array['news_author'] . ",news_category_id = '"
                   . $data_array['news_category_id'] . "',news_title = " . $data_array['news_title'] . ",news_date = '" . $data_array['news_date'] . "',news_images = "
                   . $data_array['news_images'] . ",news_short_text = '" . $data_array['news_short_text'] . "',news_content = '" . $data_array['news_content']
                   . "',news_read_counter = '" . $data_array['news_readcounter'] . "',news_notes = " . $data_array['news_notes'] . ",news_value_view = '"
                   . $data_array['news_valueview'] . "',news_status = '" . $data_array['news_status'] . "',news_term_expire = " . $data_array['news_termexpire']
                   . ",news_term_expire_action = " . $data_array['news_termexpireaction'] . ",news_on_home = '" . $data_array['news_onhome'] . "',news_link = "
                   . $data_array['news_link'] . " WHERE news_id = '" . $data_array['news_id'] . "'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($result === false) {
            $this->error = 'edit_sql_error' . $temp;
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_ok' . $temp;

            return;
        } else {
            if ($temp == "program") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showprogram");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogram|" . $data_array['news_id']);
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit|" . $data_array['news_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread|" . $data_array['news_id']);
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread|" . $data_array['news_id'] . "admin");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread|" . $data_array['news_id'] . "user");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show");
            }
            $this->result = 'edit_done' . $temp;
        }
    }


    public function news_add($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
            $temp = "program";
            $this->result['include_jquery'] = "yes";
            $this->result['include_jquery_data'] = "dateinput";
        } else {
            $temp = "";
        }
        $sql = "SELECT news_category_id,news_category_name FROM " . PREFIX . "_news_categories_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $news_category_id = intval($result->fields['0']);
                    $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $category_all[] = [
                        "news_category_id"   => $news_category_id,
                        "news_category_name" => $news_category_name,
                    ];
                    $result->MoveNext();
                }
                $this->result['category_all'] = $category_all;
            } else {
                $this->result['category_all'] = "no";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'add_sql_error' . $temp;
            $this->error_array = $error;

            return;
        }
        if ($temp != "program") {
            $this->result['adminmodules_news_programm'] = "no";
        } else {
            $this->result['adminmodules_news_programm'] = "yes";
        }
    }


    public function news_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'news_title',
                'news_short_text',
                'news_content',
                'news_notes',
                'news_termexpireaction',
                'news_link',
                'news_date',
            ];
            $keys2 = [
                'news_category_id',
                'news_valueview',
                'news_status',
                'news_termexpire',
                'news_onhome',
                'prog',
                'news_hour',
                'news_minute',
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
        if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
            $temp = "program";
            $news_auto = '1';
        } else {
            $temp = "";
            $news_auto = '0';
        }
        if ((!isset($array['news_category_id']) || !isset($array['news_title']) || !isset($array['news_short_text']) || !isset($array['news_content'])
             || !isset($array['news_notes'])
             || !isset($array['news_valueview'])
             || !isset($array['news_status'])
             || !isset($array['news_termexpire'])
             || !isset($array['news_termexpireaction'])
             || !isset($array['news_onhome'])
             || !isset($array['news_link']))
            || ($data_array['news_title'] == "" || $data_array['news_short_text'] == "" || $data_array['news_content'] == ""
                || (isset($array['prog'])
                    && intval(
                           $array['prog']
                       ) != 0
                    && intval($array['prog']) == 1
                    && (!isset($data_array['news_date']) || $data_array['news_date'] == "")))
        ) {
            $this->error = 'add_not_all_data' . $temp;

            return;
        }
        if ($temp == "program") {
            $data_array['news_date'] = $this->registry->main_class->format_striptags($data_array['news_date']);
            if (isset($array['news_hour'])) {
                $data_array['news_hour'] = intval($array['news_hour']);
            } else {
                $data_array['news_hour'] = 0;
            }
            if (isset($array['news_minute'])) {
                $data_array['news_minute'] = intval($array['news_minute']);
            } else {
                $data_array['news_minute'] = 0;
            }
            $data_array['news_date'] = $data_array['news_date'] . " " . sprintf("%02d", $data_array['news_hour']) . ":" . sprintf("%02d", $data_array['news_minute']);
            $now = $this->registry->main_class->time_to_unix($data_array['news_date']);
        } else {
            $now = $this->registry->main_class->get_time();
        }
        if ($data_array['news_termexpire'] == 0) {
            $data_array['news_termexpire'] = 'NULL';
            $data_array['news_termexpireaction'] = 'NULL';
        } else {
            if ($temp == "program") {
                $data_array['news_termexpire'] = $now + ($data_array['news_termexpire'] * 86400);
            } else {
                $data_array['news_termexpire'] = $this->registry->main_class->get_time() + ($data_array['news_termexpire'] * 86400);
            }
            $data_array['news_termexpire'] = "'" . $data_array['news_termexpire'] . "'";
            $data_array['news_termexpireaction'] = $this->registry->main_class->format_striptags($data_array['news_termexpireaction']);
            $data_array['news_termexpireaction'] = $this->registry->main_class->processing_data($data_array['news_termexpireaction']);
        }
        if ($data_array['news_notes'] == "") {
            $data_array['news_notes'] = 'NULL';
        } else {
            $data_array['news_notes'] = $this->registry->main_class->processing_data($data_array['news_notes']);
        }
        if ($data_array['news_link'] == "") {
            $data_array['news_link'] = 'NULL';
        } else {
            $data_array['news_link'] = $this->registry->main_class->format_striptags($data_array['news_link']);
            $data_array['news_link'] = $this->registry->main_class->processing_data($data_array['news_link']);
        }
        $news_author = trim($this->registry->main_class->get_user_login());
        $news_author = $this->registry->main_class->format_striptags($news_author);
        $news_author = $this->registry->main_class->processing_data($news_author);
        $data_array['news_title'] = $this->registry->main_class->format_striptags($data_array['news_title']);
        $data_array['news_title'] = $this->registry->main_class->processing_data($data_array['news_title']);
        $data_array['news_short_text'] = $this->registry->main_class->processing_data($data_array['news_short_text']);
        $data_array['news_short_text'] = substr(substr($data_array['news_short_text'], 0, -1), 1);
        $data_array['news_content'] = $this->registry->main_class->processing_data($data_array['news_content']);
        $data_array['news_content'] = substr(substr($data_array['news_content'], 0, -1), 1);
        require_once('../modules/news/news_config.inc');
        if (isset($_FILES['news_images']['name']) && trim($_FILES['news_images']['name']) !== '') {
            if (is_uploaded_file($_FILES['news_images']['tmp_name'])) {
                if ($_FILES['news_images']['size'] > MODULE_NEWS_IMAGE_MAX_SIZE) {
                    $this->error = 'add_max_size' . $temp;

                    return;
                }
                if (($_FILES['news_images']['type'] == "image/gif") || ($_FILES['news_images']['type'] == "image/pjpeg")
                    || ($_FILES['news_images']['type'] == "image/jpeg")
                    || ($_FILES['news_images']['type'] == "image/png")
                    || ($_FILES['news_images']['type'] == "image/bmp")
                ) {
                    $file_name = $_FILES['news_images']['name'];
                    $data_kod = time();
                    $news_images = $data_kod . "_" . $file_name;
                    $result_file = @move_uploaded_file($_FILES['news_images']['tmp_name'], "../" . MODULE_NEWS_IMAGE_PATH . "/" . $news_images);
                    if (!$result_file) {
                        $this->error = 'add_not_upload' . $temp;

                        return;
                    }
                    $size2 = @getimagesize("../" . MODULE_NEWS_IMAGE_PATH . "/" . $news_images);
                    if (($size2[0] > MODULE_NEWS_IMAGE_MAX_WIDTH) || ($size2[1] > MODULE_NEWS_IMAGE_MAX_HEIGHT)) {
                        @unlink("../" . MODULE_NEWS_IMAGE_PATH . "/" . $news_images);
                        $this->error = 'add_not_eq' . $temp;

                        return;
                    }
                } else {
                    $this->error = 'add_not_eq_type' . $temp;

                    return;
                }
            } else {
                $this->error = 'add_file' . $temp;

                return;
            }
            $news_images = "'$news_images'";
        } else {
            $news_images = 'NULL';
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $news_id = $this->db->GenID(PREFIX . "_news_id_" . $this->registry->sitelang);
            $sql = "INSERT INTO " . PREFIX . "_news_" . $this->registry->sitelang
                   . " (news_id,news_auto,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_read_counter,news_notes,news_value_view,news_status,news_term_expire,news_term_expire_action,news_on_home,news_link,news_meta_title,news_meta_keywords,news_meta_description)  VALUES ('"
                   . $news_id . "','" . $news_auto . "'," . $news_author . ",'" . $data_array['news_category_id'] . "'," . $data_array['news_title'] . ",'" . $now . "',"
                   . $news_images . ",empty_clob(),empty_clob(),'0',empty_clob(),'" . $data_array['news_valueview'] . "','" . $data_array['news_status'] . "',"
                   . $data_array['news_termexpire'] . "," . $data_array['news_termexpireaction'] . ",'" . $data_array['news_onhome'] . "'," . $data_array['news_link']
                   . ",NULL,NULL,NULL)";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $result2 = $this->db->UpdateClob(PREFIX . '_news_' . $this->registry->sitelang, 'news_short_text', $data_array['news_short_text'], 'news_id = ' . $news_id);
            if ($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $result3 = $this->db->UpdateClob(PREFIX . '_news_' . $this->registry->sitelang, 'news_content', $data_array['news_content'], 'news_id = ' . $news_id);
            if ($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            if ($data_array['news_notes'] != 'NULL') {
                $data_array['news_notes'] = substr(substr($data_array['news_notes'], 0, -1), 1);
                $result4 = $this->db->UpdateClob(
                    PREFIX . '_news_' . $this->registry->sitelang, 'news_notes', $data_array['news_notes'], 'news_id = ' . $news_id
                );
                if ($result4 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                $result4 = true;
            }
            $this->db->CompleteTrans();
            if ($result2 === false || $result3 === false || $result4 === false) {
                $result = false;
            }
        } else {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_news_id_" . $this->registry->sitelang, 'news_id');
            $sql = "INSERT INTO " . PREFIX . "_news_" . $this->registry->sitelang . " (" . $sequence_array['field_name_string']
                   . "news_auto,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_read_counter,news_notes,news_value_view,news_status,news_term_expire,news_term_expire_action,news_on_home,news_link,news_meta_title,news_meta_keywords,news_meta_description)  VALUES ("
                   . $sequence_array['sequence_value_string'] . "'" . $news_auto . "'," . $news_author . ",'" . $data_array['news_category_id'] . "',"
                   . $data_array['news_title'] . ",'" . $now . "'," . $news_images . ",'" . $data_array['news_short_text'] . "','" . $data_array['news_content']
                   . "','0',"
                   . $data_array['news_notes'] . ",'" . $data_array['news_valueview'] . "','" . $data_array['news_status'] . "'," . $data_array['news_termexpire'] . ","
                   . $data_array['news_termexpireaction'] . ",'" . $data_array['news_onhome'] . "'," . $data_array['news_link'] . ",NULL,NULL,NULL)";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($result === false) {
            $this->error = 'add_sql_error' . $temp;
            $this->error_array = $error;

            return;
        } else {
            if ($temp == "program") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showprogram");
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show");
            }
            $this->result = 'add_ok' . $temp;
        }
    }


    public function news_category($num_page)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM " . PREFIX . "_news_categories_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT news_category_id,news_category_name FROM " . PREFIX . "_news_categories_" . $this->registry->sitelang . " order by news_category_id DESC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
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
                    $news_category_id = intval($result->fields['0']);
                    $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $category_all[] = [
                        "news_category_id"   => $news_category_id,
                        "news_category_name" => $news_category_name,
                    ];
                    $result->MoveNext();
                }
                $this->result['category_all'] = $category_all;
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
                    $this->result['totalcategories'] = $num_rows;
                    $this->result['num_pages'] = $num_pages;
                } else {
                    $this->result['html'] = "no";
                }
            } else {
                $this->result['category_all'] = "no";
                $this->result['html'] = "no";
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'show_cat_sql_error';
            $this->error_array = $error;

            return;
        }
    }


    public function category_add()
    {
        //
    }


    public function category_add_inbase($category_name)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $category_name = trim($category_name);
        if (empty($category_name)) {
            $this->error = 'add_cat_not_all_data';

            return;
        }
        $category_name = $this->registry->main_class->format_striptags($category_name);
        $category_name = $this->registry->main_class->processing_data($category_name);
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_news_categories_id_" . $this->registry->sitelang, 'news_category_id');
        $sql = "INSERT INTO " . PREFIX . "_news_categories_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
               . "news_category_name,news_category_meta_title,news_category_meta_keywords,news_category_meta_description) VALUES ("
               . $sequence_array['sequence_value_string'] . "" . $category_name . ",null,null,null)";
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'add_cat_sql_error';
            $this->error_array = $error;

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcat");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogram");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|newsprogram|add");
        $this->result = 'add_cat_ok';
    }


    public function category_edit($category_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $category_id = intval($category_id);
        if (empty($category_id)) {
            $this->error = 'empty_data';

            return;
        }
        $sql = "SELECT news_category_id,news_category_name FROM " . PREFIX . "_news_categories_" . $this->registry->sitelang . " WHERE news_category_id = '"
               . $category_id . "'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_cat_sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'edit_cat_not_found';

            return;
        }
        while (!$result->EOF) {
            $news_category_id = intval($result->fields['0']);
            $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $category_all[] = [
                "news_category_id"   => $news_category_id,
                "news_category_name" => $news_category_name,
            ];
            $result->MoveNext();
        }
        $this->result['category_all'] = $category_all;
    }


    public function category_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['category_name'])) {
            $category_name = trim($array['category_name']);
        }
        if (isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if ((!isset($array['category_name']) || !isset($array['category_id'])) || ($category_name == "" || $category_id == 0)) {
            $this->error = 'edit_cat_not_all_data';

            return;
        }
        $category_name = $this->registry->main_class->format_striptags($category_name);
        $category_name = $this->registry->main_class->processing_data($category_name);
        $sql = "UPDATE " . PREFIX . "_news_categories_" . $this->registry->sitelang . " SET news_category_name = " . $category_name
               . " WHERE news_category_id = '" . $category_id . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'edit_cat_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'edit_cat_ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showprogram");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogram");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|newsprogram|add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcat");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editcat|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|user|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|admin|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|guest|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|user|no");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|admin|no");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|guest|no");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread");
        $this->result = 'edit_cat_done';
    }


    public function category_delete($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['category_id'])) {
            $category_id = intval($array['category_id']);
        }
        if (isset($array['conf'])) {
            $conf = intval($array['conf']);
        }
        if ((!isset($array['conf']) || !isset($array['category_id'])) || ($category_id == 0)) {
            $this->error = 'del_cat_not_all_data';

            return;
        }
        if (empty($conf)) {
            $sql = "SELECT a.news_category_id,a.news_category_name,count(b.news_category_id) as check_num FROM " . PREFIX . "_news_categories_"
                   . $this->registry->sitelang . " a LEFT OUTER JOIN (SELECT news_category_id FROM " . PREFIX . "_news_" . $this->registry->sitelang
                   . ") b on a.news_category_id = b.news_category_id WHERE a.news_category_id = '" . $category_id . "' group by  a.news_category_id,a.news_category_name";
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'del_cat_sql_error';
                $this->error_array = $error;

                return;
            }
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist < 1) {
                $this->error = 'del_cat_not_found';

                return;
            }
            $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $check_num = intval($result->fields['2']);
            $this->result['category_id'] = $category_id;
            $this->result['category_del_name'] = $news_category_name;
            $this->result['count_del_news_id'] = $check_num;
            if ($check_num > 0) {
                $this->result['news_update'] = 'delete_cat_conf';

                return;
            }
        }
        $this->db->StartTrans();
        $sql = "DELETE FROM " . PREFIX . "_news_categories_" . $this->registry->sitelang . " WHERE news_category_id = '" . $category_id . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        $sql = "DELETE FROM " . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_category_id = '" . $category_id . "'";
        $result2 = $this->db->Execute($sql);
        if ($result2 === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
        }
        if ($result === false || $result2 === false) {
            $result = false;
        }
        $this->db->CompleteTrans();
        if ($result === false) {
            $this->error = 'del_cat_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'del_cat_ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showprogram");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogram");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|newsprogram|add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcat");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editcat|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|user|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|admin|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|guest|" . $category_id);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|user|no");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|admin|no");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show|guest|no");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread");
        $this->result['news_update'] = 'delete_cat_done';
    }


    public function news_status($array)
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
        $news_id = 0;
        if (isset($array['get_news_id'])) {
            $news_id = intval($array['get_news_id']);
        }
        if ($news_id == 0 && isset($array['post_news_id']) && is_array($array['post_news_id']) && count($array['post_news_id']) > 0) {
            $news_id_is_arr = 1;
            for ($i = 0, $size = count($array['post_news_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $news_id = "'" . intval($array['post_news_id'][$i]) . "'";
                } else {
                    $news_id .= ",'" . intval($array['post_news_id'][$i]) . "'";
                }
            }
        } else {
            $news_id_is_arr = 0;
            $news_id = "'" . $news_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if (!isset($news_id) || $action == "" || $news_id == "'0'") {
            $this->error = 'empty_data';

            return;
        }
        $temp = "";
        if (isset($array['prog']) && intval($array['prog']) != 0 && intval($array['prog']) == 1) {
            $temp = "program";
        }
        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang . " SET news_status = '1' WHERE news_id in (" . $news_id . ")";
            $result = $this->db->Execute($sql);
        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_news_" . $this->registry->sitelang . " SET news_status = '0' WHERE news_id IN (" . $news_id . ")";
            $result = $this->db->Execute($sql);
        } elseif ($action == "delete") {
            $sql = "DELETE FROM " . PREFIX . "_news_" . $this->registry->sitelang . " WHERE news_id IN (" . $news_id . ")";
            $result = $this->db->Execute($sql);
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result) {
            $num_result = $this->db->Affected_Rows();
        }
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'status_sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'status_ok';

            return;
        }
        if ($temp == "program") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showprogram");
            if ($news_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogram");
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|editprogram|" . intval($array['get_news_id']));
            }
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|showcurrent");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show");
            if ($news_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread");
            } else {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|edit|" . intval($array['get_news_id']));
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread|" . intval($array['get_news_id']));
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread|" . intval($array['get_news_id']) . "admin");
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread|" . intval($array['get_news_id']) . "user");
            }
        }
        $this->result = $action;
    }


    public function news_config()
    {
        $this->result = false;
        require_once('../modules/news/news_config.inc');
        $this->result['systemdk_news_num_news'] = trim(MODULE_NEWS_NUM_NEWS);
        $this->result['systemdk_news_image_path'] = trim(MODULE_NEWS_IMAGE_PATH);
        $this->result['systemdk_news_image_position'] = trim(MODULE_NEWS_IMAGE_POSITION);
        $this->result['systemdk_news_image_max_size'] = trim(MODULE_NEWS_IMAGE_MAX_SIZE);
        $this->result['systemdk_news_image_max_width'] = trim(MODULE_NEWS_IMAGE_MAX_WIDTH);
        $this->result['systemdk_news_image_max_height'] = trim(MODULE_NEWS_IMAGE_MAX_HEIGHT);
        $this->result['systemdk_news_image_width'] = trim(MODULE_NEWS_IMAGE_WIDTH);
        $this->result['systemdk_news_image_width2'] = trim(MODULE_NEWS_IMAGE_WIDTH2);
    }


    public function news_config_save($array)
    {
        $this->result = false;
        $this->error = false;
        if (!empty($array)) {
            $keys = [
                'systemdk_news_image_path',
                'systemdk_news_image_position',
            ];
            $keys2 = [
                'systemdk_news_num_news',
                'systemdk_news_image_max_size',
                'systemdk_news_image_max_width',
                'systemdk_news_image_max_height',
                'systemdk_news_image_width',
                'systemdk_news_image_width2',
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
        if ((!isset($array['systemdk_news_num_news']) || !isset($array['systemdk_news_image_path']) || !isset($array['systemdk_news_image_position'])
             || !isset($array['systemdk_news_image_max_size'])
             || !isset($array['systemdk_news_image_max_width'])
             || !isset($array['systemdk_news_image_max_height'])
             || !isset($array['systemdk_news_image_width'])
             || !isset($array['systemdk_news_image_width2']))
            || ($data_array['systemdk_news_num_news'] == 0 || $data_array['systemdk_news_image_path'] == "" || $data_array['systemdk_news_image_position'] == ""
                || $data_array['systemdk_news_image_max_size'] == 0
                || $data_array['systemdk_news_image_max_width'] == 0
                || $data_array['systemdk_news_image_max_height'] == 0
                || $data_array['systemdk_news_image_width'] == 0
                || $data_array['systemdk_news_image_width2'] == 0)
        ) {
            $this->error = 'conf_not_all_data';

            return;
        }
        $data_array['systemdk_news_image_path'] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($data_array['systemdk_news_image_path']);
        $data_array['systemdk_news_image_position'] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags(
            $data_array['systemdk_news_image_position']
        );
        @chmod("../modules/news/news_config.inc", 0777);
        $file = @fopen("../modules/news/news_config.inc", "w");
        if (!$file) {
            $this->error = 'conf_no_file';

            return;
        }
        $content = "<?php\n";
        $content .= 'define("MODULE_NEWS_NUM_NEWS","' . $data_array['systemdk_news_num_news'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_PATH","' . $data_array['systemdk_news_image_path'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_POSITION","' . $data_array['systemdk_news_image_position'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_MAX_SIZE","' . $data_array['systemdk_news_image_max_size'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_MAX_WIDTH","' . $data_array['systemdk_news_image_max_width'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_MAX_HEIGHT","' . $data_array['systemdk_news_image_max_height'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_WIDTH","' . $data_array['systemdk_news_image_width'] . "\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_WIDTH2","' . $data_array['systemdk_news_image_width2'] . "\");\n";
        $writefile = @fwrite($file, $content);
        @fclose($file);
        @chmod("../modules/news/news_config.inc", 0604);
        if (!$writefile) {
            $this->error = 'conf_no_write';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|show");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|news|newsread");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|news|config");
        $this->result = 'config_ok';
    }
}
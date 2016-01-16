<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class guestbook extends model_base
{


    private $error;
    private $error_array;
    private $result;
    private $systemdk_guestbook_metakeywords;
    private $systemdk_guestbook_metadescription;
    private $systemdk_guestbook_storynum;
    private $systemdk_guestbook_antispam_ipaddr;
    private $systemdk_guestbook_antispam_num;
    private $systemdk_guestbook_confirm_need;
    private $systemdk_guestbook_sortorder;


    public function __construct($registry)
    {
        parent::__construct($registry);
        require_once('guestbook_config.inc');
        $this->systemdk_guestbook_metakeywords = SYSTEMDK_GUESTBOOK_METAKEYWORDS;
        $this->systemdk_guestbook_metadescription = SYSTEMDK_GUESTBOOK_METADESCRIPTION;
        $this->systemdk_guestbook_storynum = SYSTEMDK_GUESTBOOK_STORYNUM;
        $this->systemdk_guestbook_antispam_ipaddr = SYSTEMDK_GUESTBOOK_ANTISPAM_IPADDR;
        $this->systemdk_guestbook_antispam_num = SYSTEMDK_GUESTBOOK_ANTISPAM_NUM;
        $this->systemdk_guestbook_confirm_need = SYSTEMDK_GUESTBOOK_CONFIRM_NEED;
        $this->systemdk_guestbook_sortorder = SYSTEMDK_GUESTBOOK_SORTORDER;
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($num_page = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($num_page) && intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_guestbook_storynum;
        $offset = ($num_page - 1) * $num_string_rows;
        if (isset($this->systemdk_guestbook_sortorder) && $this->systemdk_guestbook_sortorder == 'ASC') {
            $systemdk_guestbook_sortorder = 'ASC';
        } else {
            $systemdk_guestbook_sortorder = 'DESC';
        }
        $sql = "SELECT count(post_id) FROM " . PREFIX . "_guestbook WHERE post_status = '1'";
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql =
                "SELECT a.post_id,a.post_user_name,a.post_user_country,a.post_user_city,a.post_user_telephone,a.post_show_telephone,a.post_user_email,a.post_show_email,a.post_user_ip,a.post_date,a.post_text,a.post_answer,a.post_answer_date,a.post_user_id FROM "
                . PREFIX . "_guestbook a WHERE a.post_status = '1' ORDER BY a.post_date " . $systemdk_guestbook_sortorder;
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        $this->result['systemdk_guestbook_metadescription'] = $this->systemdk_guestbook_metadescription;
        $this->result['systemdk_guestbook_metakeywords'] = $this->systemdk_guestbook_metakeywords;
        if (!$result) {
            $this->result['guestbook_posts_all'] = 'no';
            $this->result['guestbook_total_posts'] = '0';
            $this->result['html'] = 'no';

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
        if ($row_exist < 1) {
            $this->result['guestbook_posts_all'] = 'no';
            $this->result['guestbook_total_posts'] = '0';
            $this->result['html'] = 'no';

            return;
        }
        while (!$result->EOF) {
            $post_id = intval($result->fields['0']);
            $post_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $post_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
            if ($post_user_country == "") {
                $post_user_country = "no";
            }
            $post_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
            if ($post_user_city == "") {
                $post_user_city = "no";
            }
            $post_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
            if ($post_user_telephone == "") {
                $post_user_telephone = "no";
            }
            $post_show_telephone = intval($result->fields['5']);
            $post_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
            $post_show_email = intval($result->fields['7']);
            $post_user_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
            $post_date = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
            $post_date = date("d.m.y H:i", $post_date);
            $post_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
            $post_answer = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
            if ($post_answer == "") {
                $post_answer = "no";
            }
            $post_answer_date = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
            if ($post_answer_date !== "") {
                $post_answer_date = date("d.m.y H:i", $post_answer_date);
            }
            $post_user_id = intval($result->fields['13']);
            $posts_all[] = [
                "post_id"             => $post_id,
                "post_user_name"      => $post_user_name,
                "post_user_country"   => $post_user_country,
                "post_user_city"      => $post_user_city,
                "post_user_telephone" => $post_user_telephone,
                "post_show_telephone" => "$post_show_telephone",
                "post_user_email"     => $post_user_email,
                "post_show_email"     => "$post_show_email",
                "post_user_ip"        => $post_user_ip,
                "post_date"           => $post_date,
                "post_text"           => $post_text,
                "post_answer"         => $post_answer,
                "post_answer_date"    => $post_answer_date,
                "post_user_id"        => $post_user_id,
            ];
            $result->MoveNext();
        }
        $this->result['guestbook_posts_all'] = $posts_all;
        $this->result['guestbook_total_posts'] = $num_rows;
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
                $this->result['prevpage'] = 'no';
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
                $this->result['nextpage'] = 'no';
            }
            $this->result['html'] = $html;
            $this->result['num_pages'] = $num_pages;
        } else {
            $this->result['html'] = 'no';
        }
    }


    public function add()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->result['systemdk_guestbook_metadescription'] = $this->systemdk_guestbook_metadescription;
        $this->result['systemdk_guestbook_metakeywords'] = $this->systemdk_guestbook_metakeywords;
        if (extension_loaded("gd") && (ENTER_CHECK == "yes")) {
            $this->result['enter_check'] = 'yes';
        } else {
            $this->result['enter_check'] = 'no';
        }
        $guestbook_user_name = "no";
        $guestbook_user_id = "no";
        $guestbook_user_email = "no";
        $guestbook_user_country = "no";
        $guestbook_user_city = "no";
        $guestbook_user_telephone = "no";
        $guestbook_user_viewemail = "no";
        if ($this->registry->main_class->is_user()) {
            $row = $this->registry->main_class->get_user_info();
            if (!empty($row) && intval($row['0']) > 0) {
                $guestbook_user_id = intval($row['0']);
                $guestbook_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $guestbook_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['7']));
                $guestbook_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['8']));
                $guestbook_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['10']));
                $guestbook_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $guestbook_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                $guestbook_user_viewemail = intval($row['19']);
                if (empty($guestbook_user_name) && !empty($guestbook_user_surname)) {
                    $guestbook_user_name = $guestbook_user_surname;
                } elseif (empty($guestbook_user_name) && empty($guestbook_user_surname)) {
                    $guestbook_user_name = $this->registry->main_class->extracting_data($row['5']);
                } elseif (!empty($guestbook_user_name) && !empty($guestbook_user_surname)) {
                    $guestbook_user_name = $guestbook_user_name . " " . $guestbook_user_surname;
                }
            }
        }
        $this->result['modules_guestbook_sendername'] = $guestbook_user_name;
        $this->result['modules_guestbook_senderid'] = $guestbook_user_id;
        $this->result['modules_guestbook_senderemail'] = $guestbook_user_email;
        $this->result['modules_guestbook_sendercountry'] = $guestbook_user_country;
        $this->result['modules_guestbook_sendercity'] = $guestbook_user_city;
        $this->result['modules_guestbook_sendertelephone'] = $guestbook_user_telephone;
        $this->result['modules_guestbook_viewsenderemail'] = $guestbook_user_viewemail;
    }


    public function insert($post_array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($post_array)) {
            $keys = [
                'post_user_name',
                'post_user_country',
                'post_user_city',
                'post_user_telephone',
                'post_user_email',
                'post_text',
                'captcha',
            ];
            $keys2 = [
                'post_show_telephone',
                'post_show_email',
            ];
            foreach ($post_array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $this->result['systemdk_guestbook_metadescription'] = $this->systemdk_guestbook_metadescription;
        $this->result['systemdk_guestbook_metakeywords'] = $this->systemdk_guestbook_metakeywords;
        if ((!isset($post_array['post_user_name']) || !isset($post_array['post_user_country']) || !isset($post_array['post_user_city'])
             || !isset($post_array['post_user_telephone'])
             || !isset($post_array['post_user_email'])
             || !isset($post_array['post_text']))
            || ($data_array['post_user_name'] == "" || $data_array['post_user_email'] == "" || $data_array['post_text'] == "")
        ) {
            $this->error = 'not_all_data';

            return;
        }
        if (ENTER_CHECK == "yes") {
            if (extension_loaded("gd")
                && ((!isset($post_array["captcha"]))
                    || (isset($_SESSION["captcha"])
                        && $_SESSION["captcha"] !== $this->registry->main_class->format_striptags(trim($post_array["captcha"])))
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
        if (isset($this->systemdk_guestbook_antispam_ipaddr) && $this->systemdk_guestbook_antispam_ipaddr == "yes") {
            $post_date_check = $this->registry->main_class->get_time() - 86400;
            $sql = "SELECT count(*) FROM " . PREFIX . "_guestbook WHERE post_user_ip = '" . $this->registry->ip_addr . "' && post_date > " . $post_date_check;
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $num_rows = intval($result->fields['0']);
                } else {
                    $num_rows = 0;
                }
                if ($num_rows >= intval($this->systemdk_guestbook_antispam_num) && intval($this->systemdk_guestbook_antispam_num) > 0) {
                    $this->error = 'post_limit';

                    return;
                }
            }
        }
        $data_array['post_user_email'] = $this->registry->main_class->format_striptags($data_array['post_user_email']);
        if (!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($data_array['post_user_email']))) {
            $this->error = 'user_email';

            return;
        }
        $data_array['post_user_name'] = $this->registry->main_class->format_striptags($data_array['post_user_name']);
        $data_array['post_user_country'] = $this->registry->main_class->format_striptags($data_array['post_user_country']);
        $data_array['post_user_city'] = $this->registry->main_class->format_striptags($data_array['post_user_city']);
        $data_array['post_user_telephone'] = $this->registry->main_class->format_striptags($data_array['post_user_telephone']);
        $data_array['post_text'] = $this->registry->main_class->format_striptags($data_array['post_text']);
        if ($this->registry->main_class->is_user()) {
            $row = $this->registry->main_class->get_user_info();
            if (!empty($row) && intval($row['0']) > 0) {
                $guestbook_user_id = intval($row['0']);
                if ($guestbook_user_id == 0) {
                    $guestbook_user_id = 'NULL';
                } else {
                    $guestbook_user_id = "'" . $guestbook_user_id . "'";
                }
                $guestbook_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $guestbook_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $guestbook_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                if (empty($guestbook_user_name) && !empty($guestbook_user_surname)) {
                    $guestbook_user_name = $guestbook_user_surname;
                } elseif (empty($guestbook_user_name) && empty($guestbook_user_surname)) {
                    $guestbook_user_name = $this->registry->main_class->extracting_data($row['5']);
                } elseif (!empty($guestbook_user_name) && !empty($guestbook_user_surname)) {
                    $guestbook_user_name = $guestbook_user_name . " " . $guestbook_user_surname;
                }
            } else {
                $guestbook_user_id = 'NULL';
                $guestbook_user_name = "no";
                $guestbook_user_email = "no";
            }
        } else {
            $guestbook_user_id = 'NULL';
            $guestbook_user_name = "no";
            $guestbook_user_email = "no";
        }
        if (!empty($guestbook_user_name) && $guestbook_user_name !== "no") {
            $data_array['post_user_name'] = $guestbook_user_name;
        }
        if (!empty($guestbook_user_email) && $guestbook_user_email !== "no") {
            $data_array['post_user_email'] = $guestbook_user_email;
        }
        $data_array['post_user_email'] = $this->registry->main_class->processing_data($data_array['post_user_email']);
        $data_array['post_user_name'] = $this->registry->main_class->processing_data($data_array['post_user_name']);
        $data_array['post_user_country'] = $this->registry->main_class->processing_data($data_array['post_user_country']);
        $data_array['post_user_city'] = $this->registry->main_class->processing_data($data_array['post_user_city']);
        $data_array['post_user_telephone'] = $this->registry->main_class->processing_data($data_array['post_user_telephone']);
        $data_array['post_text'] = $this->registry->main_class->processing_data($data_array['post_text']);
        $data_array['post_text'] = substr(substr($data_array['post_text'], 0, -1), 1);
        $post_text_length = strlen($data_array['post_text']);
        if ($post_text_length > 10000) {
            $this->error = 'post_length';

            return;
        }
        $now = $this->registry->main_class->get_time();
        if (isset($this->systemdk_guestbook_confirm_need) && $this->systemdk_guestbook_confirm_need == "yes") {
            $post_status = 0;
        } else {
            $post_status = 1;
        }
        if (!isset($post_array['post_show_telephone'])) {
            $data_array['post_show_telephone'] = 1;
        }
        if (!isset($post_array['post_show_email'])) {
            $data_array['post_show_email'] = 1;
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if ($check_db_need_lobs == 'yes') {
            $post_add_id = $this->db->GenID(PREFIX . "_guestbook_id");
            $this->db->StartTrans();
            $sql = "INSERT INTO " . PREFIX
                   . "_guestbook(post_id,post_user_name,post_user_country,post_user_city,post_user_telephone,post_show_telephone,post_user_email,post_show_email,post_user_id,post_user_ip,post_date,post_text,post_status,post_answer,post_answer_date)  VALUES ('"
                   . $post_add_id . "'," . $data_array['post_user_name'] . "," . $data_array['post_user_country'] . "," . $data_array['post_user_city'] . ","
                   . $data_array['post_user_telephone'] . ",'" . $data_array['post_show_telephone'] . "'," . $data_array['post_user_email'] . ",'"
                   . $data_array['post_show_email'] . "'," . $guestbook_user_id . ",'" . $this->registry->ip_addr . "','" . $now . "',empty_clob(),'" . $post_status
                   . "',NULL,NULL)";
            $insert_result = $this->db->Execute($sql);
            if ($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $insert_result2 = $this->db->UpdateClob(PREFIX . '_guestbook', 'post_text', $data_array['post_text'], 'post_id=' . $post_add_id);
            if ($insert_result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $insert_result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $sql = "INSERT INTO " . PREFIX
                   . "_guestbook(post_user_name,post_user_country,post_user_city,post_user_telephone,post_show_telephone,post_user_email,post_show_email,post_user_id,post_user_ip,post_date,post_text,post_status,post_answer,post_answer_date)  VALUES ("
                   . $data_array['post_user_name'] . "," . $data_array['post_user_country'] . "," . $data_array['post_user_city'] . ","
                   . $data_array['post_user_telephone']
                   . ",'" . $data_array['post_show_telephone'] . "'," . $data_array['post_user_email'] . ",'" . $data_array['post_show_email'] . "'," . $guestbook_user_id
                   . ",'" . $this->registry->ip_addr . "','" . $now . "','" . $data_array['post_text'] . "','" . $post_status . "',NULL,NULL)";
            $insert_result = $this->db->Execute($sql);
            if ($insert_result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
        }
        if ($insert_result === false) {
            $this->error_array = $error;
            $this->error = 'not_insert';

            return;
        } elseif ($post_status == 1) {
            $this->registry->main_class->systemdk_clearcache("modules|guestbook|display");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
            $this->result['guestbook_message'] = 'insert_ok';
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showpending");
            $this->result['guestbook_message'] = 'will_insert_ok';
        }
    }
}
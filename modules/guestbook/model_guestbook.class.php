<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class guestbook extends model_base {


    private $systemdk_guestbook_metakeywords;
    private $systemdk_guestbook_metadescription;
    private $systemdk_guestbook_storynum;
    private $systemdk_guestbook_antispam_ipaddr;
    private $systemdk_guestbook_antispam_num;
    private $systemdk_guestbook_confirm_need;
    private $systemdk_guestbook_sortorder;


    public function __construct($registry) {
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


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        $num_string_rows = $this->systemdk_guestbook_storynum;
        $offset = ($num_page - 1) * $num_string_rows;
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $cachuser = "admin";
        } else {
            $cachuser = "other";
        }
        if(isset($this->systemdk_guestbook_sortorder) and $this->systemdk_guestbook_sortorder == 'ASC') {
            $systemdk_guestbook_sortorder = 'ASC';
        } else {
            $systemdk_guestbook_sortorder = 'DESC';
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|display|".$cachuser."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.post_id,a.post_user_name,a.post_user_country,a.post_user_city,a.post_user_telephone,a.post_show_telephone,a.post_user_email,a.post_show_email,a.post_user_ip,a.post_date,a.post_text,a.post_unswer,a.post_unswer_date,a.post_user_id,(SELECT count(b.post_id) FROM ".PREFIX."_guestbook b WHERE b.post_status = '1') as count_posts_id FROM ".PREFIX."_guestbook a WHERE a.post_status = '1' ORDER BY a.post_date ".$systemdk_guestbook_sortorder;
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
                        header("Location: /".SITE_DIR.$this->registry->sitelang."/guestbook/");
                    } else {
                        header("Location: /".SITE_DIR."index.php?path=guestbook&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['14']);
                        }
                        $post_id = intval($result->fields['0']);
                        $post_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $post_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                        if($post_user_country == "") {
                            $post_user_country = "no";
                        }
                        $post_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        if($post_user_city == "") {
                            $post_user_city = "no";
                        }
                        $post_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                        if($post_user_telephone == "") {
                            $post_user_telephone = "no";
                        }
                        $post_show_telephone = intval($result->fields['5']);
                        $post_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                        $post_show_email = intval($result->fields['7']);
                        $post_user_ip = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                        $post_date = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                        $post_date = date("d.m.y H:i",$post_date);
                        $post_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                        $post_unswer = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                        if($post_unswer == "") {
                            $post_unswer = "no";
                        }
                        $post_unswer_date = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                        if($post_unswer_date !== "") {
                            $post_unswer_date = date("d.m.y H:i",$post_unswer_date);
                        }
                        $post_user_id = intval($result->fields['13']);
                        $posts_all[] = array(
                            "post_id" => $post_id,
                            "post_user_name" => $post_user_name,
                            "post_user_country" => $post_user_country,
                            "post_user_city" => $post_user_city,
                            "post_user_telephone" => $post_user_telephone,
                            "post_show_telephone" => "$post_show_telephone",
                            "post_user_email" => $post_user_email,
                            "post_show_email" => "$post_show_email",
                            "post_user_ip" => $post_user_ip,
                            "post_date" => $post_date,
                            "post_text" => $post_text,
                            "post_unswer" => $post_unswer,
                            "post_unswer_date" => $post_unswer_date,
                            "post_user_id" => $post_user_id
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("guestbook_posts_all",$posts_all);
                    $this->registry->main_class->assign("guestbook_total_posts",$numrows2);
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
                    $this->registry->main_class->assign("guestbook_posts_all","no");
                    $this->registry->main_class->assign("guestbook_total_posts","0");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->assign("guestbook_posts_all","no");
                $this->registry->main_class->assign("guestbook_total_posts","0");
                $this->registry->main_class->assign("html","no");
            }
            $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_show.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|display|".$cachuser."|".$num_page."|".$this->registry->language);
    }


    public function add() {
        if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
            $this->registry->main_class->assign("enter_check","yes");
        } else {
            $this->registry->main_class->assign("enter_check","no");
        }
        $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
        if($this->registry->main_class->is_user_in_mainfunc()) {
            $row = $this->registry->main_class->get_user_info($this->registry->user);
            if(isset($row) and $row != "") {
                $guestbook_user_id = intval($row['0']);
                $guestbook_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $guestbook_user_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['8']));
                $guestbook_user_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['9']));
                $guestbook_user_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['11']));
                $guestbook_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $guestbook_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                $guestbook_user_viewemail = intval($row['18']);
                if((!isset($guestbook_user_name) or $guestbook_user_name == "") and (isset($guestbook_user_surname) and $guestbook_user_surname != "")) {
                    $guestbook_user_name = $guestbook_user_surname;
                } elseif((!isset($guestbook_user_name) or $guestbook_user_name == "") and (!isset($guestbook_user_surname) or $guestbook_user_surname == "")) {
                    $guestbook_user_name = $this->registry->main_class->extracting_data($row['5']);
                } elseif(isset($guestbook_user_name) and $guestbook_user_name !== "" and isset($guestbook_user_surname) and $guestbook_user_surname !== "") {
                    $guestbook_user_name = $guestbook_user_name." ".$guestbook_user_surname;
                }
            } else {
                $guestbook_user_name = "no";
                $guestbook_user_id = "no";
                $guestbook_user_email = "no";
                $guestbook_user_country = "no";
                $guestbook_user_city = "no";
                $guestbook_user_telephone = "no";
                $guestbook_user_viewemail = "no";
            }
            $this->registry->main_class->assign("modules_guestbook_sendername",$guestbook_user_name);
            $this->registry->main_class->assign("modules_guestbook_senderid",$guestbook_user_id);
            $this->registry->main_class->assign("modules_guestbook_senderemail",$guestbook_user_email);
            $this->registry->main_class->assign("modules_guestbook_sendercountry",$guestbook_user_country);
            $this->registry->main_class->assign("modules_guestbook_sendercity",$guestbook_user_city);
            $this->registry->main_class->assign("modules_guestbook_sendertelephone",$guestbook_user_telephone);
            $this->registry->main_class->assign("modules_guestbook_viewsenderemail","".$guestbook_user_viewemail."");
        } else {
            $this->registry->main_class->assign("modules_guestbook_sendername","no");
            $this->registry->main_class->assign("modules_guestbook_senderid","no");
            $this->registry->main_class->assign("modules_guestbook_senderemail","no");
            $this->registry->main_class->assign("modules_guestbook_sendercountry","no");
            $this->registry->main_class->assign("modules_guestbook_sendercity","no");
            $this->registry->main_class->assign("modules_guestbook_sendertelephone","no");
            $this->registry->main_class->assign("modules_guestbook_viewsenderemail","no");
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|add|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_add.html");
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|add|".$this->registry->language);
    }


    public function insert() {
        if(isset($_POST['post_user_name'])) {
            $post_user_name = trim($_POST['post_user_name']);
        }
        if(isset($_POST['post_user_country'])) {
            $post_user_country = trim($_POST['post_user_country']);
        }
        if(isset($_POST['post_user_city'])) {
            $post_user_city = trim($_POST['post_user_city']);
        }
        if(isset($_POST['post_user_telephone'])) {
            $post_user_telephone = trim($_POST['post_user_telephone']);
        }
        if(isset($_POST['post_show_telephone'])) {
            $post_show_telephone = intval($_POST['post_show_telephone']);
        }
        if(isset($_POST['post_user_email'])) {
            $post_user_email = trim($_POST['post_user_email']);
        }
        if(isset($_POST['post_show_email'])) {
            $post_show_email = intval($_POST['post_show_email']);
        }
        if(isset($_POST['post_text'])) {
            $post_text = trim($_POST['post_text']);
        }
        if((!isset($_POST['post_user_name']) or !isset($_POST['post_user_country']) or !isset($_POST['post_user_city']) or !isset($_POST['post_user_telephone']) or !isset($_POST['post_user_email']) or !isset($_POST['post_text'])) or ($post_user_name == "" or $post_user_email == "" or $post_text == "")) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|error|notalldata|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                $this->registry->main_class->assign("message","notalldata");
                $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|error|notalldata|".$this->registry->language);
            exit();
        }
        if(ENTER_CHECK == "yes") {
            if(extension_loaded("gd") and session_id() == "") {
                session_start();
            }
            if(extension_loaded("gd") and ((!isset($_POST["captcha"])) or (isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->registry->main_class->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|error|checkkode|".$this->registry->language)) {
                    $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                    $this->registry->main_class->assign("message","checkkode");
                    $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|error|checkkode|".$this->registry->language);
                exit();
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
        }
        if(isset($this->systemdk_guestbook_antispam_ipaddr) and $this->systemdk_guestbook_antispam_ipaddr == "yes") {
            $post_date_check = $this->registry->main_class->gettime() - 86400;
            $sql = "SELECT count(*) FROM ".PREFIX."_guestbook WHERE post_user_ip = '".$this->registry->ip_addr."' and post_date > ".$post_date_check;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows >= intval($this->systemdk_guestbook_antispam_num) and intval($this->systemdk_guestbook_antispam_num) > 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|error|postlimit|".$this->registry->language)) {
                        $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                        $this->registry->main_class->assign("message","postlimit");
                        $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|error|postlimit|".$this->registry->language);
                    exit();
                }
            }
        }
        $post_user_email = $this->registry->main_class->format_striptags($post_user_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($post_user_email))) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|error|useremail|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                $this->registry->main_class->assign("message","useremail");
                $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|error|useremail|".$this->registry->language);
            exit();
        }
        $post_user_name = $this->registry->main_class->format_striptags($post_user_name);
        $post_user_country = $this->registry->main_class->format_striptags($post_user_country);
        $post_user_city = $this->registry->main_class->format_striptags($post_user_city);
        $post_user_telephone = $this->registry->main_class->format_striptags($post_user_telephone);
        $post_text = $this->registry->main_class->format_striptags($post_text);
        if($this->registry->main_class->is_user_in_mainfunc()) {
            $row = $this->registry->main_class->get_user_info($this->registry->user);
            if(isset($row) and $row != "") {
                $guestbook_user_id = intval($row['0']);
                if($guestbook_user_id == 0) {
                    $guestbook_user_id = 'NULL';
                } else {
                    $guestbook_user_id = "'".$guestbook_user_id."'";
                }
                $guestbook_user_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['1']));
                $guestbook_user_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['2']));
                $guestbook_user_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($row['4']));
                if((!isset($guestbook_user_name) or $guestbook_user_name == "") and (isset($guestbook_user_surname) and $guestbook_user_surname != "")) {
                    $guestbook_user_name = $guestbook_user_surname;
                } elseif((!isset($guestbook_user_name) or $guestbook_user_name == "") and (!isset($guestbook_user_surname) or $guestbook_user_surname == "")) {
                    $guestbook_user_name = $this->registry->main_class->extracting_data($row['5']);
                } elseif(isset($guestbook_user_name) and $guestbook_user_name !== "" and isset($guestbook_user_surname) and $guestbook_user_surname !== "") {
                    $guestbook_user_name = $guestbook_user_name." ".$guestbook_user_surname;
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
        if(isset($guestbook_user_name) and $guestbook_user_name !== "no" and $guestbook_user_name !== "") {
            $post_user_name = $guestbook_user_name;
        }
        if(isset($guestbook_user_email) and $guestbook_user_email !== "no" and $guestbook_user_email !== "") {
            $post_user_email = $guestbook_user_email;
        }
        $post_user_email = $this->registry->main_class->processing_data($post_user_email);
        $post_user_name = $this->registry->main_class->processing_data($post_user_name);
        $post_user_country = $this->registry->main_class->processing_data($post_user_country);
        $post_user_city = $this->registry->main_class->processing_data($post_user_city);
        $post_user_telephone = $this->registry->main_class->processing_data($post_user_telephone);
        $post_text = $this->registry->main_class->processing_data($post_text);
        $post_text = substr(substr($post_text,0,-1),1);
        $post_text_length = strlen($post_text);
        if($post_text_length > 10000) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|error|postlength|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                $this->registry->main_class->assign("message","postlength");
                $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|error|postlength|".$this->registry->language);
            exit();
        }
        $now = $this->registry->main_class->gettime();
        if(isset($this->systemdk_guestbook_confirm_need) and $this->systemdk_guestbook_confirm_need == "yes") {
            $post_status = 0;
        } else {
            $post_status = 1;
        }
        if(!isset($_POST['post_show_telephone'])) {
            $post_show_telephone = 1;
        }
        if(!isset($_POST['post_show_email'])) {
            $post_show_email = 1;
        }
        $post_add_id = $this->db->GenID(PREFIX."_guestbook_id");
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_guestbook(post_id,post_user_name,post_user_country,post_user_city,post_user_telephone,post_show_telephone,post_user_email,post_show_email,post_user_id,post_user_ip,post_date,post_text,post_status,post_unswer,post_unswer_date)  VALUES ('".$post_add_id."',".$post_user_name.",".$post_user_country.",".$post_user_city.",".$post_user_telephone.",'".$post_show_telephone."',".$post_user_email.",'".$post_show_email."',".$guestbook_user_id.",'".$this->registry->ip_addr."','".$now."',empty_clob(),'".$post_status."',NULL,NULL)";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message =  $this->db->ErrorMsg();
                $error_code =  $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $insert_result2 = $this->db->UpdateClob(PREFIX.'_guestbook','post_text',$post_text,'post_id='.$post_add_id);
            if($insert_result2 === false) {
                $error_message =  $this->db->ErrorMsg();
                $error_code =  $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $insert_result = false;
            }
             $this->db->CompleteTrans();
        } else {
            $sql = "INSERT INTO ".PREFIX."_guestbook(post_id,post_user_name,post_user_country,post_user_city,post_user_telephone,post_show_telephone,post_user_email,post_show_email,post_user_id,post_user_ip,post_date,post_text,post_status,post_unswer,post_unswer_date)  VALUES ('".$post_add_id."',".$post_user_name.",".$post_user_country.",".$post_user_city.",".$post_user_telephone.",'".$post_show_telephone."',".$post_user_email.",'".$post_show_email."',".$guestbook_user_id.",'".$this->registry->ip_addr."','".$now."','".$post_text."','".$post_status."',NULL,NULL)";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $error_message =  $this->db->ErrorMsg();
                $error_code =  $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("errortext",$error);
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                $this->registry->main_class->assign("message","notinsert");
                $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|error|sqlerror|".$this->registry->language);
            exit();
        } elseif($post_status == 1) {
            $this->registry->main_class->systemdk_clearcache("modules|guestbook|display");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showcurrent");
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|sendok|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                $this->registry->main_class->assign("message","insertok");
                $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|sendok|".$this->registry->language);
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|guestbook|showpending");
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|guestbook|willsendok|".$this->registry->language)) {
                $this->registry->main_class->configLoad($this->registry->language."/modules/guestbook/guestbook.conf");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_GUESTBOOK_MODULE_METATITLE'),$this->systemdk_guestbook_metadescription,$this->systemdk_guestbook_metakeywords);
                $this->registry->main_class->assign("message","willinsertok");
                $this->registry->main_class->assign("include_center","modules/guestbook/guestbook_messages.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|guestbook|willsendok|".$this->registry->language);
        }
    }
}
?>
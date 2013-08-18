<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_news extends model_base {


    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry->main_class->modules_news_programm();
    }


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONNEWSPAGETITLE'));
        $sql = "SELECT (SELECT count(a.news_id) FROM ".PREFIX."_news_".$this->registry->sitelang." a) as count1, (SELECT count(b.news_id) FROM ".PREFIX."_news_auto_".$this->registry->sitelang." b) as count2 ".$this->registry->main_class->check_db_need_from_clause();
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $count_news = intval($result->fields['0']);
            } else {
                $count_news = 0;
            }
            if(isset($result->fields['1'])) {
                $count_waitnews = intval($result->fields['1']);
            } else {
                $count_waitnews = 0;
            }
        } else {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|sqlerror|".$this->registry->language);
            exit();
        }
        $this->registry->main_class->assign("modules_news_num_news",$count_news);
        $this->registry->main_class->assign("modules_news_num_programmnews",$count_waitnews);
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|show|".$this->registry->language)) {
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|show|".$this->registry->language);
    }


    public function news_current() {
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $temp = "programm";
            $tbl = "_auto";
            $sql_order = "ASC";
        } else {
            $temp = "current";
            $tbl = "";
            $sql_order = "DESC";
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|show".$temp."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.news_id,a.news_author,a.news_category_id,a.news_title,a.news_date,a.news_valueview,a.news_status,a.news_termexpire,a.news_termexpireaction,a.news_onhome,b.news_category_name,(SELECT count(c.news_id) FROM ".PREFIX."_news".$tbl."_".$this->registry->sitelang." c) as count_news_id FROM ".PREFIX."_news".$tbl."_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_news_categories_".$this->registry->sitelang." b on a.news_category_id=b.news_category_id order by a.news_date ".$sql_order;
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_news&func=news_current&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['11']);
                        }
                        $news_id = intval($result->fields['0']);
                        $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $news_category_id = intval($result->fields['2']);
                        if($news_category_id != 0 and isset($result->fields['10']) and trim($result->fields['10']) != "") {
                            $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                        } else {
                            $news_category_name = "no";
                        }
                        $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                        $news_date = intval($result->fields['4']);
                        $news_date = date("d.m.y H:i",$news_date);
                        $news_valueview = intval($result->fields['5']);
                        $news_status = intval($result->fields['6']);
                        $news_termexpire = intval($result->fields['7']);
                        if(!isset($news_termexpire) or $news_termexpire == "" or $news_termexpire == 0) {
                            $news_termexpire = "unlim";
                        } else {
                            $news_termexpire = "notunlim";
                        }
                        $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                        $news_onhome = intval($result->fields['9']);
                        $news_all[] = array(
                            "news_id" => $news_id,
                            "news_author" => $news_author,
                            "news_category_id" => $news_category_id,
                            "news_category_name" => $news_category_name,
                            "news_title" => $news_title,
                            "news_date" => $news_date,
                            "news_valueview" => $news_valueview,
                            "news_status" => $news_status,
                            "news_termexpire" => $news_termexpire,
                            "news_termexpireaction" => $news_termexpireaction,
                            "news_onhome" => $news_onhome,
                            "count_news_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("news_all",$news_all);
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
                        $this->registry->main_class->assign("totalnews",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("news_all","no");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|showsqlerror".$temp."|".$this->registry->language)) {
                    if($temp != "programm") {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONCURRNEWSPAGETITLE'));
                    } else {
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONPROGNEWSPAGETITLE'));
                    }
                    $this->registry->main_class->assign("news_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|showsqlerror".$temp."|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|show".$temp."|".$num_page."|".$this->registry->language)) {
            if($temp != "programm") {
                $this->registry->main_class->assign("adminmodules_news_programm","no");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONCURRNEWSPAGETITLE'));
            } else {
                $this->registry->main_class->assign("adminmodules_news_programm","yes");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONPROGNEWSPAGETITLE'));
            }
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_current.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|show".$temp."|".$num_page."|".$this->registry->language);
    }


    public function news_edit() {
        if(isset($_GET['news_id'])) {
            $news_id = intval($_GET['news_id']);
        }
        if(!isset($_GET['news_id']) or $news_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $temp = "programm";
            $tbl = "_auto";
            $this->registry->main_class->assign("include_jquery","yes");
            $this->registry->main_class->assign("include_jquery_data","dateinput");
        } else {
            $temp = "";
            $tbl = "";
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if($temp != "programm") {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITCURRNEWSPAGETITLE'));
        } else {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITPROGNEWSPAGETITLE'));
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|edit".$temp."|".$news_id."|".$this->registry->language)) {
            $sql = "SELECT news_id,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_readcounter,news_notes,news_valueview,news_status,news_termexpire,news_termexpireaction,news_onhome,news_link FROM ".PREFIX."_news".$tbl."_".$this->registry->sitelang." WHERE news_id='".$news_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $news_id = intval($result->fields['0']);
                    $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $news_category_id = intval($result->fields['2']);
                    $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $news_date = intval($result->fields['4']);
                    if($temp == "programm") {
                        $news_date2 = date("d.m.y",$news_date);
                    } else {
                        $news_date2 = date("d.m.y H:i",$news_date);
                    }
                    $news_hour = date("H",$news_date);
                    $news_minute = date("i",$news_date);
                    $news_images = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    if(!isset($news_images) or $news_images == "" or $news_images == 0) {
                        $news_images = "no";
                    }
                    $news_short_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                    $news_content = $this->registry->main_class->extracting_data($result->fields['7']);
                    $news_readcounter = intval($result->fields['8']);
                    $news_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                    $news_valueview = intval($result->fields['10']);
                    $news_status = intval($result->fields['11']);
                    $news_termexpire = intval($result->fields['12']);
                    if(isset($news_termexpire) and $news_termexpire !== "" and $news_termexpire !== 0) {
                        $news_termexpireday = intval((($news_termexpire - $this->registry->main_class->gettime()) / 3600) / 24);
                        $news_termexpire = date("d.m.y H:i",$news_termexpire);
                    } else {
                        $news_termexpire = "";
                        $news_termexpireday = "";
                    }
                    $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                    $news_onhome = intval($result->fields['14']);
                    $news_link = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                    $sql2 = "SELECT news_category_id,news_category_name FROM ".PREFIX."_news_categories_".$this->registry->sitelang;
                    $result2 = $this->db->Execute($sql2);
                    if($result2) {
                        if(isset($result2->fields['0'])) {
                            $numrows2 = intval($result2->fields['0']);
                        } else {
                            $numrows2 = 0;
                        }
                        if($numrows2 > 0) {
                            while(!$result2->EOF) {
                                $news_category_id2 = intval($result2->fields['0']);
                                $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result2->fields['1']));
                                $category_all[] = array(
                                    "news_category_id" => $news_category_id2,
                                    "news_category_name" => $news_category_name
                                );
                                $result2->MoveNext();
                            }
                            $this->registry->main_class->assign("category_all",$category_all);
                        } else {
                            $this->registry->main_class->assign("category_all","no");
                        }
                    } else {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                        $this->registry->main_class->assign("error",$error);
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editsqlerror".$temp."|".$this->registry->language)) {
                            $this->registry->main_class->assign("news_update","notinsert");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editsqlerror".$temp."|".$this->registry->language);
                        exit();
                    }
                    require_once('../modules/news/news_config.inc');
                    $news_all[] = array(
                        "news_id" => $news_id,
                        "news_author" => $news_author,
                        "news_category_id" => $news_category_id,
                        "news_title" => $news_title,
                        "news_date" => $news_date,
                        "news_hour" => $news_hour,
                        "news_minute" => $news_minute,
                        "news_date2" => $news_date2,
                        "news_images" => $news_images,
                        "news_short_text" => $news_short_text,
                        "news_content" => $news_content,
                        "news_readcounter" => $news_readcounter,
                        "news_notes" => $news_notes,
                        "news_valueview" => $news_valueview,
                        "news_status" => $news_status,
                        "news_termexpire" => $news_termexpire,
                        "news_termexpireday" => $news_termexpireday,
                        "news_termexpireaction" => $news_termexpireaction,
                        "news_onhome" => $news_onhome,
                        "news_link" => $news_link,
                        "image_path" => MODULE_NEWS_IMAGE_PATH
                    );
                    $this->registry->main_class->assign("news_all",$news_all);
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnotfind".$temp."|".$this->registry->language)) {
                        $this->registry->main_class->assign("news_update","notfind");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnotfind".$temp."|".$this->registry->language);
                    exit();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editsqlerror".$temp."|".$this->registry->language)) {
                    $this->registry->main_class->assign("news_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editsqlerror".$temp."|".$this->registry->language);
                exit();
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|edit".$temp."|".$news_id."|".$this->registry->language)) {
            if($temp != "programm") {
                $this->registry->main_class->assign("adminmodules_news_programm","no");
            } else {
                $this->registry->main_class->assign("adminmodules_news_programm","yes");
            }
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_edit.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|edit".$temp."|".$news_id."|".$this->registry->language);
    }


    public function news_edit_inbase() {
        if(isset($_POST['news_id'])) {
            $news_id = intval($_POST['news_id']);
        }
        if(isset($_POST['news_author'])) {
            $news_author = trim($_POST['news_author']);
        }
        if(isset($_POST['news_category_id'])) {
            $news_category_id = intval($_POST['news_category_id']);
        }
        if(isset($_POST['news_title'])) {
            $news_title = trim($_POST['news_title']);
        }
        if(isset($_POST['news_date'])) {
            if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
                $news_date = trim($_POST['news_date']);
            } else {
                $news_date = intval($_POST['news_date']);
            }
        }
        if(isset($_POST['news_newdate'])) {
            $news_newdate = trim($_POST['news_newdate']);
        }
        if(isset($_POST['delete_picture'])) {
            $delete_picture = trim($_POST['delete_picture']);
        }
        if(isset($_POST['news_short_text'])) {
            $news_short_text = trim($_POST['news_short_text']);
        }
        if(isset($_POST['news_content'])) {
            $news_content = trim($_POST['news_content']);
        }
        if(isset($_POST['news_readcounter'])) {
            $news_readcounter = intval($_POST['news_readcounter']);
        }
        if(isset($_POST['news_notes'])) {
            $news_notes = trim($_POST['news_notes']);
        }
        if(isset($_POST['news_valueview'])) {
            $news_valueview = intval($_POST['news_valueview']);
        }
        if(isset($_POST['news_status'])) {
            $news_status = intval($_POST['news_status']);
        }
        if(isset($_POST['news_termexpire'])) {
            $news_termexpire = intval($_POST['news_termexpire']);
        }
        if(isset($_POST['news_termexpireaction'])) {
            $news_termexpireaction = trim($_POST['news_termexpireaction']);
        }
        if(isset($_POST['news_onhome'])) {
            $news_onhome = intval($_POST['news_onhome']);
        }
        if(isset($_POST['news_link'])) {
            $news_link = trim($_POST['news_link']);
        }
        if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
            $temp = "programm";
            $tbl = "_auto";
        } else {
            $temp = "";
            $tbl = "";
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if($temp != "programm") {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITCURRNEWSPAGETITLE'));
        } else {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITPROGNEWSPAGETITLE'));
        }
        if((!isset($_POST['news_id']) or !isset($_POST['news_author']) or !isset($_POST['news_category_id']) or !isset($_POST['news_title']) or !isset($_POST['news_date']) or !isset($_POST['news_newdate']) or !isset($_POST['delete_picture']) or !isset($_POST['news_short_text']) or !isset($_POST['news_content']) or !isset($_POST['news_readcounter']) or !isset($_POST['news_notes']) or !isset($_POST['news_valueview']) or !isset($_POST['news_status']) or !isset($_POST['news_termexpire']) or !isset($_POST['news_termexpireaction']) or !isset($_POST['news_onhome']) or !isset($_POST['news_link'])) or ($news_id == 0 or $news_author == "" or $news_title == "" or ((isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1 and $news_date == "") or (!isset($_POST['prog']) and $news_date == 0)) or $news_newdate == "" or $delete_picture == "" or $news_short_text == "" or $news_content == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnotalldata".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnotalldata".$temp."|".$this->registry->language);
            exit();
        }
        if($temp != "programm" and isset($news_newdate) and $news_newdate == 'yes') {
            $news_date = $this->registry->main_class->gettime();
        } elseif($temp == "programm") {
            $news_date = $this->registry->main_class->format_striptags($news_date);
            if(isset($_POST['news_hour'])) {
                $news_hour = intval($_POST['news_hour']);
            } else {
                $news_hour = 0;
            }
            if(isset($_POST['news_minute'])) {
                $news_minute = intval($_POST['news_minute']);
            } else {
                $news_minute = 0;
            }
            $news_date = $news_date.".".$news_hour.".".$news_minute;
            $news_date = $this->registry->main_class->timetounix($news_date);
        }
        if($news_termexpire == 0) {
            $news_termexpire = 'NULL';
            $news_termexpireaction = 'NULL';
        } else {
            if($temp == "programm") {
                $news_termexpire = $news_date + ($news_termexpire * 86400);
            } else {
                $news_termexpire = $this->registry->main_class->gettime() + ($news_termexpire * 86400);
            }
            $news_termexpire = "'$news_termexpire'";
            $news_termexpireaction = $this->registry->main_class->format_striptags($news_termexpireaction);
            $news_termexpireaction = $this->registry->main_class->processing_data($news_termexpireaction);
        }
        if($news_notes == "") {
            $news_notes = 'NULL';
        } else {
            $news_notes = $this->registry->main_class->processing_data($news_notes);
        }
        if($news_link == "") {
            $news_link = 'NULL';
        } else {
            $news_link = $this->registry->main_class->format_striptags($news_link);
            $news_link = $this->registry->main_class->processing_data($news_link);
        }
        $news_author = $this->registry->main_class->format_striptags($news_author);
        $news_author = $this->registry->main_class->processing_data($news_author);
        $news_title = $this->registry->main_class->format_striptags($news_title);
        $news_title = $this->registry->main_class->processing_data($news_title);
        $news_short_text = $this->registry->main_class->processing_data($news_short_text);
        $news_short_text = substr(substr($news_short_text,0,-1),1);
        $news_content = $this->registry->main_class->processing_data($news_content);
        $news_content = substr(substr($news_content,0,-1),1);
        require_once('../modules/news/news_config.inc');
        if($delete_picture == 'yes') {
            $news_images = trim($_POST['news_images']);
            $news_images = $this->registry->main_class->format_striptags($news_images);
            @unlink("../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
            $news_images = 'NULL';
        } elseif($delete_picture == 'new_file') {
            if(isset($_FILES['news_images']['name']) and trim($_FILES['news_images']['name']) !== '') {
                if(is_uploaded_file($_FILES['news_images']['tmp_name'])) {
                    if($_FILES['news_images']['size'] > MODULE_NEWS_IMAGE_MAX_SIZE) {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editmaxsize".$temp."|".$this->registry->language)) {
                            $this->registry->main_class->assign("news_update","maxsize");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editmaxsize".$temp."|".$this->registry->language);
                        exit();
                    }
                    if(($_FILES['news_images']['type'] == "image/gif") || ($_FILES['news_images']['type'] == "image/pjpeg") || ($_FILES['news_images']['type'] == "image/jpeg") || ($_FILES['news_images']['type'] == "image/png") || ($_FILES['news_images']['type'] == "image/bmp")) {
                        $file_name = $_FILES['news_images']['name'];
                        $data_kod = time();
                        $news_images = $data_kod."_".$file_name;
                        $result_file = @copy($_FILES['news_images']['tmp_name'],"../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
                        if(!$result_file) {
                            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnotupload".$temp."|".$this->registry->language)) {
                                $this->registry->main_class->assign("news_update","notupload");
                                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                            }
                            $this->registry->main_class->database_close();
                            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnotupload".$temp."|".$this->registry->language);
                            exit();
                        }
                        $size2 = @getimagesize("../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
                        if(($size2[0] > MODULE_NEWS_IMAGE_MAX_WIDTH) or ($size2[1] > MODULE_NEWS_IMAGE_MAX_HEIGHT)) {
                            @unlink("../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
                            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnoteq".$temp."|".$this->registry->language)) {
                                $this->registry->main_class->assign("news_update","noteq");
                                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                            }
                            $this->registry->main_class->database_close();
                            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnoteq".$temp."|".$this->registry->language);
                            exit();
                        }
                    } else {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnoteqtype".$temp."|".$this->registry->language)) {
                            $this->registry->main_class->assign("news_update","noteqtype");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editnoteqtype".$temp."|".$this->registry->language);
                        exit();
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editfile".$temp."|".$this->registry->language)) {
                        $this->registry->main_class->assign("news_update","attack");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editfile".$temp."|".$this->registry->language);
                    exit();
                }
                $news_images = "'$news_images'";
            } else {
                $news_images = 'NULL';
            }
        } else {
            $news_images = trim($_POST['news_images']);
            $news_images = $this->registry->main_class->format_striptags($news_images);
            $news_images = $this->registry->main_class->processing_data($news_images);
        }
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "UPDATE ".PREFIX."_news".$tbl."_".$this->registry->sitelang." SET news_author=$news_author,news_category_id='$news_category_id',news_title=$news_title,news_date='$news_date',news_images=$news_images,news_short_text=empty_clob(),news_content=empty_clob(),news_readcounter='$news_readcounter',news_notes=empty_clob(),news_valueview='$news_valueview',news_status='$news_status',news_termexpire=$news_termexpire,news_termexpireaction=$news_termexpireaction,news_onhome='$news_onhome',news_link=$news_link WHERE news_id='$news_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_news'.$tbl."_".$this->registry->sitelang,'news_short_text',$news_short_text,'news_id='.$news_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result3 = $this->db->UpdateClob(PREFIX.'_news'.$tbl."_".$this->registry->sitelang,'news_content',$news_content,'news_id='.$news_id);
            if($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($news_notes != 'NULL') {
                $news_notes = substr(substr($news_notes,0,-1),1);
                $result4 = $this->db->UpdateClob(PREFIX.'_news'.$tbl."_".$this->registry->sitelang,'news_notes',$news_notes,'news_id='.$news_id);
                if($result4 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $result4 = true;
            }
            $this->db->CompleteTrans();
            if($result2 === false or $result3 === false or $result4 === false) {
                $result = false;
            }
        } else {
            $sql = "UPDATE ".PREFIX."_news".$tbl."_".$this->registry->sitelang." SET news_author=$news_author,news_category_id='$news_category_id',news_title=$news_title,news_date='$news_date',news_images=$news_images,news_short_text='$news_short_text',news_content='$news_content',news_readcounter='$news_readcounter',news_notes=$news_notes,news_valueview='$news_valueview',news_status='$news_status',news_termexpire=$news_termexpire,news_termexpireaction=$news_termexpireaction,news_onhome='$news_onhome',news_link=$news_link WHERE news_id='$news_id'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editsqlerror".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editsqlerror".$temp."|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editok".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editok".$temp."|".$this->registry->language);
            exit();
        } else {
            if($temp == "programm") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm|".$news_id);
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit|".$news_id);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread|".$news_id);
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread|".$news_id."admin");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread|".$news_id."user");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show");
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|editok".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                $this->registry->main_class->assign("news_update","editok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|editok".$temp."|".$this->registry->language);
        }
    }


    public function news_add() {
        if(isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) {
            $temp = "programm";
            $this->registry->main_class->assign("include_jquery","yes");
            $this->registry->main_class->assign("include_jquery_data","dateinput");
        } else {
            $temp = "";
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if($temp != "programm") {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDCURRNEWSPAGETITLE'));
        } else {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDPROGNEWSPAGETITLE'));
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news".$temp."|add|".$this->registry->language)) {
            $sql = "SELECT news_category_id,news_category_name FROM ".PREFIX."_news_categories_".$this->registry->sitelang;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $news_category_id = intval($result->fields['0']);
                        $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $category_all[] = array(
                            "news_category_id" => $news_category_id,
                            "news_category_name" => $news_category_name
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("category_all",$category_all);
                } else {
                    $this->registry->main_class->assign("category_all","no");
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addsqlerror".$temp."|".$this->registry->language)) {
                    $this->registry->main_class->assign("news_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addsqlerror".$temp."|".$this->registry->language);
                exit();
            }
        }
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news".$temp."|add|".$this->registry->language)) {
            if($temp != "programm") {
                $this->registry->main_class->assign("adminmodules_news_programm","no");
            } else {
                $this->registry->main_class->assign("adminmodules_news_programm","yes");
            }
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_add.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news".$temp."|add|".$this->registry->language);
    }


    public function news_add_inbase() {
        if(isset($_POST['news_category_id'])) {
            $news_category_id = intval($_POST['news_category_id']);
        }
        if(isset($_POST['news_title'])) {
            $news_title = trim($_POST['news_title']);
        }
        if(isset($_POST['news_short_text'])) {
            $news_short_text = trim($_POST['news_short_text']);
        }
        if(isset($_POST['news_content'])) {
            $news_content = trim($_POST['news_content']);
        }
        if(isset($_POST['news_notes'])) {
            $news_notes = trim($_POST['news_notes']);
        }
        if(isset($_POST['news_valueview'])) {
            $news_valueview = intval($_POST['news_valueview']);
        }
        if(isset($_POST['news_status'])) {
            $news_status = intval($_POST['news_status']);
        }
        if(isset($_POST['news_termexpire'])) {
            $news_termexpire = intval($_POST['news_termexpire']);
        }
        if(isset($_POST['news_termexpireaction'])) {
            $news_termexpireaction = trim($_POST['news_termexpireaction']);
        }
        if(isset($_POST['news_onhome'])) {
            $news_onhome = intval($_POST['news_onhome']);
        }
        if(isset($_POST['news_link'])) {
            $news_link = trim($_POST['news_link']);
        }
        if(isset($_POST['news_date'])) {
            $news_date = trim($_POST['news_date']);
        }
        if(isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1) {
            $temp = "programm";
            $tbl = "_auto";
        } else {
            $temp = "";
            $tbl = "";
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if($temp != "programm") {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDCURRNEWSPAGETITLE'));
        } else {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDPROGNEWSPAGETITLE'));
        }
        if((!isset($_POST['news_category_id']) or !isset($_POST['news_title']) or !isset($_POST['news_short_text']) or !isset($_POST['news_content']) or !isset($_POST['news_notes']) or !isset($_POST['news_valueview']) or !isset($_POST['news_status']) or !isset($_POST['news_termexpire']) or !isset($_POST['news_termexpireaction']) or !isset($_POST['news_onhome']) or !isset($_POST['news_link'])) or ($news_title == "" or $news_short_text == "" or $news_content == "" or (isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1 and (!isset($news_date) or $news_date == "")))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnotalldata".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnotalldata".$temp."|".$this->registry->language);
            exit();
        }
        if($temp == "programm") {
            $news_date = $this->registry->main_class->format_striptags($news_date);
            if(isset($_POST['news_hour'])) {
                $news_hour = intval($_POST['news_hour']);
            } else {
                $news_hour = 0;
            }
            if(isset($_POST['news_minute'])) {
                $news_minute = intval($_POST['news_minute']);
            } else {
                $news_minute = 0;
            }
            $news_date = $news_date.".".$news_hour.".".$news_minute;
            $now = $this->registry->main_class->timetounix($news_date);
        } else {
            $now = $this->registry->main_class->gettime();
        }
        if($news_termexpire == 0) {
            $news_termexpire = 'NULL';
            $news_termexpireaction = 'NULL';
        } else {
            if($temp == "programm") {
                $news_termexpire = $now + ($news_termexpire * 86400);
            } else {
                $news_termexpire = $this->registry->main_class->gettime() + ($news_termexpire * 86400);
            }
            $news_termexpire = "'$news_termexpire'";
            $news_termexpireaction = $this->registry->main_class->format_striptags($news_termexpireaction);
            $news_termexpireaction = $this->registry->main_class->processing_data($news_termexpireaction);
        }
        if($news_notes == "") {
            $news_notes = 'NULL';
        } else {
            $news_notes = $this->registry->main_class->processing_data($news_notes);
        }
        if($news_link == "") {
            $news_link = 'NULL';
        } else {
            $news_link = $this->registry->main_class->format_striptags($news_link);
            $news_link = $this->registry->main_class->processing_data($news_link);
        }
        $news_author = trim($this->registry->main_class->get_admin_login());
        $news_author = $this->registry->main_class->format_striptags($news_author);
        $news_author = $this->registry->main_class->processing_data($news_author);
        $news_title = $this->registry->main_class->format_striptags($news_title);
        $news_title = $this->registry->main_class->processing_data($news_title);
        $news_short_text = $this->registry->main_class->processing_data($news_short_text);
        $news_short_text = substr(substr($news_short_text,0,-1),1);
        $news_content = $this->registry->main_class->processing_data($news_content);
        $news_content = substr(substr($news_content,0,-1),1);
        require_once('../modules/news/news_config.inc');
        if(isset($_FILES['news_images']['name']) and trim($_FILES['news_images']['name']) !== '') {
            if(is_uploaded_file($_FILES['news_images']['tmp_name'])) {
                if($_FILES['news_images']['size'] > MODULE_NEWS_IMAGE_MAX_SIZE) {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addmaxsize".$temp."|".$this->registry->language)) {
                        $this->registry->main_class->assign("news_update","maxsize");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addmaxsize".$temp."|".$this->registry->language);
                    exit();
                }
                if(($_FILES['news_images']['type'] == "image/gif") || ($_FILES['news_images']['type'] == "image/pjpeg") || ($_FILES['news_images']['type'] == "image/jpeg") || ($_FILES['news_images']['type'] == "image/png") || ($_FILES['news_images']['type'] == "image/bmp")) {
                    $file_name = $_FILES['news_images']['name'];
                    $data_kod = time();
                    $news_images = $data_kod."_".$file_name;
                    $result_file = @copy($_FILES['news_images']['tmp_name'],"../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
                    if(!$result_file) {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnotupload".$temp."|".$this->registry->language)) {
                            $this->registry->main_class->assign("news_update","notupload");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnotupload".$temp."|".$this->registry->language);
                        exit();
                    }
                    $size2 = @getimagesize("../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
                    if(($size2[0] > MODULE_NEWS_IMAGE_MAX_WIDTH) or ($size2[1] > MODULE_NEWS_IMAGE_MAX_HEIGHT)) {
                        @unlink("../".MODULE_NEWS_IMAGE_PATH."/".$news_images);
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnoteq".$temp."|".$this->registry->language)) {
                            $this->registry->main_class->assign("news_update","noteq");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnoteq".$temp."|".$this->registry->language);
                        exit();
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnoteqtype".$temp."|".$this->registry->language)) {
                        $this->registry->main_class->assign("news_update","noteqtype");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addnoteqtype".$temp."|".$this->registry->language);
                    exit();
                }
            } else {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addfile".$temp."|".$this->registry->language)) {
                    $this->registry->main_class->assign("news_update","attack");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addfile".$temp."|".$this->registry->language);
                exit();
            }
            $news_images = "'$news_images'";
        } else {
            $news_images = 'NULL';
        }
        $news_id = $this->db->GenID(PREFIX."_news_id_".$this->registry->sitelang);
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        if($check_db_need_lobs == 'yes') {
            $this->db->StartTrans();
            $sql = "INSERT INTO ".PREFIX."_news".$tbl."_".$this->registry->sitelang." (news_id,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_notes,news_valueview,news_status,news_termexpire,news_termexpireaction,news_onhome,news_link)  VALUES ('$news_id',$news_author,'$news_category_id',$news_title,'$now',$news_images,empty_clob(),empty_clob(),empty_clob(),'$news_valueview','$news_status',$news_termexpire,$news_termexpireaction,'$news_onhome',$news_link)";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result2 = $this->db->UpdateClob(PREFIX.'_news'.$tbl."_".$this->registry->sitelang,'news_short_text',$news_short_text,'news_id='.$news_id);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $result3 = $this->db->UpdateClob(PREFIX.'_news'.$tbl."_".$this->registry->sitelang,'news_content',$news_content,'news_id='.$news_id);
            if($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($news_notes != 'NULL') {
                $news_notes = substr(substr($news_notes,0,-1),1);
                $result4 = $this->db->UpdateClob(PREFIX.'_news'.$tbl."_".$this->registry->sitelang,'news_notes',$news_notes,'news_id='.$news_id);
                if($result4 === false) {
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } else {
                $result4 = true;
            }
            $this->db->CompleteTrans();
            if($result2 === false or $result3 === false or $result4 === false) {
                $result = false;
            }
        } else {
            $sql = "INSERT INTO ".PREFIX."_news".$tbl."_".$this->registry->sitelang." (news_id,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_notes,news_valueview,news_status,news_termexpire,news_termexpireaction,news_onhome,news_link)  VALUES ('$news_id',$news_author,'$news_category_id',$news_title,'$now',$news_images,'$news_short_text','$news_content',$news_notes,'$news_valueview','$news_status',$news_termexpire,$news_termexpireaction,'$news_onhome',$news_link)";
            $result = $this->db->Execute($sql);
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addsqlerror".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addsqlerror".$temp."|".$this->registry->language);
            exit();
        } else {
            if($temp == "programm") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show");
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|addok".$temp."|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                $this->registry->main_class->assign("news_update","add");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|addok".$temp."|".$this->registry->language);
        }
    }


    public function news_category() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) !== 0) {
            $num_page = intval($_GET['num_page']);
            if($num_page == 0) {
                $num_page = 1;
            }
        } else {
            $num_page = 1;
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|showcat|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.news_category_id,a.news_category_name,(SELECT count(b.news_category_id) FROM ".PREFIX."_news_categories_".$this->registry->sitelang." b) as count_category_id FROM ".PREFIX."_news_categories_".$this->registry->sitelang." a order by a.news_category_id DESC";
            $result = $this->db->SelectLimit($sql,$num_string_rows,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    header("Location: index.php?path=admin_news&func=news_category&lang=".$this->registry->sitelang);
                    exit();
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        if(!isset($numrows2)) {
                            $numrows2 = intval($result->fields['2']);
                        }
                        $news_category_id = intval($result->fields['0']);
                        $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $category_all[] = array(
                            "news_category_id" => $news_category_id,
                            "news_category_name" => $news_category_name,
                            "count_categories_id" => $numrows2
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("category_all",$category_all);
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
                        $this->registry->main_class->assign("totalcategories",$numrows2);
                        $this->registry->main_class->assign("num_pages",$num_pages);
                    } else {
                        $this->registry->main_class->assign("html","no");
                    }
                } else {
                    $this->registry->main_class->assign("category_all","no");
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->display_theme_adminheader();
                $this->registry->main_class->display_theme_adminmain();
                $this->registry->main_class->displayadmininfo();
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|showcatsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONCATNEWSPAGETITLE'));
                    $this->registry->main_class->assign("news_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|showcatsqlerror|".$this->registry->language);
                exit();
            }
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|showcat|".$num_page."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONCATNEWSPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_categories.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|showcat|".$num_page."|".$this->registry->language);
    }


    public function category_add() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|addcat|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDCATNEWSPAGETITLE'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/category_add.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|addcat|".$this->registry->language);
    }


    public function category_add_inbase() {
        if(isset($_POST['category_name'])) {
            $category_name = trim($_POST['category_name']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONADDCATNEWSPAGETITLE'));
        if((!isset($_POST['category_name'])) or ($category_name == "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addcatnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addcatnotalldata|".$this->registry->language);
            exit();
        }
        $category_name = $this->registry->main_class->format_striptags($category_name);
        $category_name = $this->registry->main_class->processing_data($category_name);
        $news_category_id = $this->db->GenID(PREFIX."_news_categories_id_".$this->registry->sitelang);
        $sql = "INSERT INTO ".PREFIX."_news_categories_".$this->registry->sitelang."(news_category_id,news_category_name) VALUES ('$news_category_id',$category_name)";
        $result = $this->db->Execute($sql);
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addcatsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|addcatsqlerror|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcat");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|add");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|newsprogramm|add");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|addcatok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                $this->registry->main_class->assign("news_update","addcategory");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|addcatok|".$this->registry->language);
        }
    }


    public function category_edit() {
        if(isset($_GET['category_id'])) {
            $category_id = intval($_GET['category_id']);
        }
        if((!isset($_GET['category_id'])) or ($category_id == 0)) {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITCATNEWSPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|editcat|".$category_id."|".$this->registry->language)) {
            $sql = "SELECT news_category_id,news_category_name FROM ".PREFIX."_news_categories_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    while(!$result->EOF) {
                        $news_category_id = intval($result->fields['0']);
                        $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                        $category_all[] = array(
                            "news_category_id" => $news_category_id,
                            "news_category_name" => $news_category_name
                        );
                        $result->MoveNext();
                    }
                    $this->registry->main_class->assign("category_all",$category_all);
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatnotfind|".$this->registry->language)) {
                        $this->registry->main_class->assign("news_update","notfindcat");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatnotfind|".$this->registry->language);
                    exit();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("news_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatsqlerror|".$this->registry->language);
                exit();
            }
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/category_edit.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|editcat|".$category_id."|".$this->registry->language);
    }


    public function category_edit_inbase() {
        if(isset($_POST['category_name'])) {
            $category_name = trim($_POST['category_name']);
        }
        if(isset($_POST['category_id'])) {
            $category_id = intval($_POST['category_id']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITCATNEWSPAGETITLE'));
        if((!isset($_POST['category_name']) or !isset($_POST['category_id'])) or ($category_name == "" or $category_id == 0)) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatnotalldata|".$this->registry->language);
            exit();
        }
        $category_name = $this->registry->main_class->format_striptags($category_name);
        $category_name = $this->registry->main_class->processing_data($category_name);
        $sql = "UPDATE ".PREFIX."_news_categories_".$this->registry->sitelang." SET news_category_name=$category_name WHERE news_category_id='$category_id'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatok|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|editcatok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|add");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|newsprogramm|add");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcat");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editcat|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|user|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|guest|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|user|no");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin|no");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|guest|no");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|editcatok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                $this->registry->main_class->assign("news_update","editcatok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|editcatok|".$this->registry->language);
        }
    }


    public function category_delete() {
        if(isset($_GET['category_id'])) {
            $category_id = intval($_GET['category_id']);
        }
        if(isset($_GET['conf'])) {
            $conf = intval($_GET['conf']);
        }
        if((!isset($_GET['conf']) or !isset($_GET['category_id'])) or ($category_id == 0)) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatnotalldata|".$this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONDELCATNEWSPAGETITLE'));
                $this->registry->main_class->assign("news_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatnotalldata|".$this->registry->language);
            exit();
        }
        if($conf == 0) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->registry->main_class->displayadmininfo();
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONDELCATNEWSPAGETITLE'));
            $sql = "SELECT a.news_category_id,a.news_category_name,count(b.news_category_id) as check_num FROM ".PREFIX."_news_categories_".$this->registry->sitelang." a LEFT OUTER JOIN (SELECT news_category_id FROM ".PREFIX."_news_".$this->registry->sitelang." UNION ALL SELECT news_category_id FROM ".PREFIX."_news_auto_".$this->registry->sitelang.") b on a.news_category_id=b.news_category_id WHERE a.news_category_id='".$category_id."' group by  a.news_category_id,a.news_category_name";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $news_category_id = intval($result->fields['0']);
                    $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $check_num = intval($result->fields['2']);
                    $this->registry->main_class->assign("category_id",$category_id);
                    $this->registry->main_class->assign("category_del_name",$news_category_name);
                    $this->registry->main_class->assign("count_del_news_id",$check_num);
                    if($check_num == 0) {
                        $this->db->StartTrans();
                        $sql = "DELETE FROM ".PREFIX."_news_categories_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
                        $result2 = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($result2 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $sql = "DELETE FROM ".PREFIX."_news_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
                        $result3 = $this->db->Execute($sql);
                        if($result3 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        $sql = "DELETE FROM ".PREFIX."_news_auto_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
                        $result4 = $this->db->Execute($sql);
                        if($result4 === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        }
                        if($result2 === false or $result3 === false or $result4 === false) {
                            $result = false;
                        }
                        $this->db->CompleteTrans();
                        if($result === false) {
                            $this->registry->main_class->assign("error",$error);
                            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatsqlerror|".$this->registry->language)) {
                                $this->registry->main_class->assign("news_update","notinsert");
                                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                            }
                            $this->registry->main_class->database_close();
                            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatsqlerror|".$this->registry->language);
                            exit();
                        } elseif($result !== false and $num_result == 0) {
                            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatok|".$this->registry->language)) {
                                $this->registry->main_class->assign("news_update","notinsert2");
                                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                            }
                            $this->registry->main_class->database_close();
                            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatok|".$this->registry->language);
                            exit();
                        } else {
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|add");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|newsprogramm|add");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcat");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editcat|".$category_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|user|".$category_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin|".$category_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|guest|".$category_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|user|no");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin|no");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|guest|no");
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread");
                            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|deletecatok|".$this->registry->language)) {
                                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                                $this->registry->main_class->assign("news_update","delcatok");
                            }
                            $this->registry->main_class->database_close();
                            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|deletecatok|".$this->registry->language);
                        }
                        exit();
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatnotfind|".$this->registry->language)) {
                        $this->registry->main_class->assign("news_update","notfindcat");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatnotfind|".$this->registry->language);
                    exit();
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatsqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("news_update","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatsqlerror|".$this->registry->language);
                exit();
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|deletecatconf|".$category_id."|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","deletecatconf");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|deletecatconf|".$category_id."|".$this->registry->language);
            exit();
        } elseif($conf == 1) {
            $this->db->StartTrans();
            $sql = "DELETE FROM ".PREFIX."_news_categories_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
            $result = $this->db->Execute($sql);
            $num_result = $this->db->Affected_Rows();
            if($result === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql = "DELETE FROM ".PREFIX."_news_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
            $result2 = $this->db->Execute($sql);
            if($result2 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            $sql = "DELETE FROM ".PREFIX."_news_auto_".$this->registry->sitelang." WHERE news_category_id='".$category_id."'";
            $result3 = $this->db->Execute($sql);
            if($result3 === false) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
            if($result === false or $result2 === false or $result3 === false) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONDELCATNEWSPAGETITLE'));
        if($result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatok|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|delcatok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|add");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|newsprogramm|add");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcat");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editcat|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|user|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|guest|".$category_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|user|no");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin|no");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|guest|no");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread");
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|deletecatok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
                $this->registry->main_class->assign("news_update","delcatok");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|deletecatok|".$this->registry->language);
        }
    }


    public function news_status() {
        if(isset($_GET['action'])) {
            $action = trim($_GET['action']);
        } else {
            $action = "";
        }
        if(!isset($_GET['action']) and isset($_POST['action'])) {
            $action = trim($_POST['action']);
        }
        if(isset($_GET['news_id'])) {
            $news_id = intval($_GET['news_id']);
        } else {
            $news_id = 0;
        }
        if($news_id == 0 and isset($_POST['news_id']) and is_array($_POST['news_id']) and count($_POST['news_id']) > 0) {
            $news_id_is_arr = 1;
            for($i = 0,$size = count($_POST['news_id']);$i < $size;++$i) {
                if($i == 0) {
                    $news_id = "'".intval($_POST['news_id'][$i])."'";
                } else {
                    $news_id .= ",'".intval($_POST['news_id'][$i])."'";
                }
            }
        } else {
            $news_id_is_arr = 0;
            $news_id = "'".$news_id."'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if(!isset($news_id) or $action == "" or $news_id == "'0'") {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if((isset($_GET['prog']) and intval($_GET['prog']) != 0 and intval($_GET['prog']) == 1) or (isset($_POST['prog']) and intval($_POST['prog']) != 0 and intval($_POST['prog']) == 1)) {
            $temp = "programm";
            $tbl = "_auto";
        } else {
            $temp = "";
            $tbl = "";
        }
        if($action == "on") {
            $sql = "UPDATE ".PREFIX."_news".$tbl."_".$this->registry->sitelang." SET news_status='1' WHERE news_id in (".$news_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("news_update","on");
        } elseif($action == "off") {
            $sql = "UPDATE ".PREFIX."_news".$tbl."_".$this->registry->sitelang." SET news_status='0' WHERE news_id IN (".$news_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("news_update","off");
        } elseif($action == "delete") {
            $sql = "DELETE FROM ".PREFIX."_news".$tbl."_".$this->registry->sitelang." WHERE news_id IN (".$news_id.")";
            $result = $this->db->Execute($sql);
            $this->registry->main_class->assign("news_update","delete");
        } else {
            $this->registry->main_class->database_close();
            header("Location: index.php?path=admin_news&func=index&lang=".$this->registry->sitelang);
            exit();
        }
        if($result) {
            $num_result = $this->db->Affected_Rows();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONNEWSPAGETITLE'));
        if($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|newsstatsqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|newsstatsqlerror|".$this->registry->language);
            exit();
        } elseif($result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|newsstatok|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|newsstatok|".$this->registry->language);
            exit();
        } else {
            if($temp == "programm") {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
                if($news_id_is_arr == 1) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm");
                } else {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm|".intval($_GET['news_id']));
                }
            } else {
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show");
                if($news_id_is_arr == 1) {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread");
                } else {
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit|".intval($_GET['news_id']));
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread|".intval($_GET['news_id']));
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread|".intval($_GET['news_id'])."admin");
                    $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread|".intval($_GET['news_id'])."user");
                }
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news".$temp."|".$action."|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news".$temp."|".$action."|ok|".$this->registry->language);
        }
    }


    public function news_config() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONCONFNEWSPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|config|".$this->registry->language)) {
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_config.html");
            require_once('../modules/news/news_config.inc');
            $this->registry->main_class->assign("systemdk_news_num_news",trim(MODULE_NEWS_NUM_NEWS));
            $this->registry->main_class->assign("systemdk_news_image_path",trim(MODULE_NEWS_IMAGE_PATH));
            $this->registry->main_class->assign("systemdk_news_image_position",trim(MODULE_NEWS_IMAGE_POSITION));
            $this->registry->main_class->assign("systemdk_news_image_max_size",trim(MODULE_NEWS_IMAGE_MAX_SIZE));
            $this->registry->main_class->assign("systemdk_news_image_max_width",trim(MODULE_NEWS_IMAGE_MAX_WIDTH));
            $this->registry->main_class->assign("systemdk_news_image_max_height",trim(MODULE_NEWS_IMAGE_MAX_HEIGHT));
            $this->registry->main_class->assign("systemdk_news_image_width",trim(MODULE_NEWS_IMAGE_WIDTH));
            $this->registry->main_class->assign("systemdk_news_image_width2",trim(MODULE_NEWS_IMAGE_WIDTH2));
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|config|".$this->registry->language);
    }


    public function news_config_save() {
        if(isset($_POST['systemdk_news_num_news'])) {
            $systemdk_news_num_news = intval($_POST['systemdk_news_num_news']);
        }
        if(isset($_POST['systemdk_news_image_path'])) {
            $systemdk_news_image_path = trim($_POST['systemdk_news_image_path']);
        }
        if(isset($_POST['systemdk_news_image_position'])) {
            $systemdk_news_image_position = trim($_POST['systemdk_news_image_position']);
        }
        if(isset($_POST['systemdk_news_image_max_size'])) {
            $systemdk_news_image_max_size = intval($_POST['systemdk_news_image_max_size']);
        }
        if(isset($_POST['systemdk_news_image_max_width'])) {
            $systemdk_news_image_max_width = intval($_POST['systemdk_news_image_max_width']);
        }
        if(isset($_POST['systemdk_news_image_max_height'])) {
            $systemdk_news_image_max_height = intval($_POST['systemdk_news_image_max_height']);
        }
        if(isset($_POST['systemdk_news_image_width'])) {
            $systemdk_news_image_width = intval($_POST['systemdk_news_image_width']);
        }
        if(isset($_POST['systemdk_news_image_width2'])) {
            $systemdk_news_image_width2 = intval($_POST['systemdk_news_image_width2']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONCONFNEWSPAGETITLE'));
        if((!isset($_POST['systemdk_news_num_news']) or !isset($_POST['systemdk_news_image_path']) or !isset($_POST['systemdk_news_image_position']) or !isset($_POST['systemdk_news_image_max_size']) or !isset($_POST['systemdk_news_image_max_width']) or !isset($_POST['systemdk_news_image_max_height']) or !isset($_POST['systemdk_news_image_width']) or !isset($_POST['systemdk_news_image_width2'])) or ($systemdk_news_num_news == 0 or $systemdk_news_image_path == "" or $systemdk_news_image_position == "" or $systemdk_news_image_max_size == 0 or $systemdk_news_image_max_width == 0 or $systemdk_news_image_max_height == 0 or $systemdk_news_image_width == 0 or $systemdk_news_image_width2 == 0)) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|confnotalldata|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|confnotalldata|".$this->registry->language);
            exit();
        }
        $systemdk_news_image_path = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_news_image_path);
        $systemdk_news_image_position = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_news_image_position);
        @chmod("../modules/news/news_config.inc",0777);
        $file = @fopen("../modules/news/news_config.inc","w");
        if(!$file) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|confnofile|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","nofile");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|confnofile|".$this->registry->language);
            exit();
        }
        $content = "<?php\n";
        $content .= 'define("MODULE_NEWS_NUM_NEWS","'.$systemdk_news_num_news."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_PATH","'.$systemdk_news_image_path."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_POSITION","'.$systemdk_news_image_position."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_MAX_SIZE","'.$systemdk_news_image_max_size."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_MAX_WIDTH","'.$systemdk_news_image_max_width."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_MAX_HEIGHT","'.$systemdk_news_image_max_height."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_WIDTH","'.$systemdk_news_image_width."\");\n";
        $content .= 'define("MODULE_NEWS_IMAGE_WIDTH2","'.$systemdk_news_image_width2."\");\n";
        $content .= "?>\n";
        $writefile = @fwrite($file,$content);
        if(!$writefile) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|confnowrite|".$this->registry->language)) {
                $this->registry->main_class->assign("news_update","nowrite");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|error|confnowrite|".$this->registry->language);
            exit();
        }
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|config");
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|configok|".$this->registry->language)) {
            $this->registry->main_class->assign("news_update","writeok");
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/news/news_update.html");
        }
        @fclose($file);
        @chmod("../modules/news/news_config.inc",0604);
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|news|configok|".$this->registry->language);
    }
}

?>
<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class news extends model_base {


    private $num_news;
    private $image_path;
    private $image_position;
    private $image_max_size;
    private $image_max_width;
    private $image_max_height;
    private $image_width;
    private $image_width2;


    public function __construct($registry) {
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
        $this->registry->main_class->modules_news_programm();
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
        if(isset($_GET['cat_id']) and intval($_GET['cat_id']) !== 0) {
            $cat_id = intval($_GET['cat_id']);
            if($cat_id == 0) {
                $cat_id = "no";
                $input = "a.news_onhome='1'";
            } else {
                $input = "a.news_category_id='".$cat_id."'";
            }
        } else {
            $cat_id = "no";
            $input = "a.news_onhome='1'";
        }
        $sql_add = "a.news_valueview = '0'";
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $this->registry->main_class->assign("news_admin","yes");
            $cachuser = "admin";
            $sql_add .= " or a.news_valueview = '1' or a.news_valueview = '3' or a.news_valueview = '2'";
        } elseif($this->registry->main_class->is_user_in_mainfunc()) {
            $this->registry->main_class->assign("news_admin","no");
            $cachuser = 'user';
            $sql_add .= " or a.news_valueview = '1'";
        } else {
            $this->registry->main_class->assign("news_admin","no");
            $cachuser = 'guest';
            $sql_add .= " or a.news_valueview = '3'";
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|news|show|".$cachuser."|".$cat_id."|".$num_page."|".$this->registry->language)) {
            $num_news = $this->num_news;
            $offset = ($num_page - 1) * $num_news;
            $sql = "SELECT a.news_id,a.news_author,a.news_category_id,a.news_title,a.news_date,a.news_images,a.news_short_text,a.news_readcounter,a.news_notes,a.news_valueview,a.news_termexpire,a.news_termexpireaction,a.news_link, b.news_category_name, (SELECT count(a.news_id) FROM ".PREFIX."_news_".$this->registry->sitelang." a WHERE ".$input." and a.news_status='1' and (".$sql_add.")) as count_news_id FROM ".PREFIX."_news_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_news_categories_".$this->registry->sitelang." b on a.news_category_id=b.news_category_id WHERE ".$input." and a.news_status='1' and (".$sql_add.") ORDER BY a.news_date DESC"; //LIMIT $offset, $num_news
            $result = $this->db->SelectLimit($sql,$num_news,$offset);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0 and $num_page > 1) {
                    $this->registry->main_class->database_close();
                    if(intval($cat_id) != 0) {
                        if(SYSTEMDK_MODREWRITE === 'yes') {
                            header("Location: /".SITE_DIR.$this->registry->sitelang."/news/cat/".$cat_id);
                        } else {
                            header("Location: /".SITE_DIR."index.php?path=news&cat_id=".$cat_id."&lang=".$this->registry->sitelang);
                        }
                    } else {
                        if(SYSTEMDK_MODREWRITE === 'yes') {
                            header("Location: /".SITE_DIR.$this->registry->sitelang."/news/");
                        } else {
                            header("Location: /".SITE_DIR."index.php?path=news&lang=".$this->registry->sitelang);
                        }
                    }
                    exit();
                }
            }
            if(isset($numrows) and $numrows > 0) {
                while(!$result->EOF) {
                    $news_id = intval($result->fields['0']);
                    $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $news_category_id = intval($result->fields['2']);
                    if(!isset($news_category_id) or $news_category_id == 0) {
                        $news_category_id = "no";
                    }
                    $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $news_date = intval($result->fields['4']);
                    $news_date = date("d.m.y H:i",$news_date);
                    $news_images = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    if(!isset($news_images) or $news_images == "") {
                        $news_images = "no";
                    }
                    $news_short_text = $this->registry->main_class->extracting_data($result->fields['6']);
                    $news_readcounter = intval($result->fields['7']);
                    $news_notes = $this->registry->main_class->extracting_data($result->fields['8']);
                    if(!isset($news_notes) or $news_notes == "") {
                        $news_notes = "no";
                    }
                    $news_valueview = intval($result->fields['9']);
                    if($news_valueview == "0") {
                        $news_valueview = "all";
                    } elseif($news_valueview == "1") {
                        $news_valueview = "users&admins";
                    } elseif($news_valueview == "2") {
                        $news_valueview = "admins";
                    } elseif($news_valueview == "3") {
                        $news_valueview = "guests";
                    } else {
                        $news_valueview = "no";
                    }
                    $news_termexpire = intval($result->fields['10']);
                    if(isset($news_termexpire) and $news_termexpire !== 0) {
                        $news_termexpire = date("d.m.y H:i",$news_termexpire);
                    } else {
                        $news_termexpire = "no";
                    }
                    $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                    $news_link = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
                    if(!isset($news_link) or $news_link == "") {
                        $news_link = "no";
                    }
                    if($news_category_id > 0) {
                        $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                        if($news_category_name == "") {
                            $news_category_name = "no";
                        }
                    } else {
                        $news_category_name = "no";
                    }
                    if(!isset($numrows3)) {
                        $numrows3 = intval($result->fields['14']);
                    }
                    $news_all[] = array(
                        "news_id" => $news_id,
                        "news_author" => $news_author,
                        "news_category_id" => $news_category_id,
                        "news_title" => $news_title,
                        "news_date" => $news_date,
                        "news_images" => $news_images,
                        "news_short_text" => $news_short_text,
                        "news_readcounter" => $news_readcounter,
                        "news_notes" => $news_notes,
                        "news_valueview" => $news_valueview,
                        "news_termexpire" => $news_termexpire,
                        "news_termexpireaction" => $news_termexpireaction,
                        "news_category_name" => $news_category_name,
                        "news_link" => $news_link
                    );
                    $result->MoveNext();
                }
                $this->registry->main_class->assign("news_all",$news_all);
                $this->registry->main_class->assign("image_position",$this->image_position);
                $this->registry->main_class->assign("image_path",$this->image_path);
                $this->registry->main_class->assign("image_width",$this->image_width);
                $this->registry->main_class->assign("news_category_id",$cat_id);
                if(isset($numrows3)) {
                    $num_pages = @ceil($numrows3 / $num_news);
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
                            $pagelink = 8;
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
                    $this->registry->main_class->assign("totalnews",$numrows3);
                    $this->registry->main_class->assign("num_pages",$num_pages);
                } else {
                    $this->registry->main_class->assign("html","no");
                }
            } else {
                $this->registry->main_class->assign("news_all","no");
                $this->registry->main_class->assign("html","no");
            }
            $this->registry->main_class->assign("page_title","module|news");
            $this->registry->main_class->assign("include_center","modules/news/news.html");
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULENEWSTITLE'));
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|news|show|".$cachuser."|".$cat_id."|".$num_page."|".$this->registry->language);
    }


    public function newsread(){
        if(isset($_GET['news_id'])) {
            $news_id = intval($_GET['news_id']);
        }
        if(!isset($_GET['news_id']) or (isset($_GET['news_id']) and $news_id == 0)) {
            if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|news|error|notselect|".$this->registry->language)) {
                $this->registry->main_class->assign("page_title","module|news");
                $this->registry->main_class->assign("news_all","notselectnews");
                $this->registry->main_class->assign("include_center","modules/news/newsread.html");
                $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULENEWSTITLE'));
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|news|error|notselect|".$this->registry->language);
            exit();
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $sql_add = "a.news_valueview = '0'";
        if($this->registry->main_class->is_admin_in_mainfunc()) {
            $this->registry->main_class->assign("news_admin","yes");
            $cachuser = "admin";
            $sql_add .= " or a.news_valueview = '1' or a.news_valueview = '3' or a.news_valueview = '2'";
        } elseif($this->registry->main_class->is_user_in_mainfunc()) {
            $this->registry->main_class->assign("news_admin","no");
            $cachuser = 'user';
            $sql_add .= " or a.news_valueview = '1'";
        } else {
            $this->registry->main_class->assign("news_admin","no");
            $cachuser = '';
            $sql_add .= " or a.news_valueview = '3'";
        }
        if($num_page == 1) {
            $sql0 = "UPDATE ".PREFIX."_news_".$this->registry->sitelang." SET news_readcounter=news_readcounter+1 WHERE news_id='".$news_id."'";
            $result0 = $this->db->Execute($sql0);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit|".$news_id);
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show|admin");
        }
        if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|news|newsread|".$news_id.$cachuser."|".$num_page."|".$this->registry->language)) {
            $sql = "SELECT a.news_id,a.news_author,a.news_category_id,a.news_title,a.news_date,a.news_images,a.news_short_text,a.news_content,a.news_readcounter,a.news_notes,a.news_valueview,a.news_status,a.news_termexpire,a.news_termexpireaction,a.news_onhome,a.news_link, b.news_category_name FROM ".PREFIX."_news_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_news_categories_".$this->registry->sitelang." b on a.news_category_id=b.news_category_id WHERE a.news_id='".$news_id."' and (".$sql_add.")";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
            }
            if(isset($numrows) and $numrows > 0) {
                $news_id = intval($result->fields['0']);
                $news_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $news_category_id = intval($result->fields['2']);
                if(!isset($news_category_id) or $news_category_id == "" or $news_category_id == 0) {
                    $news_category_id = "no";
                }
                $news_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                $news_date = intval($result->fields['4']);
                $news_date = date("d.m.y H:i",$news_date);
                $news_images = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                if(!isset($news_images) or $news_images == "") {
                    $news_images = "no";
                }
                $news_short_text = $this->registry->main_class->extracting_data($result->fields['6']);
                $news_content = $this->registry->main_class->extracting_data($result->fields['7']);
                $news_readcounter = intval($result->fields['8']);
                $news_notes = $this->registry->main_class->extracting_data($result->fields['9']);
                if(!isset($news_notes) or $news_notes == "") {
                    $news_notes = "no";
                }
                $news_status = intval($result->fields['11']);
                if($news_status == 0) {
                    if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|news|error|notactive|".$this->registry->language)) {
                        $this->registry->main_class->assign("page_title","module|news");
                        $this->registry->main_class->assign("news_all","notactivnews");
                        $this->registry->main_class->assign("include_center","modules/news/newsread.html");
                        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULENEWSTITLE'));
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|news|error|notactive|".$this->registry->language);
                    exit();
                }
                $news_content = str_replace("<span style=\"display: none;\">&nbsp;</span></div>","",$news_content);
                $news_content = explode("<div style=\"page-break-after: always;\">",$news_content);
                $count_num_pages = count($news_content);
                if($num_page > $count_num_pages) {
                    $num_page = 1;
                }
                $news_content = $news_content[$num_page - 1];
                $news_content = $this->registry->main_class->search_player_entry($news_content);
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
                $news_valueview = intval($result->fields['10']);
                if($news_valueview == "0") {
                    $news_valueview = "all";
                } elseif($news_valueview == "1") {
                    $news_valueview = "users&admins";
                } elseif($news_valueview == "2") {
                    $news_valueview = "admins";
                } elseif($news_valueview == "3") {
                    $news_valueview = "guests";
                } else {
                    $news_valueview = "no";
                }
                $news_termexpire = intval($result->fields['12']);
                if(isset($news_termexpire) and $news_termexpire !== 0) {
                    $news_termexpire = date("d.m.y H:i",$news_termexpire);
                } else {
                    $news_termexpire = "no";
                }
                $news_termexpireaction = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['13']));
                $news_onhome = intval($result->fields['14']);
                $news_link = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                if(!isset($news_link) or $news_link == "") {
                    $news_link = "no";
                }
                if($news_category_id > 0) {
                    $news_category_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['16']));
                    if($news_category_name == "") {
                        $news_category_name = "no";
                    }
                } else {
                    $news_category_name = "no";
                }
                $news_all['1'] = array(
                    "news_id" => $news_id,
                    "news_author" => $news_author,
                    "news_category_id" => $news_category_id,
                    "news_title" => $news_title,
                    "news_date" => $news_date,
                    "news_images" => $news_images,
                    "news_short_text" => $news_short_text,
                    "news_content" => $news_content,
                    "news_readcounter" => $news_readcounter,
                    "news_notes" => $news_notes,
                    "news_valueview" => $news_valueview,
                    "news_termexpire" => $news_termexpire,
                    "news_termexpireaction" => $news_termexpireaction,
                    "news_category_name" => $news_category_name,
                    "news_onhome" => $news_onhome,
                    "news_link" => $news_link
                );
                $this->registry->main_class->assign("news_all",$news_all);
                $this->registry->main_class->assign("image_position",$this->image_position);
                $this->registry->main_class->assign("image_path",$this->image_path);
                $this->registry->main_class->assign("image_width2",$this->image_width2);
                $this->registry->main_class->assign("page_title","module|news");
                $this->registry->main_class->assign("include_center","modules/news/newsread.html");
                $this->registry->main_class->set_sitemeta($news_title);
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|news|newsread|".$news_id.$cachuser."|".$num_page."|".$this->registry->language);
            } else {
                if(!$this->registry->main_class->isCached("index.html",$this->registry->sitelang."|modules|news|error|notfind|".$this->registry->language)) {
                    $this->registry->main_class->assign("page_title","module|news");
                    $this->registry->main_class->assign("news_all","notfindnews");
                    $this->registry->main_class->assign("include_center","modules/news/newsread.html");
                    $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MODULENEWSTITLE'));
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|news|error|notfind|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|modules|news|newsread|".$news_id.$cachuser."|".$num_page."|".$this->registry->language);
        }
    }
}

?>
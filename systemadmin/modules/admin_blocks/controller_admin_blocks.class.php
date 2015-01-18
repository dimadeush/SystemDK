<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_blocks.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_admin_blocks extends controller_base {


    private $model_admin_blocks;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_blocks = singleton::getinstance('admin_blocks',$registry);
    }


    private function blocks_view($type,$cache_category = false,$template = false,$title) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|blocks|error';
        }
        if(empty($template)) {
            $template = 'blockupdate.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'blockupdate.html') {
                $this->assign("block_save",$type);
            } elseif($template === 'blockaddend.html') {
                $this->assign("blockadd",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/blocks/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        $type = 'show';
        $cache_category = 'modules|blocks';
        $title = '_MENUBLOCKS';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_blocks->index();
            $error = $this->model_admin_blocks->get_property_value('error');
            $error_array = $this->model_admin_blocks->get_property_value('error_array');
            $result = $this->model_admin_blocks->get_property_value('result');
            if($error === 'show_sql_error') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->blocks_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'blocks.html';
        $this->blocks_view($type,$cache_category,$template,$title);
    }


    public function blockorder() {
        $data_array = false;
        if(isset($_GET['block_newpriority'])) {
            $data_array['block_newpriority'] = intval($_GET['block_newpriority']);
        }
        if(isset($_GET['block_priority'])) {
            $data_array['block_priority'] = intval($_GET['block_priority']);
        }
        if(isset($_GET['block_nextid'])) {
            $data_array['block_nextid'] = intval($_GET['block_nextid']);
        }
        if(isset($_GET['block_id'])) {
            $data_array['block_id'] = intval($_GET['block_id']);
        }
        $title = '_MENUBLOCKS';
        $template = false;
        $this->model_admin_blocks->blockorder($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'empty_data' or $error === 'show_sql_error' or $error === 'order_ok') {
            if($error === 'empty_data') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|blocks|order';
        $this->blocks_view($result,$cache_category,$template,$title);
    }


    public function select_block_type() {
        $type = 'select_type';
        $title = '_ADMINISTRATIONADDBLOCKPAGETITLE';
        $cache_category = 'modules|blocks';
        $template = 'blockadd.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_blocks->select_block_type();
            $result = $this->model_admin_blocks->get_property_value('result');
            $this->assign_array($result);
        }
        $this->blocks_view($type,$cache_category,$template,$title);
    }


    public function add_block() {
        $block_type = false;
        if(isset($_POST['block_type'])) {
            $block_type = trim($_POST['block_type']);
        }
        $title = '_ADMINISTRATIONADDBLOCKPAGETITLE';
        $this->model_admin_blocks->add_block($block_type);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'add_not_all_data' or $error === 'add_sql_error' or $error === 'add_no_file' or $error == 'unknown_block_type') {
            if($error === 'unknown_block_type') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            if($error === 'add_not_all_data' or $error === 'add_no_file') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $template = 'blockadd.html';
        $cache_category = 'modules|blocks|add';
        $this->blocks_view($result['block_type'],$cache_category,$template,$title);
    }


    public function add_block_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'block_type',
                    'block_custom_name',
                    'block_file',
                    'block_arrangements',
                    'block_termexpireaction',
                    'block_content',
                    'block_url',
                    'rss_url'
                );
                $keys2 = array(
                    'block_status',
                    'block_termexpire',
                    'block_valueview',
                    'block_refresh',
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONADDBLOCKPAGETITLE';
        $this->model_admin_blocks->add_block_inbase($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'add_not_all_data' or $error === 'add_found' or $error === 'add_sql_error' or $error === 'error_url' or $error === 'unknown_block_type') {
            if($error === 'unknown_block_type') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            if($error === 'add_not_all_data' or $error === 'add_found' or $error === 'error_url') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $template = 'blockaddend.html';
        $cache_category = 'modules|blocks|add';
        $this->blocks_view($result,$cache_category,$template,$title);
    }


    public function block_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['action'] = trim($_GET['action']);
        }
        if(isset($_GET['block_id'])) {
            $data_array['block_id'] = intval($_GET['block_id']);
        }
        $title = '_MENUBLOCKS';
        $cache_category = false;
        $template = false;
        $this->model_admin_blocks->block_status($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'not_all_data' or $error === 'unknown_action' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'not_all_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|blocks|status';
        $this->blocks_view($result,$cache_category,$template,$title);
    }


    public function block_delete() {
        $block_id = false;
        if(isset($_GET['block_id'])) {
            $block_id = intval($_GET['block_id']);
        }
        $title = '_MENUBLOCKS';
        $cache_category = false;
        $template = false;
        $this->model_admin_blocks->block_delete($block_id);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'not_all_data' or $error === 'status_not_found' or $error === 'status_sql_error') {
            if($error === 'not_all_data') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            if($error === 'status_not_found') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|blocks|status';
        $this->blocks_view($result,$cache_category,$template,$title);
    }


    public function block_edit() {
        $data_array = false;
        if(isset($_GET['block_type'])) {
            $data_array['block_type'] = trim($_GET['block_type']);
        }
        if(isset($_GET['block_id'])) {
            $data_array['block_id'] = intval($_GET['block_id']);
        }
        $title = '_ADMINISTRATIONEDITBLOCKPAGETITLE';
        $this->model_admin_blocks->block_edit($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'not_all_data' or $error === 'edit_sql_error' or $error === 'unknown_block_type' or $error === 'edit_not_found') {
            if($error === 'not_all_data' or $error === 'unknown_block_type') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            if($error === 'edit_not_found') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $cache_category = 'modules|blocks|edit|'.$result['block_type'];
        $template = 'blockedit.html';
        $this->blocks_view($data_array['block_id'],$cache_category,$template,$title);
    }


    public function block_edit_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'block_type',
                    'block_custom_name',
                    'block_file',
                    'block_arrangements',
                    'block_termexpireaction',
                    'block_content',
                    'block_url',
                    'rss_url'
                );
                $keys2 = array(
                    'block_id',
                    'block_status',
                    'block_termexpire',
                    'block_valueview',
                    'block_refresh',
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONEDITBLOCKPAGETITLE';
        $template = false;
        $this->model_admin_blocks->block_edit_save($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'edit_not_all_data' or $error === 'edit_found' or $error === 'edit_block_rss_url' or $error === 'unknown_block_type' or $error === 'edit_sql_error' or $error === 'edit_ok') {
            if($error === 'unknown_block_type') {
                header("Location: index.php?path=admin_blocks&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            if($error === 'edit_not_all_data' or $error === 'edit_found' or $error === 'edit_block_rss_url') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $type = 'edit_done';
        $cache_category = 'modules|blocks|edit';
        $this->blocks_view($type,$cache_category,$template,$title);
    }


    public function block_rsssite() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $title = '_ADMINISTRATIONRSSBLOCKPAGETITLE';
        $type = $num_page;
        $cache_category = 'modules|blocks|rss';
        $template = 'blockaddrsssite.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$num_page."|".$this->registry->language)) {
            $this->model_admin_blocks->block_rsssite($num_page);
            $error = $this->model_admin_blocks->get_property_value('error');
            $error_array = $this->model_admin_blocks->get_property_value('error_array');
            $result = $this->model_admin_blocks->get_property_value('result');
            if($error == 'rss_sql_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_blocks&func=block_rsssite&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->blocks_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $this->blocks_view($type,$cache_category,$template,$title);
    }


    public function block_addrsssite() {
        $title = '_ADMINISTRATIONADDRSSBLOCKPAGETITLE';
        $type = 'addrss';
        $cache_category = 'modules|blocks';
        $template = 'blockaddrsssite.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$this->registry->language)) {
            $this->model_admin_blocks->block_addrsssite();
            $result = $this->model_admin_blocks->get_property_value('result');
            $this->assign_array($result);
        }
        $this->blocks_view($type,$cache_category,$template,$title);
    }


    public function block_addrsssite_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'rss_sitename',
                    'rss_siteurl'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        $title = '_ADMINISTRATIONADDRSSBLOCKPAGETITLE';
        $this->model_admin_blocks->block_addrsssite_inbase($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'add_rss_not_all_data' or $error === 'add_rss_sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            if($error === 'add_rss_not_all_data') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|blocks';
        $template = 'blockaddrsssite.html';
        $this->assign_array($result);
        $this->blocks_view($result['addrss_inbase'],$cache_category,$template,$title);
    }


    public function block_editrsssite() {
        $rss_id = 0;
        if(isset($_GET['rss_id'])) {
            $rss_id = intval($_GET['rss_id']);
        }
        $title = '_ADMINISTRATIONEDITRSSBLOCKPAGETITLE';
        $cache_category = 'modules|blocks|editrss';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$rss_id."|".$this->registry->language)) {
            $this->model_admin_blocks->block_editrsssite($rss_id);
            $error = $this->model_admin_blocks->get_property_value('error');
            $error_array = $this->model_admin_blocks->get_property_value('error_array');
            $result = $this->model_admin_blocks->get_property_value('result');
            if($error === 'empty_rss_id' or $error === 'edit_rss_sql_error' or $error === 'edit_rss_not_found') {
                if($error === 'empty_rss_id') {
                    header("Location: index.php?path=admin_blocks&func=block_rsssite&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                if($error === 'edit_rss_not_found') {
                    $template = 'blockaddrsssite.html';
                    $this->assign("addrss_inbase","edit_rss_not_found");
                }
                $this->blocks_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'blockaddrsssite.html';
        $this->blocks_view($rss_id,$cache_category,$template,$title);
    }


    public function block_editrsssite_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'rss_sitename',
                    'rss_siteurl'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if(isset($_POST['rss_id'])) {
            $data_array['rss_id'] = intval($_POST['rss_id']);
        }
        $title = '_ADMINISTRATIONEDITRSSBLOCKPAGETITLE';
        $this->model_admin_blocks->block_editrsssite_inbase($data_array);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'edit_rss_not_all_data' or $error === 'edit_rss_sql_error' or $error === 'edit_rss_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            if($error === 'edit_rss_not_all_data') {
                $template = 'blockaddend.html';
            }
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $template = 'blockaddrsssite.html';
        $cache_category = 'modules|blocks';
        $this->blocks_view($result['addrss_inbase'],$cache_category,$template,$title);
    }


    public function block_deletersssite() {
        $rss_id = 0;
        if(isset($_GET['rss_id'])) {
            $rss_id = intval($_GET['rss_id']);
        }
        $title = '_ADMINISTRATIONDELETERSSBLOCKPAGETITLE';
        $this->model_admin_blocks->block_deletersssite($rss_id);
        $error = $this->model_admin_blocks->get_property_value('error');
        $error_array = $this->model_admin_blocks->get_property_value('error_array');
        $result = $this->model_admin_blocks->get_property_value('result');
        if($error === 'empty_rss_id' or $error === 'del_rss_sql_error' or $error === 'del_rss_ok') {
            if($error == 'empty_rss_id') {
                header("Location: index.php?path=admin_blocks&func=block_rsssite&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->blocks_view($error,$cache_category,$template,$title);
        }
        $this->assign_array($result);
        $template = 'blockaddrsssite.html';
        $cache_category = 'modules|blocks';
        $this->blocks_view($result['addrss_inbase'],$cache_category,$template,$title);
    }
}
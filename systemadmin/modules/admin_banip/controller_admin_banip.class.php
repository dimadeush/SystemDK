<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_banip.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_admin_banip extends controller_base {


    private $model_admin_banip;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_banip = singleton::getinstance('admin_banip',$registry);
    }


    private function banip_view($type,$cache_category = false) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|banip|error';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_MENUBANIP'));
            $this->assign("ban_save",$type);
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/banip/banip.html");
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        $type = 'show';
        $cache_category = 'modules|banip';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_banip->index();
            $error = $this->model_admin_banip->get_property_value('error');
            $result = $this->model_admin_banip->get_property_value('result');
            if($error === 'no_file') {
                $this->banip_view($error);
            }
            $this->assign_array($result);
        }
        $this->banip_view($type,$cache_category);
    }


    public function banip_save() {
        $post_array = false;
        if(isset($_POST['ban_ip_add'])) {
            $post_array['ban_ip_add'] = trim($_POST['ban_ip_add']);
        }
        if(isset($_POST['ban_ip_del'])) {
            $post_array['ban_ip_del'] = trim($_POST['ban_ip_del']);
        }
        $this->model_admin_banip->banip_save($post_array);
        $error = $this->model_admin_banip->get_property_value('error');
        $result = $this->model_admin_banip->get_property_value('result');
        if($error === 'not_all_data' or $error === 'no_file' or $error === 'not_write') {
            $this->banip_view($error);
        }
        $cache_category = 'modules|banip|save';
        $this->banip_view($result,$cache_category);
    }
}
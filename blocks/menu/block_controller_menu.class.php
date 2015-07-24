<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      block_controller_menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class block_controller_menu extends controller_base {


    private $model_block_menu;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_block_menu = singleton::getinstance('block_menu',$registry);
    }


    public function index() {
        $block_content_array = $this->model_block_menu->index();
        $this->assign_array($block_content_array);
        return $block_content = $this->fetch("blocks/menu/menu.html");
    }
}
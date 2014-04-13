<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      block_controller_verticalmenu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class block_controller_verticalmenu extends controller_base {


    private $model_block_verticalmenu;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_block_verticalmenu = singleton::getinstance('block_verticalmenu',$registry);
    }


    public function index() {
        $block_content_array = $this->model_block_verticalmenu->index();
        $this->assign_array($block_content_array);
        return $block_content = $this->fetch("blocks/verticalmenu/verticalmenu.html");
    }
}
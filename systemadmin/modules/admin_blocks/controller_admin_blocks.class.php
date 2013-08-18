<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_blocks.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_blocks extends controller_base {


    private $model_admin_blocks;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_blocks = new admin_blocks($registry);
    }


    public function index() {
        $this->model_admin_blocks->index();
    }


    public function blockorder() {
        $this->model_admin_blocks->blockorder();
    }


    public function select_block_type() {
        $this->model_admin_blocks->select_block_type();
    }


    public function add_block() {
        $this->model_admin_blocks->add_block();
    }


    public function add_block_inbase() {
        $this->model_admin_blocks->add_block_inbase();
    }


    public function block_status() {
        $this->model_admin_blocks->block_status();
    }


    public function block_delete() {
        $this->model_admin_blocks->block_delete();
    }


    public function block_edit() {
        $this->model_admin_blocks->block_edit();
    }


    public function block_edit_save() {
        $this->model_admin_blocks->block_edit_save();
    }


    public function block_rsssite() {
        $this->model_admin_blocks->block_rsssite();
    }


    public function block_addrsssite() {
        $this->model_admin_blocks->block_addrsssite();
    }


    public function block_addrsssite_inbase() {
        $this->model_admin_blocks->block_addrsssite_inbase();
    }


    public function block_editrsssite() {
        $this->model_admin_blocks->block_editrsssite();
    }


    public function block_deletersssite() {
        $this->model_admin_blocks->block_deletersssite();
    }


    public function block_editrsssite_inbase() {
        $this->model_admin_blocks->block_editrsssite_inbase();
    }
}

?>
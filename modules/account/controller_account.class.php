<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_account extends controller_base {


    private $model_account;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_account = new account($registry);
    }


    public function process_autoload() {
        $this->model_account->process_autoload();
        $this->process_action();
    }


    private function process_action() {
        if($this->model_account->is_usernow() === 1 and $this->registry->router->get_action() !== 'index') {
            if(in_array($this->registry->router->get_action(),array(
                                                                   "account_register",
                                                                   "account_add_save",
                                                                   "account_add_confirm",
                                                                   "account_lostpass",
                                                                   "account_lostpassconf",
                                                                   "account_newpassconf"
                                                              ))
            ) {
                $this->registry->router->set_action('account_only_for_unreg');
            } elseif(!in_array($this->registry->router->get_action(),array(
                                                                          "mainuser",
                                                                          "account_edit",
                                                                          "account_edit_save",
                                                                          "account_logout"
                                                                     ))
            ) {
                $this->registry->router->set_action('mainuser');
            }
        } elseif($this->registry->router->get_action() !== 'index') {
            if(!in_array($this->registry->router->get_action(),array(
                                                                    "account_logout",
                                                                    "account_register",
                                                                    "account_add_save",
                                                                    "account_add_confirm",
                                                                    "account_lostpass",
                                                                    "account_lostpassconf",
                                                                    "account_newpassconf"
                                                               ))
            ) {
                $this->registry->router->set_action('account_enter');
            }
        }
    }


    public function index() {
        if($this->model_account->is_usernow() === 1) {
            $this->mainuser();
        } else {
            $this->account_enter();
        }
    }


    public function account_enter() {
        $this->model_account->account_enter();
    }


    public function account_register() {
        $this->model_account->account_register();
    }


    public function account_add_save() {
        $this->model_account->account_add_save();
    }


    public function account_add_confirm() {
        $this->model_account->account_add_confirm();
    }


    public function account_lostpass() {
        $this->model_account->account_lostpass();
    }


    public function account_lostpassconf() {
        $this->model_account->account_lostpassconf();
    }


    public function account_newpassconf() {
        $this->model_account->account_newpassconf();
    }


    public function mainuser() {
        $this->model_account->mainuser();
    }


    public function account_edit() {
        $this->model_account->account_edit();
    }


    public function account_edit_save() {
        $this->model_account->account_edit_save();
    }


    public function account_logout() {
        $this->model_account->account_logout();
    }


    public function account_only_for_unreg() {
        $this->model_account->account_only_for_unreg();
    }
}

?>
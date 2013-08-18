<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      router.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class router {


    //Registry member
    private $registry;
    //The path to the controller
    private $path;
    // private $args = array();
    private $file; //public
    private $controller; //public
    private $action; //public
    public function __construct($registry) {
        $this->registry = $registry;
    }


    /*Set the controller directory path
      @param string $path The control file path
      @return void*/
    public function setpath($path) {
        //Make sure that the path to the file exists
        if(is_dir($path) == false) {
            throw new Exception ('Invalid controller path: `'.$path.'`');
        }
        //Set the path member
        $this->path = $path;
    }


    public function get_action() {
        return $this->action;
    }


    public function set_action($action) {
        $this->action = $action;
    }


    /*Load the controller.
     @access public
     @return void */
    public function loader() {
        //Set the controller route
        $this->getcontroller();
        if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
            if($this->registry->main_class->systemdk_is_admin() === 1) {
                if(is_readable($this->file)) {
                    $this->load_controller();
                } else {
                    $this->registry->main_class->load_controller_error($this->file,true);
                }
            } else {
                $this->registry->main_class->mainenter();
            }
        } else {
            $this->registry->main_class->header();
            $this->registry->main_class->footer();
            if($this->controller === 'index') {
                $this->registry->main_class->loadhome('ok');
            } else {
                $result = $this->registry->main_class->path($this->controller);
                if($result) {
                    if(isset($result->fields['4'])) {
                        $row_exist = intval($result->fields['4']);
                    } else {
                        $row_exist = 0;
                    }
                }
                if($result and isset($row_exist) and $row_exist > 0) {
                    $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['0']));
                    $module_status = intval($result->fields['1']);
                    $module_valueview = intval($result->fields['2']);
                    $page_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    if($page_title != "") {
                        $this->registry->main_class->assign("page_title",$page_title);
                    } else {
                        $this->registry->main_class->assign("page_title","no");
                    }
                    if($module_status == 1) {
                        if($module_valueview == "0") {
                            if(is_readable($this->file)) {
                                $this->load_controller();
                            } else {
                                $this->registry->main_class->load_controller_error('nomodulefile');
                            }
                        } elseif($module_valueview == "1") {
                            if($this->registry->main_class->is_user_in_mainfunc() or $this->registry->main_class->is_admin_in_mainfunc()) {
                                if(is_readable($this->file)) {
                                    $this->load_controller();
                                } else {
                                    $this->registry->main_class->load_controller_error('nomodulefile');
                                }
                            } else {
                                $this->registry->main_class->load_controller_error('accessonlyusers');
                            }
                        } elseif($module_valueview == "2") {
                            if($this->registry->main_class->is_admin_in_mainfunc()) {
                                if(is_readable($this->file)) {
                                    $this->load_controller();
                                } else {
                                    $this->registry->main_class->load_controller_error('nomodulefile');
                                }
                            } else {
                                $this->registry->main_class->load_controller_error('accessonlyadmins');
                            }
                        } elseif($module_valueview == "3") {
                            if(!$this->registry->main_class->is_user_in_mainfunc() or $this->registry->main_class->is_admin_in_mainfunc()) {
                                if(is_readable($this->file)) {
                                    $this->load_controller();
                                } else {
                                    $this->registry->main_class->load_controller_error('nomodulefile');
                                }
                            } else {
                                $this->registry->main_class->load_controller_error('accessonlyguests');
                            }
                        }
                    } else {
                        $this->registry->main_class->load_controller_error('modulenotactive');
                    }
                } else {
                    $this->registry->main_class->load_controller_error('modulenotfind');
                }
            }
        }
    }


    private function load_controller() {
        //Include the controller
        include_once $this->file;
        // Set the controller class name, assuming a file name suffix of 'Controller'
        $className = 'controller_'.$this->controller;
        if(class_exists($className)) {
            //Instantiate the controller class
            $this->registry->$className = new $className($this->registry);
        } elseif(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
            $this->registry->main_class->load_controller_error($className,true);
        } else {
            $this->registry->main_class->load_controller_error('nomoduleclass');
        }
        //$this->registry->$className = $controller;
        //check if process_autoload method is available for calling
        if(is_callable(array($this->registry->$className,'process_autoload')) == true) {
            $this->registry->$className->process_autoload();
        }
        //Check if the action is available for calling
        if(is_callable(array(
                            $this->registry->$className,
                            $this->action
                       )) == false and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes'
        ) {
            $this->registry->main_class->load_controller_error($className,true,$this->action);
        } elseif(is_callable(array($this->registry->$className,$this->action)) == false) {
            $action = 'index';
        } else {
            $action = $this->action;
        }
        //Run the action
        $this->registry->$className->$action();
    }


    /*Set the controller member from URL
      @access private
      @return void */
    private function getcontroller() {
        //Get the route from the url
        /*if(defined('SYSTEMDK_MODREWRITE') and SYSTEMDK_MODREWRITE === "yes") {
            $request_uri = $_SERVER['REQUEST_URI'];
            $request_uri = str_ireplace(SITE_DIR,'',$request_uri);
            $route = explode('/',$request_uri);
            if(!empty($route[1]) and !defined('__SITE_ADMIN_PART')) {
                $this->controller = trim($route[1]);
            } elseif(!empty($route[2]) and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->controller = trim($route[2]);
            }
            if(!empty($route[2]) and !defined('__SITE_ADMIN_PART')) {
                $this->action = trim($route[2]);
            } elseif(!empty($route[3]) and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->action = trim($route[3]);
            } elseif(!empty($_POST['func'])) {
                $this->action = trim($_POST['func']);
            }
        } else {*/
            if(!empty($_GET['path'])) {
                $this->controller = trim($_GET['path']);
            } elseif(!empty($_POST['path'])) {
                $this->controller = trim($_POST['path']);
            }
            if(!empty($_GET['func'])) {
                $this->action = trim($_GET['func']);
            } elseif(!empty($_POST['func'])) {
                $this->action = trim($_POST['func']);
            }
        //}
        if(!empty($this->controller)) {
            $this->controller = $this->registry->main_class->format_striptags($this->controller);
            $this->controller = $this->registry->main_class->processing_data($this->controller);
            $this->controller = substr(substr($this->controller,0,-1),1);
        }
        if(!empty($this->action)) {
            $this->action = $this->registry->main_class->format_striptags($this->action);
        }
        //Default to the index if the controller is empty
        if(empty($this->controller) and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
            $this->controller = 'admin_administrator';
        } elseif(empty($this->controller)) {
            $this->registry->home_page = 1;
            if(defined('HOME_MODULE') and $this->registry->main_class->format_striptags(trim(HOME_MODULE)) != "" and $this->registry->main_class->module_is_active($this->registry->main_class->format_striptags(trim(HOME_MODULE)))) {
                $this->controller = $this->registry->main_class->format_striptags(trim(HOME_MODULE));
            } else {
                $this->controller = 'index';
            }
        }
        //If the action does not exist, use the default action
        if(empty($this->action)) {
            $this->action = 'index';
        }
        //Set the control file path
        if($this->controller === 'index') {
            $this->file = false;
        } else {
            $this->file = $this->path.'/'.$this->controller.'/'.'controller_'.$this->controller.'.class.php';
        }
    }
}

?>
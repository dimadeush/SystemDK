<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      router.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class router
{


    /**
     * @var registry
     */
    private $registry;
    private $path;
    private $file;
    private $controller;
    private $action;


    public function __construct($registry)
    {
        $this->registry = $registry;
    }


    // Set the controller directory path
    public function setpath($path)
    {
        if (is_dir($path) == false) {
            throw new Exception ('Invalid controller path: `' . $path . '`');
        }
        $this->path = $path;
    }


    public function get_action()
    {
        return $this->action;
    }


    public function set_action($action)
    {
        $this->action = $action;
    }


    // Load the controller method
    public function loader()
    {
        // Set the controller route
        $this->getcontroller();
        if (defined('__SITE_ADMIN_PART') && __SITE_ADMIN_PART === 'yes') {
            if ($this->registry->main_class->is_admin()) {
                if (is_readable($this->file)) {
                    $this->load_controller();
                } else {
                    $this->registry->main_class->load_controller_error($this->file, true);
                }
            } else {
                $controller_admin_administrator = singleton::getinstance('controller_admin_administrator', $this->registry);
                $controller_admin_administrator->mainenter();
            }
        } else {
            $this->registry->main_class->header();
            $this->registry->main_class->footer();
            if ($this->controller === 'index') {
                $this->registry->main_class->load_home('ok');
            } else {
                $result = $this->registry->main_class->path($this->controller);
                if ($result) {
                    if (isset($result->fields['4'])) {
                        $row_exist = intval($result->fields['4']);
                    } else {
                        $row_exist = 0;
                    }
                }
                if ($result && isset($row_exist) && $row_exist > 0) {
                    $module_customname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['0']));
                    $module_status = intval($result->fields['1']);
                    $module_valueview = intval($result->fields['2']);
                    $page_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    if ($page_title != "") {
                        $this->registry->main_class->set_page_title($page_title);
                    } else {
                        $this->registry->main_class->set_page_title('no');
                    }
                    if ($module_status == 1) {
                        if ($module_valueview == "0") {
                            if (is_readable($this->file)) {
                                $this->load_controller();
                            } else {
                                $this->registry->main_class->load_controller_error('nomodulefile');
                            }
                        } elseif ($module_valueview == "1") {
                            if ($this->registry->main_class->is_user() || $this->registry->main_class->is_admin()) {
                                if (is_readable($this->file)) {
                                    $this->load_controller();
                                } else {
                                    $this->registry->main_class->load_controller_error('nomodulefile');
                                }
                            } else {
                                $this->registry->main_class->load_controller_error('accessonlyusers');
                            }
                        } elseif ($module_valueview == "2") {
                            if ($this->registry->main_class->is_admin()) {
                                if (is_readable($this->file)) {
                                    $this->load_controller();
                                } else {
                                    $this->registry->main_class->load_controller_error('nomodulefile');
                                }
                            } else {
                                $this->registry->main_class->load_controller_error('accessonlyadmins');
                            }
                        } elseif ($module_valueview == "3") {
                            if (!$this->registry->main_class->is_user() || $this->registry->main_class->is_admin()) {
                                if (is_readable($this->file)) {
                                    $this->load_controller();
                                } else {
                                    $this->registry->main_class->load_controller_error('nomodulefile');
                                }
                            } else {
                                $this->registry->main_class->load_controller_error('accessonlyguests');
                            }
                        } else {
                            $this->registry->main_class->load_controller_error('nomodulefile');
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


    private function load_controller()
    {
        // Include the controller
        include_once $this->file;
        // Set the controller class name, assuming a file name suffix of 'Controller'
        $className = 'controller_' . $this->controller;
        if (class_exists($className)) {
            // Instantiate the controller class
            $this->registry->$className = singleton::getinstance($className, $this->registry);//new $className($this->registry);
        } elseif (defined('__SITE_ADMIN_PART') && __SITE_ADMIN_PART === 'yes') {
            $this->registry->main_class->load_controller_error($className, true);
        } else {
            $this->registry->main_class->load_controller_error('nomoduleclass');
        }
        // check if process_autoload method is available for calling
        if (is_callable([$this->registry->$className, 'process_autoload']) == true) {
            $this->registry->$className->process_autoload();
        }
        // Check if the action is available for calling
        if (is_callable(
                [
                    $this->registry->$className,
                    $this->action,
                ]
            ) == false
            && defined('__SITE_ADMIN_PART')
            && __SITE_ADMIN_PART === 'yes'
        ) {
            $this->registry->main_class->load_controller_error($className, true, $this->action);
        } elseif (is_callable([$this->registry->$className, $this->action]) == false) {
            $action = 'index';
        } else {
            $action = $this->action;
        }
        // Run the action
        $this->registry->$className->$action();
    }


    // Set the controller and action from URL
    private function getcontroller()
    {
        if (!empty($_GET['path'])) {
            $this->controller = trim($_GET['path']);
        } elseif (!empty($_POST['path'])) {
            $this->controller = trim($_POST['path']);
        }
        if (!empty($_GET['func'])) {
            $this->action = trim($_GET['func']);
        } elseif (!empty($_POST['func'])) {
            $this->action = trim($_POST['func']);
        }
        if (!empty($this->controller)) {
            $this->controller = $this->registry->main_class->format_striptags($this->controller);
            $this->controller = $this->registry->main_class->process_file_name($this->controller);
            $this->controller = $this->registry->main_class->processing_data($this->controller);
            $this->controller = substr(substr($this->controller, 0, -1), 1);
        }
        if (!empty($this->action)) {
            $this->action = $this->registry->main_class->format_striptags($this->action);
            $this->action = $this->registry->main_class->process_file_name($this->action);
        }
        // Default to the index if the controller is empty
        if (empty($this->controller) && defined('__SITE_ADMIN_PART') && __SITE_ADMIN_PART === 'yes') {
            $this->controller = 'admin_administrator';
        } elseif (empty($this->controller)) {
            $this->registry->home_page = 1;
            if (defined('HOME_MODULE') && $this->registry->main_class->format_striptags(trim(HOME_MODULE)) != ""
                && $this->registry->main_class->module_is_active(
                    $this->registry->main_class->format_striptags(trim(HOME_MODULE))
                )
            ) {
                $this->controller = $this->registry->main_class->format_striptags(trim(HOME_MODULE));
                $this->controller = $this->registry->main_class->process_file_name($this->controller);
            } else {
                $this->controller = 'index';
            }
        }
        // If the action does not exist, use the default action
        if (empty($this->action)) {
            $this->action = 'index';
        }
        // Set the control file path
        if ($this->controller === 'index') {
            $this->file = false;
        } else {
            $this->file = $this->path . '/' . $this->controller . '/' . 'controller_' . $this->controller . '.class.php';
        }
    }
}
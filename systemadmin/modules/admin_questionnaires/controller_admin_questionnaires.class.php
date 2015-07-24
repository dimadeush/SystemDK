<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_questionnaires.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class controller_admin_questionnaires extends controller_base {


    private $model_admin_questionnaires;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_questionnaires = singleton::getinstance('admin_questionnaires',$registry);
    }


    private function questionnaires_view($type,$title,$cache_category = false,$template = false,$cache = false) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|questionnaires|error';
        }
        if(empty($template)) {
            $template = 'questionnaires_messages.html';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->configLoad($this->registry->language."/systemadmin/modules/questionnaires/questionnaires.conf");
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'questionnaires_messages.html') {
                $this->assign("questionnaires_message",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/questionnaires/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function index() {
        $data_array['num_page'] = 1;
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        }
        $title = '_ADMINISTRATIONQUESTIONNAIRESPAGETITLE';
        $cache_category = 'modules|questionnaires|show';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->index($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if($error === 'unknown_page' or $error === 'sql_error') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_questionnaires&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->questionnaires_view($error,$title,$cache_category,$template,'show_'.$error);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaires_show.html';
        $this->questionnaires_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function questionnaire_add() {
        $type = 'add_questionnaire';
        $title = '_QUESTIONNAIRES_ADDTITLE';
        $cache_category = 'modules|questionnaires';
        $template = 'questionnaire_add.html';
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function questionnaire_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            $keys = array(
                'questionnaire_name',
                'questionnaire_meta_title',
                'questionnaire_meta_keywords',
                'questionnaire_meta_description'
            );
            $keys2 = array(
                'questionnaire_status',
            );
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_QUESTIONNAIRES_ADDTITLE';
        $this->model_admin_questionnaires->questionnaire_add_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'add_questionnaire_'.$error);
        }
        $cache_category = 'modules|questionnaires';
        $this->questionnaires_view($result,$title,$cache_category);
    }


    public function questionnaire_edit() {
        $data_array['questionnaire_id'] = 0;
        if(isset($_GET['questionnaire_id'])) {
            $data_array['questionnaire_id'] = intval($_GET['questionnaire_id']);
        }
        $title = '_QUESTIONNAIRES_EDIT_TITLE';
        $cache_category = 'modules|questionnaires|edit_questionnaire';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['questionnaire_id']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->questionnaire_edit($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if($error === 'questionnaire_empty_data' or $error === 'sql_error' or $error === 'questionnaire_not_found') {
                if($error === 'questionnaire_empty_data') {
                    header("Location: index.php?path=admin_questionnaires&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = 'questionnaire_edit_'.$error;
                $this->questionnaires_view($error,$title,$cache_category,$template,$cache);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaire_edit.html';
        $this->questionnaires_view($data_array['questionnaire_id'],$title,$cache_category,$template);
    }


    public function questionnaire_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            $keys = array(
                'questionnaire_name',
                'questionnaire_meta_title',
                'questionnaire_meta_keywords',
                'questionnaire_meta_description'
            );
            $keys2 = array(
                'questionnaire_id',
                'questionnaire_status',
                'questionnaire_date'
            );
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_QUESTIONNAIRES_EDIT_TITLE';
        $this->model_admin_questionnaires->questionnaire_edit_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error' or $error === 'ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'edit_questionnaire_'.$error);
        }
        $cache_category = 'modules|questionnaires';
        $this->questionnaires_view($result,$title,$cache_category);
    }


    public function questionnaires_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if(isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if(isset($_GET['questionnaire_id'])) {
            $data_array['get_questionnaire_id'] = intval($_GET['questionnaire_id']);
        }
        if(isset($_POST['questionnaire_id'])) {
            $data_array['post_questionnaire_id'] = $_POST['questionnaire_id'];
        }
        $title = '_ADMINISTRATIONQUESTIONNAIRESPAGETITLE';
        $this->model_admin_questionnaires->questionnaires_status($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if($error === 'empty_data' or $error === 'unknown_action' or $error === 'sql_error' or $error === 'ok') {
            if($error === 'empty_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_questionnaires&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'status_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|status';
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function groups() {
        $data_array['num_page'] = 1;
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        }
        $title = '_QUESTIONNAIRES_GROUPS';
        $cache_category = 'modules|questionnaires|groups';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->groups($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if($error === 'unknown_page' or $error === 'sql_error') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_questionnaires&func=groups&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->questionnaires_view($error,$title,$cache_category,$template,'groups_'.$error);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaires_groups.html';
        $this->questionnaires_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function group_add() {
        $type = 'group_add';
        $title = '_QUESTIONNAIRES_ADD_GROUP_TITLE';
        $cache_category = 'modules|questionnaires';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_questionnaires->group_add();
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if($error === 'no_questionnaires' or $error === 'sql_error') {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = $error;
                $this->questionnaires_view($error,$title,$cache_category,$template,'add_group_'.$cache);
            }
            $this->assign_array($result);
        }
        $template = 'group_add.html';
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function group_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            $keys = array(
                'group_name',
            );
            $keys2 = array(
                'group_status',
                'questionnaire_id',
            );
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_QUESTIONNAIRES_ADD_GROUP_TITLE';
        $this->model_admin_questionnaires->group_add_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error' or $error === 'already_assigned') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'add_group_'.$error);
        }
        $cache_category = 'modules|questionnaires';
        $this->questionnaires_view($result,$title,$cache_category);
    }


    public function group_edit() {
        $data_array['group_id'] = 0;
        if(isset($_GET['group_id'])) {
            $data_array['group_id'] = intval($_GET['group_id']);
        }
        $title = '_QUESTIONNAIRES_GROUP_EDIT_TITLE';
        $cache_category = 'modules|questionnaires|edit_group';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['group_id']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->group_edit($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if($error === 'empty_data' or $error === 'sql_error' or $error === 'group_edit_not_found' or $error === 'no_questionnaires') {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_questionnaires&func=groups&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = $error;
                if($error != 'group_edit_not_found') {
                    $cache = 'group_edit_'.$error;
                }
                $this->questionnaires_view($error,$title,$cache_category,$template,$cache);
            }
            $this->assign_array($result);
        }
        $template = 'group_edit.html';
        $this->questionnaires_view($data_array['group_id'],$title,$cache_category,$template);
    }


    public function group_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            $keys = array(
                'group_name',
            );
            $keys2 = array(
                'group_id',
                'questionnaire_id',
                'group_status',
            );
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_QUESTIONNAIRES_GROUP_EDIT_TITLE';
        $this->model_admin_questionnaires->group_edit_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if($error === 'not_all_data' or $error === 'sql_error' or $error === 'already_assigned' or $error === 'ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'group_edit_'.$error);
        }
        $cache_category = 'modules|questionnaires';
        $this->questionnaires_view($result,$title,$cache_category);
    }


    public function groups_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if(isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if(isset($_GET['group_id'])) {
            $data_array['get_group_id'] = intval($_GET['group_id']);
        }
        if(isset($_POST['group_id'])) {
            $data_array['post_group_id'] = $_POST['group_id'];
        }
        $title = '_QUESTIONNAIRES_GROUPS';
        $this->model_admin_questionnaires->groups_status($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if($error === 'empty_data' or $error === 'already_assigned' or $error === 'unknown_action' or $error === 'sql_error' or $error === 'ok') {
            if($error === 'empty_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_questionnaires&func=groups&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'groups_status_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|groups_status';
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function questions() {
        $type = 'questions';
        $data_array['questionnaire_id'] = 0;
        if(isset($_GET['questionnaire_id'])) {
            $data_array['questionnaire_id'] = intval($_GET['questionnaire_id']);
        }
        $title = '_QUESTIONNAIRES_QUESTIONS_LIST_TITLE';
        $cache_category = 'modules|questionnaires|questionnaire|'.$data_array['questionnaire_id'];
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_questionnaires->questions($data_array['questionnaire_id']);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(in_array($error,array('empty_data','sql_error','questionnaire_not_found','no_questions'))) {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_questionnaires&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = $error;
                $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$cache);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaires_questions.html';
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function question_add() {
        $type = 'question_add';
        $data_array['questionnaire_id'] = 0;
        if(isset($_GET['questionnaire_id'])) {
            $data_array['questionnaire_id'] = intval($_GET['questionnaire_id']);
        }
        $title = '_QUESTIONNAIRES_ADD_QUESTION_TITLE';
        $cache_category = 'modules|questionnaires|questionnaire|'.$data_array['questionnaire_id'];
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_questionnaires->question_add($data_array['questionnaire_id']);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(in_array($error,array(
                'empty_questionnaire',
                'sql_error',
                'questionnaire_not_found',
                'no_question_types'
            ))) {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = $error;
                $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$cache);
            }
            $this->assign_array($result);
        }
        $template = 'question_add_or_edit.html';
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function question_item_add() {
        $type = 'question_item_add';
        $title = '_QUESTIONNAIRES_ADD_QUESTION_ITEM_TITLE';
        $this->process_questionnaire_and_question($type,$title);
    }


    public function question_answer_add() {
        $type = 'question_answer_add';
        $title = '_QUESTIONNAIRES_ADD_QUESTION_ANSWER_TITLE';
        $this->process_questionnaire_and_question($type,$title);
    }


    private function process_questionnaire_and_question($type,$title) {
        $data_array['questionnaire_action'] = $type;
        $data_array['questionnaire_id'] = 0;
        $data_array['question_id'] = 0;
        if(!empty($_GET)) {
            $keys = array(
                'questionnaire_id',
                'question_id'
            );
            foreach($_GET as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if($type == 'question_edit') {
            $cache_category = 'modules|questionnaires|questionnaire|'.$data_array['questionnaire_id'].'|'.$type;
            $template = 'question_add_or_edit.html';
            $type = $data_array['question_id'];
        } else {
            $cache_category = 'modules|questionnaires|questionnaire|'.$data_array['questionnaire_id'].'|question|'.$data_array['question_id'];
            $template = 'question_item_or_answer_add_or_edit.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_questionnaires->process_questionnaire_and_question($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(in_array($error,array(
                'empty_questionnaire',
                'empty_question',
                'sql_error',
                'questionnaire_not_found',
                'question_not_found'
            ))) {
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = $error;
                $this->questionnaires_view($error,$title,$cache_category,$template,$data_array['questionnaire_action'].'_'.$cache);
            }
            $this->assign_array($result);
        }
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function question_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            $keys = array(
                'title',
                'question_subtext'
            );
            $keys2 = array(
                'questionnaire_id',
                'question_type',
                'question_obligatory'
            );
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_QUESTIONNAIRES_ADD_QUESTION_TITLE';
        $this->model_admin_questionnaires->question_add_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if(in_array($error,array('not_all_data','sql_error'))) {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,'question_add_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|question_add|'.$result['questionnaire_id'];
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function question_item_add_inbase() {
        $type = 'question_item_add';
        $title = '_QUESTIONNAIRES_ADD_QUESTION_ITEM_TITLE';
        $this->item_or_answer_add_inbase($type,$title);
    }


    public function question_answer_add_inbase() {
        $type = 'question_answer_add';
        $title = '_QUESTIONNAIRES_ADD_QUESTION_ANSWER_TITLE';
        $this->item_or_answer_add_inbase($type,$title);
    }


    private function item_or_answer_add_inbase($type,$title) {
        $data_array['questionnaire_add_type'] = $type;
        if(!empty($_POST)) {
            $keys = array(
                'title'
            );
            $keys2 = array(
                'questionnaire_id',
                'question_id'
            );
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $this->model_admin_questionnaires->item_or_answer_add_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if(in_array($error,array('not_all_data','sql_error'))) {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|'.$data_array['questionnaire_add_type'].'|'.$result['questionnaire_id'];
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function question_edit() {
        $type = 'question_edit';
        $title = '_QUESTIONNAIRES_EDIT_QUESTION_TITLE';
        $this->process_questionnaire_and_question($type,$title);
    }


    public function question_item_edit() {
        $type = 'question_item_edit';
        $title = '_QUESTIONNAIRES_QUESTION_ITEM_EDIT_TITLE';
        $this->get_question_item_or_answer($type,$title);
    }


    public function question_answer_edit() {
        $type = 'question_answer_edit';
        $title = '_QUESTIONNAIRES_QUESTION_ANSWER_EDIT_TITLE';
        $this->get_question_item_or_answer($type,$title);
    }


    private function get_question_item_or_answer($type,$title) {
        $item = 'question_answer_id';
        if($type == 'question_item_edit') {
            $item = 'question_item_id';
        }
        $data_array['questionnaire_action'] = $type;
        $data_array['questionnaire_id'] = 0;
        $data_array['question_id'] = 0;
        $data_array[$item] = 0;
        if(!empty($_GET)) {
            $keys = array(
                'questionnaire_id',
                'question_id',
                $item
            );
            foreach($_GET as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $cache_category = 'modules|questionnaires|questionnaire|'.$data_array['questionnaire_id'].'|question|'.$data_array['question_id'].'|'.$type;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array[$item]."|".$this->registry->language)) {
            $this->model_admin_questionnaires->get_question_item_or_answer($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(!empty($error)) {
                if($error === 'unknown_questionnaire_action') {
                    header("Location: index.php?path=admin_questionnaires&func=questions&questionnaire_id=".$data_array['questionnaire_id']."&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $cache = $error;
                $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$cache);
            }
            $this->assign_array($result);
        }
        $template = 'question_item_or_answer_add_or_edit.html';
        $this->questionnaires_view($data_array[$item],$title,$cache_category,$template);
    }


    public function question_item_edit_inbase() {
        $type = 'question_item_edit';
        $title = '_QUESTIONNAIRES_QUESTION_ITEM_EDIT_TITLE';
        $this->question_or_item_or_answer_edit_inbase($type,$title);
    }


    public function question_answer_edit_inbase() {
        $type = 'question_answer_edit';
        $title = '_QUESTIONNAIRES_QUESTION_ANSWER_EDIT_TITLE';
        $this->question_or_item_or_answer_edit_inbase($type,$title);
    }


    private function question_or_item_or_answer_edit_inbase($type,$title) {
        $item = 'question_item_id';
        if($type == 'question_answer_edit') {
            $item = 'question_answer_id';
        } elseif($type == 'question_edit') {
            $item = false;
        }
        $data_array['questionnaire_action'] = $type;
        if(!empty($_POST)) {
            $keys = array(
                'title'
            );
            $keys2 = array(
                'questionnaire_id',
                'question_id',
                $item
            );
            if($type == 'question_edit') {
                $keys = array_merge($keys,array('question_subtext'));
                $keys2 = array_merge($keys2,array('question_type','question_obligatory'));
            }
            foreach($_POST as $key => $value) {
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $this->model_admin_questionnaires->question_or_item_or_answer_edit_inbase($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if(!empty($error)) {
            if($error === 'unknown_questionnaire_action') {
                header("Location: index.php?path=admin_questionnaires&func=questions&questionnaire_id=".$data_array['questionnaire_id']."&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|'.$data_array['questionnaire_action'].'|'.$result['questionnaire_id'];
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function question_edit_inbase() {
        $type = 'question_edit';
        $title = '_QUESTIONNAIRES_EDIT_QUESTION_TITLE';
        $this->question_or_item_or_answer_edit_inbase($type,$title);
    }


    public function question_delete() {
        $data_array['type'] = 'question_delete';
        $title = '_ADMINISTRATIONQUESTIONNAIRESPAGETITLE';
        $this->question_or_item_or_answer_delete($data_array,$title);
    }


    public function question_item_delete() {
        $data_array['type'] = 'question_item_delete';
        $title = '_ADMINISTRATIONQUESTIONNAIRESPAGETITLE';
        $this->question_or_item_or_answer_delete($data_array,$title);
    }


    public function question_answer_delete() {
        $data_array['type'] = 'question_answer_delete';
        $title = '_ADMINISTRATIONQUESTIONNAIRESPAGETITLE';
        $this->question_or_item_or_answer_delete($data_array,$title);
    }


    private function question_or_item_or_answer_delete($data_array,$title) {
        $keys = array(
            'action'
        );
        $keys2 = array(
            'questionnaire_id',
            'question_id',
            'question_item_id',
            'question_answer_id'
        );
        $source_array = array($_GET,$_POST);
        foreach($source_array as $source) {
            if(!empty($source)) {
                foreach($source as $key => $value) {
                    if(in_array($key,$keys)) {
                        $data_array[$key] = trim($value);
                    }
                    if(in_array($key,$keys2)) {
                        $data_array[$key] = intval($value);
                    }
                }
            }
        }
        $this->model_admin_questionnaires->question_or_item_or_answer_delete($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if(!empty($error)) {
            if($error === 'empty_data') {
                header("Location: index.php?path=admin_questionnaires&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,$data_array['type'].'_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|'.$data_array['type'].'|'.$result['questionnaire_id'];
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function results() {
        $data_array['num_page'] = 1;
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        }
        $title = '_QUESTIONNAIRES_RESULTS_TITLE';
        $cache_category = 'modules|questionnaires|results';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->results($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(!empty($error)) {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_questionnaires&func=results&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->questionnaires_view($error,$title,$cache_category,$template,'results_'.$error);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaires_results.html';
        $this->questionnaires_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function results_details() {
        $type = 'results_details';
        $keys = array('group_id','questionnaire_id');
        foreach($keys as $key) {
            $data_array[$key] = 0;
            if(!empty($_GET[$key])) {
                $data_array[$key] = intval($_GET[$key]);
            }
        }
        $title = '_QUESTIONNAIRES_QUESTIONNAIRE_RESULTS_TITLE';
        $cache_category = 'modules|questionnaires|'.$type.'|questionnaire|'.$data_array['questionnaire_id'].'|group';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['group_id']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->results_details($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(!empty($error)) {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_questionnaires&func=results&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$error);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaires_results_details.html';
        $this->questionnaires_view($data_array['group_id'],$title,$cache_category,$template);
    }


    public function results_person_text_answers() {
        $type = 'results_person_text_answers';
        $keys = array('group_id','questionnaire_id','question_id','person_id','num_page');
        foreach($keys as $key) {
            $data_array[$key] = 0;
            if(!empty($_GET[$key])) {
                $data_array[$key] = intval($_GET[$key]);
            }
        }
        if(empty($data_array['num_page']) or $data_array['num_page'] < 1) {
            $data_array['num_page'] = 1;
        }
        $title = '_QUESTIONNAIRES_QUESTIONNAIRE_RESULTS_TITLE';
        $cache_category = 'modules|questionnaires|results_details|questionnaire|'.$data_array['questionnaire_id'].'|group|'.$data_array['group_id'].'|'.$type.'|'.$data_array['question_id'];
        if($data_array['person_id'] > 0) {
            $cache_category = 'modules|questionnaires|results_details|questionnaire|'.$data_array['questionnaire_id'].'|group|'.$data_array['group_id'].'|person|'.$data_array['person_id'].'|'.$type.'|'.$data_array['question_id'];
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_questionnaires->results_person_text_answers($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(!empty($error)) {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_questionnaires&func=results_details&group_id=".$data_array['group_id']."&questionnaire_id=".$data_array['questionnaire_id']."&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$error);
            }
            $this->assign_array($result);
        }
        $template = 'results_person_text_answers.html';
        $this->questionnaires_view($data_array['num_page'],$title,$cache_category,$template);
    }


    public function results_details_by_persons() {
        $type = 'results_by_persons';
        $data_array[$type] = true;
        $keys = array('group_id','questionnaire_id');
        foreach($keys as $key) {
            $data_array[$key] = 0;
            if(!empty($_GET[$key])) {
                $data_array[$key] = intval($_GET[$key]);
            }
        }
        $title = '_QUESTIONNAIRES_QUESTIONNAIRE_RESULTS_BY_PERSONS_TITLE';
        $cache_category = 'modules|questionnaires|results_details|questionnaire|'.$data_array['questionnaire_id'].'|group|'.$data_array['group_id'];
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_questionnaires->results_details($data_array);
            $error = $this->model_admin_questionnaires->get_property_value('error');
            $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
            $result = $this->model_admin_questionnaires->get_property_value('result');
            if(!empty($error)) {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_questionnaires&func=results&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$error);
            }
            $this->assign_array($result);
        }
        $template = 'questionnaires_results_details_by_persons.html';
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function results_delete() {
        $data_array['type'] = 'results_delete';
        $title = '_QUESTIONNAIRES_RESULTS_TITLE';
        $keys = array(
            'action'
        );
        $keys2 = array(
            'voted_group_id'
        );
        $source_array = array('get' => $_GET,'post' => $_POST);
        foreach($source_array as $source_key => $source_value) {
            if(!empty($source_value)) {
                foreach($source_value as $key => $value) {
                    if(in_array($key,$keys)) {
                        $data_array[$source_key.'_'.$key] = trim($value);
                    }
                    if(in_array($key,$keys2)) {
                        $data_array[$source_key.'_'.$key] = $source_key === 'post' ? $value : intval($value);
                    }
                }
            }
        }
        $this->model_admin_questionnaires->results_delete($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $error_array = $this->model_admin_questionnaires->get_property_value('error_array');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if(!empty($error)) {
            if($error === 'empty_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_questionnaires&func=results&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,$data_array['type'].'_'.$error);
        }
        $this->assign_array($result);
        $cache_category = 'modules|questionnaires|'.$data_array['type'];
        $this->questionnaires_view($result['questionnaires_message'],$title,$cache_category);
    }


    public function config() {
        $type = 'config';
        $title = '_QUESTIONNAIRES_CONFIGURATION_TITLE';
        $cache_category = 'modules|questionnaires';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_questionnaires->config();
            $result = $this->model_admin_questionnaires->get_property_value('result');
            $this->assign_array($result);
        }
        $template = 'questionnaires_config.html';
        $this->questionnaires_view($type,$title,$cache_category,$template);
    }


    public function config_save() {
        $type = 'config_save';
        $title = '_QUESTIONNAIRES_CONFIGURATION_TITLE';
        $data_array = false;
        $keys = array(
            'systemdk_questionnaires_organization_id' => 'string',
            'systemdk_questionnaires_organization_name' => 'string',
            'systemdk_questionnaires_voted_method_check' => 'string',
            'systemdk_questionnaires_voted_control_live_time' => 'integer',
            'systemdk_questionnaires_who_can_vote' => 'string'
        );
        foreach($keys as $key => $value) {
            if(!empty($_POST[$key])) {
                $data_array[$key] = $value === 'string' ? trim($_POST[$key]) : intval($_POST[$key]);
            }
        }
        $this->model_admin_questionnaires->config_save($data_array);
        $error = $this->model_admin_questionnaires->get_property_value('error');
        $result = $this->model_admin_questionnaires->get_property_value('result');
        if(!empty($error)) {
            $cache_category = false;
            $template = false;
            $this->questionnaires_view($error,$title,$cache_category,$template,$type.'_'.$error);
        }
        $cache_category = 'modules|questionnaires';
        $this->questionnaires_view($result,$title,$cache_category);
    }
}
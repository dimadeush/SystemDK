<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class controller_feedback extends controller_base {


    private $model_feedback;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_feedback = singleton::getinstance('feedback',$registry);
    }


    private function feedback_view($type,$cache_category = false,$cache = false,$template = false) {
        if(empty($cache_category)) {
            $cache_category = 'modules|feedback|error';
        }
        if(empty($template)) {
            $template = 'feedback_messages.html';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        $this->configLoad($this->registry->language."/modules/feedback/feedback.conf");
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_FEEDBACKMODULETITLE'));
            if($template === 'feedback_messages.html') {
                $this->assign("feedback_message",$type);
            }
            $this->assign("include_center","modules/feedback/".$template);
        } elseif($template != 'feedback_messages.html') {
            $this->assign("include_center","modules/feedback/".$template);
        }
        $this->display("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function index() {
        $type = false;
        $cache_category = 'modules|feedback';
        $cache = 'display';
        $template = 'feedback.html';
        $get_receivers = false;
        if(defined('SYSTEMDK_FEEDBACK_RECEIVER') and SYSTEMDK_FEEDBACK_RECEIVER != 'default') {
            if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
                $get_receivers = true;
            }
        }
        $this->model_feedback->index($get_receivers);
        $result = $this->model_feedback->get_property_value('result');
        $this->assign_array($result);
        $this->feedback_view($type,$cache_category,$cache,$template);
    }


    public function feedback_send() {
        $post_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'feedback_user_name',
                    'feedback_user_email',
                    'feedback_subject',
                    'feedback_message',
                    'captcha'
                );
                $keys2 = array(
                    'feedback_user_id',
                    'feedback_towhom'
                );
                if(in_array($key,$keys)) {
                    $post_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $post_array[$key] = intval($value);
                }
            }
        }
        $post_array['feedback_admin_name'] = $this->getConfigVars('_FEEDBACKADMINNAME');
        $this->model_feedback->feedback_send($post_array);
        $error = $this->model_feedback->get_property_value('error');
        if(empty($error)) {
            $this->configLoad($this->registry->language."/modules/feedback/feedback.conf");
            $result = $this->model_feedback->get_property_value('result');
            $this->assign_array($result);
            $feedback_content = $this->fetch("modules/feedback/feedback_mail.html");
            $process_check = false;
            $this->model_feedback->feedback_send($post_array,$process_check,$feedback_content);
            $error = $this->model_feedback->get_property_value('error');
            $error_array = $this->model_feedback->get_property_value('error_array');
            $result = $this->model_feedback->get_property_value('result');
        }
        if($error === 'not_all_data' or $error === 'check_code' or $error === 'mail_limit' or $error === 'user_email' or $error === 'find_such_user' or $error === 'message_length' or $error === 'not_insert') {
            if($error === 'not_insert') {
                $this->assign("errortext",$error_array);
            }
            $this->feedback_view($error);
        }
        $cache_category = 'modules|feedback|sent';
        $this->feedback_view($result,$cache_category);
    }
}
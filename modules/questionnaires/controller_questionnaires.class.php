<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_questionnaires.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_questionnaires extends controller_base
{


    /**
     * @var questionnaires
     */
    private $model_questionnaires;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_questionnaires = singleton::getinstance('questionnaires', $registry);
    }


    private function questionnaires_view($type, $titles, $cache_category = false, $cache = false, $template = false)
    {
        if (empty($cache_category)) {
            $cache_category = 'modules|questionnaires|error';
        }
        if (empty($template)) {
            $template = 'questionnaires_messages.html';
        }
        if (empty($cache)) {
            $cache = $type;
        }
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->configLoad($this->registry->language . "/modules/questionnaires/questionnaires.conf");
            if (!empty($titles)) {
                $this->registry->main_class->set_sitemeta(
                    $this->getConfigVars($titles['title']), $this->getConfigVars($titles['description']), $this->getConfigVars($titles['keywords'])
                );
            }
            if ($template === 'questionnaires_messages.html' && !empty($type)) {
                $this->assign("questionnaires_message", $type);
            }
            $this->assign("include_center", "modules/questionnaires/" . $template);
        }
        $this->display("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language);
        exit();
    }


    public function index()
    {
        $cache_category = 'modules|questionnaires';
        $cache = 'questionnaires_list';
        $titles['title'] = '_QUESTIONNAIRES_MODULE_METATITLE';
        $titles['description'] = '_QUESTIONNAIRES_MODULE_METADESCRIPTION';
        $titles['keywords'] = '_QUESTIONNAIRES_MODULE_METAKEYWORDS';
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_questionnaires->index();
            $error = $this->model_questionnaires->get_property_value('error');
            $result = $this->model_questionnaires->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'questionnaires_error') {
                $this->questionnaires_view($error, $titles);
            }
        }
        $type = false;
        $template = 'questionnaires_list.html';
        $this->questionnaires_view($type, $titles, $cache_category, $cache, $template);
    }


    public function view()
    {
        $data_array['questionnaire_id'] = 0;
        if (isset($_GET['questionnaire_id'])) {
            $data_array['questionnaire_id'] = intval($_GET['questionnaire_id']);
        }
        $cache_category = 'modules|questionnaires|view';
        $cache = $data_array['questionnaire_id'];
        $titles = false;
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_questionnaires->view($data_array['questionnaire_id']);
            $error = $this->model_questionnaires->get_property_value('error');
            $result = $this->model_questionnaires->get_property_value('result');
            $this->assign_array($result);
            if (!empty($error)) {
                $cache_category = false;
                if ($error != 'no_questions') {
                    $cache = false;
                    $titles['title'] = '_QUESTIONNAIRES_MODULE_METATITLE';
                    $titles['description'] = '_QUESTIONNAIRES_MODULE_METADESCRIPTION';
                    $titles['keywords'] = '_QUESTIONNAIRES_MODULE_METAKEYWORDS';
                } else {
                    $cache = $cache . '|' . $error;
                }
                $this->questionnaires_view($error, $titles, $cache_category, $cache);
            }
        }
        $type = false;
        $template = 'questionnaire_view.html';
        $this->questionnaires_view($type, $titles, $cache_category, $cache, $template);
    }


    public function vote()
    {
        $data_array['questionnaire_id'] = 0;
        if (isset($_POST['questionnaire_id'])) {
            $data_array['questionnaire_id'] = intval($_POST['questionnaire_id']);
        }
        $data_array['group_id'] = 0;
        if (isset($_POST['group_id'])) {
            $data_array['group_id'] = intval($_POST['group_id']);
        }
        $this->model_questionnaires->vote($data_array);
        $error = $this->model_questionnaires->get_property_value('error');
        $error_array = $this->model_questionnaires->get_property_value('error_array');
        $result = $this->model_questionnaires->get_property_value('result');
        $this->assign_array($result);
        $type = false;
        $titles = false;
        $cache_category = 'modules|questionnaires|vote';
        $cache = $data_array['questionnaire_id'];
        if (!empty($error)) {
            if ($error === 'sql_error') {
                $this->assign("errortext", $error_array);
            }
            $cache_category = false;
            if (in_array(
                $error, [
                    'no_questionnaire_data',
                    'already_voted',
                    'not_user',
                    'questionnaires_error',
                    'not_found_questionnaire',
                    'group_not_assign',
                ]
            )) {
                $cache = false;
                $titles['title'] = '_QUESTIONNAIRES_MODULE_METATITLE';
                $titles['description'] = '_QUESTIONNAIRES_MODULE_METADESCRIPTION';
                $titles['keywords'] = '_QUESTIONNAIRES_MODULE_METAKEYWORDS';
            } else {
                $cache = $cache . '|' . $error;
            }
            $this->questionnaires_view($error, $titles, $cache_category, $cache);
        }
        $this->questionnaires_view($type, $titles, $cache_category, $cache);
    }
}
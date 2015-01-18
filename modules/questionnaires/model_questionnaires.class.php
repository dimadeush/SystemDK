<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_questionnaires.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class questionnaires extends model_base {


    private $error;
    private $error_array;
    private $result;
    private $systemdk_questionnaires_voted_method_check;
    private $systemdk_questionnaires_voted_control_live_time;
    private $systemdk_questionnaires_who_can_vote;


    public function __construct($registry) {
        parent::__construct($registry);
        require_once('questionnaires_config.inc');
        $this->systemdk_questionnaires_voted_method_check = SYSTEMDK_QUESTIONNAIRES_VOTED_METHOD_CHECK;
        $this->systemdk_questionnaires_voted_control_live_time = SYSTEMDK_QUESTIONNAIRES_VOTED_CONTROL_LIVE_TIME;
        $this->systemdk_questionnaires_who_can_vote = SYSTEMDK_QUESTIONNAIRES_WHO_CAN_VOTE;
    }


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    private function set_organization_info() {
        $this->result['organization_id'] = SYSTEMDK_QUESTIONNAIRES_ORGANIZATION_ID;
        $this->result['organization_name'] = SYSTEMDK_QUESTIONNAIRES_ORGANIZATION_NAME;
    }


    public function index() {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->set_organization_info();
        $sql = "SELECT questionnaire_id,questionnaire_name,questionnaire_date FROM ".PREFIX."_q_questionnaires_".$this->registry->sitelang." WHERE questionnaire_status = '1' ORDER BY questionnaire_date ASC";
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'questionnaires_error';
            return;
        }
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if($row_exist > 0) {
            while(!$result->EOF) {
                $questionnaire_id = intval($result->fields['0']);
                $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $questionnaire_date = intval($result->fields['2']);
                $questionnaire_date = date("d.m.y",$questionnaire_date);
                $questionnaires_all[] = array(
                    'questionnaire_id' => $questionnaire_id,
                    'questionnaire_name' => $questionnaire_name,
                    'questionnaire_date' => $questionnaire_date,
                );
                $result->MoveNext();
            }
            $this->result['questionnaires_all'] = $questionnaires_all;
        } else {
            $this->result['questionnaires_all'] = "no_questionnaires";
        }
    }


    public function view($questionnaire_id) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->set_organization_info();
        $questionnaire_id = intval($questionnaire_id);
        if($questionnaire_id == 0) {
            $this->error = 'questionnaire_not_selected';
            return;
        }
        $results = $this->get_questions($questionnaire_id);
        if($results) {
            $results['systemdk_questionnaires_who_can_vote'] = $this->systemdk_questionnaires_who_can_vote;
            $this->result = array_merge($results,$this->result);
        }
    }


    private function get_questions($questionnaire_id) {
        $sql = "SELECT a.questionnaire_id,a.questionnaire_name,a.questionnaire_date,a.questionnaire_meta_title,a.questionnaire_meta_keywords,a.questionnaire_meta_description,b.group_id,b.group_name,c.question_id,c.question_title,c.type_id,c.question_priority,c.question_subtext,c.question_obligatory,d.question_answer_id,d.question_answer,d.question_answer_priority,e.question_item_id,e.question_item_title,e.question_item_priority
                FROM ".PREFIX."_q_questionnaires_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_q_groups_".$this->registry->sitelang." b on (a.questionnaire_id=b.questionnaire_id and b.group_status = '1') LEFT OUTER JOIN ".PREFIX."_q_questions_".$this->registry->sitelang." c on a.questionnaire_id=c.questionnaire_id LEFT OUTER JOIN ".PREFIX."_q_question_answers_".$this->registry->sitelang." d on c.question_id=d.question_id LEFT OUTER JOIN ".PREFIX."_q_question_items_".$this->registry->sitelang." e on c.question_id=e.question_id
                WHERE a.questionnaire_id = ".$questionnaire_id." and a.questionnaire_status = '1' ORDER BY c.question_priority,d.question_answer_priority,e.question_item_priority ASC";
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error = 'questionnaires_error';
            return false;
        }
        $row_exist = 0;
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        }
        if($row_exist < 1) {
            $this->error = 'not_found_questionnaire';
            return false;
        }
        $group_id = intval($result->fields['6']);
        if($group_id < 1) {
            $this->error = 'group_not_assign';
            return false;
        }
        $results['questionnaire_id'] = intval($result->fields['0']);
        $results['questionnaire_name'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $questionnaire_date = intval($result->fields['2']);
        $results['questionnaire_date'] = date("d.m.y",$questionnaire_date);
        $questionnaire_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        $questionnaire_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
        $questionnaire_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $this->registry->main_class->set_sitemeta($questionnaire_meta_title,$questionnaire_meta_keywords,$questionnaire_meta_description);
        $results['group_id'] = intval($result->fields['6']);
        $results['group_name'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
        $question_exist = intval($result->fields['8']);
        if($question_exist < 1) {
            $this->error = 'no_questions';
            return false;
        }
        while(!$result->EOF) {
            $question_id = intval($result->fields['8']);
            $question_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
            $type_id = intval($result->fields['10']);
            $question_priority = intval($result->fields['11']);
            $question_subtext = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['12']));
            $question_obligatory = intval($result->fields['13']);
            if(!isset($results['questions'][$question_id])) {
                $results['questions'][$question_id] = array(
                    'question_id' => $question_id,
                    'question_title' => $question_title,
                    'type_id' => $type_id,
                    'question_priority' => $question_priority,
                    'question_subtext' => $question_subtext,
                    'question_obligatory' => $question_obligatory
                );
            }
            $question_answer_id = intval($result->fields['14']);
            if($question_answer_id < 1) {
                if(!isset($results['questions'][$question_id]['question_answers'])) {
                    $results['questions'][$question_id]['question_answers'] = 'no';
                }
            } else {
                $question_answer = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['15']));
                $question_answer_priority = intval($result->fields['16']);
                $results['questions'][$question_id]['question_answers'][$question_answer_id] = array(
                    'question_answer_id' => $question_answer_id,
                    'question_answer' => $question_answer,
                    'question_answer_priority' => $question_answer_priority
                );
            }
            $question_item_id = intval($result->fields['17']);
            if($question_item_id < 1) {
                if(!isset($results['questions'][$question_id]['question_items'])) {
                    $results['questions'][$question_id]['question_items'] = 'no';
                }
            } else {
                $question_item_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['18']));
                $question_item_priority = intval($result->fields['19']);
                $results['questions'][$question_id]['question_items'][$question_item_id] = array(
                    'question_item_id' => $question_item_id,
                    'question_item_title' => $question_item_title,
                    'question_item_priority' => $question_item_priority
                );
            }
            $result->MoveNext();
        }
        return $results;
    }


    public function vote($array) {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $this->set_organization_info();
        if(isset($array['questionnaire_id'])) {
            $array['questionnaire_id'] = intval($array['questionnaire_id']);
        }
        if(isset($array['group_id'])) {
            $array['group_id'] = intval($array['group_id']);
        }
        if(empty($array['questionnaire_id']) or empty($array['group_id'])) {
            $this->error = 'no_questionnaire_data';
            return;
        }
        if(!$this->check_if_vote_allow($array['group_id'],$array['questionnaire_id'])) {
            return;
        }
        $results = $this->get_questions($array['questionnaire_id']);
        if(!$results) {
            return;
        }
        if($results['group_id'] != $array['group_id']) {
            $this->error = 'incorrect_group_id';
            return;
        }
        foreach($results['questions'] as $key => $value) {
            $question_id = $value['question_id'];
            $type_id = $value['type_id'];
            $question_obligatory = $value['question_obligatory'];
            $question_items = $value['question_items'];
            $question_answers = $value['question_answers'];
            $post_question_id = 'question_'.$question_id;
            $post_question_text = 'question_text_'.$question_id;
            $post_question_answer = 'question_answer_'.$question_id;
            $answer_text = false;
            if(in_array($type_id,array(1,2,3,4,5,6))) {
                if(in_array($type_id,array(1,3,4,6)) and is_array($question_items)) {
                    $question_item_id = false;
                    $answer_id = false;
                    foreach($question_items as $key2 => $value2) {
                        if(isset($_POST[$post_question_id][$key2])) {
                            $question_item_id = intval($_POST[$post_question_id][$key2]);
                        }
                        if(in_array($type_id,array(3,6))) {
                            if(isset($_POST[$post_question_answer][$key2]) and array_key_exists(intval($_POST[$post_question_answer][$key2]),$question_answers)) {
                                $answer_id = intval($_POST[$post_question_answer][$key2]);
                            }
                        }
                    }
                    if(empty($question_item_id) and $question_obligatory == 1) {
                        $this->error = 'not_all_data';
                        return;
                    }
                    if(in_array($type_id,array(3,6)) and empty($answer_id) and $question_obligatory == 1) {
                        $this->error = 'not_all_data';
                        return;
                    }
                } elseif(in_array($type_id,array(2,5))) {
                    $question_item_id = false;
                    if(isset($_POST[$post_question_id])) {
                        $question_item_id = intval($_POST[$post_question_id]);
                    }
                    if(empty($question_item_id) and $question_obligatory == 1) {
                        $this->error = 'not_all_data';
                        return;
                    }
                } else {
                    $this->error = 'incorrect_data';
                    return;
                }
            } elseif($type_id == 7) {
                if(isset($_POST[$post_question_text])) {
                    $answer_text = $this->registry->main_class->format_striptags(trim($_POST[$post_question_text]));
                }
                if(empty($answer_text) and $question_obligatory == 1) {
                    $this->error = 'not_all_data';
                    return;
                }
            } else {
                $this->error = 'unknown_question_type';
                return;
            }
        }
        $user_id = 'NULL';
        if($this->registry->main_class->is_user()) {
            $current_user_id = $this->registry->main_class->get_user_id();
            if(!empty($current_user_id) and intval($current_user_id) > 0) {
                $user_id = "'".intval($current_user_id)."'";
            }
        }
        $this->db->StartTrans();
        $sql = "SELECT count(*) FROM ".PREFIX."_q_voted_groups_".$this->registry->sitelang." WHERE group_id = '".$array['group_id']."' and questionnaire_id = '".$array['questionnaire_id']."'";
        $result = $this->db->Execute($sql);
        if(!$result) {
            $this->error_array[] = array("code" => $this->db->ErrorNo(),"message" => $this->db->ErrorMsg());
            $this->error = 'sql_error';
            return;
        }
        $row_exist = 0;
        if(isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        }
        if($row_exist < 1) {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_voted_groups_'.$this->registry->sitelang,'voted_group_id');
            $sql = "INSERT INTO ".PREFIX."_q_voted_groups_".$this->registry->sitelang." (".$sequence_array['field_name_string']."group_id,questionnaire_id) VALUES (".$sequence_array['sequence_value_string']."'".$array['group_id']."','".$array['questionnaire_id']."')";
            $insert_result = $this->db->Execute($sql);
            if($insert_result === false) {
                $this->error_array[] = array("code" => $this->db->ErrorNo(),"message" => $this->db->ErrorMsg());
                $this->error = 'sql_error';
                return;
            }
        }
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_voted_persons_'.$this->registry->sitelang,'person_id');
        $person_id = $sequence_array['sequence_value'];
        $sql = "INSERT INTO ".PREFIX."_q_voted_persons_".$this->registry->sitelang." (".$sequence_array['field_name_string']."user_id,voted_group_id,person_ip,voted_date) VALUES (".$sequence_array['sequence_value_string']."".$user_id.",(SELECT voted_group_id FROM ".PREFIX."_q_voted_groups_".$this->registry->sitelang." WHERE group_id = '".$array['group_id']."' and questionnaire_id = '".$array['questionnaire_id']."'),'".$this->registry->ip_addr."','".$this->registry->main_class->get_time()."')";
        $insert_result = $this->db->Execute($sql);
        if($insert_result === false) {
            $this->error_array[] = array("code" => $this->db->ErrorNo(),"message" => $this->db->ErrorMsg());
            $this->error = 'sql_error';
            return;
        } else {
            if(empty($person_id)) {
                $person_id = $this->registry->main_class->db_get_last_insert_id();
            }
        }
        foreach($results['questions'] as $key => $value) {
            $question_id = $value['question_id'];
            $type_id = $value['type_id'];
            $question_items = $value['question_items'];
            $question_answers = $value['question_answers'];
            $post_question_id = 'question_'.$question_id;
            $post_question_text = 'question_text_'.$question_id;
            $post_question_answer = 'question_answer_'.$question_id;
            if(in_array($type_id,array(1,2,3,4,5,6))) {
                if(in_array($type_id,array(1,3,4,6)) and is_array($question_items)) {
                    foreach($question_items as $key2 => $value2) {
                        $question_item_id = false;
                        if(isset($_POST[$post_question_id][$key2])) {
                            $question_item_id = intval($_POST[$post_question_id][$key2]);
                        }
                        if(in_array($type_id,array(3,6))) {
                            $question_item_answer_id = false;
                            if(!empty($_POST[$post_question_answer][$key2]) and array_key_exists(intval($_POST[$post_question_answer][$key2]),$question_answers)) {
                                $question_item_answer_id = "'".intval($_POST[$post_question_answer][$key2])."'";
                            }
                        } else {
                            $question_item_answer_id = 'NULL';
                        }
                        if(!empty($question_item_answer_id) and !empty($question_item_id)) {
                            $result = $this->process_question_item(array(
                                                                       'person_id' => $person_id,
                                                                       'question_id' => $question_id,
                                                                       'question_item_id' => $question_item_id,
                                                                       'type_id' => $type_id,
                                                                       'questionnaire_id' => $array['questionnaire_id'],
                                                                       'group_id' => $array['group_id'],
                                                                       'question_item_answer_id' => $question_item_answer_id
                                                                   ));
                            if(!$result) {
                                return;
                            }
                        }
                    }
                } elseif(in_array($type_id,array(2,5))) {
                    $question_item_id = false;
                    if(isset($_POST[$post_question_id])) {
                        $question_item_id = intval($_POST[$post_question_id]);
                    }
                    if(!empty($question_item_id)) {
                        $result = $this->process_question_item(array(
                                                                   'person_id' => $person_id,
                                                                   'question_id' => $question_id,
                                                                   'question_item_id' => $question_item_id,
                                                                   'type_id' => $type_id,
                                                                   'questionnaire_id' => $array['questionnaire_id'],
                                                                   'group_id' => $array['group_id'],
                                                                   'question_item_answer_id' => 'NULL'
                                                               ));
                        if(!$result) {
                            return;
                        }
                    }
                }
            }
            if(in_array($type_id,array(
                    4,
                    5,
                    6,
                    7
                )) and !empty($_POST[$post_question_text]) and $this->registry->main_class->format_striptags(trim($_POST[$post_question_text])) != ''
            ) {
                $result = $this->process_answer_text(array(
                                                         'post_question_text' => $post_question_text,
                                                         'person_id' => $person_id,
                                                         'question_id' => $question_id,
                                                         'type_id' => $type_id,
                                                         'questionnaire_id' => $array['questionnaire_id'],
                                                         'group_id' => $array['group_id']
                                                     ));
                if(!$result) {
                    return;
                }
            }
        }
        $this->db->CompleteTrans();
        if($this->systemdk_questionnaires_voted_method_check == 'cookie') {
            $voted_cookie = base64_encode($array['questionnaire_id'].":".$array['group_id']);
            $systemdk_questionnaires_voted_control_live_time = intval($this->systemdk_questionnaires_voted_control_live_time) > 0 ? intval($this->systemdk_questionnaires_voted_control_live_time) : 31536000; // if revote disable, we just set cookie live time to one year
            setcookie("voted_cookie_".$array['questionnaire_id']."_".$array['group_id']."_".$this->registry->sitelang,"$voted_cookie",time() + $systemdk_questionnaires_voted_control_live_time,"/".SITE_DIR);
        }
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|questionnaires|results");
        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|questionnaires|results_details|questionnaire|".$array['questionnaire_id']."|group|".$array['group_id']);
        $this->result['questionnaires_message'] = 'voted';
    }


    private function process_question_item($data_array) {
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_voted_answers_'.$this->registry->sitelang,'answer_id');
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        $answer_text = 'NULL';
        if($check_db_need_lobs == 'yes') {
            $answer_text = 'empty_clob()';
        }
        $sql = "INSERT INTO ".PREFIX."_q_voted_answers_".$this->registry->sitelang." (".$sequence_array['field_name_string']."person_id,question_id,question_item_id,questionnaire_id,answer_text,group_id,question_answer_id) VALUES (".$sequence_array['sequence_value_string']."'".$data_array['person_id']."','".$data_array['question_id']."','".$data_array['question_item_id']."','".$data_array['questionnaire_id']."',".$answer_text.",'".$data_array['group_id']."',".$data_array['question_item_answer_id'].")";
        $insert_result = $this->db->Execute($sql);
        if($insert_result === false) {
            $this->error_array[] = array("code" => $this->db->ErrorNo(),"message" => $this->db->ErrorMsg());
            $this->error = 'sql_error';
            return false;
        }
        return true;
    }


    private function process_answer_text($data_array) {
        $answer_text = $this->registry->main_class->processing_data($this->registry->main_class->format_striptags(trim($_POST[$data_array['post_question_text']])));
        $answer_text = substr(substr($answer_text,0,-1),1);
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX.'_voted_answers_'.$this->registry->sitelang,'answer_id');
        $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
        $answer_text_str = "'".$answer_text."'";
        if($check_db_need_lobs == 'yes') {
            $answer_text_str = 'empty_clob()';
        }
        $sql = "INSERT INTO ".PREFIX."_q_voted_answers_".$this->registry->sitelang." (".$sequence_array['field_name_string']."person_id,question_id,question_item_id,questionnaire_id,answer_text,group_id,question_answer_id) VALUES (".$sequence_array['sequence_value_string']."'".$data_array['person_id']."','".$data_array['question_id']."',NULL,'".$data_array['questionnaire_id']."',".$answer_text_str.",'".$data_array['group_id']."',NULL)";
        $insert_result = $this->db->Execute($sql);
        if($insert_result === false) {
            $this->error_array[] = array("code" => $this->db->ErrorNo(),"message" => $this->db->ErrorMsg());
            $this->error = 'sql_error';
            return false;
        }
        if($check_db_need_lobs == 'yes') {
            $insert_result2 = $this->db->UpdateClob(PREFIX.'_q_voted_answers_'.$this->registry->sitelang,'answer_text',$answer_text,'answer_id='.$sequence_array['sequence_value']);
            if($insert_result2 === false) {
                $this->error_array[] = array("code" => $this->db->ErrorNo(),"message" => $this->db->ErrorMsg());
                $this->error = 'sql_error';
                return false;
            }
        }
        return true;
    }


    private function check_if_vote_allow($group_id,$questionnaire_id) {
        if(in_array($this->systemdk_questionnaires_voted_method_check,array('ip_address','cookie'))) {
            $voted_questionnaire_id = false;
            $voted_group_id = false;
            if($this->systemdk_questionnaires_voted_method_check == 'ip_address') {
                $sql_add = false;
                if(intval($this->systemdk_questionnaires_voted_control_live_time) > 0) {
                    $sql_add = "and a.voted_date >= ".($this->registry->main_class->get_time() - $this->systemdk_questionnaires_voted_control_live_time);
                }
                $sql = "SELECT count(*) FROM ".PREFIX."_q_voted_persons_".$this->registry->sitelang." a,".PREFIX."_q_voted_groups_".$this->registry->sitelang." b WHERE a.voted_group_id = b.voted_group_id and a.person_ip = '".$this->registry->ip_addr."' and b.group_id = '".$group_id."' and b.questionnaire_id = '".$questionnaire_id."' ".$sql_add;
                $result = $this->db->Execute($sql);
                if(!$result) {
                    $this->error = 'questionnaires_error';
                    return false;
                }
                $row_exist = 0;
                if(isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                }
                if($row_exist > 0) {
                    $voted_questionnaire_id = $questionnaire_id;
                    $voted_group_id = $group_id;
                }
            } else {
                if(!empty($_COOKIE['voted_cookie_'.$questionnaire_id.'_'.$group_id.'_'.$this->registry->sitelang])) {
                    $voted_cookie = explode(":",base64_decode($_COOKIE['voted_cookie_'.$questionnaire_id.'_'.$group_id.'_'.$this->registry->sitelang]));
                    $voted_questionnaire_id = intval($voted_cookie[0]);
                    $voted_group_id = intval($voted_cookie[1]);
                }
            }
            if(!empty($voted_questionnaire_id) and !empty($voted_group_id) and $questionnaire_id == $voted_questionnaire_id and $group_id == $voted_group_id) {
                $this->error = 'already_voted';
                return false;
            }
        }
        if($this->systemdk_questionnaires_who_can_vote == 'only_users' and !$this->registry->main_class->is_user()) {
            $this->error = 'not_user';
            return false;
        }
        return true;
    }
}
<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_questionnaires.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class admin_questionnaires extends model_base
{


    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $num_page = 1;
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT questionnaire_id,questionnaire_name,questionnaire_date,questionnaire_status FROM " . PREFIX . "_q_questionnaires_"
                   . $this->registry->sitelang . " ORDER BY questionnaire_date ASC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if ($row_exist < 1) {
            $this->result['questionnaires_all'] = "no_questionnaires";
            $this->result['pages_menu'] = "no";

            return;
        }
        while (!$result->EOF) {
            $questionnaire_id = intval($result->fields['0']);
            $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $questionnaire_date = intval($result->fields['2']);
            $questionnaire_date = date("d.m.y", $questionnaire_date);
            $questionnaire_status = intval($result->fields['3']);
            $questionnaires_all[] = [
                'questionnaire_id'     => $questionnaire_id,
                'questionnaire_name'   => $questionnaire_name,
                'questionnaire_date'   => $questionnaire_date,
                'questionnaire_status' => $questionnaire_status,
            ];
            $result->MoveNext();
        }
        $this->result['questionnaires_all'] = $questionnaires_all;
        if (isset($num_rows)) {
            $num_pages = @ceil($num_rows / $num_string_rows);
        } else {
            $num_pages = 0;
        }
        if ($num_pages > 1) {
            if ($num_page > 1) {
                $prevpage = $num_page - 1;
                $this->result['prevpage'] = $prevpage;
            } else {
                $this->result['prevpage'] = "no";
            }
            for ($i = 1; $i < $num_pages + 1; $i++) {
                if ($i == $num_page) {
                    $html[] = ["number" => $i, "param1" => "1"];
                } else {
                    $pagelink = 5;
                    if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "2"];
                    }
                    if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "3"];
                    }
                    if (($i == 1) && ($num_page > $pagelink + 1)) {
                        $html[] = ["number" => $i, "param1" => "4"];
                    }
                }
            }
            if ($num_page < $num_pages) {
                $nextpage = $num_page + 1;
                $this->result['nextpage'] = $nextpage;
            } else {
                $this->result['nextpage'] = "no";
            }
            $this->result['pages_menu'] = $html;
            $this->result['total_questionnaires'] = $num_rows;
            $this->result['num_pages'] = $num_pages;
        } else {
            $this->result['pages_menu'] = "no";
        }
    }


    public function questionnaire_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'questionnaire_name',
                'questionnaire_meta_title',
                'questionnaire_meta_keywords',
                'questionnaire_meta_description',
            ];
            $keys2 = [
                'questionnaire_status',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_name']) || !isset($data_array['questionnaire_status'])) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['questionnaire_name'] = $this->registry->main_class->processing_data($data_array['questionnaire_name']);
        $data_array['questionnaire_meta_title'] = $this->registry->main_class->processing_data($data_array['questionnaire_meta_title']);
        $data_array['questionnaire_meta_keywords'] = $this->registry->main_class->processing_data($data_array['questionnaire_meta_keywords']);
        $data_array['questionnaire_meta_description'] = $this->registry->main_class->processing_data($data_array['questionnaire_meta_description']);
        $now = $this->registry->main_class->get_time();
        $sequence_array = $this->registry->main_class->db_process_sequence(
            PREFIX . "_questionnaires_" . $this->registry->sitelang, 'questionnaire_id'
        );
        $sql = "INSERT INTO " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . "("
               . $sequence_array['field_name_string']
               . "questionnaire_name,questionnaire_date,questionnaire_status,questionnaire_meta_title,questionnaire_meta_keywords,questionnaire_meta_description) VALUES ("
               . $sequence_array['sequence_value_string'] . "" . $data_array['questionnaire_name'] . ",'" . $now . "','" . $data_array['questionnaire_status'] . "',"
               . $data_array['questionnaire_meta_title'] . "," . $data_array['questionnaire_meta_keywords'] . "," . $data_array['questionnaire_meta_description'] . ")";
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|show");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|questionnaires_list");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|group_add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_group");
        $this->result = 'questionnaire_add_ok';
    }


    public function questionnaire_edit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['questionnaire_id'])) {
            $array['questionnaire_id'] = intval($array['questionnaire_id']);
        }
        if (empty($array['questionnaire_id']) || $array['questionnaire_id'] < 1) {
            $this->error = 'questionnaire_empty_data';

            return;
        }
        $sql =
            "SELECT questionnaire_id,questionnaire_name,questionnaire_date,questionnaire_status,questionnaire_meta_title,questionnaire_meta_keywords,questionnaire_meta_description FROM "
            . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " WHERE questionnaire_id = '" . $array['questionnaire_id'] . "'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $row_exist = 0;
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        }
        if ($row_exist < 1) {
            $this->error = 'questionnaire_not_found';

            return;
        }
        $questionnaire_id = intval($result->fields['0']);
        $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $questionnaire_date = intval($result->fields['2']);
        $questionnaire_date = date("d.m.Y H:i", $questionnaire_date);
        $questionnaire_status = intval($result->fields['3']);
        $questionnaire_meta_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
        $questionnaire_meta_keywords = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $questionnaire_meta_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
        $this->result['questionnaire_all'] = [
            "questionnaire_id"               => $questionnaire_id,
            "questionnaire_name"             => $questionnaire_name,
            "questionnaire_date"             => $questionnaire_date,
            "questionnaire_status"           => $questionnaire_status,
            "questionnaire_meta_title"       => $questionnaire_meta_title,
            "questionnaire_meta_keywords"    => $questionnaire_meta_keywords,
            "questionnaire_meta_description" => $questionnaire_meta_description,
        ];
    }


    public function questionnaire_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'questionnaire_name',
                'questionnaire_meta_title',
                'questionnaire_meta_keywords',
                'questionnaire_meta_description',
            ];
            $keys2 = [
                'questionnaire_id',
                'questionnaire_status',
                'questionnaire_date',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_name']) || empty($data_array['questionnaire_id']) || !isset($data_array['questionnaire_status'])) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['questionnaire_name'] = $this->registry->main_class->processing_data($data_array['questionnaire_name']);
        $data_array['questionnaire_meta_title'] = $this->registry->main_class->processing_data($data_array['questionnaire_meta_title']);
        $data_array['questionnaire_meta_keywords'] = $this->registry->main_class->processing_data($data_array['questionnaire_meta_keywords']);
        $data_array['questionnaire_meta_description'] = $this->registry->main_class->processing_data($data_array['questionnaire_meta_description']);
        $now = $this->registry->main_class->get_time();
        $sql_add = false;
        if (!empty($data_array['questionnaire_date'])) {
            $sql_add = ",questionnaire_date = '" . $now . "'";
        }
        $sql = "UPDATE " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " SET questionnaire_name = " . $data_array['questionnaire_name'] . $sql_add
               . ",questionnaire_status = '" . $data_array['questionnaire_status'] . "',questionnaire_meta_title = " . $data_array['questionnaire_meta_title']
               . ",questionnaire_meta_keywords = " . $data_array['questionnaire_meta_keywords'] . ",questionnaire_meta_description = "
               . $data_array['questionnaire_meta_description'] . " WHERE questionnaire_id = '" . $data_array['questionnaire_id'] . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|show");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|questionnaires_list");
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_questionnaire|" . $data_array['questionnaire_id']
        );
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view|" . $data_array['questionnaire_id']);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|group_add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_group");
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|questions"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question_add"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question_edit"
        );
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results");
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire|" . $data_array['questionnaire_id'] . "|group"
        );
        $this->result = 'questionnaire_edit_done';
    }


    public function questionnaires_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = "";
        if (isset($array['get_action'])) {
            $action = trim($array['get_action']);
        }
        if (!isset($array['get_action']) && isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }
        $questionnaire_id = 0;
        if (isset($array['get_questionnaire_id'])) {
            $questionnaire_id = intval($array['get_questionnaire_id']);
        }
        if ($questionnaire_id == 0 && isset($array['post_questionnaire_id']) && is_array($array['post_questionnaire_id']) && count($array['post_questionnaire_id']) > 0) {
            $questionnaire_id_is_arr = 1;
            for ($i = 0, $size = count($array['post_questionnaire_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $questionnaire_id = "'" . intval($array['post_questionnaire_id'][$i]) . "'";
                } else {
                    $questionnaire_id .= ",'" . intval($array['post_questionnaire_id'][$i]) . "'";
                }
            }
        } else {
            $questionnaire_id_is_arr = 0;
            $questionnaire_id = "'" . $questionnaire_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if (!isset($questionnaire_id) || $action == "" || $questionnaire_id == "'0'") {
            $this->error = 'empty_data';

            return;
        }
        if ($action == "on") {
            $sql = "UPDATE " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " SET questionnaire_status = '1' WHERE questionnaire_id in ("
                   . $questionnaire_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            }
        } elseif ($action == "off") {
            $sql = "UPDATE " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " SET questionnaire_status = '0' WHERE questionnaire_id IN ("
                   . $questionnaire_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            }
        } elseif ($action == "confirm_delete") {
            $sql = "SELECT questionnaire_id,questionnaire_name FROM " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " WHERE questionnaire_id IN ("
                   . $questionnaire_id . ")";
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            $row_exist = 0;
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            }
            if ($row_exist < 1) {
                $this->error = 'empty_data';

                return;
            }
            while (!$result->EOF) {
                $questionnaire_id = intval($result->fields['0']);
                $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $questionnaires_all[] = [
                    'questionnaire_id'   => $questionnaire_id,
                    'questionnaire_name' => $questionnaire_name,
                ];
                $result->MoveNext();
            }
            $this->result['questionnaires_all'] = $questionnaires_all;
            $this->result['questionnaires_message'] = $action;

            return;
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " WHERE question_id in (SELECT question_id FROM " . PREFIX
                    . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . "))";
            $result1 = $this->db->Execute($sql1);
            if ($result1 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql2 = "DELETE FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " WHERE question_id in (SELECT question_id FROM " . PREFIX
                    . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . "))";
            $result2 = $this->db->Execute($sql2);
            if ($result2 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql3 = "DELETE FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . ")";
            $result3 = $this->db->Execute($sql3);
            if ($result3 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql4 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . ")";
            $result4 = $this->db->Execute($sql4);
            if ($result4 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql5 = "DELETE FROM " . PREFIX . "_q_voted_persons_" . $this->registry->sitelang . " WHERE voted_group_id IN (SELECT voted_group_id FROM " . PREFIX
                    . "_q_voted_groups_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . "))";
            $result5 = $this->db->Execute($sql5);
            if ($result5 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql6 = "DELETE FROM " . PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . ")";
            $result6 = $this->db->Execute($sql6);
            if ($result6 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql7 = "DELETE FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . ")";
            $result7 = $this->db->Execute($sql7);
            if ($result7 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql8 = "DELETE FROM " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " WHERE questionnaire_id IN (" . $questionnaire_id . ")";
            $result = $this->db->Execute($sql8);
            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            } else {
                $num_result = $this->db->Affected_Rows();
            }
            if ($result1 === false || $result2 === false || $result3 === false || $result4 === false || $result5 === false || $result6 === false || $result7 === false) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result === false) {
            if ($action != "delete") {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|questionnaires_list");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|show");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|groups");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|group_add");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_group");
        if ($action == "delete") {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results");
        }
        if ($questionnaire_id_is_arr == 1) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_questionnaire");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire");
            if ($action == "delete") {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire");
            }
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view|" . intval($array['get_questionnaire_id']));
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_questionnaire|" . intval($array['get_questionnaire_id'])
            );
            if ($action == "delete") {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . intval($array['get_questionnaire_id']) . "|questions"
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . intval($array['get_questionnaire_id']) . "|question_add"
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . intval($array['get_questionnaire_id']) . "|question"
                );
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . intval($array['get_questionnaire_id']) . "|question_edit"
                );
                $this->registry->main_class->clearCache(
                    null,
                    $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire|" . intval($array['get_questionnaire_id']) . "|group"
                );
            }
        }
        $this->result['questionnaires_message'] = $action;
    }


    public function groups($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $num_page = 1;
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT group_id,questionnaire_id,group_name,group_status FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang . " ORDER BY group_id ASC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if ($row_exist < 1) {
            $this->result['groups_all'] = "no_groups";
            $this->result['pages_menu'] = "no";

            return;
        }
        while (!$result->EOF) {
            $group_id = intval($result->fields['0']);
            $questionnaire_id = intval($result->fields['1']);
            $group_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
            $group_status = intval($result->fields['3']);
            $questionnaires_all[] = [
                'group_id'         => $group_id,
                'questionnaire_id' => $questionnaire_id,
                'group_name'       => $group_name,
                'group_status'     => $group_status,
            ];
            $result->MoveNext();
        }
        $this->result['groups_all'] = $questionnaires_all;
        if (isset($num_rows)) {
            $num_pages = @ceil($num_rows / $num_string_rows);
        } else {
            $num_pages = 0;
        }
        if ($num_pages > 1) {
            if ($num_page > 1) {
                $prevpage = $num_page - 1;
                $this->result['prevpage'] = $prevpage;
            } else {
                $this->result['prevpage'] = "no";
            }
            for ($i = 1; $i < $num_pages + 1; $i++) {
                if ($i == $num_page) {
                    $html[] = ["number" => $i, "param1" => "1"];
                } else {
                    $pagelink = 5;
                    if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "2"];
                    }
                    if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "3"];
                    }
                    if (($i == 1) && ($num_page > $pagelink + 1)) {
                        $html[] = ["number" => $i, "param1" => "4"];
                    }
                }
            }
            if ($num_page < $num_pages) {
                $nextpage = $num_page + 1;
                $this->result['nextpage'] = $nextpage;
            } else {
                $this->result['nextpage'] = "no";
            }
            $this->result['pages_menu'] = $html;
            $this->result['total_groups'] = $num_rows;
            $this->result['num_pages'] = $num_pages;
        } else {
            $this->result['pages_menu'] = "no";
        }
    }


    public function group_add()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $sql = "SELECT questionnaire_id,questionnaire_name,questionnaire_status FROM " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'no_questionnaires';

            return;
        }
        while (!$result->EOF) {
            $questionnaire_id = intval($result->fields['0']);
            $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            $questionnaire_status = intval($result->fields['2']);
            $questionnaires_all[] = [
                "questionnaire_id"     => $questionnaire_id,
                "questionnaire_name"   => $questionnaire_name,
                "questionnaire_status" => $questionnaire_status,
            ];
            $result->MoveNext();
        }
        $this->result['questionnaires_all'] = $questionnaires_all;
    }


    public function group_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'group_name',
            ];
            $keys2 = [
                'group_status',
                'questionnaire_id',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['group_name']) || !isset($data_array['group_status']) || empty($data_array['questionnaire_id'])) {
            $this->error = 'not_all_data';

            return;
        }
        if (!$this->check_questionnaire_assign($data_array['questionnaire_id'])) {
            return;
        }
        $data_array['group_name'] = $this->registry->main_class->processing_data($data_array['group_name']);
        $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_groups_" . $this->registry->sitelang, 'group_id');
        $sql = "INSERT INTO " . PREFIX . "_q_groups_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
               . "questionnaire_id,group_name,group_status) VALUES (" . $sequence_array['sequence_value_string'] . "'" . $data_array['questionnaire_id'] . "',"
               . $data_array['group_name'] . ",'" . $data_array['group_status'] . "')";
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|groups");
        $this->result = 'group_add_ok';
    }


    private function check_questionnaire_assign($questionnaire_id, $exclude_group_id = false)
    {
        $sql_add = false;
        if ($exclude_group_id) {
            $sql_add = " and group_id <> '" . $exclude_group_id . "'";
        }
        $sql = "SELECT count(*) FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang . " WHERE questionnaire_id = '" . $questionnaire_id
               . "' and group_status = '1'" . $sql_add;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return false;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist > 0) {
            $this->error = 'already_assigned';

            return false;
        }

        return true;
    }


    public function group_edit($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (isset($array['group_id'])) {
            $array['group_id'] = intval($array['group_id']);
        }
        if (empty($array['group_id']) || $array['group_id'] < 1) {
            $this->error = 'empty_data';

            return;
        }
        $sql = "SELECT a.group_id,a.questionnaire_id,a.group_name,a.group_status,null,null FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang
               . " a WHERE a.group_id = '" . $array['group_id'] . "' UNION ALL SELECT null,null,null,null,b.questionnaire_id,b.questionnaire_name FROM " . PREFIX
               . "_q_questionnaires_" . $this->registry->sitelang . " b";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $row_exist1 = 0;
        $row_exist2 = 0;
        if (isset($result->fields['0'])) {
            $row_exist1 = intval($result->fields['0']);
        }
        if (isset($result->fields['4'])) {
            $row_exist2 = intval($result->fields['4']);
        }
        if ($row_exist1 < 1 && $row_exist2 < 1) {
            if ($row_exist1 < 1) {
                $this->error = 'group_edit_not_found';
            } else {
                $this->error = 'no_questionnaires';
            }

            return;
        }
        while (!$result->EOF) {
            if (empty($this->result['group_all']) && intval($result->fields['0']) > 0) {
                $group_id = intval($result->fields['0']);
                $questionnaire_id = intval($result->fields['1']);
                $group_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $group_status = intval($result->fields['3']);
                $this->result['group_all'] = [
                    "group_id"         => $group_id,
                    "questionnaire_id" => $questionnaire_id,
                    "group_name"       => $group_name,
                    "group_status"     => $group_status,
                ];
            }
            if (isset($result->fields['4']) && intval($result->fields['4']) > 0) {
                $questionnaire_id = intval($result->fields['4']);
                $questionnaire_name = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['5'])
                );
                $this->result['questionnaires_all'][] = [
                    "questionnaire_id"   => $questionnaire_id,
                    "questionnaire_name" => $questionnaire_name,
                ];
            }
            $result->MoveNext();
        }
        if (empty($this->result['group_all'])) {
            $this->error = 'group_edit_not_found';

            return;
        }
        if (empty($this->result['questionnaires_all'])) {
            $this->error = 'no_questionnaires';

            return;
        }
    }


    public function group_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'group_name',
            ];
            $keys2 = [
                'group_id',
                'questionnaire_id',
                'group_status',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['group_name']) || empty($data_array['group_id']) || empty($data_array['questionnaire_id']) || !isset($data_array['group_status'])) {
            $this->error = 'not_all_data';

            return;
        }
        if (!$this->check_questionnaire_assign($data_array['questionnaire_id'], $data_array['group_id'])) {
            return;
        }
        $data_array['group_name'] = $this->registry->main_class->processing_data($data_array['group_name']);
        $sql = "UPDATE " . PREFIX . "_q_groups_" . $this->registry->sitelang . " SET questionnaire_id = '" . $data_array['questionnaire_id']
               . "',group_name = " . $data_array['group_name'] . ",group_status = '" . $data_array['group_status'] . "' WHERE group_id = '" . $data_array['group_id']
               . "'";
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|groups");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|questionnaires_list");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_group|" . $data_array['group_id']);
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire");
        $this->result = 'group_edit_done';
    }


    public function groups_status($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = "";
        if (isset($array['get_action'])) {
            $action = trim($array['get_action']);
        }
        if (!isset($array['get_action']) && isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }
        $group_id = 0;
        if (isset($array['get_group_id'])) {
            $group_id = intval($array['get_group_id']);
        }
        if ($group_id == 0 && isset($array['post_group_id']) && is_array($array['post_group_id']) && count($array['post_group_id']) > 0) {
            $group_id_is_arr = 1;
            for ($i = 0, $size = count($array['post_group_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $group_id = "'" . intval($array['post_group_id'][$i]) . "'";
                } else {
                    $group_id .= ",'" . intval($array['post_group_id'][$i]) . "'";
                }
            }
        } else {
            $group_id_is_arr = 0;
            $group_id = "'" . $group_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if (!isset($group_id) || $action == "" || $group_id == "'0'") {
            $this->error = 'empty_data';

            return;
        }
        if ($action == "group_on") {
            // TODO: Currently group activation only for one POST or GET group_id
            $sql = "SELECT count(a.group_id) FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang
                   . " a WHERE a.questionnaire_id in (SELECT b.questionnaire_id FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang . " b WHERE b.group_id = "
                   . $group_id . ") and a.group_status='1' and a.group_id <> " . $group_id . "";
            $result = $this->db->Execute($sql);
            if ($result) {
                if (isset($result->fields['0'])) {
                    $row_exist = intval($result->fields['0']);
                } else {
                    $row_exist = 0;
                }
                if ($row_exist > 0) {
                    $this->error = 'already_assigned';

                    return false;
                }
                $sql = "UPDATE " . PREFIX . "_q_groups_" . $this->registry->sitelang . " SET group_status = '1' WHERE group_id in (" . $group_id . ")";
                $result = $this->db->Execute($sql);
                if ($result) {
                    $num_result = $this->db->Affected_Rows();
                }
            }
        } elseif ($action == "group_off") {
            $sql = "UPDATE " . PREFIX . "_q_groups_" . $this->registry->sitelang . " SET group_status = '0' WHERE group_id IN (" . $group_id . ")";
            $result = $this->db->Execute($sql);
            if ($result) {
                $num_result = $this->db->Affected_Rows();
            }
        } elseif ($action == "confirm_group_delete") {
            $sql = "SELECT group_id,group_name FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . ")";
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            $row_exist = 0;
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            }
            if ($row_exist < 1) {
                $this->error = 'empty_data';

                return;
            }
            while (!$result->EOF) {
                $group_id = intval($result->fields['0']);
                $group_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $groups_all[] = [
                    'group_id'   => $group_id,
                    'group_name' => $group_name,
                ];
                $result->MoveNext();
            }
            $this->result['groups_all'] = $groups_all;
            $this->result['questionnaires_message'] = $action;

            return;
        } elseif ($action == "group_delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " WHERE question_id IN (SELECT question_id FROM " . PREFIX
                    . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id IN (SELECT questionnaire_id FROM " . PREFIX . "_q_groups_"
                    . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . ")))";
            $result1 = $this->db->Execute($sql1);
            if ($result1 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql2 = "DELETE FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " WHERE question_id IN (SELECT question_id FROM " . PREFIX
                    . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id IN (SELECT questionnaire_id FROM " . PREFIX . "_q_groups_"
                    . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . ")))";
            $result2 = $this->db->Execute($sql2);
            if ($result2 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql3 = "DELETE FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id IN (SELECT questionnaire_id FROM " . PREFIX
                    . "_q_groups_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . "))";
            $result3 = $this->db->Execute($sql3);
            if ($result3 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql4 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . ")";
            $result4 = $this->db->Execute($sql4);
            if ($result4 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql5 = "DELETE FROM " . PREFIX . "_q_voted_persons_" . $this->registry->sitelang . " WHERE voted_group_id IN (SELECT voted_group_id FROM " . PREFIX
                    . "_q_voted_groups_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . "))";
            $result5 = $this->db->Execute($sql5);
            if ($result5 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql6 = "DELETE FROM " . PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . ")";
            $result6 = $this->db->Execute($sql6);
            if ($result6 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql7 = "DELETE FROM " . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " WHERE questionnaire_id IN (SELECT questionnaire_id FROM " . PREFIX
                    . "_q_groups_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . "))";
            $result7 = $this->db->Execute($sql7);
            if ($result7 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql8 = "DELETE FROM " . PREFIX . "_q_groups_" . $this->registry->sitelang . " WHERE group_id IN (" . $group_id . ")";
            $result = $this->db->Execute($sql8);
            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            } else {
                $num_result = $this->db->Affected_Rows();
            }
            if ($result1 === false || $result2 === false || $result3 === false || $result4 === false || $result5 === false || $result6 === false || $result7 === false) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result === false) {
            if ($action != "group_delete") {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
            }
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        if (in_array($action, ['group_on', 'group_off'])) {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|questionnaires_list");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|groups");
            if ($group_id_is_arr == 1) {
                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_group");
            } else {
                $this->registry->main_class->clearCache(
                    null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|edit_group|" . intval($array['get_group_id'])
                );
            }
        } else {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires");
        }
        $this->result['questionnaires_message'] = $action;
    }


    public function questions($questionnaire_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $questionnaire_id = intval($questionnaire_id);
        if ($questionnaire_id < 1) {
            $this->error = 'empty_data';

            return;
        }
        $results = $this->get_questions($questionnaire_id);
        if ($results) {
            $this->result = $results;
        }
    }


    private function get_questions($questionnaire_id, $group_id = false, $questionnaire_results = false)
    {
        $fields
            =
            "a.questionnaire_id,a.questionnaire_name,a.questionnaire_date,a.questionnaire_status,b.question_id,b.question_title,b.type_id,b.question_priority,b.question_subtext,b.question_obligatory,c.type_name,c.type_description,d.question_answer_id,d.question_answer,d.question_answer_priority,e.question_item_id,e.question_item_title,e.question_item_priority";
        $fields_add = false;
        $fields_add2 = false;
        $fields_add2_group_by = false;
        $count_fields = false;
        $tables_add = false;
        $join_tables = false;
        $where_add = false;
        $group_by = false;
        if ($questionnaire_results) {
            $fields_add = ",g.group_name";
            $count_fields = ",count(h.answer_id) as count_voted_question_item,count(k.answer_id) as count_voted_question_answer";
            if ($questionnaire_results === 'results_by_persons') {
                $fields_add2
                    =
                    ",l.person_id,m.question_item_id as answer_question_item_id,(CASE WHEN n.question_answer_id IS NOT NULL THEN d.question_answer ELSE NULL END) as answer_question_answer";
                $fields_add2_group_by = ",l.person_id,m.question_item_id,(CASE WHEN n.question_answer_id IS NOT NULL THEN d.question_answer ELSE NULL END)";
                $check_db_need_lobs = $this->registry->main_class->check_db_need_lobs();
                if ($check_db_need_lobs == 'yes') {
                    $fields_add2 .= ",dbms_lob.substr(o.answer_text) as answer_text"; // workaround for Oracle ORA-00932: inconsistent datatypes: expected - got CLOB
                    $fields_add2_group_by .= ",dbms_lob.substr(o.answer_text)";
                } else {
                    $fields_add2 .= ",o.answer_text";
                    $fields_add2_group_by .= ",o.answer_text";
                }
                $fields_add2 .= ",l.user_id,l.voted_date";
                $fields_add2_group_by .= ",l.user_id,l.voted_date";
                //(l.group_id = '".$group_id."' and l.questionnaire_id = a.questionnaire_id)
                $join_tables = "LEFT OUTER JOIN " . PREFIX . "_q_voted_persons_" . $this->registry->sitelang . " l on l.voted_group_id IN (SELECT l2.voted_group_id FROM "
                               . PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " l2 WHERE l2.group_id = '" . $group_id . "' and l2.questionnaire_id = a.questionnaire_id)
                LEFT OUTER JOIN " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " m on a.questionnaire_id = m.questionnaire_id and m.group_id = '"
                               . $group_id . "' and b.question_id = m.question_id and b.type_id in (1,2,4,5)  and e.question_item_id = m.question_item_id and l.person_id = m.person_id
                LEFT OUTER JOIN " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " n on a.questionnaire_id = n.questionnaire_id and n.group_id = '"
                               . $group_id . "' and b.question_id = n.question_id and b.type_id in (3,6) and e.question_item_id = n.question_item_id and d.question_answer_id = n.question_answer_id and l.person_id = n.person_id
                LEFT OUTER JOIN " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " o on a.questionnaire_id = o.questionnaire_id and o.group_id = '"
                               . $group_id . "' and b.question_id = o.question_id and b.type_id in (4,5,6,7) and l.person_id = o.person_id ";
            }
            $tables_add = PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " f, " . PREFIX . "_q_groups_" . $this->registry->sitelang . " g, ";
            $join_tables = $join_tables . "LEFT OUTER JOIN " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang
                           . " h on a.questionnaire_id = h.questionnaire_id and h.group_id = '" . $group_id . "' and b.question_id = h.question_id and b.type_id in (1,2,4,5)  and e.question_item_id = h.question_item_id
            LEFT OUTER JOIN " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " k on a.questionnaire_id = k.questionnaire_id and k.group_id = '" . $group_id
                           . "' and b.question_id = k.question_id and b.type_id in (3,6)  and e.question_item_id = k.question_item_id and d.question_answer_id = k.question_answer_id";
            $where_add = "a.questionnaire_id = f.questionnaire_id and f.group_id = g.group_id and f.group_id = '" . $group_id . "' and ";
            if ($questionnaire_results === 'results_by_persons') {
                $where_add = $where_add . "l.voted_group_id = f.voted_group_id and ";
                //$where_add = $where_add."l.group_id = '".$group_id."' and l.questionnaire_id = a.questionnaire_id and ";
            }
            $group_by = " GROUP BY " . $fields . $fields_add . $fields_add2_group_by;
        }
        $sql = "SELECT " . $fields . $fields_add . $count_fields . $fields_add2 . "
                FROM " . $tables_add . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " a
                LEFT OUTER JOIN " . PREFIX . "_q_questions_" . $this->registry->sitelang . " b on a.questionnaire_id=b.questionnaire_id
                LEFT OUTER JOIN " . PREFIX . "_q_question_types c on b.type_id=c.type_id
                LEFT OUTER JOIN " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " d on b.question_id=d.question_id
                LEFT OUTER JOIN " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " e on b.question_id=e.question_id " . $join_tables . " WHERE "
               . $where_add . "a.questionnaire_id = " . $questionnaire_id . $group_by
               . " ORDER BY b.question_priority,d.question_answer_priority,e.question_item_priority ASC";
        //echo $sql; exit();
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return false;
        }
        $row_exist = 0;
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        }
        if ($row_exist < 1) {
            $this->error = 'questionnaire_not_found';

            return false;
        }
        $results['questionnaire_id'] = intval($result->fields['0']);
        $results['questionnaire_name'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $questionnaire_date = intval($result->fields['2']);
        $results['questionnaire_date'] = date("d.m.y", $questionnaire_date);
        $results['questionnaire_status'] = intval($result->fields['3']);
        if ($questionnaire_results) {
            $results['group_name'] = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['18']));
        }
        $question_exist = intval($result->fields['4']);
        if ($question_exist < 1) {
            $results['questions'] = 'no';

            return $results;
        }
        while (!$result->EOF) {
            $question_id = intval($result->fields['4']);
            $question_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
            $type_id = intval($result->fields['6']);
            $question_priority = intval($result->fields['7']);
            $question_subtext = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
            $question_obligatory = intval($result->fields['9']);
            $type_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
            $type_description = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
            $question_answer_id = intval($result->fields['12']);
            $question_item_id = intval($result->fields['15']);
            $question_allow_question_items = $this->check_allow_question_items($type_id);
            $question_allow_answer_variants = $this->check_allow_answer_variants($type_id);
            if ($questionnaire_results && $questionnaire_results === 'results_by_persons') {
                $person_id = intval($result->fields['21']);
                $user_id = intval($result->fields['25']);
                $voted_date = date("d.m.y H:i", intval($result->fields['26']));
                $results['voted_persons_list'][$person_id]['person_id'] = $person_id;
                $results['voted_persons_list'][$person_id]['user_id'] = $user_id;
                $results['voted_persons_list'][$person_id]['voted_date'] = $voted_date;
                $answer_question_item_id = false;
                if ($question_item_id > 0) {
                    $answer_question_item_id = intval($result->fields['22']);
                }
                $answer_question_answer = false;
                if ($question_answer_id > 0) {
                    $answer_question_answer = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['23']));
                }
                $answer_text = false;
                if ($this->check_need_question_subtext($type_id)) {
                    $answer_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['24']));
                }
            }
            if (!isset($results['questions'][$question_id])) {
                $results['questions'][$question_id] = [
                    'question_id'                    => $question_id,
                    'question_title'                 => $question_title,
                    'type_id'                        => $type_id,
                    'question_priority'              => $question_priority,
                    'question_obligatory'            => $question_obligatory,
                    'type_name'                      => $type_name,
                    'type_description'               => $type_description,
                    'question_allow_question_items'  => $question_allow_question_items,
                    'question_allow_answer_variants' => $question_allow_answer_variants,
                ];
                if ($this->check_need_question_subtext($type_id)) {
                    $results['questions'][$question_id] = array_merge($results['questions'][$question_id], ['question_subtext' => $question_subtext]);
                }
            }
            if ($questionnaire_results && $questionnaire_results === 'results_by_persons' && $question_item_id < 1) {
                $results['questions'][$question_id]['results_by_persons'][$person_id] = [
                    'person_id'   => $person_id,
                    'answer_text' => $answer_text,
                ];
            }
            if ($question_answer_id < 1) {
                if (!isset($results['questions'][$question_id]['question_answers'])) {
                    $results['questions'][$question_id]['question_answers'] = 'no';
                }
            } else {
                $question_answer = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['13'])
                );
                $question_answer_priority = intval($result->fields['14']);
                $results['questions'][$question_id]['question_answers'][$question_answer_id] = [
                    'question_answer_id'       => $question_answer_id,
                    'question_answer'          => $question_answer,
                    'question_answer_priority' => $question_answer_priority,
                ];
                if ($questionnaire_results) {
                    if ($questionnaire_results === 'results_by_persons') {
                        if ($answer_question_answer !== '') {
                            $results['questions'][$question_id]['question_items'][$question_item_id]['results_by_persons'][$person_id]['answer_question_answer']
                                = $answer_question_answer;
                        }
                    } else {
                        $results['questions'][$question_id]['question_items'][$question_item_id]['count_voted_question_answers'][$question_answer_id] = intval(
                            $result->fields['20']
                        );
                    }
                }
            }
            if ($question_item_id < 1) {
                if (!isset($results['questions'][$question_id]['question_items'])) {
                    $results['questions'][$question_id]['question_items'] = 'no';
                }
            } else {
                $question_item_title = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['16'])
                );
                $question_item_priority = intval($result->fields['17']);
                $results['questions'][$question_id]['question_items'][$question_item_id]['question_item_id'] = $question_item_id;
                $results['questions'][$question_id]['question_items'][$question_item_id]['question_item_title'] = $question_item_title;
                $results['questions'][$question_id]['question_items'][$question_item_id]['question_item_priority'] = $question_item_priority;
                if ($questionnaire_results) {
                    $results['questions'][$question_id]['question_items'][$question_item_id]['count_voted_question_item'] = intval($result->fields['19']);
                    if ($questionnaire_results === 'results_by_persons') {
                        $results['questions'][$question_id]['question_items'][$question_item_id]['results_by_persons'][$person_id]['person_id'] = $person_id;
                        $results['questions'][$question_id]['question_items'][$question_item_id]['results_by_persons'][$person_id]['answer_question_item_id']
                            = $answer_question_item_id;
                        $results['questions'][$question_id]['results_by_persons'][$person_id]['answer_text']
                            = $answer_text; //['question_items'][$question_item_id]
                    }
                }
            }
            $result->MoveNext();
        }
        //var_dump($results);
        //exit();
        return $results;
    }


    private function check_allow_question_items($question_type_id)
    {
        if (in_array($question_type_id, [1, 2, 3, 4, 5, 6])) {
            return true;
        }

        return false;
    }


    private function check_allow_answer_variants($question_type_id)
    {
        if (in_array($question_type_id, [3, 6])) {
            return true;
        }

        return false;
    }


    private function check_need_question_subtext($question_type)
    {
        if (in_array($question_type, [4, 5, 6, 7])) {
            return true;
        }

        return false;
    }


    public function question_add($questionnaire_id)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $questionnaire_id = intval($questionnaire_id);
        if ($questionnaire_id < 1) {
            $this->error = 'empty_questionnaire';

            return;
        }
        $sql = "SELECT questionnaire_id,questionnaire_name,null as type_id,null as type_name,null as type_description FROM " . PREFIX . "_q_questionnaires_"
               . $this->registry->sitelang . " WHERE questionnaire_id = " . $questionnaire_id
               . " UNION ALL SELECT null as questionnaire_id,null as questionnaire_name,type_id,type_name,type_description FROM " . PREFIX . "_q_question_types";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $row_exist1 = 0;
        $row_exist2 = 0;
        if (isset($result->fields['0'])) {
            $row_exist1 = intval($result->fields['0']);
        }
        if (isset($result->fields['2'])) {
            $row_exist2 = intval($result->fields['2']);
        }
        if ($row_exist1 < 1 && $row_exist2 < 1) {
            if ($row_exist1 < 1) {
                $this->error = 'questionnaire_not_found';
            } else {
                $this->error = 'no_question_types';
            }

            return;
        }
        while (!$result->EOF) {
            if (empty($this->result['questionnaires_all']) && intval($result->fields['0']) > 0) {
                $questionnaire_id = intval($result->fields['0']);
                $questionnaire_name = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['1'])
                );
                $this->result['questionnaires_all'] = [
                    "questionnaire_id"   => $questionnaire_id,
                    "questionnaire_name" => $questionnaire_name,
                ];
            }
            if (isset($result->fields['2']) && intval($result->fields['2']) > 0) {
                $type_id = intval($result->fields['2']);
                $type_name = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['3'])
                );
                $type_description = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['4'])
                );
                $this->result['question_types_all'][] = [
                    "type_id"          => $type_id,
                    "type_name"        => $type_name,
                    "type_description" => $type_description,
                ];
            }
            $result->MoveNext();
        }
        if (empty($this->result['questionnaires_all'])) {
            $this->error = 'questionnaire_not_found';

            return;
        }
        if (empty($this->result['question_types_all'])) {
            $this->error = 'no_question_types';

            return;
        }
    }


    public function process_questionnaire_and_question($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'questionnaire_id',
                'question_id',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_id'])) {
            $this->error = 'empty_questionnaire';

            return;
        }
        if (empty($data_array['question_id'])) {
            $this->error = 'empty_question';

            return;
        }
        $sql = "SELECT a.questionnaire_id,a.questionnaire_name,b.question_id,b.question_title,b.question_subtext,b.question_obligatory,b.type_id FROM " . PREFIX
               . "_q_questionnaires_" . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_q_questions_" . $this->registry->sitelang
               . " b ON (a.questionnaire_id=b.questionnaire_id and b.question_id = " . $data_array['question_id'] . ") WHERE a.questionnaire_id = "
               . $data_array['questionnaire_id'];
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $questionnaire_id = 0;
        if (isset($result->fields['0'])) {
            $questionnaire_id = intval($result->fields['0']);
        }
        if ($questionnaire_id < 1) {
            $this->error = 'questionnaire_not_found';

            return false;
        }
        $question_id = intval($result->fields['2']);
        if ($question_id < 1) {
            $this->error = 'question_not_found';

            return false;
        }
        $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $question_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        $question_subtext = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
        $question_obligatory = intval($result->fields['5']);
        $question_type = intval($result->fields['6']);
        $this->result['questionnaires_all'] = [
            'questionnaire_id'    => $questionnaire_id,
            'questionnaire_name'  => $questionnaire_name,
            'question_id'         => $question_id,
            'question_title'      => $question_title,
            'question_subtext'    => $question_subtext,
            'question_obligatory' => $question_obligatory,
            'question_type'       => $question_type,
        ];
        $this->result['questionnaire_action'] = $array['questionnaire_action'];
    }


    public function question_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'title',
                'question_subtext',
            ];
            $keys2 = [
                'questionnaire_id',
                'question_type',
                'question_obligatory',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_id']) || empty($data_array['title']) || empty($data_array['question_type']) || !isset($data_array['question_obligatory'])
            || ($this->check_need_question_subtext($data_array['question_type']) && empty($data_array['question_subtext']))
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['title'] = $this->registry->main_class->processing_data($data_array['title']);
        if ($this->check_need_question_subtext($data_array['question_type'])) {
            $data_array['question_subtext'] = $this->registry->main_class->processing_data($data_array['question_subtext']);
        } else {
            $data_array['question_subtext'] = 'NULL';
        }
        $sql = "SELECT max(question_priority) FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " WHERE questionnaire_id = "
               . $data_array['questionnaire_id'];
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $question_priority = intval($result->fields['0']) + 1;
            }
        }
        if (!isset($question_priority)) {
            $question_priority = 1;
        }
        if ($result) {
            $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_questions_" . $this->registry->sitelang, 'question_id');
            $sql = "INSERT INTO " . PREFIX . "_q_questions_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
                   . "question_title,questionnaire_id,type_id,question_priority,question_subtext,question_obligatory) VALUES (" . $sequence_array['sequence_value_string']
                   . "" . $data_array['title'] . "," . $data_array['questionnaire_id'] . "," . $data_array['question_type'] . ",'" . $question_priority . "',"
                   . $data_array['question_subtext'] . ",'" . $data_array['question_obligatory'] . "')";
            $result = $this->db->Execute($sql);
        }
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view|" . $data_array['questionnaire_id']);
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|questions"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire|" . $data_array['questionnaire_id'] . "|group"
        );
        $this->result['questionnaire_id'] = $data_array['questionnaire_id'];
        $this->result['questionnaires_message'] = 'question_add_ok';
    }


    public function item_or_answer_add_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'title',
                'questionnaire_add_type',
            ];
            $keys2 = [
                'questionnaire_id',
                'question_id',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_id']) || empty($data_array['question_id']) || empty($data_array['title']) || empty($data_array['questionnaire_add_type'])) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['title'] = $this->registry->main_class->processing_data($data_array['title']);
        if ($data_array['questionnaire_add_type'] === 'question_item_add') {
            $sql = "SELECT max(question_item_priority) FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " WHERE question_id = "
                   . $data_array['question_id'];
        } else {
            $sql = "SELECT max(question_answer_priority) FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " WHERE question_id = "
                   . $data_array['question_id'];
        }
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $priority = intval($result->fields['0']) + 1;
            }
        }
        if (!isset($priority)) {
            $priority = 1;
        }
        if ($result) {
            if ($data_array['questionnaire_add_type'] === 'question_item_add') {
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_question_items_" . $this->registry->sitelang, 'question_item_id');
                $sql = "INSERT INTO " . PREFIX . "_q_question_items_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
                       . "question_id,question_item_title,question_item_priority) VALUES (" . $sequence_array['sequence_value_string'] . "" . $data_array['question_id']
                       . "," . $data_array['title'] . "," . $priority . ")";
            } else {
                $sequence_array = $this->registry->main_class->db_process_sequence(PREFIX . "_question_answers_" . $this->registry->sitelang, 'question_answer_id');
                $sql = "INSERT INTO " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . "(" . $sequence_array['field_name_string']
                       . "question_id,question_answer,question_answer_priority) VALUES (" . $sequence_array['sequence_value_string'] . "" . $data_array['question_id']
                       . ","
                       . $data_array['title'] . "," . $priority . ")";
            }
            $result = $this->db->Execute($sql);
        }
        if ($result === false) {
            $error_message = $this->db->ErrorMsg();
            $error_code = $this->db->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view|" . $data_array['questionnaire_id']);
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|questions"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire|" . $data_array['questionnaire_id'] . "|group"
        );
        $this->result['questionnaire_id'] = $data_array['questionnaire_id'];
        $this->result['questionnaires_message'] = $data_array['questionnaire_add_type'] . '_ok';
    }


    public function get_question_item_or_answer($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (empty($array['questionnaire_action'])) {
            $this->error = 'unknown_questionnaire_action';

            return;
        }
        $item = 'question_item_id';
        if ($array['questionnaire_action'] == 'question_answer_edit') {
            $item = 'question_answer_id';
        }
        if (!empty($array)) {
            $keys = [
                'questionnaire_id',
                'question_id',
                $item,
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_id'])) {
            $this->error = 'empty_questionnaire';

            return;
        }
        if (empty($data_array['question_id'])) {
            $this->error = 'empty_question';

            return;
        }
        if ($array['questionnaire_action'] == 'question_item_edit' && empty($data_array[$item])) {
            $this->error = 'empty_question_item';

            return;
        }
        if ($array['questionnaire_action'] == 'question_answer_edit' && empty($data_array[$item])) {
            $this->error = 'empty_question_answer';

            return;
        }
        $fields = "c.question_item_id,c.question_item_title";
        $table = "q_question_items";
        $clause = "b.question_id=c.question_id and c.question_item_id = " . $data_array[$item];
        if ($array['questionnaire_action'] == 'question_answer_edit') {
            $fields = "c.question_answer_id,c.question_answer";
            $table = "q_question_answers";
            $clause = "b.question_id=c.question_id and c.question_answer_id = " . $data_array[$item] . "";
        }
        $sql = "SELECT a.questionnaire_id,a.questionnaire_name,b.question_id,b.question_title," . $fields . " FROM " . PREFIX . "_q_questionnaires_"
               . $this->registry->sitelang . " a LEFT OUTER JOIN " . PREFIX . "_q_questions_" . $this->registry->sitelang
               . " b ON (a.questionnaire_id=b.questionnaire_id and b.question_id = " . $data_array['question_id'] . ") LEFT OUTER JOIN " . PREFIX . "_" . $table . "_"
               . $this->registry->sitelang . " c ON (" . $clause . ") WHERE a.questionnaire_id = " . $data_array['questionnaire_id'];
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $questionnaire_id = 0;
        if (isset($result->fields['0'])) {
            $questionnaire_id = intval($result->fields['0']);
        }
        if ($questionnaire_id < 1) {
            $this->error = 'questionnaire_not_found';

            return false;
        }
        $question_id = intval($result->fields['2']);
        if ($question_id < 1) {
            $this->error = 'question_not_found';

            return false;
        }
        $id = intval($result->fields['4']);
        if ($id < 1) {
            $this->error = 'question_item_not_found';
            if ($array['questionnaire_action'] == 'question_answer_edit') {
                $this->error = 'question_answer_not_found';
            }

            return false;
        }
        $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $question_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
        $title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
        $this->result['questionnaires_all'] = [
            'questionnaire_id'   => $questionnaire_id,
            'questionnaire_name' => $questionnaire_name,
            'question_id'        => $question_id,
            'question_title'     => $question_title,
            $item                => $id,
            'title'              => $title,
        ];
        $this->result['questionnaire_action'] = $array['questionnaire_action'];
    }


    public function question_or_item_or_answer_edit_inbase($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (empty($array['questionnaire_action'])) {
            $this->error = 'unknown_questionnaire_action';

            return;
        }
        $item = 'question_item_id';
        if ($array['questionnaire_action'] == 'question_answer_edit') {
            $item = 'question_answer_id';
        } elseif ($array['questionnaire_action'] == 'question_edit') {
            $item = 'question_id';
        }
        if (!empty($array)) {
            $keys = [
                'title',
                'questionnaire_action',
            ];
            $keys2 = [
                'questionnaire_id',
                'question_id',
                $item,
            ];
            if ($array['questionnaire_action'] == 'question_edit') {
                $keys = array_merge($keys, ['question_subtext']);
                $keys2 = array_merge($keys2, ['question_type', 'question_obligatory']);
            }
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['questionnaire_id'])) {
            $this->error = 'empty_questionnaire';

            return;
        }
        if (empty($data_array['question_id'])) {
            $this->error = 'empty_question';

            return;
        }
        if ($data_array['questionnaire_action'] == 'question_item_edit' && empty($data_array[$item])) {
            $this->error = 'empty_question_item';

            return;
        }
        if ($data_array['questionnaire_action'] == 'question_answer_edit' && empty($data_array[$item])) {
            $this->error = 'empty_question_answer';

            return;
        }
        if (empty($data_array['title'])
            || ($data_array['questionnaire_action'] == 'question_edit'
                && (empty($data_array['question_type'])
                    || !isset($data_array['question_obligatory'])
                    || ($this->check_need_question_subtext($data_array['question_type']) && empty($data_array['question_subtext']))))
        ) {
            $this->error = 'not_all_data';

            return;
        }
        $data_array['title'] = $this->registry->main_class->processing_data($data_array['title']);
        $fields = "question_item_title = " . $data_array['title'];
        $table = "q_question_items";
        $clause = "question_item_id = '" . $data_array[$item] . "'";
        if ($data_array['questionnaire_action'] == 'question_answer_edit') {
            $fields = "question_answer = " . $data_array['title'];
            $table = "q_question_answers";
            $clause = "question_answer_id = '" . $data_array[$item] . "'";
        } elseif ($data_array['questionnaire_action'] == 'question_edit') {
            if ($this->check_need_question_subtext($data_array['question_type'])) {
                $data_array['question_subtext'] = $this->registry->main_class->processing_data($data_array['question_subtext']);
            } else {
                $data_array['question_subtext'] = 'NULL';
            }
            $fields = "question_title = " . $data_array['title'] . ", question_subtext = " . $data_array['question_subtext'] . ", question_obligatory = '"
                      . $data_array['question_obligatory'] . "'";
            $table = "q_questions";
            $clause = "question_id = '" . $data_array[$item] . "' and questionnaire_id = '" . $data_array['questionnaire_id'] . "'";
        }
        $sql = "UPDATE " . PREFIX . "_" . $table . "_" . $this->registry->sitelang . " SET " . $fields . " WHERE " . $clause;
        $result = $this->db->Execute($sql);
        $num_result = $this->db->Affected_Rows();
        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view|" . $data_array['questionnaire_id']);
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|questions"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire|" . $data_array['questionnaire_id'] . "|group"
        );
        if ($data_array['questionnaire_action'] == 'question_edit') {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question_edit|"
                      . $data_array['question_id']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|"
                      . $data_array['question_id'] . "|question_item_edit"
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|"
                      . $data_array['question_id'] . "|question_answer_edit"
            );
        } else {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|"
                      . $data_array['question_id'] . "|" . $data_array['questionnaire_action'] . "|" . $data_array[$item]
            );
        }
        $this->result['questionnaire_id'] = $data_array['questionnaire_id'];
        $this->result['questionnaires_message'] = $data_array['questionnaire_action'] . '_done';
    }


    public function question_or_item_or_answer_delete($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'type',
                'action',
            ];
            $keys2 = [
                'questionnaire_id',
                'question_id',
                'question_item_id',
                'question_answer_id',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($this->registry->main_class->format_striptags($value));
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['type']) || empty($data_array['action'])
            || !in_array(
                $data_array['type'], [
                    'question_delete',
                    'question_item_delete',
                    'question_answer_delete',
                ]
            )
            || !in_array($data_array['action'], ['confirm_delete', 'delete'])
        ) {
            $this->error = 'empty_data';

            return;
        }
        if ($data_array['action'] != "delete") {
            if ($data_array['type'] == 'question_delete') {
                if (empty($data_array['question_id'])) {
                    $this->error = 'empty_question';

                    return;
                }
                $delete_name = 'question_id';
                $sql = "SELECT question_id,question_title,questionnaire_id FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang
                       . " WHERE question_id = '" . $data_array['question_id'] . "'";
            } elseif ($data_array['type'] == 'question_item_delete') {
                if (empty($data_array['question_item_id'])) {
                    $this->error = 'empty_question_item';

                    return;
                }
                $delete_name = 'question_item_id';
                $sql = "SELECT a.question_item_id,a.question_item_title,b.questionnaire_id FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang
                       . " a," . PREFIX . "_q_questions_" . $this->registry->sitelang . " b WHERE a.question_id = b.question_id and a.question_item_id = '"
                       . $data_array['question_item_id'] . "'";
            } elseif ($data_array['type'] == 'question_answer_delete') {
                if (empty($data_array['question_answer_id'])) {
                    $this->error = 'empty_question_answer';

                    return;
                }
                $delete_name = 'question_answer_id';
                $sql = "SELECT a.question_answer_id,a.question_answer,b.questionnaire_id FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang
                       . " a," . PREFIX . "_q_questions_" . $this->registry->sitelang . " b WHERE a.question_id = b.question_id and a.question_answer_id = '"
                       . $data_array['question_answer_id'] . "'";
            }
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = ["code" => $error_code, "message" => $error_message];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            $row_exist = 0;
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            }
            if ($row_exist < 1) {
                if ($data_array['type'] == 'question_delete') {
                    $this->error = 'question_not_found';
                } elseif ($data_array['type'] == 'question_item_delete') {
                    $this->error = 'question_item_not_found';
                } elseif ($data_array['type'] == 'question_answer_delete') {
                    $this->error = 'question_answer_not_found';
                }

                return;
            }
            $delete_id = intval($result->fields['0']);
            $delete_title = $this->registry->main_class->format_htmlspecchars(
                $this->registry->main_class->extracting_data($result->fields['1'])
            );
            $questionnaire_id = intval($result->fields['2']);
            $this->result['delete_id'] = $delete_id;
            $this->result['delete_name'] = $delete_name;
            $this->result['delete_title'] = $delete_title;
            $this->result['delete_type'] = $data_array['type'];
            $this->result['questionnaire_id'] = $questionnaire_id;
            $this->result['questionnaires_message'] = $data_array['action'] . '_data';

            return;
        }
        if (empty($data_array['questionnaire_id'])) {
            $this->error = 'empty_questionnaire';

            return;
        }
        if ($data_array['type'] == 'question_delete') {
            if (empty($data_array['question_id'])) {
                $this->error = 'empty_question';

                return;
            }
            $sql_for_priority = "SELECT question_id,question_priority FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " WHERE question_id = '"
                                . $data_array['question_id'] . "' and questionnaire_id = '" . $data_array['questionnaire_id'] . "'";
            $result_for_priority = $this->db->Execute($sql_for_priority);
            if (!$result_for_priority) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            if (isset($result_for_priority->fields['0'])) {
                $row_exist = intval($result_for_priority->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist < 1) {
                $this->error = 'question_not_found';

                return;
            }
            $question_priority = intval($result_for_priority->fields['1']);
            $sql1 = "UPDATE " . PREFIX . "_q_questions_" . $this->registry->sitelang
                    . " SET question_priority = question_priority-1 WHERE questionnaire_id = '" . $data_array['questionnaire_id'] . "' and question_priority > '"
                    . $question_priority . "'";
            $sql2 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " WHERE question_item_id IN (SELECT a.question_item_id FROM "
                    . PREFIX . "_q_question_items_" . $this->registry->sitelang . " a," . PREFIX . "_q_questions_" . $this->registry->sitelang
                    . " b WHERE a.question_id = b.question_id and a.question_id = '" . $data_array['question_id'] . "' and b.questionnaire_id = '"
                    . $data_array['questionnaire_id'] . "')";
            $sql3 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " WHERE question_id = '" . $data_array['question_id']
                    . "' and questionnaire_id = '" . $data_array['questionnaire_id'] . "'";
            $sql4 = "DELETE FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " WHERE question_id = '" . $data_array['question_id']
                    . "' and question_id IN (SELECT a.question_id FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " a WHERE a.question_id = '"
                    . $data_array['question_id'] . "' and a.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
            $sql5 = "DELETE FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " WHERE question_id = '" . $data_array['question_id']
                    . "' and question_id IN (SELECT a.question_id FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " a WHERE a.question_id = '"
                    . $data_array['question_id'] . "' and a.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
            $sql = "DELETE FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang . " WHERE question_id = '" . $data_array['question_id']
                   . "' and questionnaire_id = '" . $data_array['questionnaire_id'] . "'";
        } elseif ($data_array['type'] == 'question_item_delete') {
            if (empty($data_array['question_item_id'])) {
                $this->error = 'empty_question_item';

                return;
            }
            $sql_for_priority = "SELECT question_id,question_item_priority FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang
                                . " WHERE question_item_id = '" . $data_array['question_item_id'] . "' and question_id IN (SELECT a.question_id FROM " . PREFIX
                                . "_q_questions_"
                                . $this->registry->sitelang . " a WHERE a.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
            $result_for_priority = $this->db->Execute($sql_for_priority);
            if (!$result_for_priority) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            if (isset($result_for_priority->fields['0'])) {
                $row_exist = intval($result_for_priority->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist < 1) {
                $this->error = 'question_item_not_found';

                return;
            }
            $question_id = intval($result_for_priority->fields['0']);
            $question_item_priority = intval($result_for_priority->fields['1']);
            $sql1 = "UPDATE " . PREFIX . "_q_question_items_" . $this->registry->sitelang
                    . " SET question_item_priority = question_item_priority-1 WHERE question_id = '" . $question_id . "' and question_item_priority > '"
                    . $question_item_priority . "'";
            $sql2 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang
                    . " WHERE question_item_id IN (SELECT a.question_item_id FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " a," . PREFIX
                    . "_q_questions_" . $this->registry->sitelang . " b WHERE a.question_id = b.question_id and a.question_item_id = '" . $data_array['question_item_id']
                    . "' and b.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
            $sql = "DELETE FROM " . PREFIX . "_q_question_items_" . $this->registry->sitelang . " WHERE question_item_id = '"
                   . $data_array['question_item_id'] . "' and question_id IN (SELECT a.question_id FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang
                   . " a WHERE a.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
        } elseif ($data_array['type'] == 'question_answer_delete') {
            if (empty($data_array['question_answer_id'])) {
                $this->error = 'empty_question_answer';

                return;
            }
            $sql_for_priority = "SELECT question_id,question_answer_priority FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang
                                . " WHERE question_answer_id = '" . $data_array['question_answer_id'] . "' and question_id IN (SELECT a.question_id FROM " . PREFIX
                                . "_q_questions_"
                                . $this->registry->sitelang . " a WHERE a.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
            $result_for_priority = $this->db->Execute($sql_for_priority);
            if (!$result_for_priority) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            if (isset($result_for_priority->fields['0'])) {
                $row_exist = intval($result_for_priority->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist < 1) {
                $this->error = 'question_answer_not_found';

                return;
            }
            $question_id = intval($result_for_priority->fields['0']);
            $question_answer_priority = intval($result_for_priority->fields['1']);
            $sql1 = "UPDATE " . PREFIX . "_q_question_answers_" . $this->registry->sitelang
                    . " SET question_answer_priority = question_answer_priority-1 WHERE question_id = '" . $question_id . "' and question_answer_priority > '"
                    . $question_answer_priority . "'";
            $sql2 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang
                    . " WHERE question_answer_id IN (SELECT a.question_answer_id FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " a," . PREFIX
                    . "_q_questions_" . $this->registry->sitelang . " b WHERE a.question_id = b.question_id and a.question_answer_id = '"
                    . $data_array['question_answer_id']
                    . "' and b.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
            $sql = "DELETE FROM " . PREFIX . "_q_question_answers_" . $this->registry->sitelang . " WHERE question_answer_id = '"
                   . $data_array['question_answer_id'] . "' and question_id IN (SELECT a.question_id FROM " . PREFIX . "_q_questions_" . $this->registry->sitelang
                   . " a WHERE a.questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
        }
        $this->db->StartTrans();
        $result1 = $this->db->Execute($sql1);
        if ($result1 === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
        }
        $result2 = $this->db->Execute($sql2);
        if ($result2 === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
        }
        $result3 = true;
        $result4 = true;
        $result5 = true;
        if ($data_array['type'] == 'question_delete') {
            $result3 = $this->db->Execute($sql3);
            if ($result3 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $result4 = $this->db->Execute($sql4);
            if ($result4 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $result5 = $this->db->Execute($sql5);
            if ($result5 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
        }
        $result = $this->db->Execute($sql);
        if ($result === false) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
        } else {
            $num_result = $this->db->Affected_Rows();
        }
        if ($result1 === false || $result2 === false || $result3 === false || $result4 === false || $result5 === false) {
            $result = false;
        }
        $this->db->CompleteTrans();
        if ($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|questionnaires|view|" . $data_array['questionnaire_id']);
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|questions"
        );
        $this->registry->main_class->clearCache(
            null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details|questionnaire|" . $data_array['questionnaire_id'] . "|group"
        );
        if ($data_array['type'] == 'question_delete') {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question_edit|"
                      . $data_array['question_id']
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|"
                      . $data_array['question_id'] . "|question_item_edit"
            );
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|"
                      . $data_array['question_id'] . "|question_answer_edit"
            );
        } elseif ($data_array['type'] == 'question_item_delete') {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|" . $question_id
                      . "|question_item_edit|" . $data_array['question_item_id']
            );
        } elseif ($data_array['type'] == 'question_answer_delete') {
            $this->registry->main_class->clearCache(
                null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|questionnaire|" . $data_array['questionnaire_id'] . "|question|" . $question_id
                      . "|question_answer_edit|" . $data_array['question_answer_id']
            );
        }
        $this->result['questionnaire_id'] = $data_array['questionnaire_id'];
        $this->result['questionnaires_message'] = $data_array['type'] . '_done';
    }


    public function results($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $num_page = 1;
        if (isset($array['num_page']) && intval($array['num_page']) != 0) {
            $num_page = intval($array['num_page']);
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql = "SELECT count(*) FROM " . PREFIX . "_q_voted_groups_" . $this->registry->sitelang;
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT a.voted_group_id,a.group_id,b.group_name,a.questionnaire_id,c.questionnaire_name FROM " . PREFIX . "_q_voted_groups_"
                   . $this->registry->sitelang . " a," . PREFIX . "_q_groups_" . $this->registry->sitelang . " b," . PREFIX . "_q_questionnaires_"
                   . $this->registry->sitelang . " c  WHERE a.group_id = b.group_id and a.questionnaire_id = c.questionnaire_id ORDER BY a.voted_group_id ASC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if ($row_exist < 1) {
            $this->result['voted_groups'] = "no_voted_groups";
            $this->result['pages_menu'] = "no";

            return;
        }
        while (!$result->EOF) {
            $id = intval($result->fields['0']);
            $group_id = intval($result->fields['1']);
            $group_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
            $questionnaire_id = intval($result->fields['3']);
            $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
            $voted_groups[] = [
                'id'                 => $id,
                'group_id'           => $group_id,
                'group_name'         => $group_name,
                'questionnaire_id'   => $questionnaire_id,
                'questionnaire_name' => $questionnaire_name,
            ];
            $result->MoveNext();
        }
        $this->result['voted_groups'] = $voted_groups;
        if (isset($num_rows)) {
            $num_pages = @ceil($num_rows / $num_string_rows);
        } else {
            $num_pages = 0;
        }
        if ($num_pages > 1) {
            if ($num_page > 1) {
                $prevpage = $num_page - 1;
                $this->result['prevpage'] = $prevpage;
            } else {
                $this->result['prevpage'] = "no";
            }
            for ($i = 1; $i < $num_pages + 1; $i++) {
                if ($i == $num_page) {
                    $html[] = ["number" => $i, "param1" => "1"];
                } else {
                    $pagelink = 5;
                    if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "2"];
                    }
                    if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "3"];
                    }
                    if (($i == 1) && ($num_page > $pagelink + 1)) {
                        $html[] = ["number" => $i, "param1" => "4"];
                    }
                }
            }
            if ($num_page < $num_pages) {
                $nextpage = $num_page + 1;
                $this->result['nextpage'] = $nextpage;
            } else {
                $this->result['nextpage'] = "no";
            }
            $this->result['pages_menu'] = $html;
            $this->result['total_voted_groups'] = $num_rows;
            $this->result['num_pages'] = $num_pages;
        } else {
            $this->result['pages_menu'] = "no";
        }
    }


    public function results_details($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'group_id',
                'questionnaire_id',
                'results_by_persons',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['group_id']) || empty($data_array['questionnaire_id'])) {
            $this->error = 'empty_data';

            return;
        }
        $sql = "SELECT count(*) as voted_persons FROM " . PREFIX . "_q_voted_persons_" . $this->registry->sitelang
               . " WHERE voted_group_id IN (SELECT voted_group_id FROM " . PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " WHERE group_id = '"
               . $data_array['group_id'] . "' and questionnaire_id = '" . $data_array['questionnaire_id'] . "')";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        $this->result['voted_persons'] = 0;
        $this->result['group_id'] = $data_array['group_id'];
        if (isset($result->fields['0']) && intval($result->fields['0']) > 0) {
            $this->result['voted_persons'] = intval($result->fields['0']);
        }
        $questionnaire_results = true;
        if (!empty($data_array['results_by_persons'])) {
            $questionnaire_results = 'results_by_persons';
        }
        $results = $this->get_questions($data_array['questionnaire_id'], $data_array['group_id'], $questionnaire_results);
        if ($results) {
            $this->result = array_merge($results, $this->result);
        }
    }


    public function results_person_text_answers($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (!empty($array)) {
            $keys = [
                'group_id',
                'questionnaire_id',
                'question_id',
                'person_id',
                'num_page',
            ];
            foreach ($array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if (empty($data_array['group_id'])) {
            $this->error = 'empty_group';

            return;
        }
        if (empty($data_array['questionnaire_id'])) {
            $this->error = 'empty_questionnaire';

            return;
        }
        if (empty($data_array['question_id'])) {
            $this->error = 'empty_question';

            return;
        }
        $num_page = 1;
        if (isset($data_array['num_page']) && intval($data_array['num_page']) > 0) {
            $num_page = $data_array['num_page'];
        }
        $num_string_rows = SYSTEMDK_ADMINROWS_PERPAGE;
        $offset = ($num_page - 1) * $num_string_rows;
        $sql_add = false;
        if ($data_array['person_id'] > 0) {
            $sql_add = "and a.person_id = '" . $data_array['person_id'] . "'";
        }
        $sql = "SELECT count(*) FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " a," . PREFIX . "_q_groups_" . $this->registry->sitelang . " b,"
               . PREFIX . "_q_questionnaires_" . $this->registry->sitelang . " c," . PREFIX . "_q_questions_" . $this->registry->sitelang
               . " d  WHERE a.group_id = b.group_id and a.questionnaire_id = c.questionnaire_id and a.question_id = d.question_id and a.group_id = '"
               . $data_array['group_id'] . "' and a.questionnaire_id = '" . $data_array['questionnaire_id'] . "' and a.question_id = '" . $data_array['question_id']
               . "' "
               . $sql_add . " and a.answer_text is not NULL";
        $result = $this->db->Execute($sql);
        if ($result) {
            $num_rows = intval($result->fields['0']);
            $sql = "SELECT a.answer_id,a.answer_text,b.group_id,b.group_name,c.questionnaire_id,c.questionnaire_name,d.question_id,d.question_title FROM " . PREFIX
                   . "_q_voted_answers_" . $this->registry->sitelang . " a," . PREFIX . "_q_groups_" . $this->registry->sitelang . " b," . PREFIX . "_q_questionnaires_"
                   . $this->registry->sitelang . " c," . PREFIX . "_q_questions_" . $this->registry->sitelang
                   . " d  WHERE a.group_id = b.group_id and a.questionnaire_id = c.questionnaire_id and a.question_id = d.question_id and a.group_id = '"
                   . $data_array['group_id'] . "' and a.questionnaire_id = '" . $data_array['questionnaire_id'] . "' and a.question_id = '" . $data_array['question_id']
                   . "' " . $sql_add . " and a.answer_text is not NULL ORDER BY a.answer_id ASC";
            $result = $this->db->SelectLimit($sql, $num_string_rows, $offset);
        }
        if (!$result) {
            $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist == 0 && $num_page > 1) {
            $this->error = 'unknown_page';

            return;
        }
        if ($row_exist < 1) {
            $this->result['person_text_answers'] = "no_person_text_answers";
            $this->result['pages_menu'] = "no";

            return;
        }
        while (!$result->EOF) {
            $answer_id = intval($result->fields['0']);
            $answer_text = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
            if (!isset($person_text_answers['person_text_answers_data'])) {
                $group_id = intval($result->fields['2']);
                $group_name = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['3'])
                );
                $questionnaire_id = intval($result->fields['4']);
                $questionnaire_name = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['5'])
                );
                $question_id = intval($result->fields['6']);
                $question_title = $this->registry->main_class->format_htmlspecchars(
                    $this->registry->main_class->extracting_data($result->fields['7'])
                );
                $person_text_answers['person_text_answers_data'] = [
                    'group_id'           => $group_id,
                    'group_name'         => $group_name,
                    'questionnaire_id'   => $questionnaire_id,
                    'questionnaire_name' => $questionnaire_name,
                    'question_id'        => $question_id,
                    'question_title'     => $question_title,
                ];
            }
            if (!empty($answer_text)) { // workaround for Oracle when empty clob
                $person_text_answers['person_text_answers'][] = [
                    'answer_id'   => $answer_id,
                    'answer_text' => $answer_text,
                ];
            }
            $result->MoveNext();
        }
        $this->result = $person_text_answers;
        if ($data_array['person_id'] > 0) {
            $this->result['person_id'] = $data_array['person_id'];
        }
        if (isset($num_rows)) {
            $num_pages = @ceil($num_rows / $num_string_rows);
        } else {
            $num_pages = 0;
        }
        if ($num_pages > 1) {
            if ($num_page > 1) {
                $prevpage = $num_page - 1;
                $this->result['prevpage'] = $prevpage;
            } else {
                $this->result['prevpage'] = "no";
            }
            for ($i = 1; $i < $num_pages + 1; $i++) {
                if ($i == $num_page) {
                    $html[] = ["number" => $i, "param1" => "1"];
                } else {
                    $pagelink = 5;
                    if (($i > $num_page) && ($i < $num_page + $pagelink) || ($i < $num_page) && ($i > $num_page - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "2"];
                    }
                    if (($i == $num_pages) && ($num_page < $num_pages - $pagelink)) {
                        $html[] = ["number" => $i, "param1" => "3"];
                    }
                    if (($i == 1) && ($num_page > $pagelink + 1)) {
                        $html[] = ["number" => $i, "param1" => "4"];
                    }
                }
            }
            if ($num_page < $num_pages) {
                $nextpage = $num_page + 1;
                $this->result['nextpage'] = $nextpage;
            } else {
                $this->result['nextpage'] = "no";
            }
            $this->result['pages_menu'] = $html;
            $this->result['total_person_text_answers'] = $num_rows;
            $this->result['num_pages'] = $num_pages;
        } else {
            $this->result['pages_menu'] = "no";
        }
    }


    public function results_delete($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $action = "";
        if (isset($array['get_action'])) {
            $action = trim($array['get_action']);
        }
        if (!isset($array['get_action']) && isset($array['post_action'])) {
            $action = trim($array['post_action']);
        }
        $voted_group_id = 0;
        if (isset($array['get_voted_group_id'])) {
            $voted_group_id = intval($array['get_voted_group_id']);
        }
        if ($voted_group_id == 0 && isset($array['post_voted_group_id']) && is_array($array['post_voted_group_id']) && count($array['post_voted_group_id']) > 0) {
            for ($i = 0, $size = count($array['post_voted_group_id']); $i < $size; ++$i) {
                if ($i == 0) {
                    $voted_group_id = "'" . intval($array['post_voted_group_id'][$i]) . "'";
                } else {
                    $voted_group_id .= ",'" . intval($array['post_voted_group_id'][$i]) . "'";
                }
            }
        } else {
            $voted_group_id = "'" . $voted_group_id . "'";
        }
        $action = $this->registry->main_class->format_striptags($action);
        if (isset($array['type'])) {
            $type = $this->registry->main_class->format_striptags($array['type']);
        }
        if (!isset($voted_group_id) || $action == "" || $voted_group_id == "'0'" || empty($type)) {
            $this->error = 'empty_data';

            return;
        }
        if ($action == "confirm_delete") {
            $sql = "SELECT a.voted_group_id,b.group_name,c.questionnaire_name FROM " . PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " a," . PREFIX
                   . "_q_groups_" . $this->registry->sitelang . " b," . PREFIX . "_q_questionnaires_" . $this->registry->sitelang
                   . " c WHERE a.group_id = b.group_id and a.questionnaire_id = c.questionnaire_id and a.voted_group_id IN (" . $voted_group_id . ")";
            $result = $this->db->Execute($sql);
            if (!$result) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
                $this->error = 'sql_error';
                $this->error_array = $error;

                return;
            }
            $row_exist = 0;
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            }
            if ($row_exist < 1) {
                $this->error = 'empty_data';

                return;
            }
            while (!$result->EOF) {
                $voted_group_id = intval($result->fields['0']);
                $group_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                $questionnaire_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                $voted_groups_all[] = [
                    'voted_group_id'     => $voted_group_id,
                    'group_name'         => $group_name,
                    'questionnaire_name' => $questionnaire_name,
                ];
                $result->MoveNext();
            }
            $this->result['voted_groups_all'] = $voted_groups_all;
            $this->result['questionnaires_message'] = $type;

            return;
        } elseif ($action == "delete") {
            $this->db->StartTrans();
            $sql1 = "DELETE FROM " . PREFIX . "_q_voted_answers_" . $this->registry->sitelang . " WHERE person_id IN (SELECT person_id FROM " . PREFIX
                    . "_q_voted_persons_" . $this->registry->sitelang . " WHERE voted_group_id IN (" . $voted_group_id . "))";
            //$sql1 = "DELETE FROM ".PREFIX."_q_voted_answers_".$this->registry->sitelang." WHERE person_id IN (SELECT a.person_id FROM ".PREFIX."_q_voted_persons_".$this->registry->sitelang." a INNER JOIN (SELECT b.group_id,b.questionnaire_id FROM ".PREFIX."_q_voted_groups_".$this->registry->sitelang." b WHERE b.voted_group_id IN (".$voted_group_id.")) res ON a.group_id = res.group_id and a.questionnaire_id = res.questionnaire_id)";
            $result1 = $this->db->Execute($sql1);
            if ($result1 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql2 = "DELETE FROM " . PREFIX . "_q_voted_persons_" . $this->registry->sitelang . " WHERE voted_group_id IN (" . $voted_group_id . ")";
            $result2 = $this->db->Execute($sql2);
            if ($result2 === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            }
            $sql3 = "DELETE FROM " . PREFIX . "_q_voted_groups_" . $this->registry->sitelang . " WHERE voted_group_id IN (" . $voted_group_id . ")";
            $result = $this->db->Execute($sql3);
            if ($result === false) {
                $error[] = ["code" => $this->db->ErrorNo(), "message" => $this->db->ErrorMsg()];
            } else {
                $num_result = $this->db->Affected_Rows();
            }
            if ($result1 === false || $result2 === false) {
                $result = false;
            }
            $this->db->CompleteTrans();
        } else {
            $this->error = 'unknown_action';

            return;
        }
        if ($result === false) {
            $this->error = 'sql_error';
            $this->error_array = $error;

            return;
        } elseif ($result !== false && $num_result == 0) {
            $this->error = 'ok';

            return;
        }
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results");
        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|questionnaires|results_details");
        $this->result['questionnaires_message'] = $type . '_done';
    }


    public function config()
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        require_once('../modules/questionnaires/questionnaires_config.inc');
        $this->result['systemdk_questionnaires_organization_id'] = trim(SYSTEMDK_QUESTIONNAIRES_ORGANIZATION_ID);
        $this->result['systemdk_questionnaires_organization_name'] = trim(SYSTEMDK_QUESTIONNAIRES_ORGANIZATION_NAME);
        $this->result['systemdk_questionnaires_voted_method_check'] = trim(SYSTEMDK_QUESTIONNAIRES_VOTED_METHOD_CHECK);
        $this->result['systemdk_questionnaires_voted_control_live_time'] = trim(SYSTEMDK_QUESTIONNAIRES_VOTED_CONTROL_LIVE_TIME);
        $this->result['systemdk_questionnaires_who_can_vote'] = trim(SYSTEMDK_QUESTIONNAIRES_WHO_CAN_VOTE);
    }


    public function config_save($array)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        $keys = [
            'systemdk_questionnaires_organization_id'         => 'string',
            'systemdk_questionnaires_organization_name'       => 'string',
            'systemdk_questionnaires_voted_method_check'      => 'string',
            'systemdk_questionnaires_voted_control_live_time' => 'integer',
            'systemdk_questionnaires_who_can_vote'            => 'string',
        ];
        foreach ($keys as $key => $value) {
            $data_array[$key] = false;
            if (!empty($array[$key])) {
                $data_array[$key] = $value === 'string'
                    ? $this->registry->main_class->format_htmlspecchars_stripslashes_striptags(trim($array[$key]))
                    : intval($array[$key]);
            }
        }
        if (!isset($data_array['systemdk_questionnaires_organization_id']) || !isset($data_array['systemdk_questionnaires_organization_name'])
            || !isset($data_array['systemdk_questionnaires_voted_method_check'])
            || !isset($data_array['systemdk_questionnaires_voted_control_live_time'])
            || !isset($data_array['systemdk_questionnaires_who_can_vote'])
        ) {
            $this->error = 'not_all_data';

            return;
        }
        @chmod("../modules/questionnaires/questionnaires_config.inc", 0777);
        $file = @fopen("../modules/questionnaires/questionnaires_config.inc", "w");
        if (!$file) {
            $this->error = 'conf_no_file';

            return;
        }
        $content = "<?php\n";
        $content .= 'define("SYSTEMDK_QUESTIONNAIRES_ORGANIZATION_ID","' . $data_array['systemdk_questionnaires_organization_id'] . "\");\n";
        $content .= 'define("SYSTEMDK_QUESTIONNAIRES_ORGANIZATION_NAME","' . $data_array['systemdk_questionnaires_organization_name'] . "\");\n";
        $content .= 'define("SYSTEMDK_QUESTIONNAIRES_VOTED_METHOD_CHECK","' . $data_array['systemdk_questionnaires_voted_method_check'] . "\");\n";
        $content .= 'define("SYSTEMDK_QUESTIONNAIRES_VOTED_CONTROL_LIVE_TIME",' . $data_array['systemdk_questionnaires_voted_control_live_time'] . ");\n";
        $content .= 'define("SYSTEMDK_QUESTIONNAIRES_WHO_CAN_VOTE","' . $data_array['systemdk_questionnaires_who_can_vote'] . "\");\n";
        $write_file = @fwrite($file, $content);
        @fclose($file);
        @chmod("../modules/questionnaires/questionnaires_config.inc", 0604);
        if (!$write_file) {
            $this->error = 'conf_no_write';

            return;
        }
        $this->registry->main_class->systemdk_clearcache("modules|questionnaires");
        $this->registry->main_class->systemdk_clearcache("systemadmin|modules|questionnaires|config");
        $this->result = 'config_done';
    }
}
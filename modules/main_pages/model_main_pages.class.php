<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class main_pages extends model_base
{


    private $error;
    private $error_array;
    private $result;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->process_autoload();
    }


    public function process_autoload()
    {
        $now = $this->registry->main_class->get_time();
        $clear = 'no';
        $this->db->Execute(
            "UPDATE " . PREFIX . "_main_pages_" . $this->registry->sitelang
            . " SET main_page_status = '0', main_page_term_expire = NULL, main_page_term_expire_action = NULL WHERE main_page_term_expire is not NULL and main_page_term_expire <= "
            . $now . " and main_page_term_expire_action = 'off'"
        );
        $num_result1 = $this->db->Affected_Rows();
        if ($num_result1 > 0) {
            $clear = "yes";
            $clear_done = 1;
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|edit");
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|main_pages|show");
        }
        $sql = "SELECT main_page_id FROM " . PREFIX . "_main_pages_" . $this->registry->sitelang
               . " WHERE main_page_term_expire is not NULL and main_page_term_expire <= " . $now . " and main_page_term_expire_action = 'delete'";
        $result = $this->db->Execute($sql);
        if ($result) {
            if (isset($result->fields['0'])) {
                $row_exist = intval($result->fields['0']);
            } else {
                $row_exist = 0;
            }
            if ($row_exist > 0) {
                while (!$result->EOF) {
                    $main_page_id = intval($result->fields['0']);
                    $this->db->StartTrans();
                    $this->db->Execute("DELETE FROM " . PREFIX . "_main_pages_" . $this->registry->sitelang . " WHERE main_page_id = '" . $main_page_id . "'");
                    $sql2 = "SELECT main_menu_priority FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang
                            . " WHERE (main_menu_module = 'main_pages' || main_menu_submodule = 'main_pages') and main_menu_add_param = '" . $main_page_id . "'";
                    $result2 = $this->db->Execute($sql2);
                    if ($result2) {
                        if (isset($result2->fields['0'])) {
                            $mainmenu_priority = intval($result2->fields['0']);
                        } else {
                            $mainmenu_priority = 0;
                        }
                        if ($mainmenu_priority > 0) {
                            $this->db->Execute(
                                "UPDATE " . PREFIX . "_main_menu_" . $this->registry->sitelang
                                . " SET main_menu_priority=main_menu_priority-1 WHERE main_menu_priority > " . $mainmenu_priority
                            );
                            $num_result3 = $this->db->Affected_Rows();
                            $sql4 = "DELETE FROM " . PREFIX . "_main_menu_" . $this->registry->sitelang
                                    . " WHERE (main_menu_module = 'main_pages' || main_menu_submodule = 'main_pages') and main_menu_add_param = '" . $main_page_id . "'";
                            $this->db->Execute($sql4);
                            $num_result4 = $this->db->Affected_Rows();
                            if ($num_result4 > 0 || $num_result3 > 0) {
                                $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|menu|show");
                            }
                        }
                    }
                    $this->db->CompleteTrans();
                    if (empty($clear_done)) {
                        $clear = 'yes';
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|edit|" . $main_page_id);
                        $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|modules|main_pages|show|" . $main_page_id);
                    }
                    $result->MoveNext();
                }
            }
        }
        if ($clear == 'yes') {
            $this->registry->main_class->clearCache(null, $this->registry->sitelang . "|systemadmin|modules|main_pages|show");
        }
    }


    public function get_property_value($property)
    {
        if (isset($this->$property) && in_array($property, ['error', 'error_array', 'result'])) {
            return $this->$property;
        }

        return false;
    }


    public function index($main_page_id, $num_page = false)
    {
        $this->result = false;
        $this->error = false;
        $this->error_array = false;
        if (intval($main_page_id) < 1) {
            $this->error = 'empty_data';

            return;
        }
        $sql =
            "SELECT main_page_id,main_page_author,main_page_title,main_page_date,main_page_content,main_page_notes,main_page_value_view,main_page_status,main_page_term_expire,main_page_term_expire_action,(SELECT max(main_menu_name) FROM "
            . PREFIX . "_main_menu_" . $this->registry->sitelang
            . " WHERE (main_menu_module = 'main_pages' or main_menu_submodule = 'main_pages') and main_menu_add_param = '" . $main_page_id . "') as main_menu_name FROM "
            . PREFIX . "_main_pages_" . $this->registry->sitelang . " WHERE main_page_id = '" . $main_page_id . "'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error = 'nopage';
            $this->result['pages_menu'] = 'no';
            $this->result['page_title'] = 'no';
            $this->result['mainpage_all'] = $this->error;

            return;
        }
        if (isset($result->fields['0'])) {
            $row_exist = intval($result->fields['0']);
        } else {
            $row_exist = 0;
        }
        if ($row_exist < 1) {
            $this->error = 'nopage';
            $this->result['pages_menu'] = 'no';
            $this->result['page_title'] = 'no';
            $this->result['mainpage_all'] = $this->error;

            return;
        }
        $mainpage_status = intval($result->fields['7']);
        if ($mainpage_status == 0) {
            $this->error = 'notactive';
            $this->result['pages_menu'] = 'no';
            $this->result['page_title'] = 'no';
            $this->result['mainpage_all'] = $this->error;

            return;
        }
        $main_page_id = intval($result->fields['0']);
        //$mainpage_author = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
        $mainpage_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
        //$mainpage_date = intval($result->fields['3']);
        $mainpage_content = $this->registry->main_class->extracting_data($result->fields['4']);
        $mainpage_notes = $this->registry->main_class->extracting_data($result->fields['5']);
        $mainpage_valueview = intval($result->fields['6']);
        //$mainpage_termexpire = intval($result->fields['8']);
        //$mainpage_termexpireaction = $this->registry->main_class->extracting_data($result->fields['9']);
        $page_title = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
        if (!empty($num_page) && intval($num_page) != 0) {
            $num_page = intval($num_page);
        } else {
            $num_page = 1;
        }
        $mainpage_content = $this->registry->main_class->split_content_by_pages($mainpage_content);
        $count_num_pages = count($mainpage_content);
        if ($num_page > $count_num_pages) {
            $num_page = 1;
        }
        $mainpage_content = $mainpage_content[$num_page - 1];
        $mainpage_content = $this->registry->main_class->search_player_entry($mainpage_content);
        if ($count_num_pages > 1) {
            if ($num_page < $count_num_pages) {
                $next_page = $num_page + 1;
            } else {
                $next_page = "no";
            }
            if ($num_page > 1) {
                $prev_page = $num_page - 1;
            } else {
                $prev_page = "no";
            }
            $pages_menu[] = [
                "current_page"    => $num_page,
                "next_page"       => $next_page,
                "prev_page"       => $prev_page,
                "count_num_pages" => $count_num_pages,
            ];
        } else {
            $pages_menu = 'no';
        }
        $all = [
            "mainpage_id"      => $main_page_id,
            "mainpage_title"   => $mainpage_title,
            "mainpage_content" => $mainpage_content,
            "mainpage_notes"   => $mainpage_notes,
        ];
        if ($mainpage_valueview == "0") {
            $this->result['mainpage_all'] = $all;
        } elseif ($mainpage_valueview == "1" && ($this->registry->main_class->is_user() || $this->registry->main_class->is_admin())) {
            $this->result['mainpage_all'] = $all;
        } elseif ($mainpage_valueview == "2" && $this->registry->main_class->is_admin()) {
            $this->result['mainpage_all'] = $all;
        } elseif ($mainpage_valueview == "3" && (!$this->registry->main_class->is_user() || $this->registry->main_class->is_admin())) {
            $this->result['mainpage_all'] = $all;
        } else {
            $this->error = 'noaccess';
            $this->result['pages_menu'] = 'no';
            $this->result['page_title'] = 'no';
            $this->result['mainpage_all'] = $this->error;

            return;
        }
        $this->result['pages_menu'] = $pages_menu;
        $this->result['page_title'] = $page_title;
    }
}
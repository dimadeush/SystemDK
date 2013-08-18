<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_administrator.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_administrator extends model_base {


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|show|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUADMINISTRATION'));
            $this->displaysysteminfo();
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/systeminfo.html");
        }
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|show|".$this->registry->language);
    }


    private function displaysysteminfo() {
        $phpversion = phpversion();
        $query = $this->db->ServerInfo();
        $sqlversion = $query['description'].' ('.$query['version'].')';
        $register_globals = ini_get('register_globals');
        $safe_mode = ini_get('safe_mode');
        $display_errors = ini_get('display_errors');
        $log_errors = ini_get('log_errors');
        $zlib_compression = ini_get('zlib.output_compression');
        $magic_quotes_gpc = ini_get('magic_quotes_gpc');
        $config_memory_limit = ini_get('memory_limit');
        if(strlen(ini_get('disable_functions')) > 1) {
            $config_disable_functions = ini_get('disable_functions');
        } else {
            $config_disable_functions = "no";
        }
        $config_site_status = SITE_STATUS;
        if(@php_uname()) {
            $config_os_version = php_uname();
        } else {
            $config_os_version = "no";
        }
        $config_maxupload = $this->registry->main_class->formatsize(intval(ini_get('upload_max_filesize')) * 1024 * 1024);
        if(DBTYPE == 'mysqli' or DBTYPE == 'pdo' or DBTYPE == 'mysql' or DBTYPE == 'mysqlt' or DBTYPE == 'maxsql') {
            $config_db_size = 0;
            $sql = "SHOW TABLE STATUS FROM ".DB_NAME;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    while(!$result->EOF) {
                        if(strpos($result->fields['0'],PREFIX."_") !== false) {
                            if(isset($result->fields['17'])) {
                                $config_db_size += $result->fields['6'] + $result->fields['8']; // for mysql < v5, Data_length + Index_length
                            } else {
                                $config_db_size += $result->fields['5'] + $result->fields['7']; //Data_length + Index_length
                            }
                        }
                        $result->MoveNext();
                    }
                    $config_db_size = $this->registry->main_class->formatsize($config_db_size);
                }
            }
            $this->registry->main_class->assign("config_db_size",$config_db_size);
        } else {
            $this->registry->main_class->assign("config_db_size","no");
        }
        if(@disk_free_space(".")) {
            $config_diskfreespace = $this->registry->main_class->formatsize(disk_free_space("."));
        } else {
            $config_diskfreespace = "no";
        }
        $countadminmodules = 0;
        $countmainmodules = 0;
        $dir = dir(__SITE_ADMIN_PATH.'/modules');
        while(false !== ($entry = $dir->read())) {
            if(strpos($entry,".") === 0) {
                continue;
            } elseif(preg_match("/html/i",$entry)) {
                continue;
            } else {
                $countadminmodules++;
            }
        }
        $dir->close();
        $dir = dir(__SITE_PATH.'/modules');
        while(false !== ($entry = $dir->read())) {
            if(strpos($entry,".") === 0) {
                continue;
            } elseif(preg_match("/html/i",$entry)) {
                continue;
            } else {
                $countmainmodules++;
            }
        }
        $dir->close();
        $this->registry->main_class->assign("phpversion",$phpversion);
        $this->registry->main_class->assign("config_os_version",$config_os_version);
        $this->registry->main_class->assign("sqlversion",DBTYPE." ".$sqlversion);
        $this->registry->main_class->assign("systemdk_version",SYSTEMDK_VERSION." ".SYSTEMDK_DESCRIPTION);
        $this->registry->main_class->assign("register_globals",$register_globals);
        $this->registry->main_class->assign("safe_mode",$safe_mode);
        $this->registry->main_class->assign("display_errors",$display_errors);
        $this->registry->main_class->assign("log_errors",$log_errors);
        $this->registry->main_class->assign("zlib_compression",$zlib_compression);
        $this->registry->main_class->assign("magic_quotes_gpc",$magic_quotes_gpc);
        $this->registry->main_class->assign("persistency",DB_PERSISTENCY);
        $this->registry->main_class->assign("caching",SMARTY_CACHING);
        $this->registry->main_class->assign("cache_lifetime",CACHE_LIFETIME);
        $this->registry->main_class->assign("gzip",GZIP);
        $this->registry->main_class->assign("enter_check",ENTER_CHECK);
        $this->registry->main_class->assign("countadminmodules",$countadminmodules);
        $this->registry->main_class->assign("countmainmodules",$countmainmodules);
        $this->registry->main_class->assign("config_site_status",$config_site_status);
        $this->registry->main_class->assign("config_diskfreespace",$config_diskfreespace);
        $this->registry->main_class->assign("config_memory_limit",$config_memory_limit);
        $this->registry->main_class->assign("config_maxupload",$config_maxupload);
        $this->registry->main_class->assign("config_disable_functions",$config_disable_functions);
        $this->registry->main_class->assign("systemdk_account_registration",SYSTEMDK_ACCOUNT_REGISTRATION);
        $this->registry->main_class->assign("systemdk_account_activation",SYSTEMDK_ACCOUNT_ACTIVATION);
        $this->registry->main_class->assign("systemdk_account_maxusers",SYSTEMDK_ACCOUNT_MAXUSERS);
        $this->registry->main_class->assign("systemdk_account_maxactivationdays",SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS);
        $this->registry->main_class->assign("systemdk_feedback_receiver",SYSTEMDK_FEEDBACK_RECEIVER);
        $this->registry->main_class->assign("systemdk_ipantispam",SYSTEMDK_IPANTISPAM);
    }


    public function mainadmin_edit() {
        $admin_id = intval($this->registry->main_class->get_admin_id());
        if(intval($admin_id) == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php&lang=".$this->registry->sitelang);
            exit();
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITPROFILEPAGETITLE'));
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|".$admin_id."|".$this->registry->language)) {
            $sql = "SELECT admin_id,admin_name,admin_surname,admin_website,admin_email,admin_country,admin_city,admin_address,admin_telephone,admin_icq,admin_gmt,admin_notes,admin_viewemail FROM ".PREFIX."_admins WHERE admin_id='".$admin_id."'";
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows > 0) {
                    $admin_id = intval($result->fields['0']);
                    $admin_name = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['1']));
                    $admin_surname = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['2']));
                    $admin_website = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['3']));
                    $admin_email = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['4']));
                    $admin_country = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['5']));
                    $admin_city = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['6']));
                    $admin_address = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['7']));
                    $admin_telephone = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['8']));
                    $admin_icq = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['9']));
                    $admin_gmt = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['10']));
                    $admin_notes = $this->registry->main_class->format_htmlspecchars($this->registry->main_class->extracting_data($result->fields['11']));
                    $admin_viewemail = intval($result->fields['12']);
                    $admin_all[] = array(
                        "admin_id" => $admin_id,
                        "admin_name" => $admin_name,
                        "admin_surname" => $admin_surname,
                        "admin_website" => $admin_website,
                        "admin_email" => $admin_email,
                        "admin_country" => $admin_country,
                        "admin_city" => $admin_city,
                        "admin_address" => $admin_address,
                        "admin_telephone" => $admin_telephone,
                        "admin_icq" => $admin_icq,
                        "admin_gmt" => $admin_gmt,
                        "admin_notes" => $admin_notes,
                        "admin_viewemail" => $admin_viewemail
                    );
                    $this->registry->main_class->assign("admin_all",$admin_all);
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/admin_edit.html");
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|".$admin_id."|".$this->registry->language);
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|notfind|".$this->registry->language)) {
                        $this->registry->main_class->assign("admin_edit","notfind");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|notfind|".$this->registry->language);
                }
            } else {
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
                $this->registry->main_class->assign("error",$error);
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|sqlerror|".$this->registry->language)) {
                    $this->registry->main_class->assign("admin_edit","notinsert");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|sqlerror|".$this->registry->language);
                exit();
            }
        } else {
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|".$admin_id."|".$this->registry->language);
        }
    }


    public function mainadmin_edit_inbase() {
        $admin_edit_id = intval($this->registry->main_class->get_admin_id());
        if($admin_edit_id == 0) {
            $this->registry->main_class->database_close();
            header("Location: index.php&lang=".$this->registry->sitelang);
            exit();
        }
        if(isset($_POST['admin_edit_name'])) {
            $admin_edit_name = trim($_POST['admin_edit_name']);
        }
        if(isset($_POST['admin_edit_surname'])) {
            $admin_edit_surname = trim($_POST['admin_edit_surname']);
        }
        if(isset($_POST['admin_edit_website'])) {
            $admin_edit_website = trim($_POST['admin_edit_website']);
        }
        if(isset($_POST['admin_edit_email'])) {
            $admin_edit_email = trim($_POST['admin_edit_email']);
        }
        if(isset($_POST['admin_edit_newpassword'])) {
            $admin_edit_newpassword = trim($_POST['admin_edit_newpassword']);
        }
        if(isset($_POST['admin_edit_newpassword2'])) {
            $admin_edit_newpassword2 = trim($_POST['admin_edit_newpassword2']);
        }
        if(isset($_POST['admin_edit_country'])) {
            $admin_edit_country = trim($_POST['admin_edit_country']);
        }
        if(isset($_POST['admin_edit_city'])) {
            $admin_edit_city = trim($_POST['admin_edit_city']);
        }
        if(isset($_POST['admin_edit_address'])) {
            $admin_edit_address = trim($_POST['admin_edit_address']);
        }
        if(isset($_POST['admin_edit_telephone'])) {
            $admin_edit_telephone = trim($_POST['admin_edit_telephone']);
        }
        if(isset($_POST['admin_edit_icq'])) {
            $admin_edit_icq = trim($_POST['admin_edit_icq']);
        }
        if(isset($_POST['admin_edit_gmt'])) {
            $admin_edit_gmt = trim($_POST['admin_edit_gmt']);
        }
        if(isset($_POST['admin_edit_notes'])) {
            $admin_edit_notes = trim($_POST['admin_edit_notes']);
        }
        if(isset($_POST['admin_edit_viewemail'])) {
            $admin_edit_viewemail = intval($_POST['admin_edit_viewemail']);
        }
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_ADMINISTRATIONEDITPROFILEPAGETITLE'));
        if((!isset($_POST['admin_edit_name']) or !isset($_POST['admin_edit_surname'])or !isset($_POST['admin_edit_website']) or !isset($_POST['admin_edit_email']) or !isset($_POST['admin_edit_newpassword']) or !isset($_POST['admin_edit_newpassword2']) or !isset($_POST['admin_edit_country']) or !isset($_POST['admin_edit_city']) or !isset($_POST['admin_edit_address']) or !isset($_POST['admin_edit_telephone']) or !isset($_POST['admin_edit_icq']) or !isset($_POST['admin_edit_gmt']) or !isset($_POST['admin_edit_notes']) or !isset($_POST['admin_edit_viewemail'])) or (trim($admin_edit_email) == "" or trim($admin_edit_gmt) == "" or intval($admin_edit_viewemail) === "")) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|notallfields|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_edit","notalldata");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|notallfields|".$this->registry->language);
            exit();
        }
        if($admin_edit_name == "") {
            $admin_edit_name = 'NULL';
        } else {
            $admin_edit_name = $this->registry->main_class->format_striptags($admin_edit_name);
            $admin_edit_name = $this->registry->main_class->processing_data($admin_edit_name);
        }
        if($admin_edit_surname == "") {
            $admin_edit_surname = 'NULL';
        } else {
            $admin_edit_surname = $this->registry->main_class->format_striptags($admin_edit_surname);
            $admin_edit_surname = $this->registry->main_class->processing_data($admin_edit_surname);
        }
        if($admin_edit_website == "") {
            $admin_edit_website = 'NULL';
        } else {
            $admin_edit_website = $this->registry->main_class->format_striptags($admin_edit_website);
            $admin_edit_website = $this->registry->main_class->processing_data($admin_edit_website);
        }
        if($admin_edit_country == "") {
            $admin_edit_country = 'NULL';
        } else {
            $admin_edit_country = $this->registry->main_class->format_striptags($admin_edit_country);
            $admin_edit_country = $this->registry->main_class->processing_data($admin_edit_country);
        }
        if($admin_edit_city == "") {
            $admin_edit_city = 'NULL';
        } else {
            $admin_edit_city = $this->registry->main_class->format_striptags($admin_edit_city);
            $admin_edit_city = $this->registry->main_class->processing_data($admin_edit_city);
        }
        if($admin_edit_address == "") {
            $admin_edit_address = 'NULL';
        } else {
            $admin_edit_address = $this->registry->main_class->format_striptags($admin_edit_address);
            $admin_edit_address = $this->registry->main_class->processing_data($admin_edit_address);
        }
        if($admin_edit_telephone == "") {
            $admin_edit_telephone = 'NULL';
        } else {
            $admin_edit_telephone = $this->registry->main_class->format_striptags($admin_edit_telephone);
            $admin_edit_telephone = $this->registry->main_class->processing_data($admin_edit_telephone);
        }
        if($admin_edit_icq == "") {
            $admin_edit_icq = 'NULL';
        } else {
            $admin_edit_icq = $this->registry->main_class->format_striptags($admin_edit_icq);
            $admin_edit_icq = $this->registry->main_class->processing_data($admin_edit_icq);
        }
        if($admin_edit_notes == "") {
            $admin_edit_notes = 'NULL';
        } else {
            $admin_edit_notes = $this->registry->main_class->format_striptags($admin_edit_notes);
            $admin_edit_notes = $this->registry->main_class->processing_data($admin_edit_notes);
        }
        $admin_edit_email = $this->registry->main_class->format_striptags($admin_edit_email);
        if(!$this->registry->main_class->check_email($this->registry->main_class->checkneed_stripslashes($admin_edit_email))) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|incorrectemail|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_update","incorrectemail");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/modules/administrators/adminupdate.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|administrators|error|incorrectemail|".$this->registry->language);
            exit();
        }
        $admin_edit_email = $this->registry->main_class->processing_data($admin_edit_email);
        $admin_edit_gmt = $this->registry->main_class->format_striptags($admin_edit_gmt);
        $admin_edit_gmt = $this->registry->main_class->processing_data($admin_edit_gmt);
        if(isset($admin_edit_newpassword) and $admin_edit_newpassword !== "") {
            $admin_edit_newpassword = $this->registry->main_class->format_striptags($admin_edit_newpassword);
            $admin_edit_newpassword = $this->registry->main_class->processing_data($admin_edit_newpassword);
            $admin_edit_newpassword = substr(substr($admin_edit_newpassword,0,-1),1);
            $admin_edit_newpassword2 = $this->registry->main_class->format_striptags($admin_edit_newpassword2);
            $admin_edit_newpassword2 = $this->registry->main_class->processing_data($admin_edit_newpassword2);
            $admin_edit_newpassword2 = substr(substr($admin_edit_newpassword2,0,-1),1);
            if($admin_edit_newpassword !== $admin_edit_newpassword2 or $admin_edit_newpassword == "") {
                if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|pass|".$this->registry->language)) {
                    $this->registry->main_class->assign("admin_edit","adderrorpass");
                    $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                    $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
                }
                $this->registry->main_class->database_close();
                $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|pass|".$this->registry->language);
                exit();
            } else {
                $sql0 = "SELECT admin_id FROM ".PREFIX."_admins WHERE admin_id<>'".$admin_edit_id."' AND admin_email=".$admin_edit_email;
                $result0 = $this->db->Execute($sql0);
                if($result0) {
                    if(isset($result0->fields['0'])) {
                        $numrows = intval($result0->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows == 0) {
                        $admin_edit_password = md5($admin_edit_newpassword);
                        $sql = "UPDATE ".PREFIX."_admins SET admin_name=$admin_edit_name,admin_surname=$admin_edit_surname,admin_website=$admin_edit_website,admin_email=$admin_edit_email,admin_password='$admin_edit_password',admin_country=$admin_edit_country,admin_city=$admin_edit_city,admin_address=$admin_edit_address,admin_telephone=$admin_edit_telephone,admin_icq=$admin_edit_icq,admin_gmt=$admin_edit_gmt,admin_notes=$admin_edit_notes,admin_viewemail='$admin_edit_viewemail' WHERE admin_id='$admin_edit_id'";
                        $insert_result = $this->db->Execute($sql);
                        $num_result = $this->db->Affected_Rows();
                        if($insert_result === false) {
                            $error_message = $this->db->ErrorMsg();
                            $error_code = $this->db->ErrorNo();
                            $error[] = array("code" => $error_code,"message" => $error_message);
                        } else {
                            setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                        }
                    } else {
                        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|find|".$this->registry->language)) {
                            $this->registry->main_class->assign("admin_edit","findsuchadmin");
                            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                            $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
                        }
                        $this->registry->main_class->database_close();
                        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|find|".$this->registry->language);
                        exit();
                    }
                } else {
                    $insert_result = false;
                    $error_message = $this->db->ErrorMsg();
                    $error_code = $this->db->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            }
        } else {
            $sql0 = "SELECT admin_id FROM ".PREFIX."_admins WHERE admin_id<>'".$admin_edit_id."' AND admin_email=".$admin_edit_email."";
            $result0 = $this->db->Execute($sql0);
            if($result0) {
                if(isset($result0->fields['0'])) {
                    $numrows = intval($result0->fields['0']);
                } else {
                    $numrows = 0;
                }
                if($numrows == 0) {
                    $sql = "UPDATE ".PREFIX."_admins SET admin_name=$admin_edit_name,admin_surname=$admin_edit_surname,admin_website=$admin_edit_website,admin_email=$admin_edit_email,admin_country=$admin_edit_country,admin_city=$admin_edit_city,admin_address=$admin_edit_address,admin_telephone=$admin_edit_telephone,admin_icq=$admin_edit_icq,admin_gmt=$admin_edit_gmt,admin_notes=$admin_edit_notes,admin_viewemail='$admin_edit_viewemail' WHERE admin_id='$admin_edit_id'";
                    $insert_result = $this->db->Execute($sql);
                    $num_result = $this->db->Affected_Rows();
                    if($insert_result === false) {
                        $error_message = $this->db->ErrorMsg();
                        $error_code = $this->db->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                } else {
                    if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|find|".$this->registry->language)) {
                        $this->registry->main_class->assign("admin_edit","findsuchadmin");
                        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                        $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
                    }
                    $this->registry->main_class->database_close();
                    $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|find|".$this->registry->language);
                    exit();
                }
            } else {
                $insert_result = false;
                $error_message = $this->db->ErrorMsg();
                $error_code = $this->db->ErrorNo();
                $error[] = array("code" => $error_code,"message" => $error_message);
            }
        }
        if($insert_result === false) {
            $this->registry->main_class->assign("error",$error);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|sqlerror|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_edit","notinsert");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|sqlerror|".$this->registry->language);
            exit();
        } elseif($insert_result !== false and $num_result == 0) {
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_edit","notinsert2");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|ok|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|show");
            $this->registry->main_class->systemdk_clearcache("systemadmin|modules|administrators|edit|".$admin_edit_id);
            $this->registry->main_class->systemdk_clearcache("systemadmin|edit|".$admin_edit_id);
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|ok|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_edit","editok");
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
            }
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|edit|ok|".$this->registry->language);
        }
    }


    public function logout() {
        $this->registry->main_class->display_theme_adminheader();
        setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
        if(session_id() == "") {
            session_start();
        }
        if(isset($_SESSION['systemdk_if_admin'])) {
            unset($_SESSION['systemdk_if_admin']);
        }
        //session_unset();
        //setcookie(session_name(), '', time()-3600, '/');
        //session_destroy();
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/exit.html",$this->registry->sitelang."|systemadmin|exit|".$this->registry->language);
    }
}

?>
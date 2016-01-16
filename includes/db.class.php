<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      db.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
include_once(__SITE_PATH . "/adodb/adodb.inc.php");

class db
{


    /**
     * @var registry
     */
    private $registry;
    private $vars = [];
    /**
     * @var ADOConnection
     */
    private $adodb;
    private $db2;


    public function __construct($registry)
    {
        $this->adodb = ADONewConnection(DBTYPE);
        if (DBTYPE !== 'mysqli') {
            $GLOBALS['ADODB_COUNTRECS'] = false;
        }
        $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_NUM;
        //$this->adodb->bulkBind = true;
        if (ADODB_DEBUGGING === 'yes') {
            $this->adodb->debug = true;
        }
        if (DBTYPE === 'oci8') {
            $this->adodb->charSet = DB_CHARACTER;
            $cnt = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=" . DB_HOST . ")(PORT=" . DB_PORT . "))(CONNECT_DATA=(SID=" . DB_NAME . ")))";
        }
        if (DB_PERSISTENCY === 'yes') {
            /*if(DBTYPE === 'sqlite')
             {
               $this->db2 = $this->adodb->PConnect(__SITE_PATH."/includes/data/".DB_NAME.".db");
             }
            elseif(DBTYPE === 'access')
             {
               $this->db2 = $this->adodb->PConnect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".__SITE_PATH."/includes/data/".DB_NAME.".mdb;Uid=".DB_USER_NAME.";Pwd=".DB_PASSWORD.";");
             }
            if(DBTYPE === 'ado_mssql') {
                $this->db2 = $this->adodb->PConnect("PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=".DB_HOST.";DATABASE=".DB_NAME.";UID=".DB_USER_NAME.";PWD=".DB_PASSWORD.";");
            } elseif(DBTYPE === 'odbc_mssql') {
                $dsn = "Driver={SQL Server};Server=".DB_HOST.";Database=".DB_NAME.";";
                $this->db2 = $this->adodb->PConnect($dsn,DB_USER_NAME,DB_PASSWORD);
            }*/
            if (DBTYPE === 'oci8' && DB_PORT != 0) {
                $this->db2 = $this->adodb->PConnect($cnt, DB_USER_NAME, DB_PASSWORD);
            } elseif (DBTYPE === 'pdo') {
                $this->db2 = $this->adodb->PConnect('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER_NAME, DB_PASSWORD);
            } else {
                $this->db2 = $this->adodb->PConnect(DB_HOST, DB_USER_NAME, DB_PASSWORD, DB_NAME);
            }
        } else {
            /*if(DBTYPE === 'sqlite')
             {
               $this->db2 = $this->adodb->Connect(__SITE_PATH."/includes/data/".DB_NAME.".db");
             }
            elseif(DBTYPE === 'access')
             {
               $this->db2 = $this->adodb->Connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".__SITE_PATH."/includes/data/".DB_NAME.".mdb;Uid=".DB_USER_NAME.";Pwd=".DB_PASSWORD.";");
             }
            if(DBTYPE === 'ado_mssql') {
                $this->db2 = $this->adodb->Connect("PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=".DB_HOST.";DATABASE=".DB_NAME.";UID=".DB_USER_NAME.";PWD=".DB_PASSWORD.";");
            } elseif(DBTYPE === 'odbc_mssql') {
                $dsn = "Driver={SQL Server};Server=".DB_HOST.";Database=".DB_NAME.";";
                $this->db2 = $this->adodb->Connect($dsn,DB_USER_NAME,DB_PASSWORD);
            }*/
            if (DBTYPE === 'oci8' && DB_PORT != 0) {
                $this->db2 = $this->adodb->Connect($cnt, DB_USER_NAME, DB_PASSWORD);
            } elseif (DBTYPE === 'pdo') {
                $this->db2 = $this->adodb->Connect('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER_NAME, DB_PASSWORD);
            } else {
                $this->db2 = $this->adodb->Connect(DB_HOST, DB_USER_NAME, DB_PASSWORD, DB_NAME);
            }
        }
        if ($this->db2) {
            $this->adodb->fnExecute = 'CountExecs';
            $this->adodb->fnCacheExecute = 'CountCachedExecs';
            if (DBTYPE === 'mysqlt' || DBTYPE === 'pdo' || DBTYPE === 'mysql' || DBTYPE === 'maxsql' || DBTYPE === 'mysqli') {
                $this->adodb->Execute("set names '" . DB_CHARACTER . "' COLLATE " . DB_COLLATE);
            }
        }
    }


    public function check_connect()
    {
        if (!$this->db2) {
            return false;
        } else {
            return true;
        }
    }


    public function qstr($string, $magic_quotes)
    {
        return $this->adodb->qstr($string, $magic_quotes);
    }


    public function Execute($sql)
    {
        return $this->adodb->Execute($sql);
    }


    public function SelectLimit($sql, $num, $offset)
    {
        return $this->adodb->SelectLimit($sql, $num, $offset);
    }


    public function UpdateClob($table, $name, $value, $where)
    {
        $this->adodb->UpdateClob($table, $name, $value, $where);
    }


    public function Affected_Rows()
    {
        return $this->adodb->Affected_Rows();
    }


    public function StartTrans()
    {
        $this->adodb->StartTrans();
    }


    public function CompleteTrans()
    {
        $this->adodb->CompleteTrans();
    }


    public function GenID($table)
    {
        return $this->adodb->GenID($table);
    }


    public function ServerInfo()
    {
        return $this->adodb->ServerInfo();
    }


    public function ErrorMsg()
    {
        return $this->adodb->ErrorMsg();
    }


    public function ErrorNo()
    {
        return $this->adodb->ErrorNo();
    }


    public function FailTrans()
    {
        return $this->adodb->FailTrans();
    }


    public function Close()
    {
        $this->adodb->Close();
    }
}
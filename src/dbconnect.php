<?php
namespace Sham\Db;


class dbconnect extends dbase{

      public $queryCount = 0;
      public $queryTime = 0;

      public $settings = array();
      public $queryLog = array();

      public $starttime = 1;

      public $_config = array();


      //=====================================================
      // return true or false
      //连接数据库
      //=====================================================
      protected function connect()
      {
            $this->link_id = @mysql_connect($this->_config['hostname'], $this->_config['username'], $this->_config['password'], true);         //非持久连接

            if (!$this->link_id){
                  if (!$this->_config['quiet'])   $this->ErrorMsg("Can't Connect MySQL Server({$this->_config['hostname']})!");
                  return false;
            }

            $this->version = mysql_get_server_info($this->link_id);
            mysql_query("
            SET character_set_connection={$this->_config['charset']},
            character_set_results={$this->_config['charset']},
            character_set_client=binary",
                $this->link_id);
            mysql_query("SET sql_mode=''", $this->link_id);

            //连接开始时间
            $this->starttime = microtime(true);//time();

            /* 选择数据库 */
            if ($this->_config['database']){
                  if (mysql_select_db($this->_config['database'], $this->link_id) === false ){
                        if (!$this->_config['quiet'])  $this->ErrorMsg("Can't select MySQL database({$this->_config['database']})!");
                        return false;
                  }else{
                        return true;
                  }
            }else{
                  return true;
            }
      }



//    /*
//     *设置字符集
//     * */
//    public function set_mysql_charset($charset){   /* 如果mysql 版本是 4.1+ 以上，需要对字符集进行初始化 */
//        if (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8', 'utf8'))){
//            $charset = str_replace('-', '', $charset);
//        }
//        if ($charset != 'latin1'){
//            mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
//        }
//    }

      /*
       * //有可能执行,比如update / delete
       * */
      public function query($sql, $type = ''){
            if ($this->link_id === NULL){
                  $this->connect();
            }
            if ($this->queryCount++ <= 999){
                  $this->queryLog[] = $sql;
            }
            if ($this->queryTime == 0){
                  $this->queryTime = microtime(true);
            }

            /* 当当前的时间大于类初始化时间的时候，自动执行 ping 这个自动重新连接操作 */
            if (time() > $this->starttime + 1){
                  mysql_ping($this->link_id);
            }

            if (!($query = mysql_query($sql, $this->link_id)) && $type != 'SILENT'){
                  $this->error_message[]['message'] = 'MySQL Query Error';
                  $this->error_message[]['sql'] = $sql;
                  $this->error_message[]['error'] = mysql_error($this->link_id);
                  $this->error_message[]['errno'] = mysql_errno($this->link_id);
                  $this->ErrorMsg();
            }

            //记录慢查询
            if($this->_config['slowquery']){
                  if (($this->queryTime - $this->starttime) > $this->_config['slowquery']){
                        $str = $sql."\r\n".'TM : '.($this->queryTime - $this->starttime).' : '.date('Y-m-d H:i:s')."\r\n----------------------------\r\n";
                        $cachefile = $this->_config['rootpath'] . 'slowquery.php';
                        @file_put_contents($cachefile, $str, FILE_APPEND);
                  }
            }

            if ($query === false)$this->ErrorMsg("query error!");

//        return $query;

            $this->queryres = $query;
            return $query;
      }



}


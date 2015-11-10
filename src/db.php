<?php

namespace Sham\Db;

class Db{

      private $_config = array();
      private $_cacheroot     = '';
      private $_cache_query   = 'query_caches/';	//缓存记录
      private $_cache_error   = 'errlog/';		//错误/慢查询 日志


      public $link_id     = NULL;
      public $queryCount  = 0;
      public $retemp      = array();              //临时结果集 gsql的结果集临时存储
      public $queryTime   = 0;
      public $queryLog    = array();
      public $max_cache_time = 300; 				// 最大的缓存时间，以秒为单位

      public $debug      	    = false;
      public $error_message  = array();
      public $platform       = '';			    //操作系统
      public $version        = '';
      public $dbhash         = '';			    //配置缓存文件名
      public $starttime      = 0;
      public $timeline       = 0;
      public $timezone       = 0;
      public $lifetime       = 0;			        //缓存有效时间,<=0标识关闭
      public $slowquery      = -1;			    //慢查询记录时间 超过这个时间,进行记录
      public $mysql_config_cache_file_time = 0;
      public $cache_data  	= '';
      public $cache_data_name= '';
      public $mysql_disable_cache_tables = array();// 不允许被缓存的表，遇到将不会进行缓存
      public $base            = '';
      public $queryres        = null;
      public $Config = array();                   //单例结构





      /**
       * @param string $conf
       * 根据配置获取设定
       */
      public function __construct($conf = array()){
            /**
             * 配置信息
             * */
            $this->_config = array(
                'hostname'  => $conf['hostname'],
                'username'  => $conf['username'],
                'password'  => $conf['password'],
                'database'  => $conf['database'],
                'charset'   => $conf['charset']?:'utf8',
                'pconnect'  => $conf['pconnect'],
                'quiet'     => $conf['quiet'],
            );

            /**
             * cacheroot
             */
            $this->_cacheroot  = $conf['cachefile'];

            //检查定义的文件目录是否存在
            if(!file_exists($this->_cacheroot))
                  $this->ErrorMsg("Sham\\Db\\Db::miss cachepath {$this->_cacheroot} with param \$config['cachefile']");

            $path = rtrim($this->_cacheroot,'\\').$this->_cache_query;
            if(!is_readable($path))
                  is_file($path) or mkdir($path,0777);

            $path = rtrim($this->_cacheroot,'\\').$this->_cache_error;
            if(!is_readable($path))
                  is_file($path) or mkdir($path,0777);


      }


      //=====================================================
      // return true or false
      //连接数据库
      //=====================================================
      private function connect()
      {
            $this->link_id = @mysql_connect($this->_config['dbhost'], $this->_config['dbuser'], $this->_config['dbpw'], true);         //非持久连接
            if (!$this->link_id){
                  if (!$this->_config['quiet'])   $this->ErrorMsg("Can't Connect MySQL Server($dbhost)!");
                  return false;
            }

            $this->version = mysql_get_server_info($this->link_id);
            mysql_query("
            SET character_set_connection={$this->settings['charset']},
            character_set_results={$this->settings['charset']},
            character_set_client=binary",
                $this->link_id);
            mysql_query("SET sql_mode=''", $this->link_id);

            $this->starttime = microtime(true);//time();
            /* 选择数据库 */
            if ($this->settings['dbname']){
                  if (mysql_select_db($this->settings['dbname'], $this->link_id) === false ){
                        $this->ErrorMsg("Can't select MySQL database({$this->settings['dbname']})!");
                        return false;
                  }else{
                        return true;
                  }
            }else{
                  return true;
            }
      }


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

//            //记录慢查询
//            if (($this->queryTime - $this->starttime) > $this->slowquery){
//                  $str = $sql."\r\n".'TM : '.($this->queryTime - $this->starttime).' : '.date('Y-m-d H:i:s')."\r\n----------------------------\r\n";
//                  $cachefile = $this->root_path . $this->err_path . 'slowquery.php';
//                  @file_put_contents($cachefile, $str, FILE_APPEND);
//            }


            if ($query === false)$this->ErrorMsg("query error!");

            $this->queryres = $query;

            return $query;
      }





      /*
       * 输出错误信息
       * */
      public function ErrorMsg($message = ''){
            //if (!$this->settings['quiet'])
            if ($message){
                  echo "<b>info</b>: $message\n\n<br /><br />";
            }else{
                  echo "<b>MySQL server error report:";
                  print_r($this->error_message);
            }
            exit;
      }

      public function close(){
            return mysql_close($this->link_id);
      }

}











<?php

namespace Seter\Library;

class SDb{
      protected $properties;
      //private $settings = array();
      //下面是单例结构===================================================
      public $link_id     = NULL;
      public $queryCount  = 0;
      public $retemp      = array();              //临时结果集 gsql的结果集临时存储
      public $queryTime   = 0;
      public $queryLog    = array();
      public $max_cache_time = 300; 				// 最大的缓存时间，以秒为单位
      public $root_path      = 'cache/';          //RHCACHE
      public $cache_data_dir = 'query_caches/';	//缓存记录
      public $err_path      	= 'errlog/';		//错误/慢查询 日志

      public $debug      	    = false;
      public $error_message  = array();
      public $platform       = '';			    //操作系统
      public $version        = '';
      public $dbhash         = '';			    //配置缓存文件名
      public $starttime      = 0;
      public $timeline       = 0;
      public $timezone       = 0;
      public $lifetime       = 0;			        //缓存有效时间,<=0标识关闭
      public $slowquery      = -1;			    //慢查询记录时间 超过这个时间,进行记录
      public $mysql_config_cache_file_time = 0;
      public $cache_data  	= '';
      public $cache_data_name= '';
      public $mysql_disable_cache_tables = array();// 不允许被缓存的表，遇到将不会进行缓存
      public $base            = '';
      public $queryres        = null;

      private $props = array();                   //单例结构
      public $Config = array();                   //单例结构
      public static $instance;

      public static function getInstance($configchr = 'default'){
            !(self::$instance instanceof self)&&self::$instance = new SDb2($configchr);
            return self::$instance;
      }
      private function __clone(){}

      public function __construct($chr = 'default'){
            $config = C('app')['mysql'];


            $this->Config   = $config[$chr];

            $this->base     = dirname(__FILE__)."/";
            $this->root_path= SHAM_PATH."/".$this->root_path;

            $this->settings = array(
                'dbhost'    => $config[$chr]['hostname'],
                'dbuser'    => $config[$chr]['username'],
                'dbpw'      => $config[$chr]['password'],
                'dbname'    => $config[$chr]['database'],
                'charset'   => $config[$chr]['charset']?:'utf8',
                'pconnect'  => $config[$chr]['pconnect'],
                'quiet'     => $config[$chr]['quiet'],
            );
            if ($this->settings['pconnect']){
                  if(!$this->connect()){
                        $this->ErrorMsg("Can't Connect MySQL Server({$this->settings['dbhost']})!");
                  }
            }
            //持久连接
      }

      //=====================================================
      // return true or false
      //连接数据库
      //=====================================================
      private function connect()
      {
            $this->link_id = @mysql_connect($this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpw'], true);         //非持久连接
            if (!$this->link_id){
                  if (!$this->settings['quiet'])   $this->ErrorMsg("Can't Connect MySQL Server($dbhost)!");
                  return false;
            }

            $this->version = mysql_get_server_info($this->link_id);
            mysql_query("
            SET character_set_connection={$this->settings['charset']},
            character_set_results={$this->settings['charset']},
            character_set_client=binary",
                $this->link_id);
            mysql_query("SET sql_mode=''", $this->link_id);

            $this->starttime = microtime(true);//time();
            /* 选择数据库 */
            if ($this->settings['dbname']){
                  if (mysql_select_db($this->settings['dbname'], $this->link_id) === false ){
                        if (!$this->settings['quiet'])  $this->ErrorMsg("Can't select MySQL database({$this->settings['dbname']})!");
                        return false;
                  }else{
                        return true;
                  }
            }else{
                  return true;
            }
      }

      private function fetch_array($query, $result_type = MYSQL_ASSOC){		//内部
            return mysql_fetch_array($query, $result_type);
      }

      /*
       *选择数据库
       * */
      public function select_database($dbname){
            return mysql_select_db($dbname, $this->link_id);
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
            if (($this->queryTime - $this->starttime) > $this->slowquery){
                  $str = $sql."\r\n".'TM : '.($this->queryTime - $this->starttime).' : '.date('Y-m-d H:i:s')."\r\n----------------------------\r\n";
                  $cachefile = $this->root_path . $this->err_path . 'slowquery.php';
                  @file_put_contents($cachefile, $str, FILE_APPEND);
            }
            if ($query === false)$this->ErrorMsg("query error!");
//        return $query;
            $this->queryres = $query;
            return $query;
      }


      //-------------------------------------------------------------------
      //查询开始
      function getOne($sql, $limited = false){
            if ($limited == true){
                  $sql = trim($sql . ' LIMIT 1');
            }
            $row = mysql_fetch_row($this->query($sql));
            if ($row !== false){
                  return $row[0];
            }else{
                  return '';
            }
      }

      function getRow($sql, $limited = false){
            if ($limited == true){
                  $sql = trim($sql . ' LIMIT 1');
            }
            $vsr = mysql_fetch_assoc($this->query($sql));

            return $vsr;
      }

      public function getAll($sql,$str=''){

            $res = $this->query($sql);
            $arr = array();
            while ($row = mysql_fetch_assoc($res)){
                  if(empty($str)){
                        $arr[] = $row;
                  }else{
                        $arr[$row[$str]] = $row;
                  }
            }
            return $arr;
      }

      function getMap($sql){
            $res = $this->query($sql);
            //===================================
            $arr = array();
            while ($row = mysql_fetch_row($res)){
                  $arr[$row[0]] = $row[1];
            }
            return $arr;
      }


      function getCol($sql){
            $res = $this->query($sql);
            $arr = array();
            while ($row = mysql_fetch_row($res)){
                  $arr[] = $row[0];
            }
            return $arr;
      }

      //===================================================================
      //只会执行一遍的语法结构,,会把结果缓存起来
      //再次遇到该类型的话,直接读取缓存,输出 存储位置$retemp
      //===================================================================
      function gsql($sql,$type='all',$str=''){        //$retemp
            $markstr = $sql.$type;
            if(!empty($this->retemp[$markstr]))        return $this->retemp[$markstr];
            switch($type){
                  case 'all':
                        $rc = $this->getAll($sql,$str);
                        $this->retemp[$markstr] = $rc;
                        return $rc;
                        break;
                  case 'one':
                        $rc = $this->getOne($sql);
                        $this->retemp[$markstr] = $rc;
                        return $rc;
                        break;
                  case 'row':
                        $rc = $this->getRow($sql);
                        $this->retemp[$markstr] = $rc;
                        return $rc;
                        break;
                  case 'col':
                        $rc = $this->getCol($sql);
                        $this->retemp[$markstr] = $rc;
                        return $rc;
                        break;
                  case 'map':
                        $rc = $this->getMap($sql);
                        $this->retemp[$markstr] = $rc;
                        return $rc;
                        break;
            }
            return false;
      }

      public function setMaxCacheTime($second){
            $this->max_cache_time = $second;
      }

      public function getMaxCacheTime(){
            return $this->max_cache_time;
      }

      /* 仿真 Adodb 函数 */
      function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = ''){
            $field_names = $this->getCol('DESC ' . $table);
            $sql = '';


            if ($mode == 'INSERT'){
                  $fields = $values = array();
                  foreach ($field_names AS $value){
                        if (array_key_exists($value, $field_values) == true){
                              $fields[] = $value;
                              $values[] = "'" . $field_values[$value] . "'";
                        }
                  }

                  if (!empty($fields)){
                        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                  }
            }else{
                  $sets = array();
                  foreach ($field_names AS $value){
                        if (array_key_exists($value, $field_values) == true){
                              $sets[] = $value . " = '" . $field_values[$value] . "'";
                        }
                  }
                  if (!empty($sets)){
                        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
                  }
            }
            //echo $sql;
            if ($sql){
                  return $this->query($sql, $querymode);
            }else{
                  return false;
            }
      }


      function autoReplace($table, $field_values, $update_values, $where = '', $querymode = ''){
            $field_descs = $this->getAll('DESC ' . $table);

            $primary_keys = array();
            foreach ($field_descs AS $value){
                  $field_names[] = $value['Field'];
                  if ($value['Key'] == 'PRI'){
                        $primary_keys[] = $value['Field'];
                  }
            }

            $fields = $values = array();
            foreach ($field_names AS $value){
                  if (array_key_exists($value, $field_values) == true){
                        $fields[] = $value;
                        $values[] = "'" . $field_values[$value] . "'";
                  }
            }

            $sets = array();
            foreach ($update_values AS $key => $value){
                  if (array_key_exists($key, $field_values) == true){
                        if (is_int($value) || is_float($value)){
                              $sets[] = $key . ' = ' . $key . ' + ' . $value;
                        }else{
                              $sets[] = $key . " = '" . $value . "'";
                        }
                  }
            }

            $sql = '';
            if (empty($primary_keys)){
                  if (!empty($fields)){
                        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                  }
            }else{
                  if ($this->version() >= '4.1'){
                        if (!empty($fields)){
                              $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                              if (!empty($sets)){
                                    $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
                              }
                        }
                  }else{
                        if (empty($where)){
                              $where = array();
                              foreach ($primary_keys AS $value){
                                    if (is_numeric($value)){
                                          $where[] = $value . ' = ' . $field_values[$value];
                                    }else{
                                          $where[] = $value . " = '" . $field_values[$value] . "'";
                                    }
                              }
                              $where = implode(' AND ', $where);
                        }

                        if ($where && (!empty($sets) || !empty($fields))){
                              if (intval($this->getOne("SELECT COUNT(*) FROM $table WHERE $where")) > 0){
                                    if (!empty($sets)){
                                          $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
                                    }
                              }else{
                                    if (!empty($fields)){
                                          $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                                    }
                              }
                        }
                  }
            }

            if ($sql){
                  return $this->query($sql, $querymode);
            }else{
                  return false;
            }
      }


      public function version(){
            return $this->version;
      }

      /*
       * 输出错误信息
       * */
      public function ErrorMsg($message = '', $sql = ''){
            if ($message){
                  echo "<b>info</b>: $message\n\n<br /><br />";
            }else{
                  echo "<b>MySQL server error report:";
                  print_r($this->error_message);
            }
            exit;
      }


      //------------------------------------------------------------
      //元操作
      private function result($query, $row){
            return @mysql_result($query, $row);
      }

      private function num_rows($query){
            return mysql_num_rows($query);
      }

      private function num_fields($query){
            return mysql_num_fields($query);
      }

      private function free_result($query){
            return mysql_free_result($query);
      }


      private function fetchRow($query){
            return mysql_fetch_assoc($query);
      }

      private function fetch_fields($query){
            return mysql_fetch_field($query);
      }



      private function escape_string($unescaped_string){
            return mysql_real_escape_string($unescaped_string);
      }

      //针对connection的操作
      private function affected_rows(){
            return mysql_affected_rows($this->link_id);
      }

      private function error(){
            return mysql_error($this->link_id);
      }

      private function errno(){
            return mysql_errno($this->link_id);
      }

      private function ping(){
            return mysql_ping($this->link_id);
      }

      public function insert_id(){
            return mysql_insert_id($this->link_id);
      }

      public function close(){
            return mysql_close($this->link_id);
      }

      //=======================================
      //=======================================



}


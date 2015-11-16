<?php
namespace Sham\Db;


class db extends dbconnect{

      protected $properties;
      //private $settings = array();
      //下面是单例结构===================================================
      public $queryCount  = 0;
      public $queryTime   = 0;
      public $queryLog    = array();
      public $root_path      = './';          //RHCACHE

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


      public static $instance;
      public static function getInstance($config= array()){
            !(self::$instance instanceof self)&&self::$instance = new db($config);
            return self::$instance;
      }
      public function __construct($config = array()){
            $this->_config   = $config;

            /**
             * 写文件的地方有1个
             * 1 : slow log
             */
            $this->root_path= $config['rootpath'];

            $this->_config['hostname']    = $config['hostname'];
            $this->_config['username']    = $config['username'];
            $this->_config['password']    = $config['password'];
            $this->_config['database']    = $config['database'];

            $this->_config['charset']     = $config['charset']?:'utf8';
            $this->_config['pconnect']    = $config['pconnect'];
            $this->_config['quiet']       = $config['quiet'];
            $this->_config['slowquery']   = $config['slowquery']?:0;




            if ($this->_config['pconnect']){
                  if(!$this->connect()){
                        $this->ErrorMsg("Can't Connect MySQL Server({$this->_config['hostname']})!");
                  }
            }
            //持久连接


      }


      //-------------------------------------------------------------------
      //查询开始
      public function getOne($sql, $limited = false){
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

      public function getRow($sql, $limited = false){
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

      public function getMap($sql){
            $res = $this->query($sql);
            //===================================
            $arr = array();
            while ($row = mysql_fetch_row($res)){
                  $arr[$row[0]] = $row[1];
            }
            return $arr;
      }


      public function getCol($sql){
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
      public function gsql($sql,$type='all',$str=''){        //$retemp
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


      /* 仿真 Adodb 函数 */
      public function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = ''){
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


//      function autoReplace($table, $field_values, $update_values, $where = '', $querymode = ''){
//            $field_descs = $this->getAll('DESC ' . $table);
//
//            $primary_keys = array();
//            foreach ($field_descs AS $value){
//                  $field_names[] = $value['Field'];
//                  if ($value['Key'] == 'PRI'){
//                        $primary_keys[] = $value['Field'];
//                  }
//            }
//
//            $fields = $values = array();
//            foreach ($field_names AS $value){
//                  if (array_key_exists($value, $field_values) == true){
//                        $fields[] = $value;
//                        $values[] = "'" . $field_values[$value] . "'";
//                  }
//            }
//
//            $sets = array();
//            foreach ($update_values AS $key => $value){
//                  if (array_key_exists($key, $field_values) == true){
//                        if (is_int($value) || is_float($value)){
//                              $sets[] = $key . ' = ' . $key . ' + ' . $value;
//                        }else{
//                              $sets[] = $key . " = '" . $value . "'";
//                        }
//                  }
//            }
//
//            $sql = '';
//            if (empty($primary_keys)){
//                  if (!empty($fields)){
//                        $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
//                  }
//            }else{
//                  if ($this->version() >= '4.1'){
//                        if (!empty($fields)){
//                              $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
//                              if (!empty($sets)){
//                                    $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
//                              }
//                        }
//                  }else{
//                        if (empty($where)){
//                              $where = array();
//                              foreach ($primary_keys AS $value){
//                                    if (is_numeric($value)){
//                                          $where[] = $value . ' = ' . $field_values[$value];
//                                    }else{
//                                          $where[] = $value . " = '" . $field_values[$value] . "'";
//                                    }
//                              }
//                              $where = implode(' AND ', $where);
//                        }
//
//                        if ($where && (!empty($sets) || !empty($fields))){
//                              if (intval($this->getOne("SELECT COUNT(*) FROM $table WHERE $where")) > 0){
//                                    if (!empty($sets)){
//                                          $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
//                                    }
//                              }else{
//                                    if (!empty($fields)){
//                                          $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
//                                    }
//                              }
//                        }
//                  }
//            }
//
//            if ($sql){
//                  return $this->query($sql, $querymode);
//            }else{
//                  return false;
//            }
//      }




}


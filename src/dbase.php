<?php
namespace Sham\Db;


class Dbase{

      public $version = '';
      public $link_id = null;
      public $error_message = '';


      public function insert_id(){
            return mysql_insert_id($this->link_id);
      }

      public function close(){
            return mysql_close($this->link_id);
      }


      /*
       *选择数据库
       * */
      public function select_database($dbname){
            return mysql_select_db($dbname, $this->link_id);
      }

      public function version(){
            return $this->version;
      }

      /*
       * 输出错误信息
       * */
      public function ErrorMsg($message = ''){
            if ($message){
                  echo "<b>info</b>: $message\n\n<br /><br />";
            }else{
                  echo "<b>MySQL server error report:";
                  print_r($this->error_message);
            }
            exit;
      }


      private function fetch_array($query, $result_type = MYSQL_ASSOC){		//内部
            return mysql_fetch_array($query, $result_type);
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


      //=======================================
      //=======================================

}


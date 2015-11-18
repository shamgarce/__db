<?php
include("../vendor/autoload.php");

//配置
/**
 * 数据库访问地址
 */
$conf['hostname'] = '127.0.0.1';

/**
 * 用户名
 */
$conf['username'] = 'root';

/**
 * 密码
 */
$conf['password'] = 'root';

/**
 * 数据库名
 */
$conf['database'] = 'weidong';

/**
 * 字符集设置
 */
$conf['charset']  = 'utf8';

/**
 * 1长连接模式
 * 0短连接
 */
$conf['pconnect'] = 0;

/**
 * 安静模式
 */
$conf['quiet']    = 1;



/**
 * 对慢查询记录
 */
$conf['slowquery']    = 0;

/**
 * 慢查询日志存放的地址
 */
$conf['rootpath']    = './Mysql/errlog/';


/**
 * 实例化
 */
//$db = new Sham\Db\db($conf);
$db =  Sham\Db\Db::getInstance($conf);


echo '<pre>';
$rc = $db->getall("select * from dz_open_module");
$rc = $db->getone("select name from dz_open_module");
$rc = $db->getcol("select name from dz_open_module");
$rc = $db->getrow("select * from dz_open_module");
$rc = $db->getmap("select * from dz_open_module");

print_r($rc);















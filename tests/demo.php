<?php
include("../vendor/autoload.php");

//����
/**
 * ���ݿ���ʵ�ַ
 */
$conf['hostname'] = '127.0.0.1';

/**
 * �û���
 */
$conf['username'] = 'root';

/**
 * ����
 */
$conf['password'] = 'root';

/**
 * ���ݿ���
 */
$conf['database'] = 'weidong';

/**
 * �ַ�������
 */
$conf['charset']  = 'utf8';

/**
 * 1������ģʽ
 * 0������
 */
$conf['pconnect'] = 0;

/**
 * ����ģʽ
 */
$conf['quiet']    = 1;



/**
 * ������ѯ��¼
 */
$conf['slowquery']    = 0;

/**
 * ����ѯ��־��ŵĵ�ַ
 */
$conf['rootpath']    = './Mysql/errlog/';


/**
 * ʵ����
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















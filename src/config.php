<?php
/* *
 * 配置文件
 * 日期：2015-08-20
 * 作者：Wjw
 */
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//读取配置文件
$configs = parse_ini_file("/srun3/etc/campay.conf");
//业务系统的id号
$campay_config['sysId']			= $configs['sysId'];
//项目分类id
$campay_config['itemId']		= $configs['itemId'];
//系统证书
$campay_config['cert']			= $configs['cert'];
//申请支付页面路径
$campay_config['apply_url']		= 'http://'.$configs['url'].'/pay/itemDeal3.html';
//支付页面路径
$campay_config['pay_url']		= 'http://'.$configs['url'].'/pay/dealPay.html';
//查询页面路径
$campay_config['query_url']		= 'http://'.$configs['url'].'/pay/queryPR3.html';
//服务器异步通知页面路径
$campay_config['notify_url']	= 'http://'.$configs['service_url'].'/pay/notify/campay';

return $campay_config;
?>
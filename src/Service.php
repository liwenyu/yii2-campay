<?php

namespace srun\campay;

/**
 * Yii2 campay Extension.
 */
class Service
{
    var $config;

    function __construct()
    {
        //默认设置
        $this->config = require(__DIR__ . '/config.php');
    }

    function Service()
    {
        $this->__construct();
    }

    /**
     * 向支付平台添加支付信息
     * @param $para_temp 请求参数数组
     * @return payPassword
     */
    function createDirectPay($para_temp)
    {
        //创建submit 实例
        $submit = new Submit($this->config);
        //构建请求数组即：增加MD5签名
        $para = $submit->buildRequestPara($para_temp, $this->config);
        //获取请求url
        $url = $this->config['apply_url'];
        //发送post数据
        $result = $submit->sendPostInfo($url, $para);
        //验证解析结果
        $res = $submit->resolveResponseInfo($result, $this->config['cert']);
        if ($res['error'] == false) {
            $res['Url'] = $this->config['pay_url'] . '?pwd=' . $res['payPassword'];
        }

        return $res;
    }

    /**
     * 异步通知处理支付信息
     * @param $para_temp 请求参数数组
     * @return payPassword
     */
    function handelNotify()
    {
        //计算得出通知验证结果
        $notify = new Notify($this->config);
        $verify_result = $notify->verifyNotify();
        if ($verify_result) {//验证成功
            return $_POST;
        } else {//验证失败
            return false;
        }
    }
}

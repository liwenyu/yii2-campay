<?php

namespace srun\campay;

use yii\helpers\ArrayHelper;

/* *
 * 类名：Notify
 * 功能：校园网支付通通知处理类
 * 详细：处理校园网支付接口通知返回
 * Yii2 campay Extension.
 */

class Notify
{
    var $config = [];

    public function __construct()
    {
        //默认设置
        $_config = require(__DIR__ . '/config.php');
        //load config file
        $this->config = ArrayHelper::merge($_config, $this->config);
    }

    public function Notify()
    {
        $this->__construct();
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * @return 验证结果
     */
    public function verifyNotify()
    {
        $core = new Core();
        if (empty($_POST)) {//判断POST来的数组是否为空
            $core->logResult('post is empty');
            return false;
        } else {
            //获取签名值
            $sign = $_POST["sign"];
            //生成签名结果
            $mysign = $this->getMysign();
            //若果签名不相等
            if ($sign !== $mysign) {
                $core->logResult('notify sign false');
                return false;
            }
            $core->logResult('notify sign true');
            //签名正确
            return true;
        }
    }

    /**
     * 根据反馈回来的信息，生成签名结果
     * @param $para_temp 通知返回来的参数数组
     * @return 生成的签名结果
     */
    public function getMysign()
    {
        $core = new Core();
        //对待签名参数数组排序
        $para_sort = $core->payResSort($_POST);
        //生成签名结果
        $mysign = $core->buildMysign($para_sort, trim($this->config['cert']));
        return $mysign;
    }
}

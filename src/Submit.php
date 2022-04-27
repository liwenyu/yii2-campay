<?php

namespace srun\campay;

/**
 * Yii2 campay Extension.
 */
class Submit
{
    /**
     * 生成要请求给校园支付平台的参数数组
     * @param $para_temp 请求前的参数数组
     * @param $config    基本配置信息数组
     * @return 要请求的参数数组
     */
    public function buildRequestPara($para_temp, $config)
    {
        $syscf = [
            'sysId' => $config['sysId'],
            'itemId' => $config['itemId'],
            'returnURL' => $config['notify_url']
        ];

        //系统证书
        $cert = $config['cert'];
        //合并配置信息到请求数组中
        $para_data = array_merge($para_temp, $syscf);
        $core = new Core;
        //对待签名参数数组排序
        $para_sort = $core->reqSort($para_data);
        //生成签名结果
        $mysign = $core->buildMysign($para_sort, trim($cert));
        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        return $para_sort;
    }

    /**
     * 解析添加支付信息返回的结果
     * @param $data 返回的结果
     * @return array(0|1, errormsg|payPassword)
     */
    public function resolveResponseInfo($data, $cert)
    {
        $core = new Core;
        //解析json文件
        $para = json_decode($data, true);
        //获取返回的code
        $returnCode = $para['returnCode'];
        //如果returnCode 等于00 或者01
        if ($returnCode == '00' || $returnCode == '01') {
            $sign = $para['sign'];
            //对待签名参数数组排序
            $para_sort = $core->reqResSort($para);
            //系统证书
            //$cert = $this->campay_config['cert'];
            //生成签名结果
            $mysign = $core->buildMysign($para_sort, trim($cert));
            //判断签名是否正确
            if ($sign !== $mysign) {
                $core->logResult('sign error, campay fail:' . $data);
                return [
                    'error' => true,
                    'msg' => $this->errorMsg('11')
                ];
            }
            //记录申请date
            $core->logResult('campay success:' . $data);

            //返回payPassword
            return [
                'error' => false,
                'objId' => $para['objId'],
                //'objName' => $data['objName'],
                'amount' => $para['amount'],
                'projectId' => $para['projectId'],
                'payId' => $para['payId'],
                'payPassword' => $para['payPassword'],
                'msg' => $this->errorMsg($returnCode)
            ];
        }

        //记录申请date
        $core->logResult('campay fail:' . $data);
        //返回错误code
        return [
            'error' => true,
            'msg' => $this->errorMsg($returnCode) . ';' . $para['msg']
        ];
    }

    public function errorMsg($errorCode)
    {
        $ErrorMsg = [
            '00' => '成功',
            '01' => '删除成功',
            '11' => '签名信息不正确',
            '12' => '两次信息不匹配，当收费记录已存在时会出现此错误提示',
            '13' => '错误的系统编号',
            '21' => '收费记录不存在，在删除时会出现此错误提示',
            '22' => '收费项目不存在',
            '23' => '收费记录无法删除，在删除时会出现此错误提示',
            '31' => '金额格式不正确',
            '32' => 'otherId长度超出',
            '35' => 'objName长度超出',
            '99' => '其他异常错误'
        ];

        return $ErrorMsg[$errorCode];
    }

    /**
     * 构造模拟远程HTTP的POST请求，获取校园支付系统的返回结果
     * @param $para_temp 请求参数数组
     * @param $gateway   网关地址
     * @return 返回请求结果
     */
    public function sendPostInfo($url, $data)
    {
        $postdata = http_build_query($data);
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            ]
        ];

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
}

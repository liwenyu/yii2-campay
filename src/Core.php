<?php

namespace srun\campay;

use Yii;

/**
 * Yii2 campay Extension.
 * 校园支付系统接口公用函数
 */
class Core
{
    /**
     * 生成签名结果
     * @param $sort_para 要签名的数组
     * @param $cert      校园支付系统证书
     * @param $sign_type 签名类型
     *                   默认值：MD5
     *                   return 签名结果字符串
     */
    public function buildMysign($sort_para, $cert, $sign_type = "MD5")
    {
        //获取证书第一个字符
        $firstchar = substr($cert, 0, 1);
        //进行拼接
        $prestr = $this->createLinkstring($sort_para, $firstchar);
        // 把拼接后的字符串再与系统证书直接连接起来
        $prestr = $prestr . $cert;
        // 把最终的字符串签名，获得签名结果
        $mysgin = $this->sign($prestr, $sign_type);
        return $mysgin;
    }

    //请求数组固定顺序 type=000
    public function reqSort($data)
    {
        $array = [
            'sysId' => '',
            'itemId' => '',
            'objId' => '',
            'otherId' => time(),
            'objName' => '',
            'amount' => '',
            'remove' => '',
            'returnType' => 'data',
            'specialValue' => '',
            'returnURL' => ''
        ];
        return array_merge($array, $data);
    }

    //请求结果固定顺序 type=001
    public function reqResSort($data)
    {
        unset($data['msg']);
        unset($data['sign']);
        $array = [
            'returnCode' => '',
            'sysId' => '',
            'itemId' => '',
            'objId' => '',
            'otherId' => '',
            'objName' => '',
            'amount' => '',
            'projectId' => '',
            'payId' => '',
            'payPassword' => '',
            'specialValue' => '',
            'returnURL' => ''
        ];
        return array_merge($array, $data);
    }

    //支付返回结果固定顺序	type=100
    public function payResSort($data)
    {
        unset($data['sign']);
        $array = [
            'version' => '',
            'sysId' => '',
            'itemId' => '',
            'objId' => '',
            'otherId' => '',
            'objName' => '',
            'amount' => '',
            'paid' => '',
            'refund' => '',
            'overTime' => '',
            'status' => '',
            'projectId' => '',
            'payId' => '',
            'payPassword' => '',
            'specialValue' => '',
            'payType' => ''
        ];
        return array_merge($array, $data);
    }

    //查询请求数组固定顺序 type=010
    public function queSort($data)
    {
        $array = [
            'sysId' => '',
            'itemId' => '',
            'objId' => '',
            'otherId' => '',
            'projectId' => '',
            'batch' => '',
            'status' => ''
        ];
        return array_merge($array, $data);
    }

    //查询返回结果固定顺序 type=011
    public function queResSort($data)
    {
        $array = [
            'returnCode' => '',
            'sysId' => '',
            'itemId' => '',
            'objId' => '',
            'otherId' => '',
            'objName' => '',
            'amount' => '',
            'paid' => '',
            'refund' => '',
            'overTime' => '',
            'status' => '',
            'projectId' => '',
            'payId' => '',
            'payPassword' => '',
            'specialValue' => '',
            'payType' => ''
        ];
        return array_merge($array, $data);
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     *              return 拼接完成以后的字符串
     */
    public function createLinkstring($para, $firstCh)
    {
        $arg = "";
        foreach ($para as $key => $value) {
            $arg .= $firstCh . $value;
        }

        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     *              return 拼接完成以后的字符串
     */
    public function createLinkstringUrlencode($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     *              return 去掉空值与签名参数后的新签名参数组
     */
    public function paraFilter($para)
    {
        $para_filter = [];
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "")
                continue;
            else
                $para_filter [$key] = $para [$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     *              return 排序后的数组
     */
    public function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 签名字符串
     * @param $prestr    需要签名的字符串
     * @param $sign_type 签名类型
     *                   默认值：MD5
     *                   return 签名结果
     */
    public function sign($prestr, $sign_type = 'MD5')
    {
        $sign = '';
        if ($sign_type == 'MD5') {
            $sign = md5($prestr);
        } else if ($sign_type == 'DSA') {
            // DSA 签名方法待后续开发
            die ("DSA 签名方法待后续开发，请先使用MD5签名方式");
        } else {
            die ("支付宝暂不支持" . $sign_type . "类型的签名方式");
        }
        return strtolower($sign);
    }

    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容
     *              默认值：空值
     */
    public function logResult($word = '')
    {
        $path = '@app/runtime/logs/';
        $vendorDir = Yii::getAlias($path);
        $fp = fopen($vendorDir . 'campay' . date('Y-m-d') . ".log", "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 实现多种字符编码方式
     * @param $input           需要编码的字符串
     * @param $_output_charset 输出的编码格式
     * @param $_input_charset  输入的编码格式
     *                         return 编码后的字符串
     */
    public function charsetEncode($input, $_output_charset, $_input_charset)
    {
        $output = "";
        if (!isset ($_output_charset))
            $_output_charset = $_input_charset;
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } else if (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } else if (function_exists("iconv")) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else
            die ("sorry, you have no libs support for charset change.");
        return $output;
    }

    /**
     * 实现多种字符解码方式
     * @param $input           需要解码的字符串
     * @param $_output_charset 输出的解码格式
     * @param $_input_charset  输入的解码格式
     *                         return 解码后的字符串
     */
    public function charsetDecode($input, $_input_charset, $_output_charset)
    {
        $output = "";
        if (!isset ($_input_charset))
            $_input_charset = $_input_charset;
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } else if (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } else if (function_exists("iconv")) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else
            die ("sorry, you have no libs support for charset changes.");
        return $output;
    }
}

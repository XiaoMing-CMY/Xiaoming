<?php
require_once 'config.php';

class WechatHandler {
    private $token;
    
    public function __construct() {
        $this->token = TOKEN;
    }
    
    // 验证签名
    public function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        
        return $tmpStr == $signature;
    }
    
    // 处理消息
    public function responseMsg() {
        $postStr = file_get_contents("php://input");
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $msgType = "text";
            
            $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
            
            // 根据关键词回复不同内容
            switch ($keyword) {
                case "公司简介":
                    $contentStr = "六盘水华兴物业服务有限公司成立于2015年，是一家专业的物业服务企业，致力于为业主提供优质的物业服务。";
                    break;
                    
                case "联系我们":
                    $contentStr = "地址：贵州省六盘水市钟山区大街城西路2号搬迁街\n电话：13158289988\n邮箱：1318940380@qq.com";
                    break;
                    
                case "服务项目":
                    $contentStr = "我们提供以下服务：\n1. 物业管理\n2. 保洁服务\n3. 安保服务\n4. 维修服务\n5. 绿化养护\n6. 客户服务";
                    break;
                    
                default:
                    $contentStr = "欢迎关注六盘水华兴物业服务有限公司公众号！\n\n请回复以下关键词获取相关信息：\n- 公司简介\n- 联系我们\n- 服务项目";
            }
            
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }
    }
}

// 创建实例并处理请求
$wechat = new WechatHandler();

// 验证签名
if ($wechat->checkSignature()) {
    // 如果是首次验证
    if (isset($_GET["echostr"])) {
        echo $_GET["echostr"];
    } else {
        // 处理消息
        $wechat->responseMsg();
    }
}
?> 
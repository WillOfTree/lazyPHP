<?php
require_once './PHPMailer.php';
require_once './reptile.php';

header("Content-type: text/html; charset=utf-8");
date_default_timezone_set("Asia/Shanghai");
set_time_limit(0);
mb_regex_encoding('utf-8');


//人民日报地址
$url = "http://opinion.people.com.cn/GB/8213/49160/49217/index.html";
$a = new reptile;
$url_content = $a->init($url);
$url = $a->dispose_url($url_content);

//判断是否继续进行
if(empty($url))
{
    echo "没有url地址！";
    exit;
}
$res = $a->dispose_word($url);
//将时间写入日志
file_put_contents("./time.txt", time());

/**
 * 调用发送邮件函数
 */
$a = new mail();
$a->searchDir();
//测试  
$b = $a->sendMail('703872513@qq.com', '三水');  
echo "<br/>" . $b;
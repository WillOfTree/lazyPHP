<?php
//设置编码格式
// header("Content-type: text/html; charset=utf-8");

/**
 * 发送邮件方法
 */
class mail
{
    //发送邮件的账户和密码
    private $email_username = 'tianxia2_wish@126.com';
    private $email_password = 'dante';
    private $service_dir = "./result/";

    public function sendMail($toAddr, $toUser){
        
        //引入PHPMailer的核心文件 使用require_once包含避免出现PHPMailer类重复定义的警告
        require_once './PHPMailer/vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
            $mail->SMTPDebug = 2; 
            //使用smtp鉴权方式发送邮件                               
            $mail->isSMTP();
            //smtp需要鉴权 这个必须是true
            $mail->SMTPAuth = true;

            //链接qq、126域名邮箱的服务器地址
            // $mail->Host = 'smtp1.example.com;smtp2.example.com'; 
            $mail->Host = 'smtp.126.com'; 
            //发信箱的账号密码
            $mail->Username = $this->email_username;                 // SMTP username
            $mail->Password = $this->email_password;                           // SMTP password

            //设置使用ssl加密方式登录鉴权
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('tianxia2_wish@126.com', '细心的小哥哥');
            $mail->addAddress($toAddr, $toUser);     // Add a recipient

            //Attachments附件
            $name = $this->searchDir();
            $face = $this->getFace();

            if(!empty($name))
            {
                require_once "./zip.php";
                //加载压缩文件
                $a = new ZipF;
                $filename = $a->zip();
                //添加附件
                $mail->addAttachment($filename);

                // foreach($name as $v)
                // {
                //     //添加附件
                //     $mail->addAttachment($this->service_dir . $v);
                //     // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
                // }

                //Content正文
                //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
                $mail->isHTML(true);                                 
                $mail->Subject = '每天的人民日报评论';
                $mail->Body    = '报纸在附件里，下载后再去打印哦~~<br/>' . $face;
                // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            } 
            else 
            {
                //Content正文
                //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
                $mail->isHTML(true);                                 
                $mail->Subject = '今天日报没有更新哦~';
                $mail->Body    = '今天没找到日报哦~~~，一定不是程序坏掉了<br/>' . $face;
                // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            }
            
            $mail->send();
            $info = 'Message has been sent';
        } catch (Exception $e) {
            $info = 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo;
        }

        //删除文件
        $this->delete($name);

        return $info;
    }
    
    //生成随机的表情
    private function getFace()
    {
        $face = array(
            "╭(●｀∀´●)╯╰(●’◡’●)╮",
            "(●’◡’●)ﾉ ヾ(*´▽‘*)ﾉ",
            "( ˘ ³˘) ♥",
            "(๑ơ ₃ ơ)ﻌﻌﻌ♥",
            "ε٩(๑> ₃ <)۶з",
            " ≖‿≖✧",   
            "o‿≖✧",
            "(๑•̀ㅂ•́)و✧",
            "(ง •̀_•́)ง",     
            "(*•̀ㅂ•́)و",
            " ╮(๑•́ ₃•̀๑)╭",
            "ฅ'ω'ฅ",
            "٩(๛˘³˘)۶♥",
            "ฅ(๑*д*๑)ฅ",
            "ฅ(๑˙o˙๑)ฅ",
            " (๑•̀ㅁ•́ฅ)",
            "(..•˘_˘•..)",
            "٩(//̀Д/́/)۶",
            "(づ￣ 3￣)づ",
            "( ˙ε ˙ )",
            " (•‾̑⌣‾̑•)✧˖° ",
            "๑乛◡乛๑"
        );
        //生成随机数
        $a = rand(0,count($face));

        return $face[$a];
    }
    /**
     * 读取文件夹中的文件
     * 
     * 中文会乱码
     */
    public function searchDir()
    {
        $res = array();
        $dir = "./result/";
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $res[] = $file;
                }
            }
            closedir($handle);
        }

        return $res;
    }

    /**
     * 删除文件
     * 删除readme
     * @paramete
     */
    private function delete($file)
    {
        $dir = $this->service_dir;
        if(!empty($file))
        {
            foreach($file as $v)
            {
                if($v != "Readme.md")
                {
                    unlink($dir.$v);
                }
            }
        }
        
    }
}

// $a = new mail();
// $a->searchDir();
// echo "<pre>";
// print_r($a);
// //测试  
// $a->sendMail('703872513@qq.com', '三水');  


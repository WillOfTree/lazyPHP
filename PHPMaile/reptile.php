<?php
header("Content-type: text/html; charset=utf-8");
/**
 * PHP爬虫
 * 获取人民日报数据
 */
class reptile 
{
    //定义url地址
    private $url = "opinion.people.com.cn";
    private $key = "";
  
    /**
     * 读取人民日报中的html
     */
    public function init($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        //转码
        // $output = mb_convert_encoding($output, "UTF-8","UTF-8,GBK,GB2312,BIG5");
        // $output = mb_convert_encoding($output, "UTF-8", "GB2312");

        return $output;
    }

    /**
     * 获取人民日报中列表中的url地址
     */
    public function dispose_url($word)
    {
        preg_match_all('/<a\s+href=[\"\']{0,1}(\/n[^>\'\"]*).*?>.*?<\/a>(.*?)<br>/', $word, $res);
        //过滤地址
        $res_lost = $this->judge($res);

        return $res_lost;
    }

    /**
     * 匹配每一条日报详情内容的标题，副标题，内容等信息
     */
    private function dispose_content($word)
    {
        //匹配标题
        preg_match("/<h1>(.*?)<\/h1>/", $word, $res_main);
        //匹配副标题
        preg_match("/<h4 class=\"sub\">(.*)<\/h4>/", $word, $res_subtitle);
        //替换文本中的换行等
        $word = preg_replace("/[\t\n\r]+/","",$word);
        //匹配内容
        preg_match("/id=\"rwb_zw\">(.*)<div\s+class=\"zdfy\s+clearfix\">/", $word, $content);
        preg_match_all("/<p>(.*?)<\/p>/", $content[1], $res_last);
        

        $res_word = array();
        $res_word['title_main'] = $res_main[1]; 
        $res_word['subtitle'] = $res_subtitle[1];
        $res_word['content'] = $res_last[1];

        // echo "<pre>";
        // print($res_main[1]);
        // echo "<hr/>";
        // print_r($res_word);
        
        return $res_word;
    }

    /**
     * 访问url中的信息，获取每条日报的详细html内容
     */
    public function dispose_word($url)
    {
        foreach($url as $v)
        {
            $ch = curl_init();
            //拼接url地址
            curl_setopt($ch, CURLOPT_URL, $this->url.$v);
            // curl_setopt($ch, CURLOPT_URL, $this->url.$url['0']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            $output = mb_convert_encoding($output, "UTF-8","UTF-8,GBK,GB2312,BIG5");

            $word = array();
            $word = $this->dispose_content($output);
            $this->build_word($word);
            echo "<hr/>";
        }
    }

    /**
     * 使用phpword操作word，生成一定样式的word2007文件
     */
    private function build_word($build)
    {
        require_once './class/bootstrap.php';
        $phpWord = new PhpOffice\PhpWord\PhpWord();

        // $fontStyle = array('bold'=>true, 'align'=>'center');        //添加table样式
        // $PHPWord->addTableStyle('myOwnTableStyle', $styleTable, $styleFirstRow);        //添加表格table
        // $table = $section->addTable('myOwnTableStyle');        
        //-------------------------------------------------
        // $PHPWord->setDefaultFontName('仿宋'); // 全局字体
        // $PHPWord->setDefaultFontSize(16);     // 全局字号为3号

        //----------------------------
        //定义样式
        //---------------------------

        //定义标题样式
        $phpWord->addTitleStyle(1 ,array(
            "size"=>"22",
            "bold"=>"true",
        ));
    
        //设置字体，不知道为什么addFontStyle函数不能使用
        $fontStyleName = array(
            'size' => 16,
            'name' => '仿宋',
        );
        
        //定义段落样式
        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, array(
                // 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, //居中
                // 'spaceAfter' => 100, //段前段后缩进
                'lineHeight' => 1, //1倍行间距
            )
        );
        
        //------------------------
        // 设置内容
        //----------------------

        //添加一页
        $section = $phpWord->addSection();
        //添加标题
        $section->addTitle($build['title_main'], 1);
        //添加副标题
        $section->addText($build['subtitle']);
        $section->addTextBreak();

        //添加文字
        foreach($build['content'] as $v)
        {
            $section->addText(" ".$v, $fontStyleName, $paragraphStyleName);
            $section->addTextBreak();
        }

        //----------------------
        // 处理文件名称、生成文件
        // 文件名比较特殊
        //------------------------

        //处理文件名称
        $name_one = preg_split("/[\:\-\：]+/",$build['title_main']);
        $name = time().".docx";

        //生成文档
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save("./result/".$name);
        echo "成功生成文档${name}<br/>";
        sleep(1);
    }

    /**
     * 获取时间，并判断时间是不是近期可以使用的时间段中
     */
    public function judge($data)
    {   
        $file_path = "./time.txt";
        $time = file_get_contents($file_path);

        //如果time为空
        if(empty($time))
        {
            $res = $data['1'];
        } else {
            $time_two = (int)$time;
            
            foreach($data['2'] as $k=>$v)
            {
                $v_one = mb_convert_encoding($v, "UTF-8","UTF-8,GBK,GB2312,BIG5");
                //将时间转换为时间戳
                preg_match("/(\d{4})年(\d{0,2})月(\d{0,2})日(\d{0,2}):(\d{0,2})/", $v_one ,$v);
                
                $d = mktime($v[4], $v[5], 0, $v[2], $v[3], $v[1]);
                $d_one = (int)$d;
                
                if($d_one > $time_two)
                {
                    $res[] = $data['1'][$k];
                } 
            }
            
        }
        
        return $res;
    }
}


<?php
class Log{
    public static $logForSqlConn='log_sqlConn.html';//连接相关的log存放文件
    public static $logForBlog='log_blog.html';//博客操作相关的log存放文件
    public static $logForImg='log_img.html';//图片操作相关的log存放文件
    public static $logForUser='log_user.html';//用户操作相关的log存放文件

    private static $logDir='./log/';//日志存放的根目录
    /*
     *生成日志
     *
     * */
	public static function setLog($logType,$log,$url){
		 self::FileIsExist($logType);
		 @file_put_contents(self::$logDir.$logType,self::CreateLog($log,$url),FILE_APPEND);
	}
    /*
     * 创建一条日志的HTML
     * */
    private  static function  CreateLog($log,$url){
        date_default_timezone_set('PRC');
        $time=date("Y-m-d H:i",time());
        $content='<tr>';
        $content.='<td>1</td>';
        $content.=' <td>'.$time.'</td>';
        $content.='<td>'.$log.'</td>';
        $content.='<td>'.$url.'</td>';
		$content.='</tr>';
		return $content;
}

    /*
     * 获取当前无参数的URL
     * */
     public  static function GetCurPageURL()
        {
            $pageURL = 'http';

            if ($_SERVER["HTTPS"] == "on")
            {
                $pageURL .= "s";
            }
            $pageURL .= "://";

            if ($_SERVER["SERVER_PORT"] != "80")
            {
                $pageURL .= $_SERVER["SERVER_NAME"].":" . $_SERVER["SERVER_PORT"] . $_SERVER['PHP_SELF'];
            }
            else
            {
                $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER['PHP_SELF'];
            }
            return $pageURL;
        }

    /*
     * 判断文件是否存在，不存在新建一个
     * */
    private  static function FileIsExist($file){
        if(!file_exists(self::$logDir.$file)){
			self::createLogFile($file);
			}
	}
    /*
     *新建一个日志文件
     * */
    private  static  function  CreateLogFile($file){
      //  chmod(self::$logDir,0777);
        //chmod(self::$logDir.$file,0777);
        $myfile = fopen(self::$logDir.$file, "w") or die("Unable to open file!");
        $txt = '<!DOCTYPE HTML><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" /><title>jaybril博客后台日志页</title></head><body style="background:#2c373b;color: #b2b9bC;line-height: 2em; font-size:0.8rem;"><div class="container"><div id="header" class="row"><div><h1 style=" text-align:center">日志</h1></div></div><div class="row"><table  style="margin:0 auto; width:96%" border="1"><tbody><tr><th>序号</th><th>时间</th><th>页面</th><th>描述</th></tr>';
        fwrite($myfile, $txt);
        fclose($myfile);
    }
}

?>
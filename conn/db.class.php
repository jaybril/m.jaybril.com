<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/log/log.class.php');
/**
 *
 */
class db {
    /**
     * *错误编号
     */
    public static $is_error = false;
    /**
     * *当执行出错时是否中断
     */
    public static $OnErrorStop = false;
    /**
     * *当执行出错时是否提示错误信息
     */
    public static $OnErrorShow = true;
    /**
     * *当前查询SQL语句
     */
    protected static $sql = '';
    /**
     * *mysqli 对象
     */
    protected static $mysqli = null;
    /**
     * *当前结果集
     */
    protected static $result = false;
    /**
     * *查询统计次数
     */
    protected static $query_count = 0;
    /**
     * *当前查询是否开户了事物处理
     */
    protected static $is_commit = false;
 
    /**
     * *连接Mysql
     */
    protected static function ConnectToMySqli() {
		try{	
		include_once('db.config.php');
        if (is_null(self :: $mysqli)) {
            self :: $mysqli = new mysqli(dbConfig::$hostname,dbConfig::$username,dbConfig::$password,dbConfig::$dbName,3306);
            if (mysqli_connect_errno()) {
                $error = 'Database Connect failed:'.mysqli_connect_error();
                Log::setLog(Log::$logForSqlConn,$error,Log::GetCurPageURL());
                exit();
            } else {
                self :: $mysqli -> query("SET character_set_connection=" .DBCHARSET. ", character_set_results=" .DBCHARSET. ", character_set_client=binary");
            } 
        } 
		}
		catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
    /**
     * *执行查询
     * 
     * @param  $sql [string] :SQL查询语句
     * @return 成功赋值并返回self::$result; 失败返回 false 如果有事务则回滚
     */
    public static function QueryBySqlString($sql) {
	 try{	
        self :: ConnectToMySqli();
        self :: $sql = $sql;
        self :: $result = self :: $mysqli -> query($sql);
        if (self :: $mysqli -> error) {
            $error="SQL Query Error:".self::$mysqli->error;
		    self :: $is_error = true;
            Log::setLog(Log::$logForSqlConn,$error,Log::GetCurPageURL());
            Log::setLog(Log::$logForSqlConn,$sql,Log::GetCurPageURL());
			if (self :: $OnErrorStop){
				exit();
			}
            return false;
        } 
	   $resultArray=array();
	   $count=0;
	    /* Fetch the results of the query 返回查询的结果 */   
       /* while($row = mysqli_fetch_assoc(self::$result)){   
            $resultArray[$count]=$row;
			$count++;
        } */  
        /* Destroy the result set and free the memory used for it 结束查询释放内存 */   
       // mysqli_free_result(self::$result);      
       /* Close the connection 关闭连接*/   
	   //mysqli_close(self::$mysqli);
       return self::$result;
	 }
	 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
	 /**
     * *查询指定SQl 所有记录
     * 
     * @param  $sql [string] :SQL查询语句
     * @param  $key_field [string] :指定记录结果键值使用哪个字段,默认为 false 使用 regI{0...count}
     * @param  $assoc [bool] :true 返回数组; false 返回stdClass对象;默认 false
     * @return 失败返回 false
     */
    public static function QueryAllRowsBySqlString($sql, $key_field = false, $assoc = false) {
	 try{
        if (self :: $result = self::QueryBySqlString($sql)) {
            return self :: GetAllRows($key_field, $assoc);
        } else {
            return false;
        } 
	 }
	 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
	 /**
     * 执行更新数据操作(通过数据)
     * 
     * @param  $table [string] 数据库表名称
     * @param  $data [array|stdClass] 待更新的数据
     * @param  $where [string] 更新条件
     * @return 成功 true; 失败 false
     */
    public static function UpdateByData($table, $data, $where) {
	 try{	
        $set = '';
        if (is_object($data) || is_array($data)) {
            foreach ($data as $k => $v) {
                self :: FormatValue($v);
                $set .= empty($set) ? ("`{$k}` = {$v}") : (", `{$k}` = {$v}");
            } 
        } else {
            $set = $data;
        }
         $sqlStr="UPDATE `{$table}` SET {$set} WHERE {$where}";
         //Log::setLog(Log::$logForSqlConn,$sqlStr,Log::GetCurPageURL());
         return self :: QueryBySqlString($sqlStr);
	 }
	 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
	/**
     * 执行更新数据操作(通过sql语句)
     * 
     * @param  $sql 拼装好的SQL语句
     * @return 成功 true; 失败 false
     */
    public static function UpdateBySqlString($sql) {
        Log::setLog(Log::$logForSqlConn,$sql,Log::GetCurPageURL());
	 try{	
        return self :: QueryBySqlString($sql);
	 }
	 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
    /**
     * 执行插入数据操作
     * 
     * @param  $table [string] 数据库表名称
     * @param  $data [array|stdClass] 待更新的数据
     * @param  $fields [string] 数据库字段，默认为 null。 为空时取 $data的 keys
     * @return 成功 true; 失败 false
     */
    public static function InsertByData($table, $data, $fields = null) {
	 try{	
        if ($fields == null) {
            foreach($data as $v) {
                if (is_array($v)) {
                    $fields = array_keys($v);
                } elseif (is_object($v)) {
                    foreach($v as $k2 => $v2) {
                        $fields[] = $k2;
                    } 
                } elseif (is_array($data)) {
                    $fields = array_keys($data);
                } elseif (is_object($data)) {
                    foreach($data as $k2 => $v2) {
                        $fields[] = $k2;
                    } 
                } 
                break;
            } 
        } 
        $_fields = '`' . implode('`, `', $fields) . '`';
        $_data = self :: FormatInsertData($data);
        return self :: QueryBySqlString("INSERT INTO `{$table}` ({$_fields}) VALUES {$_data}");
	 }
	 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
	/**
     * *统计表记录
     * 
     * @param  $table [string] 数据库表名称
     * @param  $where [string] SQL统计条件,默认为 1 查询整个表
     */
    public static function GetTotalRowsCount($table, $where = '1') {
	 try{	
        $sql = "SELECT count(*) FROM {$table} WHERE {$where}";
        self :: QueryBySqlString($sql);
        return self :: FetchOneRowCount();
	 }
	 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}
    } 
    /**
     * *取结果(self::$result)中第一行，第一列值
     * 
     * @return 没有结果返回 false
     */
    public static function FetchOneRowCount() {
        if (!empty(self :: $result)) {
            $row =mysqli_fetch_array(self::$result);  //self :: $result -> fetch_array();
            return $row[0];
        } else {
            return false;
        } 
    } 
    /**
     * *取结果$result中第一行记录
     * 
     * @param  $result [object] :查询结果数据集
     * @param  $assoc [bool] :true 返回数组; false 返回stdClass对象;默认 false
     * @return 没有结果返回 false
     */
    public static function GetOneRow($result = null , $assoc = false) {
        if ($result == null) $result = self :: $result;
        if (empty($result)) {
            return false;
        }
        return mysqli_fetch_array($result);
//        if ($assoc) {
//            return mysqli_fetch_assoc($result); //$result -> fetch_assoc();
//        } else {
//            return  mysqli_fetch_object($result);//$result -> fetch_object();
//
//        }
    } 
    /**
     * *取结果(self::$result)中所有记录
     * 
     * @param  $key_field [string] :指定记录结果键值使用哪个字段,默认为 false 则使用 regI{0...count}
     * @param  $assoc [bool] :true 返回数组; false 返回stdClass对象;默认 false
     * @return 没有结果返回 false
     */
    public static function GetAllRows($key_field = false, $assoc = false) {
        //$rows = ($assoc) ? array() : new stdClass;
        $rows=array();
        $regI = -1;
        while ($row = self :: GetOneRow(self :: $result, $assoc)) {
//            if ($key_field != false) {
//                $regI = ($assoc) ? $row[$key_field] : $row -> $key_field;
//            } else {
//                $regI++;
//            }
            $regI++;
            $rows[$regI]=$row;
//            if ($assoc) {
//                $rows[$regI] = $row;
//            } else {
//                $rows -> {$regI} = $row;
//            }
        } 
        self :: MysqliFreeResult();
        return ($regI > -1) ? $rows : false;
    } 
   
    /**
     * *格式化插入数据
     * 
     * @param  $data [array|stdClass] 待格式化的插入数据
     * @return insert 中 values 后的 SQL格式
     */
    protected static function FormatInsertData($data) {
        $output = '';
        $is_list = false;
        foreach ($data as $value) {
            if (is_object($value) || is_array($value)) {
                $is_list = true;
                $tmp = '';
                foreach ($value as $v) {
                    self :: FormatValue($v);
                    $tmp .= !empty($tmp) ? ", {$v}" : $v;
                } 
                $tmp = "(" . $tmp . ")";
                $output .= !empty($output) ? ", {$tmp}" : $tmp;
                unset($tmp);
            } else {
                self :: FormatValue($value);
                $output .= !empty($output) ? ", {$value}" : $value;
            } 
        } 
        if (!$is_list) $output = '(' . $output . ')';
        return $output;
    } 
    /**
     * *格式化值
     * 
     * @param  $ &$value [string] 待格式化的字符串,格式成可被数据库接受的格式
     */
    protected static function FormatValue(&$value) {
        $value = trim($value);
        if ($value === null || $value == '') {
            $value = 'NULL';
        } elseif (preg_match('/\[\w+\]\.\(.*?\)/', $value)) { // mysql函数 格式:[UNHEX].(参数);
            $value = preg_replace('/\[(\w+)\]\.\((.*?)\)/', "$1($2)", $value);
        } else {
            // $value = "'" . addslashes(stripslashes($value)) ."'";strip
            $value = "'" . addslashes(stripslashes($value)) . "'";
        } 
    } 
    /**
     * *返回最后一次插入的ID
     */
    public static function GetInsertId() {
        return mysqli_insert_id(self::$mysqli);
		//return self :: $mysqli -> insert_id;
    } 
       
    /**
     * *开始事物处理,关闭MYSQL的自动提交模式
     */
    public static function CommitBegin() {
        self :: ConnectToMySqli();
        self :: $is_error = false;
        self :: $mysqli -> autocommit(false); //使用事物处理,不自动提交
        self :: $is_commit = true;
    } 
    /**
     * *提交事物处理
     */
    public static function CommitEnd() {
        if (self :: $is_commit) {
            self :: $mysqli -> commit();
        } 
        self :: $mysqli -> autocommit(true); //不使用事物处理,开启MYSQL的自动提交模式
        self :: $is_commit = false;
        self :: $is_error = false;
    } 
    /**
     * *回滚事物处理
     */
    public static function RollBack() {
        self :: $mysqli -> rollback();
    } 
    /**
     * *释放数据集
     */
    public static function MysqliFreeResult($result = null) {
        if (is_null($result)) $result = self :: $result;
        @mysqli_free_result($result);
    } 
    /**
     * *选择数据库
     * 
     * @param  $dbname [string] 数据库名称
     */
    public static function SelectDb($dbname) {
        self :: ConnectToMySqli();
        return self :: $mysqli -> select_db($dbname);
    } 

}
/*
用法：
$result = db::query($sql);
$rows = new stdClass;
$regI = 0;
while($row = db::fetch_row($result)){
$rows->{$regI} = $row;
$regI++;
}
 
在上面有个 stdClass 类，这个类是PHP的默认基类，因此它可以不用声明而直接使用。这个基类只能传递属性，而不能定义方法。

事务操作方法：
db::commit_begin();

if(db::$is_error) db::rollback();
db::commit_end();
*/


//mysqli 接口说明
//
//mysqli_affected_rows -返回一个MySQL操作受影响的行数
//mysqli_autocommit -开启或关闭 autocommit 数据库修改
//mysqli_bind_param -别名mysqli_stmt_bind_param （ ）
//mysqli_bind_result -别名mysqli_stmt_bind_result （ ）
//mysqli_change_user -更改指定数据库连接的用户
//mysqli_character_set_name -返回数据库连接的默认字符集
//mysqli_client_encoding -别名mysqli_character_set_name （ ）
//mysqli_close -关闭当前打开数据库连接
//mysqli_commit -当前事物
//mysqli_connect_errno -返回最后一次操作的错误代码
//mysqli_connect_error -返回最后一次操作的错误字符串描述
//mysqli_connect -打开一个新的连接到MySQL服务器
//mysqli_data_seek -在当前记录结果集中任意移动行指针
//mysqli_debug -调试
//mysqli_disable_reads_from_master -禁用读取主对像
//mysqli_disable_rpl_parse -禁用RPL解析
//mysqli_dump_debug_info -转储调试信息的日志
//mysqli_embedded_connect -打开一个连接到嵌入式MySQL服务器
//mysqli_enable_reads_from_master -启用内容由主
//mysqli_enable_rpl_parse -启用RPL解析
//mysqli_errno -返回错误代码最近函数调用
//mysqli_error -返回一个字符串描述过去的错误
//mysqli_escape_string -别名mysqli_real_escape_string （ ）
//mysqli_execute -别名mysqli_stmt_execute （ ）
//mysqli_fetch_array -从结果集中取得一行作为关联，数字数组，或两者兼施
//mysqli_fetch_assoc -从结果集中取得一行作为关联数组
//mysqli_fetch_field_direct -获取元数据的一个单一的领域
//mysqli_fetch_field -返回明年领域中的结果集
//mysqli_fetch_fields -返回一个数组对象代表领域的结果集
//mysqli_fetch_lengths -返回长度列的当前行的结果集
//mysqli_fetch_object -返回当前行的结果集作为一个对象
//mysqli_fetch_row -取得结果集中取得一行作为枚举数组
//mysqli_fetch -别名mysqli_stmt_fetch （ ）
//mysqli_field_count -返回的列数最近查询
//mysqli_field_seek -设为结果指针到指定的外地抵消
//mysqli_field_tell -获取当前外地抵消的结果指针
//mysqli_free_result -释放内存与结果
//mysqli_get_client_info -返回MySQL客户端版本作为一个字符串
//mysqli_get_client_version -取得MySQL客户端信息
//mysqli_get_host_info -返回一个字符串代表的连接类型使用
//mysqli_get_metadata -别名mysqli_stmt_result_metadata （ ）
//mysqli_get_proto_info -返回版本的MySQL使用协议
//mysqli_get_server_info -返回版本的MySQL服务器
//mysqli_get_server_version -返回版本的MySQL服务器作为一个整数
//mysqli_info -检索信息，最近执行的查询
//mysqli_init -初始化MySQLi并返回一个资源使用mysqli_real_connect （ ）
//mysqli_insert_id -返回自动生成的编号使用最后查询
//mysqli_kill -要求服务器要杀死一个MySQL线程
//mysqli_master_query -强制执行查询总在主/从设置
//mysqli_more_results -检查是否有任何更多的查询结果来自一个多查询
//mysqli_multi_query -执行查询数据库
//mysqli_next_result -准备明年的结果multi_query
//mysqli_num_fields -获取的若干领域中的结果
//mysqli_num_rows -获取的行数的结果
//mysqli_options -设置选项
//mysqli_param_count -别名mysqli_stmt_param_count （ ）
//mysqli_ping -的Ping一个服务器连接，或尝试重新连接，如果已下降
//mysqli_prepare -准备一个SQL语句的执行
//mysqli_query -执行查询数据库
//mysqli_real_connect -打开一个连接到MySQL服务器
//mysqli_real_escape_string -转义特殊字符的字符串，用于SQL语句，并考虑到当前的字符集的连接
//mysqli_real_query -执行一个SQL查询
//mysqli_report -启用或禁用内部报告功能
//mysqli_rollback -回滚当前事务
//mysqli_rpl_parse_enabled -检查是否启用RPL解析
//mysqli_rpl_probe - RPL探针
//mysqli_rpl_query_type -返回RPL查询类型
//mysqli_select_db -选择的默认数据库数据库查询
//mysqli_send_long_data -别名mysqli_stmt_send_long_data （ ）
//mysqli_send_query -发送查询并返回
//mysqli_server_end -关机嵌入式服务器
//mysqli_server_init -初始化嵌入式服务器
//mysqli_set_charset -集的默认客户端字符集
//mysqli_set_opt -别名mysqli_options （ ）
//mysqli_sqlstate -返回SQLSTATE错误从一个MySQL操作
//mysqli_ssl_set -用于建立安全连接使用SSL
//mysqli_stat -获取当前的系统状态
//mysqli_stmt_affected_rows -返回总数列改变，删除或插入的最后执行的声明
//mysqli_stmt_bind_param -绑定变量一份声明作为参数
//mysqli_stmt_bind_result -绑定变量的一份声明中存储的结果
//mysqli_stmt_close -关闭一份声明
//mysqli_stmt_data_seek -寻找一个任意行声明的结果集
//mysqli_stmt_errno -返回错误代码的最新声明呼吁
//mysqli_stmt_error -返回一个字符串描述最后声明错误
//mysqli_stmt_execute -执行一个准备查询
//mysqli_stmt_fetch -获取结果一份准备好的声明中纳入约束变量
//mysqli_stmt_free_result -免储存记忆的结果给予处理的声明
//mysqli_stmt_init -初始化了言，并返回一个对象，用于mysqli_stmt_prepare
//mysqli_stmt_num_rows -返回的行数报表结果集
//mysqli_stmt_param_count -返回一些参数给定的声明
//mysqli_stmt_prepare -准备一个SQL语句的执行
//mysqli_stmt_reset -重置一份声明
//mysqli_stmt_result_metadata -返回结果集元数据的一份书面声明
//mysqli_stmt_send_long_data -发送数据块
//mysqli_stmt_sqlstate -返回SQLSTATE错误行动从以往的声明
//mysqli_stmt_store_result -转让的结果集由一份声明
//mysqli_store_result -转让的结果集的最后查询
//mysqli_thread_id -返回线程ID为当前连接
//mysqli_thread_safe -返回是否线程安全考虑或不
//mysqli_use_result -开创检索结果集
//mysqli_warning_count -返回一些警告过去查询提供链接
?> 
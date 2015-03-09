<?php
class BaseDao
{
	var $ErrMsg;
	var $baseresult;
	//构造函数
	function BaseDao()
	{		
	}
	//返回错误原因
	function getErrorMsg($link)
	{
		$msg = mysql_error ($link);
		return $msg;
		//return $this->ErrMsg;
	}
	//返回最后插入的id, 该id必须是自动增长的
	function getLastID($link)
	{
		$id = mysql_insert_id($link);
		return $id;
	}
	//update/delete时影响的记录条数
	function getAffectedRows($link)
	{
		$count = mysql_affected_rows($link);
		return $count;
	}
	//返回查询结果中的列数
	function getFieldNumber($baseresult, $link)
	{
		$count = mysql_num_fields($baseresult, $link);
		return $count;
	}
	//返回查询结果中的行数
	function getRowCount($baseresult, $link)
	{
		$count = mysql_num_rows($baseresult, $link);
		return $count;
	}
	//执行SQL语句，insert, select, update, delete均可。
	function ExecuteSQL($sql, $link)
	{
		$this->baseresult = mysql_query($sql, $link);
		if(!$this->baseresult)
		{
			$err_file = debug_backtrace();
			error_log(date("[Y-m-d H:i:s]")." - [".$_SERVER['REQUEST_URI']."] :\n".mysql_error()."\n".$sql."\n".print_r($err_file,true)."\n", 3, "/tmp/cp_sql_err.log");
		}
		return $this->baseresult;
	}
	//执行Select语句，只返回一条记录的情况，可以直接取结果
	function ExecuteOneSelectSQL($sqlString, $link)
	{
		$this->baseresult = $this->ExecuteSQL($sqlString, $link);
		if (!$this->baseresult) 
		{
	    	return $this->baseresult;
		}
		else 
		{
			$Ret = $this->FetchRowValue();
			$this->FreeSelectResult();
			return $Ret;
		}		
	}
	//执行Select语句，如果有多条纪录，请参考该例子，这个函数不能在这里实现，
	//需要到处理查询的程序中处理。
	function ExecuteMultipleSelectSQL($sqlString, $link)
	{
		$this->baseresult = $this->ExecuteSQL($sqlString, $link);
		if (!$this->baseresult) {
	    	return $this->baseresult;
		}
		else 
		{
			$baseresultArray = array();
			$i = 0;
			while(true)
			{
				$row = $this->FetchRowValue();
				if(!$row)	break;
				$baseresultArray[$i++] = $row;
			}
			$this->FreeSelectResult();
			return $baseresultArray;
		}		
	}
	//释放查询结果
	function FreeSelectResult()
	{
		mysql_free_result($this->baseresult);
	}
	//取查询结果中一行的内容。
	function FetchRowValue()
	{
		$row = mysql_fetch_array($this->baseresult, MYSQL_BOTH);
		if(!$row)
		{
			return false;
		}
		else 
		{
			//要先判断取出的数据有几行
			$this->AssignRowValue($row);
			return $row;
		}		
	}
	//将查询结果中的行的内容赋值到变量，需要继承（重写）
	function AssignRowValue($row)
	{
	}
}
?>
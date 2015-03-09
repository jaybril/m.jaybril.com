 <?php
 include_once($_SERVER['DOCUMENT_ROOT'].'/conn/db.class.php');
 include_once($_SERVER['DOCUMENT_ROOT'].'/log/log.class.php');
 //include_once('base_dao.php');
 class UsersDao{
        var $table_name = 'b_users';
        var $id;
        var $name;
        var $password;
        var $user_icon;
        var $register_time;
        var $true_name;
        var $email;
        var $mobile_no;
    /*
     * 把结果转换成对象形式访问，仅支持单表查询
     * */
    function AssignRowValue($row)
    {
    	$this->id = $row["id"];
    	$this->name = $row["name"];
    	$this->password = $row["password"];
    	$this->user_icon = $row["user_icon"];
    	$this->register_time = $row["register_time"];
    	$this->true_name = $row["true_name"];
    	$this->email = $row["email"];
    	$this->mobile_no = $row["mobile_no"];
    }
     /*
      * 根据id查出该用户
      */
	function GetUserByUserId($id)
	{
		try{
			$sql = "SELECT * FROM $this->table_name WHERE id = $id";
			$res=db::QueryAllRowsBySqlString($sql);
			return self::AssignRowValue($res[0]);
			}
		 catch(Exception $e){
			Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
			}	
	}
     /*
      * 根据name查出该用户
      */
     function GetUserByUserName($name)
     {
         try{
             $sql = "SELECT * FROM $this->table_name WHERE name = '$name'";
             $res=db::QueryAllRowsBySqlString($sql);
             return self::AssignRowValue($res[0]);
         }
         catch(Exception $e){
             Log::setLog(Log::$logForSqlConn,$e->getMessage(),Log::GetCurPageURL());
         }
     }
	 
	 }
  ?>
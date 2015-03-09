<?php 
 include_once( './conn/db.class.php');
include_once('./table/users_dao.php');
 //print_r(get_extension_funcs('mysqli'));
 $data=array('liweisi','fuck');
 $fields=array('name','password');
 //db::InsertByData('b_users',$data,$fields);
 $upArr=array(
     'name'=>'gujiajin',
     'password'=>'lala'
 );
// db::UpdateByData('b_users',$upArr,'name="jaybril"');
 //$roew=db::query('select * from b_users'); 
  $row=db::QueryAllRowsBySqlString('select * from b_users');
  //echo $row[0][1];
$userDao=new UsersDao();
$roww=$userDao->GetUserByUserId(1);
echo ($userDao->password);
//$userDao=$row[0];
//echo $userDao->name;
//echo $row[0].'<br/>';
//echo $row[0];
?>
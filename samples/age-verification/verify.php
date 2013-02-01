<?php
 
session_start();
if($_SERVER['REQUEST_METHOD']=='POST')
{
if(isset($_POST['YES']))
{
$redirect=isset($_GET['return'])?urldecode($_GET['return']):'./';
$expire=isset($_GET['x']) && is_numeric($_GET['x'])?intval($_GET['x']):-1;
if($expire==-1)
{
$_SESSION['verified']="yes";
header("location: ".$redirect);
exit(0);
}
if($expire==0)
{
setcookie("verified", "yes",mktime(0,0,0,01,01,date("Y")+30));
$_SESSION['verified']="yes";
header("location: ".$redirect);
exit(0);
}
setcookie("verified", "yes",(time()+$expire));
$_SESSION['verified']="yes";
header("location: ".$redirect);
exit(0);
}else{
header("location: http://www.youtube.com/watch?v=gppbrYIcR80");
exit(0);
}
}
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Alcohol Age Verification Example Page</title>
<link href="style.css" type="text/css" rel="stylesheet" />
</head>
<body>
<form action="" method="POST">
<p id="textVerify">PLEASE VERIFY THAT YOU ARE OVER AGE 21 BEFORE ENTERING THIS SITE</p>
<input name="NO" id="no" type="Submit" value="NO - Leave" />
<input name="YES" id="yes" type="Submit" value="Yes - Enter" />
</form>
</body>
</html>
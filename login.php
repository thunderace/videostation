<?php
require_once('lib/config.php');
require_once('lib/system_config.php');
require_once('lib/lang.php');
if($INSTALL === TRUE){
session_unset();
session_destroy();
setcookie('user');
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="css/nyroModal.css">
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.nyroModal.custom.min.js"></script>
</head>
<body>
<?php
if($INSTALL){
echo '<div style="width:100%;text-align:center;color:red;">'.warning2.'</div>';
}
?>
<nav><div><h1>Login</h1></div>
</nav>
<div id="content">
<form method="POST" action="index.php?action=login"><br>
<table cellspacing="10" style="margin-left:auto;margin-right:auto;">
<tr><td rowspan="5"><img src="images/login.png" alt="login"></td></tr>
<tr><td><?php echo username;?> :</td><td><input type="text" name="user" class="form"<?php if($INSTALL) echo 'value="admin"';?>></td></tr>
<tr><td><?php echo password;?> :</td><td><input type="password" name="pass" class="form"></td></tr>
<tr><td><?php echo rememberme;?></td><td><input type="checkbox" name="cookie" class="form"></td></tr>
<tr><td colspan="2"><input type="submit" value="Login!" name="submit" class="form"></td></tr>
</table>
</form>
</div>
</body>
</html>

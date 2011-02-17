<?php
define ("ABSOLUTE_PATH", dirname (__FILE__) . "/");

if (extension_loaded ('zlib')) {
    ob_start ('ob_gzhandler');
}

require_once (ABSOLUTE_PATH . "lib/webstart.php");
if (! is_file (ABSOLUTE_PATH . "config/config.php")) {
	die ('You have to <a href="./install.php">install</a> Online-Bookmarks.');
}
else {
	require_once (ABSOLUTE_PATH . "config/config.php");
}
require_once (ABSOLUTE_PATH . "lib/mysql.php");
$mysql = & new mysql;
require_once (ABSOLUTE_PATH . "lib/auth.php");
$auth = & new Auth;
require_once (ABSOLUTE_PATH . "lib/lib.php");
require_once (ABSOLUTE_PATH . "lib/login.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Online-Bookmarks</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Pragma" content="No-cache">
		<link rel="stylesheet" type="text/css" href="./style.css">
		<link rel="shortcut icon" href="favicon.ico">
		<script type="text/javascript" src="./lib/lib.js"></script>
		</head>
<body>

<?php

if (is_file (ABSOLUTE_PATH . "install.php")) {
	message ('Remove "install.php" before using Online-Bookmarks.');
}

if ($display_login_form) {
	$auth->display_login_form ();
	require_once (ABSOLUTE_PATH . "footer.php");
}

?>

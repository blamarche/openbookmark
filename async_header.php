<?php
define ("ABSOLUTE_PATH", dirname (__FILE__) . "/");

if (extension_loaded ('zlib')) {
    ob_start ('ob_gzhandler');
}

require_once (ABSOLUTE_PATH . "lib/webstart.php");
if (! is_file (ABSOLUTE_PATH . "config/config.php")) {
	die ('You have to <a href="./install.php">install</a> OpenBookmark.');
}
else {
	require_once (ABSOLUTE_PATH . "config/config.php");
}
require_once (ABSOLUTE_PATH . "lib/mysql.php");
$mysql = new mysql;
require_once (ABSOLUTE_PATH . "lib/auth.php");
$auth = new Auth;
require_once (ABSOLUTE_PATH . "lib/lib.php");
require_once (ABSOLUTE_PATH . "lib/login.php");

//if (is_file (ABSOLUTE_PATH . "install.php")) {
//	message ('Remove "install.php" before using OpenBookmark.');
//}

if ($display_login_form) {
	$auth->display_login_form ();
	require_once (ABSOLUTE_PATH . "footer.php");
}

?>
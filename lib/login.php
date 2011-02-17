<?php
if (basename ($_SERVER['SCRIPT_NAME']) == basename (__FILE__)) {
	die ("no direct access allowed");
}

$display_login_form = false;

if (isset ($_SESSION)) {
	if (isset ($_POST['username']) && $_POST['username'] != '' && ! $_SESSION['logged_in']) {
		$auth->login ();
	}
	if (isset ($_GET['login']) && $_GET['login'] && ! $_SESSION['logged_in']) {
		$display_login_form = true;
	}
	if (isset ($_GET['logout']) && $_GET['logout'] && $_SESSION['logged_in']) {
		$auth->logout ();
	}
	if (isset ($_SESSION['username']) && ! check_username ($_SESSION['username'])) {  # XXX hoffe das ist ok so.
		$auth->logout ();
	}

	if (isset ($_SESSION['logged_in']) && $_SESSION['logged_in']) {
		if (isset ($_SESSION['username']) && $_SESSION['username'] != '') {
			$username = $_SESSION['username'];
			$query = sprintf ("SELECT * FROM user WHERE username='%s'",
				$mysql->escape ($username));

			# now get the settings.
			if ($mysql->query ($query)) {
				$settings = mysql_fetch_assoc ($mysql->result);
			}
			else {
				message ($mysql->error);
			}
		
			unset ($settings['password']);
		}
		else {
			# instead of user preferences, set default settings.
			$settings = default_settings ();
			$username = '';
			$auth->logout ();
		}
	}
	else {
		$settings = default_settings ();
		$username = '';
		$auth->logout ();
	}
}
else {
	$settings = default_settings ();
	$username = '';
	$auth->logout ();
}

function default_settings () {
	$settings = array (
		'root_folder_name' => '',
		'column_width_folder' => 400,
		'column_width_bookmark' => 0,
		'table_height' => 400,
		'confirm_delete' => true,
		'open_new_window' => true,
		'show_bookmark_description' => true,
		'show_bookmark_icon' => true,
		'show_column_date' => true,
		'date_format' => '0',
		'show_column_edit' => false,
		'show_column_move' => false,
		'show_column_delete' => false,
		'fast_folder_minus' => true,
		'fast_folder_plus' => true,
		'fast_symbol' => true,
		'simple_tree_mode' => false,
		);
	return $settings;
}

# adjust some settings
if ($settings['column_width_bookmark'] == 0 || ! is_numeric ($settings['column_width_bookmark'])) {
	$column_width_bookmark = "100%";
}
else {
	$column_width_bookmark = $settings['column_width_bookmark'] . "px";
}

$column_width_folder = $settings['column_width_folder'] . "px";
$table_height = $settings['table_height'] . "px";

if ( ! is_numeric ($settings['date_format'])) {
	$settings['date_format'] = 0;
}

# set some often used vars
$folderid = set_get_folderid ();
$expand   = set_get_expand ();

?>
<?php
define ("ABSOLUTE_PATH", dirname (__FILE__) . "/");
require_once (ABSOLUTE_PATH . "lib/webstart.php");
require_once (ABSOLUTE_PATH . "lib/lib.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Online-Bookmarks</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
<body>

<?php

$mysql_hostname = set_post_string_var ('mysql_hostname', 'localhost');
$mysql_db_name = set_post_string_var ('mysql_db_name', 'bookmarks');
$mysql_db_username = set_post_string_var ('mysql_db_username', 'bookmarkmgr');
$mysql_db_password = set_post_string_var ('mysql_db_password');
$mysql_db_create = set_post_bool_var ('mysql_db_create', false);
$mysql_db_su_username = set_post_string_var ('mysql_db_su_username', 'root');
$mysql_db_su_password = set_post_string_var ('mysql_db_su_password');

$cookie_name = set_post_string_var ('cookie_name', 'ob_cookie');
$cookie_domain = set_post_string_var ('cookie_domain', '');
$cookie_path = set_post_string_var ('cookie_path', '/');
$cookie_seed = set_post_string_var ('cookie_seed', random_string ());
$cookie_expire = set_post_string_var ('cookie_expire', '31536000');

$submit = set_post_bool_var ('submit', false);

$admin_message = '';

if (intval(str_replace('.', '', phpversion())) < 430) {
	print_msg ('You are running PHP version '.PHP_VERSION.'. Online-Bookmarks requires at least PHP 4.3.0 to run properly. You must upgrade your PHP installation before you can continue.', "error");
}

############## database control ##############

function create_table_bookmark () {
		$query = "CREATE TABLE bookmark (
			user char(20) NOT NULL default '',
			title char(70) NOT NULL default '',
			url char(200) NOT NULL default '',
			description mediumtext default NULL,
			private enum('0','1') default NULL,
			date timestamp(14) NOT NULL,
			childof int(11) NOT NULL default '0',
			id int(11) NOT NULL auto_increment,
			deleted enum('0','1') NOT NULL default '0',
			favicon varchar(200),
			public enum('0','1') NOT NULL default '0',
			PRIMARY KEY (id),
			FULLTEXT KEY title (title,url,description)
		) TYPE=MyISAM";

	if (mysql_query ($query)) {
		return true;
	}
	else {
		return false;
	}
}

function create_table_folder () {
	$query = "CREATE TABLE folder (
			id int(11) NOT NULL auto_increment,
			childof int(11) NOT NULL default '0',
			name char(70) NOT NULL default '',
			user char(20) NOT NULL default '',
			deleted enum('0','1') NOT NULL default '0',
			public enum('0','1') NOT NULL default '0',
			UNIQUE KEY id (id)
		) TYPE=MyISAM;";

	if (mysql_query ($query)) {
		return true;
	}
	else {
		return false;
	}
}

function create_table_user () {
	$query = "CREATE TABLE user (
			username char(50) NOT NULL default '',
			password char(50) NOT NULL default '',
			admin enum('0','1') NOT NULL default '0',
			language char(20) NOT NULL default '',
			root_folder_name char(50) NOT NULL default 'My Bookmarks',
			column_width_folder smallint(3) NOT NULL default '400',
			column_width_bookmark smallint(3) NOT NULL default '0',
			table_height smallint(3) NOT NULL default '400',
			confirm_delete enum('0','1') NOT NULL default '1',
			open_new_window enum('0','1') NOT NULL default '1',
			show_bookmark_description enum('0','1') NOT NULL default '1',
			show_bookmark_icon enum('0','1') NOT NULL default '1',
			show_column_date enum('0','1') NOT NULL default '1',
			date_format SMALLINT(6) NOT NULL DEFAULT '0',
			show_column_edit enum('0','1') NOT NULL default '1',
			show_column_move enum('0','1') NOT NULL default '1',
			show_column_delete enum('0','1') NOT NULL default '1',
			fast_folder_minus enum('0','1') NOT NULL default '1',
			fast_folder_plus enum('0','1') NOT NULL default '1',
			fast_symbol enum('0','1') NOT NULL default '1',
			simple_tree_mode enum('0','1') NOT NULL default '0',
			show_public enum('0','1') NOT NULL default '1',
			UNIQUE KEY id (username)
		) TYPE=MyISAM;";

	if (mysql_query ($query)) {
		return true;
	}
	else {
		return false;
	}
}

function create_admin_user () {
	$query = "INSERT INTO user (username, password, admin) 
			  VALUES ('admin', MD5('admin'), '1');";

	if (mysql_query ($query)) {
		return true;
	}
	else {
		return false;
	}
}

function random_string ($max = 14){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_";
	$string = '';
	for($i = 0; $i < $max; $i++){
		$rand_key = mt_rand (0, strlen($chars));
		$string  .= substr ($chars, $rand_key, 1);
	}
	return str_shuffle ($string);
}

function print_msg ($message, $type = "") {
	if ($type == "success") {
		$color = "green";
	}
	else if ($type == "error") {
		$color = "red";
	}
	else if ($type == "notice") {
		$color = "orange";
	}
	else {
		$color = "black";
	}
	echo '<div style="font:bold 12pt Times; color: ' . $color . '">' . $message . '</div>' . "\n";
}

function check_table_version ($table, $field) {
	$query = "DESC $table";
	$return = false;
	if ($result = mysql_query ($query)) {
		while ($row = mysql_fetch_row ($result)) {
			if ($row[0] == $field) {
				$return = true;
				break;
			}
		}
	}
	return $return;
}

function upgrade_table ($table, $field, $query) {
	if (check_table_version ($table, $field)) {
		print_msg ("Table $table contains '$field' field, good.", "success");
	}
	else {
		print_msg ("Table $table does not contain $field field, attempting to upgrade", "notice");
		if (mysql_query ($query)) {
			print_msg ("Table $table altered, $field added.", "success");
		}
		else {
			print_msg ("Failure! Table $table not changed.", "error");
		}
	}
}


############## html stuff ##############

function html_db () {
	global $mysql_hostname,
	       $mysql_db_name, 
	       $mysql_db_username, 
	       $mysql_db_su_username, 
	       $cookie_name, 
	       $cookie_domain, 
	       $cookie_path, 
	       $cookie_seed, 
	       $cookie_expire;
	?>
	
	<h3>Database connection:</h3>

	<form method="POST" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>">
	<table>
		<tr>
			<td>Database hostname:</td>
			<td><input type="text" name="mysql_hostname" value="<?php echo $mysql_hostname; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Database name:</td>
			<td><input type="text" name="mysql_db_name" value="<?php echo $mysql_db_name; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Database username:</td>
			<td><input type="text" name="mysql_db_username" value="<?php echo $mysql_db_username; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Database password:</td>
			<td><input type="password" name="mysql_db_password" value=""></td>
			<td></td>
		</tr>

		<tr>
			<td>Create new database:</td>
			<td><input type="checkbox" name="mysql_db_create"></td>
			<td></td>
		</tr>

		<tr>
			<td>using Superuser account:</td>
			<td><input type="text" name="mysql_db_su_username" value="<?php echo $mysql_db_su_username; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Superuser password:</td>
			<td><input type="password" name="mysql_db_su_password" value=""></td>
			<td></td>
		</tr>

		<tr>
			<td><h3>Cookie settings:</h3></td>
			<td></td>
			<td></td>
		</tr>

		<tr>
			<td>Cookie name:</td>
			<td><input type="text" name="cookie_name" value="<?php echo $cookie_name; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Cookie domain:</td>
			<td><input type="text" name="cookie_domain" value="<?php echo $cookie_domain; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Cookie path:</td>
			<td><input type="text" name="cookie_path" value="<?php echo $cookie_path; ?>"></td>
			<td></td>
		</tr>

		<tr>
			<td>Cookie seed:</td>
			<td><input type="text" name="cookie_seed" value="<?php echo $cookie_seed; ?>"></td>
			<td>Just some random junk.</td>
		</tr>

		<tr>
			<td>Cookie expire:</td>
			<td><input type="text" name="cookie_expire" value="<?php echo $cookie_expire; ?>"></td>
			<td>Set an amount of seconds when the cookie will expire.</td>
		</tr>

		<tr>
			<td></td>
			<td><input type="submit" name="submit"></td>
			<td></td>
		</tr>
	</table>
	</form>
	
	<?php
}

if ($submit) {
	if ($mysql_db_create) {
		if (! @mysql_connect ($mysql_hostname, $mysql_db_su_username, $mysql_db_su_password)) {
			html_db ();
			print_msg (mysql_error (), "error");
			require_once (ABSOLUTE_PATH . "footer.php");
		}
		else {
			if (mysql_query ("CREATE DATABASE $mysql_db_name")) {
				print_msg ("Database $mysql_db_name created", "success");
			}
			else {
				html_db ();
				print_msg (mysql_error (), "error");
				require_once (ABSOLUTE_PATH . "footer.php");
			}

			if (mysql_query ("GRANT ALL PRIVILEGES ON $mysql_db_name.* TO '$mysql_db_username'@'$mysql_hostname' IDENTIFIED BY '$mysql_db_password'")) {
				print_msg ("User $mysql_db_username created", "success");
			}
			else {
				html_db ();
				print_msg (mysql_error (), "error");
				require_once (ABSOLUTE_PATH . "footer.php");
			}
		}
	}

	@mysql_close ();

	$dsn = array(
		'db_username' => $mysql_db_username,
		'db_password' => $mysql_db_password,
		'db_hostname' => $mysql_hostname,
		'db_name'     => $mysql_db_name,
	);

	if (! @mysql_connect ($dsn['db_hostname'], $dsn['db_username'], $dsn['db_password'])) {
		html_db ();
		print_msg (mysql_error (), "error");
	}
	else {
		if (! @mysql_select_db ($dsn['db_name'])) {
			html_db ();
			print_msg (mysql_error (), "error");
		}
		else {
			############## DB support ##############
			print_msg ("DB connection succeeded", "success");

			$query = "SHOW TABLES";
			$tables = array ();
			$result = mysql_query ($query);

			while ($row = mysql_fetch_row ($result)) {
				array_push ($tables, $row[0]);
			}

			# the bookmark table
			if (!in_array ("bookmark", $tables)) {
				if (create_table_bookmark ()) {
					print_msg ("Table bookmark created", "success");
				}
				else {
					print_msg (mysql_error (), "error");
				}
			}
			else {
				print_msg ("Table bookmark exists, checking for version:", "notice");
				
				# check for favicon support
				upgrade_table ("bookmark", "favicon", "ALTER TABLE bookmark ADD COLUMN favicon varchar(200)");

				# check for public field in table
				upgrade_table ("bookmark", "public", "ALTER TABLE bookmark ADD COLUMN public ENUM('0','1') DEFAULT 0 NOT NULL");
			}

			# the folder table
			if (!in_array ("folder", $tables)) {
				if (create_table_folder ()) {
					print_msg ("Table folder created", "success");
				}
				else {
					print_msg (mysql_error (), "error");
				}
			}
			else {
				print_msg ("Table folder exists, checking for version:", "notice");

				# check for public field in table
				upgrade_table ("folder", "public", "ALTER TABLE folder ADD COLUMN public ENUM('0','1') DEFAULT 0 NOT NULL");
			}



			# the user table
			if (!in_array ("user", $tables)) {
				if (create_table_user ()) {
					print_msg ("Table user created", "success");
					if (create_admin_user ()) {
						print_msg ("Admin user created (see below)", "success");
						$admin_message = 'Initial user created. Login with username "admin" and password "admin"';
					}
				}
				else {
					print_msg (mysql_error (), "error");
				}
			}
			else {
				print_msg ("Table user exists, checking for version:", "notice");

				# check for date_format field in table
				upgrade_table ("user", "date_format", "ALTER TABLE user ADD COLUMN date_format SMALLINT(6) NOT NULL DEFAULT '0' AFTER show_column_date");

				# check for show_public field in table
				upgrade_table ("user", "show_public", "ALTER TABLE user ADD COLUMN show_public ENUM('0','1') DEFAULT 1 NOT NULL");

				# check for admin field in table
				upgrade_table ("user", "admin", "ALTER TABLE user ADD COLUMN admin ENUM('0','1') DEFAULT 0 NOT NULL AFTER password");
			}

			############## favicon support ##############
		
			if ($convert = @exec ('which convert')) {
				$convert_favicons = "true";
				print_msg ("ImageMagick convert found: $convert", "success");
			}
			else {
				$convert = "";
				$convert_favicons = "false";
				print_msg ("ImageMagick convert not found. Make sure ImageMagick is installed and specify location of convert manually or set \$convert_favicons to false.", "error");
			}
		
			if ($identify = @exec ('which identify')) {
				$convert_favicons = "true";
				print_msg ("ImageMagick identify found: $identify", "success");
			}
			else {
				$identify = "";
				$convert_favicons = "false";
				print_msg ("ImageMagick identify not found. Make sure ImageMagick is installed and specify location of identify manually or set \$convert_favicons to false.", "error");
			}
		
			if (is_writable ("./favicons/")) {
				print_msg ("./favicons directory is writable by the webserver, good.", "success");
			}
			else {
				print_msg ("./favicons directory is not writable by the webserver. Adjust permissions manually.", "error");
			}


			$config = '
&lt;?php
if (basename ($_SERVER[\'SCRIPT_NAME\']) == basename (__FILE__)) {
	die ("no direct access allowed");
}

$dsn = array(
	\'username\' => \'' . $mysql_db_username . '\',
	\'password\' => \'' . $mysql_db_password . '\',
	\'hostspec\' => \'' . $mysql_hostname . '\',
	\'database\' => \'' . $mysql_db_name . '\',
);

$cookie = array (
	\'name\'   => \'' . $cookie_name   . '\',
	\'domain\' => \'' . $cookie_domain . '\',
	\'path\'   => \'' . $cookie_path   . '\',
	\'seed\'   => \'' . $cookie_seed   . '\',
	\'expire\' => time() + ' . $cookie_expire . ',
);

# Feel free to add values to this list as you like
# according to the PHP documentation 
# http://www.php.net/manual/en/function.date.php
$date_formats = array (
	\'d/m/Y\',
	\'Y-m-d\',
	\'m/d/Y\',
	\'d.m.Y\',
	\'F j, Y\',
	\'dS \o\f F Y\',
	\'dS F Y\',
	\'d F Y\',
	\'d. M Y\',
	\'Y F d\',
	\'F d, Y\',
	\'M. d, Y\',
	\'m/d/Y\',
	\'m-d-Y\',
	\'m.d.Y\',
	\'m.d.y\',
);

$convert_favicons = ' . $convert_favicons . ';
$convert = \'' . $convert . '\';
$identify = \'' . $identify . '\';
$timeout = 5;
$folder_closed = \'&lt;img src="./images/folder.gif" alt=""&gt;\';
$folder_opened = \'&lt;img src="./images/folder_open.gif" alt=""&gt;\';
$folder_closed_public = \'&lt;img src="./images/folder_red.gif" alt=""&gt;\';
$folder_opened_public = \'&lt;img src="./images/folder_open_red.gif" alt=""&gt;\';
$bookmark_image = \'&lt;img src="./images/bookmark_image.gif" alt=""&gt;\';
$plus = \'&lt;img src="./images/plus.gif" alt=""&gt;&nbsp;\';
$minus = \'&lt;img src="./images/minus.gif" alt=""&gt;&nbsp;\';
$neutral = \'&lt;img src="./images/spacer.gif" width="13" height="1" alt=""&gt;&nbsp;\';
$edit_image = \'&lt;img src="./images/edit.gif" title="%s" alt=""&gt;\';
$move_image = \'&lt;img src="./images/move.gif" title="%s" alt=""&gt;\';
$delete_image = \'&lt;img src="./images/delete.gif" title="%s" alt=""&gt;\';
$delimiter = "/";
?&gt;';

			echo '<p>Paste the configuration shown below in the configuration file <span style="font-family:courier">./config/config.php</span></p>' . "\n";
			if ($admin_message != '') {
				echo $admin_message;
			}
			print_msg ("<p>IMPORTANT! Do not forget to remove this install.php script.</p>");
			echo '<pre style="background-color: #E0E0E0; border: 1px black solid; padding: 20px">';
			echo $config;
			echo "</pre>\n";
			echo '<a href="./index.php">Now go Bookmark...</a>';
		}
	}
}
else {
	html_db ();
}

require_once (ABSOLUTE_PATH . "footer.php");

?>

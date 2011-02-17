<?php

if (!isset ($_POST['browser']) || $_POST['browser'] == "" ||
         ($_POST['browser'] != "netscape" &&
          $_POST['browser'] != "opera" &&
          $_POST['browser'] != "IE")) {

	# header.php is included here, because we want to print 
	# plain text when exporting bookmarks, so that browsers
	# can handle results better. header.php is needed only to
	# display html.
	require_once ("./header.php");
	logged_in_only ();

	$folderid = set_get_folderid ();

	# get the browser type for default setting below if possible
	if( eregi ("opera", $_SERVER['HTTP_USER_AGENT'])) {
		$default_browser = "opera";
	}
	else if (eregi ("msie", $_SERVER['HTTP_USER_AGENT'])) {
		$default_browser = "IE";
	}
	else{
		$default_browser = "netscape";
	}
?>

<h1 id="caption">Export Bookmarks</h1>

<!-- Wrapper starts here. -->
<div style="min-width: <?php echo 230 + $settings['column_width_folder']; ?>px;">
	<!-- Menu starts here. -->
	<div id="menu">
		<h2 class="nav">Bookmarks</h2>
		<ul class="nav">
		  <li><a href="./index.php">My Bookmarks</a></li>
		  <li><a href="./shared.php">Shared Bookmarks</a></li>
		</ul>
	
		<h2 class="nav">Tools</h2>
		<ul class="nav">
			<?php if (admin_only ()) { ?>
			<li><a href="./admin.php">Admin</a></li>
			<?php } ?>
			<li><a href="./import.php">Import</a></li>
			<li><a href="./export.php">Export</a></li>
			<li><a href="./sidebar.php">View as Sidebar</a></li>
			<li><a href="./settings.php">Settings</a></li>
			<li><a href="./index.php?logout=1">Logout</a></li>
		</ul>
	<!-- Menu ends here. -->
	</div>

	<!-- Main content starts here. -->
	<div id="main">
		<div id="content">

<form enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME'];?>" method="POST">
  <table border="0">
    <tr>
      <td>
        Export Bookmarks to Browser:
      </td>
      <td width="<?php echo $column_width_folder?>">
        <select name="browser">
          <option value="IE"<?php if ($default_browser == "IE") {echo " selected"; } ?>>Internet Explorer</option>
          <option value="netscape"<?php if ($default_browser == "netscape") {echo " selected"; } ?>>Netscape / Mozilla</option>
          <option value="opera"<?php if ($default_browser == "opera") {echo " selected"; } ?>>Opera .adr</option>
        </select>
      </td>
    </tr>

	<tr>
		<td>Character encoding</td>
		<td>
			<select name="charset">
			<?php
			$charsets = return_charsets ();
			foreach ($charsets as $value) {
				$selected = '';
				if ($value == 'UTF-8') {$selected = ' selected';}
				echo '<option value="'.$value.'"'.$selected.'>'.$value.'</option>' . "\n";
			}
			?>
			</select>
		</td>
	</tr>

    <tr>
      <td>
        Folder to export:
      </td>
      <td>
	<div style="width:<?php echo $column_width_folder; ?>; height:350px; overflow:auto;">

	<?php
	require_once (ABSOLUTE_PATH . "folders.php");
	$tree = & new folder;
	$tree->make_tree (0);
	$tree->print_tree ();
	?>

	</div>
      </td>
    </tr>

    <tr>
      <td>
        <input type="hidden" name="folder" value="<?php echo $folderid; ?>">
        <input type="submit" value="Export">
        <input type="button" value=" Cancel " onClick="self.location.href='./index.php'">
      </td>
      <td>
      </td>
    </tr>
  </table>
</form>

		</div>
	<!-- Main content ends here. -->
	</div>
<!-- Wrapper ends here. -->
</div>

<?php
	print_footer ();
	require_once (ABSOLUTE_PATH . "footer.php");
}

else{
	# these files are being included, because we do not want to include
	# header.php since there is no reason for the http header to display.
	define ("ABSOLUTE_PATH", dirname (__FILE__) . "/");
	require_once (ABSOLUTE_PATH . "lib/webstart.php");
	require_once (ABSOLUTE_PATH . "config/config.php");
	require_once (ABSOLUTE_PATH . "lib/mysql.php");
	$mysql = & new mysql;
	require_once (ABSOLUTE_PATH . "lib/auth.php");
	$auth = & new Auth;
	require_once (ABSOLUTE_PATH . "lib/lib.php");
	logged_in_only ();
	require_once (ABSOLUTE_PATH . "lib/login.php");

	$browser = set_post_browser ();
	if ($browser == "opera") {
		$filename = "opera6.adr";
	}
	else if ($browser == "IE") {
		$filename = "bookmark.htm";
	}
	else if ($browser == "netscape") {
		$filename = "bookmarks.html";
	}
	else {
		$filename = "bookmarks.html";
	}

	header("Content-Disposition: attachment; filename=$filename");
	header("Content-type: application/octet-stream");
	header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);     // HTTP/1.0
	header("Content-Type: text/html; charset=UTF-8");

	$folderid = set_get_folderid ();
	if ($browser == "netscape" || $browser == "IE") {
		echo "<!DOCTYPE NETSCAPE-Bookmark-file-1>\n";
		echo "<TITLE>Bookmarks</TITLE>\n";
		echo "<H1>Bookmarks</H1>\n";
		echo "<DL><p>\n";
		$export = & new export;
		$export->make_tree ($folderid);
		echo "</DL><p>\n";
	}
	else if ($browser == "opera") {
		echo "Opera Hotlist version 2.0\n";
		echo "Options: encoding = utf8, version=3\n\n";
		$export = & new export;
		$export->make_tree ($folderid);
	}
}

class export {
	function export () {
		global $settings, $browser;
		# collect the folder data
		require_once (ABSOLUTE_PATH . "folders.php");
		$this->tree = & new folder;
		$this->tree->folders[0] = array ('id' => 0, 'childof' => null, 'name' => $settings['root_folder_name']);

		global $username, $mysql;
		$this->browser = $browser;

		$this->counter = 0;

		# work around PHP < 5 problem
		# http://bugs.php.net/bug.php?id=25670
		if (intval(str_replace('.', '', phpversion())) < 500) {
			$this->charset = 'iso-8859-1';
		}
		else {
			$this->charset = set_post_charset ();
		}

		# collect the bookmark data
		$query = sprintf ("SELECT title, url, description, childof, id
			FROM bookmark 
			WHERE user='%s' 
			AND deleted!='1'",
			$mysql->escape ($username));
	
		if ($mysql->query ($query)) {
			while ($row = mysql_fetch_assoc ($mysql->result)) {
				if (!isset ($this->bookmarks[$row['childof']])) {
					$this->bookmarks[$row['childof']] = array ();
				}
				array_push ($this->bookmarks[$row['childof']], $row);
			}
		}
		else {
			message ($mysql->error);
		}
	}

	function make_tree ($id) {
		if (isset ($this->tree->children[$id])) {
			$this->counter++;
			foreach ($this->tree->children[$id] as $value) {
				$this->print_folder ($value);
				$this->make_tree ($value);
				$this->print_folder_close ();
			}
			$this->counter--;
		}
		$this->print_bookmarks ($id);
	}


	function print_folder ($folderid) {
		$spacer = str_repeat ("    ", $this->counter);
		$foldername = html_entity_decode ($this->tree->folders[$folderid]['name'], ENT_QUOTES, $this->charset);
		if ($this->browser == "netscape") {
			echo $spacer . "<DT><H3>" . $foldername . "</H3>\n";
			echo $spacer . "<DL><p>\n";
		}
		else if ($this->browser == "IE") {
			echo $spacer . '<DT><H3 FOLDED ADD_DATE="">' . $foldername . "</H3>\n";
			echo $spacer . "<DL><p>\n";
		}
		else if ($this->browser == "opera") {
			echo "\n#FOLDER\n";
			echo "\tNAME=" . $foldername . "\n";
		}
	}

	function print_folder_close () {
		$spacer = str_repeat ("    ", $this->counter); 
		if ($this->browser == "netscape" || $this->browser == "IE"){
			echo $spacer . "</DL><p>\n";
		}
		else if ($this->browser == "opera"){
			echo "\n-\n";
		}
	}

	function print_bookmarks ($folderid) {
		$spacer = str_repeat ("    ", $this->counter); 
		if (isset ($this->bookmarks[$folderid])) {
			foreach ($this->bookmarks[$folderid] as $value) {
				$url   = html_entity_decode ($value['url'],   ENT_QUOTES, $this->charset);
				$title = html_entity_decode ($value['title'], ENT_QUOTES, $this->charset);
				if ($value['description'] != '') {
					$description = html_entity_decode ($value['description'], ENT_QUOTES, $this->charset);
				}
				else {
					$description = '';
				}
				
				if ($this->browser == 'netscape') {
					echo $spacer . '    <DT><A HREF="' . $url . '">' . $title . "</A>\n";
					if ($description != '') {
						echo $spacer . '    <DD>' . $description . "\n";
					}
				}
				else if ($this->browser == 'IE') {
					echo $spacer . '    <DT><A HREF="' . $url . '" ADD_DATE="" LAST_VISIT="" LAST_MODIFIED="">' . $title . "</A>\n";
					# unfortunately description for bookmarks in MS Internet Explorer is not supported.
					# thats why we just ignore the output of the description here.
				}
				else if ($this->browser == 'opera') {
					echo "\n#URL\n";
					echo "\tNAME=" . $title . "\n";
					echo "\tURL=" . $url . "\n";
					if ($description != "") {
						# opera cannot handle the \r\n character, so we fix this.
						$description = str_replace ("\r\n", " ", $description);
						echo "\tDESCRIPTION=" . $description . "\n";
					}
				}
			}
		}
	}
}

?>

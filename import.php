<?php
require_once ("./header.php");
logged_in_only ();
?>

<h1 id="caption">Import Bookmarks</h1>

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

<?php

if (!isset ($_FILES['importfile']['tmp_name']) || $_FILES['importfile']['tmp_name'] == null){
	# get the browser type for default setting below if possible
	if( eregi ("opera", $_SERVER['HTTP_USER_AGENT'])){
		$default_browser = "opera";
	}
	else{
		$default_browser = "netscape";
	}
	?>
	
	<form enctype="multipart/form-data" action="<?php echo $_SERVER['SCRIPT_NAME'];?>" method="post">
	  <table border="0">
	    <tr>
	      <td>
	        from Browser:
	      </td>
	      <td>
	        <select name="browser">
	          <option value="netscape"<?php if ($default_browser=="netscape"){echo " selected";} ?>>Netscape / Mozilla / IE</option>
	          <option value="opera"<?php if ($default_browser=="opera"){echo " selected";} ?>>Opera .adr</option>
	        </select>
	      </td>
	    </tr>
	
	    <tr>
	      <td>
	        select File:
	      </td>
	      <td>
	        <input type="file" name="importfile">
	      </td>
	    </tr>

		<tr>
			<td>Character encoding:</td>
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
			<td>Make them:</td>
			<td>
				<select name="public">
				<option value="1">public</option>
				<option value="0" selected>private</option>
				</select>
			</td>
		</tr>

	    <tr>
	      <td valign="top">
	        Destination Folder:
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
	      	<p><input type="button" value=" New Folder " onClick="self.location.href='javascript:foldernew(<?php echo $folderid; ?>)'"></p>
	        <input type="hidden" name="parentfolder" value="<?php echo $folderid; ?>">
	        <input type="submit" value="Import">
	        <input type="button" value=" Cancel " onClick="self.location.href='./index.php'">
	      </td>
	      <td>
	      </td>
	    </tr>
	
	  </table>
	</form>
	
	<?php
}
else{
	if(!isset($_POST['browser']) || $_POST['browser'] == ""){
		message ("no browser selected");
	}

	$parentfolder = set_post_parentfolder ();
	$import = & new import;

	if ($_POST['browser'] == "opera") {
		$import->import_opera ();
	}
	else if ($_POST['browser'] == "netscape") {
		$import->import_netscape ();
	}
	echo "$import->count_folders folders and $import->count_bookmarks bookmarks imported.<br>\n";
	echo '<a href="./index.php">My Bookmarks</a>';
}

?>

		</div>
	<!-- Main content ends here. -->
	</div>
<!-- Wrapper ends here. -->
</div>

<?php

class import {
	function import () {
		global $username, $parentfolder, $mysql;

		# open the importfile
		$this->fp = fopen ($_FILES['importfile']['tmp_name'], "r");
		if ($this->fp == null){
			message ("Failed to open file");
		}

		$this->charset = set_post_charset ();
		$this->public = set_post_bool_var ("public", false);

		$this->count_folders = 0;
		$this->count_bookmarks = 0;

		$this->username = $username;
		$this->parent_folder = $parentfolder;
		$this->current_folder = $this->parent_folder;
		
		$this->folder_depth = array ();

		$this->mysql = $mysql;

	}

	function import_opera () {
		while (!feof ($this->fp)) {
			$line = trim (fgets ($this->fp, 4096));

			# a folder has been found
			if ($line == "#FOLDER") {
				$item = "Folder";
			}
			# a bookmark has been found
			else if ($line == "#URL") {
				$item = "Bookmark";
			}
			# if a line starts with NAME= ...
			else if (substr ($line, 0, strlen("NAME=")) == "NAME=") {
				$line = substr ($line, strlen ("NAME="));
				# ... depending on the value of "$item" we assign the name to
				# either folder or bookmark.
				if ($item == "Folder") {
					$this->name_folder = input_validation ($line, $this->charset);
				}
				else if ($item == "Bookmark") {
					$this->name_bookmark = input_validation ($line, $this->charset);
				}
			}
			# only bookmarks can have a description or/and an url.
			else if (substr ($line, 0, strlen ("DESCRIPTION=")) == "DESCRIPTION=") {
				$this->description = substr (input_validation ($line, $this->charset), strlen ("DESCRIPTION="));
			}
			else if (substr ($line, 0, strlen ("URL=")) == "URL="){
				$this->url = substr (input_validation ($line, $this->charset), strlen ("URL="));
			}
			# process the corresponding item, if there is an empty line found
			else if ($line == "") {
				if (isset ($item) && $item == "Folder") {
					$this->folder_new ();
					unset ($item);
				}
				else if (isset ($item) && $item == "Bookmark") {
					$this->bookmark_new ();
					unset ($item);
				}
			}
			# this indicates, that the folder is being closed
			else if ($line == "-") {
				$this->folder_close ();
			}
		}
	}

	function import_netscape () {
		while (!feof ($this->fp)){
			$line = trim (fgets ($this->fp));
			# netscape seems to store html encoded values
			$line = html_entity_decode ($line, ENT_QUOTES, $this->charset);

			# a folder has been found
			if (ereg ("<DT><H3", $line)) {
				$this->name_folder = input_validation (ereg_replace ("^( *<DT><[^>]*>)([^<]*)(.*)", "\\2", $line), $this->charset);
				$this->folder_new ();
			}
			# a bookmark has been found
			else if (ereg("<DT><A", $line)){
				$this->name_bookmark = input_validation (ereg_replace ("^( *<DT><[^>]*>)([^<]*)(.*)", "\\2", $line), $this->charset);
				$this->url = input_validation (ereg_replace ("([^H]*HREF=\")([^\"]*)(\".*)", "\\2", $line), $this->charset);
				$this->bookmark_new ();
				$insert_id = mysql_insert_id ();
			}
			# this is a description. it is only being saved
			# if a bookmark has been saved previously
			else if (ereg("<DD>*", $line)) {
				if (isset ($insert_id)) {
					$this->description = input_validation (ereg_replace ("^( *<DD>)(.*)", "\\2", $line), $this->charset);
					$query = sprintf ("UPDATE bookmark SET description='%s' WHERE id='%d' and user='%s'",
						$this->mysql->escape ($this->description),
						$this->mysql->escape ($insert_id),
						$this->mysql->escape ($this->username));

					@$this->mysql->query ($query);
					unset ($this->description);
					unset ($insert_id);
				}
			}
			# this indicates, that the folder is being closed
			else if ($line == "</DL><p>") {
				$this->folder_close ();
			}
		}
	}

	function folder_new () {
		if (!isset ($this->name_folder)) {
			$this->name_folder == "";
		}
		$query = sprintf ("INSERT INTO folder (childof, name, user, public) values ('%d', '%s', '%s', '%d')",
			$this->mysql->escape ($this->current_folder),
			$this->mysql->escape ($this->name_folder),
			$this->mysql->escape ($this->username),
			$this->mysql->escape ($this->public));

		if ($this->mysql->query ($query)) {
			$this->current_folder = mysql_insert_id ();
			array_push ($this->folder_depth, $this->current_folder);
			unset ($this->name_folder);
			$this->count_folders++;
		}
		else {
			message ($this->mysql->error);
		}
	}

	function bookmark_new () {
		if (!isset ($this->name_bookmark)) {
			$this->name_bookmark = "";
		}
		if (!isset ($this->url)) {
			$this->url = "";
		}
		if (!isset ($this->description)) {
			$this->description = "";
		}
		$query = sprintf ("INSERT INTO bookmark (user, title, url, description, childof, public)
				 	values ('%s', '%s', '%s', '%s', '%d', '%d')",
					$this->mysql->escape ($this->username),
					$this->mysql->escape ($this->name_bookmark),
					$this->mysql->escape ($this->url),
					$this->mysql->escape ($this->description),
					$this->mysql->escape ($this->current_folder),
					$this->mysql->escape ($this->public));

		if ($this->mysql->query ($query)) {
			unset ($this->name_bookmark, $this->url, $this->description);
			$this->count_bookmarks++;
		}
		else {
			message ($this->mysql->error);
		}
	}

	function folder_close () {
		if (count ($this->folder_depth) <= 1) {
			$this->folder_depth = array ();
			$this->current_folder = $this->parent_folder;
		}
		else{
			# remove the last folder from the folder history
			unset ($this->folder_depth[count ($this->folder_depth) - 1]);
			$this->folder_depth = array_values ($this->folder_depth);
			# set the last folder to the current folder
			$this->current_folder = $this->folder_depth[count ($this->folder_depth) - 1];
		}
	}
}

print_footer ();
require_once (ABSOLUTE_PATH . "footer.php");
?>

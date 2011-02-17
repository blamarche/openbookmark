<?php
require_once ("./header.php");
logged_in_only ();

$noconfirm = set_get_noconfirm ();

# the root folder cannot be deleted
if ($folderid == "" || $folderid == 0){
	message ("No Folder selected");	
}
else if (!$settings['confirm_delete'] || $noconfirm) {
	# lets do the deletion if the confirm variable is set to FALSE or after confirmation
	require_once (ABSOLUTE_PATH . "folders.php");
	$tree = & new folder;
	$tree->get_children ($folderid);

	# we need $parent_folders for javascript code below.
	$parent_folders = $tree->get_path_to_root ($folderid);
	if (count ($parent_folders) > 1) {
		$parent_folder = $parent_folders[1];
	}
	else {
		$parent_folder = 0;
	}

	array_push ($tree->get_children, $folderid);
	$folders = implode (",", $tree->get_children);
	# first delete all subfolders
	$query = sprintf ("DELETE FROM folder WHERE childof IN (%s) AND user='%s'", 
		$mysql->escape ($folders),
		$mysql->escape ($username));
	if (!$mysql->query ($query)) {
		message ($mysql->error);
	}

	# of course, we want to delete all bookmarks as well
	$query = sprintf ("DELETE FROM bookmark WHERE childof IN (%s) AND user='%s'", 
		$mysql->escape ($folders),
		$mysql->escape ($username));
	if (!$mysql->query ($query)) {
		message ($mysql->error);
	}

	# now delete the folder itself
	$query = sprintf ("DELETE FROM folder WHERE id=%d AND user='%s'", 
		$mysql->escape ($folderid),
		$mysql->escape ($username));
	if (!$mysql->query ($query)) {
		message ($mysql->error);
	}

	?>

<script language="JavaScript">
<!--
function reloadparentwindow() {
  var path = window.opener.document.URL;
  searchstring = /(folderid=[0-9]*)/gi;
  result = searchstring.test(path);

  if(result == false) {
    urlparams = window.opener.location.search;
    if (urlparams == "") {
      result = path + "?folderid=<?php echo $parent_folder; ?>";
    }
    else {
      result = path + "&folderid=<?php echo $parent_folder; ?>";
    }
  }
  else {
    result = path.replace (searchstring,"folderid=<?php echo $parent_folder; ?>");
  }
  window.opener.location = result;
  window.close();
}
reloadparentwindow();
//-->
</script>

	<?php
}
else {
	# if there was no confirmation, as to _really_ delete the whole stuff
	# print the verification form
	$query = sprintf ("SELECT name, public FROM folder WHERE id=%d AND user='%s' AND deleted!='1'", 
		$mysql->escape ($folderid),
		$mysql->escape ($username));

	if ($mysql->query ($query)) {
		if (mysql_num_rows ($mysql->result) == 0){
			message ("Folder does not exist");
		}
		$row = mysql_fetch_object ($mysql->result);
		?>

		<h2 class="title">Delete this Folder?</h2>
		<p><?php echo $row->public ? $folder_opened_public : $folder_opened; echo " " . $row->name; ?></p>

		<form action="<?php echo $_SERVER['SCRIPT_NAME'] . "?folderid=" . $folderid . "&amp;noconfirm=1";?>" method="POST" name="fdelete">
		<input type="submit" value=" OK ">
		<input type="button" value=" Cancel " onClick="self.close()">
		</form>

		<?php
	}
	else {
		message ($mysql->error);
	}
}

require_once (ABSOLUTE_PATH . "footer.php");
?>
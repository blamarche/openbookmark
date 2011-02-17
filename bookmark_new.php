<?php
require_once ('./header.php');

$get_title			= set_title ();
$get_url			= set_url ();

logged_in_only ();

$post_title			= set_post_title ($persistent = true);
$post_url			= set_post_url ();
$post_description	= set_post_description ();
$post_childof		= set_post_childof ();
$post_public		= set_post_bool_var ("public", false);

require_once (ABSOLUTE_PATH . "folders.php");
$tree = & new folder;
$query_string = "?expand=" . implode(",", $tree->get_path_to_root ($post_childof)) . "&amp;folderid=" . $post_childof;

if ($post_title == '' || $post_url == '') {
	$path = $tree->print_path ($folderid);
	if ($post_title != '') {
		$title = $post_title;
	}
	else {
		$title = $get_title;
	}
	if ($post_url != '') {
		$url = $post_url;
	}
	else if ($get_url != '') {
		$url = $get_url;
	}
	else {
		$url = 'http://';
	}
	if (strtolower (basename ($_SERVER['SCRIPT_NAME'])) == 'bookmark_add.php') {
		$js_onclick = 'history.back()';
	}
	else {
		$js_onclick = 'self.close()';
	}

?>

	<h2 class="title">New Bookmark</h2>
	<form action="<?php echo $_SERVER['SCRIPT_NAME'] . "?folderid=" . $folderid; ?>" id="bmnew" method="POST">
	<p>Title<br>
	<input type=text name="title" size="50" value="<?php echo $title; ?>"></p>
	<p>URL<br>
	<input type=text name="url" size="50" value="<?php echo $url; ?>"></p>
	<p>Description<br>
	<textarea name="description" cols="50" rows="8"><?php echo $post_description; ?></textarea></p>
	<p><input type="button" value="Select folder" onClick="window.childof=document.forms['bmnew'].childof; window.path=document.forms['bmnew'].path; selectfolder('<?php echo $query_string; ?>')"><br>
	<input type="text" name="path" value="<?php echo $path; ?>" size="50" readonly>
	<input type="text" name="childof" value="<?php echo $folderid; ?>" size="1" class="invisible" readonly></p>
	<p>Tags<br>
	<input type=text name="tags" size="50" value="Not yet working"></p>
	<input type="submit" value=" OK ">
	<input type="button" value=" Cancel " onClick="<?php echo $js_onclick; ?>">
	Public <input type="checkbox" name="public" <?php echo $post_public ? "checked" : "";?>>
	</form>
	<script>
	this.focus();
	document.getElementById('bmnew').title.focus();
	</script>

<?php
}
else {
	$query = sprintf ("INSERT INTO bookmark
		(user, title, url, description, childof, public)
		VALUES ('%s', '%s', '%s', '%s', '%d', '%d')", 
		$mysql->escape ($username),
		$mysql->escape ($post_title),
		$mysql->escape ($post_url),
		$mysql->escape ($post_description),
		$mysql->escape ($post_childof),
		$mysql->escape ($post_public));

	if ($mysql->query ($query)) {
		echo "Bookmark successfully created<br>\n";
		$bm_id = mysql_insert_id ();
	}
	else {
		message ($mysql->error);
	}
	unset ($_SESSION['title'], $_SESSION['url']);

	# safing the favicon in a separate second step is done because
	# we want to make sure the bookmark is safed in any case. the
	# favicon is not that important.
	if ($settings['show_bookmark_icon']) {
		require_once (ABSOLUTE_PATH . "favicon.php");
		$favicon = & new favicon ($post_url);
		if (isset ($favicon->favicon)) {
			$query = sprintf ("UPDATE bookmark set favicon='%s' WHERE user='%s' AND id='%d'", 
				$mysql->escape ($favicon->favicon),
				$mysql->escape ($username),
				$mysql->escape ($bm_id));
			$mysql->query ($query);
			$icon = '<img src="'.$favicon->favicon.'">';
		}
		else {
			$icon = $bookmark_image;
		}
	}

	if (strtolower (basename ($_SERVER['SCRIPT_NAME'])) == "bookmark_add.php") {
		echo 'Back to '.$icon.' <a href="'.$post_url.'">'.$post_title.'</a><br>' . "\n";
		echo 'Open '.$folder_opened.' <a href="./index.php'.$query_string.'">folder</a> containing new Bookmark<br>' . "\n";
	}
	else {
		echo '<script language="JavaScript">reloadclose();</script>';
		# I know, the following is ugly, but I found no other way to do.
		# When creating a bookmark out of the personal toolbar, there is no
		# window.opener that can be closed. Thus javascript exits with an error
		# without finishing itself (self.close()).
		echo '<script language="JavaScript">self.close();</script>';
	}
}
require_once (ABSOLUTE_PATH . "footer.php");
?>

<?php
require_once ("./header.php");
logged_in_only ();

$bmlist				= set_get_num_list ('bmlist');

$post_title			= set_post_title ();
$post_url			= set_post_url ();
$post_description	= set_post_description ();
$post_icon			= set_post_bool_var ('favicon', false);
$post_childof		= set_post_childof ();
$post_public		= set_post_bool_var ("public", false);

if (count ($bmlist) > 1) {
	# if there is more than one bookmark to edit, we just care about the
	# public/private field.
	if ( ! isset ($_POST['public'])) {
		$qbmlist = implode (",", $bmlist);
		$query = sprintf ("SELECT title, id, public, favicon FROM bookmark WHERE id IN (%s) AND user='%s' ORDER BY title",
			$mysql->escape ($qbmlist),
			$mysql->escape ($username));
		if ($mysql->query ($query)) {
			require_once (ABSOLUTE_PATH . "bookmarks.php");
			$query_string = "?bmlist=" . implode ("_", $bmlist);
			?>

			<h2 class="title">Change public state:</h2>
			<div style="width:100%; height:330px; overflow:auto;">

			<?php
			$bookmarks = array ();
			while ($row = mysql_fetch_assoc ($mysql->result)) {
				array_push ($bookmarks, $row);
			}
			list_bookmarks ($bookmarks,
				false,
				false,
				$settings['show_bookmark_icon'],
				false,
				false,
				false,
				false,
				false,
				false,
				true,
				false);
			?>

			</div>

			<br>
			<form action="<?php echo $_SERVER['SCRIPT_NAME'] . $query_string; ?>" method="POST" name="bmedit">
			<p>
				<select name="public">
				<option value="1">public</option>
				<option value="0">private</option>
				</select>
			</p>
			<input type="submit" value=" OK ">
			<input type="button" value=" Cancel " onClick="self.close()">
			</form>

			<?php
		}
		else {
			message ($mysql->error);
		}
	}
	else {
		$bmlist = implode (",", $bmlist);
		$query = sprintf ("UPDATE bookmark SET public='%d'
					WHERE id IN (%s)
					AND user='%s'",
					$mysql->escape ($post_public),
					$mysql->escape ($bmlist),
					$mysql->escape ($username));
		if ($mysql->query ($query)) {
			echo "Bookmark successfully updated<br>\n";
			echo '<script language="JavaScript">reloadclose();</script>';
		}
		else {
			message ($mysql->error);
		}
	}

}
else if (count ($bmlist) < 1) {
	message ("No Bookmark to edit.");
}
else if ($post_title == "" || $post_url == "" || $post_icon) {
	$query = sprintf ("SELECT title, url, description, childof, id, favicon, public
				FROM bookmark
				WHERE id='%d'
				AND user='%s'
				AND deleted != '1'",
				$mysql->escape ($bmlist[0]),
				$mysql->escape ($username));
	if ($mysql->query ($query)) {
		if (mysql_num_rows ($mysql->result) != 1) {
			message ("No Bookmark to edit");
		}
		else {
			$row = mysql_fetch_object ($mysql->result);
			require_once (ABSOLUTE_PATH . "folders.php");
			$tree = & new folder;
			$query_string = "?expand=" . implode(",", $tree->get_path_to_root ($row->childof)) . "&amp;folderid=" . $row->childof;
			$path = $tree->print_path ($row->childof);
			if ($post_icon && $settings['show_bookmark_icon']) {
				if (isset ($row->favicon)) {
					@unlink ($row->favicon);
				}
				require_once (ABSOLUTE_PATH . "favicon.php");
				$favicon = & new favicon ($post_url);
				if (isset ($favicon->favicon)) {
					$icon = '<img src="' . $favicon->favicon . '" width="16" height="16" alt="">';
					$query = sprintf ("UPDATE bookmark SET favicon='%s' WHERE user='%s' AND id='%d'",
							$mysql->escape ($favicon->favicon),
							$mysql->escape ($username),
							$mysql->escape ($bmlist[0]));
					if (!$mysql->query ($query)) {
						message ($mysql->error);
					}
				}
				else {
					$icon = $bookmark_image;
				}
			}
			else if ($row->favicon && is_file ($row->favicon)) {
				$icon = '<img src="' . $row->favicon . '" width="16" height="16" alt="">';
			}
			else {
				$icon = $bookmark_image;
			}
		}
	}
	else {
		message ($mysql->error);
	}

?>

	<h2 class="title">Edit Bookmark</h2>
	<form action="<?php echo $_SERVER['SCRIPT_NAME'] . "?bmlist=" . $row->id; ?>" id="bmedit" method="POST">
	<p>Title<br>
	<input type=text name="title" size="50" value="<?php echo $row->title; ?>"> <?php echo $settings['show_bookmark_icon'] ? $icon : ""; ?></p>
	<p>URL<br>
	<input type=text name="url" size="50" value="<?php echo $row->url; ?>">
	<p>Description<br>
	<textarea name="description" cols="50" rows="8"><?php echo $row->description; ?></textarea></p>
	<p><input type="button" value="Select folder" onClick="window.childof=document.forms['bmedit'].childof; window.path=document.forms['bmedit'].path; selectfolder('<?php echo $query_string; ?>')"><br>
	<input type="text" name="path" value="<?php echo $path; ?>" size="50" readonly>
	<input type="text" name="childof" value="<?php echo $row->childof; ?>" size="1" class="invisible" readonly></p>
	<p>Tags<br>
	<input type=text name="tags" size="50" value="Not yet working"></p>
	<input type="submit" value=" OK ">
	<input type="button" value=" Cancel " onClick="self.close()">
	<?php if ($settings['show_bookmark_icon']) : ?><input type="submit" value="Refresh Icon" name="favicon"><?php endif; ?>
	Public <input type="checkbox" name="public" <?php echo $row->public ? "checked" : "";?>>
	</form>
	<script>
	this.focus();
	document.getElementById('bmedit').title.focus();
	</script>

<?php

}
else {
	$query = sprintf ("UPDATE bookmark SET title='%s', url='%s', description='%s', childof='%d', public='%d'
				WHERE id='%d'
				AND user='%s'",
				$mysql->escape ($post_title),
				$mysql->escape ($post_url),
				$mysql->escape ($post_description),
				$mysql->escape ($post_childof),
				$mysql->escape ($post_public),
				$mysql->escape ($bmlist[0]),
				$mysql->escape ($username));
	if ($mysql->query ($query)) {
		echo "Bookmark successfully updated<br>\n";
		echo '<script language="JavaScript">reloadclose();</script>';
	}
	else {
		message ($mysql->error);
	}
}

require_once (ABSOLUTE_PATH . "footer.php");
?>

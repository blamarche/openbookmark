<?php
require_once ("./header.php");
logged_in_only ();

$bmlist = set_get_num_list ('bmlist');

if (count ($bmlist) == 0){
	echo "No Bookmarks selected";	
}
else if (!$settings['confirm_delete'] || set_get_noconfirm ()){
	$bmlist = implode (",", $bmlist);
	$query = sprintf ("DELETE FROM bookmark WHERE id IN (%s) AND user='%s'",
		$mysql->escape ($bmlist),
		$mysql->escape ($username));
	if ($mysql->query ($query)) {
		echo "Bookmarks successfully deleted<br>\n";
		echo '<script language="JavaScript">reloadclose();</script>';
	}
	else {
		message ($mysql->error);
	}
}
else {
	$bmlistq = implode (",", $bmlist);
	$query = sprintf ("SELECT title, id, favicon FROM bookmark WHERE id IN (%s) AND user='%s' ORDER BY title",
		$mysql->escape ($bmlistq),
		$mysql->escape ($username));
	if ($mysql->query ($query)) {
		require_once (ABSOLUTE_PATH . "bookmarks.php");
		$query_string = "?bmlist=" . implode ("_", $bmlist) . "&noconfirm=1";
		?>
	
		<h2 class="title">Delete these Bookmarks?</h2>
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
			false,
			false);
		?>
	
		</div>
	
		<br>
		<form action="<?php echo $_SERVER['SCRIPT_NAME'] . $query_string; ?>" method="POST" name="bmdelete">
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
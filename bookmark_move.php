<?php
require_once ("./header.php");
logged_in_only ();

$bmlist = set_post_num_list ('bmlist');

if (count ($bmlist) == 0) {
	?>

	<h2 class="title">Move bookmarks to:</h2>
	<form action="<?php echo $_SERVER['SCRIPT_NAME'] . "?folderid=" . $folderid; ?>" method="POST" name="bookmarksmove">

				<div style="width:100%; height:330px; overflow:auto;">

					<?php
					require_once (ABSOLUTE_PATH . "folders.php");
					$tree = & new folder;
					$tree->make_tree (0);
					$tree->print_tree ();
					?>

				</div>
				<br>
				<input type="hidden" name="bmlist">
				<input type="submit" value=" OK ">
				<input type="button" value=" Cancel " onClick="self.close()">
				<input type="button" value=" New Folder " onClick="self.location.href='javascript:foldernew(<?php echo $folderid; ?>)'">

	</form>

	<script type="text/javascript">
	document.bookmarksmove.bmlist.value = self.name;
	</script>

	<?php
}
else if ($folderid == '') {
	message ('No destination Folder selected.');
}
else {
	$query = sprintf ("UPDATE bookmark SET childof='%d' WHERE id IN (%s) AND user='%s'",
		$mysql->escape ($folderid),
		$mysql->escape (implode (",", $bmlist)),
		$mysql->escape ($username));

	if ($mysql->query ($query)) {
		echo "Bookmarks moved<br>\n";
		echo '<script language="JavaScript">reloadclose();</script>';
	}
	else {
		message ($mysql->error);
	}
}

require_once (ABSOLUTE_PATH . "footer.php");
?>
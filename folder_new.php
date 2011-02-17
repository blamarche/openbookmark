<?php
require_once ("./header.php");
logged_in_only ();

$foldername = set_post_foldername ();
$public = set_post_bool_var ("public", false);

if ($foldername == "") {
	?>
	
	<h2 class="title">New Folder</h2>
	<form action="<?php echo $_SERVER['SCRIPT_NAME'] . "?folderid=" . $folderid; ?>" id="fnew" method="POST">
	<p><input type=text name="foldername" size="50" value="<?php echo $foldername; ?>"></p>
	<p><input type="checkbox" name="public"> Public</p>
	<input type="submit" value=" OK ">
	<input type="button" value=" Cancel " onClick="self.close()">
	</form>
	<script>
	this.focus();
	document.getElementById('fnew').foldername.focus();
	</script>
	
	<?php
}
else {
	$query = sprintf ("INSERT INTO folder (childof, name, public, user) values ('%d', '%s', '%d', '%s')",
		$mysql->escape ($folderid),
		$mysql->escape ($foldername),
		$mysql->escape ($public),
		$mysql->escape ($username));
	if ($mysql->query ($query)) {
		echo "Folder successfully created<br>\n";
		echo '<script language="JavaScript">reloadclose();</script>';
	}
	else {
		message ($mysql->error);
	}
}

require_once (ABSOLUTE_PATH . "footer.php");
?>
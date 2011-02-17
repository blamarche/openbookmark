<?php
require_once ("./header.php");
logged_in_only ();

$pw_message = null;

if (isset ($_POST['settings_password']) && $_POST['settings_password'] == 1) {
	if (isset ($_POST['set_password1']) && $_POST['set_password1'] != "" &&
		isset ($_POST['set_password2']) && $_POST['set_password2'] != "") {
		if ($_POST['set_password1'] != $_POST['set_password2']) {
			$pw_message = 'Passwords do not match.'."\n";
			$password = false;
		}
		else {
			$password = trim ($_POST['set_password1']);
		}
	}
	else {
		$pw_message = 'Please fill out both password fields.'."\n";
		$password = false;
	}

	if ($password) {
		$query = sprintf ("UPDATE user SET password=md5('%s') WHERE username='%s'",
			$mysql->escape ($password),
			$mysql->escape ($username));

		if ($mysql->query ($query)) {
			$pw_message = "Password changed.<br>\n";
		}
		else {
			message ($mysql->error);
		}
	}
	unset ($_POST['set_password1'], $_POST['set_password2'], $password);
}

?>

<h2 class="title">Change Password</h2>

<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
<table>
	<tr>
		<td>New Password</td>
		<td><input type="password" name="set_password1"></td>
	</tr>

	<tr>
		<td>Verify new Password</td>
		<td><input type="password" name="set_password2"></td>
	</tr>

	<tr>
		<td>
		<input type="submit" value=" Save ">
		<input type="button" value=" Cancel " onClick="self.close()">
		<input type="hidden" name="settings_password" value="1">
		</td>
		<td>
		<?php echo $pw_message; ?>
		</td>
	</tr>
</table>
</form>

<?php
require_once (ABSOLUTE_PATH . "footer.php");
?>

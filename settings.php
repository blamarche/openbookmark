<?php
require_once ("./header.php");
logged_in_only ();

$message = '';

if (isset ($_POST['settings_apply'])) {
	$settings = array (
		'root_folder_name'			=> set_post_foldername ("settings_root_folder_name"),
		'column_width_folder'		=> check_num_var ("settings_column_width_folder"),
		'column_width_bookmark'		=> check_num_var ("settings_column_width_bookmark"),
		'table_height'				=> check_num_var ("settings_table_height"),
		'confirm_delete'			=> set_post_bool_var ("settings_confirm_delete", false),
		'open_new_window'			=> set_post_bool_var ("settings_open_new_window", false),
		'show_bookmark_description'	=> set_post_bool_var ("settings_show_bookmark_description", false),
		'show_bookmark_icon'		=> set_post_bool_var ("settings_show_bookmark_icon", false),
		'show_column_date'			=> set_post_bool_var ("settings_show_column_date", false),
		'date_format'				=> check_date_format (),
		'show_column_edit'			=> set_post_bool_var ("settings_show_column_edit", false),
		'show_column_move'			=> set_post_bool_var ("settings_show_column_move", false),
		'show_column_delete'		=> set_post_bool_var ("settings_show_column_delete", false),
		'fast_folder_minus'			=> set_post_bool_var ("settings_fast_folder_minus", false),
		'fast_folder_plus'			=> set_post_bool_var ("settings_fast_folder_plus", false),
		'fast_symbol'				=> set_post_bool_var ("settings_fast_symbol", false),
		'simple_tree_mode'			=> set_post_bool_var ("settings_simple_tree_mode", false),
		'show_public'				=> set_post_bool_var ("settings_show_public", false),
	);

	$query = sprintf ("UPDATE user SET
		root_folder_name			='%s',
		column_width_folder			='%d',
		column_width_bookmark		='%d',
		table_height				='%d',
		confirm_delete				='%d',
		open_new_window				='%d',
		show_bookmark_description	='%d',
		show_bookmark_icon			='%d',
		show_column_date			='%d',
		date_format					='%s',
		show_column_edit			='%d',
		show_column_move			='%d',
		show_column_delete			='%d',
		fast_folder_minus			='%d',
		fast_folder_plus			='%d',
		fast_symbol					='%d',
		simple_tree_mode			='%d',
		show_public					='%d'
		WHERE username='%s'",

		$mysql->escape ($settings['root_folder_name']),
		$settings['column_width_folder'],
		$settings['column_width_bookmark'],
		$settings['table_height'],
		$settings['confirm_delete'],
		$settings['open_new_window'],
		$settings['show_bookmark_description'],
		$settings['show_bookmark_icon'],
		$settings['show_column_date'],
		$mysql->escape ($settings['date_format']),
		$settings['show_column_edit'],
		$settings['show_column_move'],
		$settings['show_column_delete'],
		$settings['fast_folder_minus'],
		$settings['fast_folder_plus'],
		$settings['fast_symbol'],
		$settings['simple_tree_mode'],
		$settings['show_public'],
		$mysql->escape ($username));

	if ($mysql->query ($query)) {
		$message = "Settings applied.";
	}
	else {
		message ($mysql->error);
	}
}

# I really don't feel like putting these very specific function into lib.php...
function check_num_var ($varname) {
	if (!is_numeric ($_POST[$varname])) {
		return 280;
	}
	else if ($_POST[$varname] == 0 && $varname == "settings_column_width_bookmark") {
		return 0;
	}
	else if ($_POST[$varname] < 100) {
		return 100;
	}
	else if ($_POST[$varname] > 800) {
		return 800;
	}
	else {
		return $_POST[$varname];
	}
}

function check_date_format () {
	global $date_formats;
	$date_format = set_post_num_var ('settings_date_format');

	if ($date_format < 0 || $date_format > count ($date_formats)) {
		return 0;
	}
	else {
		return $date_format;
	}
}

?>

<h1 id="caption">My Settings</h1>

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

<table>
	<tr>
		<td valign="top">
			<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
			<table>
				<tr>
					<td>Name of the root folder</td>
					<td>
						<input type="text" name="foldername" value="<?php echo $settings['root_folder_name']; ?>">
					</td>
				</tr>
			
				<tr>
					<td>The width in pixels of the folder column<br>100 - 800 pixel</td>
					<td>
						<input type="text" name="settings_column_width_folder" value="<?php echo $settings['column_width_folder']; ?>" size="5">
					</td>
				</tr>
			
				<tr>
					<td>The width in pixels of the bookmark column<br>100 - 800 pixel or 0 for 100%</td>
					<td>
						<input type="text" name="settings_column_width_bookmark" value="<?php echo $settings['column_width_bookmark']; ?>" size="5">
					</td>
				</tr>
			
				<tr>
					<td>The height in pixels of the main table<br>100 - 800 pixel</td>
					<td>
						<input type="text" name="settings_table_height" value="<?php echo $settings['table_height']; ?>" size="5">
					</td>
				</tr>
			
				<tr>
					<td>Confirm deletions of bookmarks an folders</td>
					<td>
						<input type="checkbox" name="settings_confirm_delete" <?php if ($settings['confirm_delete'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Open a new window when clicking a bookmark</td>
					<td>
						<input type="checkbox" name="settings_open_new_window" <?php if ($settings['open_new_window'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Show the bookmarks description in the overview</td>
					<td>
						<input type="checkbox" name="settings_show_bookmark_description" <?php if ($settings['show_bookmark_description'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Enable favicon support: <?php echo $bookmark_image; ?></td>
					<td>
						<input type="checkbox" name="settings_show_bookmark_icon" <?php if ($settings['show_bookmark_icon'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Show the column with the change date: <?php echo date ($date_formats[$settings['date_format']]); ?></td>
					<td>
						<input type="checkbox" name="settings_show_column_date" <?php if ($settings['show_column_date'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Date format:</td>
					<td>
						<select name="settings_date_format">
							<?php
								foreach ($date_formats as $format_key => $format) {
									echo '<option value="'.$format_key.'"';
									if ($settings['date_format'] == $format_key) {echo ' selected';}
									echo '>'.date ($format)."</option>\n";
								}
							?>
						</select>
					</td>
				</tr>

				<tr>
					<td>Show the private/public column: <span class="private">private</span></td>
					<td>
						<input type="checkbox" name="settings_show_public" <?php if ($settings['show_public'] == 1) {echo "checked";}?>>
					</td>
				</tr>

				<tr>
					<td>Show the column to edit a bookmark: <?php echo $edit_image; ?></td>
					<td>
						<input type="checkbox" name="settings_show_column_edit" <?php if ($settings['show_column_edit'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Show the column to move a bookmark: <?php echo $move_image; ?></td>
					<td>
						<input type="checkbox" name="settings_show_column_move" <?php if ($settings['show_column_move'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Show the column to delete a bookmark: <?php echo $delete_image; ?></td>
					<td>
						<input type="checkbox" name="settings_show_column_delete" <?php if ($settings['show_column_delete'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Collapse tree when clicking on folder icon: <?php echo $minus . $folder_opened; ?></td>
					<td>
						<input type="checkbox" name="settings_fast_folder_minus" <?php if ($settings['fast_folder_minus'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Expand tree when clicking on folder icon: <?php echo $plus . $folder_opened; ?></td>
					<td>
						<input type="checkbox" name="settings_fast_folder_plus" <?php if ($settings['fast_folder_plus'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Select folder when clicking on plus/minus symbol</td>
					<td>
						<input type="checkbox" name="settings_fast_symbol" <?php if ($settings['fast_symbol'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td>Allways open just one tree</td>
					<td>
						<input type="checkbox" name="settings_simple_tree_mode" <?php if ($settings['simple_tree_mode'] == 1) {echo "checked";}?>>
					</td>
				</tr>
			
				<tr>
					<td></td>
					<td>
						<input type="submit" value="Apply" name="settings_apply"> <?php echo $message; ?>
					</td>
				</tr>
			</table>
			</form>
		</td>

		<td valign="top" style="width: 40%;">
			<p>
				<b><a href="javascript:chpw()">Change Password</a></b>
			</p>

			<hr>

			<?php
				if (isset($_SERVER['HTTPS'])) {
					$scheme = 'https://';
				}
				else {
					$scheme = 'http://';
				}
				if (dirname ($_SERVER['SCRIPT_NAME']) == '/') {
					$path = '';
				}
				else {
					$path = dirname ($_SERVER['SCRIPT_NAME']);
				}

				$js_url = $scheme . $_SERVER['SERVER_NAME'] . $path;

			?>
			<p>
				You can add a button to your browsers "Link Bar" or "Hotlist" so that any homepage
				can be bookmarked with one click. Title and URL of the current homepage are being preset.
				Basically you can make Online-Bookmarks behave in two different ways showing its 
				dialog. Either a new window pops up or it shows it in the same window.
			</p>
			<p>
				To show the Online-Bookmarks dialog in a new window, drag this link to the
				Link Bar.<br>

				<a href="javascript:(function(){bmadd=window.open('<?php echo $js_url; ?>/bookmark_new.php?title='+encodeURIComponent(document.title)+'&url='+encodeURIComponent(location.href),'bmadd','toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=500,left=50,top=50');setTimeout(function(){bmadd.focus();});})();" title="bookmark">
				<img src="./images/bookmark.gif" alt="bookmark" title="bookmark">
				</a><br>
			</p>
			<p>
				To open the Online-Bookmarks dialog in the same window, drag this link to the
				Link Bar.<br>
				<a href="javascript:location.href='<?php echo $js_url; ?>/bookmark_add.php?title='+encodeURIComponent(document.title)+'&url='+encodeURIComponent(location.href)" title="bookmark">
				<img src="./images/bookmark.gif" alt="bookmark" title="bookmark">
				</a><br>
			</p>
			<p>
				Note that if your browser has a Popup Blocker enabled you might experience difficulties using 
				the upper link.
			</p>

			<hr>

			<p>
				<script type="text/javascript">
				<!--
				function addSidebar() {
				  if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")){
				    var sidebarname=window.location.host;
				    if (!/bug/i.test(sidebarname))
				      sidebarname="Online Bookmarks "+sidebarname;
				    window.sidebar.addPanel (sidebarname, "<?php echo $js_url; ?>/sidebar.php", "");
				  }
				  else{
				    var rv = window.confirm ("Your browser does not support the sidebar extension.  " + "Would you like to upgrade now?");
				    if (rv)
				      document.location.href = "http://www.mozilla.org/";
				  }
				}
				//-->
				</script>
				If you are using <a href="http://www.mozilla.com/firefox/">Firefox</a> as a webbrowser, you can
				use the link below to add a bookmark which opens Online-Bookmarks as a sidebar.<br>
				<b><a href="javascript:addSidebar()">Add to Sidebar</a></b>
			</p>

			<hr>
		</td>
	</tr>
</table>

	<!-- Main content ends here. -->
	</div>
<!-- Wrapper ends here. -->
</div>

<?php
print_footer ();
require_once (ABSOLUTE_PATH . "footer.php");
?>

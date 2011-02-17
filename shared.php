<?php
require_once ('./header.php');

if ($_SESSION['logged_in']) {
        $user = set_get_string_var ('user', $username);
}
else {
        $user = set_get_string_var ('user');
}
$display_shared = false;

if (isset ($_GET['user']) && check_username ($user)) {
	$title = $user . "&#039;s Online Bookmarks";
}
else {
	$title = "Shared Online-Bookmarks";
}

$order = set_get_order ();

?>

<h1 id="caption"><?php echo $title; ?></h1>

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
			<?php if (isset ($_SESSION['logged_in']) && $_SESSION['logged_in']) { ?>
			<?php if (admin_only ()) { ?>
			<li><a href="./admin.php">Admin</a></li>
			<?php } ?>
			<li><a href="./import.php">Import</a></li>
			<li><a href="./export.php">Export</a></li>
			<li><a href="./sidebar.php">View as Sidebar</a></li>
			<li><a href="./settings.php">Settings</a></li>
			<li><a href="./index.php?logout=1">Logout</a></li>
			<?php } else { ?>
			<li><a href="./index.php">Login</a></li>
			<?php } ?>
		</ul>
	<!-- Menu ends here. -->
	</div>

	<!-- Main content starts here. -->
	<div id="main">


<?php
if (isset ($_GET['user']) && check_username ($user)) {
?>


	<!-- Folders starts here. -->
	<div class="folders" style="width: <?php echo $column_width_folder; ?>; height: <?php echo $table_height; ?>;">

	<?php
	require_once (ABSOLUTE_PATH . "folders.php");
	$tree = & new folder ($user);
	$tree->make_tree (0);
	$tree->print_tree ();
	?>

	<!-- Folders ends here. -->
	</div>

	<!-- Bookmarks starts here. -->
	<div class="bookmarks" style="height: <?php echo $table_height; ?>;">

    <?php

    require_once (ABSOLUTE_PATH . "bookmarks.php");
    $query = sprintf ("SELECT title, url, description, UNIX_TIMESTAMP(date) AS timestamp, id, favicon
            FROM bookmark
            WHERE user='%s'
            AND childof='%d'
            AND deleted!='1'
            AND public='1'
            ORDER BY $order[1]",
            $mysql->escape ($user),
            $mysql->escape ($folderid));

    if ($mysql->query ($query)) {
            $bookmarks = array ();
            while ($row = mysql_fetch_assoc ($mysql->result)) {
                    array_push ($bookmarks, $row);
            }
            list_bookmarks ($bookmarks,
                    false,
                    false,
                    $settings['show_bookmark_icon'],
                    true,
                    $settings['show_bookmark_description'],
                    $settings['show_column_date'],
                    false,
                    false,
                    false,
                    false,
                    true,
                    $user);
    }
    else {
            message ($mysql->error);
    }

    ?>
	
	<!-- Bookmarks ends here. -->
	</div>

<?php
}
else {
		echo '<div id="content" style="height:' .  $table_height . ';">' . "\n";
        $query = "SELECT user, SUM(bookmarks) AS bookmarks, SUM(folders) AS folders FROM (
                SELECT user, 1 AS bookmarks, 0 AS folders FROM bookmark WHERE public='1' AND deleted!='1'
                UNION ALL
                SELECT user, 0 AS bookmarks , 1 AS folders FROM folder WHERE public='1' AND deleted!='1'
                ) AS tmp GROUP BY user";

        if ($mysql->query ($query)) {
                while ($row = mysql_fetch_object ($mysql->result)) {
                        echo '<p class="shared"><a href="' . $_SERVER['SCRIPT_NAME'] . '?user=' . $row->user . '&folderid=0"><b>' . $row->user . "</b><br>\n";
                        echo "Shares $row->folders Folders and $row->bookmarks Bookmarks</a></p>\n";
                }
        }
        else {
                message ($mysql->error);
        }
				echo "</div>";
}
?>

	<!-- Main content ends here. -->
	</div>
<!-- Wrapper ends here. -->
</div>

<?php
print_footer ();
require_once (ABSOLUTE_PATH . "footer.php");
?>

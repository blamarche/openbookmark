<?php
require_once ("./header.php");
logged_in_only ();

$search = set_get_string_var ('search');
if ($search != '') {
	$search_mode = true;
}
else {
	$search_mode = false;
}

$order = set_get_order ();

?>

	<?php if (!$search_mode): ?>
	<script>
	<!--
	var selected_folder_id = 0;

	$(document).ready(function() {
		//setup collapsing menus
		$(".mnu").click(function(){
			var options = {};
			$("#"+$(this).attr("target")).toggle('blind',options,300);
		});
		
		setupFolderIntercepts();
		setupBookmarkIntercepts();
	});

	function setupFolderIntercepts()
	{
		$(".folders").removeClass("loading-anim");
		$(".bookmarks").removeClass("loading-anim");
		
		$(".flink").click(function(){
			var url = $(this).attr('href');
			var folderurl=url.replace('index.php','async_folders.php');
			var bookmarkurl=url.replace('index.php','async_bookmarks.php');
			
			if ($("#folders").hasClass("mobile"))
				$("#folders").toggle('blind',{},300);
			
			selected_folder_id = $(this).attr("folderid");
			
			$(".folders").addClass("loading-anim");
			$(".bookmarks").addClass("loading-anim");
			
			$(".folders").load(folderurl, setupFolderIntercepts);
			$(".bookmarks").load(bookmarkurl, setupBookmarkIntercepts);
			
			return false;
		});
	}

	function setupBookmarkIntercepts()
	{
		$(".bookmarks").removeClass("loading-anim");
		
		$(".blink").click(function(){
			var url = $(this).attr('href');
			var bookmarkurl=url.replace('index.php','async_bookmarks.php');
			
			$(".bookmarks").addClass("loading-anim");
			$(".bookmarks").load(bookmarkurl, setupBookmarkIntercepts);
			
			return false;
		});
	}
	-->
	</script>
	<?php endif; ?>

	<?php if (is_mobile_browser() && !$search_mode): ?>
	<script>
	<!--
	$(document).ready(function() {
		//make collapsible menu
		$("#menu").hide();
		
	});
	-->
	</script>
	<link rel="stylesheet" type="text/css" href="mobile.css" />
	<?php echo ($settings["theme"]!="") ? '<link rel="stylesheet" type="text/css" href="./mobile'.$settings["theme"].'.css" />' : ""; ?>
	
	<?php endif; ?>

<h1 id="caption"><?php echo $username; ?>&#039;s Online Bookmarks</h1>

<!-- Wrapper starts here. -->
<div style="min-width: <?php echo 230 + $settings['column_width_folder']; ?>px;">
	<!-- google search box -->
	<div class="desktop" id="googlesearch">
		<form method="get" action="http://www.google.com/search" target="_blank">
			<input type="text"   name="q" size="15" maxlength="255" value="" />
			<input type="submit" value="Google" />
		</form>
	</div>
	
	<!-- Menu starts here. -->
	<h2 id="menu-head" class="mobile nav mnu" target="menu">Actions</h2>
	<div id="menu">
		<div class="navblock">
		<h2 class="nav mnu" target="mnu_search">Filter</h2>
		<ul class="nav" id="mnu_search">
		  <li>
		  	<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="GET" class="nav">
				<input type="text" name="search" size="7" value="<?php echo $search; ?>"/>
				<input type="submit" value="Go" name="submit"/>
		  	</form>
		  </li>
		</ul>
		</div>

		<div class="navblock">
		<h2 class="nav mnu" target="mnu_bookmarks">Bookmarks</h2>
		<ul class="nav" id="mnu_bookmarks">
			<?php if ($search_mode) { ?>
			<li><a href="./index.php"><?php echo $settings['root_folder_name']; ?></a></li>
			<?php } ?>
		  <li><a href="javascript:bookmarknew('<?php echo $folderid; ?>')">New Bookmark</a></li>
		  <li><a href="javascript:bookmarkedit(checkselected())">Edit Bookmarks</a></li>
		  <li><a href="javascript:bookmarkmove(checkselected())">Move Bookmarks</a></li>
		  <li><a href="javascript:bookmarkdelete(checkselected())">Delete Bookmarks</a></li>
		  <li><a href="./shared.php">Shared Bookmarks</a></li>
		</ul>
		</div>
	
		<div class="navblock">
		<h2 class="nav mnu" target="mnu_folders">Folders</h2>
		<ul class="nav" id="mnu_folders">
			<li><a href="javascript:foldernew('<?php echo $folderid; ?>')">New Folder</a></li>
			<li><a href="javascript:folderedit('<?php echo $folderid; ?>')">Edit Folder</a></li>
			<li><a href="javascript:foldermove('<?php echo $folderid; ?>')">Move Folder</a></li>
			<li><a href="javascript:folderdelete('<?php echo $folderid; ?>')">Delete Folder</a></li>
			<li><a href="./index.php?expand=&amp;folderid=0">Collapse All</a></li>
		</ul>
		</div>
		
		<div class="navblock">
		<h2 class="nav mnu" target="mnu_tools">Tools</h2>
		<ul class="nav" id="mnu_tools">
			<?php if (admin_only ()) { ?>
			<li><a href="./admin.php">Admin</a></li>
			<?php } ?>
			<li><a href="./import.php">Import</a></li>
			<li><a href="./export.php">Export</a></li>
			<li><a href="./sidebar.php">View as Sidebar</a></li>
			<li><a href="./settings.php">Settings</a></li>
			<li><a href="./index.php?logout=1">Logout</a></li>
		</ul>
		</div>
	<!-- Menu ends here. -->
	</div>

	<!-- Main content starts here. -->
	<div id="main">

			<?php if ($search_mode): ?>

			<div style="height: <?php echo ($table_height == 0) ? "auto" : $table_height; ?>; overflow:auto;">

				<div class="bookmark">
					<a class="f" href="./index.php"><img src="./images/folder_open.gif" alt=""> My Bookmarks</a>
				</div>

					<?php

	          require_once ('./lib/BooleanSearch.php');
	          $searchfields = array ('url', 'title', 'description');
	
	          $query = assemble_query ($search, $searchfields);
	
	          if ($mysql->query ($query)) {
	                  $bookmarks = array ();
	                  while ($row = mysql_fetch_assoc ($mysql->result)) {
	                          array_push ($bookmarks, $row);
	                  }
	                  if (count ($bookmarks) > 0) {
	                          require_once (ABSOLUTE_PATH . "bookmarks.php");
	                          list_bookmarks ($bookmarks,
	                                  true,
	                                  true,
	                                  $settings['show_bookmark_icon'],
	                                  true,
	                                  $settings['show_bookmark_description'],
	                                  $settings['show_column_date'],
	                                  $settings['show_column_edit'],
	                                  $settings['show_column_move'],
	                                  $settings['show_column_delete'],
	                                  $settings['show_public'],
	                                  false);
	                  }
	                  else {
	                          echo '<div id="content"> No Bookmarks found matching <b>' . $search . '</b>.</div>';
	                  }
	          }
	          else {
	                  message ($mysql->error);
	          }

					?>

			</div>

			<?php else: ?>

	<!-- Folders starts here. -->
	<h2 id="folders-head" class="mobile nav mnu" target="folders">Folders</h2>
	<div id="folders" class="folders mnu <?php echo (is_mobile_browser() ? "mobile" : ""); ?>" style="width: <?php echo ($column_width_folder == 0) ? "auto" : $column_width_folder; ?>; height: <?php echo ($table_height == 0) ? "auto" : $table_height; ?>;">

	<?php
	require_once (ABSOLUTE_PATH . "folders.php");
	$tree = new folder;
	$tree->make_tree (0);
	$tree->print_tree ();
	?>

	<!-- Folders ends here. -->
	</div>

	<!-- Bookmarks starts here. -->
	<div class="bookmarks" style="height: <?php echo ($table_height == 0) ? "auto" : $table_height; ?>;">

	<?php

	require_once (ABSOLUTE_PATH . "bookmarks.php");
	$query = sprintf ("SELECT title, url, description, UNIX_TIMESTAMP(date) AS timestamp, id, favicon, public
		FROM bookmark 
		WHERE user='%s'
		AND childof='%d'
		AND deleted!='1'
		ORDER BY $order[1]",
		$mysql->escape ($username),
		$mysql->escape ($folderid));

	if ($mysql->query ($query)) {
		$bookmarks = array ();
		while ($row = mysql_fetch_assoc ($mysql->result)) {
			array_push ($bookmarks, $row);
		}
		list_bookmarks ($bookmarks,
			true,
			false,
			$settings['show_bookmark_icon'],
			true,
			$settings['show_bookmark_description'],
			$settings['show_column_date'],
			$settings['show_column_edit'],
			$settings['show_column_move'],
			$settings['show_column_delete'],
			$settings['show_public'],
			true);
	}
	else {
		message ($mysql->error);
	}
	?>

	<!-- Bookmarks ends here. -->
	</div>

			<?php endif; ?>


	<!-- Main content ends here. -->
	</div>
<!-- Wrapper ends here. -->
</div>

<?php
print_footer ();
require_once (ABSOLUTE_PATH . "footer.php");
?>
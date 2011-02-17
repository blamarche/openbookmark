<?php
if (basename ($_SERVER['SCRIPT_NAME']) == basename (__FILE__)) {
	die ("no direct access allowed");
}

function list_bookmarks ($bookmarks, $show_checkbox, $show_folder, $show_icon, $show_link, $show_desc, $show_date, $show_edit, $show_move, $show_delete, $show_share, $show_header, $user = false) {
	global $folderid,
		$expand,
		$settings,
		$column_width_folder,
		$bookmark_image,
		$edit_image,
		$move_image,
		$delete_image,
		$folder_opened,
		$folder_opened_public,
		$date_formats,
		$order;

	# print the bookmark header if enabled.
	# Yes, it's ugly PHP code, but beautiful HTML code.
	if ($show_header) {
		if ($order[0] == 'titleasc') {
			$sort_t = 'titledesc';
			$img_t = '<img src="./images/ascending.gif" alt="">';
		}
		else if ($order[0] == 'titledesc') {
			$sort_t = 'titleasc';
			$img_t = '<img src="./images/descending.gif" alt="">';
		}
		else {
			$sort_t = 'titleasc';
			$img_t = '<img src="./images/descending.gif" alt="" class="invisible">';
		}

		if ($order[0] == 'dateasc') {
			$sort_d = 'datedesc';
			$img_d = '<img src="./images/ascending.gif" alt="">';
		}
		else if ($order[0] == 'datedesc') {
			$sort_d = 'dateasc';
			$img_d = '<img src="./images/descending.gif" alt="">';
		}
		else {
			$sort_d = 'dateasc';
			$img_d = '<img src="./images/descending.gif" alt="" class="invisible">';
		}

		echo '<div class="bookmarkcaption">' . "\n";
		if ($show_folder) {
			echo "\t" . '<div style="width:' . $column_width_folder . '; float: left;">&nbsp;</div>' . "\n";
		}
		if ($show_checkbox) {
			echo "\t\t" . '<div class="bmleft">' . "\n";
			echo "\t\t\t" . '<input type="checkbox" name="CheckAll" onClick="selectthem(\'checkall\', this.checked)">' . "\n";
			echo "\t\t" . '</div>' . "\n";
		}
		if ($show_date) {
			$query_data = array (
				'folderid' => $folderid,
				'expand' => implode (",", $expand),
				'order' => $sort_d,
			);
			if ($user) {
				$query_data['user'] = $user;
			}
			$query_string = assemble_query_string ($query_data);
			echo "\t\t" . '<div class="bmright">' . "\n";
			echo "\t\t\t" . '<span class="date">' . "\n";
			echo "\t\t\t\t" . '<a href="' . $_SERVER['SCRIPT_NAME'] . '?' . $query_string . '" class="f">Date ' . $img_d . '</a>' . "\n";
			echo "\t\t\t" . '</span>' . "\n";
			if ($show_edit) {
				echo "\t\t\t" . '<img src="./images/edit.gif"   alt="" class="invisible">' . "\n";
			}
			if ($show_move) {
				echo "\t\t\t" . '<img src="./images/move.gif"   alt="" class="invisible">' . "\n";
			}
			if ($show_delete) {
				echo "\t\t\t" . '<img src="./images/delete.gif" alt="" class="invisible">' . "\n";
			}
			echo "\t\t" . '</div>' . "\n";
		}
		echo "\t\t" . '<div class="link">' . "\n";
		if ($show_icon) {
			echo "\t\t\t" . '<img src="./images/bookmark_image.gif" alt="" class="invisible">' . "\n";
		}
		$query_data ['order'] = $sort_t;
		$query_string = assemble_query_string ($query_data);
		echo "\t\t\t" . '<a href="' . $_SERVER['SCRIPT_NAME'] . '?' . $query_string . '" class="f">Title ' . $img_t . '</a>' . "\n";
		echo "\t\t" . '</div>' . "\n";
		echo "\t" . '</div>' . "\n\n";
	}


	if ($show_folder) {
		require_once (ABSOLUTE_PATH . "folders.php");
		$tree = & new folder;
	}

	echo '<form name="bookmarks" action="" class="nav">' . "\n";

	foreach ($bookmarks as $value) {
		echo '<div class="bookmark">' . "\n";

		# the folders, only needed when searching for bookmarks
		if ($show_folder) {
			if ($value['fid'] == null) {
				$value['name'] = $settings['root_folder_name'];
				$value['fid'] = 0;
			}
			if ($value['fpublic']) {
				$folder_image = $folder_opened_public;
			}
			else {
				$folder_image = $folder_opened;
			}
			$expand = $tree->get_path_to_root ($value['fid']);
			echo "\t" . '<div style="width:' . $column_width_folder . '; float: left;">';
			echo '<a class="f" href="./index.php?expand=' . implode (",", $expand) . '&folderid='. $value['fid'] .'#' . $value['fid'] . '">';
			echo $folder_image . " " . $value['name'] . "</a>";
			echo "</div>\n";
		}

		# the checkbox and favicon section
		echo "\t" . '<div class="bmleft">' . "\n";
		# the checkbox
		if ($show_checkbox){
			echo "\t\t" . '<input type="checkbox" name="' . $value['id'] . '">' . "\n";
		}
		echo "\n\t</div>\n";

		# the share, date and edit/move/delete icon section
		echo "\t" . '<div class="bmright">' . "\n";
		if ($show_share) {
			$share = $value['public'] ? 'public' : 'private';
			echo "\t\t" . '<span class="' . $share . '">' . $share . "</span>\n";
		}

		if ($show_date) {
			echo "\t\t" . '<span class="date">';
			echo date ($date_formats[$settings['date_format']], $value['timestamp']);
			echo "\t</span>\n";
		}

		# the edit column
		if ($show_edit) {
			echo "\t\t" . '<a href="javascript:bookmarkedit(\'' . $value['id'] . '\')">';
			echo sprintf ($edit_image, "Edit");
			echo "</a>\n";
		}

		# the move column
		if ($show_move) {
			echo "\t\t" . '<a href="javascript:bookmarkmove(\'' . $value['id'] . '\', \'' . 'expand=' . implode (",", $expand) . '&amp;folderid=' . $folderid . '\')">';
			echo sprintf ($move_image, "Move");
			echo "</a>\n";
		}

		# the delete column
		if ($show_delete) {
			echo "\t\t" . '<a href="javascript:bookmarkdelete(\'' . $value['id'] . '\')">';
			echo sprintf ($delete_image, "Delete");
			echo "</a>\n";
		}
		echo "\t</div>\n";

		# the favicon
		echo "\t" . '<div class="link">' . "\n";
		echo "\t\t";
		if ($show_icon){
			if ($value['favicon'] && is_file ($value['favicon'])) {
				echo '<img src="' . $value['favicon'] . '" width="16" height="16" alt="">' . "\n";
			}
			else {
				echo $bookmark_image . "\n";
			}
		}

		# the link
		if ($settings['open_new_window']) {
			$target = ' target="_blank"';
		}
		else {
			$target = null;
		}

		if ($show_link){
			$link = '<a href="' . $value['url'] . '" title="' . $value['url'] . '"' . $target . '>' . $value['title'] . "</a>";
		}
		else {
			$link = $value['title'];
		}
		echo "\t\t$link\n";
		echo "\t</div>\n";

		# the description and if not empty
		if ($show_desc && $value['description'] != "") {
			if ($show_folder) {
				$css_extension = ' style="margin-left: ' . $column_width_folder . ';"';
			}
			else {
				$css_extension = "";
			}
			echo "\t" . '<div class="description"' . $css_extension . '>' . $value['description'] . "</div>\n";
		}

		echo "</div>\n\n";
	}
	echo "</form>\n";
}

?>

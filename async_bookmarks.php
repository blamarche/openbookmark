<?php
require_once ("./async_header.php");
logged_in_only ();

$order = set_get_order ();

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
			true,
			false,
			'index.php');
	}
	else {
		message ($mysql->error);
	}
?>
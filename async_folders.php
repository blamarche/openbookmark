<?php
require_once ("./async_header.php");
logged_in_only ();

require_once (ABSOLUTE_PATH . "folders.php");
$tree = new folder;
$tree->make_tree (0);
$tree->print_tree ('index.php');
?>
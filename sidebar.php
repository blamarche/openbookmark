<?php
define ("ABSOLUTE_PATH", dirname (__FILE__) . "/");
require_once (ABSOLUTE_PATH . "lib/webstart.php");
require_once (ABSOLUTE_PATH . "config/config.php");
require_once (ABSOLUTE_PATH . "lib/mysql.php");
$mysql = & new mysql;
require_once (ABSOLUTE_PATH . "lib/auth.php");
$auth = & new Auth;
require_once (ABSOLUTE_PATH . "lib/lib.php");
require_once (ABSOLUTE_PATH . "lib/login.php");

class sidebar {
        function sidebar () {
                # collect the folder data
                require_once (ABSOLUTE_PATH . "folders.php");
                $this->tree = & new folder;
                $this->tree->folders[0] = array ('id' => 0, 'childof' => null, 'name' => $GLOBALS['settings']['root_folder_name']);

                global $username, $mysql;

                $this->counter = 0;

                # collect the bookmark data
                $query = sprintf ("SELECT title, url, description, childof, id, favicon
                        FROM bookmark
                        WHERE user='%s'
                        AND deleted!='1' ORDER BY title",
                        $mysql->escape ($username));

                if ($mysql->query ($query)) {
                        while ($row = mysql_fetch_assoc ($mysql->result)) {
                                if (!isset ($this->bookmarks[$row['childof']])) {
                                        $this->bookmarks[$row['childof']] = array ();
                                }
                                array_push ($this->bookmarks[$row['childof']], $row);
                        }
                }
                else {
                        message ($mysql->error);
                }
        }

        function make_tree ($folderid) {
                if (isset ($this->tree->children[$folderid])) {
                        $this->counter++;
                        foreach ($this->tree->children[$folderid] as $value) {
                                $this->print_folder ($value);
                                $this->make_tree ($value);
                                $this->print_folder_close ($value);
                        }
                        $this->counter--;
                }
                $this->print_bookmarks ($folderid);
        }

        function print_folder ($folderid) {
                echo str_repeat ("    ", $this->counter) . '<li class="closed"><img src="./jquery/images/folder.gif" alt=""> ' . $this->tree->folders[$folderid]['name'] . "\n";
                if (isset ($this->tree->children[$folderid]) || isset ($this->bookmarks[$folderid])) {
                        echo str_repeat ("    ", $this->counter + 1) . "<ul>\n";
                }
        }

        function print_folder_close ($folderid) {
                if (isset ($this->tree->children[$folderid]) || isset ($this->bookmarks[$folderid])) {
                        echo str_repeat ("    ", $this->counter + 1) . "</ul>\n";
                }
                echo str_repeat ("    ", $this->counter) . "</li>\n";
        }

        function print_bookmarks ($folderid) {
                $spacer = str_repeat ("    ", $this->counter);
                if (isset ($this->bookmarks[$folderid])) {
                        foreach ($this->bookmarks[$folderid] as $value) {
                                if ($value['favicon'] && is_file ($value['favicon'])) {
                                        $icon = '<img src="' . $value['favicon'] . '" width="16" height="16" border="0" alt="">';
                                }
                                else {
                                        $icon = '<img src="./jquery/images/file.gif" alt="">';
                                }
                                echo $spacer . '    <li><a href="' . $value['url'] . '" target="_blank">' . $icon . " " . $value['title'] . "</a></li>\n";
                        }
                }
        }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Online-Bookmarks</title>
        <link rel="stylesheet" type="text/css" href="./style.css">

        <script src="./jquery/jquery.js" type="text/javascript"></script>
        <script src="./jquery/jquery.treeview.js" type="text/javascript"></script>
        <script type="text/javascript">
        $(document).ready(function(){
                $("#browser").Treeview();
        });
        </script>
        <style type="text/css">
                html, body {height:100%; margin: 0; padding: 0; }

                html>body {
                        font-size: 16px;
                        font-size: 68.75%;
                } /* Reset Base Font Size */

                body {
                        font-family: Verdana, helvetica, arial, sans-serif;
                        font-size: 68.75%;
                        background: #fff;
                        color: #333;
                        padding-left: 20px;
                } /* Reset Font Size */

                .treeview, .treeview ul {
                        padding: 0;
                        margin: 0;
                        list-style: none;
                }

                .treeview li {
                        margin: 0;
                        padding: 3px 0pt 3px 16px;
                }

                ul.dir li { padding: 2px 0 0 16px; }

                .treeview li { background: url(./jquery/images/tv-item.gif) 0 0 no-repeat; }
                .treeview .collapsable { background-image: url(./jquery/images/tv-collapsable.gif); }
                .treeview .expandable { background-image: url(./jquery/images/tv-expandable.gif); }
                .treeview .last { background-image: url(./jquery/images/tv-item-last.gif); }
                .treeview .lastCollapsable { background-image: url(./jquery/images/tv-collapsable-last.gif); }
                .treeview .lastExpandable { background-image: url(./jquery/images/tv-expandable-last.gif); }

        </style>
        </head>
        <body>

<p><a href="./">Back to Online-Bookmarks</a></p>

<?php

logged_in_only ();

$sidebar = & new sidebar;

echo '<ul id="browser" class="dir">' . "\n";
$sidebar->make_tree (0);
echo "</ul>\n";

require_once (ABSOLUTE_PATH . "footer.php");
?>

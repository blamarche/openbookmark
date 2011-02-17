<?php
require_once ('./header.php');
logged_in_only ();
?>

<h2 class="title">Select Folder</h2>

			<div style="width:100%; height:330px; overflow:auto;">

				<?php
				require_once ('./folders.php');
				$tree = & new folder;
				$tree->make_tree (0);
				$tree->print_tree ();
				$path = $tree->print_path ($folderid);
				?>

			</div>
			<br>
			<input type="submit" value=" OK " onClick="javascript:opener.childof.value = '<?php echo $folderid; ?>';opener.path.value = '<?php echo $path; ?>'; self.close()">
			<input type="button" value="Cancel" onClick="window.close()">
			<input type="button" value=" New Folder " onClick="self.location.href='javascript:foldernew(<?php echo $folderid; ?>)'">

<?php
require_once (ABSOLUTE_PATH . "footer.php");
?>

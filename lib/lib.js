function reloadclose(){
  window.opener.location.reload();
  self.close();
}

function bookmarknew(folderid) {
    bookmark_new = window.open("./bookmark_new.php?folderid=" + folderid, "bookmarknew","toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=500");
}

function bookmarkedit(bmlist) {
  if (bmlist==""){
    alert("No Bookmarks selected.");
  }
  else {
    bookmark_edit = window.open("./bookmark_edit.php?bmlist=" + bmlist, "bookmarkedit","toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=500");
  }
}

function bookmarkmove(bmlist) {
  if (bmlist==""){
    alert("No Bookmarks selected.");
  }
  else {
    bookmark_move = window.open("./bookmark_move.php", bmlist, "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=450");
  }
}

function bookmarkdelete(bmlist) {
  if (bmlist==""){
    alert("No Bookmarks selected.");
  }
  else {
    bookmark_delete = window.open("./bookmark_delete.php?bmlist=" + bmlist, "bookmarkdelete", "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=450");
  }
}

function foldernew(folderid) {
  folder_new = window.open("./folder_new.php?folderid=" + folderid, "foldernew", "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=200");
}

function folderedit(folderid) {
  if (folderid=="" || folderid=='0'){
    alert("No Folder selected.");
  }
  else {
    folder_edit = window.open("./folder_edit.php?folderid=" + folderid, "folderedit", "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=220");
  }
}

function foldermove(folderid) {
  if (folderid=="" || folderid=='0'){
    alert("No Folder selected.");
  }
  else {
    folder_move = window.open("./folder_move.php", folderid, "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=450");
  }
}

function folderdelete(folderid) {
  if (folderid=="" || folderid=="0"){
    alert("No Folder selected.");
  }
  else {
    folder_delete= window.open("./folder_delete.php?folderid=" + folderid, "folderdelete", "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=200");
  }
}

function selectfolder(url) {
  select_folder = window.open("./select_folder.php" + url, "selectfolder", "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=450");
}

function chpw() {
  chpw_window = window.open("./change_password.php", "chpw", "toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=500,height=200");
}

function checkselected(){
var i;
var parameter='';
  for ( i = 0; i < window.document.forms['bookmarks'].elements.length; i++) {
    if (window.document.forms['bookmarks'].elements[i].checked == true) {
      parameter = parameter + window.document.forms['bookmarks'].elements[i].name + "_";
    }
  }
  result=parameter.replace(/_$/,"");
  return result
}

/* This function is from the following location:
   http://www.squarefree.com/bookmarklets/
*/

function selectthem(boxes, stat){
	var x,k,f,j;
	x=document.forms;

	for (k = 0; k < x.length; ++k){
		f = x[k];
		for (j = 0; j < f.length; ++j){
			if (f[j].type.toLowerCase() == "checkbox"){
				if (boxes == "all"){
					f[j].checked = true ;
				}
				else if (boxes == "none"){
					f[j].checked = false ;
				}
				else if (boxes == "toggle") {
					f[j].checked = !f[j].checked ;
				}
				else if (boxes == "checkall") {
					f[j].checked = stat;
				}
			}
		}
	}
}

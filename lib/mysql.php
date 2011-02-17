<?php

class mysql {

	var $error = "";
	var $result = false;

	function mysql () {
		global $dsn;
		if ( ! @mysql_connect ($dsn['hostspec'], $dsn['username'], $dsn['password'])) {
			$this->error = mysql_error ();
		}
		if ( ! @mysql_select_db ($dsn['database'])) {
			$this->error = mysql_error ();
		}
	}

	function query ($query) {
		if ($this->result = mysql_query ($query)) {
			return true;
		}
		else{
			$this->error = mysql_error ();
			return false;
		}
	}

	function escape ($string) {
		return mysql_real_escape_string ($string);
	}


}

?>
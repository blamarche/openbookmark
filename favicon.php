<?php
if (basename ($_SERVER['SCRIPT_NAME']) == basename (__FILE__)) {
	die ("no direct access allowed");
}

class favicon {
	function favicon ($url) {
		global $settings, $convert_favicons;
		if ($settings['show_bookmark_icon']) {
			if ($this->parsed_url = $this->return_parse_url ($url)) {
				if ($this->favicon_url = $this->get_favicon_url ()) {
					$this->icon_name = rand () . basename ($this->favicon_url);
					if ($this->get_favicon_image ()) {
						if ($convert_favicons) {
							$this->favicon = $this->convert_favicon ();
						}
						else {
							$this->favicon = "./favicons/" . $this->icon_name;
						}
					}
				}
			}
		}
	}
	###
	### check the image type and convert & resize it if required
	### returns the absolute path of the (converted) .png file
	###
	function convert_favicon () {

		global $convert, $identify;

		$tmp_file = "./favicons/" . $this->icon_name;
		# find out file type
		if (@exec ("$identify $tmp_file", $output)) {
			$ident = explode (" ", $output[0]);
			if (count ($output) > 1) {
				$file_to_convert = $ident[0];
			}
			else {
				$file_to_convert = $tmp_file;
			}

			# convert image in any case to 16x16 and .png
			system ("$convert $file_to_convert -resize 16x16 $tmp_file.png");
			@unlink ($tmp_file);
			return $tmp_file . ".png";
		}
		else {
			@unlink ($tmp_file);
			return false;
		}
	}

	###
	### download and save favicon
	###
	function get_favicon_image () {
		//Selbstgebastelte, IIS-kompatible Version von Arne Haak (www.arnehaak.de)
		# HTTP-Url auswerten
		$httpparsed = $this->return_parse_url ($this->favicon_url);

		//HTTP-Request-Header erzeugen
		$httprequest = "GET ".$httpparsed['path']." HTTP/1.0\r\n".
				"Accept: */*\r\n".
				"Accept-Language: en\r\n".
				"Accept-Encoding: identity\r\n".
				"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n".
				"Host: ".$httpparsed['host']."\r\n".
				"Connection: close\r\n\r\n";

		//Verbindung aufbauen und Request abschicken
		if ($httphandle = fsockopen($httpparsed['host'],$httpparsed['port'])) {
			fputs($httphandle, $httprequest);

			//Daten runterladen solange vorhanden
			$answerdata = null;
			do {
				$answerdata .= fread($httphandle, 1024);
			} while (feof($httphandle) != true);

			// Verbindung schliessen
			fclose ($httphandle);

			//Header finden und abtrennen
			$finalposi = strpos($answerdata, "\r\n\r\n") + 4;  //Position des ersten Bytes nach dem Header bestimmen
			$finalfile = substr($answerdata, $finalposi, strlen($answerdata) - $finalposi); //Header abschneiden

			//Datei abspeichern
			if ($fp = @fopen("./favicons/" . $this->icon_name, "w")) {
				fwrite($fp, $finalfile);
				fclose($fp);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	###
	### checks for the existence of a favicon on a remote server
	### and returns the url if one exist
	###
	function get_favicon_url () {
		global $timeout;
		# search for favicon in document root first
		if ($socket = @fsockopen ($this->parsed_url['host'], $this->parsed_url['port'], $errno, $errstr, $timeout)) {
			fwrite ($socket, "HEAD /favicon.ico HTTP/1.0\r\nHost: " . $this->parsed_url['host'] . "\r\n\r\n");
			$http_response = fgets ($socket, 22);
			fclose ($socket);
			if (ereg ("200 OK", $http_response)) {
				#echo "favicon found in document root\n";
				return $this->parsed_url['scheme'] . "://" . $this->parsed_url['host'] . ":" . $this->parsed_url['port'] . "/favicon.ico";
			}
			else {
				# if favicon was not found in document root, search in html header for it
				if ($socket = @fsockopen ($this->parsed_url['host'], $this->parsed_url['port'], $errno, $errstr, $timeout)) {
					fwrite ($socket, "GET " . $this->parsed_url['path'] . " HTTP/1.0\r\nHost: " . $this->parsed_url['host'] . "\r\n\r\n");
					while (!feof ($socket)) {
						$html = fgets ($socket, 1024);
						if ($html == null) {
							return false;
						}

						# we only want to search in HTML documents
						if (preg_match ('/.*Content-Type:.*/si', $html, $contenttype)) {
							if ( ! preg_match ('/text\/html/si', $contenttype[0])) {
								return false;
							}
						}

						if (preg_match ('/<link[^>]+rel="(?:shortcut )?icon".*>/si', $html, $tag)) {
							#echo "found favicon in html header\n";
							if (preg_match ('/<link[^>]+href="([^"]+)".*>/si', $tag[0], $location)) {
								# the favicon location is an url
								if (substr($location[1], 0, 7) == 'http://') {
									$favicon = $location[1];
								}
								# the favicon location is an absolute path
								else if (substr ($location[1], 0, 1) == '/') {
									$favicon = $this->parsed_url['scheme'] . '://' . $this->parsed_url['host'] . ":" . $this->parsed_url['port'] . $location[1];
								}
								# else the path can only be something useless
								# or a relative path, looking like this.
								# ./path/to/favicon.ico
								# path/to/favicon.ico
								else {
									# The location we called is either a file or a directory.
									# We have to guess. We assume it is a directory if there is a trailing slash
									if (substr ($this->parsed_url['path'], strlen($this->parsed_url['path'])-1) == "/") {
										$favicon = $this->parsed_url['scheme'] . '://' . $this->parsed_url['host'] . ":" . $this->parsed_url['port'] . $this->parsed_url['path'] . $location[1];
									}
									else {
										$favicon = $this->parsed_url['scheme'] . '://' . $this->parsed_url['host'] . ":" . $this->parsed_url['port'] . dirname ($this->parsed_url['path']) . '/' . $location[1];
									}
								}
								return $favicon;
							}
						}
						else if (preg_match ('/.*<\/head.*>/si', $html)) {
							#echo "html header end found, giving up\n";
							return false;
						}
					}
					fclose ($socket);
				}
			}
		}
		return false;
	}

	###
	### returns an array with parts of the given url
	###
	function return_parse_url ($url) {
		if ($parsed = @parse_url ($url)) {
			if (!isset ($parsed['scheme']) || $parsed['scheme'] == "") {
				$parsed['scheme'] = "http";
			}
			else if ($parsed['scheme'] != "http") {
				return false;
			}
			if (!isset ($parsed['host']) || $parsed['host'] == "") {
				return false;
			}
			if (!isset ($parsed['port']) || $parsed['port'] == "") {
				$parsed['port'] = 80;
			}
			if (!isset ($parsed['path']) || $parsed['path'] == "") {
				$parsed['path'] = "/";
			}
			return ($parsed);
		}
		else {
			return false;
		}
	}
}

?>

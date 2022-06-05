<?php
//    MyDMS. Document Management System
//    Copyright (C) 2002-2005  Markus Westphal
//    Copyright (C) 2006-2008 Malcolm Cowe
//    Copyright (C) 2010 Matteo Lucarelli
//
//    This program is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program; if not, write to the Free Software
//    Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

/* deprecated! use SeedDMS_Core_File::format_filesize() instead */
function formatted_size($size_bytes) { /* {{{ */
	if ($size_bytes>1000000000) return number_format($size_bytes/1000000000,1,".","")." GBytes";
	else if ($size_bytes>1000000) return number_format($size_bytes/1000000,1,".","")." MBytes";
	else if ($size_bytes>1000) return number_format($size_bytes/1000,1,".","")." KBytes";
	return number_format($size_bytes,0,"","")." Bytes";
} /* }}} */

/* Date picker needs a different syntax for date formats using
 * yyyy for %Y
 * yy for %y
 * mm for %m
 * dd for %d
 * This functions returns the converted format
 */
function getConvertDateFormat() { /* {{{ */
	global $settings;
	if($settings->_dateformat) {
		return str_replace(['y', 'Y', 'm', 'M', 'F', 'd', 'l', 'D'], ['yy', 'yyyy', 'mm', 'M', 'MM', 'dd', 'DD', 'D'], $settings->_dateformat);
	} else
		return 'yyyy-mm-dd';
} /* }}} */

function getReadableDate($timestamp=0) { /* {{{ */
	global $settings;
	if(!$timestamp)
		$timestamp = time();
	elseif(!is_numeric($timestamp))
		$timestamp = strtotime($timestamp);
	if($settings->_dateformat)
		return date($settings->_dateformat, $timestamp);
	else
		return date("Y-m-d", $timestamp);
} /* }}} */

function getLongReadableDate($timestamp) { /* {{{ */
	global $settings;
	if(!is_numeric($timestamp))
		$timestamp = strtotime($timestamp);
	if($settings->_datetimeformat)
		return date($settings->_datetimeformat, $timestamp);
	else
		return date("Y-m-d H:i:s", $timestamp);
} /* }}} */

function getPeriodOfTime($timestamp) { /* {{{ */
	if(!is_numeric($timestamp))
		$timestamp = strtotime($timestamp);

	$time = time() - $timestamp; // to get the time since that moment
	$time = ($time<1)? 1 : $time;
	$tokens = array (
		31536000 => 'abbr_year',
		2592000 => 'abbr_month',
		604800 => 'abbr_week',
		86400 => 'abbr_day',
		3600 => 'abbr_hour',
		60 => 'abbr_minute',
		1 => 'abbr_second'
	);

	foreach ($tokens as $unit => $text) {
		if ($time < $unit) continue;
		$numberOfUnits = floor($time / $unit);
		return $numberOfUnits.' '.(($numberOfUnits>1) ? getMLText($text):getMLText($text));
	}
} /* }}} */

/*
 * Converts a date string into a timestamp
 *
 * @param $date string date in format understood by strftime
 * @return integer/boolean unix timestamp or false in case of an error
 */
function makeTsFromDate($date) { /* {{{ */
	return strtotime($date);
} /* }}} */

/*
 * Converts a date/time string into a timestamp
 *
 * @param $date string date in form Y-m-d H:i:s
 * @return integer/boolean unix timestamp or false in case of an error
 */
function makeTsFromLongDate($date) { /* {{{ */
	return strtotime($date);
	$tmp = explode(' ', $date);
	if(count($tmp) != 2)
		return false;
	$tarr = explode(':', $tmp[1]);
	$darr = explode('-', $tmp[0]);
	if(count($tarr) != 3 || count($darr) != 3)
		return false;
	$ts = mktime($tarr[0], $tarr[1], $tarr[2], $darr[1], $darr[2], $darr[0]);
	return $ts;
} /* }}} */

function getReadableDuration($secs) { /* {{{ */
	$s = "";
	foreach ( getReadableDurationArray($secs) as $k => $v ) {
		if ( $v ) $s .= $v." ".($v==1? substr($k,0,-1) : $k).", ";
	}

	return substr($s, 0, -2);
} /* }}} */

function getReadableDurationArray($secs) { /* {{{ */
	$units = array(
		getMLText("weeks")   => 7*24*3600,
		getMLText("days")    =>   24*3600,
		getMLText("hours")   =>      3600,
		getMLText("minutes") =>        60,
		getMLText("seconds") =>         1,
	);

	foreach ( $units as &$unit ) {
		$quot  = intval($secs / $unit);
		$secs -= $quot * $unit;
		$unit  = $quot;
	}

	return $units;
} /* }}} */

//
// The original string sanitizer, kept for reference.
//function sanitizeString($string) {
//	$string = str_replace("'",  "&#0039;", $string);
//	$string = str_replace("--", "", $string);
//	$string = str_replace("<",  "&lt;", $string);
//	$string = str_replace(">",  "&gt;", $string);
//	$string = str_replace("/*", "", $string);
//	$string = str_replace("*/", "", $string);
//	$string = str_replace("\"", "&quot;", $string);
//
//	return $string;
//}

/* Deprecated, do not use anymore */
function sanitizeString($string) { /* {{{ */

	$string = (string) $string;
	if (get_magic_quotes_gpc()) {
		$string = stripslashes($string);
	}

	// The following three are against sql injection. They are not
	// needed anymore because strings are quoted propperly when saved into
	// the database.
//	$string = str_replace("\\", "\\\\", $string);
//	$string = str_replace("--", "\-\-", $string);
//	$string = str_replace(";", "\;", $string);
	// Use HTML entities to represent the other characters that have special
	// meaning in SQL. These can be easily converted back to ASCII / UTF-8
	// with a decode function if need be.
	$string = str_replace("&", "&amp;", $string);
	$string = str_replace("%", "&#0037;", $string); // percent
	$string = str_replace("\"", "&quot;", $string); // double quote
	$string = str_replace("/*", "&#0047;&#0042;", $string); // start of comment
	$string = str_replace("*/", "&#0042;&#0047;", $string); // end of comment
	$string = str_replace("<", "&lt;", $string);
	$string = str_replace(">", "&gt;", $string);
	$string = str_replace("=", "&#0061;", $string);
	$string = str_replace(")", "&#0041;", $string);
	$string = str_replace("(", "&#0040;", $string);
	$string = str_replace("'", "&#0039;", $string);
	$string = str_replace("+", "&#0043;", $string);

	return trim($string);
} /* }}} */

/* Deprecated, do not use anymore, but keep it for upgrading
 * older versions
 */
function mydmsDecodeString($string) { /* {{{ */

	$string = (string)$string;

	$string = str_replace("&amp;", "&", $string);
	$string = str_replace("&#0037;", "%", $string); // percent
	$string = str_replace("&quot;", "\"", $string); // double quote
	$string = str_replace("&#0047;&#0042;", "/*", $string); // start of comment
	$string = str_replace("&#0042;&#0047;", "*/", $string); // end of comment
	$string = str_replace("&lt;", "<", $string);
	$string = str_replace("&gt;", ">", $string);
	$string = str_replace("&#0061;", "=", $string);
	$string = str_replace("&#0041;", ")", $string);
	$string = str_replace("&#0040;", "(", $string);
	$string = str_replace("&#0039;", "'", $string);
	$string = str_replace("&#0043;", "+", $string);

	return $string;
} /* }}} */

function createVersionigFile($document) { /* {{{ */
	global $settings, $dms;

	// if directory has been removed recreate it
	if (!file_exists($dms->contentDir . $document->getDir()))
		if (!SeedDMS_Core_File::makeDir($dms->contentDir . $document->getDir())) return false;

	$handle = fopen($dms->contentDir . $document->getDir() .$settings-> _versioningFileName , "wb");

	if (is_bool($handle)&&!$handle) return false;

	$tmp = $document->getName()." (ID ".$document->getID().")\n\n";
	fwrite($handle, $tmp);

	$owner = $document->getOwner();
	$tmp = getMLText("owner")." = ".$owner->getFullName()." <".$owner->getEmail().">\n";
	fwrite($handle, $tmp);

	$tmp = getMLText("creation_date")." = ".getLongReadableDate($document->getDate())."\n";
	fwrite($handle, $tmp);

	$latestContent = $document->getLatestContent();
	$tmp = "\n### ".getMLText("current_version")." ###\n\n";
	fwrite($handle, $tmp);

	$tmp = getMLText("version")." = ".$latestContent->getVersion()."\n";
	fwrite($handle, $tmp);

	$tmp = getMLText("file")." = ".$latestContent->getOriginalFileName()." (".$latestContent->getMimeType().")\n";
	fwrite($handle, $tmp);

	$tmp = getMLText("comment")." = ". $latestContent->getComment()."\n";
	fwrite($handle, $tmp);

	$status = $latestContent->getStatus();
	$tmp = getMLText("status")." = ".getOverallStatusText($status["status"])."\n";
	fwrite($handle, $tmp);

	$reviewStatus = $latestContent->getReviewStatus();
	$tmp = "\n### ".getMLText("reviewers")." ###\n";
	fwrite($handle, $tmp);

	foreach ($reviewStatus as $r) {

		switch ($r["type"]) {
			case 0: // Reviewer is an individual.
				$required = $dms->getUser($r["required"]);
				if (!is_object($required)) $reqName = getMLText("unknown_user")." = ".$r["required"];
				else $reqName =  getMLText("user")." = ".$required->getFullName();
				break;
			case 1: // Reviewer is a group.
				$required = $dms->getGroup($r["required"]);
				if (!is_object($required)) $reqName = getMLText("unknown_group")." = ".$r["required"];
				else $reqName = getMLText("group")." = ".$required->getName();
				break;
		}

		$tmp = "\n".$reqName."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("status")." = ".getReviewStatusText($r["status"])."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("comment")." = ". $r["comment"]."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("last_update")." = ".$r["date"]."\n";
		fwrite($handle, $tmp);

	}


	$approvalStatus = $latestContent->getApprovalStatus();
	$tmp = "\n### ".getMLText("approvers")." ###\n";
	fwrite($handle, $tmp);

	foreach ($approvalStatus as $r) {

		switch ($r["type"]) {
			case 0: // Reviewer is an individual.
				$required = $dms->getUser($r["required"]);
				if (!is_object($required)) $reqName = getMLText("unknown_user")." = ".$r["required"];
				else $reqName =  getMLText("user")." = ".$required->getFullName();
				break;
			case 1: // Reviewer is a group.
				$required = $dms->getGroup($r["required"]);
				if (!is_object($required)) $reqName = getMLText("unknown_group")." = ".$r["required"];
				else $reqName = getMLText("group")." = ".$required->getName();
				break;
		}

		$tmp = "\n".$reqName."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("status")." = ".getApprovalStatusText($r["status"])."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("comment")." = ". $r["comment"]."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("last_update")." = ".$r["date"]."\n";
		fwrite($handle, $tmp);

	}

	$versions = $document->getContent();
	$tmp = "\n### ".getMLText("previous_versions")." ###\n";
	fwrite($handle, $tmp);

	for ($i = count($versions)-2; $i >= 0; $i--){

		$version = $versions[$i];
		$status = $version->getStatus();

		$tmp = "\n".getMLText("version")." = ".$version->getVersion()."\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("file")." = ".$version->getOriginalFileName()." (".$version->getMimeType().")\n";
		fwrite($handle, $tmp);

		$tmp = getMLText("comment")." = ". $version->getComment()."\n";
		fwrite($handle, $tmp);

		$status = $latestContent->getStatus();
		$tmp = getMLText("status")." = ".getOverallStatusText($status["status"])."\n";
		fwrite($handle, $tmp);

	}

	fclose($handle);
	return true;
} /* }}} */

/**
 * Calculate disk space of file or directory
 *
 * original funcion by shalless at rubix dot net dot au (php.net)
 * stat() replace by filesize() to make it work on all platforms.
 *
 * @param string $dir directory or filename
 * @return integer number of bytes
 */
function dskspace($dir) { /* {{{ */
	$space = 0;
	if(is_file($dir)) {
		$space = filesize($dir);
	} elseif (is_dir($dir)) {
		if($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false)
				if ($file != "." and $file != "..")
					$space += dskspace($dir."/".$file);
			closedir($dh);
		}
	}
	return $space;
} /* }}} */

/**
 * Replacement of PHP's basename function
 *
 * Because basename is locale dependent and strips off non ascii chars
 * from the beginning of filename, it cannot be used in a environment
 * where locale is set to e.g. 'C'
 */
function utf8_basename($path, $suffix='') { /* {{{ */
	$rpos = strrpos($path, DIRECTORY_SEPARATOR);
	if($rpos === false)
		return $path;
	$file = substr($path, $rpos+1);

	$suflen = strlen($suffix);
	if($suflen && (substr($file, -$suflen) == $suffix)){
			$file = substr($file, 0, -$suflen);
	}

	return $file;
} /* }}} */

function getLogger($prefix='') { /* {{{ */
	global $settings;

	if($settings->_logFileEnable) {
		if ($settings->_logFileRotation=="h") $logname=date("YmdH", time());
		else if ($settings->_logFileRotation=="d") $logname=date("Ymd", time());
		else $logname=date("Ym", time());
		$logname = $settings->_contentDir."log/".$prefix.$logname.".log";
		if(!file_exists($settings->_contentDir.'log'))
			@mkdir($settings->_contentDir.'log');
		if(file_exists($settings->_contentDir.'log') && is_dir($settings->_contentDir.'log')) {
			$logger = Log::factory('file', $logname);
			$logger->setMask(Log::MAX(PEAR_LOG_DEBUG));
		} else
			$logger = null;
	} else {
		$logger = null;
	}
	return $logger;
} /* }}} */

/**
 * Log a message
 *
 * This function is still here for convienice and because it is
 * used at so many places.
 *
 * @param string $msg
 * @param int $priority can be one of PEAR_LOG_EMERG, PEAR_LOG_ALERT,
 *            PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
 *						PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
 */
function add_log_line($msg="", $priority=null) { /* {{{ */
	global $logger, $user;

	if(!$logger) return;

	$ip = "-";
	if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif(!empty($_SERVER['REMOTE_ADDR']))
		$ip = $_SERVER['REMOTE_ADDR'];
	if(!empty($_SERVER["REQUEST_URI"]))
		$scriptname = basename($_SERVER["REQUEST_URI"]).' ';
	else
		$scriptname = basename($_SERVER["SCRIPT_NAME"]).' ';
	if($user)
		$logger->log($user->getLogin()." (".$ip.") ".$scriptname.($msg ? $msg : ''), $priority);
	else
		$logger->log("-- (".$ip.") ".$scriptname.($msg ? $msg : ''), $priority);
} /* }}} */

function _add_log_line($msg="") { /* {{{ */
	global $settings,$user;

	if ($settings->_logFileEnable!=TRUE) return;

	if ($settings->_logFileRotation=="h") $logname=date("YmdH", time());
	else if ($settings->_logFileRotation=="d") $logname=date("Ymd", time());
	else $logname=date("Ym", time());

	if($h = fopen($settings->_contentDir.$logname.".log", "a")) {
		fwrite($h,date("Y/m/d H:i", time())." ".$user->getLogin()." (".$_SERVER['REMOTE_ADDR'].") ".basename($_SERVER["REQUEST_URI"]).$msg."\n");
		fclose($h);
	}
} /* }}} */

function getFolderPathHTML($folder, $tagAll=false, $document=null) { /* {{{ */
	$path = $folder->getPath();
	$txtpath = "";
	for ($i = 0; $i < count($path); $i++) {
		if ($i +1 < count($path)) {
			$txtpath .= "<a href=\"../out/out.ViewFolder.php?folderid=".$path[$i]->getID()."&showtree=".showtree()."\">".
				htmlspecialchars($path[$i]->getName())."</a> / ";
		}
		else {
			$txtpath .= ($tagAll ? "<a href=\"../out/out.ViewFolder.php?folderid=".$path[$i]->getID()."&showtree=".showtree()."\">".
									 htmlspecialchars($path[$i]->getName())."</a>" : htmlspecialchars($path[$i]->getName()));
		}
	}
	if($document)
		$txtpath .= " / <a href=\"../out/out.ViewDocument.php?documentid=".$document->getId()."\">".htmlspecialchars($document->getName())."</a>";

	return $txtpath;
} /* }}} */

function showtree() { /* {{{ */
	global $settings;

	if (isset($_GET["showtree"])) return intval($_GET["showtree"]);
	else if ($settings->_expandFolderTree==0) return 0;

	return 1;
} /* }}} */

/**
 * Create a unique key which is used for form validation to prevent
 * CSRF attacks. The key is added to a any form that has to be secured
 * as a hidden field. Once the form is submitted the key is compared
 * to the current key in the session and the request is only executed
 * if both are equal. The key is derived from the session id, a configurable
 * encryption key and form identifierer.
 *
 * @param string $formid individual form identifier
 * @return string session key
 */
function createFormKey($formid='') { /* {{{ */
	global $settings, $session;

	if($session && $id = $session->getId()) {
		return md5($id.$settings->_encryptionKey.$formid);
	} else {
		return md5($settings->_encryptionKey.$formid);
	}
} /* }}} */

/**
 * Create a hidden field with the name 'formtoken' and set its value
 * to the key returned by createFormKey()
 *
 * @param string $formid individual form identifier
 * @return string input field for html formular
 */
function createHiddenFieldWithKey($formid='') { /* {{{ */
	return '<input type="hidden" name="formtoken" value="'.createFormKey($formid).'" />';
} /* }}} */

/**
 * Check if the form key in the POST or GET request variable 'formtoken'
 * has the value of key returned by createFormKey(). Request to modify
 * data in the DMS should always use POST because it is harder to run
 * CSRF attacks using POST than GET.
 *
 * @param string $formid individual form identifier
 * @param string $method defines if the form data is pass via GET or
 * POST (default)
 * @return boolean true if key matches otherwise false
 */
function checkFormKey($formid='', $method='POST') { /* {{{ */
	switch($method) {
		case 'GET':
			if(isset($_GET['formtoken']) && $_GET['formtoken'] == createFormKey($formid))
				return true;
			break;
		default:
			if(isset($_POST['formtoken']) && $_POST['formtoken'] == createFormKey($formid))
				return true;
	}

	return false;
} /* }}} */

/**
 * Check disk usage of currently logged in user
 *
 * @return boolean/integer true if no quota is set, number of bytes until
 *         quota is reached. Negative values indicate a disk usage above quota.
 */
function checkQuota($user) { /* {{{ */
	global $settings, $dms;

	/* check if quota is turn off system wide */
	if($settings->_quota == 0)
		return true;

	$quota = 0;
	$uquota = $user->getQuota();
	if($uquota > 0)
		$quota = $uquota;
	elseif($settings->_quota > 0) {
		$quota = $settings->_quota;
	}

	if($quota == 0)
		return true;

	return ($quota - $user->getUsedDiskSpace());
} /* }}} */

/**
 * Encrypt any data with a key
 *
 * @param string $key
 * @param string $value plain text data
 * @return string encrypted data
 */
function encryptData($key, $value) { /* {{{ */
  $text = $value;
  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
  return $crypttext;
} /* }}} */

/**
 * Decrypt data previously encrypted by encrypt
 *
 * @param string $key
 * @param string $value encrypted data
 * @return string plain text data
 */
function decryptData($key, $value) { /* {{{ */
  $crypttext = $value;
  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
  return trim($decrypttext);
} /* }}} */

/**
 * Return file extension for a give mimetype
 *
 * @param string $mimetype Mime-Type
 * @return string file extension including leading dot
 */
function get_extension($mimetype) { /* {{{ */
	if(empty($mimetype)) return false;
	switch($mimetype) {
		case 'image/bmp': return '.bmp';
		case 'image/x-ms-bmp': return '.bmp';
		case 'image/cis-cod': return '.cod';
		case 'image/gif': return '.gif';
		case 'image/ief': return '.ief';
		case 'image/jpeg': return '.jpg';
		case 'image/pipeg': return '.jfif';
		case 'image/tiff': return '.tif';
		case 'image/x-cmu-raster': return '.ras';
		case 'image/x-cmx': return '.cmx';
		case 'image/x-icon': return '.ico';
		case 'image/x-portable-anymap': return '.pnm';
		case 'image/x-portable-bitmap': return '.pbm';
		case 'image/x-portable-graymap': return '.pgm';
		case 'image/x-portable-pixmap': return '.ppm';
		case 'image/x-rgb': return '.rgb';
		case 'image/x-xbitmap': return '.xbm';
		case 'image/x-xpixmap': return '.xpm';
		case 'image/x-xwindowdump': return '.xwd';
		case 'image/png': return '.png';
		case 'image/x-jps': return '.jps';
		case 'image/x-freehand': return '.fh';
		case 'image/svg+xml': return '.svg';
		case 'audio/mp3': return '.mp3';
		case 'audio/mpeg': return '.mpeg';
		case 'audio/ogg': return '.ogg';
		case 'video/mp4': return '.mp4';
		case 'video/webm': return '.webm';
		case 'application/zip': return '.zip';
		case 'application/x-gzip': return '.gz';
		case 'application/x-rar': return '.rar';
		case 'application/x-compressed-tar': return '.tgz';
		case 'application/pdf': return '.pdf';
		case 'application/dxf': return '.dxf';
		case 'application/msword': return '.doc';
		case 'application/postscript': return '.ps';
		case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document': return '.docx';
		case 'application/vnd.openxmlformats-officedocument.presentationml.presentation': return '.pptx';
		case 'text/plain': return '.txt';
		case 'text/csv': return '.csv';
		case 'text/rtf': return '.rtf';
		case 'text/xml': return '.xml';
		case 'text/x-php': return '.php';
		case 'text/x-tex': return '.tex';
		case 'message/rfc822': return '.eml';
		default: return false;
	}
} /* }}} */

/**
 * Adds a missing front slash to a string
 *
 * This function is used for making sure a directory name has a
 * trailing directory separator
 */
function addDirSep($str) { /* {{{ */
	if(trim($str) == '')
		return '';
	if(substr(trim($str), -1, 1) != DIRECTORY_SEPARATOR)
		return trim($str).DIRECTORY_SEPARATOR;
	else
		return trim($str);
} /* }}} */

/**
 * Formats comments for aknowledge of reception.
 *
 * Only use in documentListRow()
 */
function formatComment($an) { /* {{{ */
	$t = array();
	foreach($an as $a)
		$t[] = $a['n']." × ".$a['c'];
	return $t;
} /* }}} */

/**
 * Send a file from disk to the browser
 *
 * This function uses either readfile() or the xѕendfile apache module if
 * it is installed.
 *
 * @param string $filename
 */
function sendFile($filename) { /* {{{ */
	global $settings;
	if($settings->_enableXsendfile && function_exists('apache_get_modules') && in_array('mod_xsendfile',apache_get_modules())) {
		header("X-Sendfile: ".$filename);
	} else {

		$size = filesize($filename);
		if (isset($_SERVER['HTTP_RANGE'])) {
			$fp = @fopen($filename, 'rb');
			$length = $size;           // Content length
			$start  = 0;               // Start byte
			$end    = $size - 1;       // End byte

//			header("Accept-Ranges: 0-$length");
			header("Accept-Ranges: bytes");

			$c_start = $start;
			$c_end   = $end;

			list($unit, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			if (trim($unit) !== 'bytes') {
					header('HTTP/1.1 416 Requested Range Not Satisfiable');
					header("Content-Range: bytes $start-$end/$size");
					exit;
			}
			if (strpos($range, ',') !== false) {
					header('HTTP/1.1 416 Requested Range Not Satisfiable');
					header("Content-Range: bytes $start-$end/$size");
					exit;
			}
			if ($range == '-') {
					$c_start = $size - substr($range, 1);
			} else {
					$range  = explode('-', $range);
					$c_start = $range[0];
					$c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
			}
			$c_end = ($c_end > $end) ? $end : $c_end;
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
					header('HTTP/1.1 416 Requested Range Not Satisfiable');
					header("Content-Range: bytes $start-$end/$size");
					exit;
			}
			$start  = $c_start;
			$end    = $c_end;
			$length = $end - $start + 1;
			fseek($fp, $start);
			header('HTTP/1.1 206 Partial Content');
			header("Content-Range: bytes $start-$end/$size");
			header("Content-Length: " . $length);

			$buffer = 1024 * 8;
			while(!feof($fp) && ($p = ftell($fp)) <= $end) {
				if ($p + $buffer > $end) {
					$buffer = $end - $p + 1;
				}
				set_time_limit(0);
				echo fread($fp, $buffer);
				flush();
			}

			fclose($fp);
		} else {
			header("Content-Length: " . $size);
			/* Make sure output buffering is off */
			if (ob_get_level()) {
				ob_end_clean();
			}
			readfile($filename);
		}
	}
} /* }}} */

/**
 * Return protocol and host of url
 *
 * @return string
 */
function getBaseUrl() { /* {{{ */
	return "http".((isset($_SERVER['HTTPS']) && (strcmp($_SERVER['HTTPS'],'off')!=0)) ? "s" : "")."://".$_SERVER['HTTP_HOST'];
} /* }}} */

function getToken($length){ /* {{{ */
	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet.= "0123456789";
	$max = strlen($codeAlphabet);

	for ($i=0; $i < $length; $i++) {
		$token .= $codeAlphabet[random_int(0, $max-1)];
	}

	return $token;
} /* }}} */

function isAjax() { /* {{{ */
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		return true;
	else	
		return false;
} /* }}} */

/**
 * Hash a password 
 *
 * @param string $password
 * @return string hashed password
 */
function seed_pass_hash($password) { /* {{{ */
	return md5($password);
} /* }}} */

/**
 * Verify a password 
 *
 * @param string $password
 * @return string hashed password
 */
function seed_pass_verify($password, $hash) { /* {{{ */
	return $hash == md5($password);
} /* }}} */

function resolveTask($task) {
	global $dms, $user, $settings, $logger, $fulltextservice, $notifier, $conversionmgr;

	if(is_object($task))
		return $task;
	if(is_string($task)) {
		if(class_exists($task)) {
			$task = new $task($dms, $user, $settings, $logger, $fulltextservice, $notifier, $conversionmgr);
		}
	}
	return $task;
}

/**
 * Return nonce for CSP
 *
 * @return string
 */
function createNonce() { /* {{{ */
	$length = 16;
	$usable = true;
	$bytes = openssl_random_pseudo_bytes($length, $usable);
	if ($usable === false) {
			// weak
			// @TODO do something?
	}
	return base64_encode($bytes);
} /* }}} */

/**
 * Compare function for sorting users by login
 *
 * Use this for usort()
 *
 * <code>
 * $users = $dms->getAllUsers();
 * usort($users, 'cmp_user_login');
 * </code>
 */
function cmp_user_login($a, $b) { /* {{{ */
	$as = strtolower($a->getLogin());
	$bs = strtolower($b->getLogin());
	if ($as == $bs) {
		return 0;
	}
	return ($as < $bs) ? -1 : 1;
} /* }}} */

/**
 * Compare function for sorting users by name 
 *
 * Use this for usort()
 *
 * <code>
 * $users = $dms->getAllUsers();
 * usort($users, 'cmp_user_fullname');
 * </code>
 */
function cmp_user_fullname($a, $b) { /* {{{ */
	$as = strtolower($a->getFullName());
	$bs = strtolower($b->getFullName());
	if ($as == $bs) {
		return 0;
	}
	return ($as < $bs) ? -1 : 1;
} /* }}} */

/**
 * Returns the mandatory reviewers
 *
 * This function checks if the reviewers have at least read access
 * on the folder containing the document.
 *
 * @param $folder folder where document is located
 * @param $user user creating the new version or document
 * @return array
 */
function getMandatoryReviewers($folder, $user) { /* {{{ */
	global $settings;

	/* Get a list of all users and groups with read access on the folder.
	 * Only those users and groups will be added as reviewers
	 */
	$docAccess = $folder->getReadAccessList($settings->_enableAdminRevApp, $settings->_enableOwnerRevApp);
	$res=$user->getMandatoryReviewers();
	$reviewers = array('i'=>[], 'g'=>[]);
	foreach ($res as $r){
		if ($r['reviewerUserID']!=0){
			foreach ($docAccess["users"] as $usr)
				if ($usr->getID()==$r['reviewerUserID']){
					$reviewers["i"][] = $r['reviewerUserID'];
					break;
				}
		} elseif ($r['reviewerGroupID']!=0){
			foreach ($docAccess["groups"] as $grp)
				if ($grp->getID()==$r['reviewerGroupID']){
					$reviewers["g"][] = $r['reviewerGroupID'];
					break;
				}
		}
	}
	return $reviewers;
} /* }}} */

/**
 * Returns the mandatory approvers
 *
 * This function checks if the approvers have at least read access
 * on the folder containing the document.
 *
 * @param $folder folder where document is located
 * @param $user user creating the new version or document
 * @return array
 */
function getMandatoryApprovers($folder, $user) { /* {{{ */
	global $settings;

	/* Get a list of all users and groups with read access on the folder.
	 * Only those users and groups will be added as approvers
	 */
	$docAccess = $folder->getReadAccessList($settings->_enableAdminRevApp, $settings->_enableOwnerRevApp);
	$res=$user->getMandatoryApprovers();
	$approvers = array('i'=>[], 'g'=>[]);
	foreach ($res as $r){

		if ($r['approverUserID']!=0){
			foreach ($docAccess["users"] as $usr)
				if ($usr->getID()==$r['approverUserID']){
					$approvers["i"][] = $r['approverUserID'];
					break;
				}
		}
		else if ($r['approverGroupID']!=0){
			foreach ($docAccess["groups"] as $grp)
				if ($grp->getID()==$r['approverGroupID']){
					$approvers["g"][] = $r['approverGroupID'];
					break;
				}
		}
	}
	return $approvers;
} /* }}} */

/**
 * Class for creating encrypted api keys
 *
 * <code>
 * <?php
 * $CSRF = new SeedDMS_CSRF($settings->_encryptionKey);
 * $kkk = $CSRF->create_api_key();
 * echo $kkk;
 * echo $CSRF->check_api_key($kkk) ? 'valid' : 'invalid';
 * ?>
 * </code>
 */
class SeedDMS_CSRF { /* {{{ */

	protected $secret;

	public function __construct($secret) { /* {{{ */
		$this->secret = $secret;
	} /* }}} */

	public function create_api_key() { /* {{{ */
		return base64_encode($this->encrypt(time().'|'.$_SERVER['REMOTE_ADDR'])); // !change if you dont want IP check
	} /* }}} */

	public function check_api_key($key, $timeout = 5) { /* {{{ */
		if (empty($key)) exit('Invalid Key');

		$keys = explode('|', $this->decrypt(base64_decode($key)));

		return (
			isset($key, $keys[0], $keys[1]) && 
			$keys[0] >= (time() - $timeout) && 
			$keys[1] == $_SERVER['REMOTE_ADDR'] // !change if you dont want IP check
		);
	} /* }}} */

	public function encrypt($string, $key = 'PrivateKey', $method = 'AES-256-CBC') { /* {{{ */
		// hash
		$key = hash('sha256', $key);
		// create iv - encrypt method AES-256-CBC expects 16 bytes
		$iv = substr(hash('sha256', $this->secret), 0, 16);
		// encrypt
		$output = openssl_encrypt($string, $method, $key, 0, $iv);
		// encode
		return base64_encode($output);
	} /* }}} */

	public function decrypt($string, $key = 'PrivateKey', $method = 'AES-256-CBC') { /* {{{ */
		// hash
		$key = hash('sha256', $key);
		// create iv - encrypt method AES-256-CBC expects 16 bytes
		$iv = substr(hash('sha256', $this->secret), 0, 16);
		// decode
		$string = base64_decode($string);
		// decrypt
		return openssl_decrypt($string, $method, $key, 0, $iv);
	} /* }}} */
} /* }}} */

/**
 * Class to represent a jwt token
 *
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  2016 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_JwtToken { /* {{{ */
	protected $jwtsecret;

	public function __construct($jwtsecret = '') { /* {{{ */
		$this->jwtsecret = $jwtsecret;
	} /* }}} */

	public function jwtEncode($payload) { /* {{{ */
		$header = [
				"alg" => "HS256",
				"typ" => "JWT"
		];
		$encHeader = self::base64UrlEncode(json_encode($header));
		$encPayload = self::base64UrlEncode(json_encode($payload));
		$hash = self::base64UrlEncode(self::calculateHash($encHeader, $encPayload));

		return "$encHeader.$encPayload.$hash";
	} /* }}} */

	public function jwtDecode($token) { /* {{{ */
		if (!$this->jwtsecret) return "";

		$split = explode(".", $token);
		if (count($split) != 3) return "";

		$hash = self::base64UrlEncode(self::calculateHash($split[0], $split[1]));

		if (strcmp($hash, $split[2]) != 0) return "";
		return self::base64UrlDecode($split[1]);
	} /* }}} */

	protected function calculateHash($encHeader, $encPayload) { /* {{{ */
		return hash_hmac("sha256", "$encHeader.$encPayload", $this->jwtsecret, true);
	} /* }}} */

	protected function base64UrlEncode($str) { /* {{{ */
		return str_replace("/", "_", str_replace("+", "-", trim(base64_encode($str), "=")));
	} /* }}} */

	protected function base64UrlDecode($payload) { /* {{{ */
		$b64 = str_replace("_", "/", str_replace("-", "+", $payload));
		switch (strlen($b64) % 4) {
			case 2:
				$b64 = $b64 . "=="; break;
			case 3:
				$b64 = $b64 . "="; break;
		}
		return base64_decode($b64);
	} /* }}} */
} /* }}} */

class SeedDMS_FolderTree { /* {{{ */

	public function __construct($folder, $callback) { /* {{{ */
		$iter = new \SeedDMS\RecursiveFolderIterator($folder);
		$iter2 = new RecursiveIteratorIterator($iter, RecursiveIteratorIterator::SELF_FIRST);
		foreach($iter2 as $ff) {
			call_user_func($callback, $ff, $iter2->getDepth());
//      echo $ff->getID().': '.$ff->getFolderPathPlain().'-'.$ff->getName()."<br />";
		}
	} /* }}} */

} /* }}} */

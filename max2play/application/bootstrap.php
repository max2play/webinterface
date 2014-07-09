<?php 
define('APPLICATION_PATH', dirname(__FILE__));

/**
 * translate init for all output strings
 * translate strings should be defined in /locale/de_DE/LC_MESSAGES/max2play.mo
 * TODO: additional translate strings are parsed from /locale/de_DE/LC_MESSAGES/custom.mo
 */

$directory = APPLICATION_PATH.'/../locale';

$domain = 'max2play';
$lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

if(file_exists($directory. '/'.$lang.'/LC_MESSAGES/'.$domain.'.mo')){
	if($lang == 'de')
		$locale = 'de_DE.utf8';
	else
		$locale = 'en_GB.utf8';
}else{
	$locale = 'en_GB.utf8';
}

setlocale( LC_MESSAGES, $locale);
bindtextdomain($domain, $directory);
textdomain($domain);
bind_textdomain_codeset($domain, 'UTF-8');


function setTimezone($default) {
	$timezone = "";
	 
	// On many systems (Mac, for instance) "/etc/localtime" is a symlink
	// to the file with the timezone info
	if (is_link("/etc/localtime")) {
		 
		// If it is, that file's name is actually the "Olsen" format timezone
		$filename = readlink("/etc/localtime");
		 
		$pos = strpos($filename, "zoneinfo");
		if ($pos) {
			// When it is, it's in the "/usr/share/zoneinfo/" folder
			$timezone = substr($filename, $pos + strlen("zoneinfo/"));
		} else {
			// If not, bail
			$timezone = $default;
		}
	}
	else {
		// On other systems, like Ubuntu, there's file with the Olsen time
		// right inside it.
		$timezone = file_get_contents("/etc/timezone");
		if (!strlen($timezone)) {
			$timezone = $default;
		}
	}
	date_default_timezone_set(str_replace("\n",'',$timezone));
}

setTimezone('UTC');

//Translate function for different PO-Files - Register in Service Class!
function _t($string, $domain = ''){
	if($domain == '' && defined('newLocale'))
		$domain = newLocale;
	return dgettext($domain, $string);
}

//Load Main-Service-Class that is implemented by most services
include_once('controller/Service.php');

//Parse Plugin-Folder for additional Services / Modules to load
$plugins = $service->getActivePlugins();
// Header, Klassenpfad für index.php, usw. anpassen auf Basis der Pluginstruktur


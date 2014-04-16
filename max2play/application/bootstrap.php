<?php 
/**
 * translate init for all output strings
 * translate strings should be defined in /locale/de_DE/LC_MESSAGES/max2play.mo
 * TODO: additional translate strings are parsed from /locale/de_DE/LC_MESSAGES/custom.mo
 */
$directory = dirname(__FILE__).'/../locale';

$domain = 'max2play';
$lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

if(file_exists($directory. '/'.$lang.'/LC_MESSAGES/'.$domain.'.mo')){
	if($lang == 'de')
		$locale = 'de_DE.utf8';	
}else{
	$locale = 'en_GB.utf8';
}

setlocale( LC_MESSAGES, $locale);
bindtextdomain($domain, $directory);
textdomain($domain);
bind_textdomain_codeset($domain, 'UTF-8');

//Load Main-Service-Class that is implemented by most services
include_once('controller/Service.php');



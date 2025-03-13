<?php
// global filter for all html requests
$requestfilter = 	array (
	array ( 'pattern' => '/(&#*\w+)[\x00-\x20]+;/u', 'replace' => '$1;'),
	array ( 'pattern' => '/(&#x*[0-9A-F]+);*/iu', 'replace' => '$1;'),
	// Remove any attribute starting with "on" or xmlns
	array ( 'pattern' => '#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', 'replace' => '$1>'),
	// Remove javascript: and vbscript: protocols
	array ( 'pattern' => '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', 'replace' => '$1=$2nojavascript...'),
	array ( 'pattern' => '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', 'replace' => '$1=$2novbscript...'),
	array ( 'pattern' => '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', 'replace' => '$1=$2nomozbinding...'),
	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	array ( 'pattern' => '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', 'replace' => '$1>'),
	array ( 'pattern' => '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', 'replace' => '$1>'),
	array ( 'pattern' => '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', 'replace' => '$1>'),
	// Remove namespaced elements (we do not need them)
	array ( 'pattern' => '#</*\w+:\w[^>]*+>#i', 'replace' => ''),
	// Remove really unwanted tags
	array ( 'pattern' => '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml|a)[^>]*+>#i', 'replace' => '')
);

<?php
// --------------------------------------------------------
//  This is a part of the Sparx Systems Pro Cloud Server.
//  Copyright (C) Sparx Systems Pty Ltd
//  All rights reserved.
//
//  This source code can be used only under terms and 
//  conditions of the accompanying license agreement.
// --------------------------------------------------------
require_once __DIR__ . '/htmlpurifier/HTMLPurifier.standalone.php';
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.DefinitionID', 'WebEA.html rich_text');
$config->set('HTML.DefinitionRev', 1);
if ($def = $config->maybeGetRawHTMLDefinition()) {
	$def->addAttribute('a', 'object', 'Text');
	$def->addAttribute('a', 'id', 'Text');
	$def->addAttribute('a', 'hyper', 'Text');
	$def->addAttribute('a', 'object-name', 'Text');
	$def->addAttribute('a', 'image-url', 'Text');
	$def->addAttribute('a', 'target', 'Text');
	$def->addAttribute('a', 'rel', 'Text');
	$def->addAttribute('img', 'src', 'Text');
	$def->addAttribute('img', 'alt', 'Text');
}
$purifier = new HTMLPurifier($config);
function _hPurify($htmlString)
{
	global $purifier;
	$htmlString = $purifier->purify($htmlString);
	return $htmlString;
}

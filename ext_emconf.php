<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "extension_uploader".
 *
 * Auto generated 16-03-2013 13:24
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Extension Uploader',
	'description' => 'Upload your extensions into the TER',
	'category' => 'module',
	'author' => 'Georg Großberger',
	'author_email' => 'contact@grossberger-ge.org',
	'author_company' => '',
	'shy' => '',
	'priority' => '',
	'dependencies' => 'extbase,fluid,extensionmanager',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '1.0.5',
	'constraints' => 
	array (
		'depends' => 
		array (
			'extbase' => '6.0-6.9.99',
			'fluid' => '6.0-6.9.99',
			'typo3' => '6.0-6.9.99',
			'extensionmanager' => '6.0.0-6.9.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
	'suggests' => 
	array (
	),
	'conflicts' => '',
);

?>
<?php

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
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '1.0.10',
	'constraints' =>
	array (
		'depends' =>
		array (
			'extbase' => '6.0.0-6.2.99',
			'fluid' => '6.0.0-6.2.99',
			'typo3' => '6.0.0-6.2.99',
			'extensionmanager' => '6.0.0-6.2.99',
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

<?php
/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

if (TYPO3_MODE === 'BE') {

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'T3x.' . $_EXTKEY,
		'tools',
		'extensionuploader',
		'',
		array(
			'Uploader' => 'list, settings, upload',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf'
		)
	);
}

?>

/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

jQuery(function($) {

	$('#typo3-extension-list').dataTable({
			"bPaginate": false,
			"bJQueryUI":true,
			"bLengthChange":false,
			'iDisplayLength':15,
			"bStateSave":true
		});
});

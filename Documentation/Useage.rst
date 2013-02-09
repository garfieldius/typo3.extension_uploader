=======================
Useage of the extension
=======================

How to upload an extension into the TER

Target group: **Developers**

Select an extension
===================

Go into the module **Extension Builder** which is found inside the **Admin Tools** section.

There click the upload icon on the left in the row that displays the extension you want to upload.

.. figure:: Images/List.png
		:alt: Extensions list

		Click upload on the left of the extension to upload

Set release information
=======================

In the settings form you can set several settings. The default settings are alright, only the fields for **Username** and **Password** are mandatory since this credentials are needed to authenticate at the TER.

Once you set everything, click the **Upload to TER** button to peform the upload

The settings in detail:

.. ..................................
.. container:: table-row

	Field
		Release state

	Description
		The state or stability of the release

	Options
		Only the displayed Options are available:
		* Stable - for mature extension
		* Beta - not sure if yet mature, but quite
		* Alpha - still in development
		* Experimental - not meant for production, a prove of concept or something similar
		* Test - Just a test
		* Obsolete - Last release of an extension, indicator that it is not maintained anymore

	Mandatory
		Yes - but set by default

	Default
		The last release state, alpha for new extensions

.. ..................................
.. container:: table-row

	Field
		Release

	Description
		The type of release, which will used to determine the version number of the release

	Options
		* Bugfix - For bugfixes, security fixes or setting a new extension state
		* Minor - Indicates new features or changes to existing ones
		* Major - Important, breaking changes. Maybe even a whole rewrite
		* Custom - Just set a custom version number

	Mandatory
		Yes - but set by default

	Default
		Bugfix

.. ..................................
.. container:: table-row

	Field
		Upload comment

	Description
		A short upload comment, like release notes or other important information.

	Options
		Any text

	Mandatory
		No

	Default
		Empty

.. ..................................
.. container:: table-row

	Field
		Username / Password

	Description
		Your username and password for typo3.org. You must have registered the extension key with this account for uploading it.

	Options
		Text

	Mandatory
		Yes

	Default
		Empty


.. figure:: Images/Settings.png
		:alt: Extensions list

		Set username and password and hit *Upload* to release your extension

/*                                                                     *
 * This file is brought to you by Georg Großberger                     *
 * (c) 2013 by Georg Großberger <contact@grossberger-ge.org>           *
 *                                                                     *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License, either version 3       *
 * of the License, or (at your option) any later version.              *
 *                                                                     */

jQuery(function($) {

	var
		currentVersion = $.map(
			$("#version").attr("disabled", true).val().split("."),
			function(num) {
				return parseInt(num);
			}),
		strPadLeft = function(str, size) {
			str = "" + str;

			while (str.length < size) {
				str = "0" + str;
			}
			return str;
		},
		versionToInteger = function(version) {
			return parseInt(
				version[0] +
				strPadLeft(version[1], 3) +
				strPadLeft(version[2], 3)
			);
		};

	$(".release input[type=radio]").click(function() {
		var
			disabled = true,
			version = currentVersion.slice(0);

		switch (this.value) {
			case "major":
				version[0]++;
				version[1] = 0;
				version[2] = 0;
				break;
			case "minor":
				version[1]++;
				version[2] = 0;
				break;
			case "bugfix":
				version[2]++;
				break;
			case "custom":
				disabled = false;
				break;
		}
		$("#version").attr("disabled", disabled).val(version.join("."));
	});

	$("form#extensionSettings").submit(function(e) {
		var
			form = $(this),
			error = false,
			version = form.find("#version").val(),
			oldVersion = versionToInteger(currentVersion);

		$(".js-error").hide();

		if (!/^([0-9]{1,4})\.([0-9]{1,4})\.([0-9]{1,4})$/.test(version)) {
			error = true;
			$(".error-version").show();
		} else if (oldVersion >= versionToInteger(version.split("."))) {
			error = true;
			$(".error-release").show();
		}

		if (!/^[0-9a-z\-_]{3,}$/.test(form.find('#username').val())) {
			error = true;
			$(".error-username").show();
		}

		if ($.trim("" + form.find('#password').val()).length < 8) {
			error = true;
			$(".error-password").show();
		}

		if (error) {
			e.preventDefault();
			return false;
		}

		form.hide();
		$(".uploadForm").show();
		return true;
	});

	$(".release :checked").click();
});

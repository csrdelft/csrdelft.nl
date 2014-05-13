/* DOKUWIKI:include jquery.form.js */

jQuery(document).ready(function() {
	
	var COOKIE_DESCRIPTION_NAME = 'DW_Admin_ConfManager_showDescription';
	var COOKIE_DEFAULTS_NAME = 'DW_Admin_ConfManager_showDefaults';
	
	var ICON_BASE_URL=DOKU_BASE+'lib/plugins/confmanager/icons/';
	var collapse_icon = ICON_BASE_URL+'collapse.png';
	var expand_icon = ICON_BASE_URL+'expand.png';

	var readCookie = function(cookieKey) {
		var ARRcookies=document.cookie.split(";");
		for (var i=0;i<ARRcookies.length;i++) {
			var key = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			var value = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			key = key.replace(/^\s+|\s+$/g,"");
			if (key == cookieKey) {
				return unescape(value);
			}
		}
	};
	
	var setCookie = function(key, value) {
		var expirationDate = new Date();
		expirationDate.setDate(expirationDate.getDate()+365);
		document.cookie = escape(key) + '=' + escape(value) + '; expires=' + expirationDate.toUTCString();
	};
	
	/*
	 * defaultValue is a boolean value the function should return if the cookie is not set (undefined).
	 * returns false if cookie is undefined and defaultValue not specified
	 */
	var getBooleanFromCookie = function(key, defaultValue) {
		var cookie = readCookie(key);
		if(cookie == null || cookie == undefined) {
			return defaultValue == undefined ? false : defaultValue;
		}
		return cookie == 'true';
	};
	
	var setDescriptionVisible = function(show) {
		if(show) {
			jQuery('#description').show();
			jQuery('#description_toggle_button').attr('src', collapse_icon);
		} else {
			jQuery('#description').hide();
			jQuery('#description_toggle_button').attr('src', expand_icon);
		}
	};
	
	var setDefaultsVisible = function(show) {
		if(show) {
			jQuery('.defaults').show();
			jQuery('#defaults_toggle_button').attr('src', collapse_icon);
		} else {
			jQuery('.defaults').hide();
			jQuery('#defaults_toggle_button').attr('src', expand_icon);
		}
	};
	
	var showDescription = getBooleanFromCookie(COOKIE_DESCRIPTION_NAME, true);
	var showDefaults = getBooleanFromCookie(COOKIE_DEFAULTS_NAME, true);
	
	setDescriptionVisible(showDescription);
	setDefaultsVisible(showDefaults);
	
	jQuery('#toggleDescription').click(function() {
		showDescription = !showDescription;
		setDescriptionVisible(showDescription);
		setCookie(COOKIE_DESCRIPTION_NAME, showDescription);
		return false;
	});
	
	jQuery('#toggleDefaults').click(function() {
		showDefaults = !showDefaults;
		setDefaultsVisible(showDefaults);
		setCookie(COOKIE_DEFAULTS_NAME, showDefaults);
		return false;
	});
});

jQuery(document).ready(function() {
	
	var isInputValid = function() {
		var result = true;
		jQuery('.newItem').each(function(){
			var inputString = jQuery(this).val();
			if(inputString == null || inputString == '') {
				result = false;
			}
		});
		return result;
	};
	
	var submitForm = function(id) {
		document.forms[id].submit();
	};
	
	jQuery('.deleteButton').click(function(nr) {
		jQuery(this).parent().parent().remove();
		jQuery('.newItem').each(function(){
			jQuery(this).val('');
		});
		submitForm('configForm');
	});
	
	jQuery('#confmanager__config__files').change(function(){
		submitForm('select_config_form');
	});
	
	jQuery('.submitOnTab').keydown(function(event){
		if(event.which != 9) {
			return true;
		}
		if(!isInputValid()) {
			return true;
		}
		submitForm('configForm');
	});
});

jQuery(document).ready(function(){
	jQuery('.newItem').first().focus();
});

jQuery(document).ready(function(){
	
	var popupVisible = false;
	
	var getEntryKey = function(element) {
		var parent = jQuery(element).parent().parent().children().first();
		var input = jQuery(parent).children('input').first();
		var value = jQuery(input).attr('value');
		return value;
	};
	
	var unloadPopup = function() {
		jQuery('.popup_mask').hide();
		jQuery('.popup').hide();
		jQuery('#keyParam').removeAttr('value');
		jQuery('#configIdParam').removeAttr('value');
		popupVisible = false;
	};
	
	var validate = function() {
		var file = jQuery('#file_upload_input').val();
		if(file == '' || file == null || file == undefined) {
			return false;
		}
		jQuery('#popup_select_file').hide();
		jQuery('#popup_show_progress').show();
		jQuery('.popup').css('cursor', 'wait');
		return true;
	};
	
	var submitOk = function() {
		jQuery('#popup_show_progress').hide();
		jQuery('#popup_success').show();
		jQuery('.popup').css('cursor', 'default');
	};
	
	var onError = function() {
		jQuery('#popup_show_progress').hide();
		jQuery('#popup_error').show();
		jQuery('.popup').css('cursor', 'default');
	};
	
	var showPopup = function() {
		var width = jQuery('.popup').width();
		var height = jQuery('.popup').height();
		jQuery('.popup').css('left', jQuery(window).width() / 2 - width / 2);
		jQuery('.popup').css('top', jQuery(window).height() / 2 - height / 2);
		jQuery('.popup').show();
		popupVisible = true;
	};
	
	var showPopupMask = function() {
		var width = jQuery(window).width();
		jQuery('.popup_mask').css('width', jQuery(window).width());
		jQuery('.popup_mask').css('height', jQuery(window).height());
		jQuery('.popup_mask').show();
	};
	
	var options = {
		beforeSubmit : validate,
		success : submitOk,
		error : onError
	};
	jQuery('#fileuploadform').ajaxForm(options);
	
	jQuery('.upload_image_button').click(function(){
		var key = getEntryKey(this);
		jQuery('#keyParam').val(key);
		jQuery('#configIdParam').val(JSINFO.configId);
		showPopup();
		showPopupMask();
	});
	
	var delete_image_success = function() {
		document.forms['configForm'].submit();
	};
	
	var delete_image_failed = function(jqXHR, textStatus, errorThrown) {
		alert(errorThrown);
	};
	
	jQuery('.delete_image_button').click(function() {
		jQuery.ajax({
			url : DOKU_BASE + 'lib/exe/ajax.php',
			type : 'POST',
			data : {
				call : 'confmanager_deleteIcon',
				configId : JSINFO.configId,
				key : getEntryKey(this)
			},
			success : delete_image_success,
			error : delete_image_failed
		});
	});
	
	jQuery('#popup_cancel').click(function() {
		unloadPopup();
		return false;
	});
	
	jQuery(window).resize(function() {
		if(!popupVisible) {
			return true;
		}
		showPopup();
		showPopupMask();
	});
	
	jQuery('.continue').attr('href', window.location);
});

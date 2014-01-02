/**
 * menubeheer.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery & dragobject.js
 */
$(document).ready(function() {
	menubeheer_knop_init();
	menubeheer_form_init();
});
function menubeheer_knop_init() {
	$('a.knop').each(function() {
		if ($(this).hasClass('post')) {
			$(this).removeClass('post');
			$(this).click(menubeheer_knop_post);
		}
	});
}
function menubeheer_knop_post(event) {
	event.preventDefault();
	if ($(this).hasClass('confirm') && !confirm($(this).attr('title') +'.\n\nWeet u het zeker?')) {
		return false;
	}
	return false;
}
function menubeheer_form_init() {
	$('.menu-item form').each(function() {
		$(this).submit(function() {
			menubeheer_submit($(this));
		}); // enter
		$(this).keyup(function(e) {
			if (e.keyCode === 27) { // esc
				menubeheer_cancel($(this).closest('div.inline-edit').attr('id'));
			}
		});
	});
}
function menubeheer_submit(form) {
	var url = $(form).attr('action');
	$(form).parent().html('<img title="'+ url +'" src="http://plaetjes.csrdelft.nl/layout/loading-arrows.gif" />');
	$.ajax({
		type: 'POST',
		cache: false,
		url: url,
		data: $(form).serialize(),
		success: function(response) {
			menubeheer_update($.trim(response));
		},
		error: function(jqXHR, textStatus, errorThrown) {
			if (errorThrown === '') {
				errorThrown = 'Nog bezig met laden!';
			}
			$('img[title="'+ this.url +'"]').each(function() {
				this.src = 'http://plaetjes.csrdelft.nl/famfamfam/cancel.png';
				this.title = errorThrown;
			});
			alert(errorThrown);
		}
	});
}
function menubeheer_update(htmlString) {
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error'); //DEBUG
		document.write(htmlString);
	}
	else if (htmlString.length > 0) {
		var html = $.parseHTML(htmlString);
		$(html).each(function() {
			var id = $(this).attr('id');
			var ding = $('#' + id);
			if (ding.length === 1) {
				if ($(this).hasClass('remove')) {
					ding.remove();
				}
				else {
					ding.replaceWith($(this));
					menubeheer_knop_init();
					menubeheer_form_init();
				}
			}
			else {
				var pid = $(this).attr('parentid');
				ding.appendTo('#children-'+ pid);
			}
		});
	}
}
function menubeheer_clone(id) {
	var clone = $('#inline-newchild-'+ id).clone(true);
	clone.attr('id', '');
	clone.attr('parentid', id);
	clone.prependTo($('#children-'+ id));
	clone.slideDown();
	
}
function menubeheer_toggle(id) {
	$('.inline-edit-'+ id).toggle();
}
function page_reload() {
	location.reload();
}
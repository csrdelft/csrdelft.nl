/* Created by jankoatwarpspeed.com */

(function ($) {
	$.fn.formSteps = function (options) {
		options = $.extend({
			submitButton: ''
		}, options);

		var element = this;

		var formSteps = $(element).find('fieldset');
		var count = formSteps.size();
		var submmitButtonName = '#' + options.submitButton;
		$(submmitButtonName).hide();

		$(element).before('<ul id="formSteps"></ul>');

		formSteps.each(function (i) {
			$(this).wrap('<div id="formStep' + i + '" class="formStep"></div>');
			$(this).append('<p id="formStep' + i + 'commands"></p>');

			var name = $(this).find('legend').html();
			$('#formSteps').append('<li id="formHeadStep' + i + '">' + name + '</li>');

			if (i === 0) {
				createNextButton(i);
				selectStep(i);
			}
			else if (i + 1 === count) {
				$('#formStep' + i).hide();
				createPrevButton(i);
			}
			else {
				$('#formStep' + i).hide();
				createPrevButton(i);
				createNextButton(i);
			}
		});

		function createPrevButton(i) {
			var prevName = $('#formHeadStep' + (i - 1)).text();
			var stepId = 'formStep' + i;
			$('#' + stepId + 'commands').append('<a href="#" id="' + stepId + 'Prev" class="btn prev">' + prevName + '</a>');

			$('#' + stepId + 'Prev').bind('click', function (e) {
				$('#' + stepId).hide();
				$('#formStep' + (i - 1)).show();
				$(submmitButtonName).hide();
				selectStep(i - 1);
			});
		}

		function createNextButton(i) {
			var nextName = $('#formHeadStep' + (i + 1)).text();
			var stepId = 'formStep' + i;
			$('#' + stepId + 'commands').append('<a href="#" id="' + stepId + 'Next" class="btn next">' + nextName + '</a>');

			$('#' + stepId + 'Next').bind('click', function (e) {
				$('#' + stepId).hide();
				$('#formStep' + (i + 1)).show();
				if (i + 2 === count) {
					$(submmitButtonName).show();
				}
				selectStep(i + 1);
			});
		}

		function selectStep(i) {
			$('#formSteps li').removeClass('current');
			$('#formHeadStep' + i).addClass('current');
		}

	}
})(jQuery); 
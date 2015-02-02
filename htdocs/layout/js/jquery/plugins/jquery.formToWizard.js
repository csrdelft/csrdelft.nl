/* Created by jankoatwarpspeed.com */

(function ($) {
	$.fn.formToWizard = function (options) {
		options = $.extend({
			submitButton: ""
		}, options);

		var element = this;

		var wizardSteps = $(element).find("fieldset");
		var count = wizardSteps.size();
		var submmitButtonName = "#" + options.submitButton;
		$(submmitButtonName).hide();

		// 2
		$(element).before("<ul id='wizardSteps'></ul>");

		wizardSteps.each(function (i) {
			$(this).wrap("<div id='wizardStep" + i + "'></div>");
			$(this).append("<p id='wizardStep" + i + "commands'></p>");

			// 2
			var name = $(this).find("legend").html();
			$("#wizardSteps").append("<li id='wizardStepDesc" + i + "'>Stap " + (i + 1) + "<span>" + name + "</span></li>");

			if (i == 0) {
				createNextButton(i);
				selectStep(i);
			}
			else if (i == count - 1) {
				$("#wizardStep" + i).hide();
				createPrevButton(i);
			}
			else {
				$("#wizardStep" + i).hide();
				createPrevButton(i);
				createNextButton(i);
			}
		});

		function createPrevButton(i) {
			var wizardStepName = "wizardStep" + i;
			$("#" + wizardStepName + "commands").append("<a href='#' id='" + wizardStepName + "Prev' class='prev'>< Terug</a>");

			$("#" + wizardStepName + "Prev").bind("click", function (e) {
				$("#" + wizardStepName).hide();
				$("#wizardStep" + (i - 1)).show();
				$(submmitButtonName).hide();
				selectStep(i - 1);
			});
		}

		function createNextButton(i) {
			var wizardStepName = "wizardStep" + i;
			$("#" + wizardStepName + "commands").append("<a href='#' id='" + wizardStepName + "Next' class='next'>Volgende ></a>");

			$("#" + wizardStepName + "Next").bind("click", function (e) {
				$("#" + wizardStepName).hide();
				$("#wizardStep" + (i + 1)).show();
				if (i + 2 == count)
					$(submmitButtonName).show();
				selectStep(i + 1);
			});
		}

		function selectStep(i) {
			$("#wizardSteps li").removeClass("current");
			$("#wizardStepDesc" + i).addClass("current");
		}

	}
})(jQuery); 
import $ from "jquery";

export function toggleForumConceptBtn(enable: boolean) {
    const $concept = $('#forumConcept');
    if (typeof enable === 'undefined') {
        $concept.attr('disabled', String(!Boolean($concept.prop('disabled'))));
    } else {
        $concept.attr('disabled', String(!enable));
    }
}

export function saveConceptForumBericht() {
	toggleForumConceptBtn(false);
	const $concept = $('#forumConcept');
	const $textarea = $('#forumBericht');
	const $titel = $('#nieuweTitel');
	if ($textarea.val() !== $textarea.attr('origvalue')) {
		$.post($concept.attr('data-url')!, {
			forumBericht: $textarea.val(),
			titel: ($titel.length === 1 ? $titel.val() : ''),
		}).done(() => {
			$textarea.attr('origvalue', String($textarea.val()));
		}).fail((error) => {
			alert(error);
		});
	}
	setTimeout(toggleForumConceptBtn, 3000);
}

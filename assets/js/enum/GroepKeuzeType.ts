/**
 * NIET AANPASSEN.
 * Gegenereerde code voor CsrDelft\entity\groepen\enum\GroepKeuzeType.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
	CHECKBOX: 'checkbox_1',
	DROPDOWN: 'dropdown_1',
	RADIOS: 'radios_1',
	TEXT: 'text_1',
};

export function getGroepKeuzeTypeDescription(option: string): string {
	return {
		checkbox_1: 'Een checkbox',
		dropdown_1: 'Een dropdown',
		radios_1: 'Radiobuttons',
		text_1: 'Een textbox',
	}[option];
}

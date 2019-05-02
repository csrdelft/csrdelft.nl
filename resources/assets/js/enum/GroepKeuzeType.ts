/**
 * NIET AANPASSEN.
 * Gegenereerde code voor CsrDelft\model\entity\groepen\GroepKeuzeType.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
	CHECKBOX: 'checkbox_1',
	RADIOS: 'radios_1',
	TEXT: 'text_1',
};

export function getGroepKeuzeTypeDescription(option: string) {
	return {
		checkbox_1: 'Een checkbox',
		radios_1: 'Radiobuttons',
		text_1: 'Een textbox',
	}[option];
}

/**
 * NIET AANPASSEN.
 * Gegenereerde code voor CsrDelft\model\entity\security\AccessAction.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
	Bekijken: 'r',
	Aanmelden: 'j',
	Bewerken: 'e',
	Afmelden: 'l',
	Opvolging: 's',
	Aanmaken: 'c',
	Wijzigen: 'u',
	Verwijderen: 'd',
	Beheren: 'm',
	Rechten: 'p',
};

export function getAccessActionDescription(option: string) {
	return {
		r: 'Bekijken',
		j: 'Aanmelden',
		e: 'Aanmelding bewerken',
		l: 'Afmelden',
		s: 'Opvolging aanpassen',
		c: 'Nieuwe aanmaken',
		u: 'Wijzigen',
		d: 'Verwijderen',
		m: 'Beheren',
		p: 'Rechten instellen',
	}[option];
}

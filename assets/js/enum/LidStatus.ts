/**
 * NIET AANPASSEN.
 * Gegenereerde code voor CsrDelft\model\entity\LidStatus.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
	Noviet: 'S_NOVIET',
	Lid: 'S_LID',
	Gastlid: 'S_GASTLID',
	Oudlid: 'S_OUDLID',
	Erelid: 'S_ERELID',
	Overleden: 'S_OVERLEDEN',
	Exlid: 'S_EXLID',
	Nobody: 'S_NOBODY',
	Commissie: 'S_CIE',
	Kringel: 'S_KRINGEL',
};

export function getLidStatusDescription(option: string): string {
	return {
		S_NOVIET: 'Noviet',
		S_LID: 'Lid',
		S_GASTLID: 'Gastlid',
		S_OUDLID: 'Oudlid',
		S_ERELID: 'Erelid',
		S_OVERLEDEN: 'Overleden',
		S_EXLID: 'Ex-lid',
		S_NOBODY: 'Nobody',
		S_CIE: 'Commissie (LDAP)',
		S_KRINGEL: 'Kringel',
	}[option];
}

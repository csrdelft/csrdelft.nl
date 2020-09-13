/**
 * NIET AANPASSEN.
 * Gegenereerde code voor CsrDelft\entity\Geslacht.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
	Man: 'm',
	Vrouw: 'v',
};

export function getGeslachtDescription(option: string): string {
	return {
		m: 'man',
		v: 'vrouw',
	}[option];
}

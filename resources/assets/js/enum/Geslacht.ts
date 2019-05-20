/**
 * NIET AANPASSEN.
 * Gegenereerde code voor CsrDelft\model\entity\Geslacht.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
	Man: 'm',
	Vrouw: 'v',
};

export function getGeslachtDescription(option: string) {
	return {
		m: 'man',
		v: 'vrouw',
	}[option];
}

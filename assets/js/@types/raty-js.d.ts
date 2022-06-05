interface JQuery {
	raty: Raty;
}

interface Raty {
	(method: 'score', score: number): void;
	(settings: RatySettings): void;
}

interface RatySettings {
	cancel?: boolean; // Creates a cancel button to cancel the rating.
	cancelClass?: string; // Name of cancel's class.
	cancelHint?: string; // The cancel's button hint.
	cancelOff?: string; // Icon used on active cancel.
	cancelOn?: string; // Icon used inactive cancel.
	cancelPlace?: string; // Cancel's button position.
	click?: (score: number, event: any) => void; // Callback executed on rating click.
	half?: boolean; // Enables half star selection.
	halfShow?: boolean; // Enables half star display.
	hints?: string[]; // Hints used on each star.
	iconRange?: unknown; // Object list with position and icon on and off to do a mixed icons.
	mouseout?: unknown; // Callback executed on mouseout.
	mouseover?: unknown; // Callback executed on mouseover.
	noRatedMsg?: string; // Hint for no rated elements when it's readOnly.
	number?: number; // Number of stars that will be presented.
	numberMax?: number; // Max of star the option number can creates.
	path?: string; // A global locate where the icon will be looked.
	precision?: boolean; // Enables the selection of a precision score.
	readOnly?: boolean; // Turns the rating read-only.
	round?: { down: number; full: number; up: number }; // Included values attributes to do the score round math.
	score?: unknown; // Initial rating.
	scoreName?: string; // Name of the hidden field that holds the score value.
	single?: boolean; // Enables just a single star selection.
	space?: boolean; // Puts space between the icons.
	starHalf?: string; // The name of the half star image.
	starOff?: string; // Name of the star image off.
	starOn?: string; // Name of the star image on.
	target?: unknown; // Element selector where the score will be displayed.
	targetForma?: string; // Template to interpolate the score in.
	targetKeep?: boolean; // If the last rating value will be keeped after mouseout.
	targetScore?: unknown; // Element selector where the score will be filled, instead of creating a new hidden field (scoreName option).
	targetText?: string; // Default text setted on target.
	targetType?: string; // Option to choose if target will receive hint o 'score' type.
	starType?: string; // Element used to represent a star.
}

declare module 'hoverintent' {
	export default function (el: Element, handlerIn: () => void, handlerOut: () => void): HoverIntentListener;

	interface HoverIntentListener {
		remove: () => void;
		options: (options: HoverIntentOptions) => HoverIntentListener;
	}

	interface HoverIntentOptions {
		sensitivity?: number;
		interval?: number;
		timeout?: number;
		handleFocus?: boolean;
	}
}

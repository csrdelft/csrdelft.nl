declare module 'parallax-js' {
	export default class Parallax {
		constructor(el: HTMLElement, options: ParallaxOptions);
	}

	export interface ParallaxOptions {
		relativeInput?: boolean;
		clipRelativeInput?: boolean;
		inputElement?: HTMLElement;
		hoverOnly?: boolean;
		calibrationThreshold?: number;
		calibrationDelay?: number;
		supportDelay?: number;
		calibrateX?: boolean;
		calibrateY?: boolean;
		invertX?: boolean;
		invertY?: boolean;
		limitX?: boolean;
		limitY?: boolean;
		scalarX?: number;
		scalarY?: number;
		frictionX?: number;
		frictionY?: number;
		originX?: number;
		originY?: number;
		pointerEvents?: boolean;
		precision?: number;
		onReady?: any;
		selector?: string;
	}
}

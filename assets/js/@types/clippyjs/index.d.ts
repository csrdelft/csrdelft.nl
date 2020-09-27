declare module 'clippyjs' {
	export default class Clippy {
		public static load(name: AgentName, cb: (agent: Agent) => void): void;
	}

	type AgentName = 'Bonzi' | 'Clippy' | 'F1' | 'Genie' | 'Genius' | 'Links' | 'Merlin' | 'Peedy' | 'Rocky' | 'Rover';

	interface Agent {
		_animator: Animator;

		show(fast?: boolean): void;

		play(animation: string, timeout?: number, callback?: () => void): this;

		animate(): this;

		animations(): string[];

		hasAnimation(animation: string): boolean;

		delay(time?: number): void;

		closeBalloon(): void;

		speak(text: string): this;

		moveTo(x: number, y: number, duration?: number): this;

		gestureAt(x: number, y: number): this;

		stopCurrent(): this;

		stop(): this;

		_getDirection(x: number, y: number): string;
	}

	interface Animator {
		_sounds: HTMLAudioElement[];
	}
}

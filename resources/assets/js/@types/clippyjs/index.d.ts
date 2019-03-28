declare module 'clippyjs' {
	export default class Clippy {
		public static load(name: AgentName, cb: (agent: Agent) => void): void;
	}

	type AgentName = 'Bonzi' | 'Clippy' | 'F1' | 'Genie' | 'Genius' | 'Links' | 'Merlin' | 'Peedy' | 'Rocky' | 'Rover';

	interface Agent {
		show(): void;

		play(animation: string): this;

		animate(): this;

		animations(): string[];

		speak(text: string): this;

		moveTo(x: number, y: number): this;

		gestureAt(x: number, y: number): this;

		stopCurrent(): this;

		stop(): this;
	}
}

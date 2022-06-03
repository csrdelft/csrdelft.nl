/**
 * Context waar aan gehangen kan worden.
 *
 * Als de pagina wordt geladen of als er een nieuw object aan de dom wordt gehangen, wordt met de selector gezocht die
 * aan deze klasse is gegeven. Als deze selector gevonden is in de update aan de dom, wordt de handler uitgevoerd.
 */
interface ContextHandler {
	selector: string;
	handler: ContextHandlerFunction;
}

interface ContextHandlers {
	[selector: string]: ContextHandlerFunction;
}

declare global {
	interface Window {
		_stek_context: Context;
	}
}

type ContextHandlerFunction = (el: HTMLElement) => void;

class Context {
	private handlers: ContextHandler[] = [];

	public addHandlers(selectors: ContextHandlers) {
		for (const selector of Object.keys(selectors)) {
			this.addHandler(selector, selectors[selector]);
		}
	}

	public addHandler(selector: string, handler: ContextHandlerFunction) {
		this.handlers.push({selector, handler});
	}

	public init(parent: HTMLElement) {
		if (!parent.querySelectorAll) {
			throw new Error('Kan geen context initializeren op dit element: ' + parent);
		}

		for (const {selector, handler} of this.handlers) {
			if (selector === '') {
				handler(parent);
			} else {
				for (const el of Array.from(parent.querySelectorAll<HTMLElement>(selector))) {
					handler(el);
				}
			}
		}
	}
}

window._stek_context = new Context();

export default window._stek_context;
export const init = (parent: HTMLElement): void => window._stek_context.init(parent);

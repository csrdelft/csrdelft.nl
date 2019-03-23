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

type ContextHandlerFunction = (el: Element) => void;

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

	public init(parent: Element) {
		if (!parent.querySelectorAll) {
			throw new Error('Kan geen context initializeren op dit element: ' + parent);
		}

		for (const {selector, handler} of this.handlers) {
			if (selector === '') {
				handler(parent);
			} else {
				for (const el of Array.from(parent.querySelectorAll(selector))) {
					handler(el);
				}
			}
		}
	}
}

/**
 * Singleton Context.
 */
const ctx = new Context();

export default ctx;

export const init = (parent: Element) => ctx.init(parent);

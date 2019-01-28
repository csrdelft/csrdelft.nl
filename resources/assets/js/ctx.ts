/**
 * Context waar aan gehangen kan worden.
 */
interface ContextListener {
	selector: string;
	callback: (el: Element) => void;
}

interface InitType {
	[k: string]: (el: Element) => void;
}

class Context {
	private initFunctions: ContextListener[] = [];

	public init(selectors: InitType) {
		for (const selector of Object.keys(selectors)) {
			this.addContext(selector, selectors[selector]);
		}
	}

	public addContext(selector: string, callback: (el: Element) => void) {
		this.initFunctions.push({selector, callback});
	}

	public initContext(parent: Element) {
		for (const {selector, callback} of this.initFunctions) {
			if (selector === '') {
				callback(parent);
			} else {
				parent.querySelectorAll(selector).forEach(callback);
			}
		}
	}
}

export default new Context();

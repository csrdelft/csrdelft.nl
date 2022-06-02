declare module 'corejs-typeahead' {
	export default class Bloodhound {
		public static tokenizers: BloodhoundTokenizers;

		constructor(options: BloodhoundOptions);

		public initialize(): void;

		public ttAdapter(): (query: string, syncResults: any, asyncResults: any) => void;
	}

	interface BloodhoundTokenizers {
		whitespace: BloodhoundTokenizer;
		nonword: BloodhoundTokenizer;
		ngram: BloodhoundTokenizer;
		obj: {
			whitespace: (field: string) => BloodhoundTokenizer;
		};
	}

	type BloodhoundTokenizer = (query: string) => string[];

	export interface BloodhoundOptions {
		datumTokenizer: BloodhoundTokenizer;
		queryTokenizer: BloodhoundTokenizer;
		matchAnyQueryToken?: boolean;
		initialize?: boolean;
		identify?: (datum: any) => string;
		sufficient?: number;
		sorter?: (a: any, b: any) => number;
		local?: object[] | (() => object[]);
		prefetch?: string | BloodhoundPrefetchOptions;
		remote?: string | BloodhoundRemoteOptions;
		indexRemote?: boolean;
	}

	interface BloodhoundPrefetchOptions {
		url: string;
		cache?: boolean;
		ttl?: number;
		cacheKey?: string;
		thumbprint?: string;
		prepare?: (settings: BloodhoundOptions) => BloodhoundOptions;
		transform: (response: object) => object;
	}

	interface BloodhoundRequestData {
		dataType: 'json';
		type: 'GET' | 'POST';
		url: string;
	}

	interface BloodhoundRemoteOptions {
		url: string;
		prepare?: (query: string, settings: BloodhoundOptions) => BloodhoundOptions;
		wildcard?: string;
		rateLimitBy?: (fun: () => void) => void;
		rateLimitWait?: number;
		transform?: (response: any) => any;
		transport?: (
			options: BloodhoundRequestData,
			onSuccess: (data: object) => void,
			onError: (error: object) => void
		) => void;
	}

	interface TypeaheadOptions {
		hint?: boolean;
		highlight?: boolean;
		autoselect?: boolean;
		minLength?: number;
		classNames?: {
			input?: string;
			hint?: string;
			menu?: string;
			dataset?: string;
			suggestion?: string;
			empty?: string;
			open?: string;
			cursor?: string;
			highlight?: string;
		};
	}

	export interface TypeaheadDataset {
		source: (query: string, syncResults: any, asyncResults: any) => void;
		async?: boolean;
		name?: string;
		limit?: number;
		display?: string | ((result: string) => string);
		displayKey?: string;
		templates?: {
			notfound?: string | ((query: string) => string);
			pending?: string | ((query: string) => string);
			header?: string | ((query: string, suggestions: string[]) => string);
			footer?: string | ((query: string, suggestions: string[]) => string);
			suggestion?: (suggestion: any) => string;
		};
	}

	global {
		interface JQuery {
			typeahead(options: TypeaheadOptions, ...datasets: TypeaheadDataset[]): this;

			typeahead(action: 'val'): string;

			typeahead(action: 'val', val: string): void;

			typeahead(action: 'open' | 'close' | 'destroy'): void;
		}
	}
}

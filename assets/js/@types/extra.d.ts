declare module '@fullcalendar/core/locales/nl'
declare module 'textcomplete'

interface JQueryStatic {
	markItUp: (arg: unknown) => unknown;
}

interface JQuery {
	markItUp: (arg: unknown) => unknown;
	scrollTo: (arg: unknown) => void;
	timeago: () => void
}


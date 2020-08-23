import clippy, {Agent, AgentName} from 'clippyjs';

declare global {
	interface Window {
		ASSISTENT: AgentName
		ASSISTENT_GELUIDEN: string
	}
}

interface AgentRule {
	cb?: (agent: Agent) => void;
	location?: string;
	probability?: number;
	noOther?: boolean;
	messageCondition?: (message: string) => boolean;
}

function sleep(ms: number) {
	return new Promise((resolve) => setTimeout(resolve, ms));
}

function offset(el: JQuery<HTMLElement>) {
	// Can probably be better, but this is what ClippyJS uses for determining direction,
	// so this way we guarantee the expected result
	const rect = el.offset() || {top: 0, left: 0};
	const width = el.width() || 0;
	const height = el.height() || 0;

	return {
		top: rect.top,
		left: rect.left,
		width,
		height,
		x: rect.left,
		y: rect.top,
		centerX: rect.left + width / 2,
		centerY: rect.top + height / 2,
	};
}

function currentViewPort() {
	return {
		height: document.documentElement.clientHeight,
		width: document.documentElement.clientWidth,
	};
}

function isInViewport(element: JQuery<HTMLElement>) {
	const elOffset = offset(element);
	const viewportTop = $(window).scrollTop() || 0;
	const viewportBottom = viewportTop + ($(window).height() || 0);
	return elOffset.top + elOffset.height > viewportTop && elOffset.top < viewportBottom;
}

function randomElement<T>(array: T[]) {
	return array[Math.floor(Math.random() * array.length)];
}

function gestureAt(agent: Agent, x: number, y: number, cb: () => void) {
	const direction = agent._getDirection(x, y);
	const gestureAnim = 'Gesture' + direction;
	const lookAnim = 'Look' + direction;
	return agent.play(agent.hasAnimation(gestureAnim) ? gestureAnim : lookAnim, 10000, cb);
}

const rules: AgentRule[] = [];

function addRule(options: AgentRule, cb: (agent: Agent) => void) {
	rules.push({
		...options,
		cb,
	});
}

$(() => {
	const assistant = window.ASSISTENT || 'Clippy';
	const geenGeluiden = (window.ASSISTENT_GELUIDEN || 'nee') === 'nee';
	const welcomeMessages = [
		'Hallo, welkom op de stek! Hoe kan ik je vandaag helpen?',
		'Hoe kan ik je stek ervaring vandaag weer verrijken?',
		'Weet je zeker dat je niet eigenlijk moet studeren nu?',
		'Welke stek functie wil je vandaag weer gebruiken?',
		'Wist je dat er ook een C.S.R. Sponsorextensie is?',
		'Heb je de C.S.R. Sponsorextensie al geïnstalleerd?',
		'Weet je eigenlijk al hoe je ketzers kunt maken?',
		'Wist je dat je uit meerdere assistenten kunt kiezen in instellingen?',
		'Heb je de stek in het roze thema wel eens geprobeerd?',
		'Heb je de PubCie al eens bedankt voor al hun werk?',
	];
	clippy.load(assistant, async (agent) => {
		if (geenGeluiden) {
			agent._animator._sounds = [];
		}

		const viewPort = currentViewPort();
		let message = '';

		agent.moveTo(viewPort.width - 250, viewPort.height - 250);
		agent.show();
		if (!sessionStorage.getItem('clippy-first')) {
			message = 'Hallo, welkom op de stek! Hoe kan ik je vandaag helpen?';
			sessionStorage.setItem('clippy-first', 'true');
		} else {
			message = randomElement(welcomeMessages);
		}
		agent.speak(message);
		agent.play('Wave', 5000, () => {
			agent.closeBalloon();

			let foundOne = false;
			rules.forEach((rule) => {
				if (!rule.cb || (rule.noOther && foundOne)) {
					return;
				}

				if (rule.messageCondition && !rule.messageCondition(message)) {
					return;
				}

				if (rule.location && window.location.pathname.startsWith(rule.location)) {
					rule.cb(agent);

					if (rule.location !== '/') {
						foundOne = true;
					}
				}

				if (rule.probability && rule.probability > Math.random()) {
					rule.cb(agent);
					foundOne = true;
				}
			});

			setInterval(() => agent.animate(), 14000);
		});
	});
});

addRule({location: '/profiel'}, async (agent) => {
	const pasfoto = $<HTMLImageElement>('.naam .pasfoto img')
	const foto = pasfoto[0]

	if (!foto) {
		return;
	}

	if (foto.complete) {
		pasfotoLoaded().then();
	} else {
		pasfoto.on('load', pasfotoLoaded);
	}

	async function pasfotoLoaded() {
		const box = offset($(foto));

		await sleep(2000);
		agent.stop();
		agent.moveTo(box.left - 200, box.centerY, 750);
		await sleep(1000);
		agent.play('GestureLeft', 5000, () => {
			agent.moveTo(box.left + (box.width / 2), box.top + box.height + 100, 500);
			sleep(1000).then(() => agent.play('GestureUp'));
		});
	}
});

addRule({location: '/'}, (agent) => {
	$('#search input.ZoekField').on('focusin', (event) => {
		const box = event.target.getBoundingClientRect();

		agent.stopCurrent();
		agent.moveTo(box.left, box.bottom);
		agent.play('Searching');
	});

	$('.bb-maaltijd').one('mouseenter', (event) => {
		const box = event.target.getBoundingClientRect();

		agent.stopCurrent();
		agent.moveTo(box.right + 50, Math.max(50, box.top - 100));
		agent.speak('Eet smakelijk!');
	});
});

addRule({location: '/ledenlijst'}, async (agent) => {
	agent.moveTo(100, 100);
	agent.speak('Het lijkt erop dat je iemand wilt vinden.\nHeb je het zoekveld al geprobeerd?');
	agent.play('Searching');
});

addRule({location: '/instellingen'}, async (agent) => {
	const wolkenKnop = document.evaluate(
		'//div[contains(@class,\'block\')]//a[text()=\'Wolken\']',
		document,
		null,
		XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
		null,
	).snapshotItem(1);
	const $wolkenKnop = $(wolkenKnop as HTMLElement);
	if (isInViewport($wolkenKnop)) {
		const loc = offset($wolkenKnop);
		agent.moveTo(loc.left, loc.top);
		agent.speak('Kijk, hier kun je een mooi effect kiezen');
	}
});

addRule({location: '/forum'}, async (agent) => {
	let writing = false;
	const animations = ['Writing', 'CheckingSomething'];
	const availableAnimations = agent.animations().filter((el) => animations.indexOf(el) > -1);
	let laatsteWoord = '';
	$('textarea#forumBericht').on('keyup', (event) => {
		switch (event.key.toLowerCase()) {
			case 'r':
				if (laatsteWoord === 'ketze') {
					laatsteWoord = '';
					const ketzerLinkLoc = offset($('.butn a[href="/groepen/activiteiten/nieuw"]'));
					writing = true;
					agent.stop();
					agent.play('GetAttention');
					gestureAt(agent, ketzerLinkLoc.centerX, ketzerLinkLoc.centerY, () => {
						writing = false;
					});
					return;
				}
				break;
			case ' ':
				laatsteWoord = '';
				break;
			case 'backspace':
				laatsteWoord = laatsteWoord.slice(0, -1);
				break;
			default:
				// Avoid adding special character descriptions to the word
				if (event.key.length === 1) {
					laatsteWoord += event.key.toLowerCase();
				}
		}

		const box = event.target.getBoundingClientRect();
		if (!writing) {
			writing = true;
			agent.moveTo(box.right - 150, box.bottom - 130, 500);
			agent.play(randomElement(availableAnimations), 5000, () => writing = false);
		}
	});
});

addRule({
	probability: 0.3,
	noOther: true,
	messageCondition: (message: string) => message.indexOf('Sponsorextensie') === -1,
}, async (agent) => {
	const times = sessionStorage.getItem('clippy-extensie');
	const amount = times ? parseInt(times, 10) : 0;
	if (amount < 3 && sessionStorage.getItem('clippy-first')) {
		const extensie = $('a[title="Sponsorkliks extensie (Chrome)"]');
		const extOffset = offset(extensie);
		if (extensie[0]) {
			extensie[0].scrollIntoView({
				behavior: 'smooth',
				block: 'center',
			});
			await sleep(500);
			agent.stopCurrent();
			agent.moveTo(200, extOffset.top - 20);
			await sleep(1500);
			agent.speak('Heb je de sponsorkliks extensie al geïnstalleerd?');
			agent.play('GestureRight', 5000, () => {
				agent.closeBalloon();
				sessionStorage.setItem('clippy-extensie', (amount + 1).toString());
			});
		}
	}
});

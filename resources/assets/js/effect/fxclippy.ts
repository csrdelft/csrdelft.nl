import clippy, {Agent} from 'clippyjs';

interface AgentRule {
	cb?: (agent: Agent) => void;
	location?: string;
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

function randomElement<T>(array: T[]) {
	return array[Math.floor(Math.random() * array.length)];
}

const rules: AgentRule[] = [];

function addRule(options: any, cb: (agent: Agent) => void) {
	rules.push({
		...options,
		cb,
	});
}

$(() => {
	// @ts-ignore
	const assistant = ASSISTENT || 'Clippy';
	clippy.load(assistant, async (agent) => {
		const viewPort = currentViewPort();
		agent.moveTo(viewPort.width - 250, viewPort.height - 250);
		agent.show();
		agent.play('Wave');
		agent.speak('Hallo, welkom op de stek! Hoe kan ik je vandaag helpen?');
		await sleep(2500);
		agent.closeBalloon();

		rules.forEach((rule) => {
			if (!rule.cb) {
				return;
			}

			if (rule.location && window.location.pathname.startsWith(rule.location)) {
				rule.cb(agent);
			}
		});

		setInterval(() => agent.animate(), 14000);
	});
});

addRule({location: '/profiel'}, async (agent) => {
	const pasfoto = $('.naam .pasfoto img');
	const foto = pasfoto[0] as HTMLImageElement;
	if (foto.complete) {
		pasfotoLoaded().then();
	} else {
		pasfoto.on('load', pasfotoLoaded);
	}

	async function pasfotoLoaded() {
		const box = offset(pasfoto);

		agent.stop();
		await sleep(1000);
		agent.moveTo(box.left - 100, box.centerY, 750);
		await sleep(1000);
		agent.gestureAt(box.centerX, box.centerY);
		await sleep(1000);
		agent.moveTo(box.left + (box.width / 2), box.top + box.height + 100, 750);
		await sleep(1000);
		agent.gestureAt(box.centerX, box.centerY);
	}
});

addRule({location: '/'}, (agent) => {
	// @ts-ignore
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
	const loc = offset($(wolkenKnop as HTMLElement));
	agent.moveTo(loc.left, loc.top);
	agent.speak('Kijk, hier kun je een mooi effect kiezen');
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
					agent.stop();
					agent.play('GetAttention', 5000, () => agent.gestureAt(ketzerLinkLoc.centerX, ketzerLinkLoc.centerY));
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
			agent.stop();
			agent.moveTo(box.right - 120, box.bottom - 100, 500);
			agent.play(randomElement(availableAnimations), 5000, () => writing = false);
		}
	});
});

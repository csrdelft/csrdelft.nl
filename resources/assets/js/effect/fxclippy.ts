import clippy, {Agent} from 'clippyjs';

interface AgentRule {
	cb?: (agent: Agent) => void;
	location?: string;
}

function sleep(ms: number) {
	return new Promise((resolve) => setTimeout(resolve, ms));
}

function offset(el: HTMLElement) {
	const rect = el.getBoundingClientRect() as any;
	return {
		top: rect.top,
		left: rect.left,
		width: rect.width,
		height: rect.height,
		x: rect.x,
		y: rect.y,
		centerX: rect.left + rect.width / 2,
		centerY: rect.top + rect.height / 2,
	};
}

const rules: AgentRule[] = [];

function addRule(options: any, cb: (agent: Agent) => void) {
	rules.push({
		...options,
		cb,
	});
}
$(() =>
	clippy.load('Clippy', async (agent) => {
		rules.forEach((rule) => {
			if (!rule.cb) {
				return;
			}

			if (rule.location && window.location.pathname.startsWith(rule.location)) {
				rule.cb(agent);
			}
		});
	}));

addRule({location: '/profiel'}, async (agent) => {
	const pasfoto = $('.naam .pasfoto img');

	pasfoto.on('load', async () => {
		const box = offset(pasfoto[0]);

		agent.moveTo(box.left - 100, box.centerY);
		agent.show();
		agent.gestureAt(box.centerX, box.centerY);
		await sleep(1000);
		agent.moveTo(box.x + (box.width / 2), box.y + box.height + 100);
		agent.gestureAt(box.centerX, box.centerY);
	});
});

addRule({location: '/ledenlijst'}, (agent) => {
	agent.moveTo(100, 100);
	agent.show();
	agent.play('Searching');
});

addRule({location: '/'}, (agent) => {
	agent.show();

	$('.bb-maaltijd').on('mouseenter', (event) => {
		const box = event.target.getBoundingClientRect();

		agent.stopCurrent();
		agent.moveTo(box.right + 50, Math.max(50, box.top - 100));
		agent.speak('Eet smakelijk!');
	});
});

addRule({location: '/instellingen'}, async (agent) => {
	const wolkenKnop = document.evaluate(
		'//div[contains(@class,\'block\')]//a[text()=\'Wolken\']',
		document,
		null,
		XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
		null,
	).snapshotItem(1);
	const loc = offset(wolkenKnop as HTMLElement);
	agent.moveTo(loc.left, loc.top);
	agent.speak('Kijk, hier kun je een mooi effect kiezen');
});

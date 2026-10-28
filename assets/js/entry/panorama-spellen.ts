import DOMPurify from 'dompurify';

type Kind = 'route' | 'puzzle' | 'memory' | 'spot' | 'stars';
type Info = { name: string; art: string; enabled?: boolean };
type RouteMap = {
	size: number;
	nodes: {
		x: number;
		y: number;
		target: boolean;
		bonus: boolean;
		spring: boolean;
		key: boolean;
	}[];
	edges: {
		a: number;
		b: number;
		cost: number;
		water: number;
		locked: boolean;
	}[];
	start: number;
	goal: number;
	targets: number[];
	bonuses: number[];
	springs: number[];
	keyNode: number;
	water: number;
	budget: number;
	maxSteps: number;
};
type Result = {
	game: Kind;
	level: number;
	score: number;
	time: number;
	mistakes: number;
	moves: number;
	art: string;
	perfect: boolean;
	medal: string | null;
	routeResult?: {
		cost: number;
		water: number;
		coins: number;
		objective: number;
	} | null;
};
type Active = {
	id: string;
	kind: Kind;
	level: number;
	elapsed: number;
	mistakes: number;
	moves: number;
	art: string;
	puzzle?: RouteMap;
	size?: number;
	tiles?: number[];
	cards?: (string | null)[];
	matched?: number[];
	open?: number[];
	round?: number;
	rounds?: number;
	crop?: string;
	options?: string[];
	cropPosition?: number[];
	phase?: string;
	sequence?: number[] | null;
	waitMs?: number;
	position?: number;
	length?: number;
};
type RecordScore = { best: number; plays: number; fastestHard: number | null };
type Daily = {
	day: string;
	totalPoints: number;
	question: {
		id: number;
		revision: number;
		prompt: string;
		image: string | null;
		guesses: string[];
		remaining: number;
		solved: boolean;
		points: number;
		answerRevision: number;
		statistics?: {
			participants: number;
			correct: number;
			correctPercent: number | null;
			firstTry: number;
			showParticipants?: boolean;
			showFirstTry?: boolean;
		};
		answer: string | null;
		credit: string;
	} | null;
};
type Data = {
	games: Record<Kind, Info>;
	art: Record<string, string>;
	photos: string[];
	uid: string;
	name: string | null;
	daily?: Daily;
	canManage?: boolean;
	state: {
		revision: number;
		records: Partial<Record<Kind, RecordScore>>;
		medals: string[];
		plays: number;
		total: number;
		active: Active | null;
		lastResult: Result | null;
	} | null;
	leaderboard: {
		uid: string;
		name: string;
		score: number;
		completed: number;
		quiz_points?: number;
	}[];
	champions: Partial<Record<Kind, { uid: string; name: string; time: number }>>;
};
const root = document.querySelector<HTMLElement>('[data-arcade]');
const notice = document.querySelector<HTMLElement>('.arcade-notice');
let data: Data;
let busy = false;
let screen: 'dashboard' | 'play' | 'result' = 'dashboard';
let level = 2;
let selected: number | null = null;
let route: number[] = [0];
let routeAttempt = '';
let noticeTimeout: number;
let watchTimer: number;
let fetchedAt = performance.now();
const escape = (value: unknown): string =>
	String(value ?? '').replace(
		/[&<>"']/g,
		(c) =>
			({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[
				c
			] as string)
	);
const image = (id: string): string => {
	if (/^u_[a-f0-9]{40}$/.test(id))
		return '/panorama/spellen/spelfoto/' + encodeURIComponent(id.slice(2));
	if (/^f0[1-8]$/.test(id))
		return '/images/lustrum13/fotos/' + encodeURIComponent(id) + '.webp';
	const landscapes = [
		'bos',
		'meer',
		'nacht',
		'kloof',
		'sneeuw',
		'top',
		'heide',
		'waterval',
		'duinen',
		'vulkaan',
		'noorderlicht',
		'archipel',
	];
	return (
		'/images/lustrum13/expeditie/' +
		(landscapes.includes(id) ? encodeURIComponent(id) : 'meer') +
		'.svg'
	);
};
const time = (ms: number): string =>
	`${Math.floor(ms / 60000)}:${String(Math.floor(ms / 1000) % 60).padStart(
		2,
		'0'
	)}`;
const medal = (score: number): string =>
	score >= 1600
		? 'gold'
		: score >= 1100
		? 'silver'
		: score >= 600
		? 'bronze'
		: 'none';
const medalName: Record<string, string> = {
	gold: 'Goud',
	silver: 'Zilver',
	bronze: 'Brons',
	none: 'Nog geen medaille',
};
const badge = (type = 'gold', trophy = false): string =>
	`<svg class="award-icon ${type}" viewBox="0 0 60 70" aria-hidden="true">${
		trophy
			? '<path d="M16 8h28v18q0 15-14 18-14-3-14-18Zm0 4H6v10q0 13 15 13M44 12h10v10q0 13-15 13M30 44v14M16 62h28"/>'
			: '<path class="ribbon" d="m17 35-7 29 18-8 17 8-6-29Z"/><circle cx="29" cy="25" r="21"/><path d="m29 12 4 8 9 1-7 6 2 10-8-5-8 5 2-10-7-6 9-1Z"/>'
	}</svg>`;
function notify(text: string, error = false): void {
	if (!notice) return;
	clearTimeout(noticeTimeout);
	notice.textContent = text;
	notice.classList.toggle('error', error);
	notice.hidden = false;
	noticeTimeout = window.setTimeout(
		() => {
			notice.hidden = true;
		},
		error ? 9000 : 5000
	);
}
async function api(
	action?: string,
	values: Record<string, unknown> = {}
): Promise<Data> {
	const headers: Record<string, string> = { Accept: 'application/json' };
	if (action) {
		headers['Content-Type'] = 'application/json';
		for (const key of ['X-CSRF-ID', 'X-CSRF-VALUE']) {
			const meta = document.querySelector<HTMLMetaElement>(
				`meta[property="${key}"]`
			);
			if (meta) headers[key] = meta.content;
		}
	}
	const response = await fetch(
		'/panorama/spellen/' + (action ? 'actie' : 'stand'),
		{
			method: action ? 'POST' : 'GET',
			headers,
			cache: 'no-store',
			credentials: 'same-origin',
			...(action
				? {
						body: JSON.stringify({
							action,
							revision: data?.state?.revision ?? 0,
							...values,
						}),
				  }
				: {}),
		}
	);
	if (
		response.redirected ||
		!response.headers.get('Content-Type')?.includes('application/json')
	)
		throw new Error(
			'Log opnieuw in en vernieuw deze pagina om verder te spelen.'
		);
	const body = await response.json();
	if (!response.ok) {
		if (body.game) data = { ...data, ...body.game };
		throw new Error(body.error ?? 'Opslaan is niet gelukt. Probeer opnieuw.');
	}
	return body.partial ? ({ ...data, ...body } as Data) : (body as Data);
}
async function act(
	action: string,
	values: Record<string, unknown> = {}
): Promise<void> {
	if (busy) return;
	await flushPictures();
	if (busy) return;
	busy = true;
	root?.setAttribute('aria-busy', 'true');
	const old = data.state?.active;
	const oldMistakes = old?.mistakes ?? 0;
	try {
		data = await api(action, values);
		fetchedAt = performance.now();
		selected = null;
		if (action === 'begin') {
			route = [0];
			screen = 'play';
		}
		if (action === 'abandon') screen = 'dashboard';
		if (action === 'move' && old && !data.state?.active) {
			level = old.level;
			screen = 'result';
			root?.scrollIntoView({ block: 'start' });
		}
		if ((data.state?.active?.mistakes ?? 0) > oldMistakes)
			notify(
				old?.kind === 'spot'
					? 'Dat uitzicht was het niet. Op naar de volgende uitsnede.'
					: old?.kind === 'stars'
					? 'Nog niet het juiste patroon. Kijk nog een keer.'
					: 'Nog niet goed. Je kunt opnieuw proberen.'
			);
		render();
	} catch (error) {
		render();
		notify(error instanceof Error ? error.message : 'Er ging iets mis.', true);
	} finally {
		busy = false;
		root?.removeAttribute('aria-busy');
	}
}
async function refresh(): Promise<void> {
	if (busy) return;
	busy = true;
	try {
		data = await api();
		fetchedAt = performance.now();
		render();
	} catch (e) {
		notify(e instanceof Error ? e.message : 'Vernieuwen mislukt.', true);
	} finally {
		busy = false;
	}
}
function gameDrawing(kind: string): string {
	const drawings: Record<string, string> = {
		route:
			'<path d="M7 68 33 22 50 47 67 13 94 68Z"/><path d="m23 68 12-13 13 8 15-13 14 18M33 22l-6 23 9-6 7 3M67 13v23l7-7 9 11"/><path d="M67 13V3l15 4-15 5"/>',
		puzzle:
			'<path d="M17 13h27v14c14-10 23 10 8 14h-8v17H17V41c-15 7-19-16-4-15h4Z"/><path d="M51 13h29v16c-13-6-17 13-5 15h5v14H51M17 65h15c-5 14 15 18 16 5v-5h32v24H17Z"/>',
		memory:
			'<path d="m15 17 42-7 10 64-42 7Z"/><path d="m42 27 43 6-9 61-43-6Z"/><path d="m43 67 13-18 9 13 6-7 6 22-33-4M27 33l9-2m-7 9 9-2"/>',
		spot: '<circle cx="29" cy="66" r="21"/><circle cx="75" cy="66" r="21"/><circle cx="29" cy="66" r="13"/><circle cx="75" cy="66" r="13"/><path d="m9 60 10-35c2-9 18-10 22-1l6 28m9 0 6-28c4-9 20-8 22 1l10 35M47 55h9M47 67h9M20 27l19 1m25 0 19-1"/>',
		stars:
			'<path d="m19 26 25 15 22-27 17 48-31 22-8-43M19 26l-8 36 41 22"/><circle cx="19" cy="26" r="4"/><circle cx="44" cy="41" r="4"/><circle cx="66" cy="14" r="4"/><circle cx="83" cy="62" r="4"/><circle cx="52" cy="84" r="4"/><circle cx="11" cy="62" r="4"/>',
	};
	return (
		'<svg class="game-drawing" viewBox="0 0 104 104" aria-hidden="true" focusable="false">' +
		(drawings[kind] ?? '') +
		'</svg>'
	);
}
function gameCards(): string {
	return (
		'<div class="game-cards">' +
		Object.entries(data.games)
			.filter(([, g]) => g.enabled !== false)
			.map(([kind, g], i) => {
				const score = data.state?.records[kind as Kind]?.best ?? 0;
				return (
					'<article class="game-card compact game-' +
					kind +
					'">' +
					gameDrawing(kind) +
					'<span class="game-number">0' +
					(i + 1) +
					'</span><h2>' +
					escape(g.name) +
					'</h2><div class="card-score">' +
					(score ? badge(medal(score)) : '') +
					'<span>' +
					(score ? score + ' punten' : '—') +
					'</span></div><button type="button" class="play-button" data-begin="' +
					kind +
					'">Spelen →</button></article>'
				);
			})
			.join('') +
		'</div>'
	);
}
const distinctions: Record<string, [string, string]> = {
	'eerste-uitzicht': ['Eerste potje', 'Eén spel voltooid'],
	vijfkamper: ['Alles gespeeld', 'Alle vijf spellen voltooid'],
	foutloos: ['Foutloos', 'Een perfecte poging'],
	'snelle-blik': ['Binnen 30 seconden', 'Foutloos op normaal of moeilijk'],
	'vaste-gast': ['Tien potjes', 'Tien pogingen voltooid'],
	'panorama-meester': ['Alles goud', 'Goud in alle vijf spellen'],
};
function cabinet(): string {
	return `<section class="cabinet" id="prijzen"><p class="eyebrow"></p><h2>Medailles</h2><div class="game-medals">${Object.entries(
		data.games
	)
		.map(([kind, g]) => {
			const score = data.state?.records[kind as Kind]?.best ?? 0;
			return `<div class="game-medal ${score < 600 ? 'locked' : ''}">${badge(
				medal(score)
			)}<strong>${escape(g.name)}</strong><span>${
				medalName[medal(score)]
			}</span></div>`;
		})
		.join('')}</div><div class="distinctions">${Object.entries(distinctions)
		.map(
			([id, [name, text]]) =>
				`<div class="distinction ${
					data.state?.medals.includes(id) ? 'earned' : 'locked'
				}">${badge('gold', true)}<div><h3>${name}</h3><p>${text}</p><span>${
					data.state?.medals.includes(id) ? 'Behaald' : 'Nog te behalen'
				}</span></div></div>`
		)
		.join('')}</div></section>`;
}
function rankings(): string {
	return `<section class="rankings" id="klassement"><div><h2>Klassement</h2><button type="button" class="text-button" data-refresh>Vernieuwen ↻</button>${
		data.leaderboard.length
			? `<ol class="ranking-list">${data.leaderboard
					.map(
						(r, i) =>
							`<li class="${
								r.uid === data.uid ? 'you' : ''
							}"><span class="place">${
								i + 1
							}</span><div><a href="/profiel/${encodeURIComponent(
								r.uid
							)}">${escape(r.name)}</a><small>${
								r.completed
							}/5 spellen voltooid</small></div><strong>${
								r.score
							}</strong></li>`
					)
					.join('')}</ol>`
			: '<p class="empty">Nog geen scores.</p>'
	}</div><div class="champions"><p class="eyebrow">De wisseltrofeeën</p><h2>Records</h2><p class="small">Snelste foutloze poging op moeilijk.</p>${Object.entries(
		data.games
	)
		.map(([kind, g]) => {
			const champ = data.champions[kind as Kind];
			return `<div class="champion">${badge('gold', true)}<div><h3>${escape(
				g.name
			)}</h3>${
				champ
					? `<a href="/profiel/${encodeURIComponent(champ.uid)}">${escape(
							champ.name
					  )}${champ.uid === data.uid ? ' · jij!' : ''}</a><span>${time(
							Number(champ.time)
					  )}</span>`
					: '<span>Nog geen record</span>'
			}</div></div>`;
		})
		.join('')}</div></section>`;
}
function dailyCard(): string {
	const d = data.daily,
		q = d?.question;
	const date = d?.day
		? new Intl.DateTimeFormat('nl-NL', {
				day: 'numeric',
				month: 'long',
		  }).format(new Date(d.day + 'T12:00:00Z'))
		: '';
	const stats = q?.statistics;
	const statistics = stats
		? `<div class="daily-statistics" aria-label="Tussenstand dagvraag">${
				stats.showParticipants
					? `<div><strong>${stats.participants}</strong><span>${
							stats.participants === 1 ? 'deelnemer' : 'deelnemers'
					  }</span></div>`
					: ''
		  }<div title="Percentage van alle spelers dat het antwoord al goed heeft."><strong>${
				stats.correctPercent === null ? '—' : stats.correctPercent + '%'
		  }</strong><span>goed geraden</span></div>${
				stats.showFirstTry
					? `<div><strong>${stats.firstTry}</strong><span>in één keer</span></div>`
					: ''
		  }</div>`
		: '';

	return `<section class="daily-card" id="dagvraag"><div class="daily-heading"><h2>Dagvraag</h2><span>${escape(
		date
	)}</span>${
		data.canManage ? '<a href="/panorama/spellen/beheer">Beheren</a>' : ''
	}</div>${
		!q
			? '<p>Vandaag staat er nog geen vraag klaar.</p>'
			: `${
					q.image
						? `<img src="${escape(
								q.image
						  )}" alt="Foto voor de dagvraag" class="daily-photo">`
						: ''
			  }<div class="daily-question"><h3>${escape(q.prompt)}</h3>${
					q.answer
						? `<div class="daily-outcome"><p>${
								q.solved
									? `Goed! +${q.points} punten`
									: 'Helaas, deze keer niet.'
						  }</p><strong>${escape(
								q.answer
						  )}</strong><span>Morgen weer een nieuwe vraag.</span></div>`
						: `<form data-daily-form><label class="sr-only" for="daily-guess">Je antwoord</label><input id="daily-guess" name="guess" required maxlength="100" autocomplete="off" placeholder="Je antwoord"><button type="submit" class="play-button">Raden</button></form><p class="small daily-attempts">${
								q.remaining
						  } ${
								q.remaining === 1 ? 'poging' : 'pogingen'
						  } over · 200 / 120 / 60 punten</p>`
			  }${
					q.guesses.length
						? `<p class="daily-guesses">${q.guesses
								.map((guess) => `<span>${escape(guess)}</span>`)
								.join('')}</p>`
						: ''
			  }${
					q.credit
						? `<small class="daily-credit">${escape(q.credit)}</small>`
						: ''
			  }</div>${statistics}`
	}</section>`;
}
function dashboard(): string {
	const active = data.state?.active,
		total = (data.state?.total ?? 0) + (data.daily?.totalPoints ?? 0);
	return (
		'<section class="arcade-title poster-title"><img src="/images/lustrum13/panorama-poster.webp" alt="" width="1800" height="1279" fetchpriority="high"><div class="poster-title-copy"><p>Lustrum XIII</p><h1>De spellen</h1></div><span class="score-ticket">' +
		total +
		'<small>punten</small></span></section><nav class="dashboard-nav" aria-label="Spellen"><a href="#kies">Spellen</a><a href="#dagvraag">Dagvraag</a><a href="#prijzen">Medailles</a><a href="#klassement">Klassement</a>' +
		(data.canManage
			? '<a href="/panorama/spellen/beheer">Dagvragen beheren</a><a href="/panorama/spellen/spelbeheer">Spellen beheren</a>'
			: '') +
		'</nav>' +
		dailyCard() +
		(active
			? '<div class="resume"><span>' +
			  escape(data.games[active.kind].name) +
			  ' staat nog open.</span><button type="button" data-resume>Verder →</button></div>'
			: '') +
		'<section class="choose-section" id="kies"><fieldset class="levels"><legend>Niveau</legend>' +
		[1, 2, 3]
			.map(
				(l) =>
					'<label><input type="radio" name="level" value="' +
					l +
					'" ' +
					(level === l ? 'checked' : '') +
					'><span>' +
					['', 'Makkelijk', 'Normaal', 'Moeilijk'][l] +
					'</span></label>'
			)
			.join('') +
		'</fieldset>' +
		gameCards() +
		'</section>' +
		cabinet() +
		rankings()
	);
}
function routeMetrics(
	p: RouteMap,
	path: number[]
): {
	cost: number;
	water: number;
	coins: number;
	targets: Set<number>;
	key: boolean;
	valid: boolean;
} {
	let cost = 0,
		water = p.water,
		key = false,
		valid = true;
	const targets = new Set<number>(),
		bonuses = new Set<number>(),
		springs = new Set<number>();
	for (let i = 0; i < path.length; i++) {
		const node = path[i];
		if (i) {
			const e = p.edges.find(
				(e) =>
					(e.a === path[i - 1] && e.b === node) ||
					(e.b === path[i - 1] && e.a === node)
			);
			if (!e || e.water > water || (e.locked && !key)) {
				valid = false;
				break;
			}
			cost += e.cost;
			water -= e.water;
		}
		if (p.targets.includes(node)) targets.add(node);
		if (p.bonuses.includes(node)) bonuses.add(node);
		if (p.springs.includes(node) && !springs.has(node)) {
			water = Math.min(6, water + 2);
			springs.add(node);
		}
		if (node === p.keyNode) key = true;
	}
	return {
		cost,
		water,
		coins: bonuses.size,
		targets,
		key,
		valid: valid && cost <= p.budget,
	};
}
function routeGame(a: Active): string {
	const p = a.puzzle as RouteMap;
	if (routeAttempt !== a.id) {
		routeAttempt = a.id;
		route = [p.start];
	}
	const edges = p.edges
		.map((e, i) => {
			const x = p.nodes[e.a],
				y = p.nodes[e.b];
			return (
				'<g class="route-edge ' +
				(e.water ? 'water-edge' : '') +
				'" data-edge="' +
				i +
				'"><path d="M' +
				x.x +
				' ' +
				x.y +
				' L' +
				y.x +
				' ' +
				y.y +
				'"/><text x="' +
				(x.x + y.x) / 2 +
				'" y="' +
				((x.y + y.y) / 2 - 1.4) +
				'">' +
				e.cost +
				(e.water ? '·' + e.water + 'w' : '') +
				(e.locked ? '·⚿' : '') +
				'</text></g>'
			);
		})
		.join('');
	return (
		'<div class="route-stats" data-route-stats></div><div class="route-network" role="group" aria-label="Routekaart"><svg viewBox="0 0 100 100" aria-hidden="true">' +
		edges +
		'</svg>' +
		p.nodes
			.map(
				(n, i) =>
					'<button type="button" class="route-node" data-node="' +
					i +
					'" style="left:' +
					n.x +
					'%;top:' +
					n.y +
					'%" aria-label="Punt ' +
					(i + 1) +
					(n.target ? ', vlag' : '') +
					(n.bonus ? ', bonus' : '') +
					(n.spring ? ', water' : '') +
					(n.key ? ', sleutel' : '') +
					'">' +
					(i === p.start
						? '◎'
						: i === p.goal
						? '▲'
						: n.target
						? '⚑'
						: n.key
						? '⚿'
						: n.spring
						? '+2w'
						: n.bonus
						? '◆'
						: '·') +
					'</button>'
			)
			.join('') +
		'</div><div class="route-legend"><span>◎ start</span><span>⚑ langs de vlaggen</span><span>▲ finish</span><span>◆ uitkijkpunt: bonus</span><span>Getal op pad = minuten</span></div><p class="board-feedback" role="status">Trek je route langs de vlaggen naar de top. Korte paden en uitkijkpunten leveren meer punten op.</p><div class="board-actions"><button type="button" class="play-button" data-submit-route>Klaar →</button><button type="button" class="text-button" data-undo-route>Stap terug</button><button type="button" class="text-button" data-clear-route>Opnieuw</button></div>'
	);
}
function pictureGame(a: Active): string {
	const n = a.size as number;
	return `<p class="play-instructions">Wissel twee stukken.</p><div class="board-meta"><span>${
		a.moves
	} wissels</span><span>${n} × ${n} stukken</span></div><div class="picture-grid" style="--size:${n}" role="group" aria-label="Panoramapuzzel">${(
		a.tiles as number[]
	)
		.map(
			(piece, i) =>
				`<button type="button" data-piece="${i}" aria-pressed="${
					selected === i
				}" aria-label="Stuk rij ${Math.floor(i / n) + 1}, kolom ${
					(i % n) + 1
				}" style="background-image:url('${image(a.art)}');background-size:${
					n * 100
				}% ${n * 100}%;background-position:${((piece % n) * 100) / (n - 1)}% ${
					(Math.floor(piece / n) * 100) / (n - 1)
				}%"></button>`
		)
		.join('')}</div>`;
}
function memoryGame(a: Active): string {
	return `<p class="play-instructions">Zoek de paren.</p><div class="board-meta"><span>${
		a.moves
	} beurten</span><span>${(a.matched?.length ?? 0) / 2}/${
		(a.cards?.length ?? 0) / 2
	} paren</span></div><div class="memory-grid" role="group" aria-label="Memorykaarten">${(
		a.cards as (string | null)[]
	)
		.map(
			(card, i) =>
				`<button type="button" data-flip="${i}" class="memory-card ${
					a.matched?.includes(i) ? 'matched' : ''
				}" ${
					a.matched?.includes(i) || (a.open?.includes(i) && a.open.length === 1)
						? 'disabled'
						: ''
				} aria-label="Kaart ${i + 1}${
					card ? ', ' + escape(data.art[card]) : ', gesloten'
				}">${
					card
						? `<img src="${image(card)}" alt="${escape(data.art[card])}">`
						: '<span class="card-back" aria-hidden="true">P<span>Panorama</span></span>'
				}</button>`
		)
		.join('')}</div>`;
}
function spotGame(a: Active): string {
	return (
		'<div class="board-meta"><span>' +
		((a.round ?? 0) + 1) +
		' / ' +
		a.rounds +
		'</span><span>' +
		a.mistakes +
		' fout</span></div><div class="viewfinder real-photo" role="img" aria-label="Foto-uitsnede" style="background-image:url(' +
		image(a.crop as string) +
		');background-size:' +
		[0, 250, 350, 450][a.level] +
		'%;background-position:' +
		a.cropPosition?.[0] +
		'% ' +
		a.cropPosition?.[1] +
		'%"></div><div class="spot-options real-photo">' +
		(a.options as string[])
			.map(
				(id, i) =>
					'<button type="button" data-answer="' +
					id +
					'"><img src="' +
					image(id) +
					'" width="800" height="500" alt="Optie ' +
					(i + 1) +
					'"><span>' +
					String.fromCharCode(65 + i) +
					'</span></button>'
			)
			.join('') +
		'</div>'
	);
}
function starGame(a: Active): string {
	return (
		'<div class="board-meta"><span>Patroon ' +
		((a.round ?? 0) + 1) +
		' / ' +
		a.rounds +
		'</span><span>' +
		a.mistakes +
		' fout</span></div><div class="star-field" style="background-image:url(' +
		image('nacht') +
		')"><div class="stars-grid" role="group" aria-label="Negen sterren">' +
		Array.from(
			{ length: 9 },
			(_, i) =>
				'<button type="button" data-star="' +
				i +
				'" ' +
				(a.phase !== 'input' ? 'disabled' : '') +
				' aria-label="Ster ' +
				(i + 1) +
				'"><span aria-hidden="true">✦</span><small>' +
				(i + 1) +
				'</small></button>'
		).join('') +
		'</div></div><p class="star-status" role="status">' +
		(a.phase === 'idle'
			? 'Klaar?'
			: a.phase === 'watch'
			? 'Kijk…'
			: 'Jouw beurt.') +
		'</p><button type="button" class="play-button" data-reveal ' +
		(a.phase === 'watch' ? 'disabled' : '') +
		'>' +
		(a.phase === 'idle' ? 'Start patroon' : 'Nog eens · telt als fout') +
		'</button>'
	);
}
function play(): string {
	const a = data.state?.active;
	if (!a) {
		screen = 'dashboard';
		return dashboard();
	}
	return (
		'<section class="play-scene"><img src="' +
		image(a.art) +
		'" width="1440" height="800" alt=""><div><button class="text-button" type="button" data-dashboard>← Spellen</button><h1>' +
		escape(data.games[a.kind].name) +
		'</h1></div></section><section class="play-area"><div class="game-clock"><span data-clock>' +
		time(a.elapsed) +
		'</span><span>' +
		['', 'Makkelijk', 'Normaal', 'Moeilijk'][a.level] +
		'</span></div>' +
		(a.kind === 'route'
			? routeGame(a)
			: a.kind === 'puzzle'
			? pictureGame(a)
			: a.kind === 'memory'
			? memoryGame(a)
			: a.kind === 'spot'
			? spotGame(a)
			: starGame(a)) +
		'<button type="button" class="text-button stop-game" data-abandon>Stoppen</button></section>'
	);
}
function resultScreen(): string {
	const r = data.state?.lastResult;
	if (!r) return dashboard();
	const best = data.state?.records[r.game]?.best ?? r.score;
	return (
		'<section class="result-scene"><img src="' +
		image(r.art) +
		'" width="1440" height="800" alt=""><div>' +
		badge(r.medal ?? 'none') +
		'<h1>' +
		r.score +
		' punten</h1><p>' +
		time(r.time) +
		' · ' +
		r.mistakes +
		' fout' +
		(r.routeResult
			? ' · ' +
			  r.routeResult.cost +
			  ' minuten lopen · ' +
			  r.routeResult.coins +
			  ' bonus'
			: '') +
		'</p><p>Beste: ' +
		best +
		'</p><div class="result-buttons"><button type="button" class="play-button" data-begin="' +
		r.game +
		'">Nog een keer</button><button type="button" class="outline-button" data-dashboard>Spellen</button></div></div></section>'
	);
}
function render(): void {
	if (!root) return;
	clearInterval(watchTimer);
	root.innerHTML = DOMPurify.sanitize(
		screen === 'play'
			? play()
			: screen === 'result'
			? resultScreen()
			: dashboard()
	);
	if (screen === 'play' && data.state?.active?.kind === 'route') updateRoute();
	if (
		screen === 'play' &&
		data.state?.active?.kind === 'stars' &&
		data.state.active.phase === 'watch'
	)
		watchStars(data.state.active);
}
function updateRoute(message?: string): void {
	const p = data.state?.active?.puzzle;
	if (!p) return;
	const m = routeMetrics(p, route);
	root?.querySelectorAll<HTMLButtonElement>('[data-node]').forEach((b) => {
		const i = Number(b.dataset.node);
		b.classList.toggle('path', route.includes(i));
		b.classList.toggle('current', i === route[route.length - 1]);
		const last = route[route.length - 1];
		b.classList.toggle(
			'available',
			p.edges.some(
				(e) => (e.a === last && e.b === i) || (e.b === last && e.a === i)
			)
		);
		b.classList.toggle('visited-flag', p.nodes[i].target && route.includes(i));
		b.setAttribute('aria-pressed', String(route.includes(i)));
	});
	root?.querySelectorAll<SVGGElement>('[data-edge]').forEach((g) => {
		const e = p.edges[Number(g.dataset.edge)];
		g.classList.toggle(
			'used',
			route.some(
				(node, i) =>
					i > 0 &&
					((node === e.a && route[i - 1] === e.b) ||
						(node === e.b && route[i - 1] === e.a))
			)
		);
	});
	const stats = root?.querySelector('[data-route-stats]');
	if (stats)
		stats.textContent =
			m.targets.size +
			'/' +
			p.targets.length +
			' vlaggen · ' +
			m.cost +
			' minuten lopen · ' +
			m.coins +
			' uitkijkpunten';
	const feedback = root?.querySelector('.board-feedback');
	if (message && feedback) feedback.textContent = message;
}
function pickRoute(i: number): void {
	const p = data.state?.active?.puzzle;
	if (!p || busy || i === route[route.length - 1]) return;
	if (route.length >= p.maxSteps) {
		updateRoute('Je route is te lang.');
		return;
	}
	const candidate = [...route, i];
	const m = routeMetrics(p, candidate);
	if (!m.valid) {
		updateRoute(
			'Deze punten zijn niet rechtstreeks verbonden. Kies een aansluitend pad.'
		);
		return;
	}
	route = candidate;
	updateRoute();
}
const starSequences = new Map<string, number[]>();
let starInput: number[] = [];
let starRound = '';
function watchStars(a: Active): void {
	const seq = a.sequence;
	if (!seq) return;
	const key = a.id + ':' + a.round;
	starSequences.set(key, [...seq]);
	starInput = [];
	starRound = key;
	const remaining = a.waitMs ?? 0,
		started = performance.now(),
		total = seq.length * 340 + 350;
	watchTimer = window.setInterval(() => {
		const left = remaining - (performance.now() - started),
			position = total - left - 350;
		root
			?.querySelectorAll<HTMLElement>('[data-star]')
			.forEach((b) =>
				b.classList.toggle(
					'lit',
					left > 0 &&
						position % 340 < 220 &&
						Number(b.dataset.star) === seq[Math.floor(position / 340)]
				)
			);
		if (left <= -25) {
			clearInterval(watchTimer);
			a.phase = 'input';
			a.sequence = null;
			root?.querySelectorAll<HTMLButtonElement>('[data-star]').forEach((b) => {
				b.disabled = false;
				b.classList.remove('lit');
			});
			const status = root?.querySelector('.star-status');
			if (status) status.textContent = 'Jouw beurt.';
			const reveal = root?.querySelector<HTMLButtonElement>('[data-reveal]');
			if (reveal) reveal.disabled = false;
		}
	}, 25);
}
function pickStar(i: number): void {
	const a = data.state?.active;
	if (!a || a.kind !== 'stars' || a.phase !== 'input' || busy) return;
	const key = a.id + ':' + a.round;
	if (starRound !== key) {
		starRound = key;
		starInput = [];
	}
	const b = root?.querySelector<HTMLElement>('[data-star="' + i + '"]');
	b?.classList.add('lit');
	window.setTimeout(() => b?.classList.remove('lit'), 140);
	starInput.push(i);
	const sequence = starSequences.get(key),
		wrong = sequence && sequence[starInput.length - 1] !== i;
	const status = root?.querySelector('.star-status');
	if (status)
		status.textContent =
			starInput.length + ' / ' + (a.length ?? sequence?.length ?? 0);
	if (wrong || starInput.length === (a.length ?? sequence?.length)) {
		const pattern = [...starInput];
		starInput = [];
		move('pattern', { pattern });
	}
}
let pictureOps: number[][] = [];
let pictureSending: Promise<void> | null = null;
let pictureTimer: number;
async function flushPictures(): Promise<void> {
	clearTimeout(pictureTimer);
	if (pictureSending !== null) {
		await pictureSending;
		if (pictureOps.length) await flushPictures();
		return;
	}
	const a = data?.state?.active;
	if (!pictureOps.length || !a || a.kind !== 'puzzle') return;
	const batch = pictureOps.splice(0),
		attempt = a.id;
	pictureSending = (async () => {
		try {
			const value = await api('move', { attempt, move: 'swaps', ops: batch });
			data = value;
			fetchedAt = performance.now();
			const active = data.state?.active;
			if (active?.id === attempt && active.tiles) {
				for (const [x, y] of pictureOps) {
					[active.tiles[x], active.tiles[y]] = [
						active.tiles[y],
						active.tiles[x],
					];
					active.moves++;
				}
			} else if (!active) {
				pictureOps = [];
				screen = 'result';
			}
			render();
		} catch (e) {
			pictureOps = [];
			selected = null;
			try {
				data = await api();
			} catch {
				/* De vorige opgeslagen stand blijft gelden. */
			}
			render();
			notify(e instanceof Error ? e.message : 'Opslaan mislukt.', true);
		}
	})();
	try {
		await pictureSending;
	} finally {
		pictureSending = null;
	}
	if (pictureOps.length) await flushPictures();
}
function swapPiece(i: number): void {
	const a = data.state?.active;
	if (
		!a?.tiles ||
		a.kind !== 'puzzle' ||
		busy ||
		a.tiles.every((v, i) => v === i)
	)
		return;
	if (selected === null) {
		selected = i;
		root
			?.querySelector('[data-piece="' + i + '"]')
			?.setAttribute('aria-pressed', 'true');
		return;
	}
	if (selected === i) {
		selected = null;
		root
			?.querySelector('[data-piece="' + i + '"]')
			?.setAttribute('aria-pressed', 'false');
		return;
	}
	const j = selected;
	selected = null;
	[a.tiles[j], a.tiles[i]] = [a.tiles[i], a.tiles[j]];
	a.moves++;
	pictureOps.push([j, i]);
	const board = root?.querySelector('.picture-grid');
	if (board) {
		const wrapper = document.createElement('div');
		wrapper.innerHTML = pictureGame(a);
		const replacement = wrapper.querySelector('.picture-grid');
		if (replacement) board.replaceWith(replacement);
	} else render();
	root
		?.querySelectorAll('[data-piece]')
		.forEach((b) => b.setAttribute('aria-pressed', 'false'));
	const meta = root?.querySelector('.board-meta>span');
	if (meta) meta.textContent = a.moves + ' wissels';
	clearTimeout(pictureTimer);
	pictureTimer = window.setTimeout(() => {
		void flushPictures();
	}, 80);
}
const memoryQueue: number[] = [];
let memorySending = false;
async function flipCard(i: number): Promise<void> {
	const a = data.state?.active;
	if (
		!a ||
		a.kind !== 'memory' ||
		memoryQueue.includes(i) ||
		memoryQueue.length >= 2 ||
		a.matched?.includes(i)
	)
		return;
	memoryQueue.push(i);
	root?.querySelector('[data-flip="' + i + '"]')?.classList.add('pending');
	if (memorySending) return;
	memorySending = true;
	try {
		while (memoryQueue.length && data.state?.active?.kind === 'memory') {
			const index = memoryQueue.shift() as number;
			const active = data.state.active;
			if (active.matched?.includes(index)) continue;
			await act('move', { attempt: active.id, move: 'flip', index });
		}
	} finally {
		memorySending = false;
		memoryQueue.length = 0;
	}
}
function move(name: string, values: Record<string, unknown> = {}): void {
	if (data.state?.active)
		void act('move', { attempt: data.state.active.id, move: name, ...values });
}
let draggingRoute = false;
root?.addEventListener('click', (event) => {
	const b = (event.target as HTMLElement).closest<HTMLButtonElement>('button');
	if (!b || b.disabled) return;
	if (b.dataset.flip !== undefined) {
		void flipCard(Number(b.dataset.flip));
		return;
	}
	if (busy) return;
	if (b.dataset.begin) {
		void act('begin', { game: b.dataset.begin, level }).then(() => {
			root?.scrollIntoView({ block: 'start' });
			preloadGame();
		});
		return;
	}
	if (b.hasAttribute('data-dashboard')) {
		void flushPictures().then(() => {
			screen = 'dashboard';
			render();
			root?.scrollIntoView({ block: 'start' });
			void refresh();
		});
		return;
	}
	if (b.hasAttribute('data-resume')) {
		screen = 'play';
		render();
		preloadGame();
		root?.scrollIntoView({ block: 'start' });
		return;
	}
	if (b.hasAttribute('data-refresh')) {
		void refresh();
		return;
	}
	if (b.hasAttribute('data-abandon')) {
		void act('abandon');
		return;
	}
	if (b.dataset.node !== undefined) {
		pickRoute(Number(b.dataset.node));
		return;
	}
	if (b.hasAttribute('data-clear-route')) {
		const p = data.state?.active?.puzzle;
		if (p) {
			route = [p.start];
			updateRoute();
		}
		return;
	}
	if (b.hasAttribute('data-undo-route')) {
		if (route.length > 1) route.pop();
		updateRoute();
		return;
	}
	if (b.hasAttribute('data-submit-route')) {
		move('route', { path: route });
		return;
	}
	if (b.dataset.piece !== undefined) {
		swapPiece(Number(b.dataset.piece));
		return;
	}
	if (b.dataset.answer) {
		move('answer', { answer: b.dataset.answer });
		return;
	}
	if (b.dataset.star !== undefined) {
		pickStar(Number(b.dataset.star));
		return;
	}
	if (b.hasAttribute('data-reveal')) {
		move('reveal');
		return;
	}
});
root?.addEventListener('pointerdown', (event) => {
	const node = (event.target as HTMLElement).closest<HTMLElement>(
		'[data-node]'
	);
	if (node && data.state?.active?.kind === 'route') {
		draggingRoute = true;
		pickRoute(Number(node.dataset.node));
		event.preventDefault();
	}
});
root?.addEventListener('pointermove', (event) => {
	if (!draggingRoute) return;
	const node = document
		.elementFromPoint(event.clientX, event.clientY)
		?.closest<HTMLElement>('[data-node]');
	if (node) pickRoute(Number(node.dataset.node));
});
window.addEventListener('pointerup', () => {
	draggingRoute = false;
});
window.addEventListener('pointercancel', () => {
	draggingRoute = false;
});
root?.addEventListener('change', (event) => {
	const input = event.target as HTMLInputElement;
	if (input.name === 'level') level = Number(input.value);
});
window.addEventListener('keydown', (event) => {
	if ((event.target as HTMLElement).closest('input,textarea')) return;
	const a = data.state?.active;
	if (screen !== 'play' || !a) return;
	if (a.kind === 'stars' && /^[1-9]$/.test(event.key)) {
		event.preventDefault();
		pickStar(Number(event.key) - 1);
		return;
	}
	if (a.kind !== 'route' || !a.puzzle) return;
	const p = a.puzzle;
	if (event.key === 'Backspace' || event.key === 'z') {
		event.preventDefault();
		if (route.length > 1) route.pop();
		updateRoute();
		return;
	}
	const last = route[route.length - 1],
		n = p.nodes[last];
	const direction: Record<string, [number, number]> = {
		ArrowUp: [0, -1],
		ArrowDown: [0, 1],
		ArrowLeft: [-1, 0],
		ArrowRight: [1, 0],
	};
	if (!direction[event.key]) return;
	event.preventDefault();
	const [dx, dy] = direction[event.key];
	const possible = p.edges
		.filter((e) => e.a === last || e.b === last)
		.map((e) => (e.a === last ? e.b : e.a))
		.filter((i) => (p.nodes[i].x - n.x) * dx + (p.nodes[i].y - n.y) * dy > 0)
		.sort((i, j) => {
			const x = p.nodes[i],
				y = p.nodes[j];
			return (
				Math.abs((x.x - n.x) * dy - (x.y - n.y) * dx) -
				Math.abs((y.x - n.x) * dy - (y.y - n.y) * dx)
			);
		});
	if (possible.length) pickRoute(possible[0]);
});
function csrfHeaders(json = false): Record<string, string> {
	const headers: Record<string, string> = { Accept: 'application/json' };
	if (json) headers['Content-Type'] = 'application/json';
	for (const key of ['X-CSRF-ID', 'X-CSRF-VALUE']) {
		const meta = document.querySelector<HTMLMetaElement>(
			'meta[property="' + key + '"]'
		);
		if (meta) headers[key] = meta.content;
	}
	return headers;
}
root?.addEventListener('submit', (event) => {
	const form = event.target as HTMLFormElement;
	if (!form.hasAttribute('data-daily-form')) return;
	event.preventDefault();
	void guessDaily(form);
});
async function guessDaily(form: HTMLFormElement): Promise<void> {
	const q = data.daily?.question;
	if (!q || busy) return;
	busy = true;
	const button = form.querySelector<HTMLButtonElement>('button');
	if (button) button.disabled = true;
	try {
		const guess = new FormData(form).get('guess');
		const response = await fetch('/panorama/spellen/dagantwoord', {
			method: 'POST',
			headers: csrfHeaders(true),
			credentials: 'same-origin',
			body: JSON.stringify({
				question: q.id,
				revision: q.revision,
				answerRevision: q.answerRevision,
				guess,
			}),
		});
		if (!response.headers.get('Content-Type')?.includes('application/json'))
			throw new Error('Log opnieuw in en vernieuw de pagina.');
		const body = await response.json();
		if (!response.ok) {
			if (body.daily) data.daily = body.daily;
			throw new Error(body.error ?? 'Antwoord niet opgeslagen.');
		}
		data.daily = body as Daily;
		render();
		notify(
			data.daily.question?.solved
				? 'Goed! +' + data.daily.question.points + ' punten'
				: 'Nog niet goed.'
		);
	} catch (e) {
		render();
		notify(e instanceof Error ? e.message : 'Antwoord niet opgeslagen.', true);
	} finally {
		busy = false;
	}
	void refresh();
}
const loadedAttempts = new Set<string>();
function preloadGame(): void {
	const a = data.state?.active;
	if (!a || loadedAttempts.has(a.id)) return;
	loadedAttempts.add(a.id);
	const ids =
		a.kind === 'spot'
			? data.photos
			: a.kind === 'memory'
			? Object.keys(data.art)
			: [a.art];
	for (const id of ids) {
		const img = new Image();
		img.src = image(id);
	}
}
window.setInterval(() => {
	const clock = root?.querySelector('[data-clock]');
	if (clock && data?.state?.active)
		clock.textContent = time(
			data.state.active.elapsed + performance.now() - fetchedAt
		);
}, 250);
if (root)
	api()
		.then((value) => {
			data = value;
			fetchedAt = performance.now();
			render();
		})
		.catch((e) => {
			root.innerHTML =
				'<section class="loading"><h1>Spellen niet bereikbaar</h1><p></p><button type="button" class="play-button" data-retry>Opnieuw</button></section>';
			const p = root.querySelector('p');
			if (p) p.textContent = e.message;
			root
				.querySelector('[data-retry]')
				?.addEventListener('click', () => location.reload());
		});

type AdminQuestion = {
	id: number;
	revision: number;
	day: string;
	prompt: string;
	answers: string[];
	image: string | null;
	credit: string;
	published: boolean;
	played: number;
};
const adminRoot = document.querySelector<HTMLElement>('[data-quiz-admin]');
let adminQuestions: AdminQuestion[] = [];
let adminToday = '';
async function adminFetch<
	T = { today: string; questions: AdminQuestion[]; id?: number }
>(url: string, body?: FormData): Promise<T> {
	const response = await fetch(url, {
		method: body ? 'POST' : 'GET',
		headers: csrfHeaders(),
		credentials: 'same-origin',
		cache: 'no-store',
		...(body ? { body } : {}),
	});
	if (!response.headers.get('Content-Type')?.includes('application/json'))
		throw new Error('Geen toegang of sessie verlopen.');
	const value = await response.json();
	if (!response.ok) throw new Error(value.error ?? 'Opslaan mislukt.');
	return value;
}
function listQuestions(): void {
	const list = adminRoot?.querySelector('[data-question-list]');
	if (!list) return;
	list.innerHTML = adminQuestions.length
		? adminQuestions
				.map(
					(q) =>
						'<button type="button" class="question-row" data-edit-question="' +
						q.id +
						'"><strong>' +
						escape(q.day) +
						'</strong><span>' +
						escape(q.prompt) +
						'</span><small>' +
						(q.played ? 'Gespeeld' : q.published ? 'Ingepland' : 'Concept') +
						'</small></button>'
				)
				.join('')
		: '<p>Nog geen vragen.</p>';
}
function editQuestion(q?: AdminQuestion): void {
	const form = adminRoot?.querySelector<HTMLFormElement>(
		'[data-question-form]'
	);
	if (!form) return;
	form.reset();
	const set = (name: string, value: string) => {
		const input = form.elements.namedItem(name) as
			| HTMLInputElement
			| HTMLTextAreaElement;
		input.value = value;
	};
	set('id', String(q?.id ?? 0));
	set('revision', String(q?.revision ?? 0));
	set('day', q?.day ?? adminToday);
	(form.elements.namedItem('day') as HTMLInputElement).min = adminToday;
	set('prompt', q?.prompt ?? 'Welke stad zie je?');
	set('answers', q?.answers.join('\n') ?? '');
	set('credit', q?.credit ?? '');
	(form.elements.namedItem('published') as HTMLInputElement).checked =
		q?.published ?? true;
	const image = form.querySelector<HTMLImageElement>('[data-question-preview]');
	if (image) {
		image.hidden = !q?.image;
		if (q?.image) image.src = q.image;
		else image.removeAttribute('src');
	}
	const locked = Boolean(q && (q.played > 0 || q.day < adminToday));
	const fields = form.querySelector<HTMLFieldSetElement>('fieldset');
	if (fields) fields.disabled = locked;
	const status = form.querySelector('[data-admin-message]');
	if (status)
		status.textContent = locked
			? 'Deze vraag is al gespeeld of voorbij en staat vast.'
			: '';
}
async function compressPhoto(file: File): Promise<Blob> {
	if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type))
		throw new Error('Gebruik JPEG, PNG of WebP.');
	const bitmap = await createImageBitmap(file);
	const scale = Math.min(1, 1920 / Math.max(bitmap.width, bitmap.height));
	const canvas = document.createElement('canvas');
	canvas.width = Math.max(1, Math.round(bitmap.width * scale));
	canvas.height = Math.max(1, Math.round(bitmap.height * scale));
	const ctx = canvas.getContext('2d');
	if (!ctx) throw new Error('Kan de foto niet verwerken.');
	ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
	bitmap.close();
	return new Promise((resolve, reject) =>
		canvas.toBlob(
			(blob) =>
				blob ? resolve(blob) : reject(new Error('Kan de foto niet verwerken.')),
			'image/webp',
			0.85
		)
	);
}
if (adminRoot) {
	adminFetch('/panorama/spellen/beheer/data')
		.then((value) => {
			adminQuestions = value.questions;
			adminToday = value.today;
			listQuestions();
			editQuestion();
		})
		.catch((e) => notify(e.message, true));
	adminRoot.addEventListener('click', (event) => {
		const b = (event.target as HTMLElement).closest<HTMLButtonElement>(
			'button'
		);
		if (!b) return;
		if (b.hasAttribute('data-new-question')) editQuestion();
		if (b.dataset.editQuestion)
			editQuestion(
				adminQuestions.find((q) => q.id === Number(b.dataset.editQuestion))
			);
	});
	adminRoot.querySelector('form')?.addEventListener('submit', (event) => {
		event.preventDefault();
		const form = event.target as HTMLFormElement;
		void (async () => {
			const button = form.querySelector<HTMLButtonElement>('[type="submit"]');
			if (button) button.disabled = true;
			try {
				const values = new FormData(form);
				const file = (form.elements.namedItem('photo') as HTMLInputElement)
					.files?.[0];
				if (file) values.set('photo', await compressPhoto(file), 'foto.webp');
				else values.delete('photo');
				const saved = await adminFetch(
					'/panorama/spellen/beheer/opslaan',
					values
				);
				adminQuestions = saved.questions;
				adminToday = saved.today;
				listQuestions();
				editQuestion(adminQuestions.find((q) => q.id === saved.id));
				notify('Vraag opgeslagen.');
			} catch (e) {
				const status = form.querySelector('[data-admin-message]');
				if (status)
					status.textContent =
						e instanceof Error ? e.message : 'Opslaan mislukt.';
			} finally {
				if (button) button.disabled = false;
			}
		})();
	});
}
export {};

type GameSettings = {
	dailyVisibility: { participants: boolean; firstTry: boolean };
	revision: number;
	games: Record<Kind, Info & { enabled: boolean; images: string[] }>;
	uploads: Record<string, { credit: string }>;
};
const gameAdmin = document.querySelector<HTMLElement>('[data-game-admin]');
if (gameAdmin) {
	const panel = gameAdmin;
	const form = panel.querySelector('[data-game-settings]') as HTMLFormElement;
	const upload = panel.querySelector('[data-game-upload]') as HTMLFormElement;
	let settings: GameSettings;
	let selected: Kind = 'route';
	let saving = false;
	const message = (value: string): void => {
		(panel.querySelector('[data-game-message]') as HTMLElement).textContent =
			value;
	};
	const drawSettings = (): void => {
		const g = settings.games[selected];
		(form.elements.namedItem('revision') as HTMLInputElement).value = String(
			settings.revision
		);
		(form.elements.namedItem('game') as HTMLInputElement).value = selected;
		(form.elements.namedItem('name') as HTMLInputElement).value = g.name;
		(form.elements.namedItem('enabled') as HTMLInputElement).checked =
			g.enabled;
		(panel.querySelector('[data-game-tabs]') as HTMLElement).innerHTML =
			Object.entries(settings.games)
				.map(
					([id, v]) =>
						`<button type="button" data-edit-game="${id}" aria-pressed="${
							id === selected
						}">${escape(v.name)}</button>`
				)
				.join('');
		const pool =
			selected === 'spot'
				? [
						...['f01', 'f02', 'f03', 'f04', 'f05', 'f06', 'f07'],
						...Object.keys(settings.uploads),
				  ]
				: [
						...[
							'bos',
							'meer',
							'nacht',
							'kloof',
							'sneeuw',
							'top',
							'heide',
							'waterval',
							'duinen',
							'vulkaan',
							'noorderlicht',
							'archipel',
						],
						...['f01', 'f02', 'f03', 'f04', 'f05', 'f06', 'f07'],
						...Object.keys(settings.uploads),
				  ];
		const minimum = selected === 'memory' ? 10 : selected === 'spot' ? 4 : 1;
		const background = selected === 'route' || selected === 'stars';
		(panel.querySelector('.game-admin-warning') as HTMLElement).textContent =
			background
				? 'Een andere achtergrond verandert bestaande scores niet.'
				: 'Andere spelafbeeldingen kiezen begint voor dit spel een nieuw klassement. Bestaande records worden apart bewaard.';
		(panel.querySelector('[data-image-heading]') as HTMLElement).textContent =
			background ? 'Achtergronden' : 'Afbeeldingen';
		(panel.querySelector('[data-image-help]') as HTMLElement).textContent =
			background
				? 'Kies de achtergronden voor dit spel.'
				: `Kies minstens ${minimum} verschillende afbeeldingen.`;
		(panel.querySelector('[data-game-images]') as HTMLElement).innerHTML = pool
			.map(
				(id, i) =>
					`<label class="game-image-option"><input type="checkbox" name="images[]" value="${id}" ${
						g.images.includes(id) ? 'checked' : ''
					}><img src="${image(id)}" loading="lazy" alt="Afbeelding ${
						i + 1
					}"><span>${escape(
						settings.uploads[id]?.credit ??
							(id.startsWith('f')
								? 'Foto ' + id.slice(1)
								: 'Landschap ' + (i + 1))
					)}</span></label>`
			)
			.join('');
	};
	panel.addEventListener('click', (event) => {
		const button = (event.target as HTMLElement).closest<HTMLElement>(
			'[data-edit-game]'
		);
		if (!button || saving) return;
		const id = button.dataset.editGame as Kind;
		if (id === selected) return;
		if (
			form.dataset.dirty &&
			!window.confirm('Niet-opgeslagen wijzigingen verlaten?')
		)
			return;
		selected = id;
		delete form.dataset.dirty;
		message('');
		drawSettings();
	});
	form.addEventListener('change', () => {
		form.dataset.dirty = '1';
	});
	const saveSettings = async (target: HTMLFormElement): Promise<void> => {
		if (saving) return;
		saving = true;
		const buttons = panel.querySelectorAll<HTMLButtonElement>('button');
		buttons.forEach((b) => {
			b.disabled = true;
		});
		try {
			const body = new FormData(target);
			body.set('revision', String(settings.revision));
			const file = body.get('photo');
			if (file instanceof File && file.size)
				body.set('photo', await compressPhoto(file), 'foto.webp');
			settings = await adminFetch<GameSettings>(
				'/panorama/spellen/spelbeheer/opslaan',
				body
			);
			if (target === upload) upload.reset();
			// Een upload bewaart een nog niet opgeslagen selectie in het spelformulier.
			if (target === upload && form.dataset.dirty) {
				settings.games[selected].name = (
					form.elements.namedItem('name') as HTMLInputElement
				).value;
				settings.games[selected].enabled = (
					form.elements.namedItem('enabled') as HTMLInputElement
				).checked;
				settings.games[selected].images = Array.from(
					form.querySelectorAll<HTMLInputElement>('[name="images[]"]:checked')
				).map((e) => e.value);
			} else delete form.dataset.dirty;
			drawSettings();
			message(
				target === upload
					? 'Foto toegevoegd. Vink hem aan bij een spel.'
					: 'Spel opgeslagen.'
			);
		} catch (e) {
			message(e instanceof Error ? e.message : 'Opslaan mislukt.');
		} finally {
			saving = false;
			buttons.forEach((b) => {
				b.disabled = false;
			});
		}
	};
	form.addEventListener('submit', (event) => {
		event.preventDefault();
		void saveSettings(form);
	});
	upload.addEventListener('submit', (event) => {
		event.preventDefault();
		void saveSettings(upload);
	});
	void adminFetch<GameSettings>('/panorama/spellen/spelbeheer/data')
		.then((value) => {
			settings = value;
			drawSettings();
		})
		.catch((e: Error) => message(e.message));
}

const visibilityForm = document.querySelector<HTMLFormElement>(
	'[data-daily-visibility]'
);
if (visibilityForm) {
	let visibilityRevision = 0;
	const status = visibilityForm.querySelector('[role="status"]') as HTMLElement;
	const participants = visibilityForm.elements.namedItem(
		'showParticipants'
	) as HTMLInputElement;
	const firstTry = visibilityForm.elements.namedItem(
		'showFirstTry'
	) as HTMLInputElement;
	const submit = visibilityForm.querySelector('button') as HTMLButtonElement;
	submit.disabled = true;
	void adminFetch<GameSettings>('/panorama/spellen/spelbeheer/data')
		.then((value) => {
			visibilityRevision = value.revision;
			participants.checked = value.dailyVisibility.participants;
			firstTry.checked = value.dailyVisibility.firstTry;
			submit.disabled = false;
		})
		.catch((e: Error) => {
			status.textContent = e.message;
		});
	visibilityForm.addEventListener('submit', (event) => {
		event.preventDefault();
		submit.disabled = true;
		const body = new FormData(visibilityForm);
		body.set('revision', String(visibilityRevision));
		body.set('game', 'daily-visibility');
		void adminFetch<GameSettings>('/panorama/spellen/spelbeheer/opslaan', body)
			.then((value) => {
				visibilityRevision = value.revision;
				status.textContent = 'Weergave opgeslagen.';
			})
			.catch((e: Error) => {
				status.textContent = e.message;
			})
			.finally(() => {
				submit.disabled = false;
			});
	});
}

import { NodeSpec } from 'prosemirror-model';
import { html } from '../../lib/util';

const createBlockSpec = (type: string, attr = 'id'): NodeSpec => ({
	attrs: { [attr]: {} },
	group: 'block',
	draggable: true,
	toDOM: (node) => [
		'div',
		{ [`data-${type}`]: node.attrs[attr], class: 'pm-block' },
		`${type}: ${node.attrs[attr]}`,
	],
	parseDOM: [
		{
			tag: `div[data-${type}`,
			getAttrs: (dom: HTMLElement) => ({ [attr]: dom.dataset[type] }),
		},
	],
});

const createGroepBlockSpec = (
	tagType: string,
	type: string,
	attr = 'id'
): NodeSpec => ({
	attrs: { [attr]: {}, naam: { default: '' } },
	group: 'block',
	draggable: true,
	toDOM: (node) => {
		const el = html` <div
			data-${tagType}="${node.attrs[attr]}"
			class="bb-block"
		>
			<i class="fas fa-spinner fa-spin"></i>
		</div>`;

		fetch(`/groepen/${type}/${node.attrs[attr]}/info`)
			.then((response) => response.json())
			.then((json) => {
				el.dataset.naam = json.naam;
				const ledenDiv = html` <div
					id="groep-leden-content-2152"
					class="groep-tab-content GroepPasfotosView"
					style="height: 212.8px;"
				></div>`;

				for (const lid of json.leden) {
					ledenDiv.appendChild(html`<a
						href="#"
						title="${lid.naam}"
						class="lidLink"
						><img
							class="pasfoto"
							src="/profiel/pasfoto/${lid.uid}.jpg"
							alt="Pasfoto van ${lid.naam}"
					/></a>`);
				}

				el.innerHTML = '';
				el.appendChild(html`
<div class="bb-groep">
<div class="groep-samenvatting">
<div class="float-end">
<a
class="btn"
target="_blank"
href="/groepen/${type}/${json.id}/wijzigen"
title="Wijzig ${json.naam}"
>
<i class="fas fa-pencil"></i>
</a>
</div>
<h3>${json.naam}</h3>
${json.samenvatting_html ?? ''}
</div>

<div id="groep-leden-2152" class="groep-leden">
<ul class="groep-tabs nobullets">
<li class="geschiedenis">
<a class="btn disabled" href="#" title="Bekijk geschiedenis">
<i class="fas fa-clock-rotate-left"></i>"></i>
</a>
</li>
<li>
<a
class="btn btn-primary disabled"
href="#"
title="Pasfoto's tonen"
>
<i class="fas fa-user"></i>
</a>
</li>
<li>
<a class="btn disabled" href="#" title="Lijst tonen">
<i class="fas fa-list"></i>
</a>
</li>
<li>
<a class="btn disabled" href="#" title="Statistiek tonen">
<i class="fas fa-chart-pie"></i>
</a>
</li>
<li>
<a class="btn disabled" href="#" title="E-mails tonen">
<i class="fas fa-envelope"></i>
</a>
</li>
<li>
<a class="btn disabled" href="#" title="Allergie/dieet tonen">
<i class="fas fa-heartbeat"></i>
</a>
</li>
<li class="knop-vergroot"></li>
</ul>
${ledenDiv}
</div>
<div class="clear">&nbsp;</div>
</div>`);
			})
			.catch(() => {
				el.innerText = 'Groep niet gevonden';
			});

		return el;
	},
	parseDOM: [
		{
			tag: `div[data-${type}`,
			getAttrs: (dom: HTMLElement) => ({
				[attr]: dom.dataset[type],
				naam: dom.dataset.naam,
			}),
		},
	],
});

// Groepen
export const activiteit = createGroepBlockSpec('activiteit', 'activiteiten');
export const bestuur = createGroepBlockSpec('bestuur', 'besturen');
export const commissie = createGroepBlockSpec('commissie', 'commissies');
export const groep = createGroepBlockSpec('groep', 'overig');
export const ketzer = createGroepBlockSpec('ketzer', 'ketzers');
export const ondervereniging = createGroepBlockSpec(
	'ondervereniging',
	'onderverenigingen'
);
export const verticale = createGroepBlockSpec('verticale', 'verticalen');
export const werkgroep = createGroepBlockSpec('werkgroep', 'werkgroepen');
export const woonoord = createGroepBlockSpec('woonoord', 'woonoorden');

// Overig
export const boek = createBlockSpec('boek');
export const document = createBlockSpec('document');
export const maaltijd = createBlockSpec('maaltijd');
export const peiling = createBlockSpec('peiling');

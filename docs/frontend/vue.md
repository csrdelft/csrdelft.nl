---
layout: default
parent: Frontend
nav_order: 1
title: Vue
---

# Vue

Vue is een nieuwere manier van frontend dingen bouwen. Peilingen, namen leren en een beginnetje van groepen is in Vue
gebouwd.

## Waarom Vue?

Kijk op [vuejs.org](https://vuejs.org/) voor meer uitleg. Maar het komt er eigenlijk op neer dat als je een component
wil maken met veel gebruikers interactie, dat Vue dan een goede optie is.

## Een vue component maken

Kijk in `assets/js/components` voor alle Vue code in de stek.

Het volgen van de [Guide](https://vuejs.org/v2/guide/) van Vue is aangeraden als je nooit Vue hebt aangeraakt.

Om een nieuw component te maken maak je een nieuw `.vue` bestand aan met dezelfde structuur als hier onder beschreven.

```vue
<template>
	<div>{{ mooiId }}</div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { defineComponent } from 'vue';

export default defineComponent({
	props: {
		settings: {
			required: true,
			type: Object as PropType<{ id: number }>,
		},
	},
	data: () => ({
		id: 0,
	}),
	created() {
		this.id = this.settings.id;
	},
	computed: {
		mooiId() {
			return `Mijn id is ${this.id}`;
		},
	},
	methods: {
		veranderId() {
			this.id = 13;
		},
	},
});
</script>

<style scoped></style>
```

Tussen de template tags kun je je template definieren, tussen de script tags kun je je component definieren en tussen de
style tags kun je css definieren.

### Template

Hier kun je een normaal Vue template in kwijt. Je kan hier classnames gebruiken die je onderaan het bestand definieert
of die ergens in de css van de stek staan.

### Script

Houdt er rekening mee dat je hier dezelfde TypeScript regels hebt als in de rest van de stek. PhpStorm is niet zo goed
in deze errors checken, maar `yarn dev` kan ze je wel vertellen.

### Style

De styles die je hier definieert zijn alleen maar beschikbaar in dit component.

## Componenten registreren

In `register-vue.ts` kun je je component registreren in de algemene Vue instance.

## Component opstarten

Als je een component hebt geregistreerd, bijvoorbeeld `mijn-component` dan kun je in de php code de volgende html terug
geven.

```php
\CsrDelft\common\Util\VueUtil::vueComponent('mijn-component', [
	'settings' => ['id' => 10],
]);
```

Door de `.vue-component` klasse wordt op die html tag vue geïnitialiseerd. Zie [TypeScript](typescript.md) voor meer
uitleg over hoe de context werkt.

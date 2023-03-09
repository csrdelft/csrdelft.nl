import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// https://vitejs.dev/config/
export default defineConfig({
	plugins: [vue()],
	base: '/dist',
	build: {
		outDir: 'htdocs/dist',
		// generate manifest.json in outDir
		manifest: true,
		rollupOptions: {
			// overwrite default .html entry
			input: {
				app: 'assets/js/entry/app.ts',
				sentry: 'assets/js/entry/sentry.ts',
				ledenmemory: 'assets/js/entry/ledenmemory.ts',
				fxclouds: 'assets/js/entry/fxclouds.ts',
				fxsneeuw: 'assets/js/entry/fxsneeuw.ts',
				fxonontdekt: 'assets/js/entry/fxonontdekt.ts',
				fxtrein: 'assets/js/entry/fxtrein.ts',
				fxraket: 'assets/js/entry/fxraket.ts',
				fxdruif: 'assets/js/entry/fxdruif.ts',
				fxminion: 'assets/js/entry/fxminion.ts',
				fxspace: 'assets/js/entry/fxspace.ts',
				extern: 'assets/js/entry/extern.ts',
				'extern-style': 'assets/scss/extern.scss',
				bredeletters: 'assets/scss/bredeletters.scss',
				common: 'assets/scss/common.scss',
				'extern-forum': 'assets/scss/extern-forum.scss',
				'extern-fotoalbum': 'assets/scss/extern-fotoalbum.scss',
				'extern-owee': 'assets/js/entry/extern-owee.ts',
				'extern-owee-style': 'assets/scss/extern-owee.scss',
				maaltijdlijst: 'assets/scss/maaltijdlijst.scss',
				// Donker wordt altijd geladen
				'thema-donker': 'assets/scss/thema/donker.scss',
				'thema-civitasia': 'assets/scss/thema/civitasia.scss',
				'thema-dies': 'assets/scss/thema/dies.scss',
				'thema-lustrum': 'assets/scss/thema/lustrum.scss',
				'thema-normaal': 'assets/scss/thema/normaal.scss',
				'thema-owee': 'assets/scss/thema/owee.scss',
				'thema-roze': 'assets/scss/thema/roze.scss',
				'thema-koevoet': 'assets/scss/thema/Koevoet.scss',
				'thema-sineregno': 'assets/scss/thema/sineregno.scss',
				'effect-civisaldo': 'assets/scss/effect/civisaldo.scss',
				// lustrum-page related scss
				lustrum: 'assets/scss/lustrum12/style.scss',
				lustrumthema: 'assets/scss/lustrum12/thema.scss',
				lustrumweek: 'assets/scss/lustrum12/lustrumweek.scss',
				lustrumweek2: 'assets/scss/lustrum12/lustrumweek2.scss',
				lustrummerch: 'assets/scss/lustrum12/dikkemerch.scss',
				lustrumdiesthema: 'assets/scss/lustrum12/diesthema.scss',
				lustrumdies: 'assets/scss/lustrum12/dies.scss',
				'lustrum12-js': 'assets/js/lib/lustrum12.ts',
				lustrumreis: 'assets/scss/lustrum12/lustrumreis.scss',
				dies2023: 'assets/scss/dies2023/dies2023.scss',
			},
		},
	},
});

const path = require('path')

const PnpWebpackPlugin = require(`pnp-webpack-plugin`);

const contextPath = path.resolve(__dirname, 'resources/assets');

module.exports = (env, argv) => ({
	mode: 'development',
	context: contextPath,
	entry: {
		'app': './js/entry/app.ts',
		'sentry': './js/entry/sentry.ts',
		'ledenmemory': './js/entry/ledenmemory.ts',
		'fxclouds': './js/entry/fxclouds.ts',
		'fxsneeuw': './js/entry/fxsneeuw.ts',
		'fxonontdekt': './js/entry/fxonontdekt.ts',
		'fxtrein': './js/entry/fxtrein.ts',
		'fxraket': './js/entry/fxraket.ts',
		'fxminion': './js/entry/fxminion.ts',
		'fxclippy': './js/entry/fxclippy.ts',
		'fxspace': './js/entry/fxspace.ts',
		'extern': ['./js/entry/extern.ts', './sass/extern.scss'],
		'bredeletters': './sass/bredeletters.scss',
		'common': './sass/common.scss',
		'extern-forum': './sass/extern-forum.scss',
		'extern-fotoalbum': './sass/extern-fotoalbum.scss',
		'extern-owee': ['./js/entry/extern-owee.ts', './sass/extern-owee.scss'],
		'maaltijdlijst': './sass/maaltijdlijst.scss',
		'thema-civitasia': './sass/thema/civitasia.scss',
		'thema-dies': './sass/thema/dies.scss',
		'thema-donker': './sass/thema/donker.scss',
		'thema-lustrum': './sass/thema/lustrum.scss',
		'thema-normaal': './sass/thema/normaal.scss',
		'thema-owee': './sass/thema/owee.scss',
		'thema-roze': './sass/thema/roze.scss',
		'thema-koevoet': './sass/thema/Koevoet.scss',
		'thema-sineregno': './sass/thema/sineregno.scss',
		'effect-civisaldo': './sass/effect/civisaldo.scss',
	},
	output: {
		// De map waarin alle bestanden geplaatst worden.
		path: path.resolve(__dirname, 'htdocs/dist'),
		// Alle javascript bestanden worden in de map js geplaatst.
		filename: argv.mode !== 'production' ? 'js/[name].bundle.js' : 'js/[name].[contenthash].bundle.js',
		chunkFilename: argv.mode !== 'production' ? 'js/[name].chunk.js' : 'js/[name].[contenthash].chunk.js',
		publicPath: '/dist/',
	},
	devtool: 'source-map',
	resolve: {
		// Vanuit javascript kun je automatisch .js en .ts bestanden includen.
		extensions: ['.ts', '.js', '.vue'],
		alias: {
			vue$: 'vue/dist/vue.esm.js',
		},
		plugins: [
			PnpWebpackPlugin,
		]
	},
	resolveLoader: {
		plugins: [
			PnpWebpackPlugin.moduleLoader(module),
		]
	},
	optimization: {
		minimizer: [
			new (require('optimize-css-assets-webpack-plugin'))({}),
			new (require('terser-webpack-plugin'))(),
		],
		splitChunks: {
			chunks: 'all',
		},
	},
	plugins: [
		new (require('mini-css-extract-plugin'))({
			// Css bestanden komen in de map css terecht.
			filename: argv.mode !== 'production' ? 'css/[name].css' : 'css/[name].[contenthash].css',
		}),
		new (require('vue-loader/lib/plugin'))(),
		new (require('webpack-manifest-plugin'))(),
		new (require('moment-locales-webpack-plugin'))({
			localesToKeep: ['nl'],
		}),
		new (require('./bin/dev/css-cleanup-webpack-plugin'))(),
	],
	module: {
		// Regels voor bestanden die webpack tegenkomt, als `test` matcht wordt de rule uitgevoerd.
		rules: [
			// Controleer .js bestanden met ESLint. Zie ook .eslintrc.yaml
			{
				enforce: 'pre',
				test: /\.(js|jsx)$/,
				exclude: [
					/node_modules/,
					/lib\/external/,
				],
				use: 'eslint-loader',
			},
			// Verwerk .js bestanden met babel, dit zorgt ervoor dat alle nieuwe foefjes van javascript gebruikt kunnen worden
			// terwijl we nog wel oudere browsers ondersteunen.
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: [
					'cache-loader',
					{
						loader: 'babel-loader',
						options: {
							presets: ['@babel/preset-env'],
							plugins: [
								'@babel/syntax-dynamic-import',
								'@babel/plugin-proposal-class-properties',
								'@babel/plugin-transform-runtime',
								['@babel/plugin-proposal-decorators', {decoratorsBeforeExport: true}],
							],
						},
					},
				],
			},
			// Verwerk .ts (typescript) bestanden en maak er javascript van.
			{
				test: /\.ts$/,
				use: {
					loader: 'ts-loader',
					options: { appendTsSuffixTo: [/\.vue$/] }
				},
			},
			{
				test: /\.vue$/,
				use: {
					loader: 'vue-loader',
					options: {
						loaders: {
							ts: 'ts-loader',
						},
					},
				},
			},
			// Verwerk sass bestanden.
			// `sass-loader` >
			// Compileer naar css
			// `resolve-url-loader` >
			// Zorg ervoor dat verwijzingen naar externe bestanden kloppen (sass was meerdere bestanden, css één)
			// `css-loader` >
			// Trek alle afbeeldingen/fonts waar naar verwezen wordt naar de dist/images map
			// `postcss-loader` >
			// Haal een autoprefixer over de css, deze zorgt ervoor dat eventuele vendor-prefixes (-moz-, -webkit-)
			// worden toegevoegd.
			// `MiniCssExtractPlugin` >
			// Normaal slaat webpack css op in javascript bestanden, zodat je ze makkelijk specifiek kan opvragen
			// hier zorgen we ervoor dat de css eruit wordt getrokken en in een los .css bestand wordt gestopt.
			// css-cleanup-webpack-plugin is verantwoordelijk voor het verwijderen van leeggetrokken js bestanden.
			{
				test: /\.scss$/,
				use: [
					{
						loader: require('mini-css-extract-plugin').loader,
						options: {
							// De css bestanden zitten in de css map, / is dus te vinden op ../
							publicPath: '../',
						},
					},
					'cache-loader',
					{
						loader: 'css-loader',
						options: {
							importLoaders: 3,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							ident: 'postcss',
							plugins: [require('autoprefixer')],
						},
					},
					{
						loader: 'resolve-url-loader',
						options: {},
					},
					{
						loader: 'sass-loader',
						options: {
							// Source maps moeten aan staan om `resolve-url-loader` te laten werken.
							sourceMap: true,
						},
					},
				],
			},
			{
				test: /\.css$/,
				use: ['cache-loader', 'style-loader', 'css-loader'],
			},
			// Sla fonts op in de fonts map.
			{
				test: /\.(woff|woff2|eot|ttf|otf)$/,
				use: [{
					loader: 'file-loader',
					options: {
						name: 'fonts/[name].[ext]',
					},
				}],
			},
			// Sla plaetjes op in de images map.
			{
				test: /\.(png|svg|jpg|gif)$/,
				use: [{
					loader: 'file-loader',
					options: {
						name: 'images/[name].[ext]',
					},
				}],
			},
		],
	},
});

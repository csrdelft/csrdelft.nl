/**
 * Strip js bestanden die leeggemaakt zijn door mini css extract plugin.
 */
class CssCleanupWebpackPlugin {
	apply(compiler) {
		compiler.hooks.emit.tapAsync('MiniCssExtractPluginCleanup', (compilation, callback) => {
			const sassEntries = compilation.entries
				.filter((e) => /scss$/.test(e.rawRequest))
				.map((e) => (e.getChunks()).map((c) => c.name))
				.reduce((flat, val) => flat.concat(val), []);

			const assets = Object.keys(compilation.assets);
			for (const asset of assets) {
				if (!asset.startsWith('js')) {
					continue;
				}

				for (const sassEntry of sassEntries) {
					// asset is js/----.js of js/blah~----~blah.js of js/blah~----.js
					if (asset.startsWith(`js/${sassEntry}.`)
						|| asset.includes(`~${sassEntry}~`)
						|| asset.includes(`~${sassEntry}.`)) {
						delete compilation.assets[asset];
					}
				}
			}

			callback();
		});
	}
}

module.exports = CssCleanupWebpackPlugin;

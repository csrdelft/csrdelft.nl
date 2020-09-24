/**
 * Strip js bestanden die leeggemaakt zijn door mini css extract plugin.
 */
class CssCleanupWebpackPlugin {
	apply(compiler) {
		compiler.hooks.emit.tapAsync('MiniCssExtractPluginCleanup', (compilation, callback) => {
			const scssEntries = compilation.entries
				.filter((e) => /scss$/.test(e.rawRequest))
				.map((e) => (e.getChunks()).map((c) => c.name))
				.reduce((flat, val) => flat.concat(val), []);

			const assets = Object.keys(compilation.assets);
			for (const asset of assets) {
				if (!asset.startsWith('js')) {
					continue;
				}

				for (const scssEntry of scssEntries) {
					// asset is js/----.js of js/blah~----~blah.js of js/blah~----.js
					if (asset.startsWith(`js/${scssEntry}.`)
						|| asset.includes(`~${scssEntry}~`)
						|| asset.includes(`~${scssEntry}.`)) {
						delete compilation.assets[asset];
					}
				}
			}

			callback();
		});
	}
}

module.exports = CssCleanupWebpackPlugin;

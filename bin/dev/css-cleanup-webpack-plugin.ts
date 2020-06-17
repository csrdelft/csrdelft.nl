import webpack from 'webpack';

/**
 * Strip js bestanden die leeggemaakt zijn door mini css extract plugin.
 */
class CssCleanupWebpackPlugin {
	public apply(compiler: webpack.Compiler) {
		compiler.hooks.emit.tapAsync('MiniCssExtractPluginCleanup', (compilation, callback) => {
			const sassEntries: string[] = compilation.entries
				.filter((e) => /scss$/.test(e.id))
				.map((e) => (e.getChunks() as webpack.compilation.Chunk[]).map((c) => c.id as string))
				.reduce((flat, val) => flat.concat(val), []);

			const assets = Object.keys(compilation.assets);
			for (const asset of assets) {
				for (const sassEntry of sassEntries) {
					// asset is js/----.js of js/blah~----~blah.js of js/blah~----.js
					if (asset.startsWith(`js/${sassEntry}.`)
						|| asset.indexOf(`~${sassEntry}~`) > 0
						|| asset.indexOf(`~${sassEntry}.`) > 0) {
						delete compilation.assets[asset];
					}
				}
			}

			callback();
		});
	}
}

module.exports = CssCleanupWebpackPlugin;

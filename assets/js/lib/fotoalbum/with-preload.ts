import load from 'jgallery/src/utils/load';
import AlbumItem from 'jgallery/types/album-item';
import { GalleryDecorator } from 'jgallery/types/gallery';

const withPreload: GalleryDecorator = (constructor) =>
	class extends constructor {
		protected goToItem(item: AlbumItem) {
			const { items } = this.album;
			const next = items[items.indexOf(item) + 1];
			if (next) {
				next.url && load(next.url);
			} else {
				const first = items[0];

				first.url && load(first.url);
			}

			const prev = items[items.indexOf(item) + 1];
			if (prev) {
				prev.url && load(prev.url);
			} else {
				const last = items[items.length - 1];

				last.url && load(last.url);
			}

			return super.goToItem(item);
		}
	};

export default withPreload;

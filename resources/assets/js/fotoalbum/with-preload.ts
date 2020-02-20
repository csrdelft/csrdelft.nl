import load from 'jgallery/src/utils/load';
import AlbumItem from 'jgallery/types/album-item';
import {GalleryDecorator} from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';

const withPreload: GalleryDecorator = (constructor) =>
	class extends constructor {
		constructor(albums: AlbumItem[], params: Params) {
			super(albums, params);

			document.addEventListener('keydown', (e) => {
				switch (e.key) {
					case 'ArrowLeft':
						this.prev();
						break;
					case 'ArrowRight':
						this.next();
						break;
				}
			});
		}

		protected goToItem(item: AlbumItem) {
			const {album} = this;

			const {items} = album;
			const next = items[items.indexOf(item) + 1];
			if (next) {
				load(next.url!);
			} else {
				load(items[0].url!);
			}

			const prev = items[items.indexOf(item) + 1];
			if (prev) {
				load(prev.url!);
			} else {
				load(items[items.length - 1].url!);
			}

			return super.goToItem(item);
		}
	};

export default withPreload;

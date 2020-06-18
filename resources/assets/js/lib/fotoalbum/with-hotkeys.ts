import AlbumItem from 'jgallery/types/album-item';
import {GalleryDecorator} from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';

const withHotkeys: GalleryDecorator = (constructor) =>
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
	};

export default withHotkeys;

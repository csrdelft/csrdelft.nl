import createElement from 'jgallery/src/utils/create-element';
import withTooltip from 'jgallery/src/utils/with-tooltip';
import AlbumItem from 'jgallery/types/album-item';
import { GalleryDecorator } from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';

const withDownloadButton: GalleryDecorator = (constructor) =>
	class extends constructor {
		private currentItem: AlbumItem;

		constructor(albums: AlbumItem[], params: Params) {
			super(albums, params);

			const downloadIcon = withTooltip(
				createElement(
					`<span class="j-gallery-icon"><i class="fas fa-download"></i></span>`
				),
				{
					style: {
						color: params.backgroundColor,
						background: params.textColor,
						transform: 'translateY(-8px)',
					},
					content: 'Downloaden',
				}
			);

			downloadIcon.addEventListener(
				'click',
				() => (window.location.href = `${this.getFullUrl()}?download`)
			);
			this.appendControlsElements([downloadIcon]);
		}

		protected goToItem(item: AlbumItem) {
			this.currentItem = item;
			return super.goToItem(item);
		}

		private getFullUrl() {
			return this.currentItem.fullUrl;
		}
	};

export default withDownloadButton;

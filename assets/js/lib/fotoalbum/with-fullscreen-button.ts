import createElement from 'jgallery/src/utils/create-element';
import withTooltip from 'jgallery/src/utils/with-tooltip';
import AlbumItem from 'jgallery/types/album-item';
import { GalleryDecorator } from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';

const withFullscreenButton: GalleryDecorator = (constructor) =>
	class extends constructor {
		constructor(albums: AlbumItem[], params: Params) {
			super(albums, params);

			const fullscreenIcon = createElement(`<i class="fa fa-expand"></i>`);
			const fullscreenButton = withTooltip(
				createElement(`<span class="j-gallery-icon change-mode"></span>`, {
					children: [fullscreenIcon],
				}),
				{
					style: {
						color: params.backgroundColor,
						background: params.textColor,
						transform: 'translateY(-8px)',
					},
					content: 'Volledig scherm',
				}
			);
			fullscreenButton.addEventListener('click', () => this.toggleFullscreen());
			this.appendControlsElements([fullscreenButton]);

			document.addEventListener('fullscreenchange', () => {
				if (document.fullscreenElement) {
					fullscreenIcon.classList.replace('fa-expand', 'fa-compress');
				} else {
					fullscreenIcon.classList.replace('fa-compress', 'fa-expand');
				}
			});
		}

		private async toggleFullscreen() {
			if (document.fullscreenElement) {
				await document.exitFullscreen();
			} else {
				await this.previewElement.requestFullscreen();
			}
		}
	};

export default withFullscreenButton;

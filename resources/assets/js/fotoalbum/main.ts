import JGallery from 'jgallery';
import {Size} from 'jgallery/src/preview';
import withDownloadButton from './with-download-button';
import withFullscreenButton from './with-fullscreen-button';
import withHotkeys from './with-hotkeys';

declare module 'jgallery/types/album-item' {
	export default interface AlbumItem {
		fullUrl: string;
	}
}

declare module 'jgallery/types/gallery/parameters' {
	export default interface Params {
		root?: string;
	}
}

const loadFotoAlbum = async () => {
	const albums = document.querySelectorAll<HTMLElement>('.fotoalbum');
	for (const album of albums) {
		const {isLoggedIn, magAanpassen, root, fotos} = album.dataset;

		const decorators = [withFullscreenButton, withDownloadButton, withHotkeys];

		if (isLoggedIn === 'true') {
			const withTags = await import('./with-tags');
			decorators.push(withTags.default);
		}

		if (magAanpassen === 'true') {
			const withAdminButtons = await import('./with-admin-buttons');
			decorators.push(withAdminButtons.default);
		}

		album.appendChild(JGallery.create(JSON.parse(fotos!), {
			decorators,
			root,
			previewSize: Size.contain,
			tooltipThumbnailsToggle: 'Thumbnails weergeven',
			tooltipChangeSize: 'Grootte veranderen',
			tooltipSeeAllItems: 'Alle foto\'s weergeven',
			tooltipSeeOtherAlbums: 'Andere albums weergeven',
			tooltipSlideShowPause: 'Voorstelling pauzeren',
			tooltipSlideShowStart: 'Voorstelling starten',
		}).getElement());
	}
};

// loadFotoAlbum moet async zijn.
// noinspection JSIgnoredPromiseFromCall
loadFotoAlbum();

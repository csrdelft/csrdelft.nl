import { select, selectAll } from '../lib/dom';
import { laadForumIds, slaOpForumIds } from '../lib/forum';

slaOpForumIds('.cell-forum > div');
laadForumIds();

// Carousel indicators
const indicatorContainer = select('.carousel-indicators');
const indicatorButton = select('.carousel-indicators > button');
const carouselItems = selectAll('.carousel-inner > *');

for (let n = 1; n < carouselItems.length; n++) {
	const clone = indicatorButton.cloneNode(true) as HTMLElement;
	clone.setAttribute('data-bs-slide-to', String(n));
	clone.setAttribute('aria-current', String(false));
	clone.setAttribute('aria-label', `Slide ${n + 1}`);
	clone.classList.remove('active');
	indicatorContainer.appendChild(clone);
}

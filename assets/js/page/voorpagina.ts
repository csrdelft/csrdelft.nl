import { select, selectAll } from '../lib/dom';
import { laadForumDraden, slaOpForumDraden } from '../lib/forum';

setTimeout(async () => {
	await slaOpForumDraden('.cell-forum > div');
	laadForumDraden();
}, 1000);

// Carousel indicators
const indicatorContainer = select('.carousel-indicators');
const indicatorButton = select('.carousel-indicators > button');
const carouselItems = selectAll('.carousel-inner > .carousel-item');

for (let n = 1; n < carouselItems.length; n++) {
	const clone = indicatorButton.cloneNode(true) as HTMLElement;
	clone.setAttribute('data-bs-slide-to', String(n));
	clone.setAttribute('aria-current', String(false));
	clone.setAttribute('aria-label', `Slide ${n + 1}`);
	clone.classList.remove('active');
	indicatorContainer.appendChild(clone);
}

export const slideUp = (el: HTMLElement): void => {
	const range = document.createRange();
	range.selectNode(el);
	const container = document.createElement('div');
	range.surroundContents(container);

	container.style.transition = '0.4s opacity, 0.4s height';
	container.style.overflow = 'hidden';
	// Zet expliciet de height
	container.style.height = container.getBoundingClientRect().height + 'px';
	setTimeout(() => {
		container.style.height = '0px';
		container.style.opacity = '0';
	});

	setTimeout(() => {
		el.remove();
		container.remove();
	}, 400);
};

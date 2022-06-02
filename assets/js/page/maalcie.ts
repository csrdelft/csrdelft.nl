/**
 * maalcie.js  |  P.W.G. Brussee (brussee@live.nl)
 */
import $ from 'jquery';
import { takenMagRuilen, takenRuilen } from '../lib/maalcie';

$(() => {
	$('a.ruilen').each(function () {
		$(this).removeClass('ruilen');
		$(this).on('dragover', takenMagRuilen);
		$(this).on('drop', takenRuilen);
	});
});

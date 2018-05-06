/**
 * @source http://stackoverflow.com/a/7228322
 * @see templates/fotoalbum/slider.tpl
 * @param {Number} min
 * @param {Number} max
 * @returns {Number}
 */
window.randomIntFromInterval = function(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
};
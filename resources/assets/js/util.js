/**
 * @source http://stackoverflow.com/a/7228322
 * @see templates/fotoalbum/slider.tpl
 * @param {Number} min
 * @param {Number} max
 * @returns {Number}
 */
export const randomIntFromInterval = function(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min);
};

/**
 * Selecteer de tekst van een DOM-element.
 * @source http://stackoverflow.com/questions/985272/jquery-selecting-text-in-an-element-akin-to-highlighting-with-your-mouse/987376#987376
 *
 * @see templates/fotoalbum/album.tpl
 * @param elmnt DOM-object
 */
export const selectText = (elmnt) => {
    let range, selection;
    if (document.body.createTextRange) { //ms
        range = document.body.createTextRange();
        range.moveToElementText(elmnt);
        range.select();
    } else if (window.getSelection) { //all others
        selection = window.getSelection();
        range = document.createRange();
        range.selectNodeContents(elmnt);
        selection.removeAllRanges();
        selection.addRange(range);
    }
};


/**
 //  discuss at: http://phpjs.org/functions/dirname/
 // original by: Ozh
 // improved by: XoraX (http://www.xorax.info)
 //   example 1: dirname('/etc/passwd');
 //   returns 1: '/etc'
 //   example 2: dirname('c:/Temp/x');
 //   returns 2: 'c:/Temp'
 //   example 3: dirname('/dir/test/');
 //   returns 3: '/dir'
 * @see templates/fotoalbum/album.tpl
 * @param path
 */
export const dirname = (path) =>
    path.replace(/\\/g, '/')
        .replace(/\/[^\/]*\/?$/, '');

/**
 * @see templates/fotoalbum/album.tpl
 * @param path
 * @param suffix
 * @returns {*}
 */
export const basename = (path, suffix) => {
    //  discuss at: http://phpjs.org/functions/basename/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Ash Searle (http://hexmen.com/blog/)
    // improved by: Lincoln Ramsay
    // improved by: djmix
    // improved by: Dmitry Gorelenkov
    //   example 1: basename('/www/site/home.htm', '.htm');
    //   returns 1: 'home'
    //   example 2: basename('ecra.php?p=1');
    //   returns 2: 'ecra.php?p=1'
    //   example 3: basename('/some/path/');
    //   returns 3: 'path'
    //   example 4: basename('/some/path_ext.ext/','.ext');
    //   returns 4: 'path_ext'

    let b = path;
    let lastChar = b.charAt(b.length - 1);

    if (lastChar === '/' || lastChar === '\\') {
        b = b.slice(0, -1);
    }

    b = b.replace(/^.*[\/\\]/g, '');

    if (typeof suffix === 'string' && b.substr(b.length - suffix.length) === suffix) {
        b = b.substr(0, b.length - suffix.length);
    }

    return b;
};

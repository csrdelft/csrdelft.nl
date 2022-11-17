import { route } from './lib/util';

/**
 * Voer specifieke code uit voor specifieke routes.
 */

route('/', () => import('./page/voorpagina'));
route('/instellingen', () => import('./page/instellingen'));
route('/documenten', () => import('./page/documenten'));
route('/fotoalbum', () => import('./page/fotoalbum'));
route('/bibliotheek', () => import('./page/bibliotheek'));
route('/agenda', () => import('./page/agenda'));
route('/corvee', () => import('./page/maalcie'));
route('/forum', () => import('./page/forum'));

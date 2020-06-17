import {route} from './lib/util';

/**
 * Voer specifieke code uit voor specifieke routes.
 */

route('/instellingen', () => import(/* webpackChunkName: "instellingen" */ './page/instellingen'));
route('/documenten', () => import(/* webpackChunkName: "documenten" */ './page/documenten'));
route('/fotoalbum', () => import(/* webpackChunkName: "fotoalbum" */'./fotoalbum/main'));
route('/bibliotheek', () => import(/* webpackChunkName: "bibliotheek" */'./page/bibliotheek'));
route('/agenda', () => import(/* webpackChunkName: "agenda" */'./page/agenda'));

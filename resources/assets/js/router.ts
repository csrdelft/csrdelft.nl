import {route} from './util';

/**
 * Voer specifieke code uit voor specifieke routes.
 */

route('/instellingen', () => import(/* webpackChunkName: "instellingen" */ './instellingen'));
route('/documenten', () => import(/* webpackChunkName: "documenten" */ './documenten'));
route('/fotoalbum', () => import(/* webpackChunkName: "fotoalbum" */'./fotoalbum/FotoAlbum'));
route('/bibliotheek', () => import(/* webpackChunkName: "bibliotheek" */'./bibliotheek'));
route('/ledenlijst', () => import(/* webpackChunkName: "datatable" */'./datatable/bootstrap'));

import {route} from './util';

/**
 * Voer specifieke code uit voor specifieke routes.
 */

route('/instellingen', () => import('./instellingen'));
route('/documenten', () => import('./documenten'));

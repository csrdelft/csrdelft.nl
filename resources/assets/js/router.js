import {route} from './util';

/**
 * Voer specifieke code uit voor specifieke routes.
 */

route('/instellingen', () => import('./instellingen'));

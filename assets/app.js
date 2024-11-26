import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

//JQuery
import jquery from './vendor/jquery/jquery.index.js';
const $ = jquery;
window.$ = window.jquery = $;

import popper from './vendor/@popperjs/core/core.index.js';

import * as bootstrap from './vendor/bootstrap/bootstrap.index.js';
window.bootstrap = bootstrap
import './vendor/bootstrap/dist/css/bootstrap.min.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

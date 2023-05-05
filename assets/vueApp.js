import './styles/vueApp.scss';

// start the Stimulus application
import './bootstrap';

import { registerVueControllerComponents } from '@symfony/ux-vue';

// Registers Vue.js controller components to allow loading them from Twig
//
// Vue.js controller components are components that are meant to be rendered
// from Twig. These component can then rely on other components that won't be
// called directly from Twig.
//
// By putting only controller components in `vue/controllers`, you ensure that
// internal components won't be automatically included in your JS built file if
// they are not necessary.
registerVueControllerComponents(require.context('./components', true, /\.vue$/));

// If you prefer to lazy-load your Vue.js controller components, in order to keep the JavaScript bundle the smallest as possible,
// and improve performance, you can use the following line instead:
//registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/, 'lazy'));

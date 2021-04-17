/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import Vue from 'vue';

/**
 * Import all Vue components loaded using require.context.
 */
function importAll(requireComponents)
{
    requireComponents.keys().forEach((fileName) => {
        // Get the component config
        const componentConfig = requireComponents(fileName)
        // Get the PascalCase version of the component name
        const componentName = fileName
            // Extract filename from path
            .replace(/^.*(\\|\/|\:)/, '')
            // Remove the file extension from the end
            .replace(/\.\w+$/, '')
            // Split up kebabs
            .split('-')
            // Upper case
            .map((kebab) => kebab.charAt(0).toUpperCase() + kebab.slice(1))
            // Concatenated
            .join('');
        // Globally register the component
        Vue.component(componentName, componentConfig.default || componentConfig);
    });
}

// App components.
importAll(require.context('./components', false, /.*\.(vue)$/));
// Plugin components.
importAll(require.context('../plugins', true, /.*\/assets\/components\/.*\.vue$/));


new Vue({
    el: '#app',
    template: '<about/>',
});

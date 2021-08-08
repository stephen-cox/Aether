/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import { createApp } from "vue";
import { createStore } from 'vuex';

const app = createApp({});

// Configure Vuex.
const store = createStore({
  state () {
    return {
      text: 'Text',
    }
  },
  mutations: {
    updateText (state, text) {
      state.text = text;
    }
  }
});
app.use(store);

/**
 * Import all Vue components loaded using require.context.
 */
function importAll(requireComponents) {

  requireComponents.keys().forEach((fileName) => {
    // Get the component config
    const componentConfig = requireComponents(fileName);
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
      app.component(componentName, componentConfig.default || componentConfig);

      console.log('Registered global component: ' + componentName);
    });
}

// App components.
importAll(require.context('./components', false, /.*\.vue$/));

// Plugin components.
importAll(require.context('../plugins', true, /assets\/(?!.*components).*\.vue$/));

app.mount("#app");

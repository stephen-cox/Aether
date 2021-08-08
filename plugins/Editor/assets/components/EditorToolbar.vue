<script>
import Filesystem from '../../../Filesystem/assets/js/filesystem.js';
import EditorToolbarButton from './EditorToolbarButton.vue';
const fs = new Filesystem();

export default {
  name: 'editor-toolbar',
  components: {
    EditorToolbarButton,
  },

  methods: {
    // Open test file.
    openFile() {
      fs.openFile('/test.txt')
      .then(response => {
        console.log(response);
        if (response.status == 'success') {
          this.$store.commit('updateText', response.data.content);
        }
      });
    },

    // Save file.
    async saveFile() {
      console.log(this.$store.state.text);
      fs.saveFile('/test.txt', this.$store.state.text)
      .then(response => {
        console.log(response);
      });
    }
  },
}
</script>

<template>
  <nav class="toolbar">
    <editor-toolbar-button :onClick="openFile">Open</editor-toolbar-button>
    <editor-toolbar-button :onClick="saveFile">Save</editor-toolbar-button>
  </nav>
</template>

<style scoped lang="scss">
  .toolbar {
    background: lightgrey;
  }
</style>

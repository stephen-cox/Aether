<script>
import EditorPane from './components/EditorPane.vue';
import EditorToolbar from './components/EditorToolbar.vue';

export default {
  name: 'editor',
  components: {
    EditorPane,
    EditorToolbar,
  },
  computed: {
    text () {
      return this.$store.state.text;
    }
  }

}
</script>

<template>
  <div class="editor">
    <editor-toolbar class="toolbar"></editor-toolbar>
    <editor-pane class="pane">
      {{ text }}
    </editor-pane>
  </div>
</template>

<style scoped lang="scss">
  .editor {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100vh;
  }
  .toolbar {
    flex: 0 0 2em;
  }
  .pane {
    flex: 2;
  }
</style>

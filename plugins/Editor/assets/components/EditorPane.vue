<script>
  export default {
    name: 'editor-pane',
    computed: {
      value () {
        console.log(this.$store.state.text);
        return this.$store.state.text;
      }
    },
    methods: {
      updateText (e) {
        this.$store.commit('updateText', e.target.value);
      }
    }
  }
</script>

<template>
  <textarea class="pane" @input="updateText">{{ value }}</textarea>
</template>

<style scoped lang="scss">
  .pane {
    height: 100vh;
    width: calc(100% - 20px);
    border: 0;
    margin: 0;
    padding: 20px;
  }
</style>

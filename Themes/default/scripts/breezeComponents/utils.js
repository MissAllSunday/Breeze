Vue.component('message-box', {
    props: ['type'],
    data: function() {
        return {
            fullType: this.type + 'box',
        }
    },
    template: `
    <div v-bind:class="fullType">
        <slot></slot>
        <span class="main_icons remove_button floatright" @click="$emit('close')"></span>
    </div>
  `
})

Vue.use(VueToast, {
    duration: 4000,
    position: 'top'
});
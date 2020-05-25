Vue.component('message-box', {
    props: ['type'],
    template: `
    <div v-bind:class="type">
        <slot></slot>
        <span class="main_icons remove_button floatright" @click="$emit('close')"></span>
    </div>
  `
})


new Vue({
    el: '#breeze_app',
    data: {
        status: null,
        users: null,
        comments: null,
        errored: false,
        loading: true,
        tabs_name: {
            wall: tabs_wall,
            post: tabs_post,
            about: tabs_about,
            activity: tabs_activity,
        }
    },
    created: function() {
        this.fetchStatus();
    },
    methods: {
        fetchStatus: function() {
            axios
                .get(statusURL)
                .then(response => {
                    this.status = response.data.status
                    this.users = response.data.users
                    this.comments = response.data.comments
                    this.loading = false
                })
                .catch(error => {
                    this.errored = true
                    this.loading = false
                })
        },
        getUserData: function (user_id) {
            return this.users[user_id];
        },
        setUserData: function (user_data) {
            this.users = Object.assign({}, this.users, user_data)
        },
        getComments: function (status_id) {
            return this.comments[status_id] || null;
        }
    }
});
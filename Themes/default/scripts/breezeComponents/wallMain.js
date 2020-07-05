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

new Vue({
    el: '#breeze_app',
    data: {
        status: null,
        users: null,
        errored: false,
        notice: null,
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
                    if (response.data.type === 'error')
                    {
                        this.notice = {
                            'message': response.data.message,
                            'type': response.data.type,
                        };
                        this.errored = true;

                        return;
                    }

                    this.status = response.data.status
                    this.users = response.data.users
                    this.loading = false
                })
                .catch(error => {
                    this.errored = true
                    this.loading = false
                    this.notice = {
                        'message': error,
                        'type': 'error',
                    };
                })
        },
        onRemoveStatus: function(statusId){
            Vue.delete(this.status, statusId);
        },
        getUserData: function (user_id) {
            return this.users[user_id];
        },
        setUserData: function (user_data) {
            this.users = Object.assign({}, this.users, user_data)
        },
        clearNotice: function () {
            this.notice = null;
        }
    }
});
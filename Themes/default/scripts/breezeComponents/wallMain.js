ajax_indicator(true);

new Vue({
    el: '#breeze_app',
    data: {
        status: null,
        users: null,
        errored: false,
        notice: null,
        loading: true,
        tabs_name: {
            wall: window.tabs_wall,
            post: window.tabs_post,
            about: window.tabs_about,
            activity: window.tabs_activity,
        },
        oBreeze_editorOptions: window.oBreeze_editorOptions,
    },
    created: function() {
        this.fetchStatus();
    },
    methods: {
        fetchStatus: function() {
            axios
                .post(statusURL + smf_session_var +'='+ smf_session_id,
                    {
                        status_owner_id: wall_owner_id
                    })
                .then(response => {
                    if (response.data.type)
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
                })
                .catch(error => {
                    this.errored = true
                    this.notice = {
                        'message': error.message,
                        'type': 'error',
                    };
                }).then(() => {
                this.loading = false
                ajax_indicator(false);
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
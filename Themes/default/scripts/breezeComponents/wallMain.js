new Vue({
    el: '#breeze_app',
    data: {
        status: null,
        users: null,
        comments: null,
        errored: false,
        loading: true,
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
        getUserData: function (userId) {
            return this.users[userId];
        }
    }
})
Vue.component('editor', {
    props: ['editorOptions'],
    data: function() {
        return {
            notice: null,
            place_holder: 'say something!',
            post_status: {
                status_owner_id: wall_owner_id,
                status_poster_id: smf_member_id,
                status_body: ''
                ,
            },
        }
    },
    template: `<div>
    `,
    methods: {
    }
})

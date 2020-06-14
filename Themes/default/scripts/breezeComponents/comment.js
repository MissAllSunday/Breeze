Vue.component('comment', {
    props: ['comment', 'comment_poster_data'],
    template: `
    <div>
        <div class='floatleft comment_user_info'>
            <div class='breeze_avatar avatar_comment' v-bind:style='avatarImage(comment_poster_data.avatar.href)'></div>
            <h4 v-html='comment_poster_data.link_color'></h4>
        </div>
        <div class='comment_date_info floatright smalltext'>
            {{comment.comments_time | formatDate}}
            <span class="main_icons remove_button floatright" v-on:click="deleteComment()"></span>
        </div>
        <div class='clear comment_content'>
            <hr>
            {{comment.comments_body}}
        </div>
    </div>`,
    filters: {
        formatDate: function(unixTime) {
            return moment.unix(unixTime).format('LLLL')
        },
    },
    methods: {
        avatarImage: function (posterImageHref) {
            return { backgroundImage: 'url(' + posterImageHref + ')' }
        },
        deleteComment: function () {
            if (!confirm(smf_you_sure))
                return;

            axios.post(smf_scripturl + '?action=breezeComment;sa=deleteComment;'+ smf_session_var +'='+ smf_session_id,
                {
                    comments_id: this.comment.comments_id,
                    comments_poster_id: this.comment_poster_data.id
                }).then(response => {

                console.log(response);
            });
        }
    }
})

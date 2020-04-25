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
        }
    }
})

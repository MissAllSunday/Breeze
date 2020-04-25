Vue.component('status', {
    props: ['status_item', 'poster_data'],
    template: "<div>" +
        "<div class='breeze_avatar floatleft' v-bind:style='avatarImage(poster_data.avatar.href)'></div>" +
        "<div class='windowbg'>" +
        "<h4 class='floatleft' v-html='poster_data.link_color'></h4>" +
        "<div class='floatright smalltext'>{{status_item.status_time | formatDate}}</div><br>" +
        "<div class='content'><hr>{{status_item.status_body}}</div>" +
        "</div>" +
        "</div>",
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

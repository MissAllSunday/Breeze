Vue.component('releases-feed', {
    props: ['release'],
    template: "<div>" +
        "<a :href='release.html_url'>{{ release.name }}</a> | <span" +
        " class='smalltext'>{{release.published_at | formatDate}}</span><br>" +
        "{{ release.body }}" +
        "</div>",
    filters: {
        formatDate: function(releaseDate) {
            return moment(String(releaseDate)).format('LLLL')
        },
    },
})


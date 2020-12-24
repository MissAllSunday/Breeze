new Vue({
	el: '#live_news',
	data: {
		releases: null,
		releasesNotFound: releasesNotFound,
		errored: false
	},
	created: function () {
		this.fetchData();
	},
	methods: {
		fetchData: function () {
			axios
				.get(feedURL)
				.then(response => {
					this.releases = response.data.slice(0, 5)
				})
				.catch(error => {
					this.errored = true
				})
		}
	}
})

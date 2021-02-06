new Vue({
	el: '#moodList',
	mixins: [breezeUtils],
	data: {
		isCreatingNewMood: false,
		errored: false,
		localMoods:  {},
		txt: window.breezeTxtGeneral,
	},
	created: function () {
		this.fetchAllMoods();
		this.txt.mood = window.breezeTxtMood;
	},
	methods: {
		fetchAllMoods: function () {
			let selfVue = this

			selfVue.api
				.get(selfVue.sprintFormat(selfVue.baseUrl, [this.actions.mood ,this.subActions.mood.all]))
				.then(function(response) {
					Object.values(response.data).map(function(mood) {
						mood.emoji = selfVue.decode(mood.emoji)
						mood.isActive = Boolean(Number(mood.isActive))
						mood.id = Number(mood.id)

						return mood
					})

					selfVue.localMoods = response.data
				})
				.catch(function(error) {
					let selfVue = this;

					selfVue.errored = true
				})
		},
		decode: function (html) {
			let decoder = document.createElement('div');
			decoder.innerHTML = html;
			return decoder.textContent;
		},
		removeMood: function (moodId) {
			Vue.delete(this.localMoods, moodId);
		},
		updateList(mood) {
			this.localMoods = Object.assign({}, this.localMoods, mood);
		}
	}
})

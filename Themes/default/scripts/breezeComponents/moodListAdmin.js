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
			let selfVue = this;
			let localMoods;

			selfVue.api
				.get(selfVue.sprintFormat(selfVue.baseUrl, [this.actions.mood ,this.subActions.mood.all]))
				.then(function(response) {
					localMoods = selfVue.parseMoods(response.data);

					selfVue.localMoods = localMoods;
				})
				.catch(function(error) {
					selfVue.errored = true
				})
		},
		removeMood: function (moodId) {
			Vue.delete(this.localMoods, moodId);
		},
		updateList(mood) {
			this.localMoods = Object.assign({}, this.localMoods, mood);
		}
	}
})

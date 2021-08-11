new Vue({
	el: '#moodList',
	mixins: [breezeUtils],
	data: {
		isCreatingNewMood: false,
		errored: false,
		localMoods:  {},
		txt: window.breezeTxtGeneral,
		showModal: false,
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
					selfVue.localMoods = selfVue.parseMoods(response.data);
				})
				.catch(function(error) {
					selfVue.errored = true
				})
		},
		removeMood: function (moodId) {
			Vue.delete(this.localMoods, moodId);
		},
		updateList(mood) {
			this.localMoods = Object.assign({}, this.localMoods, {mood});
		},
		creating: function () {
			this.showModal = true
		},
		onSave: function (mood){
			let selfVue = this

			if (!selfVue.$root.ownConfirm()) {
				return;
			}

			selfVue.$root.api.post(selfVue.$root.sprintFormat(selfVue.$root.baseUrl,
					[selfVue.$root.actions.mood, selfVue.$root.subActions.mood.post]),
				mood
			).then(response => {
				selfVue.$root.setNotice(response.data)

				if (response.data.type !== 'error') {
					selfVue.showModal = false
					selfVue.updateList(mood);console.log(this.localMoods)
				}
			});
		},
	}
})

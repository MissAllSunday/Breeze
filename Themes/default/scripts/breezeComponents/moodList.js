new Vue({
	el: '#moodList',
	data: {
		errored: false,
		localMoods:  {
			type: Object,
			default: function () {
				return {}
			},
		},
		editingMood: {}
	},
	props: {
		moodsURL: {
			type: String,
			required: false,
			default: smf_scripturl + '?action=breezeMood;sa=getAllMoods;'+ smf_session_var +'='+ smf_session_id
		},
		isCreatingNewMood: false
	},
	created: function () {
		this.fetchAllMoods();
	},
	methods: {
		fetchAllMoods: function () {
			let selfVue = this;
			axios
				.get(selfVue.moodsURL)
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
		onCreate: function (){
			this.isCreatingNewMood = true
		}
	}
})

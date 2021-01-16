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
	},
	created: function () {
		this.fetchAllMoods();
	},
	methods: {
		fetchAllMoods: function () {
			axios
				.get(this.moodsURL)
				.then(response => {
					let moodCount = Object.keys(response.data).length
					for (let i = 1; i <= moodCount; i++) {
						response.data[i].emoji = this.decode(response.data[i].emoji)
						response.data[i].isActive = Boolean(Number(response.data[i].isActive))
						response.data[i].id = Number(response.data[i].id)
					}

					this.localMoods = response.data
				})
				.catch(error => {
					this.errored = true
				})
		},
		decode: function (html) {
			let decoder = document.createElement('div');
			decoder.innerHTML = html;
			return decoder.textContent;
		},
		removeMood: function (moodId) {console.log(moodId)
			Vue.delete(this.localMoods, moodId);
		},
	}
})

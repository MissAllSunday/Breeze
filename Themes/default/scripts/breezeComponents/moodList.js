new Vue({
	el: '#moodList',
	data: {
		editing: false,
		errored: false
	},
	props: {
		moodsURL: {
			type: String,
			required: false,
			default: smf_scripturl + '?action=breezeMood;sa=getActiveMoods;'+ smf_session_var +'='+ smf_session_id
		},
		localMoods:  {
			type: Object,
			default: function () {
				return {}
			},
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
					this.localMoods = response.data
				})
				.catch(error => {
					this.errored = true
				})
		}
	}
})

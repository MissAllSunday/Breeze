Vue.component('mood-edit-modal', {
	template: '#mood-edit-modal',
})

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
			default: smf_scripturl + '?action=breezeMood;sa=getActiveMoods;'+ smf_session_var +'='+ smf_session_id
		},
	},
	created: function () {
		this.fetchAllMoods();
	},
	methods: {
		onEditingMood: function (moodId){

		},
		getEditingMood: function (moodId){
			let tempObj = this.localMoods;
			for (var i = 0; i < tempObj.length; i++) {
				if (tempObj[i].moods_id === moodId) {
					return tempObj[i];
				}
			}
		},
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

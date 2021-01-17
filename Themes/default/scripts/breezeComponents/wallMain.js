ajax_indicator(true);
Vue.prototype.$sanitize = DOMPurify.sanitize;

new Vue({
	el: '#breeze_app',
	props: {
		baseUrl: '',
		subActions: {
			type: Object,
			default: function (){
				return {
					postStatus: 'postStatus',
					statusByProfile: 'statusByProfile',
				}
			},
		},
		api : null
	},
	data: {
		loading: false,
		status: null,
		errored: false,
		notice: null,
		postData: {
			type: Object,
			default: function () {
				return {
					status_owner_id: 0,
					status_poster_id: 0,
					status_body: '',
				}
			},
		},
		txt:  {
			type: Object,
			default: function () {
				return {}
			},
		},
		users:  {
			type: Object,
			default: function () {
				return {}
			},
		},
	},
	beforeCreate: function () {
		this.api = axios
		this.baseUrl = smf_scripturl + '?action=breezeStatus;' + smf_session_var +'='+ smf_session_id + ';sa='
		this.txt = window.breezeTxtGeneral
	},
	created: function () {
		this.$set(this.postData, 'status_owner_id', window.breezeUsers.wallOwner)
		this.$set(this.postData, 'status_poster_id', window.breezeUsers.wallPoster)
		this.fetchStatus()
	},
	methods: {
		editorId: function () {
			return 'breeze_status';
		},
		postStatus: function (editorContent) {
			let selfVue = this
			this.postData.status_body = editorContent

			this.api.post(this.baseUrl + this.subActions.postStatus,
				this.postData
			).then(function(response) {
				selfVue.setNotice(response.data.message, response.data.type);

				if (response.data.content) {
					selfVue.setUserData(response.data.content.users)
					selfVue.setStatus(response.data.content.status);
				}

			}).catch(function(error) {
				selfVue.setNotice(error.response);
			});
		},
		setStatus: function (newStatus) {
			this.status = Object.assign({}, this.status, newStatus);
		},
		fetchStatus: function () {
			let selfVue = this

			axios.post(this.baseUrl + this.subActions.statusByProfile,
				this.postData
			).then(function(response) {
				if (response.data.type) {
					selfVue.notice = {
						'message': response.data.message,
						'type': response.data.type,
					};
					selfVue.errored = true;

					return;
				}

				selfVue.status = response.data.status
				selfVue.users = response.data.users
			}).catch(function(error) {
				selfVue.errored = true
				selfVue.notice = {
					'message': error.message,
					'type': 'error',
				};
			}).then(function () {
				ajax_indicator(false);
			})
		},
		onRemoveStatus: function (statusId) {
			Vue.delete(this.status, statusId);
		},
		getUserData: function (user_id) {
			return this.users[user_id];
		},
		setUserData: function (user_data) {
			this.users = Object.assign({}, this.users, user_data)
		},
		setNotice: function (message, type) {
			type = type || 'error';
			let selfVue = this;
			this.notice = true;

			Vue.$toast.open({
				message: message,
				type: type,
				onClose: function () {
					selfVue.clearNotice();
				}
			});
		},
		clearNotice: function () {
			this.notice = null;
		},
	}
});

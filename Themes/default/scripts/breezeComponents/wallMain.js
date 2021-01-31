ajax_indicator(true);

new Vue({
	el: '#breeze_app',
	mixins: [breezeUtils],
	data: {
		loading: false,
		status: null,
		errored: false,
		notice: null,
		users:  {},
	},
	created: function () {
		this.fetchStatus()
	},
	methods: {
		postStatus: function (editorContent) {
			let selfVue = this

			this.api.post(this.sprintFormat(this.baseUrl, [this.actions.status ,this.subActions.pStatus]),
				{
					wallId: selfVue.wallData.ownerId,
					userId: selfVue.wallData.posterId,
					body: editorContent,
				}
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

			selfVue.api.post(this.sprintFormat(this.baseUrl, [this.actions.status ,this.subActions.statusByProfile]),
				{wallId: selfVue.wallData.ownerId}
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
				selfVue.setUserData(response.data.users)
			}).catch(function(error) {
				selfVue.errored = true
				selfVue.notice = {
					'message': error.message,
					'type': 'error',
				};
			}).then(function () {
				ajax_indicator(false);
				this.loading = false
			})
		},
		onRemoveStatus: function (statusId) {
			Vue.delete(this.status, statusId);
		},
	}
});

ajax_indicator(true);

new Vue({
	el: '#breeze_app',
	mixins: [breezeUtils],
	data: {
		txt:  window.breezeTxtGeneral,
		txtMood:  window.breezeTxtMood,
		status: null,
		errored: false,
		notice: null,
		users:  {},
		wallData: {
			ownerId: window.breezeUsers.wallOwner || 0,
			posterId: window.breezeUsers.wallPoster || 0,
		},
		currentMoodId: window.breezeProfileOwnerSettings.moodId || 0,
	},
	created: function () {
		this.fetchStatus()
	},
	methods: {
		postStatus: function (editorContent) {
			let selfVue = this

			this.api.post(this.sprintFormat(this.baseUrl,
				[this.actions.status ,this.subActions.status.post]),
				{
					wallId: selfVue.wallData.ownerId,
					userId: selfVue.wallData.posterId,
					body: editorContent,
				}
			).then(function(response) {console.log(response)
				selfVue.setNotice(response.data);

				if (response.data.content) {
					selfVue.setUserData(response.data.content.users)
					selfVue.setStatus(response.data.content.status);
				}

			}).catch(function(error) {
				selfVue.setNotice = {
					'message': error.message,
				};
			});
		},
		setStatus: function (newStatus) {
			this.status = Object.assign({}, this.status, newStatus);
		},
		fetchStatus: function () {
			let selfVue = this

			selfVue.api.post(this.sprintFormat(this.baseUrl,
				[this.actions.status ,this.subActions.status.byProfile]),
				{wallId: selfVue.wallData.ownerId}
			).then(function(response) {
				if (response.data.type) {
					selfVue.setNotice(response.data);
					selfVue.errored = true;

					return;
				}

				selfVue.status = response.data.status
				selfVue.setUserData(response.data.users)
			}).catch(function(error) {
				selfVue.errored = true
				selfVue.setNotice = {
					'message': error.message,
				};
			}).then(function () {
				selfVue.clearLoading()
			})
		},
		onRemoveStatus: function (statusId) {
			Vue.delete(this.status, statusId);
		},
		onSetNewUsers: function (newUsers){
			this.setUserData(newUsers)
		}
	}
});

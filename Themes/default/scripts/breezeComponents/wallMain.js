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
		isCurrentUserOwner: window.breezeIsCurrentUserOwner,
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
			let selfVue = this

			selfVue.status = Object.assign({}, selfVue.status, selfVue.parseStatus(newStatus));
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

				selfVue.status = selfVue.parseStatus(response.data.status)
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
		},
		parseStatus: function (status){
			let selfUtils = this;

			Object.values(status).map(function(singleStatus) {
				singleStatus.body = selfUtils.decode(singleStatus.body)
				singleStatus.formatedDate = selfUtils.formatDate(singleStatus.createdAt)

				return singleStatus
			});

			return status;
		},
	}
});

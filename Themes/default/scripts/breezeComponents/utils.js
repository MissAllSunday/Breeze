Vue.component('message-box', {
	props: ['type'],
	data: function() {
		return {
			fullType: this.type + 'box',
		}
	},
	template: `
	<div v-bind:class="fullType">
		<slot></slot>
		<span class="main_icons remove_button floatright" @click="$emit('close')"></span>
	</div>
  `
})

Vue.use(VueToast, {
	duration: 4000,
	position: 'top'
});

let breezeUtils = {
	data: function () {
		return {
			api : axios,
			sanitize: DOMPurify.sanitize,
			baseUrl: smf_scripturl + '?action={0};' + smf_session_var +'='+ smf_session_id + ';sa={1}',
			actions: {
				comment: 'breezeComment',
				status: 'breezeStatus',
				mood: 'breezeMood'
			},
			subActions: {
				status: {
					post: 'postStatus',
					byProfile: 'statusByProfile',
					delete: 'deleteStatus',
				},
				comment: {
					post: 'postComment',
					delete: 'deleteComment',
				},
				mood: {
					all: 'getAllMoods',
					delete: 'deleteMood',
					post: 'postMood'
				}
			},
		}
	},
	methods: {
		ownConfirm: function (){
			return confirm(smf_you_sure)
		},
		sprintFormat: function (str, arrayArguments) {
			let i = arrayArguments.length

			while (i--) {
				str = str.replace(new RegExp('\\{' + i + '\\}', 'gm'), arrayArguments[i]);
			}

			return str;
		},
		getUserData: function (user_id) {
			return this.users[user_id];
		},
		setUserData: function (userData) {
			this.users = Object.assign({}, this.users, userData)
		},
		getUserAvatar: function (userId) {
			let userData = this.getUserData(userId)

			return { backgroundImage: 'url(' + userData.avatar.href + ')' }
		},
		getUserLink: function (userId){
			let userData = this.getUserData(userId)

			return userData.link_color
		},
		getWallData: function (){
			return this.wallData
		},
		setNotice: function (options, onCloseCallback) {
			let type = options.type || 'error';
			let message = options.message || '';
			let selfVue = this;

			Vue.$toast.open({
				message: message,
				type: type,
				onClose: function () {
					onCloseCallback();
				}
			});
		},
		clearNotice: function () {
			Vue.$toast.clear();
		},
		formatDate: function (unixTime) {
			return moment.unix(unixTime).format('lll')
		},
		setLoading: function () {
			ajax_indicator(true);
		},
		clearLoading: function () {
			ajax_indicator(false);
		}
	},
}


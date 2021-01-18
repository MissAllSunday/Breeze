Vue.component('status', {
	props: ['status_item', 'poster_data', 'users'],
	data: function () {
		return {
			localComments: Object.assign({}, this.status_item.comments),
			comment_message: '',
			notice: null,
			place_holder: 'leave a comment',
			post_comment: {
				comments_poster_id: smf_member_id,
				comments_status_owner_id: parseInt(this.status_item.status_poster_id),
				comments_profile_id: parseInt(this.status_item.status_owner_id),
				comments_status_id: parseInt(this.status_item.status_id),
				comments_body: '',
			},
		}
	},
	template: `<li>
	<div class='breeze_avatar avatar_status floatleft' v-bind:style='avatarImage(poster_data.avatar.href)'></div>
		<div class='windowbg'>
			<h4 class='floatleft' v-html='poster_data.link_color'></h4>
			<div class='floatright smalltext'>
				{{status_item.status_time | formatDate}}
				&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteStatus()"></span>
			</div>
			<br>
			<div class='content'>
				<hr>
				{{status_item.status_body | decodedContent}}
			</div>
			<comment
				v-for ='comment in localComments'
				v-bind:comment='comment'
				v-bind:comment_poster_data='getUserData(comment.comments_poster_id)'
				v-bind:key='comment.comments_id'
				@removeComment='removeComment'
				class='windowbg'>
			</comment>
			<div v-if ='notice === null'  class='comment_posting'>
				<editor
					v-bind:editor_id='editorId()'
					v-on:get-content='postComment'>
				</editor>
			</div>
		</div>
	</li>`,
	filters: {
		formatDate: function (unixTime) {
			return moment.unix(unixTime).format('lll')
		},
	},
	methods: {
		editorId: function () {
			return 'breeze_status_' + this.status_item.status_id;
		},
		avatarImage: function (posterImageHref) {
			return { backgroundImage: 'url(' + posterImageHref + ')' }
		},
		getUserData: function (userId) {
			return this.users[userId];
		},
		setLocalComment: function (comments) {
			this.localComments = Object.assign({}, this.localComments, comments)
		},
		clearPostComment: function () {
			this.post_comment.comments_body = '';
		},
		postComment: function (editorContent) {
			let selfVue = this
			this.$root.clearNotice();
			this.post_comment.comments_body = editorContent;

			selfVue.$root.api.post(
				this.format(this.$root.baseUrl, [this.$root.actions.comment ,this.$root.subActions.pComment]),
				this.post_comment
			).then(function (response) {
				selfVue.$root.setNotice(response.data.message, response.data.type);

				if (response.data.content) {
					selfVue.$root.setUserData(response.data.content.users)
					selfVue.setLocalComment(response.data.content.comments);
				}

				selfVue.clearPostComment();
			}).catch(function (error) {
				selfVue.$root.setNotice(error.response.statusText);
			});
		},
		removeComment: function (commentId) {
			Vue.delete(this.localComments, commentId);
		},
		deleteStatus: function () {
			let selfVue = this

			if (!confirm(smf_you_sure)) {
				return;
			}

			selfVue.$root.api.post(
				selfVue.$root.format(selfVue.$root.baseUrl,
				[
					selfVue.$root.actions.status ,
					selfVue.$root.subActions.dStatus]
				),
				selfVue.status_item
			).then(function (response) {
				Vue.$toast.open({
					message: response.data.message,
					type: response.data.type,
				});

				if (response.data.type !== 'error') {
					selfVue.$emit('remove_status', selfVue.status_item.status_id);
				}
			});
		}
	}
})

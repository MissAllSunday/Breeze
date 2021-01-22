Vue.component('status', {
	mixins: [breezeUtils],
	props: ['item'],
	data: function () {
		return {
			localComments: Object.assign({}, this.item.comments),
			comment: {
				comments_poster_id: 0,
				comments_status_owner_id: parseInt(this.item.status_poster_id),
				comments_profile_id: parseInt(this.item.status_owner_id),
				comments_status_id: parseInt(this.item.status_id),
				comments_body: '',
			},
		}
	},
	template: `<li>
	<div class='breeze_avatar avatar_status floatleft' :style='getUserAvatar(item.status_owner_id)'></div>
		<div class='windowbg'>
			<h4 class='floatleft' v-html='getPosterLink()'></h4>
			<div class='floatright smalltext'>
				{{ formatDate(status.status_time) }}
				&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteStatus()"></span>
			</div>
			<br>
			<div class='content'>
				<hr>
				{{ status.status_body }}
			</div>
			<comment
				v-for ='comment in localComments'
				v-bind:comment='comment'
				v-bind:key='comment.comments_id'
				@removeComment='removeComment'
				class='windowbg'>
			</comment>
			<div v-if ='notice === null'  class='comment_posting'>
				<editor
					editor_id='editorId()'
					v-on:get-content='postComment'>
				</editor>
			</div>
		</div>
	</li>`,
	created: function (){

	},
	methods: {
		setUsers: function (users) {
			this.users = users
		},
		editorId: function () {
			return 'breeze_status_' + this.item.status_id;
		},
		setLocalComment: function (comments) {
			this.localComments = Object.assign({}, this.localComments, comments)
		},
		clearCommentBody: function () {
			this.comment.comments_body = '';
		},
		setCommentBody: function (bodyString) {
			this.comment.comments_body = bodyString;
		},
		postComment: function (editorContent) {
			let selfVue = this
			selfVue.clearNotice();
			selfVue.setCommentBody(editorContent);

			selfVue.api.post(
				selfVue.sprintFormat(selfVue.baseUrl, [
					selfVue.actions.comment, selfVue.subActions.pComment
				]),
				selfVue.comment
			).then(function (response) {
				selfVue.setNotice(response.data.message, response.data.type);

				if (response.data.content) {
					selfVue.setUserData(response.data.content.users)
					selfVue.setLocalComment(response.data.content.comments);
				}

				selfVue.clearCommentBody();
			}).catch(function (error) {
				selfVue.setNotice(error.response.statusText);
			});
		},
		removeComment: function (commentId) {
			Vue.delete(this.localComments, commentId);
		},
		deleteStatus: function () {
			let selfVue = this

			if (!selfVue.ownConfirm()) {
				return;
			}

			selfVue.api.post(
				selfVue.sprintFormat(selfVue.baseUrl,
				[
					selfVue.actions.status ,
					selfVue.subActions.dStatus]
				),
				selfVue.status
			).then(function (response) {
				selfVue.setNotice(
					response.data.message,
					response.data.type
				);

				if (response.data.type !== 'error') {
					selfVue.$emit('remove_status', selfVue.item.status_id);
				}
			});
		}
	}
})

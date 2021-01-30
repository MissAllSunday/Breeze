Vue.component('status', {
	mixins: [breezeUtils],
	props: ['item', 'users'],
	data: function () {
		return {
			localComments: Object.assign({}, this.item.comments),
			comment: {
				userId: 0,
				statusId: parseInt(this.item.id),
				body: '',
			},
		}
	},
	template: `<li>
	<div class='breeze_avatar avatar_status floatleft' :style='getUserAvatar(item.wallId)'></div>
		<div class='windowbg'>
			<h4 class='floatleft' v-html='getUserLink(this.item.wallId)'></h4>
			<div class='floatright smalltext'>
				{{ formatDate(item.status_time) }}
				&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteStatus()"></span>
			</div>
			<br>
			<div class='content'>
				<hr>
				{{ item.status_body }}
			</div>
			<comment
				v-for ='comment in localComments'
				v-bind:comment='comment'
				v-bind:key='comment.id'
				@removeComment='removeComment'
				class='windowbg'>
			</comment>
			<div v-if ='notice === null'  class='comment_posting'>
				<editor
					:editor_id='getEditorId()'
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
		getEditorId: function () {
			return 'breeze_status_' + this.item.id;
		},
		setLocalComment: function (comments) {
			this.localComments = Object.assign({}, this.localComments, comments)
		},
		clearCommentBody: function () {
			this.comment.body = '';
		},
		setCommentBody: function (bodyString) {
			this.comment.body = bodyString;
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
					selfVue.$emit('remove_status', selfVue.item.id);
				}
			});
		}
	}
})

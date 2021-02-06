Vue.component('status', {
	mixins: [breezeUtils],
	props: ['item', 'users'],
	data: function () {
		return {
			localComments: Object.assign({}, this.item.comments),
		}
	},
	template: `<li>
	<div class='breeze_avatar avatar_status floatleft' :style='getUserAvatar(item.userId)'></div>
		<div class='windowbg'>
			<h4 class='floatleft' v-html='getUserLink(this.item.userId)'></h4>
			<div class='floatright smalltext'>
				{{ formatDate(item.createdAt) }}
				&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteStatus()"></span>
			</div>
			<br>
			<div class='content'>
				<hr>
				{{ item.body }}
			</div>
			<comment
				v-for ='comment in localComments'
				v-bind:item='comment'
				v-bind:key='comment.id'
				:users="users"
				@remove-comment='removeComment($event)'>
			</comment>
			<div class='comment_posting'>
				<editor
					:editor_id='getEditorId()'
					:options='commentEditorOptions()'
					@get-content='postComment($event)'>
				</editor>
			</div>
		</div>
	</li>`,
	methods: {
		getEditorId: function () {
			return 'breeze_status_' + this.item.id;
		},
		setLocalComment: function (comments) {
			this.localComments = Object.assign({}, this.localComments, comments)
		},
		postComment: function (editorContent) {
			let selfVue = this
			selfVue.clearNotice()
			selfVue.setLoading()

			selfVue.api.post(
				selfVue.sprintFormat(selfVue.baseUrl, [
					selfVue.actions.comment, selfVue.subActions.comment.post
				]),
				{
					userId: this.$root.wallData.posterId,
					statusId: selfVue.item.id,
					body: editorContent,
				}
			).then(function (response) {
				selfVue.setNotice(response.data);
				selfVue.clearLoading()

				if (response.data.content) {
					selfVue.$emit('set-new-users', response.data.content.users)
					selfVue.setLocalComment(response.data.content.comments);
				}
			}).catch(function (error) {
				selfVue.setNotice({
					message: error.response.statusText
				});
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

			selfVue.setLoading()
			selfVue.api.post(
				selfVue.sprintFormat(selfVue.baseUrl,
				[
					selfVue.actions.status ,
					selfVue.subActions.status.eliminate]
				),
				selfVue.item
			).then(function (response) {
				selfVue.setNotice(response.data);
				selfVue.clearLoading()

				if (response.data.type !== 'error') {
					selfVue.$emit('remove-status', selfVue.item.id);
				}
			});
		},
		commentEditorOptions: function (){
			return {
				mode: 'inline',
				minHeight : '50px',
				height : '50px',
			}
		}
	}
})

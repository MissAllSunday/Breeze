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
				<div v-html="$sanitize(status_item.status_body)"></div>
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
						this.$root.clearNotice();
						this.post_comment.comments_body = editorContent;

						axios.post(
							smf_scripturl + '?action=breezeComment;sa=postComment;'+ smf_session_var +'='+ smf_session_id,
							this.post_comment
						).then(response => {

								this.$root.setNotice(response.data.message, response.data.type);

							if (response.data.content) {
								this.$root.setUserData(response.data.content.users)
								this.setLocalComment(response.data.content.comments);
							}

								this.clearPostComment();
						}).catch(error => {
							this.$root.setNotice(error.response.statusText);
						});
					},
					removeComment: function (commentId) {
						Vue.delete(this.localComments, commentId);
					},
					deleteStatus: function () {
						if (!confirm(smf_you_sure)) {
							return;
						}

						axios.post(
							smf_scripturl + '?action=breezeStatus;sa=deleteStatus;'+ smf_session_var +'='+ smf_session_id,
							{
								status_id: this.status_item.status_id,
								status_poster_id: this.status_item.status_poster_id
							}
						).then(response => {

							Vue.$toast.open({
								message: response.data.message,
								type: response.data.type,
									});

						if (response.data.type !== 'error') {
							this.$emit('remove_status', this.status_item.status_id);
						}
						});
					}
			}
})

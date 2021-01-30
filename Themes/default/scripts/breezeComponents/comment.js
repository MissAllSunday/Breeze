Vue.component('comment', {
	mixins: [breezeUtils],
	props: ['comment'],
	data: function () {
		return {
			notice: null,
		}
	},
	template: `
	<div>
		<div class='floatleft comment_user_info'>
			<div class='breeze_avatar avatar_comment' :style='getUserAvatar(comment_poster_data.avatar.href)'></div>
			<h4 v-html='getUserLink()'></h4>
		</div>
		<div class='comment_date_info floatright smalltext'>
			{{ formatDate(comment.comments_time) }}
			&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteComment()"></span>
		</div>
		<div class='clear comment_content'>
			<hr>
			<div v-html="comment.body"></div>
		</div>
	</div>`,
	methods: {
		deleteComment: function () {
			let selfVue = this

			if (!this.ownConfirm()) {
				return;
			}

			selfVue.api.post(selfVue.format(selfVue.baseUrl,
				[
					selfVue.actions.comment,
					selfVue.subActions.dComment
				]),
				selfVue.comment
			).then(function (response) {
				selfVue.setNotice(
					response.data.message,
					response.data.type
				);

				if (response.data.type !== 'error') {
					selfVue.removeComment();
				}
			});
		},
		removeComment: function () {
			this.$emit('removeComment', this.comment.id);
		}
	}
})

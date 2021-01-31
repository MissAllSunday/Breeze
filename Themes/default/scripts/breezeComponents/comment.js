Vue.component('comment', {
	mixins: [breezeUtils],
	props: ['item', 'users'],
	template: `
	<div class="windowbg">
		<div class='floatleft comment_user_info'>
			<div class='breeze_avatar avatar_comment' :style='getUserAvatar(item.userId)'></div>
			<h4 v-html='getUserLink(this.item.userId)'></h4>
		</div>
		<div class='comment_date_info floatright smalltext'>
			{{ formatDate(item.createdAt) }}
			&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteComment()"></span>
		</div>
		<div class='clear comment_content'>
			<hr>
			<div v-html="item.body"></div>
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
				this.item
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
			this.$emit('remove-comment', this.item.id);
		}
	}
})

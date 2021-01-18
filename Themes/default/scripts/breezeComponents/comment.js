Vue.component('comment', {
	props: ['comment', 'comment_poster_data'],
	data: function () {
		return {
			notice: null,
		}
	},
	template: `
	<div>
		<div class='floatleft comment_user_info'>
			<div class='breeze_avatar avatar_comment' v-bind:style='avatarImage(comment_poster_data.avatar.href)'></div>
			<h4 v-html='comment_poster_data.link_color'></h4>
		</div>
		<div class='comment_date_info floatright smalltext'>
			{{comment.comments_time | formatDate}}
			&nbsp;<span class="main_icons remove_button floatright" v-on:click="deleteComment()"></span>
		</div>
		<div class='clear comment_content'>
			<hr>
			<div v-html="$sanitize(comment.comments_body)"></div>
		</div>
	</div>`,
	filters: {
		formatDate: function (unixTime) {
			return moment.unix(unixTime).format('lll')
		},
		},
		methods: {
			avatarImage: function (posterImageHref) {
				return { backgroundImage: 'url(' + posterImageHref + ')' }
			},
			deleteComment: function () {
				let selfVue = this

				if (!confirm(smf_you_sure)) {
					return;
				}

				selfVue.$root.api.post(selfVue.$root.format(selfVue.$root.baseUrl,
					[
						selfVue.$root.actions.comment,
						selfVue.$root.subActions.dComment
					]),
					selfVue.comment
				).then(function (response) {
					Vue.$toast.open({
						message: response.data.message,
						type: response.data.type,
					});

					if (response.data.type !== 'error') {
						selfVue.removeComment();
					}
				});
			},
			removeComment: function () {
				this.$emit('removeComment', this.comment.comments_id);
			}
		}
})

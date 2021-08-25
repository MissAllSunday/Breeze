<template>
	<div class="windowbg">
		<div class='floatleft comment_user_info'>
			<div class='breeze_avatar avatar_comment' :style='getUserAvatar(item.userId)'></div>
			<h4 v-html='getUserLink(this.item.userId)'></h4>
		</div>
		<div class='comment_date_info floatright smalltext'>
			{{ formatDate(item.createdAt) }}
			&nbsp;<span class="main_icons remove_button floatright pointer_cursor" v-on:click="deleteComment()"></span>
		</div>
		<div class='clear comment_content'>
			<hr>
			<span v-html="item.body"></span>
			<like
				:like-item="buildLikeItem()"
				:current-user-id="$root.wallData.posterId"
			></like>
		</div>
	</div>
</template>

<script>
import utils from "@/utils";

export default {
	name: "Comment",
	mixins: [utils],
	props: ['item', 'users'],
	methods: {
		buildLikeItem: function (){
			let selfVue = this

			selfVue.item.likesInfo.type = 'br_com'

			return selfVue.item.likesInfo
		},
		deleteComment: function () {
			let selfVue = this

			if (!selfVue.ownConfirm()) {
				return;
			}

			selfVue.setLoading()
			selfVue.api.post(selfVue.sprintFormat(selfVue.baseUrl,
					[
						selfVue.actions.comment,
						selfVue.subActions.comment.eliminate
					]),
				selfVue.item
			).then(function (response) {
				selfVue.clearLoading()
				selfVue.setNotice(response.data);

				if (response.data.type !== 'error') {
					selfVue.removeComment();
				}
			});
		},
		removeComment: function () {
			this.$emit('remove-comment', this.item.id);
		}
	}
}
</script>

<style scoped>

</style>

<template>
	<li>
		<div class='breeze_avatar avatar_status floatleft' :style='getUserAvatar(item.userId)'></div>
		<div class='windowbg'>
			<h4 class='floatleft' v-html='getUserLink(this.item.userId)'></h4>
			<div class='floatright smalltext'>
				{{ item.formatedDate }}
				&nbsp;<span class="main_icons remove_button floatright pointer_cursor" v-on:click="deleteStatus()"></span>
			</div>
			<br>
			<div class='content'>
				<hr>
				<span v-html="item.body"></span>
				<Like
					:like-item="buildLikeItem()"
					:current-user-id="$root.wallData.posterId"
				></Like>
			</div>
			<Comment
				v-for ='comment in localComments'
				v-bind:item='comment'
				v-bind:key='comment.id'
				:users="users"
				@remove-comment='removeComment($event)'>
			</Comment>
			<div class='comment_posting'>
				<Editor
					:editor_id='getEditorId()'
					:options='commentEditorOptions()'
					@get-content='postComment($event)'>
				</Editor>
			</div>
		</div>
	</li>
</template>

<script>
import Editor from "@/components/Editor";
import Comment from "@/components/Comment";
import Like from "@/components/Like";
import utils from "@/utils";

export default {
	name: "Status",
	mixins: [utils],
	components: {Comment, Like, Editor},
	props: ['item', 'users'],
	data: function () {
		return {
			localComments: this.parseItem(this.item.comments),
		}
	},
	methods: {
		buildLikeItem: function (){
			let selfVue = this

			selfVue.item.likesInfo.type = 'br_sta'

			return selfVue.item.likesInfo
		},
		getEditorId: function () {
			return 'breeze_status_' + this.item.id;
		},
		setLocalComment: function (comments) {
			this.localComments = Object.assign({}, this.localComments, this.parseItem(comments))
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
			this.$delete(this.localComments, commentId);
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
}
</script>

<style scoped>

</style>

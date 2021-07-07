Vue.component('like', {
	mixins: [breezeUtils],
	props: ['likeItem', 'currentUserId'],
	data: function() {
		return {
			fullId: 'breeze_' + this.likeItem.contentId + '_' + this.likeItem.type,
			txt: window.breezeTxtLike,
			likeClass: '',
			likeText: '',
			thisLikeInfo: this.likeItem,
		}
	},
	template: `
	<div class="smflikebutton" :id="fullId">
		<a v-on:click="handleLike()" class="msg_like">
			<span :class='likeClass'></span>
			{{likeText}}
		</a>
		<div class="like_count smalltext" v-html="thisLikeInfo.additionalInfo">
		</div>
	</div>`,
	created: function () {
		this.buildLikeClass()
		this.buildLikeText()
	},
	methods: {
		handleLike: function (){
			let selfVue = this;

			selfVue.setLoading()
			selfVue.api.post(selfVue.sprintFormat(selfVue.baseUrl, [
				selfVue.actions.like,
				selfVue.subActions.like.like
			]),
				{
					content_id: selfVue.thisLikeInfo.contentId,
					sa: selfVue.subActions.like.like,
					content_type: selfVue.thisLikeInfo.type,
					id_member: selfVue.currentUserId
				}
			).then(function (response) {
				selfVue.clearLoading()
				selfVue.setNotice(response.data);

				if (response.data.type === 'success') {
					selfVue.buildLikeText()
					selfVue.buildLikeClass()
					selfVue.thisLikeInfo = response.data.content.likesInfo
				}
			});
		},
		hasUserLikedTheItem: function (){
			let selfVue = this

			return selfVue.thisLikeInfo.alreadyLiked
		},
		buildLikeText: function (){
			let selfVue = this

			selfVue.likeText = selfVue.hasUserLikedTheItem() ? selfVue.txt.unlike : selfVue.txt.like;
		},
		buildLikeClass: function (){
			let selfVue = this

			selfVue.likeClass =  'main_icons ' + (selfVue.hasUserLikedTheItem() ? 'unlike' : 'like');
		},
		buildLikeUrl: function (){
			let selfVue = this

			selfVue.likeUrl = selfVue.sprintFormat(selfVue.baseUrl, [
				selfVue.actions.like,
				selfVue.subActions.like.like
			]) + ';like=' + selfVue.likeItem.content_id
		},
	}
})

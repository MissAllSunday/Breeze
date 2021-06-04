Vue.component('like', {
	mixins: [breezeUtils],
	props: ['likeItem', 'likesByContent', 'currentUserId'],
	data: function() {
		return {
			fullId: 'breeze_' + this.likeItem.id + '_' + this.likeItem.type,
			txt: window.breezeTxtLike,
			likeClass: 'main_icons ',
			likeText: '',
			additionalInfo: '',
		}
	},
	template: `
	<div class="smflikebutton" :id="fullId">
		<a v-on:click="handleLike()" class="msg_like">
			<span :class='likeClass'></span>
			{{likeText}}
		</a>
		<div class="like_count smalltext">
			{{additionalInfo}}
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
				selfVue.hasUserLikedTheItem() ? selfVue.subActions.like.unlike : selfVue.subActions.like.like
			]),
				{
					content_id: selfVue.likeItem.id,
					sa: (selfVue.hasUserLikedTheItem() ? selfVue.subActions.like.unlike : selfVue.subActions.like.like),
					content_type: selfVue.likeItem.type,
					id_member: selfVue.currentUserId
				}
			).then(function (response) {
				selfVue.clearLoading()
				selfVue.setNotice(response.data);
			});
		},
		hasUserLikedTheItem: function (){
			let selfVue = this

			return selfVue.likesByContent.includes(selfVue.currentUserId)
		},
		buildLikeText: function (){
			let selfVue = this

			selfVue.likeText = selfVue.hasUserLikedTheItem() ? selfVue.txt.unlike : selfVue.txt.like;
		},
		buildLikeClass: function (){
			let selfVue = this

			selfVue.likeClass =  selfVue.likeClass + (selfVue.hasUserLikedTheItem() ? 'unlike' : 'like');
		},
		buildLikeUrl: function (){
			let selfVue = this

			selfVue.likeUrl = selfVue.sprintFormat(selfVue.baseUrl, [
				selfVue.actions.like,
				selfVue.hasUserLikedTheItem() ? selfVue.subActions.like.unlike : selfVue.subActions.like.like
			]) + ';like=' + selfVue.likeItem.id
		},
	}
})

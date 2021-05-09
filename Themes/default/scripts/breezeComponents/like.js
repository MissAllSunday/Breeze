Vue.component('like', {
	mixins: [breezeUtils],
	props: ['likeItem', 'likesByContent', 'currentUserId'],
	data: function() {
		return {
			fullId: 'breeze_' + this.likeItem.id + '_' + this.likeItem.type,
			txt: window.breezeTxtLike,
			likeUrl: '',
			likeClass: 'main_icons ',
			likeText: '',
			additionalInfo: '',
		}
	},
	template: `
	<div class="smflikebutton" :id="fullId">
		<a :href='likeUrl' class="msg_like">
			<span :class='likeClass'></span>
			{{likeText}}
		</a>
		<div class="like_count smalltext">
			{{additionalInfo}}
		</div>
	</div>`,
	created: function () {
		this.buildLikeUrl()
		this.buildLikeClass()
		this.buildLikeText()
	},
	methods: {
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

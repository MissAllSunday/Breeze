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
			likesIds: this.likesByContent,
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
		this.buildAdditionalInfo()
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
					content_id: selfVue.likeItem.id,
					sa: selfVue.subActions.like.like,
					content_type: selfVue.likeItem.type,
					id_member: selfVue.currentUserId
				}
			).then(function (response) {
				selfVue.clearLoading()
				selfVue.setNotice(response.data);

				if (response.type === 'success') {
					selfVue.likesIds.push(selfVue.currentUserId)
					selfVue.buildLikeText()
					selfVue.buildLikeClass()
					selfVue.buildAdditionalInfo()
				}
			});
		},
		hasUserLikedTheItem: function (){
			let selfVue = this

			return selfVue.likesIds.includes(selfVue.currentUserId)
		},
		buildLikeText: function (){
			let selfVue = this

			selfVue.likeText = selfVue.hasUserLikedTheItem() ? selfVue.txt.unlike : selfVue.txt.like;
		},
		buildLikeClass: function (){
			let selfVue = this

			selfVue.likeClass =  selfVue.likeClass + (selfVue.hasUserLikedTheItem() ? 'unlike' : 'like');
		},
		buildAdditionalInfo: function (){
			let selfVue = this
			let otherLikes = selfVue.hasUserLikedTheItem() ? (selfVue.likesIds.length - 1) : selfVue.likesIds.length

			if (selfVue.likesIds.length === 0) {
				return;
			}

			selfVue.additionalInfo = (selfVue.hasUserLikedTheItem() ? 'You and ' : '') +
				(otherLikes) + ' other person'+ (otherLikes > 1 ? 's' : '') +' have liked this content'
		},
		buildLikeUrl: function (){
			let selfVue = this

			selfVue.likeUrl = selfVue.sprintFormat(selfVue.baseUrl, [
				selfVue.actions.like,
				selfVue.subActions.like.like
			]) + ';like=' + selfVue.likeItem.id
		},
	}
})

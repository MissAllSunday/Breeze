Vue.component('like', {
	mixins: [breezeUtils],
	props: ['likeItem', 'likesByContent', 'currentUserId'],
	data: function() {
		return {
			fullId: 'breeze_' + this.likeItem.id + '_' + this.likeItem.type,
			txt: window.breezeTxtLike,
			likeClass: '',
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
		<div class="like_count smalltext" v-html="additionalInfo">
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
					content_id: selfVue.likeItem.id,
					sa: selfVue.subActions.like.like,
					content_type: selfVue.likeItem.type,
					id_member: selfVue.currentUserId
				}
			).then(function (response) {
				selfVue.clearLoading()
				selfVue.setNotice(response.data);

				if (response.data.type === 'success') {
					selfVue.updateLikeIds()
					selfVue.buildLikeText()
					selfVue.buildLikeClass()
					selfVue.additionalInfo = response.data.content.additionalInfo
				}
			});
		},
		updateLikeIds: function (){
			let selfVue = this

			if (!selfVue.hasUserLikedTheItem())
				selfVue.likesIds.push(selfVue.currentUserId)

			else {
				selfVue.likesIds = selfVue.likesIds.filter(function(likeId) {
					return likeId !== selfVue.currentUserId;
				});
			}
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

			selfVue.likeClass =  'main_icons ' + (selfVue.hasUserLikedTheItem() ? 'unlike' : 'like');
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

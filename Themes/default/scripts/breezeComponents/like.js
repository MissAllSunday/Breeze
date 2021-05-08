Vue.component('like', {
	mixins: [breezeUtils],
	props: ['item'],
	data: function() {
		return {
			fullId: 'breeze_' + this.item.id + '_' + this.item.type,
		}
	},
	template: `
	<div class="smflikebutton" :id="fullId">
		some like stuff
	</div>`,
	methods: {
		buildLikeUrl: function (){

		},
	}
})

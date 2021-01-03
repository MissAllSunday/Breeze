Vue.component('mood', {
	props: {
		apiUrl: {
			type: String,
			required: false
		},
		mood:{
			type: Object,
			default: function () {
				return {
					id: 0,
					emoji: '',
					description: '',
					enable: 0,
				}
			},
			required: false
		}
	},
	template: `
	<li>
		<span v-html='mood.emoji'></span>
	</li>`,
	filters: {},
})

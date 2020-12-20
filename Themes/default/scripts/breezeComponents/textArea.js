Vue.component('text-area', {
	data: function () {
		return {
			content: this.content + 'box',
		}
	},
	template: `
	<div v-bind:class="fullType">
		<textarea>{{ content }}</textarea>
	</div>
	`,
	props: {
		content: {
			type: String,
			default: ''
		},
		identifier: {
			type: Int,
			default: 0
		}
	},
})

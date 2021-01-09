Vue.component('mood', {
	props: ['mood'],
	template: `
	<li>
		<span v-on:click="editing()">{{ decodedEmoji }}</span>
		<mood-edit-modal v-if="showModal" @close="showModal = false">

		<div slot="body">
		lol custom modal {{mood.moods_id}}
		</div>
		</mood-edit-modal>
	</li>`,
	data: function (){
		return {
			showModal: false
		}
	},
	computed: {
		decodedEmoji: function (){
			return this.decode(this.mood.emoji)
		}
	},
	methods: {
		editing: function (event) {
			this.showModal = true
		},
		decode: function (html) {
			let decoder = document.createElement('div');
			decoder.innerHTML = html;
			return decoder.textContent;
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u
			this.isEditing = false;
			this.isNotValidEmoji = !emojiRegex.test(this.mood.emoji)
		},
	},
})

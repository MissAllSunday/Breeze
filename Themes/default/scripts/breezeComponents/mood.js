Vue.component('mood', {
	template: `
	<li v-on:click="editing()">
		<div class="errorbox" v-if="isNotValidEmoji">
			<p>Invalid emoji </p>
		</div>
		<span>{{ toEdit.emoji}}</span>
		<div v-if="isEditing">
			<span class="main_icons remove_button floatright" v-on:click="close()"></span>
			Emoji: <input type="text" v-model="toEdit.emoji" v-on:change="validator()">
			Description: <input type="text" v-model="toEdit.description">
			<input type="checkbox" id="checkbox" v-model="toEdit.enable">
			<label for="checkbox">Enable</label>
		</div>
	</li>`,
	data: function () {
		return {
			isNotValidEmoji: false,
			isEditing: false,
			toEdit: Object.assign(this.mood, {
				emoji: this.decode(this.mood.emoji),
				enable: parseInt(this.mood.enable)
			}),
		}
	},
	props: {
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
	methods: {
		editing: function (event) {
			this.isEditing = true;
		},
		close: function (event) {
			this.isEditing = false;
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u
			this.isEditing = false;
			this.isNotValidEmoji = !emojiRegex.test(this.toEdit.emoji)
		},
		decode: function (html) {
			let decoder = document.createElement('div');
			decoder.innerHTML = html;
			return decoder.textContent;
		}
	},
	filters: {},
})

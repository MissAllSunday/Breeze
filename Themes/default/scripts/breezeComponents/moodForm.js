Vue.component('mood-form', {
	template: `
<div>
	<message-box
		v-if="invalidEmoji"
		v-bind:type="'error'"
		@close="resetEmojiField()">
			Invalid emoji.
	</message-box>
	<dl class="settings">
		<dt>
			<span>
				<label>Mood:</label>
			</span>
		</dt>
		<dd>
			<input type="text" v-model="mood.emoji" @change="invalidEmoji = validator()">
		</dd>
		<dt>
			<span>
				<label>Description:</label>
			</span>
		</dt>
		<dd>
			<textarea v-model="mood.description"></textarea>
		</dd>
		<dt>
			<span>
				<label>Enable:</label>
			</span>
		</dt>
		<dd>
			<input type="checkbox" id="checkbox" v-model="mood.isActive">
		</dd>
	</dl>
	<input type="submit" value="Save" class="button" @click="onSave()" :disabled='invalidEmoji'>
	<input type="submit" value="Delete" class="button" @click="$emit('delete')" v-if="!newMood">
</div>`,
	props: {
		mood: {
			default: function () {
				return {
					'emoji': '',
					'description': '',
					'isActive': true,
					'id': 0
				}
			}
		},
		newMood: {
			type: Boolean,
			default: false,
		},
	},
	data: function (){
		return {
			invalidEmoji: false
		}
	},
	methods: {
		onSave: function () {
			this.$emit('save', this.mood);
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u

			return !emojiRegex.test(this.mood.emoji)
		},
		resetEmojiField: function (){
			this.mood.emoji = ''
			this.invalidEmoji = false
		}
	},
})

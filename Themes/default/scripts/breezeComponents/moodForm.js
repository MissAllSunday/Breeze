Vue.component('mood-form', {
	template: `
<div>
	<message-box
		v-if="invalidEmoji"
		v-bind:type="'error'"
		@close="resetEmojiField()">
			{{ this.$root.txt.mood.invalidEmoji }}
	</message-box>
	<dl class="settings">
		<dt>
			<span>
				<label>{{ this.$root.txt.mood.emoji }}:</label>
			</span>
		</dt>
		<dd>
			<input type="text" v-model="mood.emoji" @change="invalidEmoji = validator()">
		</dd>
		<dt>
			<span>
				<label>{{ this.$root.txt.mood.description }}:</label>
			</span>
		</dt>
		<dd>
			<textarea v-model="mood.description"></textarea>
		</dd>
		<dt>
			<span>
				<label>{{ this.$root.txt.mood.enable }}:</label>
			</span>
		</dt>
		<dd>
			<input type="checkbox" id="checkbox" v-model="mood.isActive">
		</dd>
	</dl>
	<input type="submit" v-bind:value="$root.txt.save" class="button" @click="onSave()" :disabled='invalidEmoji'>
	<input type="submit" v-bind:value="$root.txt.delete" class="button" @click="$emit('delete')" v-if="!newMood">
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
			this.invalidEmoji = this.validator()

			if (!this.invalidEmoji) {
				this.$emit('save', this.mood)
			}
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u
			this.invalidEmoji = false

			if (!this.mood.emoji ||
				0 === this.mood.emoji.length ||
				!this.mood.emoji.trim()){

				return  this.$root.txt.mood.emptyEmoji
			}

			if (!emojiRegex.test(this.mood.emoji)) {
				return this.$root.txt.mood.invalidEmoji
			}
		},
		resetEmojiField: function (){
			this.mood.emoji = ''
			this.invalidEmoji = false
		}
	},
})

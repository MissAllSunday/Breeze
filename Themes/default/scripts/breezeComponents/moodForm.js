Vue.component('mood-form', {
	props: ['mood'],
	template: `
<div>
	<message-box
		v-if="invalidEmoji"
		v-bind:type="'error'"
		@close="resetEmojiField()">
			{{ this.invalidEmoji }}
	</message-box>
	<dl class="settings">
		<dt>
			<span>
				<label>{{ this.$root.txt.mood.emoji }}:</label>
			</span>
		</dt>
		<dd>
			<input type="text" v-model="localMood.emoji" @change="invalidEmoji = validator()">
		</dd>
		<dt>
			<span>
				<label>{{ this.$root.txt.mood.description }}:</label>
			</span>
		</dt>
		<dd>
			<textarea v-model="localMood.description"></textarea>
		</dd>
		<dt>
			<span>
				<label>{{ this.$root.txt.mood.enable }}:</label>
			</span>
		</dt>
		<dd>
			<input type="checkbox" id="checkbox" v-model="localMood.isActive">
		</dd>
	</dl>
	<input type="submit" v-bind:value="$root.txt.save" class="button" @click="onSave()" :disabled='invalidEmoji'>
	<input type="submit" v-bind:value="$root.txt.delete" class="button" @click="$emit('delete')" v-if="mood.id != 0">
</div>`,
	data: function (){
		return {
			invalidEmoji: false,
			localMood: Object.assign({'emoji': '',
				'description': '',
				'isActive': true,
				'id': 0}, this.mood),
		}
	},
	methods: {
		onSave: function () {
			this.invalidEmoji = this.validator()

			if (!this.invalidEmoji) {
				this.$emit('save', this.localMood)
			}
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u
			this.invalidEmoji = false

			if (!this.localMood.emoji ||
				0 === this.localMood.emoji.length ||
				!this.localMood.emoji.trim()){

				return  this.$root.txt.mood.emptyEmoji
			}

			if (!emojiRegex.test(this.localMood.emoji)) {
				return this.$root.txt.mood.invalidEmoji
			}
		},
		resetEmojiField: function (){
			this.localMood.emoji = ''
			this.invalidEmoji = false
		}
	},
})

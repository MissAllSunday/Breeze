Vue.component('mood', {
	props: ['mood'],
	template: `
	<li>
		<span v-on:click="editing()">{{ mood.emoji }}</span>
		<mood-edit-modal v-if="showModal" @close="showModal = false">
			<div slot="header">
				Editing {{ mood.emoji }}
			</div>
			<div slot="body">
				<div class="errorbox" v-if="invalidEmoji">
					<p class="alert">!!</p>
					<h3>Invalid emoji:</h3>
					<p>
						Please provide a valid emoji<br>
					</p>
				</div>
				<dl class="settings">
					<dt>
						<span>
							<label>Mood:</label>
						</span>
					</dt>
					<dd>
						<input type="ex" v-model="mood.emoji" @change="invalidEmoji = validator()">
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
						<input type="checkbox" id="checkbox" v-model="mood.enable">
					</dd>
				</dl>
				<input type="submit" value="Save" class="button">
			</div>
		</mood-edit-modal>
	</li>`,
	data: function (){
		return {
			showModal: false,
			invalidEmoji: false
		}
	},
	methods: {
		editing: function (event) {
			this.showModal = true
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u

			return !emojiRegex.test(this.mood.emoji)
		},
	},
})

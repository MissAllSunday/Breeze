Vue.component('mood', {
	props: ['mood'],
	template: `
	<li>
		<span @click="editing()">{{ mood.emoji }}</span>
		<mood-edit-modal v-if="showModal" @close="showModal = false" @click.stop>
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
				<input type="submit" value="Save" class="button" @click="onSave()">
				<input type="submit" value="Delete" class="button" @click="onDelete()">
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
		onSave: function (){
			if (!confirm(smf_you_sure)) {
				return;
			}

			axios.post(
				smf_scripturl + '?action=breezeMood;sa=editMood;'+ smf_session_var +'='+ smf_session_id,
				this.mood
			).then(response => {
				Vue.$toast.open({
					message: response.data.message,
					type: response.data.type,
				});
			});
		},
		onDelete: function (){
			let selfVue = this;

			if (!confirm(smf_you_sure)) {
				return;
			}

			axios.post(
				smf_scripturl + '?action=breezeMood;sa=deleteMood;'+ smf_session_var +'='+ smf_session_id,
				{id: this.mood.id}
			).then(function (response) {
				selfVue.showModal = false;
				Vue.$toast.open({
					message: response.data.message,
					type: response.data.type,
				});

				if (response.data.type !== 'error') {
					selfVue.removeComment();
				}
			});
		},
		removeComment: function () {
			this.$emit('remove-mood', this.mood.id);
		},
		editing: function (event) {
			this.showModal = true
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u

			return !emojiRegex.test(this.mood.emoji)
		},
	},
})

Vue.component('mood', {
	props: ['mood'],
	template: `
	<li>
		<span @click="editing()">{{ mood.emoji }}</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<div slot="header">
				{{ $root.txt.editing }} {{ mood.emoji }}
			</div>
			<div slot="body">
				<mood-form
					v-bind:mood="mood"
					@delete="onDelete"
					@save="onSave"
				></mood-form>
			</div>
		</modal>
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

			this.api.post(
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

			if (!selfVue.ownConfirm()) {
				return;
			}

			this.api.post(
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

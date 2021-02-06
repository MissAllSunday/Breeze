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
					@save="onSave($event)"
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
		onSave: function (mood){
			let selfVue = this

			if (!selfVue.ownConfirm()) {
				return;
			}

			selfVue.mood = mood

			selfVue.api.post(selfVue.sprintFormat(selfVue.baseUrl,
				[selfVue.actions.mood, selfVue.subActions.mood.post]),
				selfVue.mood
			).then(response => {
				selfVue.setNotice(response.data)

				if (response.data.type !== 'error') {
					selfVue.updateList();
				}
			});
		},
		updateList: function () {
			this.$emit('update-list', this.mood);
		},
		onDelete: function (){
			let selfVue = this;

			if (!selfVue.$root.ownConfirm()) {
				return;
			}

			selfVue.api.post(selfVue.sprintFormat(selfVue.baseUrl,
				[selfVue.actions.mood, selfVue.subActions.mood.delete]),
				selfVue.mood
			).then(function (response) {
				selfVue.setNotice(response.data)

				if (response.data.type !== 'error') {
					selfVue.removeMood();
				}
			});
		},
		removeMood: function () {
			this.$emit('remove-mood', this.mood.id);
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u

			return !emojiRegex.test(this.mood.emoji)
		},
	},
})

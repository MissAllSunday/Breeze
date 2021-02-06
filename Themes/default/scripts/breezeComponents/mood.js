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
			let selfVue = this

			if (!selfVue.ownConfirm()) {
				return;
			}

			this.api.post(selfVue.sprintFormat(selfVue.baseUrl,
				[this.actions.mood ,this.subActions.pMood]),
				this.mood
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

			this.api.post(selfVue.sprintFormat(selfVue.baseUrl,
				[this.actions.mood ,this.subActions.dMood]),
				this.mood
			).then(function (response) {
				selfVue.showModal = false;
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

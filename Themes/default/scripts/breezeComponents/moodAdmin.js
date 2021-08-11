Vue.component('mood-admin', {
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
					@delete="onDelete()"
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

			if (!selfVue.$root.ownConfirm()) {
				return;
			}

			selfVue.mood = mood

			selfVue.$root.api.post(selfVue.$root.sprintFormat(selfVue.$root.baseUrl,
				[selfVue.$root.actions.mood, selfVue.$root.subActions.mood.post]),
				selfVue.mood
			).then(response => {
				selfVue.$root.setNotice(response.data)

				if (response.data.type !== 'error') {
					selfVue.updateList(mood);
				}

			selfVue.showModal = false;
			});
		},
		updateList: function (mood) {
			this.$emit('update-list', mood);
		},
		onDelete: function (){
			let selfVue = this;

			if (!selfVue.$root.ownConfirm()) {
				return;
			}

			selfVue.$root.api.post(selfVue.$root.sprintFormat(selfVue.$root.baseUrl,
				[selfVue.$root.actions.mood, selfVue.$root.subActions.mood.eliminate]),
				selfVue.mood
			).then(function (response) {
				selfVue.$root.setNotice(response.data)

				if (response.data.type !== 'error') {
					selfVue.removeMood();
				}
			});
		},
		removeMood: function () {
			this.$emit('remove-mood', this.mood.id);
		},
		editing: function () {
			this.showModal = true
		},
		validator: function (){
			let emojiRegex = /\p{Extended_Pictographic}/u

			return !emojiRegex.test(this.mood.emoji)
		},
	},
})

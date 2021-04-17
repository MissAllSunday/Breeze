Vue.component('set-mood', {
	mixins: [breezeUtils],
	data: function() {
		return {
			activeMoods: [],
			errored: false,
			showModal: false,
		}
	},
	template: `
	<div>
		<span @click="changeMood()">Set your mood</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<div slot="header">
				Set your mood!
			</div>
			<div slot="body">
				<ul>
					<li v-for="mood in activeMoods" :key="mood.id">
						{{ mood.emoji }}
					</li>
				</ul>
			</div>
		</modal>
	</div>`,
	created: function () {

		this.fetchActiveMoods();
	},
	methods: {
		fetchActiveMoods: function () {
			let selfVue = this

			if (localStorage.breezeAllMoods) {
				selfVue.activeMoods = localStorage.breezeAllMoods;

				return;
			}

			selfVue.api
				.get(selfVue.sprintFormat(selfVue.baseUrl,
					[this.actions.mood ,this.subActions.mood.active]))
				.then(function(response) {
					Object.values(response.data).map(function(mood) {
						mood.emoji = selfVue.decode(mood.emoji)
						mood.isActive = Boolean(Number(mood.isActive))
						mood.id = Number(mood.id)

						return mood
					})

					selfVue.activeMoods = response.data
				})
				.catch(function(error) {
					let selfVue = this;
					selfVue.errored = true
				})
		},
		changeMood: function (){
			this.showModal = true;
		},
	},
})


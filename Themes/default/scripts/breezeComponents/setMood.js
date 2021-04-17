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
	<div id="moodList">
		<span @click="showMoodList()">Set your mood</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<div slot="header">
				Set your mood!
			</div>
			<div slot="body">
				<ul>
					<li
						v-for="mood in activeMoods" :key="mood.id"
						alt="mood.description"
						@click="changeMood(mood)">
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
		changeMood: function (selectedMood){
			let selfVue = this

			selfVue.api
				.post(selfVue.sprintFormat(selfVue.baseUrl,
					[this.actions.mood ,this.subActions.mood.setMood]),
					{
						userId: 1,
						id: selectedMood.id,
					})
				.then(function(response) {console.log(response)
					selfVue.$root.setNotice(response.data)
					this.showModal = false;
				})
				.catch(function(error) {console.log(error)
					selfVue.errored = true
				}).then(function (){
					this.showModal = false;
			});
		},
		fetchActiveMoods: function () {
			let selfVue = this

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

					selfVue.activeMoods = Object.assign({}, selfVue.activeMoods, response.data);
				})
				.catch(function(error) {
					selfVue.errored = true
				})
		},
		showMoodList: function (){
			let selfVue = this
			this.showModal = true;
		},
		decode: function (html) {
			let decoder = document.createElement('div');
			decoder.innerHTML = html;
			return decoder.textContent;
		},
	},
})


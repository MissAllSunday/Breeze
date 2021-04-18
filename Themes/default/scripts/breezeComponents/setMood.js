Vue.component('set-mood', {
	props: ['currentMoodId', 'moodId', 'moodTxt'],
	mixins: [breezeUtils],
	data: function() {
		return {
			activeMoods: [],
			errored: false,
			showModal: false,
			currentMood: {}
		}
	},
	template: `
	<div id="moodList">
		<span @click="showMoodList()" title="moodLabel">{{ currentMood.emoji }}</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<div slot="header">
				{{ moodTxt.moodLabel }}
			</div>
			<div slot="body">
				<ul>
					<li
						v-for="mood in activeMoods"
						:key="mood.id"
						title="mood.description"
						@click="changeMood(mood)">
						{{ mood.emoji }}
					</li>
				</ul>
			</div>
		</modal>
	</div>`,
	created: function () {
		this.resolveCurrentMood();
		this.fetchActiveMoods();
	},
	methods: {
		resolveCurrentMood: function (){
			this.currentMood.emoji = this.moodTxt.moodLabel;
		},
		changeMood: function (selectedMood){
			let selfVue = this

			selfVue.showModal = false;

			if (selectedMood.id === this.currentMoodId) {
				selfVue.setNotice({message: this.moodTxt.sameMood});

				return;
			}

			selfVue.api
				.post(selfVue.sprintFormat(selfVue.baseUrl,
					[this.actions.mood ,this.subActions.mood.setMood]),
					{
						moodId: selectedMood.id,
						userId: this.userId,
					})
				.then(function(response) {
					selfVue.setNotice(response.data)
				})
				.catch(function(error) {
					selfVue.errored = true
				}).then(function (){

			});
		},
		fetchActiveMoods: function () {
			let selfVue = this

			if (selfVue.canUseLocalStorage() === true) {
				let activeMoods = JSON.parse(localStorage.getItem('breeze_activeMoods'));

				if (activeMoods !== null){
					Object.values(activeMoods).map(function(mood) {
						mood.emoji = selfVue.decode(mood.emoji)
						mood.isActive = Boolean(Number(mood.isActive))
						mood.id = Number(mood.id)

						if (selfVue.currentMoodId > 0 && mood.id === selfVue.currentMoodId) {
							selfVue.currentMood = Object.assign({}, selfVue.currentMood, mood);
						}

						return mood
					});

					selfVue.activeMoods = Object.assign({}, selfVue.activeMoods, activeMoods);

					return;
				}
			}

			selfVue.api
				.get(selfVue.sprintFormat(selfVue.baseUrl,
					[this.actions.mood ,this.subActions.mood.active]))
				.then(function(response) {
					Object.values(response.data).map(function(mood) {
						mood.emoji = selfVue.decode(mood.emoji)
						mood.isActive = Boolean(Number(mood.isActive))
						mood.id = Number(mood.id)

						if (selfVue.currentMoodId > 0 && mood.id === selfVue.currentMoodId) {
							selfVue.currentMood = Object.assign({}, selfVue.currentMood, mood);
						}

						return mood
					});

					if (selfVue.canUseLocalStorage() === true) {
						localStorage.setItem('breeze_activeMoods', JSON.stringify(response.data));
					}

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


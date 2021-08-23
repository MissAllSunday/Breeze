Vue.component('set-mood', {
	props: ['currentMoodId', 'userId', 'moodTxt', 'isCurrentUserOwner', 'useMood'],
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
		<span v-if="isCurrentUserOwner && useMood" @click="showMoodList()" title="moodLabel" class="pointer_cursor">
			{{ moodTxt.defaultLabel }} {{ currentMood.emoji }}
		</span>
		<span v-else title="moodLabel">{{ moodTxt.defaultLabel }} {{ currentMood.emoji }}</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<div slot="header">
				{{ moodTxt.defaultLabel }}
			</div>
			<div slot="body">
				<ul class="set_mood">
					<li
						v-for="mood in activeMoods"
						:key="mood.id"
						title="mood.description"
						@click="changeMood(mood)">
						<span>
							{{ currentMood.emoji }}
						</span>
					</li>
				</ul>
			</div>
		</modal>
	</div>`,
	created: function () {
		this.fetchActiveMoods();
	},
	methods: {
		resolveCurrentMood: function (){
			this.currentMood.emoji = this.moodTxt.moodLabel;

			if (this.activeMoods[this.currentMoodId]) {
				this.currentMood = this.activeMoods[this.currentMoodId];
			}
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

					if (response.data.type !== 'error') {
						selfVue.currentMoodId = selectedMood.id
						selfVue.resolveCurrentMood()
					}
				})
				.catch(function(error) {
					selfVue.errored = true
				}).then(function (){

			});
		},
		fetchActiveMoods: function () {
			let selfVue = this
			let activeMoods;

			if ((activeMoods = selfVue.getLocalObject('breeze_activeMoods')) !== false) {
				selfVue.activeMoods = Object.assign({}, selfVue.activeMoods, selfVue.parseMoods(activeMoods));
				selfVue.resolveCurrentMood();

				return;
			}

			selfVue.api
				.get(selfVue.sprintFormat(selfVue.baseUrl,
					[selfVue.actions.mood ,selfVue.subActions.mood.active]))
				.then(function(response) {
					activeMoods = selfVue.parseMoods(response.data);

					selfVue.setLocalObject('breeze_activeMoods', activeMoods);
					selfVue.activeMoods = Object.assign({}, selfVue.activeMoods, activeMoods);
					selfVue.resolveCurrentMood();

				})
				.catch(function(error) {
					selfVue.errored = true
				});
		},
		showMoodList: function (){
			let selfVue = this

			selfVue.showModal = true;
		},
	},
})


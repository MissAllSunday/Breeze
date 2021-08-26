<template>
	<div>
		<span v-if="isCurrentUserOwner && useMood" @click="showMoodList()" title="moodLabel" class="pointer_cursor">
				{{ txtMood.defaultLabel }} {{ currentMood.emoji }}
			</span>
		<span v-else title="moodLabel">{{ txtMood.defaultLabel }} {{ currentMood.emoji }}</span>
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<slot name="header">
				{{ txtMood.defaultLabel }}
			</slot>
			<slot name="body">
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
			</slot>
		</modal>
	</div>
</template>

<script>
import Modal from './Modal.vue'
import utils from "@/utils";

export default {
	name: "SetMood",
	mixins: [utils],
	props: ['currentMoodId', 'userId', 'isCurrentUserOwner', 'useMood'],
	components: {
		Modal
	},
	data: function() {
		return {
			activeMoods: [],
			errored: false,
			showModal: false,
			currentMood: {}
		}
	},
	created: function () {
		this.fetchActiveMoods();
	},
	methods: {
		resolveCurrentMood: function (){
			this.currentMood.emoji = '';

			if (this.activeMoods[this.currentMoodId]) {
				this.currentMood = this.activeMoods[this.currentMoodId];
			}
		},
		changeMood: function (selectedMood){
			let selfVue = this

			selfVue.showModal = false;

			if (selectedMood.id === this.currentMoodId) {
				selfVue.setNotice({message: this.txtMood.sameMood});

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
					console.error(error)
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
					console.error(error)
					selfVue.errored = true
				});
		},
		showMoodList: function (){
			let selfVue = this

			selfVue.showModal = true;
		},
	},
}
</script>

<style scoped>

</style>

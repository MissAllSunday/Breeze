<template>
	<div class="breeze_summary floatleft">
		<div class="roundframe flow_auto">
			<div class="breeze_avatar">
<!--				 style="background-image: url('urlstring')"-->

			</div>
			<h3 class="breeze_name">
				memberOnline
				member name color
			</h3>
			<p class="breeze_title">
				primary/post group
			</p>
			<p class="breeze_title">
				group icons
			</p>
			<p class="breeze_description">
				blurb
			</p>
			<p class="breeze_mood">
				<SetMood
					:current-mood-id="currentMoodId"
					:user-id="wallData.ownerId"
					:mood-txt="txtMood"
					:is-current-user-owner="isCurrentUserOwner"
					:use-mood="useMood"
				></SetMood>
			</p>
		</div>
	</div>

	<div class="breeze_wall floatright">
		<Tabs>
			<Tab :name="tabs_wall" :selected="true">
				<Editor
					editor_id="\'breeze_status\'"
					@get-content="postStatus($event)">
				</Editor>
				<ul class="status">
					<Status
						v-if="errored !== null"
						v-for="status_item in status"
						v-bind:item="status_item"
						v-bind:key="status_item.status_id"
						:users="users"
						@remove-status="onRemoveStatus($event)"
						@set-new-users="onSetNewUsers($event)">
					</Status>
				</ul>
			</Tab>
		</Tabs>
	</div>
</template>

<script>
import utils from './utils.js'
import SetMood from './components/SetMood.vue'
import Editor from '@/components/Editor.vue'
import Status from "@/components/Status";
import Tabs from "@/components/Tabs";
import Tab from "@/components/Tab";

export default {
  name: 'Wall',
  components: {
	  Tab,
	  Tabs,
	  Status,
	  Editor,
    SetMood
  },
	mixins: [utils],
	data() {
		return {
			txt: window.breezeTxtGeneral,
			txtMood: window.breezeTxtMood,
			status: null,
			errored: false,
			notice: null,
			users: {},
			wallData: {
				ownerId: window.breezeUsers.wallOwner || 0,
				posterId: window.breezeUsers.wallPoster || 0,
			},
			currentMoodId: window.breezeProfileOwnerSettings.moodId || 0,
			isCurrentUserOwner: window.breezeIsCurrentUserOwner,
			useMood: window.breezeUseMood,
		}
	},
	created: function () {
		this.fetchStatus()
	},
	methods: {
		postStatus: function (editorContent) {
			let selfVue = this

			this.api.post(this.sprintFormat(this.baseUrl,
					[this.actions.status ,this.subActions.status.post]),
				{
					wallId: selfVue.wallData.ownerId,
					userId: selfVue.wallData.posterId,
					body: editorContent,
				}
			).then(function(response) {
				selfVue.setNotice(response.data);

				if (response.data.content) {
					selfVue.setUserData(response.data.content.users)
					selfVue.setStatus(response.data.content.status);
				}

			}).catch(function(error) {
				selfVue.setNotice = {
					'message': error.message,
				};
			});
		},
		setStatus: function (newStatus) {
			let selfVue = this

			selfVue.status = Object.assign({}, selfVue.status, selfVue.parseItem(newStatus));
		},
		fetchStatus: function () {
			let selfVue = this

			selfVue.api.post(this.sprintFormat(this.baseUrl,
					[this.actions.status ,this.subActions.status.byProfile]),
				{wallId: selfVue.wallData.ownerId}
			).then(function(response) {
				if (response.data.type) {
					selfVue.setNotice(response.data);
					selfVue.errored = true;

					return;
				}

				selfVue.status = selfVue.parseItem(response.data.status)
				selfVue.setUserData(response.data.users)
			}).catch(function(error) {
				selfVue.errored = true
				selfVue.setNotice = {
					'message': error.message,
				};
			}).then(function () {
				selfVue.clearLoading()
			})
		},
		onRemoveStatus: function (statusId) {
			this.$delete(this.status, statusId);
		},
		onSetNewUsers: function (newUsers){
			this.setUserData(newUsers)
		},
	}
}
</script>

<style>

</style>

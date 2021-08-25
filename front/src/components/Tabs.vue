<template>
	<div class="adm_submenus tabs">
		<ul class="dropmenu">
			<li
				v-for="tab in tabs"
				:class="{ 'is-active': tab.isActive }"
				v-bind:key="tab"
			>
				<a :href="tab.href" @click="selectTab(tab)" :class="{ 'active': tab.isActive }">{{ tab.name }}</a>
			</li>
		</ul>
	</div>
	<div class="tabs-details">
		<slot></slot>
	</div>
</template>

<script>
export default {
	name: "Tabs",
	data() {
		return {
			tabs: [],
		};
	},
	created() {
		this.tabs = this.$children;
	},
	methods: {
		selectTab(selectedTab) {
			this.tabs.forEach(tab => {
				tab.isActive = (tab.name === selectedTab.name);
			});
		}
	}
}
</script>

<style scoped>

</style>

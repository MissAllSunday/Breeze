<template>
		<transition name="modal">
		<div class="modal-mask">
			<div class="modal-wrapper" @click="$emit('close')">
				<div class="modal-container" @click.stop>
					<div class="modal-header cat_bar">
						<h3 class="catbg">
							<span class="floatleft">
								<slot name="header"></slot>
							</span>
							<span class="main_icons remove_button floatright pointer_cursor" @click="$emit('close')"></span>
						</h3>
					</div>
					<div class="modal-body information">
						<slot name="body"></slot>
					</div>
				</div>
			</div>
		</div>
		</transition>
</template>

<script>
export default {
	name: "Modal"
}
</script>

<style scoped>

</style>

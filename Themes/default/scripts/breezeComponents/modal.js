Vue.component('mood-edit-modal', {
	template: `
<div id="mood-edit-modal">
	<transition name="modal">
		<div class="modal-mask">
			<div class="modal-wrapper" @click="$emit('close')">
				<div class="modal-container" @click.stop>
					<div class="modal-header cat_bar">
						<h3 class="catbg">
							<span class="floatleft">
								<slot name="header"></slot>
							</span>
							<span class="main_icons remove_button floatright" @click="$emit('close')"></span>
						</h3>
					</div>
					<div class="modal-body information">
						<slot name="body"></slot>
					</div>
				</div>
			</div>
		</div>
	</transition>
</div>
	`,
})

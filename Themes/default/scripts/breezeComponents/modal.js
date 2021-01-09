Vue.component('mood-edit-modal', {
	template: `
<div id="mood-edit-modal">
	<transition name="modal">
		<div class="modal-mask">
			<div class="modal-wrapper">
				<div class="modal-container">
					<div class="modal-header">
						<slot name="header"></slot>
					</div>
					<div class="modal-body">
						<slot name="body"></slot>
					</div>
					<div class="modal-footer">
						<slot name="footer">
							<button class="modal-default-button" @click="close">
								OK
							</button>
						</slot>
					</div>
				</div>
			</div>
		</div>
	</transition>
</div>
	`,
})

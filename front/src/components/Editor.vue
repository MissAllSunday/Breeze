<template>
	<div>
		<Modal v-if ="previewBody !== null" @close="clearPreview()" @click.stop>
			<slot name="header">
				{{ this.$data.txt.previewing }}
			</slot>
			<slot name="body">
				<div class="sun-editor-editable" v-html="previewBody"></div>
			</slot>
		</Modal>
		<textarea :id="editor_id"></textarea>
		<div class="post_button_container floatright">
			<input
				v-if ="previewBody === null"
				type="button"
				@click="showPreview()"
				class="button"
				:value="$data.txt.preview">
			<input type="submit" @click="postData()" class="button" :value="$data.txt.send">
		</div>
	</div>
</template>

<script>
import Modal from "@/components/Modal";
import suneditor from 'suneditor'
import utils from "@/utils";

export default {
	name: "Editor",
	components: {Modal},
	mixins: [utils],
	props: ['editor_id', 'options', 'posterInfo'],
	data: function () {
		return {
			previewBody: null,
			editor: null,
			body: '',
		}
	},
	mounted: function () {
		let editorOptions = Object.assign({
			resizingBar: true,
			width : 'auto',
			maxWidth : '1500px',
			height : 'auto',
			minHeight : '100px',
			maxHeight: '550px',
			fontSize : [
				8, 10, 14, 18, 24
			],
			buttonList : [
				['undo', 'redo', 'fontSize', 'formatBlock'],
				['bold', 'underline', 'italic', 'strike', 'removeFormat'],
				['fontColor', 'hiliteColor', 'align', 'horizontalRule'],
			],
			defaultStyle: `
				font-size: inherit;
				color: inherit;
				background-color: inherit;`
		}, this.options);

		this.editor = suneditor.create(document.getElementById("breeze_status"), editorOptions);
	},
	methods: {
		postData: function () {
			let body = this.sanitize(this.editor.getContents(true))

			if (!this.isValidContent(body)) {
				return false;
			}

			this.$emit('get-content', body);
			this.editor.setContents('');
		},
		showPreview: function () {
			let body = this.sanitize(this.editor.getContents(true))

			if (!this.isValidContent(body))
				return;

			this.previewBody = body
		},
		clearPreview: function (){
			this.previewBody = null
		},
		isValidContent: function (body) {
			if (body === '' ||
				body === '<p><br></p>' ||
				body === '<p></p>' ||
				typeof(body) !== 'string' ) {
				this.setNotice({
					message: this.$data.txt.errorEmpty
				});

				return false;
			}

			if (body === 'about:Suki' || body === '<p>about:Suki<br></p>' || body === '<p>about:Suki</p>') {
				alert('Whatcha gonna do, where are you gonna go\n' +
					'When the darkness closes on you\n' +
					'Is there anybody out there looking for you?\n' +
					'Do they know what you\'ve been through?');

				return false;
			}

			return true;
		},
	}
}
</script>

<style scoped>

</style>

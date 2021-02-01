Vue.component('editor', {
	mixins: [breezeUtils],
	props: ['editor_id', 'options', 'posterInfo'],
	data: function () {
		return {
			previewBody: null,
			editor: null,
			body: '',
		}
	},
	template: `
<div class="breeze_editor">
	<modal v-if ="previewBody !== null" @close="clearPreview()" @click.stop>
		<div slot="header">
			{{ this.txt.previewing }}
		</div>
		<div slot="body">
			<div class="sun-editor-editable" v-html="previewBody"></div>
		</div>
	</modal>
	<textarea :id="editor_id"></textarea>
	<div class="post_button_container floatright">
			<input
				v-if ="previewBody === null"
				type="button"
				@click="showPreview()"
				class="button"
				:value="txt.preview">
			<input type="submit" @click="postData()" class="button" :value="txt.send">
	</div>
</div>`,
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

		this.editor = SUNEDITOR.create(document.getElementById(this.editor_id), editorOptions);

		this.editor.onload = function (core, reload) {
			console.log('onload-core', core)
			console.log('onload-reload', reload)
		}
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
				this.setNotice(this.txt.errorEmpty);

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
})

Vue.component('editor', {
	mixins: [breezeUtils],
	props: ['editor_id'],
	data: function () {
		return {
			previewButtonValue: 'Preview',
			previewBody: null,
			editor: null,
			body: '',
			notice: null
		}
	},
	template: `
<div class="breeze_editor">
	<modal v-if ="previewBody !== null" @close="clearPreview()" @click.stop>
		<div slot="header">
			Previewing
		</div>
		<div slot="body">
			<div class="sun-editor-editable" v-html="previewBody"></div>
		</div>
	</modal>
	<textarea :id="editor_id"></textarea>
	<div v-if ="notice === null" class="post_button_container floatright">
			<input
				v-if ="previewBody === null"
				type="button"
				@click="showPreview()"
				class="button"
				:value="previewButtonValue">
			<input type="submit" @click="postData()" class="button">
	</div>
</div>`,
	mounted: function () {
		this.editor = SUNEDITOR.create(document.getElementById(this.editor_id),{
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
		});
	},
	methods: {
		postData: function () {
			this.clearNotice()

			this.body = this.editor.getContents(true);

			if (!this.isValidContent()) {
				return false;
			}

			this.$emit('get-content', this.body);
			this.editor.setContents('');
		},
		showPreview: function () {
			this.previewBody = this.editor.getContents(true)
			this.previewButtonValue = 'Clear'
		},
		clearPreview: function (){
			this.previewBody = null
			this.previewButtonValue = 'Preview'
		},
		isValidContent: function () {
			if (this.body === '' ||
				this.body === '<p><br></p>' ||
				this.body === '<p></p>' ||
				typeof(this.body) !== 'string' ) {
				this.setNotice('el body esta vacio');

				return false;
			}

			if (this.body === 'about:Suki' || this.body === '<p>about:Suki<br></p>') {
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

Vue.component('editor', {
	mixins: [breezeUtils],
	props: ['editor_id'],
	data: function () {
		return {
			previewed: null,
			preview_name: 'Preview',
			editor: null,
			notice: null,
			place_holder: 'say something!',
			body: '',
		}
	},
	template: `
<div class="breeze_editor">
	<div v-if ="previewed !== null" class="cat_bar">
		<h3 class="catbg">
			Preview
		</h3>
	</div>
	<div v-if ="previewed !== null" class="sun-editor-editable windowbg" v-html="previewed">
	</div>
	<textarea :id="editor_id"></textarea>
	<div v-if ="notice === null" class="post_button_container floatright">
			<input type="button" @click="preview()" class="button" :value="preview_name">
			<input type="submit" @click="postStatus()" class="button">
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
			]
		});
	},
	methods: {
		postStatus: function () {
			this.clearNotice()

			this.body = this.editor.getContents(true);

			if (!this.isValidStatus()) {
				return false;
			}

			this.$emit('get-content', this.body);
			this.editor.setContents('');
		},
		preview: function () {
			this.previewed = this.previewed === null ? this.editor.getContents(true) : null
			this.preview_name = this.previewed === null ? 'Preview' : 'Clear'
		},
		isValidStatus: function () {
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

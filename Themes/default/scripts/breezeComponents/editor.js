Vue.component('editor', {
    props: ['editor_id'],
    data: function() {
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
    <div>
        <div v-if="previewed !== null" class="cat_bar">
            <h3 class="catbg">
                Preview
            </h3>
        </div>
        <div v-if="previewed !== null" class="sun-editor-editable windowbg" v-html="previewed">
        </div>
        <textarea v-bind:id="editor_id"></textarea>
        <div v-if="notice === null" class="post_button_container floatright">
                <input type="button" @click="preview()" class="button" :value="preview_name">
                <input type="submit" @click="postStatus()" class="button">
        </div>
    </div>`,
    mounted: function() {
        this.editor = SUNEDITOR.create(document.getElementById(this.editor_id),{
            width : 'auto',
            maxWidth : '1200px',
            height : 'auto',
            minHeight : '100px',
            maxHeight: '350px',
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
        postStatus: function (){
            this.$root.clearNotice()

            this.body = this.editor.getContents(true);

            if (!this.isValidStatus())
                return false;

            this.$emit('get-content', this.$root.$sanitize(this.body));
            this.editor.setContents('');
        },
        preview: function (){
            this.previewed = this.previewed === null ? this.editor.getContents(true) : null
            this.preview_name = this.previewed === null ? 'Preview' : 'Clear'
        },
        isValidStatus: function () {
            if (this.body === '' ||
                this.body === '<p><br></p>' ||
                typeof(this.body) !== 'string' )
            {
                this.$root.setNotice('el body esta vacio');

                return false;
            }

            if (this.body === 'about:Suki')
            {
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

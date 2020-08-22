Vue.component('editor', {
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
        <textarea id="Breeze_editor"></textarea>
        <div v-if="notice === null" class="post_button_container floatright">
                <input type="button" @click="preview()" class="button" :value="preview_name">
                <input type="submit" @click="postStatus()" class="button">
        </div>
    </div>`,
    mounted: function() {
        this.editor = SUNEDITOR.create(document.getElementById('Breeze_editor'),{
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
            this.clearNotice()

            this.setBody(this.editor.getContents(true));

            if (!this.isValidStatus())
                return false;

            this.$emit('get-content', this.getBody());
        },
        preview: function (){
            this.previewed = this.previewed === null ? this.editor.getContents(true) : null
            this.preview_name = this.previewed === null ? 'Preview' : 'Clear'
        },
        setBody: function (stringContent){
            this.body = stringContent;
        },
        getBody: function (){
            return this.body;
        },
        isValidStatus: function () {
            if (this.body === '' ||
                this.body === '<p><br></p>' ||
                typeof(this.body) !== 'string' )
            {
                this.setNotice('el body esta vacio');

                return false;
            }

            return true;
        },
    }
})

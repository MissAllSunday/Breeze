Vue.component('editor', {
    props: ['editor_options'],
    data: function() {
        return {
            previewed: null,
            preview_name: 'Preview',
            editor: null,
            notice: null,
            place_holder: 'say something!',
            post_status: {
                status_owner_id: wall_owner_id,
                status_poster_id: smf_member_id,
                status_body: ''
                ,
            },
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

        },
        preview: function (){
            this.previewed = this.previewed === null ? this.editor.getContents(true) : null
            this.preview_name = this.previewed === null ? 'Preview' : 'Clear'
        }
    }
})

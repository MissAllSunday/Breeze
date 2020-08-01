Vue.component('editor', {
    props: ['editor_options'],
    data: function() {
        return {
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
    template: `<div>
        <textarea id="Breeze_editor">Hi</textarea>
    </div>`,
    mounted: function() {
        this.editor = SUNEDITOR.create(document.getElementById('Breeze_editor'),{
            width : 'auto',
            maxWidth : '700px',
            height : 'auto',
            minHeight : '100px',
            maxHeight: '250px',
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

    }
})

Vue.component('editor', {
    props: ['editor_options'],
    data: function() {
        return {
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
        <textarea 
            class="editor" 
            v-bind:name="editor_options.id" 
            id="Breeze_editor" 
            cols="600" 
            v-bind:style="editorInlineCss()"
            required>          
        </textarea>
        <div id="Breeze_editor_resizer" class="richedit_resize"></div>
    </div>`,
    created: function() {
        var textarea = document.getElementById('Breeze_editor');
        sceditor.create(textarea, this.editor_options);
        sceditor.instance(textarea).createPermanentDropDown();
    },
    methods: {
        editorInlineCss: function () {
            return 'width:' + this.editor_options.width + '; height: '+ this.editor_options.height + ''
        },
        editorResizerClass: function () {
            return this.editor_options.id + '_resizer'
        }
    }
})

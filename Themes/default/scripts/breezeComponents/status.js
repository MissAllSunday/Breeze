Vue.component('status', {
    props: ['status_item', 'poster_data', 'comments'],
    data: function() {
        return {
            comment_message: '',
            error: null,
            place_holder: 'leave a comment',
            post_comment: {
                posterId: smf_member_id,
                statusOwnerId: this.status_item.status_poster_id,
                profileOwnerId: this.status_item.status_owner_id,
                statusId: this.status_item.status_id,
                body: '',
            },
        }
    },
    template: `<div>
    <div class='breeze_avatar floatleft' v-bind:style='avatarImage(poster_data.avatar.href)'></div>
        <div class='windowbg'>
            <h4 class='floatleft' v-html='poster_data.link_color'></h4>
            <div class='floatright smalltext'>
                {{status_item.status_time | formatDate}}
            </div>
            <br>
            <div class='content'>
                <hr>
                {{status_item.status_body}}
            </div>
            <comment 
                v-for='comment in comments' 
                v-bind:comment='comment' 
                v-bind:comment_poster_data='getUserData(comment.comments_poster_id)' 
                v-bind:key='comment.comments_id' 
                class='windowbg'>
            </comment>
            <error-box v-if="error !== null" @close="closeErrorAlert()">
                {{error}}
            </error-box>
            <div class="comment_posting">
                <div class='breeze_avatar avatar_comment'
                    v-bind:style='avatarImage(poster_data.avatar.href)'>           
                </div>
                <textarea 
                    v-model="post_comment.body" 
                    class="post_comment" 
                    :placeholder="place_holder" 
                    @focus="closeErrorAlert()"></textarea>
            </div>
            <div class="post_button_container floatright">
                <input type="submit" @click="postComment()" class="button">
            </div>
        </div>
    </div>`,
    filters: {
        formatDate: function(unixTime) {
            return moment.unix(unixTime).format('LLLL')
        },
    },
    methods: {
        avatarImage: function (posterImageHref) {
            return { backgroundImage: 'url(' + posterImageHref + ')' }
        },
        getUserData: function (userId) {
            return this.$root.getUserData(userId);
        },
        getCurrentUserData: function () {

        },
        postComment: function () {
            this.closeErrorAlert();

            if (!this.isValidComment())
                return false;

            axios.post(smf_scripturl + '?action=breezeComment;sa=postComment;'+ smf_session_var +'='+ smf_session_id,
                this.post_comment).then(response => {
                console.log(response)
            });
        },
        closeErrorAlert: function () {
            this.error = null;
        },
        isValidComment: function () {
            let isValid = true;

            if (this.post_comment.body === '' || typeof(this.post_comment.body) !== 'string' )
            {
                this.error = 'el body esta vacio';

                isValid = false;
            }

            if (this.post_comment.body === 'about:Suki')
            {
                alert('Back against the wall and odds\n' +
                    'With the strength of a will and a cause\n' +
                    'Your pursuits are called outstanding\n' +
                    'You\'re emotionally complex');

                isValid = false;
            }

            return isValid;
        }
    }
})

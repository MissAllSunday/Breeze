Vue.component('status', {
    props: ['status_item', 'poster_data', 'users'],
    data: function() {
        return {
            localComments: Object.assign({}, this.status_item.comments),
            comment_message: '',
            notice: null,
            place_holder: 'leave a comment',
            post_comment: {
                comments_poster_id: smf_member_id,
                comments_status_owner_id: this.status_item.status_poster_id,
                comments_profile_id: this.status_item.status_owner_id,
                comments_status_id: this.status_item.status_id,
                comments_body: '',
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
                v-for='comment in localComments' 
                v-bind:comment='comment' 
                v-bind:comment_poster_data='getUserData(comment.comments_poster_id)' 
                v-bind:key='comment.comments_id' 
                @removeComment="removeComment"
                class='windowbg'>
            </comment>
            <div v-if="notice === null"  class="comment_posting">
                <div class='breeze_avatar avatar_comment'
                    v-bind:style='avatarImage(poster_data.avatar.href)'>           
                </div>
                <textarea 
                    v-model="post_comment.comments_body" 
                    class="post_comment" 
                    :placeholder="place_holder" 
                    @focus="clearNotice()"></textarea>
            </div>
            <div v-if="notice === null" class="post_button_container floatright">
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
            return this.users[userId];
        },
        setLocalComment: function(comments){
            this.localComments = Object.assign({}, this.localComments, comments)
        },
        clearPostComment: function(){
            this.post_comment.comments_body = '';
        },
        postComment: function () {
            this.clearNotice();

            if (!this.isValidComment())
                return false;

            axios.post(smf_scripturl + '?action=breezeComment;sa=postComment;'+ smf_session_var +'='+ smf_session_id,
                this.post_comment).then(response => {

                    this.setNotice(response.data.message, response.data.type);

                    if (response.data.content){
                        this.$root.setUserData(response.data.content.users)
                        this.setLocalComment(response.data.content.comments);
                    }

                    this.clearPostComment();
            });
        },
        isValidComment: function () {
            if (this.post_comment.comments_body === '' || typeof(this.post_comment.comments_body) !== 'string' )
            {
                this.setNotice('el body esta vacio');

                return false;
            }

            if (this.post_comment.comments_body === 'about:Suki')
            {
                alert('Back against the wall and odds\n' +
                    'With the strength of a will and a cause\n' +
                    'Your pursuits are called outstanding\n' +
                    'You\'re emotionally complex');

                return false;
            }

            return true;
        },
        setNotice: function(message, type){
            type = type || 'error';
            let $this = this;
            this.notice = true;

            Vue.$toast.open({
                message: message,
                type: type,
                onClose: function () {
                    $this.notice = null;
                }
            });
        },
        clearNotice: function(){
            this.notice = null;
        },
        removeComment: function (commentId) {
            Vue.delete(this.localComments, commentId);
        }
    }
})

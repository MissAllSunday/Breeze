Vue.component('status', {
    props: ['status_item', 'poster_data', 'users'],
    data: function() {
        return {
            localComments: Object.assign({}, this.status_item.comments),
            comment_message: '',
            notice: null,
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
                v-for='comment in localComments' 
                v-bind:comment='comment' 
                v-bind:comment_poster_data='getUserData(comment.comments_poster_id)' 
                v-bind:key='comment.comments_id' 
                class='windowbg'>
            </comment>
            <message-box 
                v-if="notice !== null"
                 @close="clearNotice()"
                 v-bind:type="notice.type">
                {{notice.message}}
            </message-box>
            <div v-else="error === null"  class="comment_posting">
                <div class='breeze_avatar avatar_comment'
                    v-bind:style='avatarImage(poster_data.avatar.href)'>           
                </div>
                <textarea 
                    v-model="post_comment.body" 
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
            this.post_comment.body = '';

            setTimeout(this.clearNotice,5000)
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
            });
        },
        isValidComment: function () {
            if (this.post_comment.body === '' || typeof(this.post_comment.body) !== 'string' )
            {
                this.setNotice('el body esta vacio');

                return false;
            }

            if (this.post_comment.body === 'about:Suki')
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
            
            this.notice = {
                'message': message,
                'type': type + 'box',
            };
        },
        clearNotice: function(){
            this.notice = null;
        }
    }
})

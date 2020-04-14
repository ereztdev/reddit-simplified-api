<template>
    <div id="base">
        <h2 class="text-center my-2">Moneytime's Reddit API
            <span v-if="subRedditName">for
            <a :href="`https://www.reddit.com/r/${subRedditName}`">/r/{{subRedditName}}</a>
        </span>
        </h2>
        <h4 v-if="!subRedditName">There is no
            <span class="text-danger">{{erroneousSubreddit}}</span>
            subreddit, enter an existing subreddit into the url path
        </h4>
        <div id="upper_pane"class="container-fluid">
            <div v-if="subRedditName" class="redditButton--wrapper">
                <button id="nextPostsBatch" type="button" class="btn btn-default PostsBatch float-left"
                        aria-label="Left Align">
                    <span class="fa fa-arrow-circle-left fa-3x redditButton redditButton__back float-left"
                          aria-hidden="true">
                        &nbsp;Back
                    </span>
                </button>
                <button id="prevPostsBatch" type="button" class="btn btn-default PostsBatch float-right"
                        aria-label="Right Align">
                    <span class="fa fa-arrow-circle-right fa-3x redditButton redditButton__next" aria-hidden="true">
                        &nbsp;Next
                    </span>
                </button>
            </div>
            <div class="breaker-10"></div>
            <div class="reddit_posts--wrapper">
                <div class="row my-2 py-3 reddit_post" v-for="(post, index) in posts" :key="index"
                     :style="index % 2 === 0 ? 'background:#FFEDE9' : ''">
                    <div class="col-md-1 reddit_post__img"><img src="/imgs/redditLogo.png"></div>
                    <div class="col-md-9"><a :href="post.data.url">{{post.data.title}}</a></div>
                    <div class="col-md-2">{{getPostDate(post.data.created_utc)}}</div>
                </div>
            </div>
        </div></div>
        <div id="lower_pane"class="container-fluid"></div>
</template>

<script>
    export default {
        name: "App",
        data() {
            return {
                erroneousSubreddit: '',
                subredditFullQuery: {},
                subRedditName: null,
                posts: [],
            }
        },
        created() {
            this.subredditFullQuery = redditResponse;
            if (redditResponse === null) {
                this.erroneousSubreddit = window.location.pathname.substr(1)
            } else {
                this.subRedditIsViable(this.subredditFullQuery)
            }
        },
        methods: {
            subRedditIsViable(redditResponse) {
                let subRedditName = redditResponse.data.children[0].data.subreddit;
                if (!subRedditName) {
                    subRedditName = '';
                    this.erroneousSubreddit = window.location.pathname.substr(1)
                } else {
                    this.subRedditName = subRedditName;
                    this.posts = redditResponse.data.children
                }
            },
            getPostDate(unix_timestamp) {
                var a = new Date(unix_timestamp * 1000);
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                var year = a.getFullYear();
                var month = months[a.getMonth()];
                var date = a.getDate();
                var hour = a.getHours();
                var min = a.getMinutes();
                var sec = a.getSeconds();
                var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec;
                return time;
            }
        }
    }
</script>

<style scoped>

    .reddit_post__img img {
        width: 25px;
        vertical-align: middle;
    }

    .reddit_post {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .redditButton {
        color: #FF5700;
    }

    .breaker-10 {
        height: 10px;
        clear: both;
    }
</style>

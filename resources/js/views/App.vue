<template>
    <div id="base">
        <div class="header--wrapper">
            <h2 class="text-center my-2">Moneytime's Reddit API
                <span v-if="subRedditName">for
                <a :href="`https://www.reddit.com/r/${subRedditName}`">/r/{{subRedditName}}</a>
            </span>
            </h2>
            <h4 class="text-center" v-if="!subRedditName">There is no
                <span class="text-danger">{{erroneousSubreddit}}</span>
                subreddit, enter an existing subreddit into the url path<br>
                for instance:
                <a href="/sandiego">/r/sandiego</a>&nbsp;&nbsp;
                <a href="/technology">/r/technology</a>&nbsp;&nbsp;
                <a href="/api">/r/api</a>&nbsp;&nbsp;
            </h4>
        </div>
        <div id="upper_pane" class="container-fluid view_panes">
            <div class="loading"></div>
            <div class="reddit_posts--wrapper">
                <div :class="`row ${index !== 0 ? 'my-2' : ''} py-3 reddit_post`" v-for="(post, index) in posts"
                     :key="index"
                     :style="index % 2 === 0 ? 'background:#FFEDE9' : ''">
                    <div class="col-md-1 reddit_post__img"><img src="/imgs/redditLogo.png"></div>
                    <div class="col-md-9"><a :href="`https://www.reddit.com/${post.data.permalink}`">{{post.data.title}}</a></div>
                    <div class="col-md-2">{{getPostDate(post.data.created_utc)}}</div>
                </div>
                <div v-if="subRedditName" class="row mb-5 py-3 text-capitalize scrollMore" style="background: #ff4500">
                    <h1 class="text-center text-white w-100">scroll to load more</h1>
                </div>
            </div>
        </div>
        <div id="lower_pane" class="container-fluid view_panes">
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import * as d3 from "d3";

    export default {
        name: "App",
        components:{
        },
        data() {
            return {
                erroneousSubreddit: '',
                subredditFullQuery: {},
                subRedditName: null,
                posts: [],
                after: null,
                before: null,
                postsPerCall: 5
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
        mounted() {
            let self = this;
            $('.loading').hide()
            $("#postNumberDropdown .dropdown-item").on("click", function (event) {
                event.preventDefault();
                self.postsPerCall = $(event.target).data('value');
                self.reloadPosts(self.postsPerCall)
            });
            $(document).on('scroll', this.handleScroll);

        },
        methods: {
            handleScroll (event) {
                if($(window).scrollTop() + $(window).height() == $(document).height()) {
                    console.log("bottom!");
                    this.paginate('after')
                }
            },
            subRedditIsViable(redditResponse) {
                let subRedditName = redditResponse.data.children[0].data.subreddit;
                if (!subRedditName) {
                    subRedditName = '';
                    this.erroneousSubreddit = window.location.pathname.substr(1)
                } else {
                    this.subRedditName = subRedditName;
                    this.posts = redditResponse.data.children;
                    this.before = redditResponse.data.before;
                    this.after = redditResponse.data.after;
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
            },
            paginate(type) {
                let hash = type === 'after' ? this.after : this.before;
                $(document).off('scroll', this.handleScroll);

                $('.loading').show();
                $('.reddit_post').hide();
                $('.scrollMore').hide();

                axios.post('/api/lazyLoadCurrent', {
                    subreddit:window.location.pathname.substr(1),
                    limit:25,
                    type,
                    hash
                }).then(redditResponse => {
                    console.log(this.posts);
                    redditResponse.data.data.children.forEach((post) => {this.posts.push(post)});
                    this.before = redditResponse.data.data.before;
                    this.after = redditResponse.data.data.after;

                }).catch(e => {
                        this.errors.push(e)
                }).finally((e)=>{
                    let $upperPane = $("#upper_pane")
                    $('.loading').hide();
                    $(document).on('scroll', this.handleScroll);
                    $(document).scrollTop($upperPane.prop("scrollHeight")*0.75);
                    $('.reddit_post').show();
                    $('.scrollMore').show();
                });
            }
        }
    }
</script>

<style scoped>
    .reddit_post__img img {
        width: 25px;
        vertical-align: middle;
    }
    .header--wrapper {
        height: 100px;
    }
    .loading{
        position: fixed;
        width: 100%;
        height: 100%;
        margin-top: 50px;
        background: white url(/imgs/beerLoad.gif) center center no-repeat;
    }
</style>

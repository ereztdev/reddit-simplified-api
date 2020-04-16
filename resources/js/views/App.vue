<template>
    <div id="base">
        <div v-if="callError" id="call-alert" class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">x</button>
            {{callError}}
        </div>
        <div class="header--wrapper">
            <h2 class="text-center my-2">Erez's Reddit API
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
                <a href="/overwatch">/r/api</a>&nbsp;&nbsp;
            </h4>
            <toggle-button v-if="subRedditName" @change="onChangeEventHandler"
                           :labels="{checked: 'Reddit Posts', unchecked: 'Reddit Post Graph'}"
                           :width="250"
                           :height="35"
                           :font-size="18"
                           :value="true"
                           :switch-color="{checked: '#25EF02', unchecked: '#40B7EF'}"
            />
        </div>
        <div id="upper_pane" class="container-fluid view_panes">
            <div class="loading"></div>
            <div class="reddit_posts--wrapper">
                <div :class="`row ${index !== 0 ? 'my-2' : ''} py-3 reddit_post`" v-for="(post, index) in posts"
                     :key="index"
                     :style="index % 2 === 0 ? 'background:#FFEDE9' : ''">
                    <div class="col-md-1 reddit_post__img"><img src="/imgs/redditLogo.png"></div>
                    <div class="col-md-9">
                        <a :href="`https://www.reddit.com/${post.data.permalink}`">{{post.data.title}}</a>
                    </div>
                    <div class="col-md-2">{{getPostDate(post.data.created_utc)}}</div>
                </div>
                <div v-if="subRedditName" class="row mb-5 py-3 text-capitalize scrollMore" style="background: #ff4500">
                    <h1 class="text-center text-white w-100">scroll to load more</h1>
                </div>
            </div>
        </div>
        <div id="lower_pane" class="container-fluid view_panes">
            <div class="loading"></div>
            <div class="row mb-3">
                <div id="main" style="width: 100%;height:calc(67vh - 140px);"></div>
            </div>
            <div class="row">
                <div class="loading"></div>
                <div class="jumbotron w-75 px-2 mx-auto">
                    <h1 class="display-4 text-center text-uppercase">Statistical Information</h1>
                    <ul class="list-group">
                        <li class="list-group-item">Average: {{postGraphStats.average}}</li>
                        <li class="list-group-item">Median: {{postGraphStats.median}}</li>
                        <li class="list-group-item">Maximum: {{postGraphStats.max}}</li>
                        <li class="list-group-item">Minimum: {{postGraphStats.min}}</li>
                    </ul>
                    <h1 class="display-4 text-center text-uppercase">conclusion</h1>
                    <h3 class="text-center">Your chances of getting a likable post</h3>
                    <span v-html="postGraphStats.bestPostTime"></span>
                    <hr class="my-4">
                    <a @click="paginate('after',true, $event)" class="btn btn-primary btn-lg" href="#" role="button">Add 100
                        more
                        observations</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import ECharts from 'echarts'
    import 'echarts/lib/chart/line'
    import {ToggleButton} from 'vue-js-toggle-button'

    export default {
        name: "App",
        components: {
            "toggle-button": ToggleButton,
            'v-chart': ECharts,
        },
        data() {
            return {
                callError: null,
                erroneousSubreddit: '',
                subredditFullQuery: {},
                subRedditName: null,
                posts: [],
                after: null,
                before: null,
                postGraphStats: {
                    XYMatrix: [],
                    upSeriesY: [],
                    timeSeriesX: [],
                    average: null,
                    median: null,
                    min: null,
                    max: null,
                    bestPostTime: null,
                }
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
            $('.loading').hide();
            $('#lower_pane').hide();
            $(document).on('scroll', this.handleScroll);
        },
        methods: {
            errorHandler(error) {
                if (error = "Error: Request failed with status code 500") {
                    this.callError = "Reddit's API timed out (500), what ever you did, do it again, should work, if not, refresh!";
                    $("#call-alert").delay(2500).fadeOut();
                }
            },
            computeGraphStats() {
                let PGS = this.postGraphStats;
                let occurrenceInDay = {night: 0, morning: 0, noon: 0, evening: 0};

                let timeOfDay = function (hour) {
                    if (hour >= 0 && hour <= 6) {
                        occurrenceInDay.night += 1
                    } else if (hour > 6 && hour <= 12) {
                        occurrenceInDay.morning += 1
                    } else if (hour > 12 && hour <= 18) {
                        occurrenceInDay.noon += 1
                    } else if (hour > 18 && hour <= 23) {
                        occurrenceInDay.evening += 1
                    }
                };

                const median = arr => {
                    const mid = Math.floor(arr.length / 2),
                        nums = [...arr].sort((a, b) => a - b);
                    return arr.length % 2 !== 0 ? nums[mid] : (nums[mid - 1] + nums[mid]) / 2;
                };

                PGS.average = ((PGS.upSeriesY.reduce((a, b) => a + b, 0)) / PGS.upSeriesY.length).toFixed(2);
                PGS.median = median(PGS.upSeriesY);
                PGS.min = Math.min(...PGS.upSeriesY);
                PGS.max = Math.max(...PGS.upSeriesY);

                PGS.XYMatrix.forEach(post => {
                    if (Object.values(post) > PGS.average) {
                        timeOfDay(Object.keys(post))
                    }
                });
                let allOccurrences = 0;
                Object.keys(occurrenceInDay).forEach(function (partOfDay) {
                    allOccurrences += occurrenceInDay[partOfDay]
                });
                PGS.bestPostTime = `
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Morning:</strong>${(occurrenceInDay.morning / allOccurrences * 100).toFixed(2)}%</li>
                        <li class="list-group-item"><strong>Noon:</strong>${(occurrenceInDay.noon / allOccurrences * 100).toFixed(2)}%</li>
                        <li class="list-group-item"><strong>Evening:</strong>${(occurrenceInDay.evening / allOccurrences * 100).toFixed(2)}%</li>
                        <li class="list-group-item"><strong>Night:</strong>${(occurrenceInDay.night / allOccurrences * 100).toFixed(2)}%</li>
                    </ul>
                    `;
            },
            onChangeEventHandler(event) {
                if (!event.value) {
                    $('#lower_pane').show();
                    $('#upper_pane').hide();
                    this.generateChart();
                    $(document).off('scroll', this.handleScroll);
                } else {
                    $('#lower_pane').hide();
                    $('#upper_pane').show();
                    $(document).on('scroll', this.handleScroll);
                }
            },
            generateChart() {
                this.posts.forEach(post => {
                    let postUps = post.data.ups;
                    let postTime = post.data.created_utc;
                    if (postUps > 100) {
                        this.postGraphStats.XYMatrix.push({[this.getPostDate(postTime, false, true)]: postUps});
                        this.postGraphStats.upSeriesY.push(postUps);
                        this.postGraphStats.timeSeriesX.push(this.getPostDate(postTime, true))
                    }
                });

                let myChart = ECharts.init(document.getElementById('main'));
                var option = {
                    xAxis: {
                        type: 'category',
                        axisLabel: {
                            interval: 1,
                            rotate: 45
                        },
                        data: []
                    },
                    yAxis: {
                        type: 'value'
                    },
                    grid: {
                        bottom: 0,
                        containLabel: true
                    },
                    series: [{
                        data: [],
                        type: 'line',
                        smooth: true
                    }]
                };
                option.xAxis.data = this.postGraphStats.timeSeriesX;
                option.series = [{
                    data: this.postGraphStats.upSeriesY,
                    type: 'line',
                    smooth: true
                }];
                myChart.setOption(option);
                this.computeGraphStats();

            },
            handleScroll() {
                if ($(window).scrollTop() + $(window).height() + 2 >= $(document).height()) {
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
            getPostDate(unix_timestamp, onlyTime = false, onlyHour = false) {
                let dateTime;
                var a = new Date(unix_timestamp * 1000);
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                var year = a.getFullYear();
                var month = months[a.getMonth()];
                var date = a.getDate();
                var hour = a.getHours().toString().length === 1 ? "0" + a.getHours() : a.getHours();
                var min = a.getMinutes().toString().length === 1 ? "0" + a.getMinutes() : a.getMinutes();
                var sec = a.getSeconds().toString().length === 1 ? "0" + a.getSeconds() : a.getSeconds();
                if (onlyTime) {
                    dateTime = hour + ':' + min + ':' + sec;
                } else if (onlyHour) {
                    dateTime = hour
                } else {
                    dateTime = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec;
                }
                return dateTime;
            },
            paginate(type, fromChart = false, event) {
                event.preventDefault();
                let hash = type === 'after' ? this.after : this.before;
                if (!fromChart) {
                    $(document).off('scroll', this.handleScroll);
                }
                $('.loading').show();
                $('.reddit_post').hide();
                $('.scrollMore').hide();

                if (fromChart){
                    $('#lower_pane .row').hide();
                }

                axios.post('/api/lazyLoadCurrent', {
                    subreddit: window.location.pathname.substr(1),
                    limit: 100,
                    type,
                    hash
                }).then(redditResponse => {
                    redditResponse.data.data.children.forEach((post) => {
                        this.posts.push(post)
                    });
                    this.before = redditResponse.data.data.before;
                    this.after = redditResponse.data.data.after;

                }).catch(e => {
                    this.errorHandler(e)
                }).finally((e) => {
                    let $upperPane = $("#upper_pane")
                    $('.loading').hide();
                    if (!fromChart) {
                        $(document).on('scroll', this.handleScroll);
                        $(document).scrollTop($upperPane.prop("scrollHeight") * 0.75);
                        $('.reddit_post').show();
                        $('.scrollMore').show();
                    }
                    if (fromChart) {
                        $('#lower_pane .row').show();
                        this.generateChart();
                    }
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

    .loading {
        position: fixed;
        width: 100%;
        height: 100%;
        margin-top: 50px;
        background: white url(/imgs/beerLoad.gif) center center no-repeat;
    }
</style>

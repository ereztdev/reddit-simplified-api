## Reddit Simplified API
![](https://github.com/ereztdev/reddit-simplified-api/blob/master/public/imgs/Screenshot_3.png?raw=true)
### pre-requisites
- **Composer** - if you don't have it, you can [download it right here](https://getcomposer.org/Composer-Setup.exe).
- **Node.JS** -  if you don;t have it, you can [download it right here](https://nodejs.org/dist/v12.16.2/node-v12.16.2-x64.msi).
### Installation procedures
* Either download or clone (`git clone https://github.com/ereztdev/reddit-simplified-api.git`) this repo into your webserver 
* switch into the repo directory where you pulled the repo: `cd reddit-simplified-api`
* `composer install` to install Laravel dependencies
* `npm install` to install node.js dependencies
* `npm run dev` to transpile all frontend assets
* in your root dir (`reddit-simplified-api`), create a file named `.env` and copy the contents of `.env.example` into 
the newly created `.env`
* run the command `php artisan key:generate`
* either use your own web server (XAMPP/WAMPP/Whatev) or run `php artisan serve`, though not so recommended due to some
issues with PHP  ^7.3.1

### A few points about this repo
* Reddit's API has some throttling issues with Reddit's non-OAuth API, I created a timeout of 8 seconds with an exception 
that will notify you about the timeout.
* Re statistics, it's a basic representation. The threshold for upvotes is x<100, and I use a volume weighted average 
to determine which part of the day would make more sense to post. The right way to execute this in a more thorough manner
is gaining a large amount of observations and trying to create centroids that would look for clustering hours that have
high upvotes. Also to take into consideration that there is a **large** amount of companies that offer Reddit bot 
services, for posts, likes and influence, you can find alot of those in [/r/politics](https://reddit.com/r/politics).


All the best,
Erez

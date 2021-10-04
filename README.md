# A Collection of Laravel Projects

This repository contains a collection of applications built with Laravel.<br/>
At the moment I've been hacking around WebRTC so most of the applications are about WebRTC.<br/>
Most of them are demo applications for various Technical Articles I've written and going to
write on [Dev.to](https://dev.to/mupati) and [Medium](https://mupati.medium.com).

There are endpoints for some other applications I've built as well.

## Consider Sponsoring.
I plan to explore various Real Time Communication offerings and build demo apps and write about them. To keep me going, you may consider sponsoring so that I dedicate a enough time to it. Interestingly, it seems a lot of people have found it helpful given the number of emails and queries I receive for support in one way or the other.

[:heart: Sponsor](https://dashboard.flutterwave.com/donate/9oiquwbuo2ml)


## Project Setup

1. Clone the repository.<br/>
`git clone https://github.com/Mupati/laravel-video-chat`

2. Install dependencies<br/>
`composer install && npm install`

3. Create your env file from the example.<br/>
`cp env.example env`

4. Add your db details, pusher API keys and  TURN SERVER credentials.
   

## Running the Application

1. `php artisan serve` to start the server and `npm run start` to start the frontend.
2. Note that the register endpoint has been removed to prevent people from creating <br/> 
   a lot of users when they want to try out the online demo. In your local copy you can enable it in the `routes/web.php` file.


## Available Applications
The following are the available applications and the links to the article I've written about it if available:


1. **Custom WebRTC Applications**
   * **Live stream with WebRTC in your Laravel application**<br/>
     A Live streaming application built with WebRTC using the simple-peer.js package<br/>
     [Medium Link](https://mupati.medium.com/live-stream-with-webrtc-in-your-laravel-application-b6ecc13d8509)<br/>
     [Dev.to Link](https://dev.to/mupati/live-stream-with-webrtc-in-your-laravel-application-2kl3)

   * **Adding Video Chat To Your Laravel App**<br/>
     This is one-on-one video call application with WebRTC using the simple-peer.js package<br/>
     [Medium Link](https://mupati.medium.com/adding-video-chat-to-your-laravel-app-9e333c8a01f3)<br/>
     [Dev.to Link](https://dev.to/mupati/adding-video-chat-to-your-laravel-app-5ak7)

2. **Agora Platform Applications**
   * **Build a Scalable Video Chat App with Agora in Laravel**<br/>
     This is one-on-one video call application with [Agora Platform](https://agora.io)<br/>
     [Medium Link](https://mupati.medium.com/build-a-scalable-video-chat-app-with-agora-in-laravel-29e73c97f9b0)<br/>
     [Dev.to Link](https://dev.to/mupati/using-agora-for-your-laravel-video-chat-app-1mo)

3. **[Wossop](https://wossop.netlify.app/)**<br/>
   This is a messaging and video chat application with the WhatsApp web interface.<br/>
   The APIs are in this repository but the frontend sits elsewhere. I don't plan to blog about it.

## Test Accounts for the Application
1. Visit Demo url: https://laravel-video-call.herokuapp.com/login
2. Login with these test accounts and test it <br/>
    email:            password <br/>
    foo@example.com:  DY6m7feJtbnx3ud<br/>
    bar@example.com:  Me3tm5reQpWcn3Q<br/>


# UI for one-on-one Video Call with WebRTC 
### Incoming Call UI
![Incoming Call](https://dev-to-uploads.s3.amazonaws.com/i/1qk47qwka8iz0m43tmdu.png)

### Video Chat Session
![Video Chat](https://dev-to-uploads.s3.amazonaws.com/i/80q8j4yxg6dp8xgb36ql.png)
<?php

namespace Drupal\socialmedia;

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

class TwitterService{
    protected $twitter;
    protected $request;
    protected $configFactory;

    /**
     * constructor for twitter service
     */
    public function __construct(RequestStack $request,ConfigFactoryInterface $configFactory){
        $this->request = $request;
        $this->configFactory = $configFactory;
    }

    public function getRegistrationLink(){
        $config = $this->configFactory->get('socialmedia.api_detail');
        $consumer_key = $config->get('twitter_consumer_key');
        $consumer_secret = $config->get('twitter_consumer_secret');
        $redirect_url = $config->get('twitter_return_url');
        $this->twitter = new TwitterOAuth(
            $consumer_key,
            $consumer_secret
        );
        $request_token = $this->twitter->oauth(
            'oauth/request_token',
            ['oauth_callback' => $redirect_url]
        );
        $session = $this->request->getCurrentRequest()->getSession();
        $session->set('oauth_token',$request_token['oauth_token']);
        $session->set('oauth_token_secret',$request_token['oauth_token_secret']);
        $url = $this->twitter->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        return $url;
    }
    public function getAccessToken(){
        $oauth_token = $this->request->getCurrentRequest()->query->get('oauth_token');
        $oauth_verifier = $this->request->getCurrentRequest()->query->get('oauth_verifier');
        $session = $this->request->getCurrentRequest()->getSession();
        $config = $this->configFactory->get('socialmedia.api_detail');
        $consumer_key = $config->get('twitter_consumer_key');
        $consumer_secret = $config->get('twitter_consumer_secret');
        $this->twitter = new TwitterOAuth(
            $consumer_key,
            $consumer_secret,
            $session->get('oauth_token'),
            $session->get('oauth_token_secret'),
        );
        $access_token = $this->twitter->oauth("oauth/access_token", ["oauth_verifier" => $oauth_verifier]);
       return $access_token;
    }

    public function startService($accessToken){
        $config = $this->configFactory->get('socialmedia.api_detail');
        $consumer_key = $config->get('twitter_consumer_key');
        $consumer_secret = $config->get('twitter_consumer_secret');
        $this->twitter = new TwitterOAuth(
            $consumer_key,
            $consumer_secret,
            $accessToken['oauth_token'],
            $accessToken['oauth_token_secret'],
        );
        $this->twitter->setTimeouts(20,30);
    }

    public function uploadTwitterPost($message,array $url){
        $media =[];
        $totle_file = sizeof($url);
        for($i=0;$i<$totle_file;$i++){
            $file = $this->twitter->upload('media/upload', ['media' => $url[$i]]);
            array_push($media,$file->media_id_string);
        }
        $parameters = [
            'status' => $message,
            'media_ids' => implode(',', $media)
        ];
        try{
            $result = $this->twitter->post('statuses/update', $parameters);
            return true;
        }
        catch(Exception  $err){
            dpm($err);
            return false;
        }
      
    }
}
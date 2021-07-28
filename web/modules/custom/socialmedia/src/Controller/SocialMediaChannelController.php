<?php

namespace Drupal\socialmedia\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\socialmedia\FacebookService;
use Drupal\socialmedia\TwitterService;
use Drupal\socialmedia\LinkedinService;
use Drupal\socialmedia\SocialMediaDatabaseService;


/**
 * Controller for Listing SocialMedia Channel
*/
class SocialMediaChannelController extends ControllerBase{
    /**
     *  @var \Drupal\socialmedia\SocialMediaDatabaseService
    */
    protected $database;
    protected $facebook;
    protected $twitter;
    protected $linkedin;
    /**
     * constuctor to initialize social media platform services
     */
    public function __construct(FacebookService $facebook,SocialMediaDatabaseService $database,TwitterService $twitter,LinkedinService $linkedin){
        $this->facebook = $facebook;
        $this->database = $database;
        $this->twitter = $twitter;
        $this->linkedin = $linkedin;
    }


    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('socialmedia.facebook_service'),
            $container->get('socialmedia.database_service'),
            $container->get('socialmedia.twitter_service'),
            $container->get('socialmedia.linkedin_service')
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getLinks(){
        $fblink = $this->facebook->getRegistrationLink();
        $twitterlink = $this->twitter->getRegistrationLink();
        $linkedinlink = $this->linkedin->getRegistrationLink();
        $render = [
            '#theme' => 'socialmedia_channel',
        ];
        $render['#fblink'] = $fblink;
        $render['#twitterlink'] = $twitterlink;
        $render['#linkedinlink'] = $linkedinlink;
        return $render; 
    }
     /**
     * {@inheritdoc}
     */
    public function registerFacebookToken(){
        $accessToken = $this->facebook->getAccessToken();
        if($accessToken){
            $registerToken = $this->database->registerFacebookToken($accessToken);
            $render = [
                '#theme' => 'socialmedia_channel_facebook_return',
            ];
            $render['#successmessage'] = 'registration successful';
            return $render; 
        }
        else{
            $render = [
                '#theme' => 'socialmedia_channel_return',
            ];
            $render['#error'] = 'registration unsuccessful try again later';
            return $render; 
        }
    }

    public function registerTwitterToken(){
        $accessToken = $this->twitter->getAccessToken();
        dpm($accessToken);
        if($accessToken){
            $registerToken = $this->database->registerTwitterToken($accessToken);
            $render = [
                '#theme' => 'socialmedia_channel_twitter_return',
            ];
            $render['#successmessage'] = 'registration successful';
            return $render; 
        }
        else{
            $render = [
                '#theme' => 'socialmedia_channel_return',
            ];
            $render['#error'] = 'registration unsuccessful try again later';
            return $render; 
        }
    }

    public function registerLinkedinToken(){
        $accessToken = $this->linkedin->getAccessToken();
        $render = [
            '#theme' => 'socialmedia_channel_linkedin_return',
        ];
        if($accessToken){
            //dpm($accessToken);
            $registerToken = $this->database->registerLinkedinToken($accessToken);
            $render['#successmessage'] = 'registration successful';
           
        }
        else{
            $render['#error'] = 'registration unsuccessful try again later';
        }
        return $render; 
    }
}

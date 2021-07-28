<?php
namespace Drupal\socialmedia\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\socialmedia\SocialMediaPostEvent;
use Drupal\socialmedia\FacebookService;
use Drupal\socialmedia\TwitterService;
use Drupal\socialmedia\LinkedinService;
use Drupal\socialmedia\SocialMediaDatabaseService;


class SocialMediaPostEventSubscriber implements EventSubscriberInterface{
    protected $facebook;
    protected $twitter;
    protected $linkedin;
    protected $database;
    public function __construct(SocialMediaDatabaseService $database,FacebookService $facebook,TwitterService $twitter,LinkedinService $linkedin){
        $this->facebook = $facebook;
        $this->twitter = $twitter;
        $this->linkedin = $linkedin;
        $this->database = $database;
    }
    public static function getSubscribedEvents() {
        return [
          // Static class constant => method on this class.
          SocialMediaPostEvent::EVENT => 'SocialMediaPost',
        ];
    }    
    public function SocialMediaPost(SocialMediaPostEvent $event){
      $platform = $event->getPlatform();
      $id = $this->database->getUserId();
      $message =$event->getMessage() ;
      $url = $event->getUrl() ;
      if($platform == 'facebook'){
        $pageToken = $event->getFacebookToken();
        $postfb = $this->facebook->uploadFacebookPost($message,$url,$pageToken);
      }
      if($platform == 'linkedin'){
        $linkedinAccessToken = $this->database->getLinkedinAccessToken($id);
        $this->linkedin->setToken($linkedinAccessToken);
        $postlinkedin = $this->linkedin->uploadLinkedinPost($message,$url);
      }
      if($platform == 'twitter'){
        $twitterAccessToken =  $this->database->getTwitterAccessToken($id);
        $this->twitter->startService($twitterAccessToken);
        $posttwitter = $this->twitter->uploadTwitterPost($message,$url);
      }
    }
    
}
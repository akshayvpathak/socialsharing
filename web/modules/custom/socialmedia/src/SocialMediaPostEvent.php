<?php

namespace Drupal\socialmedia;

use Symfony\Component\EventDispatcher\Event;
use Drupal\socialmedia\SocialMediaDatabaseService;
use Drupal\socialmedia\FacebookService;
use Drupal\socialmedia\TwitterService;
use Drupal\socialmedia\LinkedinService;


class SocialMediaPostEvent extends Event {

  const EVENT = 'socialmedia.post_event';
  /**
   * @var platform
  */
  protected $platform;

  /**
   * @var message
  */

  protected $message;

  /**
   * @var url
  */
  protected $url;

  /**
   * @var facebook token
  */

  protected $facebookToken;
  
  /**
   * @var twitter token 
  */

  protected $twitterToken;

  /**
   * @var linkedin token 
  */

  protected $linkedinToken;

  /**
   * set the social media platform
  */
  public function setPlatform($platform){
    $this->platform = $platform;
  }

  public function getPlatform(){
    return $this->platform;
  }

  public function setMessage($message){
    $this->message = $message;
  }
  public function getMessage(){
    return $this->message;
  }
  public function setUrl($url){
    $this->url = $url;
  }
  public function getUrl(){
    return $this->url;
  }

  public function setFacebookToken($token){
    $this->facebookToken = $token;
  }

  public function getFacebookToken(){
    return $this->facebookToken;
  }

  public function setTwitterToken($token){
    $this->twitterToken = $token;
  }

  public function getTwitterToken(){
    return $this->twitterToken;
  }

  public function setLinkedinToken($token){
    $this->linkedinToken = $token;
  }

  public function getLinkedinToken(){
    return $this->linkedinToken;
  }

}

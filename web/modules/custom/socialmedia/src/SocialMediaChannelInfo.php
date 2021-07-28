<?php

namespace Drupal\socialmedia;

use Drupal\socialmedia\SocialMediaDatabaseService;
use Drupal\socialmedia\FacebookService;
use Drupal\socialmedia\TwitterService;
use Drupal\socialmedia\LinkedinService;

class SocialMediaChannelInfo{
    /**
     * @var \Drupal\socialmedia\SocialMediaDatabaseService
     */
    protected $database;

    /**
     * @var \Drupal\socialmedia\FacebookService
     */

    protected $facebook;

    /**
     * @var \Drupal\socialmedia\TwitterService
    */

    protected $twitter;

    /**
     * @var \Drupal\socialmedia\LinkedinService
    */

    protected $linkedin;

    /**
     * constructor for social media channel info
    */

    public function __construct(SocialMediaDatabaseService $database,FacebookService $facebook,TwitterService $twitter,LinkedinService $linkedin){
        $this->database = $database;
        $this->twitter = $twitter;
        $this->linkedin = $linkedin;
        $this->facebook = $facebook;
    }

    /**
     * facebookpageDetail return the available option for facebook page
    */

    public function facebookPageDetail(){
        $id = $this->database->getUserId();
        $fbAccessToken = $this->database->getFacebookAccessToken($id);
        $this->facebook->setToken($fbAccessToken);
        $pagedetail = $this->facebook->pageDetail();
        $total_page = sizeof($pagedetail);
        for($i=0;$i<$total_page;$i++){
            $option[$pagedetail[$i]['id']] = $pagedetail[$i]['name'];
        } 
      return $option;
    }

    /**
     * facebook pagetoken
     * return the facebook pagetoken from facebook pageId
    */

    public function facebookPageToken($pageId){
        $id = $this->database->getUserId();
        $fbAccessToken = $this->database->getFacebookAccessToken($id);
        $this->facebook->setToken($fbAccessToken);
        return $this->facebook->getPageToken($pageId);
    }

    /**
     * {@inheritdoc}
     */

    public function availableChannel(){
        $channel = $this->database->getChannelInfo();
        return $channel;
    }



}
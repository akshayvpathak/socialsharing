<?php

namespace Drupal\socialmedia;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Database\Connection;


class SocialMediaDatabaseService{
    /**
     *  @var \Drupal\Core\Session\AccountProxyInterface
    */
    protected $currentUser;
    /**
     *  @var \Drupal\Core\Database\Connection
     */
    protected $database;

    public function __construct(AccountProxyInterface $currentUser,Connection $database){
        $this->database = $database;
        $this->currentUser = $currentUser;
    }
    public function getUserId(){
        return $this->currentUser->id();
    }
    public function getCurrentDate(){
        $datetime = new \Moment\Moment('now', 'Asia/Kolkata');
        return $datetime->format('Y-m-d H:i:s');        
    }
    public function getEndDate(){
        $datetime = new \Moment\Moment('now', 'Asia/Kolkata');
        $datetime->addMonths('2');
        return $datetime->format('Y-m-d H:i:s');        
    }
    public function registerFacebookToken($accessToken){
        $id = $this->getUserId();
        if($this->isRegisteredBefore($id)){
            $this->updateFacebookToken($id,$accessToken);
        }
        else{
            $this->insertFacebookToken($id,$accessToken);
        }
    }
    public function registerTwitterToken($accessToken){
        $id = $this->getUserId();
        if($this->isRegisteredBefore($id)){
            dpm($id);
            dpm($accessToken);
            $this->updateTwitterToken($id,$accessToken);
        }
        else{
            $this->insertTwitterToken($id,$accessToken);
        }
    }
    public function registerLinkedinToken($accessToken){
        $id = $this->getUserId();
        if($this->isRegisteredBefore($id)){
            $this->updateLinkedinToken($id,$accessToken);
        }
        else{
            $this->insertLinkedinToken($id,$accessToken);
        }
    }
    public function isRegisteredBefore($id){
        $result = $this->database->select('social_media', 's')
        ->fields('s')
        ->condition('user_id',$id)
        ->execute();
        $records = $result->fetchAll();  
        if(empty($records)){
            return false;
        }
        else{
            return true;
        }
    }

    public function getChannelInfo(){
        $id = $this->getUserId();
        if($this->isRegisteredBefore($id)){
            $channle['facebook'] = $this->getFacebookAccessToken($id);
            $channle['twitter'] = $this->getTwitterAccessToken($id);
            $channle['linkedin'] = $this->getLinkedinAccessToken($id);
        }
        else{
            $channle['facebook'] = false;
            $channle['twitter']  = false;
            $channle['linkedin'] = false;
        }
        return $channle;
    }

    public function insertFacebookToken($id,$accessToken){
        $start_date = $this->getCurrentDate();
        $end_date = $this->getEndDate();
        $fields = ['user_id' => $id,
         'fb_access_token' => $accessToken,
         'fb_start_date' =>$start_date ,
         'fb_end_date' =>$end_date ,
         'created_date' =>$start_date
        ];
        $id = $this->database->insert('social_media')
          ->fields($fields)
          ->execute(); 
    }
    public function updateFacebookToken($id,$accessToken){
        $start_date = $this->getCurrentDate();
        $end_date = $this->getEndDate();
        $fields = ['fb_access_token' => $accessToken,
         'fb_start_date' =>$start_date ,
         'fb_end_date' =>$end_date ,
         'created_date' =>$start_date
        ];
        $result = $this->database->update('social_media')
        ->condition('user_id',$id)
        ->fields($fields)
        ->execute(); 
    }
    public function getFacebookAccessToken($id){
        $result = $this->database->select('social_media', 's')
        ->fields('s')
        ->condition('user_id',$id)
        ->execute()
        ->fetchAllAssoc('user_id');
        $accessToken = $result[$id]->fb_access_token;
        return $accessToken;
    }
    public function getTwitterAccessToken($id){
        $result = $this->database->select('social_media', 's')
        ->fields('s')
        ->condition('user_id',$id)
        ->execute()
        ->fetchAllAssoc('user_id');
        $accessToken['oauth_token'] = $result[$id]->twitter_oauth_token;
        $accessToken['oauth_token_secret'] = $result[$id]->twitter_oauth_token_secret;
        return $accessToken;
    }
    public function getLinkedinAccessToken($id){
        $result = $this->database->select('social_media', 's')
        ->fields('s')
        ->condition('user_id',$id)
        ->execute()
        ->fetchAllAssoc('user_id');
        $accessToken= $result[$id]->linkedin_access_token;
        return $accessToken;
    }
    public function updateTwitterToken($id,$accessToken){
        $start_date = $this->getCurrentDate();
        $end_date = $this->getEndDate();
        $fields = ['twitter_oauth_token_secret' => $accessToken['oauth_token_secret'],
         'twitter_oauth_token' => $accessToken['oauth_token'],
         'twitter_start_date' =>$start_date ,
         'twitter_end_date' =>$end_date ,
         'created_date' =>$start_date
        ];
        $result = $this->database->update('social_media')
        ->fields($fields)
        ->condition('user_id',$id)
        ->execute();
    }
    public function insertTwitterToken($id,$accessToken){
        $start_date = $this->getCurrentDate();
        $end_date = $this->getEndDate();
        $fields = ['user_id' => $id,
         'twitter_oauth_token_secret' => $accessToken['oauth_token_secret'],
         'twitter_oauth_token' => $accessToken['oauth_token'],
         'twitter_start_date' =>$start_date ,
         'twitter_end_date' =>$end_date ,
         'created_date' =>$start_date
        ];
        $result = $this->database->insert('social_media')
        ->fields($fields)
        ->execute();
    }
    public function updateLinkedinToken($id,$accessToken){
        $start_date = $this->getCurrentDate();
        $end_date = $this->getEndDate();
        $fields = ['linkedin_access_token' => $accessToken,
         'linkedin_start_date' =>$start_date ,
         'linkedin_end_date' =>$end_date ,
         'created_date' =>$start_date
        ];
        $result = $this->database->update('social_media')
        ->fields($fields)
        ->condition('user_id',$id)
        ->execute();
    }
    public function insertLinkedinToken($id,$accessToken){
        $start_date = $this->getCurrentDate();
        $end_date = $this->getEndDate();
        $fields = ['user_id' => $id,
        'linkedin_access_token' => $accessToken,
        'linkedin_start_date' =>$start_date ,
        'linkedin_end_date' =>$end_date ,
        'created_date' =>$start_date
        ];
        $result = $this->database->insert('social_media')
        ->fields($fields)
        ->execute();
    }
}

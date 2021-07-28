<?php
namespace Drupal\fbshare;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
/**
 * Contains Facebook logic that is used for connect facebook manually
 * later we can add config factory setting and get app id from that
 */

class FbShareFacebookConnect{
    protected $facebook;
    protected $token;
    protected $userId;
    protected $pageToken;
    protected $pageId;
    protected $pageName;
/**
 *  Constructor
 * setup facebook for making get post request
 * 
*/
    public function __construct() {
        //it is needed to start session before using facebook
        //uncommenting this while user is not admin
        /* $session = \Drupal::service('session');
        if (!$session->isStarted()) {
          $session->migrate();
        } */
        $this->facebook = new Facebook([
            'app_id' => '200198691771297',
            'app_secret' => '1ced23b218e39c0f6d5ddc87ab2a6f11',
            'default_graph_version' => 'v9.0',
            'fileUpload' => true,
            'persistent_data_handler'=>'session'
           ]);
    }
    public function setToken($token){
        $this->token = $token;
    } 
    public function setUserId($id){
        $this->userId = $id; 
    }
    public function setPageId($id){
        $this->pageId = $id;
    }
    public function setPageName($name){
        $this->pageName = $name;
    }
    public function setPageToken($token){
        $this->pageToken = $token;
    }
    public function basicDetail(){
        try{
            $response = $this->facebook->get(
                '/me',
                $this->token
            );
              $graphNode = $response->getGraphNode();
              return $graphNode;
        }
        catch(FacebookResponseException $ex){
            return false;
        }

    }
    public function pageDeatail(){
        try{
            $response = $this->facebook->get(
                '/me/accounts',
                $this->token
            );
              $graphNode = $response->getGraphEdge();
              return $graphNode;
        }
        catch(FacebookResponseException $ex){
            return false;
        }
    }

    public function uploadPhoto($message,$url){
         $data = [
            'message' => $message,
            'source' => $this->facebook->fileToUpload($url),
            'user_selected_tags' => true,
            'place' => 'link',
          ];    
          try {
            $response = $this->facebook->post('/me/photos', $data, $this->pageToken);
            drupal_set_message('Posted Successfully');
            return $response->getGraphNode();
          } catch(FacebookResponseException $e) {
            dpm($e);
            drupal_set_message('Unable to Post');
        }
    }

    public function uploadMultiplePhoto($message,array $url){
        $total_images = sizeof($url);
        $data = [];
        $data['message'] = $message;
        $data['og_action_type_id'] = '383634835006146';
        $data['og_icoon_id'] = '436344223195445';
        $data['og_object_id'] = '467368809976558';
        $data['place'] = '106039436102339';
        $data['tags'] = '103737438294175';
        for($i=0 ;$i<$total_images;$i++){
            $data["attached_media[{$i}]"] = $this->getMediaId($url[$i]) ;
        }
        try {
            $response = $this->facebook->post('/me/feed', $data, $this->pageToken);
            drupal_set_message('Posted Successfully');
        }
        catch(FacebookResponseException $e) {
                drupal_set_message('Unable to Post');
            } catch(FacebookSDKException $e) {
                drupal_set_message('Unable to Post');
        }
    }

    private function getMediaId($url){
        $data = [
            'source' => $this->facebook->fileToUpload($url),
            'published' => false
           ];
           try {
             // Returns a `Facebook\FacebookResponse` object
             $response = $this->facebook->post('/me/photos', $data, $this->pageToken);
             $graphNode = $response->getGraphNode();
             $object = new \stdClass();
             $object->media_fbid = $graphNode['id'];
             return $object;
           } catch(FacebookResponseException $e) {
            drupal_set_message('Unable to Post');
        } catch(FacebookSDKException $e) {
            drupal_set_message('Unable to Post');
        }  
    }


}
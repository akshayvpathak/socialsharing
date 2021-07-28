<?php

namespace Drupal\socialmedia;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Drupal\Core\Config\ConfigFactoryInterface;


/**
 * sharing and managing the facebook
 */
class FacebookService{
    protected $facebook;
    protected $token;
    protected $configFactory;
    /**
     * constructor for facebook service
     *  
     **/   
    public function __construct(ConfigFactoryInterface $configFactory){
        $this->configFactory = $configFactory;
        $config = $this->configFactory->get('socialmedia.api_detail');
        $app_id = $config->get('facebook_app_id');
        $app_secret = $config->get('facebook_app_secret');

        /**
         * Initialize the Facebook service
         */
        $this->facebook = new Facebook([
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v9.0',
            'fileUpload' => true,
            'persistent_data_handler'=>'session'
        ]);
    }
    /**
     * facebook user registration link
     */
    public function getRegistrationLink(){
        $config = $this->configFactory->get('socialmedia.api_detail');
        $returnUrl = $config->get('facebook_app_return_url');
        $helper = $this->facebook->getRedirectLoginHelper();
        $permissions = ['email','pages_show_list','publish_to_groups','pages_read_engagement','pages_read_user_content','pages_manage_posts','pages_manage_engagement','public_profile']; // Optional permissions
        $loginUrl = $helper->getLoginUrl($returnUrl, $permissions);
        return $loginUrl;
    }

    public function getAccessToken(){
        try{
            $helper = $this->facebook->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            $oAuth2Client = $this->facebook->getOAuth2Client();
            $tokenMetadata = $oAuth2Client->debugToken($accessToken);
            $tokenMetadata->validateExpiration();
            if( $accessToken-> isLongLived() ){
                return $accessToken;
            }
            if (! $accessToken->isLongLived()) {
                // Exchanges a short-lived access token for a long-lived one
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                    return $accessToken;
                }catch(FacebookResponseException $e) {
                    dpm($e);
                    return false;
                } catch(FacebookSDKException $e) {
                    dpm($e);
                    return false;
                }    
            }
        }
        catch(FacebookResponseException $e) {
            dpm($e);
            return false;
        } catch(FacebookSDKException $e) {
            dpm($e);
            return false;
        }      
    }

    public function setToken($token){
        $this->token = $token;
    }
    public function pageDetail(){
        try{
            $response = $this->facebook
            ->get(
                '/me/accounts',
                $this->token
            )
            ->getGraphEdge();
            return $response;
        }
        catch(FacebookResponseException $e) {
            dpm($e);
            return false;
        } catch(FacebookSDKException $e) {
            dpm($e);
            return false;
        }     
    }
    public function getToken(){
        return $this->token;
    }
    public function getPageToken($pageId){
        $pagedetail = $this->pageDetail();
        $total_page = sizeof($pagedetail);
        for($i=0;$i<$total_page;$i++){
            if($pagedetail[$i]['id'] == $pageId){
                $accessToken= $pagedetail[$i]['access_token'];
                return $accessToken;
            }
        } 
        return false;
    }
    public function uploadFacebookPost($message,array $url,$pageToken){
       $total_images = sizeof($url);
        $data = [];
        $data['message'] = $message;
        /* $data['og_action_type_id'] = '383634835006146';
        $data['og_icoon_id'] = '436344223195445';
        $data['og_object_id'] = '467368809976558';
        $data['place'] = '106039436102339';
        $data['tags'] = '103737438294175'; */
        for($i=0 ;$i<$total_images;$i++){
            $data["attached_media[{$i}]"] = $this->getMediaId($url[$i],$pageToken) ;
        }
        try {
            $response = $this->facebook->post('/me/feed', $data, $pageToken);
            return true;
        }
        catch(FacebookResponseException $e) {
                return false;
            } catch(FacebookSDKException $e) {
                return false;
        }
    }
    private function getMediaId($url,$pageToken){
        $data = [
            'source' => $this->facebook->fileToUpload($url),
            'published' => false
           ];
           try {
             // Returns a `Facebook\FacebookResponse` object
             $response = $this->facebook->post('/me/photos', $data, $pageToken);
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
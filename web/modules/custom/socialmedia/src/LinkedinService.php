<?php

namespace Drupal\socialmedia;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;


class LinkedinService{
    protected $client;
    protected $request;
    protected $token;
    protected $configFactory;
    public function __construct(RequestStack $request,ConfigFactoryInterface $configFactory){
        $this->configFactory = $configFactory;
        $this->request = $request;
        $this->client =  new Client(['base_uri' => 'https://www.linkedin.com']);
    }
    public function getRegistrationLink(){
        $config = $this->configFactory->get('socialmedia.api_detail');
        $client_id = $config->get('linkedin_client_id');
        $redirect_url = $config->get('linkedin_return_url');
        $scope = 'r_emailaddress,r_liteprofile,w_member_social';
        $url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=".$client_id."&redirect_uri=".$redirect_url."&scope=".$scope;
        return $url;
    }
    public function getAccessToken(){
        $code = $this->request->getCurrentRequest()->query->get('code');
        $config = $this->configFactory->get('socialmedia.api_detail');
        $client_id = $config->get('linkedin_client_id');
        $client_secret = $config->get('linkedin_client_secret');
        $redirect_url = $config->get('linkedin_return_url');
        try{
            $response = $this->client->request('POST', '/oauth/v2/accessToken', [
                'form_params' => [
                        "grant_type" => "authorization_code",
                        "code" =>$code,
                        "redirect_uri" => $redirect_url,
                        "client_id" => $client_id,
                        "client_secret" => $client_secret,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $access_token = $data['access_token']; // store this token somewhere
            return $access_token;
        }
        catch(Exception $e){
            dpm($e);
            return false;
        }
        
    }

    public function setToken($token){
        $this->token = $token;
    }
    public function uploadLinkedinPost($message,array $url){
        $id = $this->getLinkedinId();
        $assetUrl = [];
        $total_image = sizeof($url);
        for($i=0;$i<$total_image;$i++){
            $registerUpload = $this->registerUpload($id);
            $asset = $registerUpload['asset'];
            $uploadUrl = $registerUpload['uploadUrl'];
            array_push($assetUrl,$asset);
            $isImageUploaded = $this->uploadImage($uploadUrl,$url[$i]);
        }
        $isRichMediaShared = $this->shareRichMedia($id,$message,$assetUrl);
        return $isRichMediaShared;
    }

    public function getLinkedinId(){
         try{
            $this->client =  new Client(['base_uri' => 'https://api.linkedin.com']);
            $response = $this->client->request('GET', '/v2/me', [
                'headers' => [
                    "Authorization" => "Bearer " . $this->token,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $id = $data['id']; // store this token somewhere
            //dpm($id);
            return $id;
        }
        catch(Exception $e){
            dpm($e);
            return false;
        }
    }

    public function registerUpload($id){
        $body = new \stdClass();
        $body->registerUploadRequest = new \stdClass();
        $body->registerUploadRequest->owner =  'urn:li:person:'.$id;
        $body->registerUploadRequest->recipes[0] = 'urn:li:digitalmediaRecipe:feedshare-image';
        $body->registerUploadRequest->serviceRelationships[0] = new \stdClass();
        $body->registerUploadRequest->serviceRelationships[0]->identifier = 'urn:li:userGeneratedContent';
        $body->registerUploadRequest->serviceRelationships[0]->relationshipType = 'OWNER';
        $body_json = json_encode($body,true);
        try{
            $this->client =  new Client(['base_uri' => 'https://api.linkedin.com']);
            $response = $this->client->request('POST', '/v2/assets?action=registerUpload', [
                'headers' => [
                    "Authorization" => "Bearer " . $this->token,
                    "Content-Type"  => "application/json",
                    "x-li-format"   => "json"
                ],
                'body' => $body_json,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            $asset = $data['value']['asset'];
            $uploadUrl = $data['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
            $registerUpload['uploadUrl'] = $uploadUrl;
            $registerUpload['asset'] = $asset;
            return $registerUpload;
        }
        catch(Exception $e){
            dpm($e);
            return false;
        }

    }

    public function uploadImage($uploadUrl,$imageUrl){
        try{
            $this->client =  new Client();
            $response=$this->client->request('PUT',$uploadUrl, [
                'headers' => [
                "Authorization" => "Bearer " . $this->token,
                "X-Restli-Protocol-Version"=> "2.0.0",
                "Content-Type"=> "image/jpg"
                ],
                'body' => fopen($imageUrl, 'r'),
            ]);
            return true;
            //dpm($response);
        }
        catch(Exception $e){
            dpm($e);
            return false;
        }
    }

    public function shareRichMedia($id,$message,$asset){
        $total_image = sizeof($asset);
        
        $body = new \stdClass();
        $body->owner =  'urn:li:person:'.$id;
        $body->content = new \stdClass();
        for($i=0;$i<$total_image;$i++){
            $body->content->contentEntities[$i] = new \stdClass();
            $body->content->contentEntities[$i]->entity = $asset[$i];
        }
        $body->content->shareMediaCategory = 'IMAGE';
        $body->distribution = new \stdClass();
        $body->distribution->linkedInDistributionTarget = new \stdClass();
        $body->text = new \stdClass();
        $body->text->text = $message;
        $body_json = json_encode($body ,true);
        try{
            $this->client = new Client(['base_uri' => 'https://api.linkedin.com']);
            $response = $this->client->request('POST', '/v2/shares', [
                'headers' => [
                    "Authorization" => "Bearer " . $this->token,
                    "Content-Type"  => "application/json",
                    "x-li-format"   => "json"
                ],
                'body' => $body_json,
            ]);
           
            $data = json_decode($response->getBody()->getContents(), true);
            return true;
        }
        catch(Exception $e){
            dpm($e);
            return false;
        }
    }


}
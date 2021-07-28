<?php
namespace Drupal\fbshare\Controller;

use Drupal\fbshare\FbShareFacebookConnect;
use Drupal\Core\Controller\ControllerBase;
use Facebook\Facebook;

class FbConnectController extends ControllerBase{
    protected $facebook; 
    public function returnFromFb(){
        $this->facebook = new Facebook([
            'app_id' => '200198691771297',
            'app_secret' => '1ced23b218e39c0f6d5ddc87ab2a6f11',
            'default_graph_version' => 'v9.0',
            'fileUpload' => true,
            'persistent_data_handler'=>'session'
           ]);
        $helper = $this->facebook->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();
        dpm($accessToken);
        return [
            '#markup' => $this->t('Hello World')
          ];
    }
}
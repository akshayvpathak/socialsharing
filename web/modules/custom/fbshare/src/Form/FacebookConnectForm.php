<?php
namespace Drupal\fbshare\Form;

use Drupal\Core\Form\FormBase;
use Facebook\Facebook;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fbshare\FbShareFacebookConnect;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Our simple form class.
 */
class FacebookConnectForm extends FormBase {
    protected $facebook;
    protected $helper;
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
        $this->helper = $this->facebook->getRedirectLoginHelper();
    }
    /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facebook_connect_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Link Facebook'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $permissions = ['email','pages_show_list','publish_to_groups','pages_read_engagement','pages_read_user_content','pages_manage_posts','pages_manage_engagement','public_profile']; // Optional permissions
    $loginUrl = $this->helper->getLoginUrl('http://localhost/socialapp/web/fb-connect/return', $permissions);
    $response = new RedirectResponse($loginUrl);
    $response->send();
    return;
  }

}
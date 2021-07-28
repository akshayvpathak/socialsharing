<?php
namespace Drupal\fbshare\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fbshare\FbShareFacebookConnect;
/**
 * Our simple form class.
 */
class FacebookContentForm extends FormBase {
  protected $facebook; 
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facebook_content_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['caption'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Caption'),
    ];
     $form['my_file'] = array(
      '#type' => 'managed_file',
      '#name' => 'my_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#multiple' => TRUE,
      '#description' => t('Images Only'),
      '#upload_location' => 'public://my_files/',
    ); 
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('POST'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->facebook = new FbShareFacebookConnect;
    $token = 'EAAC2FGPoU6EBAEKR0p0t46qJsODa0O41cIMHAxCkT4zPelz4klBCkHznai5jB3MZAyS4oZBLMVDKBMJUgECyOo8YvUrPB39CRZBhYtPzgxt9xtXJl6CBtFMXZAKSQmS4eJCxAAyhmIIoNooeNnlLVZCpfwAZC8neT857RLP3v67sVE7DEZC0jLx';
    $this->facebook->setToken($token);
    /**
     * Get User Detail
     * if user detail is not available then return
     */
    $basicdetail = $this->facebook->basicDetail();
    //dpm($basicdetail['name']);
    if(!$basicdetail)
    return;
    /**
     * get users page detail
     * if not available then return
     */
    $this->facebook->setUserId($basicdetail['id']);
    $pagedetail = $this->facebook->pageDeatail();
    if(!$pagedetail)
    return;
    //dpm($pagedetail[0]['access_token']);
    /**
     * set user pagetoken,name and id
     */
    $this->facebook->setPageToken($pagedetail[0]['access_token'])  ;
    $this->facebook->setPageName($pagedetail[0]['name']);
    $this->facebook->setPageId($pagedetail[0]['id']);
    $url =[];
    $totle_file = sizeof($form_state->getValue('my_file'));
    $message = $form_state->getValue('caption');
    /**
     * if images is more than one use uploadMultiplePhoto else
     * use UploadPhoto
     */
    if($totle_file>1){
      for($i=0;$i<$totle_file;$i++){
        $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('my_file')[$i]); // Just FYI. The file id will be stored as an array
        $uri = $file->getFileUri();
        $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
        $file_path = $stream_wrapper_manager->realpath();
        array_push($url,$file_path);
      }
      $this->facebook->uploadMultiplePhoto($message,$url);
    }
    else{
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('my_file')[0]); // Just FYI. The file id will be stored as an array
      $uri = $file->getFileUri();
      $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
      $file_path = $stream_wrapper_manager->realpath();
      $this->facebook->uploadPhoto($message,$file_path);
    }
  }

}
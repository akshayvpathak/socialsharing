<?php
namespace Drupal\socialmedia\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\socialmedia\SocialMediaChannelInfo;
use Drupal\socialmedia\SocialMediaPostEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Batch\BatchBuilder;



/**
 * Implementing a ajax form.
 */
class SocialMediaPostForm extends FormBase {
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
    */
    protected $eventDispatcher;


    /**
     * @var \Drupal\socialmedia\SocialMediaChannelInfo
    */
    protected $channel;

    public function __construct(SocialMediaChannelInfo $channel,EventDispatcherInterface $eventDispatcher){
      $this->channel = $channel;
      $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      // Instantiates this form class.
      return new static(
        // Load the service required to construct this class.
        $container->get('socialmedia.channel_info'),
        $container->get('event_dispatcher')
      );
    }


    /**
     * {@inheritdoc}
    */


    public function getFormId() {
      return 'social_media_post_form';
    }
  
    /**
     * {@inheritdoc}
    */


    public function buildForm(array $form, FormStateInterface $form_state) {
    $channel = $this->channel->availableChannel();
      if($channel['facebook'] != NULL){
        $chaneloption['facebook'] = $this->t('Facebook');
      }
      if($channel['twitter'] != NULL){
        $chaneloption['twitter'] = $this->t('Twitter');
      }
      if($channel['linkedin'] != NULL){
        $chaneloption['linkedin'] = $this->t('Linkedin');
      }
      $form['platform'] = array(
        '#type' => 'checkboxes',
        '#options' => $chaneloption,
        '#title' => $this->t('Platform'),
      );
     /*  $form['platform'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Platform'),
        '#options' => $chaneloption,
        '#attributes' => [
          'class' => 'platform',
        ],
       ); */
      if($channel['facebook'] != NULL){
        $option = $this->channel->facebookPageDetail();
         $form['page'] = array(
          '#type' => 'select',
          '#title' => t('Select FacebookPage'),
          '#multiple' => FALSE,
          '#options' => $option,
          '#required' => FALSE,
          '#states' => [
            //show this textfield only if the radio 'other' is selected above
            'visible' => [
              ':input[name="platform"]' => ['value' => 'facebook'],
            ],
          ],
        ); 
      }
      $form['caption'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Caption'),
        '#attributes' => [
          'data-emoji-picker' => "true",
        ],
      ];
       $form['my_file'] = array(
        '#type' => 'managed_file',
        '#name' => 'my_file',
        '#title' => t('File *'),
        '#size' => 20,
        '#multiple' => TRUE,
        '#description' => t('Images Only'),
        '#upload_location' => 'public://post_images/',
      );
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('post'),
      ];
      $form['#attached']['library'][] = 'socialmedia/emoji';
      $form['#attached']['library'][] = 'socialmedia/custom';

      return $form;
    }


    /**
     * Submitting the form.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
      $platform = $form_state->getValue('platform');
      $url =[];
      $totle_file = sizeof($form_state->getValue('my_file'));
      $message = $form_state->getValue('caption');
      for($i=0;$i<$totle_file;$i++){
        $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('my_file')[$i]); // Just FYI. The file id will be stored as an array
        $uri = $file->getFileUri();
        $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
        $file_path = $stream_wrapper_manager->realpath();
        array_push($url,$file_path);
      }
      $event = new SocialMediaPostEvent();
      $event->setMessage($message);
      $event->seturl($url);
      $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Posting In SocialMediaAccount'))
      ->setFinishCallback([$this, 'postingFinished']);
      if($platform['facebook']){
        $pageId = $form_state->getValue('page');
        $pageToken = $this->channel->facebookPageToken($pageId);
        $event->setFacebookToken($pageToken);
        $batch_builder->addOperation([$this, 'postfb'], [$event]);   
      }
      if($platform['twitter']){
        $batch_builder->addOperation([$this, 'posttwitter'], [$event]);
      }
      if($platform['linkedin']){
        $batch_builder->addOperation([$this, 'postlinkedin'], [$event]);
      }
      batch_set($batch_builder->toArray());
    }

    public function postFb($event){
      $event->setPlatform('facebook');
      $event = $this->eventDispatcher->dispatch(SocialMediaPostEvent::EVENT, $event);
    }
    public function posttwitter($event){
      $event->setPlatform('twitter');
      $event = $this->eventDispatcher->dispatch(SocialMediaPostEvent::EVENT, $event);
    }
    public function postlinkedin($event){
      $event->setPlatform('linkedin');
      $event = $this->eventDispatcher->dispatch(SocialMediaPostEvent::EVENT, $event);
    }

    public function postingFinished($success, $results, $operations){
      if($success){
        drupal_set_message('Posted Successfully!');
      }
      else{
        drupal_set_message('Posted Unsuccessfully!');
      }
    }
  
  }
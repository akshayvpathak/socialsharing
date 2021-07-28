<?php

namespace Drupal\socialmedia\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * congiguration form for social media api keys
 */

class SocialMediaConfigurationForm extends ConfigFormBase{
    /**
     *  @var \Drupal\Core\Logger\LoggerChannelInterface
    */
  protected $logger;
    /**
     *  SalutationConfigurationForm constructor.
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory 
     *   The factory for configuration objects.
     * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
     *   The logger.
    */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelInterface $logger) {
    parent::__construct($config_factory);
    $this->logger = $logger;
  }

    /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('socialmedia.logger.channel.social_media')
    );
  }
    /**
     * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
        return ['socialmedia.api_detail'];
    }

    /**
     * {@inheritdoc}
    */
    public function getFormId() {
        return 'social_media_configuration_form';
    }

      /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('socialmedia.api_detail');

        $form['facebook_app_id'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Facebook App ID'),
        '#description' => $this->t('Please provide the facebook app id you want to use.'),
        '#default_value' => $config->get('facebook_app_id'),
        );
        $form['facebook_app_secret'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Facebook App Secret Key'),
            '#description' => $this->t('Please provide the facebook app secret you want to use.'),
            '#default_value' => $config->get('facebook_app_secret'),
        );
        $form['facebook_app_return_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Facebook Return Url'),
            '#description' => $this->t('Please provide the facebook app return url you want to use.'),
            '#default_value' => $config->get('facebook_app_return_url'),
        );
        $form['twitter_consumer_key'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Twitter Consumer Key'),
            '#description' => $this->t('Please provide the twitter consumer key you want to use.'),
            '#default_value' => $config->get('twitter_consumer_key'),
        );
        $form['twitter_consumer_secret'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Twitter Consumer Secret'),
            '#description' => $this->t('Please provide the twitter consumer  secret you want to use.'),
            '#default_value' => $config->get('twitter_consumer_secret'),
        );
        $form['twitter_return_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Twitter Return Url'),
            '#description' => $this->t('Please provide the  twitter return url you want to use.'),
            '#default_value' => $config->get('twitter_return_url'),
        );
        $form['twitter_consumer_key'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Twitter Consumer Key'),
            '#description' => $this->t('Please provide the twitter consumer key you want to use.'),
            '#default_value' => $config->get('twitter_consumer_key'),
        );
        $form['twitter_consumer_secret'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Twitter Consumer Secret'),
            '#description' => $this->t('Please provide the twitter consumer  secret you want to use.'),
            '#default_value' => $config->get('twitter_consumer_secret'),
        );
        $form['twitter_return_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Twitter Return Url'),
            '#description' => $this->t('Please provide the  twitter return url you want to use.'),
            '#default_value' => $config->get('twitter_return_url'),
        );
        $form['linkedin_client_id'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Linkedin Client ID'),
            '#description' => $this->t('Please provide the linkedin client you want to use.'),
            '#default_value' => $config->get('linkedin_client_id'),
        );
        $form['linkedin_client_secret'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Linkedin Client Secret'),
            '#description' => $this->t('Please provide the linkedin client secret you want to use.'),
            '#default_value' => $config->get('linkedin_client_secret'),
        );
        $form['linkedin_return_url'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Linkedin Return Url'),
            '#description' => $this->t('Please provide the linkedin return url you want to use.'),
            '#default_value' => $config->get('linkedin_return_url'),
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->config('socialmedia.api_detail')
        ->set('facebook_app_id', $form_state->getValue('facebook_app_id'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('facebook_app_secret', $form_state->getValue('facebook_app_secret'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('facebook_app_return_url', $form_state->getValue('facebook_app_return_url'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('twitter_consumer_key', $form_state->getValue('twitter_consumer_key'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('twitter_consumer_secret', $form_state->getValue('twitter_consumer_secret'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('twitter_return_url', $form_state->getValue('twitter_return_url'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('linkedin_client_id', $form_state->getValue('linkedin_client_id'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('linkedin_client_secret', $form_state->getValue('linkedin_client_secret'))
        ->save();
        $this->config('socialmedia.api_detail')
        ->set('linkedin_return_url', $form_state->getValue('linkedin_return_url'))
        ->save();
        parent::submitForm($form, $form_state);
        $this->logger->info('Social Media configuration is upadated');
    }

}
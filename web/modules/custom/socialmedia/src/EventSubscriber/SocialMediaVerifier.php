<?php
namespace Drupal\socialmedia\EventSubscriber;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\LocalRedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\socialmedia\SocialMediaDatabaseService;
/**
 * Subscribes to the Kernel Request event and redirects to the homepage
 * when the user has the "non_grata" role.
 */
class SocialMediaVerifier implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;
  /**
   * @var \Drupal\socialmedia\SocialMediaDatabaseService
   */
  protected $database;
  /**
   * SocialMediaVerifier constructor.
   *  @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch,SocialMediaDatabaseService $database) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Handler for the kernel request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function onRequest(GetResponseEvent $event) {
    $route_name = $this->currentRouteMatch->getRouteName();
    if ($route_name !== 'socialmedia.postform') {
      return;
    }
      $id = $this->database->getUserId();
      if(!$id){
        $url = Url::fromUri('internal:/user/login');
        $event->setResponse(new LocalRedirectResponse($url->toString()));
      }
      else if($this->database->isRegisteredBefore($id)){
       /**
        * later we will implement accesstoken expiration time validation
        */
      }
      else{
        $url = Url::fromUri('internal:/socialmedia/channel');
        $event->setResponse(new LocalRedirectResponse($url->toString()));
      }
      
  }

}
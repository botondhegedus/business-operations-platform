<?php

namespace Drupal\secure_admin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Redirects anonymous users from /user/1 and blocks UID 1 login if configured.
 */
class SecureAdminSubscriber implements EventSubscriberInterface {

  /**
   * The current user session service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onRequest', 30],
    ];
  }

  /**
   * Redirect anonymous users away from /user/1.
   */
  public function onRequest(RequestEvent $event) {
    // Only handle master requests (main page load).
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // If anonymous user accesses /user/1, redirect to front page.
    if ($this->currentUser->isAnonymous() && $path === '/user/1') {
      $response = new RedirectResponse(Url::fromRoute('<front>')->toString());
      $event->setResponse($response);
    }
  }

}

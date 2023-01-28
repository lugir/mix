<?php

namespace Drupal\mix\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Mix event subscriber.
 */
class MixSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a MixSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Response event.
   */
  public function onKernelRequest(RequestEvent $event) {
    $isDevMode = \Drupal::config('mix.settings')->get('dev_mode');
    if ($isDevMode) {
      if ($this->currentUser->hasPermission('administer site configuration')) {
        $message = $this->t('Under development mode. <a href=":url">Switch Back.</a>', [':url' => \Drupal::urlGenerator()->generateFromRoute('mix.settings')]);
        \Drupal::messenger()->addStatus($message, FALSE);
      }
      else {
        \Drupal::messenger()->addStatus($this->t('Under development mode.'), FALSE);
      }
      // Prevent page to be cached.
      \Drupal::service('page_cache_kill_switch')->trigger();
    }
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Response event.
   */
  public function onKernelResponse(ResponseEvent $event) {
    // @todo Place code here.
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 30],
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
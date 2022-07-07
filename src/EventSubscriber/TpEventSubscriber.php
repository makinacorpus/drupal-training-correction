<?php

declare(strict_types=1);

namespace Drupal\training_correction\EventSubscriber;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\training_correction\Event\TpEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Correction for TP EventSubscriber.
 */
class TpEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  private $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(Messenger $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[TpEvent::ENTITY_INSERT][] = ['setMessage'];

    return $events;
  }

  /**
   * Display a message.
   */
  public function setMessage(TpEvent $event): void {
    $entity = $event->getEntity();
    $title = $entity->getTitle();
    $this->messenger->addWarning(
      $this->t('@article has toooo be moderated', ['@article' => $title])
    );
  }

}

<?php

declare(strict_types=1);

namespace Drupal\training_correction\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

/**
 * Correction for TP Event.
 */
class TpEvent extends Event {

  const ENTITY_INSERT = 'training_correction_event';

  /**
   * An entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityInterface $entity = NULL) {
    $this->entity = $entity;
  }

  /**
   * Returns the entity attribute of the class.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A Drupal entity.
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

}

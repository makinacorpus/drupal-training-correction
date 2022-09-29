<?php

declare(strict_types=1);

namespace Drupal\training_correction\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Correction for TP Entity.
 */
class TpEntityController extends ControllerBase {

  /**
   * Render a table with differents statistics.
   */
  public function render(): array {

    // Prepare the table render array.
    // @see https://api.drupal.org/api/drupal/core!includes!theme.inc/function/drupal_common_theme
    $rows = [];
    $header = [
      $this->t('Username'),
      $this->t('Email'),
      $this->t('Birthdate'),
    ];

    // Get all user uids.
    // Note that user 0 is the anonymous.
    $userUids = $this->entityTypeManager()
      ->getStorage('user')
      ->getQuery()
      ->condition('field_newsletter', 1)
      ->condition('uid', [0, 1], 'NOT IN')
      ->execute();

    $count = \count($userUids);

    // Load users.
    $users = $this->entityTypeManager()
      ->getStorage('user')
      ->loadMultiple($userUids);

    foreach ($users as $user) {
      // Get the user's information.
      $userName = $user->getAccountName();
      $userMail = $user->getEmail();

      // Get the value of the field using getter.
      if (!$user->get('field_birthdate')->isEmpty()) {
        // Note that we use the first() because of the 0..1 field's cardinality.
        $fieldBirthdate = $user
          ->get('field_birthdate')
          ->first()
          ->getValue();
        $birthdate = $fieldBirthdate['value'];
      }

      // You can also use the magic method.
      // $newsletter = $user->field_newsletter->value;

      // Fill the rows of the table.
      $rows[] = [
        $userName,
        $userMail,
        $birthdate ?? '',
      ];
    }

    // Build the table render array.
    $renderArray = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#caption' => $this->t('@count users', ['@count' => $count]),
    ];

    return $renderArray;
  }

}

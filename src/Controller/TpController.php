<?php

declare(strict_types=1);

namespace Drupal\training_correction\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Correction for TP Controller.
 */
class TpController extends ControllerBase {

  /**
   * Display information on the website.
   */
  public function render(): array {

    // Load the configuration.
    $configSystem = $this->config('system.site');
    // Get the required information.
    $siteName = $configSystem->get('name');
    $siteMail = $configSystem->get('mail');

    // Load the current user.
    $currentUser = $this->currentUser();
    // Get the user account name.
    $userName = $currentUser->getAccountName();

    // Display the status message.
    // Note that the message is available for translation.
    $this->messenger()->addStatus(
      $this->t('You have successfully created your first page.')
    );

    // Build the items list render array.
    // @see https://api.drupal.org/api/drupal/core!includes!theme.inc/function/drupal_common_theme
    $renderArray = [
      '#theme' => 'item_list',
      '#items' => [
        $siteName,
        $userName,
        $siteMail,
      ],
    ];

    return $renderArray;
  }

}

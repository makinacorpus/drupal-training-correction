<?php

declare(strict_types=1);

namespace Drupal\training_correction\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Correction for TP Form.
 */
class TpForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tp_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#required' => TRUE,
    ];

    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#required' => TRUE,
    ];

    $form['topic'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Center of interest'),
      '#required' => TRUE,
      '#target_type' => 'taxonomy_term',
      '#selection_settings' => [
        'target_bundles' => [
          'tag',
        ],
      ],
    ];

    $form['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your postal code'),
      '#maxlength' => 5,
    ];

    $form['locality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your locality'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $postalCode = $form_state->getValue('postal_code');
    if ('' === $postalCode) {
      return;
    }
    if (\strlen($postalCode) < 5) {
      $form_state->setErrorByName(
        'postal_code',
        $this->t('Enter a valid postal code !')
      );
    }
    if (!\preg_match('/^[0-9]*$/', $postalCode)) {
      $form_state->setErrorByName(
        'postal_code',
        $this->t('Only number are allowed !')
      );
    }
    $locality = $form_state->getValue('locality');
    if ($locality == '') {
      $form_state->setErrorByName(
        'locality',
        $this->t('You must provide your locality')
      );
      return;
    }
    if (\preg_match('/\d+/', $locality)) {
      $form_state->setErrorByName(
        'locality',
        $this->t('Only character are allowed !')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('Your subscription have been submitted'));
  }

}

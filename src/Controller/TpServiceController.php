<?php

declare(strict_types=1);

namespace Drupal\training_correction\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\training_correction\Service\TpService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Service.
 */
class TpServiceController extends ControllerBase {

  /**
   * The TpService service.
   *
   * @var \Drupal\training_correction\Service\TpService
   */
  protected $tpService;

  /**
   * The linkGenerator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('training_correction.tp_service'),
      $container->get('link_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    TpService $tp_service,
    LinkGeneratorInterface $link_generator
  ) {
    $this->tpService = $tp_service;
    $this->linkGenerator = $link_generator;
  }

  /**
   * Render a formatted list of pokemons from PockeApi.
   */
  public function render(): array {
    // Get the list form the service.
    $pokemons = $this->tpService->getPokemons(151);

    // Prepare the items list.
    $items = [];
    foreach ($pokemons as $pokemon) {
      $label = $pokemon['name'];
      // Create an Url object.
      $url = Url::fromUri($pokemon['url']);
      $items[] = $this->linkGenerator->generate($label, $url);
    }

    // Build the render array.
    $renderArray = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    // Return items list.
    return $renderArray;
  }

}

<?php

declare(strict_types=1);

namespace Drupal\training_correction\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Cache.
 *
 * @Block(
 *   id = "tp_cache",
 *   admin_label = @Translation("TP Cache"),
 *   category = @Translation("Training correction"),
 * )
 */
class TpCache extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The RouteMatchInterface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The LinkGeneratorInterface.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('link_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_manager,
    LinkGeneratorInterface $link_generator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_manager;
    $this->linkGenerator = $link_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Check the requirements.
    if ('entity.user.canonical' !== $this->routeMatch->getRouteName()) {
      return;
    }
    // Get the user.
    $user = $this->routeMatch->getParameter('user');

    // Search for the last three articles owned by this user.
    $nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'article')
      ->condition('uid', $user->id())
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->execute();

    // Return if there are no matching articles.
    if (empty($nids)) {
      return;
    }

    // Load articles.
    $articles = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);

    // Build an array that you will return in a items list theme.
    $links = [];
    foreach ($articles as $article) {
      // Create a link using the link_generator service.
      $label = $article->getTitle();
      $url = $article->toUrl();
      $links[] = $this->linkGenerator->generate($label, $url);
    }

    // Build the first render array that display the user account name.
    $renderArray['author'] = [
      '#markup' => $this->t(
        'Articles written by @user',
        ['@user' => $user->getAccountName()]
      ),
    ];

    // Build the items list render array.
    $renderArray['article'] = [
      '#theme' => 'item_list',
      '#items' => $links,
    ];

    // Manage cache for the render array.
    $renderArray['cache'] = [
      '#cache' => [
        'tags' => [
          \sprintf('user:%s', $user->id()),
          'node_list:article',
        ],
        'contexts' => [
          'route',
        ],
      ],
    ];

    return $renderArray;
  }

}

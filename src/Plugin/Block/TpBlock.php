<?php

declare(strict_types=1);

namespace Drupal\training_correction\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Correction for TP Block.
 *
 *  @Block(
 *  id = "tp_block",
 *  admin_label = @Translation("TP Block"),
 *  category = @Translation("Training correction"),
 *  context_definitions = {
 *    "node" = @ContextDefinition("entity:node"),
 *  }
 * )
 */
class TpBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Link generator service.
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
      $container->get('entity_type.manager'),
      $container->get('link_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_manager,
    LinkGeneratorInterface $link_generator
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_manager;
    $this->linkGenerator = $link_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Build form.
    $form = parent::blockForm($form, $form_state);
    $form['range'] = [
      '#type' => 'number',
      '#title' => $this->t('Choose the number of articles to display'),
      '#default_value' => $this->configuration['range'] ?? 3,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['range'] = $form_state->getValue('range');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the node from the context and check requirements.
    $node = $this->getContextValue('node');
    if ('article' !== $node->bundle()) {
      return;
    }
    if ($node->get('field_news')->isEmpty()) {
      return;
    }

    // Get the first category of the article.
    $firstTag = $node->get('field_news')->first()->getValue();

    // Get the number of articles to display from the configuration.
    $config = $this->getConfiguration();
    $range = $config['range'] ?? 3;

    // Get the latest related articles to this category.
    // Note that we don't consider the current article.
    $nids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'article')
      ->condition('field_news', $firstTag['target_id'])
      ->condition('nid', $node->id(), '!=')
      ->sort('created', 'DESC')
      ->range(0, $range)
      ->execute();

    // Return if there are no matching articles.
    if (empty($nids)) {
      return;
    }

    // Load articles.
    $articles = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);

    // Manage link.
    $links = [];
    foreach ($articles as $article) {
      // Create a link using the link_generator service.
      $label = $article->getTitle();
      $url = $article->toUrl();
      $links[] = $this->linkGenerator->generate($label, $url);
    }

    // Build the items list render array.
    $renderArray = [
      '#theme' => 'item_list',
      '#items' => $links,
    ];

    return $renderArray;
  }

}

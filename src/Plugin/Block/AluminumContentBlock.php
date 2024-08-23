<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Content' block
 *
 * @Block(
 *   id = "aluminum_content",
 *   admin_label = @Translation("Content"),
 * )
 */
class AluminumContentBlock extends AluminumBlockBase {

  /**
   * EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CurrentPathStack definition.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * Constructs a new AluminumBlockBase object.
   *
   * @param array $configuration
   *   The block plugin configuration.
   * @param string $plugin_id
   *   The block plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path_stack
   *   The current path stack.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, CurrentPathStack $current_path_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentPathStack = $current_path_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Path\CurrentPathStack $current_path_stack */
    $current_path_stack = $container->get('path.current');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory,
      $entity_type_manager,
      $current_path_stack
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    return [
      'entity_type' => [
        '#type' => 'textfield',
        '#title' => $this->t('Entity type'),
        '#description' => $this->t('Enter the machine name of the entity type to render, or leave blank to infer from the current page.'),
        '#default_value' => '',
      ],
      'entity_id' => [
        '#type' => 'textfield',
        '#title' => $this->t('Entity ID'),
        '#description' => $this->t('Enter the entity ID you wish to render, or leave blank to infer from the current page.'),
        '#default_value' => '',
      ],
      'view_mode' => [
        '#type' => 'textfield',
        '#title' => $this->t('View mode'),
        '#description' => $this->t('Enter the machine name of the view mode to render, or leave blank to use the default view mode.'),
        '#default_value' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#cache' => [
        'max-age' => 0,
      ]
    ];

    $content = $this->getEntityView();

    if (!empty($content)) {
      $build['content'] = $content;
    }

    return $build;
  }

  /**
   * Build a view using a view builder for the configured entity and view mode
   *
   * @return array
   *   The entity view.
   */
  protected function getEntityView(): array {
    $entity_view = [];
    $entity = $this->loadEntity();

    if (!is_null($entity)) {
      $view_mode = $this->getOptionValue('view_mode') ?: 'full';

      if ($this->hasContent($entity, $view_mode)) {
        $entity_view = $this->entityTypeManager
          ->getViewBuilder($entity->getEntityTypeId())
          ->view($entity, $view_mode);
      }
    }

    return $entity_view;
  }

  /**
   * If the entity has content.
   *
   * Checks of the entity has content in any of the fields displayed on the
   * provided view mode.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   *
   * @return bool
   *   Whether there is content.
   */
  protected function hasContent(FieldableEntityInterface $entity, string $view_mode): bool {
    $has_content = FALSE;

    foreach ($this->getDisplayFields($entity, $view_mode) as $field_name => $field_settings) {
      if ($entity->hasField($field_name) && !$entity->get($field_name)->isEmpty()) {
        $has_content = TRUE;
        break;
      }
    }

    return $has_content;
  }

  /**
   * Gets the fields that are configured on the provided view mode.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The view mode.
   *
   * @return array
   *   An array of fields.
   */
  protected function getDisplayFields(FieldableEntityInterface $entity, $view_mode) {
    $fields = [];

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    $display = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode);

    if (!is_null($display)) {
      $fields = $display
        ->removeComponent('title')
        ->removeComponent('uid')
        ->removeComponent('created')
        ->getComponents();
    }

    return $fields;
  }

  /**
   * Load the entity configured by the block
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface|null
   *   The loaded entity, or NULL
   */
  protected function loadEntity(): EntityInterface|FieldableEntityInterface|null {
    $entity = NULL;

    $entityType = $this->getOptionValue('entity_type');
    $entityId = $this->getOptionValue('entity_id');

    if (empty($entityType) || empty($entityId)) {
      $entity = $this->loadCurrentEntity($entityType);
    } else {
      $entityId = $this->getOptionValue('entity_id');

      if (!empty($entityId)) {
        /** @var FieldableEntityInterface $entity */
        $entity = $this->entityTypeManager->getStorage($entityType)->load($entityId);
      }
    }

    return $entity;
  }

  /**
   * Loads the current entity from the current request URI, and returns it if available.
   *
   * @param null $entityType
   *   The entity type to load, or empty to try and determine the current entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity object from the current request URI, or NULL
   */
  protected function loadCurrentEntity($entityType = NULL) {
    $path = $this->currentPathStack->getPath();

    $url = Url::fromUri('internal:' . $path);

    $params = $url->getRouteParameters();

    $entity = null;

    if (!empty($params)) {
      if (empty($entityType)) {
        if (isset($params['entity_type'])) {
          $entityType = $params['entity_type'];
        }
        else {
          $entityType = key($params);
        }

      }

      if (!empty($entityType)) {
        $param = $params['entity'] ?? $params[$entityType];
        $entity = $this->entityTypeManager->getStorage($entityType)->load($param);
      }
    }

    return $entity;
  }

}

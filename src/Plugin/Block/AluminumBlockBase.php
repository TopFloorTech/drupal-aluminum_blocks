<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an abstract base class for Aluminum Blocks.
 */
abstract class AluminumBlockBase extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Optionally override this to manually set an aluminum_id for this block.
   *
   * @var string
   */
  protected $aluminum_id = '';

  /**
   * ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $config_factory
    );
  }

  /**
   * Override to specify configuration options
   *
   * @return array
   *   An array of options.
   */
  public function getOptions(): array {
    return [];
  }

  public function getAluminumId() {
    if (!empty($this->aluminum_id)) {
      return $this->aluminum_id;
    }

    return strtolower(preg_replace([
      '/([a-z\d])([A-Z])/',
      '/([^_])([A-Z][a-z])/'
    ], '$1_$2', self::class));
  }

  /**
   * Gets the current value for an option returned by getOptions()
   *
   * @param $option_name
   * @param bool $replace_tokens
   * @return string
   */
  public function getOptionValue($option_name, $replace_tokens = FALSE) {
    $config = $this->getConfiguration();

    $options = $this->getOptions();

    $default = $options[$option_name]['#default_value'] ?? '';

    if (isset($config[$option_name]['#type']) && $config[$option_name]['#type'] === 'formatted_text') {
      $default = [
        'format' => $config[$option_name]['#format'],
        'value' => $default,
      ];
    }

    $value = $config[$option_name] ?? $default;

    if ($replace_tokens) {
      $value = \Drupal::token()->replace($value);
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    foreach ($this->getOptions() as $option_name => $option) {
      $option += [
        '#type' => 'textfield',
        '#title' => $this->t(ucfirst(str_replace('_', ' ', $option_name))),
      ];

      $default = $option['#default_value'] ?? '';

      $option['#default_value'] = $config[$option_name] ?? $default;

      $form[$option_name] = $option;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    foreach ($this->getOptions() as $option_name => $option) {
      $this->setConfigurationValue($option_name, $form_state->getValue($option_name));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = $this->configFactory->get('aluminum_blocks.settings');

    $values = [];

    foreach ($this->getOptions() as $option_name => $option) {
      $values[$option_name] = $default_config->get($this->getAluminumId() . '.' . $option_name);
    }

    return $values;
  }

}

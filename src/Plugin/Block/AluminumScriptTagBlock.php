<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

/**
 * Provides a 'Script tag' block
 *
 * @Block(
 *     id = "aluminum_script_tag",
 *     admin_label = @Translation("Script tag"),
 * )
 */
class AluminumScriptTagBlock extends AluminumBlockBase {

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper classes'),
      '#description' => $this->t('The CSS classes to give to the element wrapping the script tag.'),
      '#default_value' => '',
    ];

    $options['wrapper_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper ID'),
      '#description' => $this->t('The CSS ID to give to the element wrapping the script tag.'),
      '#default_value' => '',
    ];

    $options['script'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Script'),
      '#description' => $this->t('Enter the full (or relative) URL to the script to embed.'),
      '#default_value' => '',
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $classes = $this->getOptionValue('wrapper_classes');

    $classes = trim('AluminumScriptTag ' . $classes);

    return [
      '#theme' => 'aluminum_script_tag',
      '#wrapper_classes' => $classes,
      '#wrapper_id' => $this->getOptionValue('wrapper_id'),
      '#script' => $this->getOptionValue('script'),
    ];
  }

}

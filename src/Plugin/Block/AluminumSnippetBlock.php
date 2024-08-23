<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\Core\Render\Markup;

/**
 * Provides a 'Snippet' block
 *
 * @Block(
 *     id = "aluminum_snippet",
 *     admin_label = @Translation("Snippet"),
 * )
 */
class AluminumSnippetBlock extends AluminumBlockBase {
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

    $options['snippet'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Snippet'),
      '#description' => $this->t('Paste the full snippet here.'),
      '#default_value' => '',
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $classes = $this->getOptionValue('wrapper_classes');

    $classes = trim('AluminumSnippet ' . $classes);

    return [
      '#theme' => 'aluminum_snippet',
      '#wrapper_classes' => $classes,
      '#wrapper_id' => $this->getOptionValue('wrapper_id'),
      '#snippet' => Markup::create($this->getOptionValue('snippet')),
    ];
  }
}

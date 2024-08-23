<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

/**
 * Provides an 'Icon' block
 *
 * @Block(
 *     id = "aluminum_icon",
 *     admin_label = @Translation("Icon"),
 * )
 */
class AluminumIconBlock extends AluminumBlockBase {

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon'),
      '#description' => $this->t('Enter the name of the icon to output.'),
      '#default_value' => 'bars',
    ];

    $options['output_as_link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Output as link'),
      '#description' => $this->t('Wrap the icon with the specified link.'),
      '#default_value' => FALSE,
    ];

    $options['link_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link URL'),
      '#description' => $this->t('If outputting as a link, enter the destination URL.'),
      '#default_value' => '',
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $icon = $this->getOptionValue('icon');

    if (empty($icon)) {
      return [];
    }

    return [
      '#theme' => 'aluminum_icon',
      '#icon' => $icon,
      '#output_as_link' => $this->getOptionValue('output_as_link'),
      '#link_url' => $this->getOptionValue('link_url'),
    ];
  }

}

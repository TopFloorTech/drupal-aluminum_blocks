<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

/**
 * Provides a 'Dropdown links' block
 *
 * @Block(
 *     id = "aluminum_dropdown_links",
 *     admin_label = @Translation("Dropdown links"),
 * )
 */
class AluminumDropdownLinksBlock extends AluminumBlockBase {
  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    return [
      'button_text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button text'),
        '#description' => $this->t('Enter the text to display on the dropdown button itself.')
      ],
      'link_list' => [
        '#type' => 'textarea',
        '#title' => $this->t('Link list'),
        '#description' => $this->t('Enter your links, one per line, in the format "href|Title|icon".'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'aluminum_dropdown_links',
      '#button_text' => $this->getOptionValue('button_text'),
      '#link_list' => $this->getList(),
    ];
  }

  /**
   * Get list of dropdown links.
   *
   * @return array
   *   An array of dropdown links.
   */
  protected function getList(): array {
    $list = [];

    $links = $this->getOptionValue('link_list');

    $links = explode("\n", $links);

    foreach ($links as $linkItem) {
      $parts = explode("|", $linkItem);
      $href = array_shift($parts);

      if (!empty($href)) {
        $list[] = [
          'href' => $href,
          'title' => !empty($parts) ? array_shift($parts) : $href,
          'icon' => !empty($parts) ? array_shift($parts) : '',
        ];
      }
    }

    return $list;
  }

}

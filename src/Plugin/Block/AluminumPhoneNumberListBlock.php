<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\aluminum_storage\Aluminum\Config\ConfigManager;

/**
 * Provides a 'Phone number list' block
 *
 * @Block(
 *     id = "aluminum_phone_number_list",
 *     admin_label = @Translation("Phone number list"),
 * )
 */
class AluminumPhoneNumberListBlock extends AluminumBlockBase {

  /**
   * Gets phone number options.
   *
   * @return array
   *   An array of phone number options.
   */
  protected function getPhoneNumberOptions(): array {
    $options = [];

    foreach (aluminum_storage_phone_numbers() as $phone_number_type => $info) {
      $options[$phone_number_type] = $info['title'];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['enabled_phone_numbers'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled phone numbers'),
      '#description' => $this->t('Check which phone numbers to show in the list'),
      '#options' => $this->getPhoneNumberOptions(),
      '#default_value' => array_keys($this->getPhoneNumberOptions()),
    ];

    $options['display_short_titles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display short titles'),
      '#description' => $this->t('Check to use short abbreviations in the list instead of long titles.'),
      '#default_value' => FALSE,
    ];

    return $options;
  }

  /**
   * Get list of phone numbers.
   *
   * @return array
   *   A list.
   *
   * @throws \Drupal\aluminum_storage\Aluminum\Exception\ConfigException
   */
  protected function getList(): array {
    $enabled = array_keys(array_filter($this->getOptionValue('enabled_phone_numbers')));
    $phone_numbers = aluminum_storage_phone_numbers();
    $display_short_titles = $this->getOptionValue('display_short_titles');

    $config = ConfigManager::getConfig('content');

    $list = [];

    foreach ($enabled as $type) {
      $key = $display_short_titles ? 'short_title' : 'title';
      $title = $phone_numbers[$type][$key];

      $phone_number = $config->getValue($type, 'contact_info');

      if (!empty($phone_number)) {
        $list[] = [
          'title' => $title,
          'phone_number' => $phone_number,
          'url' => 'tel:+1 ' . $phone_number,
        ];
      }
    }

    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'aluminum_phone_number_list',
      '#list' => $this->getList(),
    ];
  }

}

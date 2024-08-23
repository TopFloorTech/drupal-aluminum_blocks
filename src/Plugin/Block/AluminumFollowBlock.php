<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\aluminum_storage\Aluminum\Config\ConfigManager;

/**
 * Provides a 'Follow links' block.
 *
 * @Block(
 *     id = "aluminum_follow",
 *     admin_label = @Translation("Follow links"),
 * )
 */
class AluminumFollowBlock extends AluminumBlockBase {

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['link_target'] = [
      '#type' => 'select',
      '#title' => $this->t('Link target'),
      '#description' => $this->t('Select the browser target for the follow links.'),
      '#options' => [
        '_blank' => 'New window',
        '_self' => 'Same window',
        '_parent' => 'Parent frame',
        '_top' => 'Top frame',
      ],
      '#default_value' => '_blank',
    ];

    $weight = 10;

    foreach (aluminum_storage_social_networks() as $id => $info) {
      $name = $info['name'];

      $options[$id . '_enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t($name . ' enabled'),
        '#description' => $this->t($name . ' will be shown if this box is checked.'),
        '#default_value' => TRUE,
        '#weight' => $weight,
      ];

      $options[$id . '_url_override'] = [
        '#type' => 'checkbox',
        '#title' => $this->t($name . ' url override'),
        '#description' => $this->t('If this is checked, the provided URL will be used.'),
        '#default_value' => FALSE,
        '#weight' => $weight,
        '#states' => [
          'visible' => [
            ':input[name="settings[' . $id . '_enabled]"]' => ['checked' => TRUE],
          ]
        ]
      ];

      $options[$id . '_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t($name . ' url'),
        '#description' => $this->t('The URL for ' . $name . '.' ),
        '#default_value' => '',
        '#weight' => $weight,
        '#states' => [
          'visible' => [
            ':input[name="settings[' . $id . '_enabled]"]' => ['checked' => TRUE],
            ':input[name="settings[' . $id . '_url_override]"]' => ['checked' => TRUE],
          ]
        ]
      ];

      $options[$id . '_weight'] = [
        '#type' => 'textfield',
        '#title' => $this->t($name . ' weight'),
        '#description' => $this->t('This integer defines the weight of ' . $name . ' in relation to other links.'),
        '#default_value' => $weight,
        '#weight' => $weight,
        '#states' => [
          'visible' => [
            ':input[name="settings[' . $id . '_enabled]"]' => ['checked' => TRUE],
          ]
        ]
      ];

      $weight += 10;
    }

    return $options;
  }

  /**
   * Get Icon class.
   *
   * @param $id
   *   The id of icon.
   *
   * @return mixed|null
   *   Icon class if defined.
   *
   * @throws \Drupal\aluminum_storage\Aluminum\Exception\ConfigException
   */
  protected function iconClass($id): mixed {
    $config = ConfigManager::getConfig('appearance');

    $class = $config->getValue($id . '_icon_class', 'icons');

    if (empty($class)) {
      return '';
    }

    $baseIconClass = $config->getValue('base_icon_class', 'icons');

    if (!empty($baseIconClass)) {
      $class = "$baseIconClass $class";
    }

    return $class;
  }

  /**
   * Get social networks.
   *
   * @return array
   *   An array of social networks.
   *
   * @throws \Drupal\aluminum_storage\Aluminum\Exception\ConfigException
   */
  protected function getSocialNetworks(): array {
    $socialNetworks = aluminum_storage_social_networks();
    $networks = [];

    foreach ($socialNetworks as $id => $name) {
      if ($this->getOptionValue($id . '_enabled')) {
        $url = $this->getUrl($id);

        if (!empty($url)) {
          $networks[$id] = [
            'name' => $name,
            'weight' => $this->getOptionValue($id . '_weight'),
            'url' => $url,
            'icon_class' => $this->iconClass($id)
          ];
        }
      }
    }

    usort($networks, function ($a, $b) {
      if ($a['weight'] === $b['weight']) {
        return 0;
      }

      return ($a['weight'] < $b['weight']) ? -1 : 1;
    });

    return $networks;
  }

  /**
   * Get URL.
   *
   * @param $id
   *   The id.
   *
   * @return mixed|null
   *   The url if found.
   * @throws \Drupal\aluminum_storage\Aluminum\Exception\ConfigException
   */
  private function getUrl($id): mixed {
    $config = ConfigManager::getConfig('content');

    if ($this->getOptionValue($id . '_url_override')) {
      $url = $this->getOptionValue($id . '_url');
    }
    else {
      $url = $config->getValue($id . '_page_url', 'social', '');
    }

    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'aluminum_follow_list',
      '#list' => $this->getSocialNetworks(),
      '#link_target' => $this->getOptionValue('link_target', '_blank'),
    ];
  }

}

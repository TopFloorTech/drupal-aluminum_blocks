<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

use Drupal\Core\Cache\Cache;

/**
 * Provides an 'AddThis' block
 *
 * @Block(
 *     id = "aluminum_addthis",
 *     admin_label = @Translation("AddThis share toolbox"),
 * )
 */
class AluminumAddThisBlock extends AluminumBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#markup' => '<div class="AddThis"><div class="addthis_inline_share_toolbox"></div></div>',
      '#attached' => ['library' => ['aluminum_blocks/aluminum_addthis']],
    ];
    // Disable caching for this block
    $build['#cache']['max-age'] = 0;

    return $build;
  }

  /**
   * Get cache contexts.
   *
   * @return array|string[]
   *   An array of cache contexts.
   */
  public function getCacheContexts(): array {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

}

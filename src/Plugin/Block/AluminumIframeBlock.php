<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

/**
 * Provides an 'Iframe' block
 *
 * @Block(
 *     id = "aluminum_iframe",
 *     admin_label = @Translation("Iframe"),
 * )
 */
class AluminumIframeBlock extends AluminumBlockBase {

  /**
   * {@inheritdoc}
   */
  public function getOptions(): array {
    $options = [];

    $options['src'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Source'),
      '#description' => $this->t('Enter the URL (or Drupal path) to use as the iframe\'s "src" attribute.'),
      '#default_value' => '',
    ];

    $options['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Enter the value to use for the iframe\'s "width" attribute, as a number of pixels.'),
      '#min' => 0,
      '#size' => 10,
      '#default_value' => 480,
    ];

    $options['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Enter the value to use for the iframe\'s "height" attribute, as a number of pixels.'),
      '#min' => 0,
      '#size' => 10,
      '#default_value' => 320,
    ];

    $options['remove_border'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove frame border'),
      '#description' => $this->t('Check this box to show the frame without any border.'),
      '#default_value' => TRUE,
    ];

    $options['responsive_width'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make frame width responsive'),
      '#description' => $this->t('Check this box to make the frame width stretch to fill its parent container.'),
      '#default_value' => TRUE,
    ];

    $options['max_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max width'),
      '#description' => $this->t('If using a responsive width, this CSS value can be specified as the maximum.'),
      '#min' => 0,
      '#size' => 10,
      '#default_value' => 0,
    ];

    $options['responsive_height'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make frame height responsive'),
      '#description' => $this->t('Check this box to make the frame width stretch to fill its parent container.'),
      '#default_value' => FALSE,
    ];

    $options['max_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max height'),
      '#description' => $this->t('If using a responsive height, this CSS value can be specified as the maximum.'),
      '#min' => 0,
      '#size' => 10,
      '#default_value' => 0,
    ];

    $options['fit_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Fit content'),
      '#description' => $this->t('Check this box to automatically adjust the iframe\'s height to fit its content.'),
      '#default_value' => FALSE,
    ];

    $options['center'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Center iframe'),
      '#description' => $this->t('Center the iframe within the block.'),
      '#default_value' => TRUE,
    ];

    $options['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('A name for the embedded browsing context (or frame). This can be used as the value of the target attribute of an a or form element, or the formtarget attribute of an input or button element.'),
      '#default_value' => '',
    ];

    return $options;
  }

  /**
   * Builds wrapper CSS classes.
   *
   * @return string
   *   Wrapper CSS classes.
   */
  protected function buildWrapperCss(): string {
    $center = $this->getOptionValue('center');

    $output = '';

    if ($center) {
      $output .= 'margin-left: auto; margin-right: auto;';
    }

    return $output;
  }

  /**
   * Build CSS.
   *
   * @return string
   *   A string of CSS.
   */
  protected function buildCss(): string {
    $css = [];

    $maxWidth = $this->getOptionValue('max_width');
    $maxHeight = $this->getOptionValue('max_height');

    if ($maxWidth > 0) {
      $css['max-width'] = $maxWidth;
    }

    if ($maxHeight > 0) {
      $css['max-height'] = $maxHeight;
    }

    $output = '';

    foreach ($css as $name => $val) {
      $output .= "$name: $val;";
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'aluminum_iframe',
      '#src' => $this->getOptionValue('src'),
      '#width' => $this->getOptionValue('width'),
      '#height' => $this->getOptionValue('height'),
      '#remove_border' => $this->getOptionValue('remove_border'),
      '#responsive_width' => $this->getOptionValue('responsive_width'),
      '#responsive_height' => $this->getOptionValue('responsive_height'),
      '#name' => $this->getOptionValue('name'),
      '#fit_content' => $this->getOptionValue('fit_content'),
      '#attached' => ['library' => ['aluminum_blocks/aluminum_iframe']],
      '#css' => $this->buildCss(),
      '#wrapper_css' => $this->buildWrapperCss(),
    ];
  }

}

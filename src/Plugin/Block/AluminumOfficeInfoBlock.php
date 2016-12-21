<?php
/**
 * Created by PhpStorm.
 * User: BMcClure
 * Date: 9/9/2016
 * Time: 2:13 PM
 */

namespace Drupal\aluminum_blocks\Plugin\Block;
use Drupal\aluminum_storage\Aluminum\Config\ConfigManager;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an 'Office information' block
 *
 * @Block(
 *     id = "aluminum_office_info",
 *     admin_label = @Translation("Office information"),
 * )
 */
class AluminumOfficeInfoBlock extends AluminumPhoneNumberListBlock {
  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = parent::getOptions();

    $options['office_address'] = [
      '#type' => 'text_format',
      '#format' => 'basic_html',
      '#title' => $this->t('Office address'),
      '#description' => $this->t('Enter the address of the office to display.'),
      '#default_value' => '<p></p>',
    ];

    $options['office_hours'] = [
      '#type' => 'text_format',
      '#format' => 'basic_html',
      '#title' => $this->t('Office hours'),
      '#description' => $this->t('Enter the hours to display for this office.'),
      '#default_value' => '<p></p>',
    ];

    return $options;
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    foreach (['office_address', 'office_hours'] as $field) {
      $data = $form[$field]['#default_value'];
      $form[$field]['#default_value'] = $data['value'];
      $form[$field]['#format'] = $data['format'];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $officeAddress = $this->getOptionValue('office_address');
    $officeHours = $this->getOptionValue('office_hours');

    return [
      '#theme' => 'aluminum_office_info',
      '#address' => [
        '#type' => 'processed_text',
        '#text' => $officeAddress['value'],
        '#format' => $officeAddress['format'],
      ],
      '#phone_list' => [
        '#theme' => 'aluminum_phone_number_list',
        '#list' => $this->getList(),
      ],
      '#office_hours' => [
        '#type' => 'processed_text',
        '#text' => $officeHours['value'],
        '#format' => $officeHours['format'],
      ],
    ];
  }
}

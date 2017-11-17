<?php

namespace Drupal\aluminum_blocks\Plugin\Block;

/**
 * Provides a 'Copyright information' block
 *
 * @Block(
 *     id = "aluminum_copyright",
 *     admin_label = @Translation("Copyright information"),
 * )
 */
class AluminumCopyrightBlock extends AluminumBlockBase {
    /**
     * {@inheritdoc}
     */
    public function getOptions() {
        return [
            'copyright_text' => [
                '#type' => 'textfield',
                '#title' => $this->t('Copyright text'),
                '#description' => $this->t('The text to display. You may use tokens such as [site:name] and [date:custom:Y].'),
                '#default_value' => 'Copyright [date:custom:Y] [site:name]. All rights reserved.',
                '#maxlength' => 256,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
        return [
            '#markup' => sprintf('<p>%s</p>', $this->t($this->getOptionValue('copyright_text', TRUE))),
        ];
    }
}

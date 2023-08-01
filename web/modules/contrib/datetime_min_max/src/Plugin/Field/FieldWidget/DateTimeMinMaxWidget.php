<?php

namespace Drupal\datetime_min_max\Plugin\Field\FieldWidget;

use DateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;

/**
 * Plugin implementation of the 'datetime_default' widget.
 *
 * @FieldWidget(
 *   id = "datetime_min_max",
 *   label = @Translation("Date and time with min max restriction"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class DateTimeMinMaxWidget extends DateTimeDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $settings = $this->getSettings();

    $min = $settings['min']['input_value'];
    if ($settings['min']['input_type'] === 'relative') {
      $min = $this->getRelativeDate($min, $settings['min']['input_modifier']);
    }

    $max = $settings['max']['input_value'];
    if ($settings['max']['input_type'] === 'relative') {
      $max = $this->getRelativeDate($max, $settings['max']['input_modifier']);
    }

    if (!empty($min)) {
      $element['value']['#attributes']['min'] = $min;
    }

    if (!empty($max)) {
      $element['value']['#attributes']['max'] = $max;
    }

    if ($element['value']['#default_value'] === NULL && !empty($min)) {
      $element['value']['#attributes']['class'] = ['datetime-min-max-default-min'];
      $element['value']['#attached']['library'][] = 'datetime_min_max/default_min';
    }

    return $element;
  }

  /**
   * Get the relative date formatted.
   *
   * @param string $value
   *   The value.
   * @param string $modifier
   *   The modifier.
   *
   * @return string
   *   The new date modified.
   *
   * @throws \Exception
   */
  protected function getRelativeDate(string $value, string $modifier = NULL) {
    $date = new DateTime($value);
    if (!empty($modifier)) {
      $date->modify($modifier);
    }
    return $date->format('Y-m-d');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'min' => [
        'input_type' => '',
        'input_value' => '',
        'input_modifier' => '',
      ],
      'max' => [
        'input_type' => '',
        'input_value' => '',
        'input_modifier' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();
    $type_options = [
      'relative' => $this->t('Relative'),
      'static' => $this->t('Static'),
    ];

    $element['min'] = [
      '#type' => 'details',
      '#title' => $this->t('Minimum settings'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    $element['min']['input_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $type_options,
      '#empty_option' => $this->t('None'),
      '#empty_value' => '',
      '#default_value' => $settings['min']['input_type'],
    ];
    $element['min']['input_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date'),
      '#default_value' => $settings['min']['input_value'],
      '#states' => [
        'invisible' => [
          '[name$="[settings][min][input_type]"]' => ['value' => ''],
        ],
        'required' => [
          '[name$="[settings][min][input_type]"]' => ['!value' => ''],
        ],
      ],
    ];
    $element['min']['input_modifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Modifier'),
      '#default_value' => $settings['min']['input_modifier'],
      '#states' => [
        'visible' => [
          '[name$="[settings][min][input_type]"]' => ['value' => 'relative'],
        ],
      ],
    ];
    $element['min']['relative_description'] = [
      '#type' => 'item',
      '#markup' => $this->t("When use relative type the Date field is used as initial value for new DateTime constructor and if it's fill Modifier field then this field is used for modify the Date field. Both fields (Date and Modifier) can be filled with relative formats supports by php: https://php.net/manual/es/datetime.formats.relative.php"),
      '#states' => [
        'visible' => [
          '[name$="[settings][min][input_type]"]' => ['value' => 'relative'],
        ],
      ],
    ];
    $element['min']['static_description'] = [
      '#type' => 'item',
      '#markup' => $this->t('You must fill the Date field with valid date in a format like this: @date', ['@date' => date('Y-m-d')]),
      '#states' => [
        'visible' => [
          '[name$="[settings][min][input_type]"]' => ['value' => 'static'],
        ],
      ],
    ];

    $element['max'] = [
      '#type' => 'details',
      '#title' => $this->t('Maximum settings'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    $element['max']['input_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $type_options,
      '#empty_option' => $this->t('None'),
      '#empty_value' => '',
      '#default_value' => $settings['max']['input_type'],
    ];
    $element['max']['input_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date'),
      '#default_value' => $settings['max']['input_value'],
      '#states' => [
        'invisible' => [
          '[name$="[settings][max][input_type]"]' => ['value' => ''],
        ],
        'required' => [
          '[name$="[settings][max][input_type]"]' => ['!value' => ''],
        ],
      ],
    ];
    $element['max']['input_modifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Modifier'),
      '#default_value' => $settings['max']['input_modifier'],
      '#states' => [
        'visible' => [
          '[name$="[settings][max][input_type]"]' => ['value' => 'relative'],
        ],
      ],
    ];
    $element['max']['relative_description'] = [
      '#type' => 'item',
      '#markup' => $this->t("When use relative type the Date field is used as initial value for new DateTime constructor and if it's fill Modifier field then this field is used for modify the Date field. Both fields (Date and Modifier) can be filled with relative formats supports by php: https://php.net/manual/es/datetime.formats.relative.php"),
      '#states' => [
        'visible' => [
          '[name$="[settings][max][input_type]"]' => ['value' => 'relative'],
        ],
      ],
    ];
    $element['max']['static_description'] = [
      '#type' => 'item',
      '#markup' => $this->t('You must fill the Date field with valid date in a format like this: @date', ['@date' => date('Y-m-d')]),
      '#states' => [
        'visible' => [
          '[name$="[settings][max][input_type]"]' => ['value' => 'static'],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary = [];

    if (!empty($settings['min']['input_type'])) {
      $min = $settings['min']['input_value'];
      if ($settings['min']['input_type'] === 'relative') {
        $min = $this->getRelativeDate($min, $settings['min']['input_modifier']);
      }

      $summary[] = $this->t('Minimum: Type = %type | Date = %date', [
        '%type' => $settings['min']['input_type'],
        '%date' => $min,
      ]);
    }

    if (!empty($settings['max']['input_type'])) {
      $max = $settings['max']['input_value'];
      if ($settings['max']['input_type'] === 'relative') {
        $max = $this->getRelativeDate($max, $settings['max']['input_modifier']);
      }

      $summary[] = $this->t('Maximum: Type = %type | Date = %date', [
        '%type' => $settings['max']['input_type'],
        '%date' => $max,
      ]);
    }

    return $summary;
  }

}

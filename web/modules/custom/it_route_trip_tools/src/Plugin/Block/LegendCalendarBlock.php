<?php

namespace Drupal\it_route_trip_tools\Plugin\Block;

// BlockBase implements the interface BlockPluginInterface.
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays a list of taxonomy terms with the colors for each term as a legend.
 *
 *  The block is a plugin that is registered through Annotations,
 * because the system will be able to know its existence,
 *  Section/block of comments is mandatory & has to contain "Block" directive.
 *
 *  - id: unique identifier, we will use it as a module name prefix.
 *  - admin_label: administrative label of the block. It corresponds with the
 * block name on the admin blocks list and the its default title.
 *  - category: the block category name, into the admin list.
 * If it isn't defined it will correspond to module name defined in the block.
 *
 * @Block(
 *    id = "it_route_trip_tools_block_legend",
 *    admin_label = @Translation("Block legend"),
 *    category = @Translation("Custom")
 * )
 */
class LegendCalendarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager, used to fetch entity link templates.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerChannelFactory) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $loggerChannelFactory;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );

  }

  /**
   * Function to get all terms of taxonomy that belongs a specific vocabulary.
   */
  private function getVocabularyTerms($vid) {
    // Check if a vocabulary identifier is arrived, else return an empty array.
    if (empty($vid)) {
      return [];
    }

    // Get all terms (it gets from into the cache).
    $terms = &drupal_static(__FUNCTION__);

    // Get all terms of taxonomy from the database (if not in cache).
    if (!isset($terms[$vid])) {
      $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $query->condition('vid', $vid)->accessCheck(TRUE);
      $tids = $query->execute();
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($tids);
    }

    return $terms;

  }

  /**
   * Retrieves the vocabulary name from its identifier.
   */
  private function getVocabularyName($vid) {

    // Check if it is a vocabulary identifier, else return an empty string.
    if (empty($vid)) {
      return "";
    }

    // Get the vocabulary name.
    $vocabulary = $this->entityTypeManager->getStorage('taxonomy_vocabulary')->load($vid);

    return $vocabulary->label();

  }

  /**
   * Returns a rendered array that contains the block content.
   */
  public function build() {
    // Get the block configuration.
    $config_str = $this->configuration['it_route_trip_tools_source_legend_calendar'];

    // We check if the configuration exists.
    if (!isset($config_str)) {
      return [
        '#markup' => '<span>' . $this->t('No configuration set. Check the block configuration.') . '</span>',
      ];
    }

    // We retrieve the block configuration (inside the configuration there are
    // two values that are separated by a &).
    $config = explode('&', $config_str);

    // We check that exist at least two params into the config variable.
    if (count($config) < 2) {
      return [
        '#markup' => '<span>' . $this->t('No configuration set. Check the block configuration.') . '</span>',
      ];
    }

    // Get 2 params (view name & presentation name) & save them into variables.
    $config_view = $config[0];
    $config_display = $config[1];

    // Get the view and the view presentation to,
    // obtain the colors configured into the view.
    $view = $this->entityTypeManager->getStorage('view')->load($config_view);
    $display = $view->getDisplay($config_display);

    // Get the vocabulary identifier to load their terms into an array.
    $vid = $display['display_options']['style']['options']['vocabularies'];

    // Check if a color configuration already exists for the vocabulary terms.
    if (!array_key_exists('color_taxonomies', $display['display_options']['style']['options'])) {
      $this->logger->get('it_route_trip_tools')
        ->warning($this->t('No colors has found for each term of the vocabulary. Check the display view configuration.'));
    }
    else {
      // Get an array that contains the color of vocabulary terms.
      $colors = $display['display_options']['style']['options']['color_taxonomies'];
    }

    // Get the taxonomy terms that belongs a vocabulary.
    $terms = $this->getVocabularyTerms($vid);

    // Iterate taxonomy terms to generate the legend table rows.
    foreach ($terms as $term_id) {
      if (array_key_exists($term_id->id(), $colors)) {
        // If the taxonomy term has defined a color.
        $table_rows[] = [$term_id->getName(), $colors[$term_id->id()]];
      }
      else {
        // If the taxonomy term has not defined a color.
        $table_rows[] = [$term_id->getName(), '#ffffff'];
      }
    }

    // Get the vocabulary name to put on the column header.
    $vocabulary_name = $this->getVocabularyName($vid);

    // Set the value of columns headers.
    $table_header = [$vocabulary_name, $this->t('Color')];

    if (count($terms) == 0) {
      $this->logger->get('it_route_trip_tools')
        ->warning($this->t('The selected vocabulary has not terms.'));
      $build = [
        '#markup' => '<span>' . $this->t('The selected vocabulary has not terms.') . '</span>',
      ];
    }
    else {
      $build['legend_calendar_block_table'] = [
        '#theme' => 'it_route_trip_tools_legend_calendar_block_table',
        '#headers' => $table_header,
        '#rows' => $table_rows,
      ];
    }

    // Return the HTML content generated.
    return [
      $build,
    ];

  }

  /**
   * Modify the default values that it will have the block configuration.
   */
  public function defaultConfiguration() {

    return [
      // Name by default that we want the block have, if we don't specify,
      // it will get the admin_label from class Annotations.
      'label' => 'Legend calendar',
      // FALSE that the title won't be visible by default, to make the title
      // visible is not correct the TRUE value, the correct way is using the
      // BlockInterface::BLOCK_LABEL_VISIBLE constant with
      // "use Drupal\block\BlockInterface".
      'label_display' => FALSE,
    ];

  }

  /**
   * Alters/add fields into the block configuration form.
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // We get an array with all active views.
    $views = Views::getEnabledViews();
    $lViews = [];
    // We iterate the list of views.
    foreach ($views as $view) {
      if ($view->get('display') != NULL) {
        $displays = $view->get('display');
        // We iterate all presentations of the current view.
        foreach ($displays as $display) {
          // We only include into the list those presentations of the views
          // that are in "Full Calendar Display" format.
          if (array_key_exists('style', $display['display_options'])) {
            if ($display['display_options']['style']['type'] == 'fullcalendar_view_display') {
              $lViews[$view->id() . '&' . $display['id']] = $view->label() . ' - ' . $display['display_title'];
            }
          }
        }
      }
    }

    $source_legend_calendar = NULL;

    // We get the configuration value in case that it exist.
    if (array_key_exists('it_route_trip_tools_source_legend_calendar', $this->configuration)) {
      $source_legend_calendar = $this->configuration['it_route_trip_tools_source_legend_calendar'];
    }

    // We add a select field where it will show all views that are selectable.
    $form['it_route_trip_tools_fullcalendar_view_display_source'] = [
      '#type' => 'select',
      '#title' => $this->t('Fullcalendar View - Presentation'),
      '#description' => $this->t('To see here your view: the format of the presentation should be Full Calendar Display and the format of the presentation should be overwritten.'),
      '#required' => TRUE,
      '#default_value' => $source_legend_calendar,
      '#options' => $lViews,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * This method has the mission to save the form values if previously has
   * passed the blockValidate method validations and also form base validations.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // We read the fields values and we save them into configuration vars.
    $this->configuration['it_route_trip_tools_source_legend_calendar'] = $form_state->getValue('it_route_trip_tools_fullcalendar_view_display_source');
  }

}


<?php
/*
 * For commands that are parts of modules, Drush expects to find command files
 * in __MODULE__/src/Commands, and the namespace is Drupal/__MODULE__/Commands.
 *
 * In addition to a commandfile like this one, you need to add a
 * drush.services.yml in root of your module like this module does.
 */

namespace Drupal\scheduled_updates\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\scheduled_updates\ScheduledUpdateInterface;
use Drupal\scheduled_updates\UpdateRunnerUtils;
use Drush\Commands\DrushCommands;

/**
 * Class RunUpdatesCommand.
 *
 * @package Drupal\scheduled_updates
 */
class RunUpdatesCommand extends DrushCommands {

  /**
   * The updates runner service.
   *
   * @var \Drupal\scheduled_updates\UpdateRunnerUtils
   */
  protected $scheduledUpdatesRunner;

  /**
   * RulesCommands constructor.
   *
   * @param \Drupal\scheduled_updates\UpdateRunnerUtils $scheduledUpdatesRunner
   *   The runner service.
   */
  public function __construct(UpdateRunnerUtils $scheduledUpdatesRunner) {
    parent::__construct();
    $this->scheduledUpdatesRunner = $scheduledUpdatesRunner;
  }

  /**
   * Check all or named types for scheduled updates.
   *
   * @command sup:run_updates
   * @aliases sup-run, sup:run
   *
   * @usage drush sup:run
   *   Run updates.
   *
   * @param array $options
   *   (optional) The options.
   *
   * @option types
   *   Set to comma-separated list of machine name(s) of the types to update,
   *   otherwise all types.
   *
   * @return string
   *   OK message.
   */
  public function update_runner($options = ['types' => NULL]) {
    if ($this->scheduledUpdatesRunner) {
      if ($options['types']) {
        $update_types = explode(',', $options['types']);
      }
      else {
        $update_types = [];
      }
      $this->scheduledUpdatesRunner->runAllUpdates($update_types, TRUE);
      $this->output()->writeln(dt('Updates run.'));
      return;
    }
    $message = dt('Could not get Global Runner service. No updates run.');
    $this->output()->writeln($message);
  }


  /**
   * Show current state of updates.
   *
   * @command sup:list_runners
   * @aliases sup-list, sup:list
   *
   * @usage drush sup:list
   *   Run updates.
   *
   * @param array $options
   *   (optional) The options.
   *
   * @field-labels
   *   id: Update ID
   *   name: Plugin ID
   *   desc: Description
   *   count: count
   *   after: After Run Action
   *   fields: Fields
   *   entities: Entities
   *   class: Class
   * @default-fields id,count,entities,after
   *
   * @option unrun
   *   Only list entities that have not been processed.
   * @option types
   *   Set to comma-separated list of machine name(s) of the update types
   *   to update, otherwise all types.
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   Table of runners.
   */
  public function list_runners($options = [
    'format' => 'table',
    'limit' => 20,
    'types' => NULL,
    'unrun' => NULL,
    ]) {

    $entityTypeManager = \Drupal::service('entity_type.manager');
    if ($options['types']) {
      $update_types = explode(',', $options['types']);
    }
    else {
      $update_types = [];
    }

    // $requestTime = \Drupal::time()->getRequestTime();
    $runners = $this->scheduledUpdatesRunner->getUpdateTypeRunners($update_types);

    $result = [];
    foreach ($runners as $key => $runner) {
      $reffields = $runner->getReferencingFieldIds();
      $entity_ids = [];
      if ($reffields) {
        /** @var \Drupal\Core\Entity\EntityStorageBase $entity_storage */
        $entityStorage = $entityTypeManager->getStorage($runner->updateEntityType());
        $entityType = $entityStorage->getEntityType();

        foreach ($reffields as $field_id) {
          /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
          $query = $entityStorage->getQuery('AND');
          $query->accessCheck(FALSE);
          //  $query->condition("$field_id.entity.". 'update_timestamp', $requestTime, '<=');
          $query->condition("$field_id.entity.". 'type', $key);
          if ($options['unrun']) {
            $query->condition("$field_id.entity.". 'status', ScheduledUpdateInterface::STATUS_UNRUN);
          }
          $entity_ids += $query->execute();
        }
      }

      $row = [
        'id' => $key,
        'name' => $runner->getPluginId(),
        'desc' => $runner->getDescription(),
        'after' => $runner->getAfterRun(),
        'fields' => implode(',', $reffields),
        'count' => $runner->getQueue()->numberOfItems(),
        'entities' => $entityType->getBaseTable() . ':' . implode(',', $entity_ids),
      ];
      $result[] = $row;
    }
    return new RowsOfFields($result);
  }

}

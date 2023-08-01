<?php

/**
 * @file
 * Contains \Drupal\scheduled_updates\ScheduledUpdateInterface.
 */

namespace Drupal\scheduled_updates;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\TypedData\TranslationStatusInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Scheduled update entities.
 *
 * @ingroup scheduled_updates
 */
interface ScheduledUpdateInterface extends ContentEntityInterface,
  TranslationStatusInterface,
  EntityChangedInterface,
  EntityOwnerInterface,
  \IteratorAggregate {

  /** @var int Status value: item not processed. */
  const STATUS_UNRUN = 1;

  /** @var int Status value: item is in queue. */
  const STATUS_INQUEUE = 2;

  /** @var int Status value: item is requeued. */
  const STATUS_REQUEUED = 3;

  /** @var int Status value: item processed successfully. */
  const STATUS_SUCCESSFUL = 4;

  /** @var int Status value: item processing not successful. */
  const STATUS_UNSUCESSFUL = 5;

  /** @var int Status value: item is inactive. */
  const STATUS_INACTIVE = 6;

  /**
   * Return the creation time of the entity in seconds since epoch.
   */
  public function getCreatedTime();

  /**
   * Set the entities to update for an update entity.
   *
   * This currently just sets the field value but does not save.
   *
   * @param array $update_entity_ids
   */
  public function setUpdateEntityIds(array $update_entity_ids);

  /**
   * Return the set of entities to update, as an array.
   *
   * @return array
   *   Array of the update entity IDs.
   */
  public function getUpdateEntityIds();

}

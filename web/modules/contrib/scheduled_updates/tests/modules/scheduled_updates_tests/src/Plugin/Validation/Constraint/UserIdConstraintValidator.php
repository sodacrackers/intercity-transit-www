<?php

namespace Drupal\scheduled_updates_tests\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserIdConstraintValidator extends ConstraintValidator {

  const CONSTRAINT_NONE = 'CONSTRAINT_NONE';
  const CONSTRAINT_ANONYMOUS = 'CONSTRAINT_ANONYMOUS';
  const CONSTRAINT_USER_1 = 'CONSTRAINT_USER_1';

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getParent()->getValue();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }
    $constraint = \Drupal::state()->get('constraint_uid', static::CONSTRAINT_NONE);
    if ($constraint !== static::CONSTRAINT_NONE) {
      if ($constraint === static::CONSTRAINT_USER_1 && ((int)\Drupal::currentUser()->id()) !== 1) {
        throw new \LogicException("Only uid 1 validates");
      }
      elseif ($constraint === static::CONSTRAINT_ANONYMOUS && !\Drupal::currentUser()->isAnonymous()) {
        throw new \LogicException("Only uid 1 validates");
      }
    }
  }

}

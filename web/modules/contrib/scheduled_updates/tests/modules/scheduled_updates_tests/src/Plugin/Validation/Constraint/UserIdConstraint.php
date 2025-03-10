<?php

namespace Drupal\scheduled_updates_tests\Plugin\Validation\Constraint;


use Symfony\Component\Validator\Constraint;

/**
 * Checks for the user id.
 *
 * @Constraint(
 *   id = "UserId",
 *   label = @Translation("User Id", context = "Validation")
 * )
 *
 */
class UserIdConstraint extends Constraint {


}

<?php
/**
 * @file
 * Contains \Drupal\Tests\scheduled_updates\WebTestExtended.
 */

namespace Drupal\Tests\scheduled_updates\FunctionalJavascript;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\BrowserTestBase;

/**
 * BrowserTestBase plus project agnostic helper functions.
 */
abstract class WebTestExtended extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Store last user to easily login back in.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $last_user;

  /**
   * Create user and login with given permissions.
   *
   * @param array $permissions
   *
   * @return \Drupal\user\Entity\User
   *
   * @throws \LogicException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function loginWithPermissions(array $permissions) {
    if ($user = $this->createUser($permissions)) {
      $this->drupalLogin($user);
      return $user;
    }
    throw new \LogicException('Could not create user.');
  }

  /**
   * Overridden to add easy switch back functionality.
   *
   * {@inheritdoc}
   */
  protected function drupalLogin(AccountInterface $account) {
    $this->last_user = $this->loggedInUser;
    parent::drupalLogin($account);
  }

  /**
   * Login previous user.
   *
   * If no previous user this logic problem with the test.
   *
   * @throws \LogicException
   */
  protected function loginLastUser() {
    if ($this->last_user) {
      $this->drupalLogin($this->last_user);
    }
    else {
      throw new \LogicException('No last user. Testing logic exception.');
    }
  }

  /**
   * Check an entity value after reload.
   *
   * @param $entity_type_id
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param $field
   * @param $value
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function checkEntityValue($entity_type_id, ContentEntityInterface $entity, $field, $value) {
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    $storage->resetCache([$entity->id()]);
    $updated_entity = $storage->load($entity->id());
    $this->assertEquals($updated_entity->get($field)->value, 1, $entity->label() . " $field = $value");
  }

  /**
   * Utility Function to get a date relative from current.
   *
   * @param string $modify
   *  Relative date string, e.g. +1 month
   * @param string $format
   *  PHP DateTime::format string.
   *
   * @return string
   */
  protected function getRelativeDate($modify, $format = 'Y-m-d') {
    $date = new \DateTime();
    $date->modify($modify);
    return $date->format($format);
  }

  /**
   * Utility function to check that a select has only the expected options.
   *
   * @param string $select_id
   *   The field name.
   * @param array $expected_options
   *   The expected options.
   * @param array $unexpected_options
   *   The unexpected options.
   */
  protected function checkExpectedOptions($select_id, $expected_options, $unexpected_options = []) {
    foreach ($expected_options as $expected_option) {
      $this->assertSession()->optionExists($select_id, $expected_option);
    }
    foreach ($unexpected_options as $unexpected_option) {
      $this->assertSession()->optionNotExists($select_id, $unexpected_option);
    }
  }

  /**
   * Utility function to check that a radio group has only the expected options.
   *
   * @param string $name
   *   The field name.
   * @param array $expected_options
   *   The expected options.
   * @param array $unexpected_options
   *   The unexpected options.
   */
  protected function checkExpectedRadioOptions($name, $expected_options, $unexpected_options = []) {
    foreach ($expected_options as $expected_option) {
      $this->assertSession()->fieldValueEquals($name, $expected_option);

    }
    foreach ($unexpected_options as $unexpected_option) {
      $this->assertSession()->fieldValueNotEquals($name, $unexpected_option);
    }
  }

  /**
   * Utility function to check that a checkboxes has the expected options.
   *
   * @param string $field_name
   *   The field name.
   * @param array $expected_options
   *   The expected options.
   */
  protected function checkExpectedCheckboxes($field_name, $expected_options) {
    foreach ($expected_options as $expected_option) {
      $this->assertSession()->fieldExists("base_fields[$expected_option]");
    }
  }

  /**
   * Utility Function around drupalGet to avoid call if not needed.
   *
   * @param $path
   */
  protected function gotoURLIfNot($path) {
    if ($path != $this->getUrl()) {
      $this->drupalGet($path);
    }
  }

  /**
   * Utility function to check that current user does not access to a given
   * path.
   *
   * @param null $path
   */
  protected function checkAccessDenied($path = NULL) {
    if ($path) {
      $this->drupalGet($path);
    }
    // 'Accessed denied on path: ' . $path
    $this->assertSession()->pageTextContains('Access denied');
  }

}

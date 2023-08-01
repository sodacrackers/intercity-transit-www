<?php
/**
 * @file
 * Contains \Drupal\Tests\scheduled_updates\EmbeddedScheduledUpdateTypeTestBase.
 */


namespace Drupal\Tests\scheduled_updates\FunctionalJavascript;

// In test sub-module scheduled_updates_tests.
use Drupal\scheduled_updates_tests\Plugin\Validation\Constraint\UserIdConstraintValidator;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Base test class for embedded update types.
 *
 * There are differences in how the types are create but after that they have
 * the same testing needs.
 *
 * @see checkAfterTypeCreated()
 *
 * This also contain utility functions dealing with Inline Entity Form.
 */
abstract class EmbeddedScheduledUpdateTypeTestBase extends ScheduledUpdatesTestBase {

  use CronRunTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'scheduled_updates',
    'scheduled_updates_tests',
    'node',
    'user',
    'field_ui',
    'block',
    'inline_entity_form',
    'dblog'
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    \Drupal::state()
      ->set('constraint_uid', UserIdConstraintValidator::CONSTRAINT_NONE);
  }

  /**
   * Make sure Referenced types do not have a direct add form.
   *
   * @param string $label
   * @param string $type_id
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function confirmNoAddForm($label, $type_id) {
    $this->loginWithPermissions(["create $type_id scheduled updates"]);
    $this->drupalGet('admin/content/scheduled-update/add');
    // 'Referenced type label does not appear on update add page.'
    $this->assertSession()->pageTextNotContains($label);
    // 'Update time field is not available on add page.'
    $this->assertSession()->pageTextNotContains('Update Date/time');
    $this->checkAccessDenied('admin/content/scheduled-update/add/' . $type_id);
    $this->loginLastUser();
  }

  /**
   * Make sure that reference field was created and put on target entity type.
   *
   * @param $entity_type
   * @param string $bundle
   * @param string $reference_field_label
   * @param string $reference_field_name
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function checkReferenceCreated($entity_type,
                                           $bundle,
                                           $reference_field_label,
                                           $reference_field_name) {
    $this->loginWithPermissions([
      'administer node fields',
      'administer content types',
    ]);
    $this->drupalGet("admin/structure/types/manage/$bundle/fields");
    $this->assertSession()->pageTextContains($reference_field_label);
    $this->assertSession()->pageTextContains($reference_field_name);
    $this->loginLastUser();
  }

  /**
   * Make sure that reference field was created and put on target entity type.
   *
   * @param string $entity_type
   * @param string $bundle
   * @param string $reference_field_label
   * @param string $reference_field_name
   *
   * @throws \Exception
   */
  protected function checkReferenceOnEntityType($entity_type,
                                                $bundle,
                                                $reference_field_label,
                                                $reference_field_name) {
    $this->loginWithPermissions(["create $bundle content"]);
    $this->drupalGet("node/add/$bundle");
    $this->assertSession()->pageTextContains($reference_field_label);
    // @todo Check html for field
    $this->loginLastUser();
    // $field_id = 'edit-' . str_replace('_', '-', $reference_field_name);
    //$this->assertFieldbyId($field_id);
  }

  /**
   * @param array $edit
   * @param string $add_url
   * @param string $reference_field_label
   * @param string $reference_field_name
   *
   * @return array
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function checkNewFieldRequired(array &$edit,
                                           $add_url,
                                           $reference_field_label,
                                           $reference_field_name) {
    // Save first without new field information.
    // This is only enforce by javascript states,
    // @see \Drupal\scheduled_updates\Form\ScheduledUpdateTypeBaseForm::validateForm

    $page = $this->getSession()->getPage();
    // Remove label explicitly.
    $page->fillField('reference_settings[new_field][label]', '');
    //$page->fillField('reference_settings[new_field][field_name]', '');
    $page->pressButton('Save');
    $this->assertUrl($add_url);

    $edit['reference_settings[new_field][label]'] = $reference_field_label;

    return $edit;
  }

  /**
   * Get the URL for adding an entity.
   *
   * Hard-coded for node style path now
   *
   * @param string $entity_type
   * @param string $bundle
   *
   * @return string
   */
  protected function getEntityAddURL($entity_type, $bundle) {
    return "$entity_type/add/$bundle";
  }

  /**
   * Gets IEF button name.
   *
   * Copied from IEF module.
   *
   * @param string $xpath
   *   Xpath of the button.
   *
   * @return string|null
   *   The name of the button.
   */
  protected function getButtonName($xpath) {
    // \Behat\Mink\Element\NodeElement[]
    $elements = $this->xpath($xpath);
    if ($elements) {
      if ($elements[0]->hasAttribute('name')) {
        return $elements[0]->getAttribute('name');
      }
    }
    return '';
  }

  /**
   * Submit a IEF Form with Ajax.
   *
   * @param string $label
   * @param string $drupal_selector
   * @param array $edit
   */
  protected function submitIEFForm($label, $drupal_selector, $edit = []) {
    $name = $this->getButtonName("//input[@type=\"submit\" and @value=\"$label\" and @data-drupal-selector=\"$drupal_selector\"]");
    // Was drupalPostAjaxForm:
    $this->drupalPostForm(NULL, $edit, $name);
  }

  /**
   * Checking adding and running updates for title.
   *
   * Hardcoded for node 'title' property now.
   *
   * @param string $bundle
   * @param string $reference_field_label
   * @param string $reference_field_name
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function checkRunningTitleUpdates($bundle,
                                              $reference_field_name,
                                              $reference_field_label) {
    foreach (['ui', 'cron'] as $run_updates_via) {
      $update_node = $this->createNodeWithUpdate(
        'Title to be updated',
        '-1 year', $bundle,
        $reference_field_name, $reference_field_label);

      $no_update_node = $this->createNodeWithUpdate(
        'Title NOT to be updated',
        '+1 year', $bundle,
        $reference_field_name, $reference_field_label);

      if ($run_updates_via == 'cron') {
        // Because the update type is UpdateRunnerInterface::USER_UPDATE_RUNNER
        // the update should switch to user #1 during cron. Trigger validation
        // error.
        \Drupal::state()
          ->set('constraint_uid', UserIdConstraintValidator::CONSTRAINT_USER_1);
        $this->cronRun();
        // Reset to avoid trigger validation error.
        \Drupal::state()
          ->set('constraint_uid', UserIdConstraintValidator::CONSTRAINT_NONE);
      }
      else {
        $this->runUpdatesUI();
      }

      $this->drupalGet("node/" . $update_node->id());
      // 'Update title appears on past update'
      $this->assertSession()->pageTextContains('Title to be updated:updated');

      $this->drupalGet("node/" . $no_update_node->id());
      // 'Original node title appears on future update'

      $this->assertSession()->pageTextContains("Title NOT to be updated");
      // , 'Update title does not appear on future update'
      $this->assertSession()->pageTextNotContains("Title NOT to be updated:updated");

      $update_node->delete();
      $no_update_node->delete();
    }

    // Make sure that the user is switched to user #1 when updates are run with
    // cron and UpdateRunnerInterface::USER_UPDATE_RUNNER was used.
    // @see \Drupal\scheduled_updates_tests\Plugin\Validation\Constraint\UserIdConstraintValidator
    $invalid_update = $this->createNodeWithUpdate(
      'Title to be updated',
      '-1 year',
      $bundle,
      $reference_field_name, $reference_field_label);

    \Drupal::state()
      ->set('constraint_uid', UserIdConstraintValidator::CONSTRAINT_ANONYMOUS);
    $this->cronRun();
    \Drupal::state()
      ->set('constraint_uid', UserIdConstraintValidator::CONSTRAINT_NONE);
    $this->drupalGet("node/" . $invalid_update->id());

    // 'Title not updated with validation error'
    $this->assertSession()->pageTextNotContains('Title to be updated:updated');
    $invalid_update->delete();

  }

  /**
   * Checking adding and running updates for title.
   *
   * Hardcoded for node 'title' property now.
   *
   * @param string $bundle
   * @param string $reference_field_label
   * @param string $reference_field_name
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function checkRunningPromoteUpdates($bundle,
                                                $reference_field_name,
                                                $reference_field_label) {
    $update_node = $this->createNodeWithUpdate(
      'Upate Node',
      '-1 year',
      $bundle,
      $reference_field_name, $reference_field_label,
      TRUE);

    $no_update_node = $this->createNodeWithUpdate(
      'No update node',
      '+1 year',
      $bundle,
      $reference_field_name, $reference_field_label,
      TRUE);

    $this->runUpdatesUI();

    $this->checkEntityValue('node', $update_node, 'promote', 1);
    $this->checkEntityValue('node', $no_update_node, 'promote', 0);
  }

  /**
   * @param string $title
   * @param string $date_offset
   * @param string $bundle
   * @param string $reference_field_label
   * @param string $reference_field_name
   *
   * @param bool $field_hidden
   *
   * @return \Drupal\node\NodeInterface
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   */
  protected function createNodeWithUpdate($title,
                                          $date_offset,
                                          $bundle,
                                          $reference_field_name,
                                          $reference_field_label,
                                          $field_hidden = FALSE) {

    $page = $this->getSession()->getPage();
    $id_field_name = str_replace('_', '-', $reference_field_name);
    $entity_add_url = $this->getEntityAddURL('node', $bundle);
    $this->drupalGet($entity_add_url);
    $ief_button = "Add new $reference_field_label";
    $this->assertSession()->buttonExists($ief_button);
    // Open IEF form
    $this->submitIEFForm($ief_button, "edit-$id_field_name-actions-ief-add");
    // Check opened form.
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Update Date/time');

    // Submit IEF form
    // Create ief_test_complex node.
    $page->fillField("{$reference_field_name}[form][0][update_timestamp][0][value][date]", $this->getRelativeDate($date_offset, 'm/d/Y'));
    $page->fillField("{$reference_field_name}[form][0][update_timestamp][0][value][time]", '01:00:00AM');
    if ($field_hidden) {
//      $inputs = $this->getSession()->getDriver()->find("(//html//form)");
//      foreach ($inputs as $element) {
//        echo "====== Found: " . PHP_EOL . $element->getOuterHtml() . PHP_EOL . PHP_EOL;
//      }
      // Hard-coded for now. @todo Create parameter to this function.
      // promote_reference[form][inline_entity_form][field_promote][value]
      $ief_field_name = "{$reference_field_name}[form][0][field_promote][value]";
      //($ief_field_name, NULL, "$reference_field_name - hides update field");
      $this->assertSession()->fieldNotExists($ief_field_name);
    }
    else {
      // Hard-coded for now. @todo Create parameter to this function.
      $ief_field_name = "{$reference_field_name}[form][0][field_title][0][value]";
      $this->assertSession()->fieldExists($ief_field_name);
      $page->fillField($ief_field_name, "$title:updated");
    }
    $page->pressButton("Create $reference_field_label");
    // Edit button appears when saved.
    // 'Saving IEF Update was successful.';
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', "[data-drupal-selector='edit-$id_field_name-entities-0-actions-ief-entity-edit']"));

    // Create ief_test_complex node.
    $edit = ['title[0][value]' => $title];
    $this->drupalPostForm(NULL, $edit, t(static::NODE_SAVE_BUTTON_TEXT));
    // 'Saving parent entity was successful.'
    $node = $this->drupalGetNodeByTitle($title);
    $this->assertTrue($node, "Node was created");
    return $node;
  }

  /**
   * @param string $label
   * @param string $type_id
   * @param string $reference_field_label
   * @param string $reference_field_name
   * @param $clone_field
   *
   * @throws \Behat\Mink\Exception\DriverException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  protected function checkAfterTypeCreated($label,
                                           $type_id,
                                           $reference_field_label,
                                           $reference_field_name,
                                           $clone_field) {
    $permissions = [
      "create $type_id scheduled updates",
      'administer scheduled updates',
    ];
    // Check both permissions tha will allow the user to create updates.
    foreach ($permissions as $permission) {
      // Give permission to create the current update type.
      $this->grantPermissionsToUser([$permission]);
      $this->confirmNoAddForm($label, $type_id);
      $this->checkReferenceCreated('node', 'page', $reference_field_label, $reference_field_name);
      $this->checkReferenceOnEntityType('node', 'page', $reference_field_label, $reference_field_name);
      switch ($clone_field) {
        case 'title':
          $this->checkRunningTitleUpdates('page', $reference_field_name, $reference_field_label);
          break;
        case 'promote':
          $this->checkRunningPromoteUpdates('page', $reference_field_name, $reference_field_label);
          break;
      }
      $this->revokePermissionsFromUser([$permission]);
    }
  }

}

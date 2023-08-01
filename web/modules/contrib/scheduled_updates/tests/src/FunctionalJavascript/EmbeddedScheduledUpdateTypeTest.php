<?php
/**
 * @file
 * Contains \Drupal\Tests\scheduled_updates\EmbeddedScheduledUpdateTypeTest.
 */

namespace Drupal\Tests\scheduled_updates\FunctionalJavascript;

use Drupal\scheduled_updates\Plugin\UpdateRunnerInterface;


/**
 * Test adding an Embedded Scheduled Update Type.
 *
 * @group scheduled_updates
 */
class EmbeddedScheduledUpdateTypeTest extends EmbeddedScheduledUpdateTypeTestBase {

  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
  }

  public function testCreateType() {
    $type_id = 'foo_type';
    $label = 'Foo Type';
    $clone_fields = [
      'base_fields[title]' => [
        'input_value' => 'title',
        'label' => t('Title'),
      ],
    ];

    $this->createType($label, $type_id, $clone_fields);
  }

  /**
   * Create a scheduled update type via the UI.
   *
   * @param $label
   * @param $type_id
   * @param array $clone_fields
   * @param array $type_options
   *
   * @throws \Exception
   */
  protected function createType($label, $type_id, array $clone_fields, $type_options = []) {
    $add_url = 'admin/config/workflow/scheduled-update-type/add';
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet($add_url);
    // Revision options should not be displayed until entity type that supports it is selected.
    $assert->pageTextNotContains('The owner of the last revision.');
    $assert->pageTextNotContains('Create New Revisions');
    $edit = $type_options + [
        'label' => $label,
        //'id' => $type_id,
        'update_entity_type' => 'node',
        'update_runner[id]' => 'default_embedded',
        'update_runner[after_run]' => UpdateRunnerInterface::AFTER_DELETE,
        'update_runner[invalid_update_behavior]' => UpdateRunnerInterface::INVALID_DELETE,
        'update_runner[update_user]' => UpdateRunnerInterface::USER_UPDATE_RUNNER,
      ];

    $this->checkRunnersAvailable();
    $page->fillField('update_entity_type', $edit['update_entity_type']);
    $assert->assertWaitOnAjaxRequest();
    $page->fillField('update_runner[id]', $edit['update_runner[id]']);
    $assert->assertWaitOnAjaxRequest();
    //
    $page->find('css', 'summary:contains("Advanced Runner Options")')->click();
    $this->assertNotEmpty($assert->waitForElementVisible('css', '[name="update_runner[after_run]"]'));

    $this->fillFields($edit, ['update_entity_type', 'update_runner[id]']);
    // Was drupalPostAjaxForm.
    //$this->drupalPostForm(NULL, $edit, 'update_entity_type');

    $assert->pageTextContains('The owner of the last revision.');
    $assert->pageTextContains('Create New Revisions');
    unset($edit['update_entity_type'], $edit['update_runner[id]']);

    $reference_field_label = 'Reference Label';
    $reference_field_name = 'reference_label';
    $this->checkNewFieldRequired($edit, $add_url, $reference_field_label, $reference_field_name);
    $page->fillField('reference_settings[new_field][label]', $reference_field_label);
    // Save a second time to redirect to clone page.
    $page->checkField('reference_settings[bundles][article]');
    $page->checkField('reference_settings[bundles][page]');
    $page->selectFieldOption('clone_field', 'multiple-field');
    $page->selectFieldOption('update_runner[create_revisions]', UpdateRunnerInterface::REVISIONS_YES);
    //$assert->assertWaitOnAjaxRequest();
    $page->pressButton('Save');
    $this->assertUrl("admin/config/workflow/scheduled-update-type/$type_id/clone-fields");
    $assert->pageTextContains("Created the $label Scheduled Update Type.");
    $assert->pageTextContains("Select fields to add to these updates");
    $this->checkExpectedCheckboxes('base_fields', $this->getNodePropertyMachineNames());
    // @todo test that node.body displays and is select field.

    $this->cloneFields($type_id, $clone_fields);
    $this->assertUrl("admin/config/workflow/scheduled-update-type/$type_id/form-display");
    $assert->pageTextContains('The fields have been created and mapped.');
    $this->assertSession()->pageTextNotContains('Entities to Update');

    $this->checkAfterTypeCreated($label, $type_id, $reference_field_label, $reference_field_name, 'title');

  }

  /**
   * @param array $edit
   * @param array $exclude_fields
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function fillFields($edit, $exclude_fields) {
    $page = $this->getSession()->getPage();
    foreach ($edit as $field_name => $field_value) {
      if (!in_array($field_name, $exclude_fields)) {
        $page->fillField($field_name, $field_value);
        $this->assertSession()->assertWaitOnAjaxRequest();
      }
    }
  }

}

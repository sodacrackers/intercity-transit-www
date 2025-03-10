<?php
/**
 * @file
 * Contains \Drupal\Tests\scheduled_updates\IndependentScheduledUpdateTypeTest.
 */


namespace Drupal\Tests\scheduled_updates\FunctionalJavascript;


use Drupal\Component\Render\FormattableMarkup;
use Drupal\scheduled_updates\Entity\ScheduledUpdateType;
use Drupal\node\NodeInterface;

/**
 * Test adding an Independent Scheduled Update Type.
 *
 * @group scheduled_updates
 */
class IndependentScheduledUpdateTypeTest extends ScheduledUpdatesTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
  }


  public function testCreateMultiTypes() {
    $label = 'New foo type';
    $id = 'foo';
    $clone_fields = [
      'base_fields[title]' => [
        'input_value' => 'title',
        'label' => t('Title'),
      ],
    ];
    $this->createType($label, $id, $clone_fields);
    $this->checkAddForm($id, $label, $clone_fields, TRUE);

    $label = 'New bar type';
    $id = 'promote_updater';
    $clone_fields = [
      'base_fields[promote]' => [
        'input_value' => 'promote',
        'label' => t('Promoted to front page'),
      ],
    ];
    $this->createType($label, $id, $clone_fields);
    $this->checkAddForm($id, $label, $clone_fields, FALSE);

    $this->checkRunningPromoteUpdates($id);

  }

  /**
   * {@inheritdoc}
   */
  protected function createType($label, $id, array $clone_fields, $type_options = []) {
    parent::createType($label, $id, $clone_fields, $type_options);
    $this->assertSession()->pageTextContains('Entities to Update', 'Entities to Update field on Independent Update Type');
    $this->checkEditType($id);
  }

  /**
   * Check that the Scheduled Update add form is correct.
   *
   * @param $type_id
   * @param $label
   * @param $fields
   * @param $only_type
   *
   * @throws \Exception
   */
  protected function checkAddForm($type_id, $label, $fields, $only_type) {
    $this->loginWithPermissions(["create $type_id scheduled updates"]);
    $this->drupalGet('admin/content/scheduled-update/add');
    if ($only_type) {
      // Form shown if only type.
      $this->checkFieldLabels($fields);
    }
    else {
      $this->assertSession()->pageTextContains($label);
      /** @var ScheduledUpdateType[] $types */
      $types = ScheduledUpdateType::loadMultiple();
      // Check that all types are shown on the add page.
      foreach ($types as $type) {
        $this->assertSession()->pageTextContains($type->label());
      }

    }
    //$this->assertSession()->pageTextContains($label);
    $this->drupalGet("admin/content/scheduled-update/add/$type_id");
    $this->assertSession()->pageTextContains(new FormattableMarkup('Create @label Scheduled Update', ['@label' => $label]));
    $this->checkFieldLabels($fields);
    $this->loginLastUser();
  }

  /**
   * Goto Add page for a update type.
   *
   * @param $type_id
   */
  protected function gotoUpdateAdd($type_id) {
    $this->drupalGet("admin/content/scheduled-update/add/$type_id");
  }

  /**
   * Checking adding and running updates.
   *
   * @param $id
   *
   * @throws \Exception
   */
  protected function checkRunningPromoteUpdates($id) {
    $this->loginWithPermissions([
      "create $id scheduled updates",
      "edit own $id scheduled updates",
    ]);
    $page_node = $this->drupalCreateNode(['promote' => NodeInterface::NOT_PROMOTED]);
    $this->gotoUpdateAdd($id);
    $edit = [
      'entity_ids[0][target_id]' => "{$page_node->label()} ({$page_node->id()})",
      'update_timestamp[0][value][date]' => $this->getRelativeDate('-1 day'),
      'update_timestamp[0][value][time]' => '01:00:00',
      'field_promote[value]' => 1,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('cannot be referenced.');

    $article_node = $this->drupalCreateNode(
      [
        'promote' => NodeInterface::NOT_PROMOTED,
        'type' => 'article',
      ]
    );
    $this->assertFalse($article_node->isPromoted(), 'Node is not promoted before update.');
    $edit['entity_ids[0][target_id]'] = "{$article_node->label()} ({$article_node->id()})";
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextNotContains('cannot be referenced.');
    $this->assertTextPattern('/Created the .* Scheduled update/');

    $this->loginLastUser();
    $this->runUpdatesUI();
    // Make sure expect update types were run.
    $this->assertSession()->pageTextContains('Updater New bar type complete. Results: 1 update(s) were performed.');
    $this->assertSession()->pageTextContains('Updater New foo type complete. Results: No updates were performed.');
    $this->checkEntityValue('node', $article_node, 'promote', 1);

  }

}

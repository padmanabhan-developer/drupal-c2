<?php

namespace Drupal\group_extras\Commands;

use Drupal\Component\Utility\Environment;
use Drupal\group_extras\EntityTypeStatisticsInGroup;
use Drupal\group_extras\GidsFieldTool;
use Drush\Commands\DrushCommands;

/**
 * Drush command file.
 */
class GroupExtrasCommands extends DrushCommands {

  /**
   * The gids field tool.
   *
   * @var \Drupal\group_extras\GidsFieldTool
   */
  protected $gidsFieldTool;

  /**
   * The entity type statistics in group.
   *
   * @var \Drupal\group_extras\EntityTypeStatisticsInGroup
   */
  protected $entityTypeStatisticsInGroup;

  /**
   * Constructs GidsFieldTool.
   *
   * @param \Drupal\group_extras\GidsFieldTool $gids_field_tool
   *   The gids field tool.
   * @param \Drupal\group_extras\EntityTypeStatisticsInGroup $entity_type_statistics_in_group
   *   The entity type statistics in group.
   */
  public function __construct(GidsFieldTool $gids_field_tool, EntityTypeStatisticsInGroup $entity_type_statistics_in_group) {
    parent::__construct();
    $this->gidsFieldTool = $gids_field_tool;
    $this->entityTypeStatisticsInGroup = $entity_type_statistics_in_group;
  }

  /**
   * Build or update gid fields definition.
   *
   * @command group_extras:build_gids_field_definitions
   */
  public function updateGidsFieldDefinition() {
    Environment::setTimeLimit(1000);
    $this->gidsFieldTool->updateGidsFieldDefinition();
    $this->output()->writeln('Build gids field storage completely');
  }

  /**
   * Delete gid bundle fields definition.
   *
   * @command group_extras:delete_gids_field_definitions
   */
  public function deleleGidsFieldDefinition() {
    Environment::setTimeLimit(1000);
    $this->gidsFieldTool->deleleGidsFieldDefinition();
    $this->output()->writeln('Delete gids field storage completely');
  }

  /**
   * Update entity's gids field value.
   *
   * @command group_extras:batch_update_gids_value
   */
  public function batchUpdateGidsValue() {
    Environment::setTimeLimit(0);
    $entity_type_ids = $this->entityTypeStatisticsInGroup->getInstalledContentEntityTypeIds();
    foreach ($entity_type_ids as $entity_type_id) {
      printf("Start update gids value for $entity_type_id \n");
      $entity_ids = \Drupal::entityQuery($entity_type_id)
        ->accessCheck(FALSE)
        ->execute();
      $entity_type_storage = \Drupal::entityTypeManager()
        ->getStorage($entity_type_id);
      foreach ($entity_ids as $entity_id) {
        /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
        $entity = $entity_type_storage->load($entity_id);
        update_entity_gids_field_value($entity);
      }
    }

    $this->output()->writeln('Batch update gids value completely');
  }

}

<?php

namespace Drupal\group_extras;

use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;

/**
 * Class GidsFieldTool.
 */
class GidsFieldTool {

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $updateManager;

  /**
   * The entity type statistics in group.
   *
   * @var \Drupal\group_extras\EntityTypeStatisticsInGroup
   */
  protected $entityTypeStatisticsInGroup;

  /**
   * Constructs GidsFieldTool.
   *
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $update_manager
   *   The entity definition update manager.
   * @param \Drupal\group_extras\EntityTypeStatisticsInGroup $entity_type_statistics_in_group
   *   The entity type statistics in group.
   */
  public function __construct(EntityDefinitionUpdateManagerInterface $update_manager, EntityTypeStatisticsInGroup $entity_type_statistics_in_group) {
    $this->updateManager = $update_manager;
    $this->entityTypeStatisticsInGroup = $entity_type_statistics_in_group;
  }

  /**
   * Update gids field definition.
   */
  public function updateGidsFieldDefinition() {
    drupal_flush_all_caches();

    $entity_types = $this->entityTypeStatisticsInGroup->getContentEntityTypes();

    foreach ($entity_types as $entity_type_id => $entity_type) {
      $new_field_definitions = group_extras_entity_base_field_info($entity_type);
      $newest_field_definition = reset($new_field_definitions);

      $field_storage_definition = $this->updateManager->getFieldStorageDefinition('gids', $entity_type_id);

      if (empty($newest_field_definition) && empty($field_storage_definition)) {
        continue;
      }
      if (!empty($newest_field_definition) && empty($field_storage_definition)) {
        $this->updateManager->installFieldStorageDefinition('gids', $entity_type_id, $entity_type_id, $newest_field_definition);
        printf("Install gids field storage definition on $entity_type_id \n");
        continue;
      }
      if (empty($newest_field_definition) && !empty($field_storage_definition)) {
        $this->updateManager->uninstallFieldStorageDefinition($field_storage_definition);
        printf("Uninstall gids field storage definition on $entity_type_id \n");
        continue;
      }
    }
  }

  /**
   * Delete all gids field.
   */
  public function deleleGidsFieldDefinition() {
    $entity_types = $this->entityTypeStatisticsInGroup->getContentEntityTypes();

    foreach ($entity_types as $entity_type_id => $entity_type) {
      if ($field_storage_definition = $this->updateManager->getFieldStorageDefinition('gids', $entity_type_id)) {
        $this->updateManager->uninstallFieldStorageDefinition($field_storage_definition);
        printf("Uninstall gids field storage definition on $entity_type_id \n");
      }
    }

  }

}

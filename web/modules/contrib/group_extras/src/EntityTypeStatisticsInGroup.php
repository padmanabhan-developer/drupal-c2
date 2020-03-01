<?php

namespace Drupal\group_extras;

use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Plugin\GroupContentEnablerManagerInterface;

/**
 * Class EntityTypeStatisticsInGroup.
 */
class EntityTypeStatisticsInGroup {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The plugin.manager.group_content_enabler service.
   *
   * @var \Drupal\group\Plugin\GroupContentEnablerManagerInterface
   */
  protected $groupContentEnablerManager;

  /**
   * Constructs GidsFieldTool.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\group\Plugin\GroupContentEnablerManagerInterface $group_content_enabler_manager
   *   The entity definition update manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, GroupContentEnablerManagerInterface $group_content_enabler_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->groupContentEnablerManager = $group_content_enabler_manager;
  }

  /**
   * Get the content entity types which has group content enabler.
   */
  public function getContentEntityTypes() {
    $plugins = $this->groupContentEnablerManager->getAll();
    /** @var \Drupal\group\Plugin\GroupContentEnablerBase $plugin */
    $entity_types = [];
    foreach ($plugins as $plugin) {
      $entity_type_id = $plugin->getEntityTypeId();

      // Ignore config entity type.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($entity_type instanceof ConfigEntityType) {
        continue;
      }

      $entity_types[$entity_type_id] = $entity_type;
    }
    return $entity_types;
  }

  /**
   * Get the content entity types which has group content enabler.
   */
  public function getInstalledContentEntityTypes() {
    $plugins = $this->groupContentEnablerManager->getInstalled();
    /** @var \Drupal\group\Plugin\GroupContentEnablerBase $plugin */
    $entity_types = [];
    foreach ($plugins as $plugin) {
      $entity_type_id = $plugin->getEntityTypeId();

      // Ignore config entity type.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($entity_type instanceof ConfigEntityType) {
        continue;
      }

      $entity_types[$entity_type_id] = $entity_type;
    }
    return $entity_types;
  }

  /**
   * Get the content entity type ids which has group content enabler.
   */
  public function getInstalledContentEntityTypeIds() {
    $plugins = $this->groupContentEnablerManager->getInstalled();
    /** @var \Drupal\group\Plugin\GroupContentEnablerBase $plugin */
    $entity_type_ids = [];
    foreach ($plugins as $plugin) {
      $entity_type_id = $plugin->getEntityTypeId();

      // Ignore config entity type.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($entity_type instanceof ConfigEntityType) {
        continue;
      }

      $entity_type_ids[] = $entity_type_id;
    }
    return $entity_type_ids;
  }

}

<?php

namespace Drupal\default_content_deploy\Commands;

use Drush\Commands\DrushCommands;

/**
 *
 * In addition to a commandfile like this one, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class DefaultContentDeployCommands extends DrushCommands {

  /**
   * Exports a single entity or group of entities.
   *
   * @command default-content-deploy:export
   *
   * @param string $EntityType The entity type to export. If a wrong content
   *   entity type is entered, module displays a list of all content entity
   *   types.
   * @param array $options An associative array of options whose values come
   *   from cli, aliases, config, etc.
   *
   * @option entity_id The ID of the entity to export.
   * @option bundle Write out the exported bundle of entity
   * @option skip_entities The ID of the entity to skip.
   * @usage drush dcde node
   *   Export all nodes
   * @usage drush dcde node --bundle=page
   *   Export all nodes with bundle page
   * @usage drush dcde node --bundle=page,article --entity_id=2,3,4
   *   Export all nodes with bundle page or article plus nodes with entities id
   *   2, 3 and 4.
   * @usage drush dcde node --bundle=page,article --skip_entities=5,7
   *   Export all nodes with bundle page or article and skip nodes with entity
   *   id 5 and 7.
   * @usage drush dcde node --skip_entities=5,7
   *   Export all nodes and skip nodes with entity id 5 and 7.
   * @validate-module-enabled default_content
   * @aliases dcde,default-content-deploy-export
   */
  public function contentDeployExport($EntityType,
                                      array $options = [
                                        'entity_id' => NULL,
                                        'bundle' => NULL,
                                        'skip_entities' => NULL,
                                      ]) {
    $entity_ids = $options['entity_id'];
    $entity_bundles = $options['bundle'];
    $skip_entities = $options['skip_entities'];

    /** @var \Drupal\default_content_deploy\Exporter $exporter */
    $exporter = \Drupal::service('default_content_deploy.exporter');
    $count = $exporter->export($EntityType, $entity_bundles, $entity_ids, $skip_entities);

    $this->logger->notice(dt('Exported @count entities.', ['@count' => $count]));
  }

  /**
   * Exports a single entity with references.
   *
   * @command default-content-deploy:export-with-references
   *
   * @param string $EntityType
   *   The entity type to export. If a wrong content entity
   *   type is entered, module displays a list of all content entity types.
   * @param array $options
   *   An associative array of options whose values come
   *   from cli, aliases, config, etc.
   *
   * @option entity_id The ID of the entity to export.
   * @option bundle Write out the exported bundle of entity
   * @option skip_entities The ID of the entity to skip.
   * @usage drush dcde node
   *   Export all nodes with references
   * @usage drush dcde node --bundle=page
   *   Export all nodes with references with bundle page
   * @usage drush dcde node --bundle=page,article --entity_id=2,3,4
   *   Export all nodes with references with bundle page or article plus nodes
   *   with entitiy id 2, 3 and 4.
   * @usage drush dcde node --bundle=page,article --skip_entities=5,7
   *   Export all nodes with references with bundle page or article and skip
   *   nodes with entity id 5 and 7.
   * @usage drush dcde node --skip_entities=5,7
   *   Export all nodes and skip nodes with references with entity id 5 and 7.
   * @validate-module-enabled default_content
   * @aliases dcder,default-content-deploy-export-with-references
   */
  public function contentDeployExportWithReferences($EntityType,
                                                    array $options = [
                                                      'entity_id' => NULL,
                                                      'bundle' => NULL,
                                                      'skip_entities' => NULL,
                                                    ]) {
    $entity_ids = $options['entity_id'];
    $entity_bundles = $options['bundle'];
    $skip_entities = $options['skip_entities'];

    /** @var \Drupal\default_content_deploy\Exporter $exporter */
    $exporter = \Drupal::service('default_content_deploy.exporter');
    $count = $exporter->exportWithReferences($EntityType, $entity_bundles, $entity_ids, $skip_entities);
    $this->logger->notice(dt('Exported @count entities with references.', ['@count' => $count]));
  }

  /**
   * Exports a whole site content.
   *
   * @command default-content-deploy:export-site
   *
   * @param array $options An associative array of options whose values come
   *   from cli, aliases, config, etc.
   *
   * @option add_entity_type The entity types to add.
   * @option skip_entity_type The entity types to skip.
   * @usage drush dcdes
   *   Export complete website.
   * @usage drush dcdes --add_entity_type=collection,my_custom_entity
   *   Export complete website plus custom entities collection and
   *   my_custom_entity.
   * @usage drush dcdes --skip_entity_type=node,user
   *   Export complete website but skip nodes and users.
   * @validate-module-enabled default_content
   * @aliases dcdes,default-content-deploy-export-site
   */
  public function contentDeployExportSite(array $options = [
    'add_entity_type' => NULL,
    'skip_entity_type' => NULL,
  ]) {
    $add_entity_type = $options['add_entity_type'];
    $skip_entity_type = $options['skip_entity_type'];

    /** @var \Drupal\default_content_deploy\Exporter $exporter */
    $exporter = \Drupal::service('default_content_deploy.exporter');
    $count = $exporter->exportSite($add_entity_type, $skip_entity_type);

    foreach ($count as $entity => $value) {
      $this->logger->notice(dt('Exported @count entities of type @entity.', [
        '@count' => $value,
        '@entity' => $entity,
      ]));
    }
  }

  /**
   * Exports site url aliases.
   *
   * @command default-content-deploy:export-aliases
   *
   * @usage drush dcdea
   *   Export url aliases.
   * @validate-module-enabled default_content
   * @aliases dcdea,default-content-deploy-export-aliases
   */
  public function contentDeployExportAliases() {
    /** @var \Drupal\default_content_deploy\Exporter $exporter */
    $exporter = \Drupal::service('default_content_deploy.exporter');
    $aliases = $exporter->exportUrlAliases();

    $this->logger->notice(dt('Exported @count aliases.', ['@count' => $aliases]));
  }

  /**
   * Import all the content defined in a module.
   *
   * @command default-content-deploy:import
   *
   * @param array $options An associative array of options whose values come
   *   from cli, aliases, config, etc.
   *
   * @option force-update Content with different UUID but same ID will be
   *   updated (UUID will be replaced).
   * @usage drush dcdi
   *   Import content. Existing older content with matching UUID will be
   *   updated. Newer content and existing content with different UUID will be
   *   ignored.
   * @usage drush dcdi --force-update
   *   Import content but existing content with different UUID will be replaced
   *   (recommended for better content synchronization).
   * @usage drush dcdi --verbose
   *   Print detailed information about importing entities.
   * @validate-module-enabled default_content
   * @aliases dcdi,default-content-deploy-import
   */
  public function contentDeployImport(array $options = ['force-update' => NULL]) {
    // See bottom of https://weitzman.github.io/blog/port-to-drush9 for details on what to change when porting a
    // legacy command.
    $force_update = $options['force-update'];
    /** @var \Drupal\default_content_deploy\Importer $importer */
    $importer = \Drupal::service('default_content_deploy.importer');

    // Perform read only update.
    $result_info = $importer->deployContent($force_update, FALSE);
    $this->output()
      ->writeln(dt('@count entities will be processed.', ['@count' => $result_info['processed']]));
    $this->displayImportResult($result_info);
    $entities_todo = $result_info['created'] + $result_info['updated'] + $result_info['file_created'];
    if ($entities_todo == 0) {
      $this->output()->writeln(dt('Nothing to do.'));
      return;
    }
    if ($this->io()->confirm(dt('Do you really want to continue?'))) {
      // Perform update.
      $result_info = $importer->deployContent($force_update, TRUE);
      $import_status = $importer->importUrlAliases();

      // Display results.
      $this->logger()
        ->notice(dt('@count entities have been processed.', ['@count' => $result_info['processed']]));
      $this->displayImportResult($result_info);

      $this->logger()
        ->notice(dt('Imported @count aliases.', ['@count' => $import_status['imported']]));
      $this->logger()
        ->notice(dt('Skipped @skipped aliases.', ['@skipped' => $import_status['skipped']]));
    }
  }

  /**
   * Import site url aliases.
   *
   * @command default-content-deploy:import-aliases
   *
   * @usage drush dcdia
   *   Import url aliases.
   * @validate-module-enabled default_content
   * @aliases dcdia,default-content-deploy-import-aliases
   */
  public function contentDeployImportAliases() {
    /** @var \Drupal\default_content_deploy\Importer $importer */
    $importer = \Drupal::service('default_content_deploy.importer');
    $import_status = $importer->importUrlAliases();

    $this->logger()
      ->notice(dt('Imported @count aliases.', ['@count' => $import_status['imported']]));
    $this->logger()
      ->notice(dt('Skipped @skipped aliases.', ['@skipped' => $import_status['skipped']]));
  }

  /**
   * Get current System Site, Admin and Anonymous UUIDs, Admin name.
   *
   * @command default-content-deploy:uuid-info
   * @usage drush dcd-uuid-info
   *   Displays the current UUID values.
   * @validate-module-enabled default_content
   * @aliases dcd-uuid-info,default-content-deploy-uuid-info
   */
  public function contentDeployUuidInfo() {
    // See bottom of https://weitzman.github.io/blog/port-to-drush9 for details on what to change when porting a
    // legacy command.
    $dcd = \Drupal::service('default_content_deploy.base');
    $import_status = $dcd->uuidInfo();

    // Display current values.
    $this->output()
      ->writeln(dt('System.site UUID = @uuid', ['@uuid' => $import_status['current_site_uuid']]));
    $this->output()
      ->writeln(dt('Anonymous user UUID = @uuid', ['@uuid' => $import_status['current_uuid_anonymous']]));
    $this->output()
      ->writeln(dt('Admin UUID = @uuid', ['@uuid' => $import_status['current_uuid_admin']]));
    $this->output()
      ->writeln(dt('Admin\'s name = @name', ['@name' => $import_status['current_admin_name']]));
  }

  /**
   * @param $result_info
   */
  private function displayImportResult($result_info) {
    $this->output()
      ->writeln(dt('- created: @count', ['@count' => $result_info['created']]));
    $this->output()
      ->writeln(dt('- updated: @count', ['@count' => $result_info['updated']]));
    $this->output()
      ->writeln(dt('- skipped: @count', ['@count' => $result_info['skipped']]));
    $this->output()
      ->writeln(dt('Missing files created: @count', ['@count' => $result_info['file_created']]));
  }

}
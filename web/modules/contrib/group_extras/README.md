# Group Extras

This is a extension module of [group](http://drupal.org/project/group)

## Function
Add a gids field on each entity type which been enabled in group.
Auto update the above gids filed value when a group content been inserted or deleted.

## Background:  
Get entity's group is easy in drupal system, but in decoupled system.  
1. Frontend is hard to get it's groups when get an entity.  
1. Filter or sort entity by groups through JSON:API is hard and has performance problem.    

To solve decoupled DX, this module add back reference a group filed on each entity type which been enabled in group.

## How to use ?  
1. Once install the module the gids field will be auto created.  
1. If you enable/disable other group content type after install this module, you can use 
`drush group_extras:build_gids_field_definitions` to build/update the gids field.
1. If the system has some entities before install gids field through this module, you can use
`drush group_extras:batch_update_gids_value` to batch update the gids value.  
1. If you want to completely remove gids field, you can use
`drush group_extras:delete_gids_field_definitions`

<?php

/**
 * Implements hook_update_N().
 */
function it_route_trip_tools_deploy_create_calendar(&$sandbox) {
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
    'name' => 'Classes',
    'uuid' => '779b1069-3ce9-4f42-ac8a-2eb625d61e4e',
    'vid' => 'calendar_type',
  ])->save();
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
    'name' => 'Bike donation',
    'uuid' => '84629195-66ec-4b29-aa1b-a5a39d943bd9',
    'vid' => 'calendar_type',
  ])->save();
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
    'name' => 'Community Bike Shop',
    'uuid' => 'a584ee48-8cf6-4f29-802e-5b724da72598',
    'vid' => 'calendar_type',
  ])->save();
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
    'name' => 'Events',
    'uuid' => '0e3033a3-050d-4184-94b7-a4ee7cd5ee90',
    'vid' => 'calendar_type',
  ])->save();
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
    'name' => 'Volunteer Orientations',
    'uuid' => 'db913e0d-8bef-478b-8bf4-a715e753fe53',
    'vid' => 'calendar_type',
  ])->save();
  $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->create([
    'name' => 'Volunteer shop',
    'uuid' => '17f80840-f153-4b5a-af92-77389b7abc0e',
    'vid' => 'calendar_type',
  ])->save();
}
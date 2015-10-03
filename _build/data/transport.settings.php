<?php

$settings = array();

$settings['elementhelper.chunk_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.chunk_path']->fromArray(array(
    'key' => 'elementhelper.chunk_path',
    'value' => 'site/elements/chunks/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'paths'
), '', true, true);

$settings['elementhelper.template_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.template_path']->fromArray(array(
    'key' => 'elementhelper.template_path',
    'value' => 'site/elements/templates/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'paths'
), '', true, true);

$settings['elementhelper.plugin_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.plugin_path']->fromArray(array(
    'key' => 'elementhelper.plugin_path',
    'value' => 'site/elements/plugins/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'paths'
), '', true, true);

$settings['elementhelper.snippet_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.snippet_path']->fromArray(array(
    'key' => 'elementhelper.snippet_path',
    'value' => 'site/elements/snippets/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'paths'
), '', true, true);

$settings['elementhelper.tv_file_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.tv_file_path']->fromArray(array(
    'key' => 'elementhelper.tv_file_path',
    'value' => 'site/elements/template_variables.json',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'paths'
), '', true, true);

$settings['elementhelper.element_sync_file_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.element_sync_file_path']->fromArray(array(
    'key' => 'elementhelper.element_sync_file_path',
    'value' => 'site/elements/element_sync.json',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'paths'
), '', true, true);

$settings['elementhelper.usergroups'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.usergroups']->fromArray(array(
    'key' => 'elementhelper.usergroups',
    'value' => 'Administrator',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'config'
), '', true, true);

$settings['elementhelper.tv_access_control'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.tv_access_control']->fromArray(array(
    'key' => 'elementhelper.tv_access_control',
    'value' => 0,
    'xtype' => 'combo-boolean',
    'namespace' => 'elementhelper',
    'area' => 'config'
), '', true, true);

$settings['elementhelper.category_whitelist'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.category_whitelist']->fromArray(array(
    'key' => 'elementhelper.category_whitelist',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'config'
), '', true, true);

$settings['elementhelper.element_blacklist'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.element_blacklist']->fromArray(array(
    'key' => 'elementhelper.element_blacklist',
    'value' => 'TinyMCE, getResources, ClientConfig',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'config'
), '', true, true);

return $settings;
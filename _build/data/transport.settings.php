<?php

$settings = array();

$settings['elementhelper.chunk_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.chunk_path']->fromArray(array(
    'key' => 'elementhelper.chunk_path',
    'value' => 'core/elements/chunks/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'default'
), '', true, true);

$settings['elementhelper.template_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.template_path']->fromArray(array(
    'key' => 'elementhelper.template_path',
    'value' => 'core/elements/templates/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'default'
), '', true, true);

$settings['elementhelper.plugin_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.plugin_path']->fromArray(array(
    'key' => 'elementhelper.plugin_path',
    'value' => 'core/elements/plugins/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'default'
), '', true, true);

$settings['elementhelper.snippet_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.snippet_path']->fromArray(array(
    'key' => 'elementhelper.snippet_path',
    'value' => 'core/elements/snippets/',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'default'
), '', true, true);

$settings['elementhelper.tv_json_path'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.tv_json_path']->fromArray(array(
    'key' => 'elementhelper.tv_json_path',
    'value' => 'core/elements/template_variables.json',
    'xtype' => 'textfield',
    'namespace' => 'elementhelper',
    'area' => 'default'
), '', true, true);

$settings['elementhelper.tv_access_control'] = $modx->newObject('modSystemSetting');
$settings['elementhelper.tv_access_control']->fromArray(array(
    'key' => 'elementhelper.tv_access_control',
    'value' => 0,
    'xtype' => 'combo-boolean',
    'namespace' => 'elementhelper',
    'area' => 'default'
), '', true, true);

return $settings;
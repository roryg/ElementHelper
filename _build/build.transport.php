<?php

$tstart = explode(' ', microtime());
$tstart = $tstart[1] + $tstart[0];

set_time_limit(0);

define('PKG_NAME', 'ElementHelper');
define('PKG_NAME_LOWER', 'elementhelper');
define('PKG_VERSION', '1.2.1');

$root = dirname(dirname(__FILE__)) . '/';

$sources = array(
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'lexicon' => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
    'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'elements' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/',
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
);

unset($root);

require_once $sources['build'] . 'build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
 
$modx = new modX();
$modx->initialize('mgr');

echo '<pre>';

$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->loadClass('transport.modPackageBuilder', '', false, true);

$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');

// Setup the package category
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

$plugins = include $sources['data'] . 'transport.plugins.php';
$settings = include $sources['data'] . 'transport.settings.php';

// Package in the plugin if available
if (empty($plugins))
{
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in plugins.');
}
else
{
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaging in plugins...');

    $category->addMany($plugins);

    $attributes = array(
        xPDOTransport::UNIQUE_KEY => 'category',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
            'Plugins' => array(
                xPDOTransport::PRESERVE_KEYS => false,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'name',
                xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
                    'PluginEvents' => array(
                        xPDOTransport::PRESERVE_KEYS => true,
                        xPDOTransport::UPDATE_OBJECT => false,
                        xPDOTransport::UNIQUE_KEY => array('pluginid', 'event')
                    )
                )
            )
        )
    );

    $vehicle = $builder->createVehicle($category, $attributes);

    $modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to category...');

    $vehicle->resolve('file', array(
        'source' => $sources['source_core'],
        'target' => "return MODX_CORE_PATH . 'components/';",
    ));

    $builder->putVehicle($vehicle);
}

// Package in the settings if available
if (empty($settings))
{
    $modx->log(modX::LOG_LEVEL_ERROR,'Could not package in settings.');
}
else
{
    $modx->log(modX::LOG_LEVEL_INFO,'Packaging in settings...');

    $attributes= array(
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    );

    foreach ($settings as $setting)
    {
        $vehicle = $builder->createVehicle($setting, $attributes);
        $builder->putVehicle($vehicle);
    }
}

$modx->log(modX::LOG_LEVEL_INFO,'Adding package attributes and setup options...');
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');

$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt')
));

$builder->pack();
 
$tend= explode(" ", microtime());
$tend= $tend[1] + $tend[0];
$totalTime= sprintf("%2.4f s", ($tend - $tstart));
$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit ();
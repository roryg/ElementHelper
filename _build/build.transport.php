<?php

$tstart = explode(' ', microtime());
$tstart = $tstart[1] + $tstart[0];

set_time_limit(0);

require_once dirname(__FILE__) . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

define('PKG_NAME', 'ElementHelper');
define('PKG_NAME_LOWER', 'elementhelper');
define('PKG_VERSION', '2.0.0');
define('PKG_RELEASE', 'rc');

$root = dirname(dirname(__FILE__)) . '/';

$sources = array(
    'root' => $root,
    'build' => $root . '_build/',
    'data' => $root . '_build/data/',
    'lexicon' => $root . PKG_NAME_LOWER . '/lexicon/',
    'docs' => $root . PKG_NAME_LOWER . '/docs/',
    'elements' => $root . PKG_NAME_LOWER . '/elements/',
    'source_core' => $root . PKG_NAME_LOWER
);

$modx = new modX();
$modx->initialize('mgr');

echo '<pre>';

$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$modx->loadClass('transport.modPackageBuilder', '', false, true);

$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(
    PKG_NAME_LOWER, 
    false, 
    true, 
    '{core_path}components/' . PKG_NAME_LOWER . '/'
);

// Setup the package category
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

$plugins = include $sources['data'] . 'transport.plugins.php';
$settings = include $sources['data'] . 'transport.settings.php';

//////////////////////////////////////////////////
//
// Package in the plugins
//
//////////////////////////////////////////////////

$modx->log(modX::LOG_LEVEL_INFO, 'Packaging in plugins...');

$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => false,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'PluginEvents' => array(
            xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false
        )
    )
);

foreach ($plugins as $plugin)
{
    $vehicle = $builder->createVehicle($plugin, $attributes);
    $builder->putVehicle($vehicle);
}

$category->addMany($plugins);

$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in plugins.'); flush();

//////////////////////////////////////////////////
//
// Package in the settings
//
//////////////////////////////////////////////////

// Package in the settings
$modx->log(modX::LOG_LEVEL_INFO, 'Packaging in settings...');

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

$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in settings.'); flush();

//////////////////////////////////////////////////
//
// Package in the category
//
//////////////////////////////////////////////////

$modx->log(modX::LOG_LEVEL_INFO, 'Packaging in category...');

$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Children' => array(
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'Plugins' => array(
                    xPDOTransport::UNIQUE_KEY => 'name',
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true
                )
            )
        ),
        'Plugins' => array(
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true
        )
    )
);

$vehicle = $builder->createVehicle($category, $attributes);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to category...');

$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));

$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in resolvers.'); flush();

$builder->putVehicle($vehicle);

//////////////////////////////////////////////////
//
// Add the package attributes
//
//////////////////////////////////////////////////

$modx->log(modX::LOG_LEVEL_INFO,'Adding package attributes...');

$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt')
));

$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...'); flush();

$builder->pack();
 
$tend= explode(" ", microtime());
$tend= $tend[1] + $tend[0];
$totalTime= sprintf("%2.4f s", ($tend - $tstart));
$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit ();
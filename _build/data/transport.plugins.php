<?php

$plugins = array();

$plugins[1] = $modx->newObject('modPlugin');

$plugins[1]->set('id', 1);
$plugins[1]->set('name', 'element_helper');
$plugins[1]->set('description', 'Creates elements automatically from static files.');

$plugins[1]->setContent(file_get_contents($sources['elements'] . 'plugins/plugin.elementhelper.php'));

$events = include $sources['data'] . 'transport.pluginevents.php';

$plugins[1]->addMany($events);
 
return $plugins;
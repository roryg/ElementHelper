<?php

$events = array();

$events[1] = $modx->newObject('modPluginEvent');
$events[1]->set('event', 'OnWebPageInit');
$events[1]->set('priority', 0);
$events[1]->set('propertyset', 0);

$events[2] = $modx->newObject('modPluginEvent');
$events[2]->set('event', 'OnManagerPageInit');
$events[2]->set('priority', 0);
$events[2]->set('propertyset', 0);

return $events;
<?php
$default_core_path = $modx->getOption('core_path') . 'components/elementhelper/';
$core_path = $modx->getOption('elementhelper.core_path', null, $default_core_path);

$usergroups = explode(',', $modx->getOption('elementhelper.usergroups', null, 'Administrator'));

// Return if the user isn't part of one of the allowed usergroups 
if ( ! $modx->user->isMember($usergroups))
{
    return;
}

// Load in our classes
$modx->loadClass('FileHelper', $core_path . 'model/', true, true);
$modx->loadClass('Element', $core_path . 'model/', true, true);
$modx->loadClass('ElementHelper', $core_path . 'model/', true, true);
$modx->loadClass('ElementSync', $core_path . 'model/', true, true);

// Path to the element sync json file
$element_sync_file = MODX_BASE_PATH . $modx->getOption('elementhelper.element_sync_file_path', null, 'site/elements/element_sync.json');

// Initialize the classes
$element_helper = new ElementHelper($modx);
$element_sync = new ElementSync($modx, $element_sync_file);

$element_types = array(
    'modTemplate' => $modx->getOption('elementhelper.template_path', null, 'site/elements/templates/'),
    'modChunk' => $modx->getOption('elementhelper.chunk_path', null, 'site/elements/chunks/'),
    'modSnippet' => $modx->getOption('elementhelper.snippet_path', null, 'site/elements/snippets/'),
    'modPlugin' => $modx->getOption('elementhelper.plugin_path', null, 'site/elements/plugins/')
);

// Loop through the element types
foreach ($element_types as $type => $type_path)
{
    $log_prefix = sprintf('[ElementHelper] %s: ', $type);
    $file_list = FileHelper::get_directory_file_list(MODX_BASE_PATH . $type_path);

    // Move onto the next element type if it has no files
    if (empty($file_list))
    {
        $modx->log(MODX_LOG_LEVEL_INFO, $log_prefix . 'No files.');

        continue;
    }

    // Process the files for this element type
    foreach ($file_list as $file_path)
    {
        $file = FileHelper::get_file_meta($file_path);
        $element = Element::get($modx, $type, $file['name']);

        // If the file is not in the sync
        if ( ! $element_sync->has_element($type, $file['name']))
        {
            // If the element doesn't exist
            if ( ! $element)
            {
                // Create the element
                $element = Element::create($modx, $type, $file['name']);

                // If the element is created successfully set it's properties and then add it to the sync
                if ($element)
                {
                    $properties = $element_helper->get_file_element_properties($type_path, $file_path);

                    if ($element->set_properties($properties))
                    {
                        $element_sync->add_element($type, $file['name'], $file['mod_time']);
                    }
                }
            }
            else
            {
                $modx->log(MODX_LOG_LEVEL_INFO, $log_prefix . 'An element with the name [' . $file['name'] . '] already exists. Unable to sync the file and element.');
            }
        }
        else
        {
            // If the element doesn't exist
            if ( ! $element)
            {
                // Delete the file and remove it from the sync if successful
                if (unlink($file_path))
                {
                    $element_sync->remove_element($type, $file['name']);
                }
            }
            else
            {
                // If file has been updated, update the element and sync
                if ($file['mod_time'] > $element_sync->get_element_mod_time($type, $file['name']))
                {
                    $properties = $element_helper->get_file_element_properties($type_path, $file_path);

                    if ($element->set_properties($properties))
                    {
                        $element_sync->add_element($type, $file['name'], $file['mod_time']);
                    }
                }
            }
        }
    }

    // Process the elements for this element type
    foreach ($modx->getCollection($type) as $element_object)
    {
        $element = Element::insert($element_object);
        $name = $element->get_property('name');
        $category = $element->get_property('category');
        $file_path = $element_helper->build_element_file_path($type, $type_path, $name, $category);

        // If a file with this element name doesn't exist
        if ( ! file_exists($file_path))
        {
            // If the element is not in the sync
            if ( ! $element_sync->has_element($type, $name))    
            {
                $properties = $element_helper->get_element_static_file_properties($element, $file_path);

                if ($element->set_properties($properties))
                {
                    $file_mod_time = filemtime($file_path);
                    $element_sync->add_element($type, $name, $file_mod_time);
                }
            }
            else
            {
                // Remove the element and remove it from the sync if successful
                if ($element->remove())
                {
                    $element_sync->remove_element($type, $name);
                }
            }
        }
    }
}

$log_prefix = '[ElementHelper] modTemplateVar: ';
$tv_file_path = MODX_BASE_PATH . $modx->getOption('elementhelper.tv_file_path', null, 'site/elements/template_variables.json');

if (file_exists($tv_file_path))
{
    $tv_file_contents = file_get_contents($tv_file_path);
    $tv_file_mod_time = filemtime($tv_file_path);
    $tvs = ($tv_file_contents !== '' ? json_decode($tv_file_contents) : array());
    $flagged_tvs = array();

    // Loop through the template variables in the file
    foreach ($tvs as $i => $tv)
    {
        $element = Element::get($modx, 'modTemplateVar', $tv->name);

        // If the element is not in the sync
        if ( ! $element_sync->has_element('modTemplateVar', $tv->name))
        {
            // If the tv doesn't exist
            if ( ! $element)
            {
                // Create the element
                $element = Element::create($modx, 'modTemplateVar', $tv->name);

                // If the element is created successfully 
                if ($element)
                {
                    $properties = $element_helper->get_tv_element_properties($tv);

                    // If templates have been specified and permission to pair tvs with templates has been given
                    if (isset($tv->template_access) && $modx->getOption('elementhelper.tv_access_control', null, false) == true)
                    {
                        $element_helper->setup_tv_template_access($element->get_property('id'), $tv->template_access);
                    }

                    // Set the tv properties and then add it to the sync
                    if ($element->set_properties($properties))
                    {
                        $element_sync->add_element('modTemplateVar', $tv->name, $tv_file_mod_time);
                    }
                }
            }
            else
            {
                $modx->log(MODX_LOG_LEVEL_INFO, $log_prefix . 'An element with the name [' . $tv->name . '] already exists. Unable to sync the element.');
            }
        }
        else
        {
            // If the tv doesn't exist
            if ( ! $element)
            {
                // Flag the tv for removal after we've checked all tvs in the file
                $flagged_tvs[] = $i;
            }
            else
            {
                // If the template variable file has been updated update the tv element and sync
                if ($tv_file_mod_time > $element_sync->get_element_mod_time('modTemplateVar', $tv->name))
                {
                    $properties = $element_helper->get_tv_element_properties($tv);

                    // If templates have been specified and permission to pair tvs with templates has been given
                    if (isset($tv->template_access) && $modx->getOption('elementhelper.tv_access_control', null, false) == true)
                    {
                        $element_helper->setup_tv_template_access($element->get_property('id'), $tv->template_access);
                    }

                    // Set the tv properties and then add it to the sync
                    if ($element->set_properties($properties))
                    {
                        $element_sync->add_element('modTemplateVar', $tv->name, $tv_file_mod_time);
                    }
                }
            }
        }
    }

    // Remove any flagged tvs
    if (count($flagged_tvs) > 0)
    {
        $updated_tvs = $tvs;

        foreach ($flagged_tvs as $i)
        {
            unset($updated_tvs[$i]);
        }

        // Update the template variable file and remove the tvs from the sync if successfull
        if ($element_helper->update_tv_file($updated_tvs))
        {
            foreach ($flagged_tvs as $i)
            {
                $element_sync->remove_element('modTemplateVar', $tvs[$i]->name);
            }
        }
    }

    // Process the template variable elements
    foreach ($modx->getCollection('modTemplateVar') as $element_object)
    {
        // Check if the tv exists in the template variables file
        $element = Element::insert($element_object);
        $name = $element->get_property('name');
        $tv_file_has_tv = false;

        // Loop through the tvs to check if it exists in the template variables file
        foreach ($tvs as $i => $tv)
        {
            if ($tv->name === $name)
            {
                $tv_file_has_tv = true;
            }
        }

        // If the tv doesn't exist in the template variables json file
        if ($tv_file_has_tv === false)
        {
            // If the element is not in the sync
            if ( ! $element_sync->has_element('modTemplateVar', $name))
            {
                // Collect the tv element properties
                $new_tv= array(array(
                    'name' => $name,
                    'caption' => $element->get_property('caption'),
                    'type' => $element->get_property('type'),
                    'description' => $element->get_property('description'),
                    'category' => ($element->get_property('category') !== 0 ? $element->get_property('category') : null),
                    'locked' => $element->get_property('locked'),
                    'elements' => $element->get_property('elements'),
                    'rank' => $element->get_property('rank'),
                    'display' => $element->get_property('display'),
                    'default_text' => $element->get_property('default_text'),
                    'properties' => $element->get_property('properties'),
                    'input_properties' => $element->get_property('input_properties'),
                    'output_properties' => $element->get_property('output_properties')
                ));

                // Fix migx json properties
                if (isset($new_tv[0]['input_properties']['formtabs']))
                {
                    $new_tv[0]['input_properties']['formtabs'] = json_decode($new_tv[0]['input_properties']['formtabs']);
                    $new_tv[0]['input_properties']['columns'] = json_decode($new_tv[0]['input_properties']['columns']);
                }

                $updated_tvs = array_merge($tvs, $new_tv);

                // Update the template variables file and add the tv to the sync
                if ($element_helper->update_tv_file($updated_tvs))
                {
                    $tv_file_mod_time = filemtime($tv_file_path);
                    $element_sync->add_element('modTemplateVar', $name, $tv_file_mod_time);
                }
            }
            else
            {
                // Remove the element and remove it from the sync if successful
                if ($element->remove())
                {
                    $element_sync->remove_element('modTemplateVar', $name);
                }
            }
        }
    }
}
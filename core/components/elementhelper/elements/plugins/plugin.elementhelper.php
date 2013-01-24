<?php
$default_element_helper_core_path = $modx->getOption('core_path') . 'components/elementhelper/';
$element_helper_core_path = $modx->getOption('elementhelper.core_path', null, $default_element_helper_core_path);

$element_helper = $modx->getService('elementhelper', 'ElementHelper', $element_helper_core_path . 'model/elementhelper/');

$element_types = array(
    'templates' => array(
        'class_name' => 'modTemplate',
        'path' => $modx->getOption('elementhelper.template_path', null, 'core/elements/templates/')
    ),
    
    'chunks' => array(
        'class_name' => 'modChunk',
        'path' => $modx->getOption('elementhelper.chunk_path', null, 'core/elements/chunks/')
    ),

    'snippets' => array(
        'class_name' => 'modSnippet',
        'path' => $modx->getOption('elementhelper.snippet_path', null, 'core/elements/snippets/')
    ),

    'plugins' => array(
        'class_name' => 'modPlugin',
        'path' => $modx->getOption('elementhelper.plugin_path', null, 'core/elements/plugins/')
    )
);

$element_history = unserialize($modx->getOption('elementhelper.element_history'));

// Get the files from the directory and all sub directories
function get_files($directory_path)
{
    $file_list = array();

    if (is_dir($directory_path))
    {
        $directory = opendir($directory_path);

        // Get a list of files from the element types directory
        while (($item = readdir($directory)) !== false)
        {   
            if ($item !== '.' && $item !== '..')
            {
                $item_path = $directory_path . $item;

                if (is_file($item_path))
                {
                    $file_list[] = $item_path;
                }
                else
                {
                    $file_list = array_merge($file_list, get_files($item_path . '/'));
                }
            }
        }

        closedir($directory);
    }

    return $file_list;
}

// Create all the templates, snippets, chunks and plugins
foreach ($element_types as $element_type)
{
    $file_list = get_files(MODX_BASE_PATH . $element_type['path']);
    $file_names = array();

    foreach ($file_list as $file)
    {
        $file_type = explode('.', $file);
        $file_type = '.' . end($file_type);
        $file_name = basename($file, $file_type);

        $file_names[] = $file_name;

        $category_path = dirname(str_replace(MODX_BASE_PATH . $element_type['path'], '', $file));
        $category_names = explode('/', $category_path);

        // If it's not the current directory
        if ($category_path !== '.')
        {
            foreach ($category_names as $i => $category_name)
            {
                $parent_id = $i !== 0 ? $element_helper->get_category_id($category_names[$i - 1]) : 0;

                $element_helper->create_category($category_name, $parent_id);
            }
        }

        $element_helper->create_element($element_type, $file, $file_type, $file_name);
    }

    // Remove elements that are in the element history but no longer exist in the elements dir
    if ($modx->getOption('elementhelper.auto_remove_elements', null, true))
    {
        $element_type_name = $element_type['class_name'];

        // Check if a history for this element type exists
        if (isset($element_history[$element_type_name]))
        {
            // Loop through the element history for this element type
            foreach ($element_history[$element_type_name] as $old_element_name)
            {
                // Remove the element if it's not in the list of files
                if ( ! in_array($old_element_name, $file_names))
                {
                    $name_field = ($element_type_name === 'modTemplate' ? 'templatename' : 'name');

                    $element = $modx->getObject($element_type_name, array($name_field => $old_element_name));

                    $element->remove();
                }
            }
        }
    }
}


$tv_json_path = MODX_BASE_PATH . $modx->getOption('elementhelper.tv_json_path', null, 'core/elements/template_variables.json');

// Get the template variables
if (file_exists($tv_json_path))
{
    $tv_json = file_get_contents($tv_json_path);
    $tvs = json_decode($tv_json);
    $tv_names = array();

    // Check if there are some TVs to loop through
    if ($tvs !== null)
    {
        // Create all the template variables
        foreach ($tvs as $tv)
        {
            $tv_names[] = $tv->name;

            if (isset($tv->category))
            {
                $element_helper->create_category($tv->category, 0);
            }

            $element_helper->create_tv($tv);
        }

        // Remove elements that are in the element history but no longer exist in the TV JSON file
        if ($modx->getOption('elementhelper.auto_remove_elements', null, true))
        {
            // Check if a history for this element type exists
            if (isset($element_history['modTemplateVar']))
            {
                // Loop through the element history for this element type
                foreach ($element_history['modTemplateVar'] as $old_element_name)
                {
                    // Remove the element if it's not in the list of files
                    if ( ! in_array($old_element_name, $tv_names))
                    {
                        $element = $modx->getObject('modTemplateVar', array('name' => $old_element_name));

                        $element->remove();
                    }
                }
            }
        }
    }
}

// Save the list of created elements
$element_history_setting = $modx->getObject('modSystemSetting', 'elementhelper.element_history');
$element_history_setting->set('value', serialize($element_helper->history));
$element_history_setting->save();

// Refresh the cache
$modx->cacheManager->refresh(array(
    'resource' => array()
));
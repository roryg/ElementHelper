<?php
$default_element_helper_core_path = $modx->getOption('core_path') . 'components/elementhelper/';
$element_helper_core_path = $modx->getOption('elementhelper.core_path', null, $default_element_helper_core_path);

$element_helper = $modx->getService('elementhelper', 'ElementHelper', $element_helper_core_path . 'model/elementhelper/');

$element_types = array(
    'templates' => array(
        'class_name' => 'modTemplate',
        'path' => $modx->getOption('elementhelper.template_path')
    ),
    
    'chunks' => array(
        'class_name' => 'modChunk',
        'path' => $modx->getOption('elementhelper.chunk_path')
    ),

    'snippets' => array(
        'class_name' => 'modSnippet',
        'path' => $modx->getOption('elementhelper.snippet_path')
    ),

    'plugins' => array(
        'class_name' => 'modPlugin',
        'path' => $modx->getOption('elementhelper.plugin_path')
    )
);

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

    // Remove elements if they aren't in the elements folder anymore
    if ($modx->getOption('elementhelper.auto_remove_elements') == true)
    {
        foreach ($modx->getCollection($element_type['class_name']) as $element)
        {
            // Seriously wtf is with using 'templatename' instead of 'name' just for template elements?!
            $name_field = ($element_type['class_name'] === 'modTemplate' ? 'templatename' : 'name');

            // Remove the element if it's not in the list of files and it's not the element_helper plugin
            if (!in_array($element->get($name_field ), $file_names) && $element->get($name_field) != 'element_helper')
            {
                $element->remove();
            }
        }
    }
}


$tv_json_path = MODX_BASE_PATH . $modx->getOption('elementhelper.tv_json_path');

// Get the template variables
if (file_exists($tv_json_path))
{
    $tv_json = file_get_contents($tv_json_path);
    $tvs = json_decode($tv_json);
    $tv_names = array();

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

    // Remove template variables if they aren't in the TV json file
    if ($modx->getOption('elementhelper.auto_remove_elements') == true)
    {
        foreach ($modx->getCollection('modTemplateVar') as $template_var)
        {
            if (!in_array($template_var->get('name'), $tv_names))
            {
                $template_var->remove();
            }
        }
    }
}
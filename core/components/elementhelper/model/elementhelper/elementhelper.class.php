<?php

class ElementHelper
{
    private $modx;
    public $element_history;

    function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->history = array();
    }

    public function create_element($element_type, $file_path, $file_type, $name)
    {
        $content = file_get_contents($file_path);

        // Weirdly MODx uses a different name title for templates
        $name_field = ($element_type['class_name'] === 'modTemplate' ? 'templatename' : 'name');

        // Get the element
        $element = $this->modx->getObject($element_type['class_name'], array($name_field => $name));

        // If the element doesn't exist create it
        if (!isset($element))
        {
            $element = $this->modx->newObject($element_type['class_name']);
            
            $element->set($name_field, $name);
        }

        $category_path = dirname(str_replace(MODX_BASE_PATH . $element_type['path'], '', $file_path));
        $category_names = explode('/', $category_path);
        $description = $this->_get_description($content);

        $element->set('category', $this->get_category_id(end($category_names)));
        $element->set('description', $description);
        $element->set('static', 1);

        // check content of system setting "elementhelper.source"
        $element->set('source', $this->modx->getOption('elementhelper.source'));
        
        // get the base path of the defined media source to determine the right path to set for the static file
        $source = $this->modx->getObject('sources.modMediaSource', array('id' => $element->get('source')));

        // unfortunately necessary, getters will not work without this
        $source->initialize();

        $element->set('static_file', str_replace($source->getBasePath(), '', $file_path));

        $element->setContent($content);

        if ($element->save())
        {
            $this->history[$element_type['class_name']][] = $name;
        }
    }

    public function create_tv($tv)
    {
        $element = $this->modx->getObject('modTemplateVar', array('name' => $tv->name));

        // If the element doesn't exist create it
        if (!isset($element))
        {
            $element = $this->modx->newObject('modTemplateVar');
            $element->set('name', $tv->name);
        }

        // Set to no category by default in case it gets
        // set and then removed from template_variables.json
        $element->set('category', 0);

        foreach ($tv as $property => $value)
        {
            // Get the category id
            $value = ($property === 'category' ? $this->get_category_id($value) : $value);

            // input_properties fix, manualy convert to array before passing into set()
            if($property == 'input_properties'){
                foreach ($value as $key => $item) {
                    //MIGX Fix, convert array object into json string
                    if($key == 'formtabs' || $key == 'columns'){
                        $input_properties[$key] = json_encode($item);
                    }else{
                        $input_properties[$key] = $item;
                    }
                }
                $element->set('input_properties',$input_properties);
            }
            if ($property !== 'name' && $property !== 'template_access' && $property !== 'input_properties')
            {
                $element->set($property, $value);
            }
        }

        if ($element->save())
        {
            $this->history['modTemplateVar'][] = $tv->name;
        }

        if ($this->modx->getOption('elementhelper.tv_access_control') == true)
        {
            $templates = $this->modx->getCollection('modTemplate');

            // Remove all tv access for each template
            foreach ($templates as $template)
            {
                $this->_remove_template_access($tv->name, $template->get('templatename'));
            }

            if (isset($tv->template_access))
            {
                // Add tv access to the specified templates
                foreach ($tv->template_access as $template)
                {
                    $this->_add_template_access($tv->name, $template);
                }
            }
        }
    }

    private function _add_template_access($tv_name, $template_name)
    {
        $tv = $this->modx->getObject('modTemplateVar', array('name' => $tv_name));
        $template = $this->modx->getObject('modTemplate', array('templatename' => $template_name));

        if ($template !== null)
        {
            $tv_template = $this->modx->getObject('modTemplateVarTemplate', array('tmplvarid' => $tv->get('id'), 'templateid' => $template->get('id')));

            if (!isset($tv_template))
            {
                $tv_template = $this->modx->newObject('modTemplateVarTemplate');

                $tv_template->set('templateid', $template->get('id'));
                $tv_template->set('tmplvarid', $tv->get('id'));

                $tv_template->save();
            }
        }
    }

    private function _remove_template_access($tv_name, $template_name)
    {
        $tv = $this->modx->getObject('modTemplateVar', array('name' => $tv_name));
        $template = $this->modx->getObject('modTemplate', array('templatename' => $template_name));

        $tv_template = $this->modx->getObject('modTemplateVarTemplate', array('tmplvarid' => $tv->get('id'), 'templateid' => $template->get('id')));

        if (isset($tv_template))
        {
            $tv_template->remove();
        }
    }

    public function create_category($name, $parent_id)
    {
        $category = $this->modx->getObject('modCategory', array('category' => $name));

        // If the category doesn't exist create it
        if (!isset($category))
        {
            $category = $this->modx->newObject('modCategory');

            $category->set('category', $name);
        }

        $category->set('parent', $parent_id);

        $category->save();
    }

    private function _get_comments($file_contents)
    {
        $tokens = token_get_all($file_contents);

        $comments = array();

        foreach ($tokens as $token)
        {
            if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT)
            {
                $comments[] = $token[1];
            }
        }

        return $comments;
    }

    private function _get_description($file_contents)
    {
        $description = $this->modx->getOption('elementhelper.default_description');
        $comments = $this->_get_comments($file_contents);

        foreach ($comments as $comment)
        {
            $comment_lines = explode("\n", $comment);
            
            foreach($comment_lines as $comment_line)
            {
                // get string to search for description from system setting
                if (preg_match('/' . $this->modx->getOption('elementhelper.descriptionkey') . ' (.*)/', $comment_line, $match))
                {
                    $description = $match[1];
                }
            }
        }

        return $description;
    }

    public function get_category_id($name)
    {
        $category = $this->modx->getObject('modCategory', array('category' => $name));
        $category_id = isset($category) ? $category->get('id') : 0;
        
        return $category_id;
    }
}
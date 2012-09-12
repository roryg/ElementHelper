--------------------
Extra: Element Helper
--------------------
Version: 1.1.1
 
Element Helper is a MODx Revolution plugin for automatically creating elements from static files without the MODx manager.

Github: https://github.com/roryg/ElementHelper

Usage:

To start using ElementHelper create a folder named elements in the core directory of your MODX install and then create folders for chunks, snippets, templates and plugins within the elements folder (See the configuration section if you want to change where ElementHelper looks for your elements). Finally simply create your elements within those folders e.g. create a header.tpl file within the chunks folder or a get_menu.php file within your snippets folder. These elements will then automatically appear as elements in your MODX manager when you reload the manager or a frontend page.

Note: It is recommend that you only use this plugin during development of your site as it runs every time a page is loaded. You can disable it by simply going to the Elements tab in the manager and selecting 'Plugin Disabled' on the 'element_helper' plugin.

Template Variables are managed using a JSON file, if you're using the default settings create a template_variables.json file within your elements folder. To create a simple text template variable add the following to your template_variables.json file:

[
    {
        "name": "example_text_tv",
        "caption": "Example Text TV",
        "type": "text"
    }
]

Expanding on that example you could add an image template variable that is assigned to two templates called 'home' and 'standard_page' with the following:

[
    {
        "name": "example_text_tv",
        "caption": "Example Text TV",
        "type": "text"

    },
    {
        "name": "example_image_tv",
        "caption": "Example Image TV",
        "type": "Image",
        "template_access": ["home", "standard_page"]
    }
]

The following is a list of properties available for TVs

"type" The input type of this TV
"name" The name of this TV, and key by which it will be referenced in tags
"caption" The caption that will be used to display the name of this TV when on the Resource page
"description" A user-provided description of this TV
"category" The Category for this TV, or 0 if not in one
"locked" Whether or not this TV can only be edited by an Administrator
"elements" Default values for this TV
"rank" The rank of the TV when sorted and displayed relative to other TVs in its Category
"display" The output render type of this TV
"default_text" The default value of this TV if no other value is set
"properties" An array of default properties for this TV
"input_properties" An array of input properties related to the rendering of the input of this TV
"output_properties" An array of output properties related to the rendering of the output of this TV

Configuration:

The following configuration optons can be found by going to System Settings within your MODX manager and selecting the elementhelper namespace.

Automatically Remove Elements : Allow elementhelper to remove elements if you delete their source files (this will also remove TVs when you remove them from the TV JSON file).

Chunk Path : Set the path to you chunk elements

Plugin Path : Set the path to you plugin elements

Snippet Path : Set the path to you snippet elements

Template Path : Set the path to you template elements

Template Variables JSON Path: Set the path to your template variable JSON file

Template Variable Access Control: Allow elementhelper to give template variables access to the templates you set in the template variable json file. Note: Turning this on will remove template variable access from all templates unless specified in the template variable json file.
<?php

$_lang['setting_elementhelper.chunk_path'] = 'Chunk Path';
$_lang['setting_elementhelper.chunk_path_desc'] = 'The path to your chunk directory.';

$_lang['setting_elementhelper.template_path'] = 'Template Path';
$_lang['setting_elementhelper.template_path_desc'] = 'The path to your template directory.';

$_lang['setting_elementhelper.snippet_path'] = 'Snippet Path';
$_lang['setting_elementhelper.snippet_path_desc'] = 'The path to your snippet directory.';

$_lang['setting_elementhelper.plugin_path'] = 'Plugin Path';
$_lang['setting_elementhelper.plugin_path_desc'] = 'The path to your plugin directory.';

$_lang['setting_elementhelper.tv_file_path'] = 'Template Variables JSON Path';
$_lang['setting_elementhelper.tv_file_path_desc'] = 'The path to your template variables json file.';

$_lang['setting_elementhelper.element_sync_file_path'] = 'Element Sync File Path';
$_lang['setting_elementhelper.element_sync_file_path_desc'] = "The path to your element sync json file. The file will be created automatically at this path if it doesn't already exists.";

$_lang['setting_elementhelper.usergroups'] = 'Usergroups';
$_lang['setting_elementhelper.usergroups_desc'] = 'Comma-delimited list of usergroups where ElementHelper should be active, usually only the group for Administrators/Devs that can change files in the target directories.';

$_lang['setting_elementhelper.tv_access_control'] = 'Template Variable Access Control';
$_lang['setting_elementhelper.tv_access_control_desc'] = 'Allow ElementHelper to give template variables access to the templates you set in the template variable json file. Note: Turning this on will remove template variable access from all templates unless specified in the template variable json file. You will need to resave your tempate_variables.json file after turning this on.';

$_lang['setting_elementhelper.category_whitelist'] = 'Category Whitelist';
$_lang['setting_elementhelper.category_whitelist'] = 'Comma-delimited list of categories that ElementHelper is allowed to work with. This helps to prevent tracking elements (such as plugins) created by other Modx Extras.';
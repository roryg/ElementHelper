<?php

class FileHelper
{
	/**
	 * Recursively gets a list of all files in a directory and its subdirectories
	 * 
	 * @param string $directory_path
	 * 
	 * @return array
	 */
	static function get_directory_file_list($directory_path)
	{
		$file_list = array();

		if (is_dir($directory_path))
		{
			$directory = opendir($directory_path);
			
			while (($item = readdir($directory)) !== false)
			{
				// Ignore filenames starting with a dot
				if ($item[0] === '.')
				{
					continue;
				}

				$item_path = $directory_path . $item;

				if (is_file($item_path))
				{
					$file_list[] = $item_path;
				}
				else
				{
					$file_list = array_merge(self::get_directory_file_list($item_path . '/'), $file_list);
				}
			}

			closedir($directory);
		}

		return $file_list;
	}

	/**
	 * Returns an array of T_DOC_COMMENTs from a string (usually a files contents).
	 * 
	 * @param string $file_content
	 * 
	 * @return array
	 */
	static function get_file_doc_comments($file_content)
	{
		$comments = array();
		$tokens = token_get_all($file_content);

		foreach ($tokens as $token)
		{
			if ($token[0] === T_DOC_COMMENT)
			{
				$comments[] = $token[1];
			}
		}

		return $comments;
	}

	/**
	 * Gets and returns file details
	 * 
	 * @param string $file_path
	 * 
	 * @return array
	 */
	static function get_file_meta($file_path)
	{
		$file_name = explode('.', basename($file_path));

		$meta = array(
			'name' => $file_name[0],
			'type' => $file_name[1],
			'mod_time' => filemtime($file_path)
		);

		return $meta;
	}
}
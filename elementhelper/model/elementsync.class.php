<?php

class ElementSync
{
	private $modx;
	private $sync_json_file;
	private $elements;

	/**
	 * @param modX $modx
	 * @param string $json_file
	 */
	function __construct(modX $modx, $sync_file_path)
	{
		$this->modx = $modx;
		$this->sync_json_file = $sync_file_path;

		// If the sync file doesn't exist create it and set the elements to null
		if ( ! $this->get_elements())
		{
			$sync_json_file = fopen($sync_file_path, 'wb');

			fwrite($sync_json_file, '');
			fclose($sync_json_file);

			$this->elements = null;
		}
	}

	/**
	 * Gets the elements from the sync file
	 * 
	 * @return boolean
	 */
	private function get_elements()
	{
		if (file_exists($this->sync_json_file))
		{
			$sync_json = file_get_contents($this->sync_json_file);
			
			$this->elements = ($sync_json === '' ? null : json_decode($sync_json, true));

			return true;
		}

		return false;
	}

	/**
	 * Checks if an element is in the sync
	 * 
	 * @param string $type
	 * @param string $name
	 * 
	 * @return boolean
	 */
	public function has_element($type, $name)
	{
		return (isset($this->elements[$type][$name]) ? true : false);
	}

	/**
	 * Returns the modification time for an element recorded in the sync
	 * 
	 * @param string $type
	 * @param string $name
	 * 
	 * @return integer
	 */
	public function get_element_mod_time($type, $name)
	{
		return $this->elements[$type][$name];
	}

	/**
	 * Adds an element to the sync
	 * 
	 * @param string $type
	 * @param string $name
	 * @param integer $mod_time
	 * 
	 * @return boolean
	 */
	public function add_element($type, $name, $mod_time)
	{
		$this->elements[$type][$name] = $mod_time;

		$sync_json = json_encode($this->elements);

		if (file_put_contents($this->sync_json_file, $sync_json))
		{
			return true;
		}

		return false;
	}

	/**
	 * Removes an element from the sync
	 * 
	 * @param string $type
	 * @param string $name
	 * 
	 * @return boolean
	 */
	public function remove_element($type, $name)
	{
		unset($this->elements[$type][$name]);

		$sync_json = json_encode($this->elements);

		if (file_put_contents($this->sync_json_file, $sync_json))
		{
			return true;
		}

		return false;
	}
}
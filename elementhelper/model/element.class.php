<?php

class Element
{
	private $element;

	private function __construct($element)
	{
		$this->element = $element;
	}

	/**
	 * Creates an element object of the specified type
	 * 
	 * @param modX $modx
	 * @param string $type
	 * @param string $name
	 * 
	 * @return Element | boolean
	 */
	public static function create(modX $modx, $type, $name)
	{
		$element = $modx->newObject($type);

		$element->set(Element::get_name_field($type), $name);

		if ($element->save())
		{
			return new Element($element);
		}

		return false;
	}

	/**
	 * Gets an element object of the specifed type
	 * 
	 * @todo Keep getting "Call to a member function getObject() on a non-object" 
	 * 
	 * @param modX $modx
	 * @param string $type
	 * @param integer | string $criteria (ID or name of the element) 
	 * 
	 * @return Element | boolean
	 */
	public static function get(modX $modx, $type, $criteria)
	{
		if (is_int($criteria))
		{
			$element = $modx->getObject($type, $criteria);
		}
		else
		{
			$element = $modx->getObject($type, array((Element::get_name_field($type)) => $criteria));
		}

		if (isset($element))
		{
			return new Element($element);
		}

		return false;
	}

	/**
	 * Simply starts a new Element instance with the passed element object
	 * 
	 * @param object $element
	 * 
	 * @return Element
	 */
	public static function insert($element)
	{
		return new Element($element);
	}

	/**
	 * Removes an element
	 * 
	 * @return boolean
	 */
	public function remove()
	{
		if ($this->element->remove())
		{
			return true;
		}

		return false;
	}

	/**
	 * Weirdly Modx uses a different title for the name field of various element
	 * types. This simplifies getting it.
	 * 
	 * @param string $type
	 * 
	 * @return string
	 */
	private static function get_name_field($type)
	{
		switch($type)
		{
			case 'modTemplate' :
				return 'templatename';
			case 'modCategory' :
				return 'category';
			default :
				return 'name';
		}
	}

	/**
	 * Loops through the supplied properties array and sets them on
	 * the $element object
	 * 
	 * @todo add name to this?
	 * @todo use switch?
	 * 
	 * @param array $properties
	 * 
	 * @return boolean
	 */
	public function set_properties($properties)
	{
		foreach ($properties as $property => $value)
		{
			if ( ! isset($value))
			{
				continue;
			}

			if ($property === 'content')
			{
				$this->element->setContent($value);
			}
			else
			{
				$this->element->set($property, $value);
			}
		}

		if ($this->element->save())
		{
			return true;
		}

		return false;
	}

	/**
	 * Gets the specifed properties value from the element object
	 * 
	 * @todo use switch?
	 * 
	 * @param string $property
	 * 
	 * @return string
	 */
	public function get_property($property)
	{
		if ($property === 'name')
		{
			$name_field = Element::get_name_field($this->element->_class);

			return $this->element->get($name_field);
		}

		if ($property === 'content')
		{
			return $this->element->getContent();
		}

		return $this->element->get($property);
	}
}
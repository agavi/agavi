<?php

/**
 * A view that uses PHP to render templates, plus convenience methods for making form elements
 *
 * @package    agavi
 * @subpackage view
 * @abstract
 *
 * @author    Mike Vincent (mike@agavi.org)
 * @since     0.10.0
 * @version   $Id$
 */
abstract class FormPHPView extends PHPView
{

	private $_firstField = false;
	
	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+


	/**
	 * Parse an associative array into a string suitable 
	 * for use as attribute key/value pairs on a form element.
	 *
	 * @param	array $parms [eg: array('size' => '10') ]
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function parmsToString($parms, $allow_first = true)
	{
		$string = ''; $ff_handled = false;
		if ( is_array($parms) && count($parms) > 0 ) {
			foreach ($parms as $key => $value) {
				if (strtolower($key) == 'class' && $this->_isFirstField($allow_first)) {
					$ff_handled = true;
					$value .= ' first';
				}
				$string .= " $key=\"$value\"";
			}
		}
		if (!$ff_handled && $this->_isFirstField($allow_first)) {
			$string .= ' class="first"';
		}
		return $string;
	}
	
	/**
	 * Build a text <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formText($name, $label = '', $parms = array('type'=>'text'), $labelparms = array())
	{
		$retval = '';
		if (is_array($parms) && !isset($parms['type'])) {
			$parms = array('type'=>'text') + $parms;
		} 
		if (is_array($parms) && !isset($parms['id'])) {
			$parms = array('id'=>$name) + $parms;
		}
		if($parms['type'] == 'hidden') {
			$allow_first = false;
		} else {
			$allow_first = true;
		}
		if($label != '') {
			$retval .= '<label' .
							$this->parmsToString($labelparms,$allow_first) . '>' . htmlspecialchars($label);
			$retval .= '<input name="' . $name . '"' .
							$this->parmsToString($parms, $allow_first) . '></label>';
		} else {
			$retval .= '<input name="' . $name . '"' . $this->parmsToString($parms,$allow_first) . '>';
		}
		return $retval;
	}
	
	/**
	 * Build a hidden type <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @todo Need to take $label out of the parameter list.
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.10.0
	 */
	public function formHidden($name, $label = '', $parms = array())
	{
		$label = '';
		$parms = is_array($parms) ? $parms + array('type' => 'hidden') : array('type' => 'hidden');
		if(isset($parms['class'])) {
			$parms['class'] .= ' hidden';
		} else {
			$parms = $parms + array('class' => 'hidden');
		}
		return $this->formText($name, $label, $parms);
	}

	/**
	 * Build an image type <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @todo Need to take $label out of the parameter list.
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.10.0
	 */
	public function formImage($name, $label = '', $parms = array())
	{
		$label = '';
		$parms = is_array($parms) ? $parms + array('type' => 'image') : array('type' => 'image');
		return $this->formText($name, $label, $parms);
	}
	
	/**
	 * Build a submit type <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @todo Need to take $label out of the parameter list.
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formSubmit($name, $label = '', $parms = array())
	{
		$label = '';
		$parms = is_array($parms) ? $parms + array('type' => 'submit', 'class' => 'submit') : array('type' => 'submit', 'class' => 'submit');
		return $this->formText($name, $label, $parms);
	}

	/**
	 * Build a button type <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formButton($name, $label = '', $parms = array(), $labelparms = array())
	{
		$parms = is_array($parms) ? $parms + array('type' => 'button') : array('type' => 'button');
		return $this->formText($name, $label, $parms, $labelparms);
	}
	
	/**
	 * Build a radio type <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formRadio($name, $label = '', $parms = array(), $labelparms = array())
	{
		$parms = is_array($parms) ? $parms + array('type' => 'radio') : array('type' => 'radio');
		return $this->formText($name, $label, $parms, $labelparms);
	}

	/**
	 * Build a checkbox type <input> form element.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formCheckbox($name, $label = '', $parms = array(), $labelparms = array())
	{
		$parms = is_array($parms) ? $parms + array('type' => 'checkbox') : array('type' => 'checkbox');
		if(isset($parms['class'])) {
			$parms['class'] .= ' checkbox';
		} else {
			$parms = is_array($parms) ? $parms + array('class' => 'checkbox') : array('class' => 'checkbox');
		}

		if(isset($parms['checked']) && !$parms['checked']) {
			unset($parms['checked']);
		}
		return $this->formText($name, $label, $parms, $labelparms);
	}
	
	/**
	 * Build <textarea> form elements.
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formTextarea($name, $label = '', $parms = array(), $labelparms = array())
	{
		$retval = '';
		if (isset($parms['value'])) {
			$val = htmlspecialchars($parms['value']);
			unset($parms['value']);
		} else {
			$val = '';
		}
		if (is_array($parms) && !isset($parms['id'])) {
			$parms = array('id'=>$name) + $parms;
		}

		if($label != '') {
			$retval .= '<label' .
							$this->parmsToString($labelparms) . '>' . htmlspecialchars($label);
			$retval .= '<textarea name="' . $name . '"' .
							$this->parmsToString($parms) . ">{$val}</textarea></label>";
		} else {
			$retval .= '<textarea name="' . $name . '"' . $this->parmsToString($parms) . ">{$val}</textarea>";
		}
		return $retval;
	}
	
	/**
	 * Parse an array into <option> elements.
	 *
	 * @param array $input array of values to be used as the value and label
	 * @param array $input array of arrays of value/label pairs to be used as the value and label, with an optional 3rd element to toggle selection
	 * @param array $selected array of values to determine selected options
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	private function selectOptions($input, $selected = false)
	{
		// input can be given as one of...
		// $input[] = 'value'; 				// value is the value and label
		// $input[] = array('value'); // ditto
		// $input[] = array('value', 'label'); // seperate value and label
		// $input[] = array('value', 'label', 'anythingThatBoolsTrue'); // seperate value/label and is Selected
		
		$options = '';
		if ($selected && !is_array($selected)) {
			$selected = array($selected);
		}
		foreach ($input as $i) {
			// Start the tag
			$value = is_array($i) ? $i[0] : $i;
			$options .= '<option value="' . $value . '"';
			if ((is_array($i) && !empty($i[2])) || (count($input) == 1) || ($selected && in_array($value, $selected))) {
				$options .= ' selected';
			}
			$options .= '>';
			
			// label
			if ( !is_array($i) ) {
				$options .= $i;
			} else {
				$options .= isset($i[1]) ? $i[1] : $i[0];
			}
			$options .= '</option>';
		}
		return $options;
	}
	
	/**
	 * Build a select input form element.
	 * options should be passed in the parms array as the 'options' key 
	 * pass 'multiple' => true for a multiple type select
	 *
	 * @param string $name required name of the element
	 * @param array $parms optional attributes to add to the element 
	 *  one of the attributes should be named 'options' and be an array of options
	 *  additionally, an array of selected values can be passed as $param['value'] = array('val1', 'val2', ...)
	 * @return string
	 *
	 * @author Mike Vincent (mike@agavi.org)
	 * @since  0.10.0
	 */
	public function formSelect($name, $label = '', $parms = array(), $labelparms = array())
	{
		$retval = '';
		$options = isset($parms['options']) ? 
			$this->selectOptions($parms['options'], (isset($parms['value']) ? $parms['value'] : false)) : '';
		unset($parms['options']);
		unset($parms['value']);
		if (is_array($parms) && !isset($parms['id'])) {
			$parms = array('id'=>$name) + $parms;
		}
		
		if($label != '') {
			$retval .= '<label' .
							$this->parmsToString($labelparms) . '>' . htmlspecialchars($label);
		}
		$retval .= '<select name="' . $name;

		if ( $parms && array_key_exists('multiple', $parms) ) {
			$retval .= '[]" multiple';
		} else {
			$retval .= '"';
		}
		unset($parms['single']);
		unset($parms['multiple']);
		
		$retval .= $this->parmsToString($parms) . ">$options</select>";
		if ($label != '') {
			$retval .= "</label>";
		}

		return $retval;
	}

	/**
	 * Build a <fieldset> tag
	 *
	 * @param string $legend optional legend text
	 * @return string
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.10.0
	 */
	public function formStartFieldset($legend = '', $parms = array())
	{
		$this->_firstField = true;
		return '<fieldset'.$this->parmsToString($parms,false).'>'.
			(!empty($legend) ? '<legend>' . htmlspecialchars($legend) . '</legend>' : '');
	}

	/**
	 * Build a </fieldset> tag
	 *
	 * @return string
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.10.0
	 */
	public function formEndFieldset()
	{
		return '<div class="clearer"></div></fieldset>';
	}

	/**
	 * Build a password field
	 *
	 * @param string $name required name of the element
	 * @param string $label optional label for element
	 * @param array $parms optional attributes to add to the element
	 * @return string
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.10.0
	 */
	public function formPassword($name, $label = '', $parms = array(), $labelparms = array())
	{
		$parms = is_array($parms) ? $parms + array('type' => 'password') : array('type' => 'password');
		return $this->formText($name, $label, $parms, $labelparms);
	}

	/**
	 * Handle setting the class="first" on first element of a <fieldset>
	 *
	 * @return string
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.10.0
	 */
	private function _isFirstField($allow_first)
	{
		if ($this->_firstField and $allow_first) {
			$this->_firstField = false;
			return true;
		}
		return false;
	}
	

	public function implode_assoc($inner_glue, $outer_glue, $array) {
		$output = array();
		foreach( $array as $key => $item ) {
			$output[] = $key . $inner_glue . $item;
		}
		return implode($outer_glue, $output);
	}
}

?>

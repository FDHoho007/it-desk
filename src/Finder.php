<?php

class Finder {
	
	private $elements;
	
	function __construct($elements) {
		$this->elements = $elements;
	}
	
	function find($functionName, $value, $comparison = "==", $param = null) {
		$result = [];
		foreach($this->elements as $element)
			eval("if(call_user_func([\$element, \$functionName], \$param) $comparison \$value) array_push(\$result, \$element);");
		return new Finder($result);
	}
	
	function fetchAll() {
		return $this->elements;
	}
	
	function fetchOne() {
		return $this->elements[0];
	}
	
}
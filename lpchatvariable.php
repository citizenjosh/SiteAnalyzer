<?php


/**
* A variable on a webpage that uses LP Chat
*
* @author     jrosenthal@LivePerson.com
* @version    1.0
*/
class lpchatvariable {

	/**
	* lpAddVars
	*/
	const lpaddvars = "lpAddVars";
	/**
	* lpSendData
	*/
	const lpsenddata = "lpSendData";

	/**
	* Scope
	*
	* @var	string	lifetime of the variable
	*/
	private $scope;
	/**
	* name
	*
	* @var	string	name of the variable
	*/
	private $name;
	/**
	* value
	*
	* @var	string	value of the variable
	*/
	private $value;

	/**
	* Private constructor
	*
	* @param	string	$scope	lifetime of the variable
	* @param	string	$name	name of the variable
	* @param	string	$value	value of the variable
	*/
	private function LpChatVariable($scope, $name, $value) {
		$this->set_scope($scope);
		$this->set_name($name);
		$this->set_value($value);
	}
	/**
	*
	* @param	string	$scope	lifetime of the variable
	* @param	string	$name	name of the variable
	* @param	string	$value	value of the variable
	* @return	object	LpChatVariable
	*/
	public static function MakeFromArguments($scope, $name, $value){
		return new LpChatVariable($scope, $name, $value);
	}
	/**
	* Parse the string of arguments of a method call into its arguments
	*
	* @param	string $method_arguments_as_a_string	Format: "(<scope>,<name>,<value>)"
	*/
	public static function MakeFromSignature($method_arguments_as_a_string) {
                /* remove method name if present */
                $method_arguments_as_a_string = str_ireplace(array("lpaddvars","lpsenddata"), "", $method_arguments_as_a_string);
            
                /* remove surrounding parenthesis if present */
		$method_arguments_as_a_string = trim($method_arguments_as_a_string,"()");

		/* split arguments into array elements */
		//$method_arguments = preg_split("/,/", $method_arguments_as_a_string);
		$method_arguments = explode(",", $method_arguments_as_a_string);
		
		/* remove surrounding quotes, spaces, or apostrophes */		
		/* assign array elements to variables */
		$scope=trim($method_arguments[0],'" \'');
		$name=trim($method_arguments[1],'" \'');
		$value=trim($method_arguments[2],'" \'');
		
		return lpchatvariable::MakeFromArguments($scope, $name, $value);
	}

	/* setters */
	function set_scope($scope){
		$this->scope = $scope;
	}
	function set_name($name){
		$this->name = $name;
	}
	function set_value($value){
		$this->value = $value;
	}
	/* getters */
	function get_scope(){
		return $this->scope;
	}
	function get_name(){
		return $this->name;
	}
	function get_value(){
		return $this->value;
	}

	/**
	* @return	string	a string representation of this object
	*/
	function __toString(){
		return "var(".$this->get_scope().", ".$this->get_name().", ".$this->get_value().")";
	}
}
?>
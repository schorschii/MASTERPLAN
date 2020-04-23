<?php
class htmlinput {

	public static function selectIf($value, $checkValue) {
		$res = "value='".$value."'";
		if($value == $checkValue)
			$res .= " selected='true'";
		return $res;
	}
	public static function check($value, $checked) {
		$res = "value='".$value."'";
		if($checked)
			$res .= " checked='true'";
		return $res;
	}

}

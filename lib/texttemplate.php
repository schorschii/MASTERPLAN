<?php

class texttemplate {

	public static function processTemplate($templateName, $parameter) {
		$filepath = TEMPLATE_FILES.'/'.$templateName;
		if(!file_exists($filepath)) return false;

		$templateContent = file_get_contents($filepath);

		// replace system variables
		$templateContent = str_replace('$$BASE_URL$$', WEB_ADDRESS, $templateContent);

		// replace special variables
		if($parameter != null) {
			foreach($parameter as $ey => $value) {
				$templateContent = str_replace($key, $value, $templateContent);
			}
		}

		return $templateContent;
	}

}

<?php

namespace aharen;

class WeatherMv {

	private $url = 'http://www.meteorology.gov.mv/fetchweather/';

	private $cities = ['Male', 'Hanimadhoo', 'Kahdhoo', 'Kaadehdhoo', 'Gan'];

	private $city;

	private $api_return;

	public function __construct($city = null) {
		$this->setCity($city);
	}

	private function getUrl() {
		return $this->url;
	}

	private function setCity($city) {
		$this->city = $city;
	}

	private function getCity() {
		return $this->city;
	}

	private function getCities() {
		return $this->cities;
	}

	private function isValidCity() {
		
		if ($this->getCity() === null || $this->getCity() === '') {
			return false;
		}

		if (!in_array($this->getCity(), $this->getCities())) {
			return false;
		}

		return true;

	}

	private function doCurl($uri) {

		try {
			$ch = curl_init ($uri);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			$rawdata = curl_exec($ch);
			curl_close ($ch);

			$removeUTF8BOM = substr($rawdata, 3);
			$cleanData = stripslashes($removeUTF8BOM);
			$this->api_return = json_decode($cleanData);
		
 		} catch (Exception $e) {			
			return $this->output('Error', 'Error communicating with Weather API: '.$e->getMessage());

		}
	}

	private function output($status, $message, $return = NULL) {

		return (object)[
			'status' => $status,
			'message' => $message,
			'data' => $return
		];

	}

	public function getData() {
		
		if (!$this->isValidCity()) {
			return $this->output('Error', 'Invalid City');
		}

		$curlUrl = $this->url . $this->getCity();
		
		$this->doCurl($curlUrl);

		return $this->output('Success', 'OK', $this->api_return);
	}

}
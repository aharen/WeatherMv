<?php

/*
The MIT License (MIT)

Copyright (c) 2016 Ahmed Khusaam

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace aharen\WeatherMv;

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
			curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			$rawdata = curl_exec($ch);
			curl_close ($ch);
			$this->api_return = $rawdata;
		
 		} catch (Exception $e) {			
			$this->api_return = 'Error communicating with Weather API: '.$e->getMessage(); 
		}

	}

	private function output($status, $message, $return = NULL) {

		return (object)[
			'status' => $status,
			'message' => $message,
			'data' => $return
		];

	}

	private function formatData() {

		if (empty($this->api_return)) {
			return $this->output('Error', 'API return is empty');
		}

		$expl = explode('","', $this->api_return);
		$formatedData = [];
		foreach ($expl as $item) {
			if ($item !== '') {
				$explItem = explode('":"', $item);
				$formatedData[ str_replace('{"', '', $explItem[0]) ] = str_replace('"}', '', $explItem[1]);
			}
		}

		return (object)$formatedData;
	}

	public function getData() {
		
		if (!$this->isValidCity()) {
			return $this->output('Error', 'Invalid City');
		}

		$curlUrl = $this->url . $this->getCity();
		
		$this->doCurl($curlUrl);

		return $this->output('Success', 'OK', $this->formatData());
	}

}
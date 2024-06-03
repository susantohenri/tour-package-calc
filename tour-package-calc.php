<?php
/*
* Plugin Name: Tour Package Calculator
* Description: Wordpress Calculator.
* Version: 1.0
* Author: Henri Susanto
* Author URI: http://github.com/susantohenri
*/

add_shortcode('tour-package-calculator', function () {
	$dropdowns = [
		[
			'name' => 'package_selection',
			'label' => 'PACKAGE SELECTION',
			'options' => [
				['text' => 'REFEREE', 'value' => 1199],
				['text' => 'SOLO TRAVELER', 'value' => 1899],
				['text' => 'COUPLE (WITH OPTION FOR KIDS)', 'value' => 1899]
			]
		],
		[
			'name' => 'extension',
			'label' => 'EXTENSION',
			'options' => [
				['text' => 'FLIGHTS & 3 NIGHTS', 'value' => 0],
				['text' => 'FLIGHTS & 5 NIGHTS', 'value' => 349],
				['text' => 'FLIGHTS & 7 NIGHTS', 'value' => 698],
				['text' => 'OWN FLIGHTS - 3 NIGHTS', 'value' => -700],
				['text' => 'OWN FLIGHTS - 5 NIGHTS', 'value' => -351],
				['text' => 'OWN FLIGHTS - 7 NIGHTS', 'value' => 2]
			]
		],
		[
			'name' => 'add_ons',
			'label' => 'ADD ONS',
			'options' => [
				['text' => 'SINGLET', 'value' => 69],
				['text' => 'CHILD (12 & UNDER)', 'value' => 899],
				['text' => 'INFANT (24 MONTHS & UNDER)', 'value' => 199],
			]
		],
	];

	$html = '';
	foreach ($dropdowns as $d) {
		$html .= "<label>{$d['label']}</label> ";
		$html .= "<select id=\"{$d['name']}\" onchange=\"javascript:tour_package_calculator();\">";
		foreach ($d['options'] as $o) {
			$html .= "<option value=\"{$o['value']}\">{$o['text']}</option>";
		}
		$html .= "</select>";
		$html .= "<br>";
	}

	$html .= "<br><b>UPFRONT (WITHIN 7 DAYS)</b> $51.95 DEPOSIT + ONE FINAL PAYMENT OF <span id=\"answer\">123</span> PER PERSON";

	$html .= "
		<script type=\"text/javascript\">
			tour_package_calculator ()
			function tour_package_calculator () {
				const package_selection = parseFloat(document.getElementById(`package_selection`).value)
				const extension = parseFloat(document.getElementById(`extension`).value)
				const add_ons = parseFloat(document.getElementById(`add_ons`).value)

				document.getElementById(`answer`).innerHTML = ((package_selection + extension + add_ons - 50) * 0.029).toFixed(2)
			}
		</script>
	";
	return $html;
});

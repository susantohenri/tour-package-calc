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
		$html .= "<label><b>{$d['label']}</b></label> ";
		$html .= "<select id=\"{$d['name']}\" onchange=\"javascript:tour_package_calculator();\">";
		foreach ($d['options'] as $o) {
			$html .= "<option value=\"{$o['value']}\">{$o['text']}</option>";
		}
		$html .= "</select>";
		$html .= "<br>";
	}

	$html .= "
		<link rel=\"stylesheet\" href=\"https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css\">
		<link rel=\"stylesheet\" href=\"/resources/demos/style.css\">
		<script src=\"https://code.jquery.com/jquery-3.7.1.js\"></script>
		<script src=\"https://code.jquery.com/ui/1.13.3/jquery-ui.js\"></script>
	";

	$today = date('m/d/Y');
	$html .= "<label><b>FIRST PAYMENT DATE</b></label> <input type=\"text\" id=\"tour-package-calc-first-payment-date\" value=\"{$today}\" onchange=\"javascript:tour_package_calculator();\" ><br>";

	$html .= "<label><b>FINAL PAYMENT DATE</b></label> <input type=\"text\" id=\"tour-package-calc-final-payment-date\" disabled ><br>";

	$html .= "<br><b>UPFRONT (WITHIN 7 DAYS)</b> $51.95 DEPOSIT + ONE FINAL PAYMENT OF $<span id=\"tour-package-calc-upfront\">0</span> PER PERSON";
	$html .= "<br><b>WEEKLY</b> $51.95 DEPOSIT + (<span class=\"tour-package-calc-weeks\"></span>) INSTALMENTS OF $<span id=\"tour-package-calc-weekly\">0</span> PER PERSON";
	$html .= "<br><b>MONTHLY</b> $51.95  DEPOSIT + (<span class=\"tour-package-calc-weeks\"></span>) INSTALMENTS OF $<span id=\"tour-package-calc-monthly\">0</span> PER PERSON";

	$html .= "<br><br>THE ABOVE INCLUDES 2.9% CREDIT CARD FEE & 9% FINANCING ADMIN FEE FOR WEEKLY OR MONTHLY";

	$html .= "
		<script type=\"text/javascript\">
			jQuery(`[id=\"tour-package-calc-first-payment-date\"]`).datepicker()

			tour_package_calculator ()
			function tour_package_calculator () {
				const package_selection = parseFloat(document.getElementById(`package_selection`).value)
				const extension = parseFloat(document.getElementById(`extension`).value)
				const add_ons = parseFloat(document.getElementById(`add_ons`).value)

				const picked = document.getElementById(`tour-package-calc-first-payment-date`).value.split(`/`)
				const first = new Date(picked[2], picked[0], picked[1])
				const current_year_final = new Date(first.getFullYear(), 11, 20)
				const next_year_final = new Date(first.getFullYear() + 1, 11, 20)
				const final = first > current_year_final ? next_year_final : current_year_final

				const weeks = Math.round((final - first) / (7 * 24 * 60 * 60 * 1000));
				const months= tour_package_calculator_month(first, final)

				document.getElementById(`tour-package-calc-final-payment-date`).value = `11/20/` + final.getFullYear()
				document.getElementById(`tour-package-calc-upfront`).innerHTML = ((package_selection + extension + add_ons - 50) * 0.029).toFixed(2)
				jQuery(`.tour-package-calc-weeks`).html(weeks)
				document.getElementById(`tour-package-calc-weekly`).innerHTML = ((package_selection + extension + add_ons - 50) * 0.119 / weeks).toFixed(2)
				document.getElementById(`tour-package-calc-monthly`).innerHTML = ((package_selection + extension + add_ons - 50) * 0.119 / months).toFixed(2)
			}

			function tour_package_calculator_month(d1, d2) {
				var months;
				months = (d2.getFullYear() - d1.getFullYear()) * 12;
				months -= d1.getMonth();
				months += d2.getMonth();
				return months <= 0 ? 0 : months;
			}
		</script>
	";
	return $html;
});

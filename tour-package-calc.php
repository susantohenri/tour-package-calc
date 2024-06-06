<?php
/*
* Plugin Name: Tour Package Calculator
* Description: Wordpress Calculator.
* Version: 1.0
* Author: Henri Susanto
* Author URI: http://github.com/susantohenri
*/

define('TOUR_PACKAGE_CALC_DEFAULT_DROPDOWNS', [
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
]);

add_shortcode('tour-package-calculator', function () {
	$dropdowns = null;
	if (!$dropdowns) $dropdowns = tour_package_calculator_option();
	if (!$dropdowns) $dropdowns = tour_package_calculator_option(json_encode(TOUR_PACKAGE_CALC_DEFAULT_DROPDOWNS));
	$dropdowns = json_decode($dropdowns);
	$dropdowns = array_map(function ($select) {
		$select->options = array_map(function ($options) {
			return (array) $options;
		}, $select->options);
		return (array) $select;
	}, $dropdowns);

	$html = '';
	foreach ($dropdowns as $i => $d) {
		if ($i > 1) continue;
		$html .= "<label><b>{$d['label']}</b></label> ";
		$html .= "<select id=\"{$d['name']}\" onchange=\"javascript:tour_package_calculator();\">";
		foreach ($d['options'] as $o) {
			$html .= "<option value=\"{$o['value']}\">{$o['text']}</option>";
		}
		$html .= "</select>";
		$html .= "<br>";
	}

	$html .= "
		<br>
		<label><b>ADD ONS</b></label>
	";
	foreach ($dropdowns[2]['options'] as $add_on) {
		$html .= "<br><input type=\"text\" onKeyUp=\"javascript:tour_package_calculator();\" class=\"tour_package_calculator_add_ons\" data-price=\"{$add_on['value']}\"> {$add_on['text']}";
	}
	$html .= "<br>";

	$html .= "
		<link rel=\"stylesheet\" href=\"https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css\">
		<link rel=\"stylesheet\" href=\"/resources/demos/style.css\">
		<script src=\"https://code.jquery.com/jquery-3.7.1.js\"></script>
		<script src=\"https://code.jquery.com/ui/1.13.3/jquery-ui.js\"></script>
	";

	$html .= "<label><b>FIRST PAYMENT DATE</b></label> <input type=\"text\" id=\"tour-package-calc-first-payment-date\" onchange=\"javascript:tour_package_calculator();\" ><br>";

	$html .= "<label><b>FINAL PAYMENT DATE</b></label> <input type=\"text\" id=\"tour-package-calc-final-payment-date\" ><br>";

	$html .= "<br><b>UPFRONT (WITHIN 7 DAYS)</b> $51.95 DEPOSIT + ONE FINAL PAYMENT OF $<span id=\"tour-package-calc-upfront\">0</span> PER PERSON";
	$html .= "<br><b>WEEKLY</b> $51.95 DEPOSIT + (<span class=\"tour-package-calc-weeks\"></span>) INSTALMENTS OF $<span id=\"tour-package-calc-weekly\">0</span> PER PERSON";
	$html .= "<br><b>MONTHLY</b> $51.95  DEPOSIT + (<span class=\"tour-package-calc-months\"></span>) INSTALMENTS OF $<span id=\"tour-package-calc-monthly\">0</span> PER PERSON";

	$html .= "<br><br>THE ABOVE INCLUDES 2.9% CREDIT CARD FEE & 9% FINANCING ADMIN FEE FOR WEEKLY OR MONTHLY";

	$html .= "
		<script type=\"text/javascript\">

			const today = new Date()
			const maxdate = `11/20/` + today.getFullYear()

			jQuery(`[id=\"tour-package-calc-first-payment-date\"]`).val(
				today.getMonth() + 1
				+`/`
				+ today.getDate()
				+`/`
				+ today.getFullYear()
			).datepicker({maxDate: maxdate})

			jQuery(`[id=\"tour-package-calc-final-payment-date\"]`).val(maxdate).datepicker({maxDate: maxdate})

			tour_package_calculator ()
			function tour_package_calculator () {
				const package_selection = parseFloat(document.getElementById(`package_selection`).value)
				const extension = parseFloat(document.getElementById(`extension`).value)
				
				let add_ons = 0
				jQuery(`.tour_package_calculator_add_ons`).each(function () {
					const add_on = jQuery(this)
					add_ons += (parseFloat(add_on.val()) || 0) * parseFloat(add_on.attr(`data-price`))
				})

				let first = document.getElementById(`tour-package-calc-first-payment-date`).value.split(`/`)
				first = new Date(first[2], first[0], first[1])

				let final = document.getElementById(`tour-package-calc-final-payment-date`).value.split(`/`)
				final = new Date(final[2], final[0], final[1])

				const weeks = Math.round((final - first) / (7 * 24 * 60 * 60 * 1000));
				const months= tour_package_calculator_month(first, final)
				
				document.getElementById(`tour-package-calc-upfront`).innerHTML = ((package_selection + extension + add_ons - 50) * 1.029).toFixed(2)
				jQuery(`.tour-package-calc-weeks`).html(weeks)
				document.getElementById(`tour-package-calc-weekly`).innerHTML = ((package_selection + extension + add_ons - 50) * 1.119 / weeks).toFixed(2)
				jQuery(`.tour-package-calc-months`).html(months)
				document.getElementById(`tour-package-calc-monthly`).innerHTML = ((package_selection + extension + add_ons - 50) * 1.119 / months).toFixed(2)
			}

			function tour_package_calculator_month(d1, d2) {
				var months
				months = (d2.getFullYear() - d1.getFullYear()) * 12
				months -= d1.getMonth()
				months += d2.getMonth()
				months = months <= 0 ? 0 : months
				return months + 1
			}
		</script>
	";
	return $html;
});

add_action('admin_menu', function () {
	add_menu_page('Tour Package Calculator', 'Tour Package Calculator', 'manage_options', 'tour-package-calculator', function () {

		$stored = null;
		if (isset($_POST['tour_package_calculator_update_option'])) {
			$stored = tour_package_calculator_option(str_replace('\"', '"', $_POST['tour_package_calculator_updated_option']));
		} else if (isset($_POST['tour_package_calculator_reset_option'])) {
			$stored = tour_package_calculator_option(json_encode(TOUR_PACKAGE_CALC_DEFAULT_DROPDOWNS));
		}

		if (!$stored) $stored = tour_package_calculator_option();
		if (!$stored) $stored = tour_package_calculator_option(json_encode(TOUR_PACKAGE_CALC_DEFAULT_DROPDOWNS));

		echo "
			<br>
			<br>
			<form method=\"POST\" id=\"tour_package_calculator_form\">
				<textarea style=\"display: none\" id=\"tour_package_calculator_updated_option\" name=\"tour_package_calculator_updated_option\" cols=\"100\" rows=\"5\">{$stored}</textarea>
				<br>
				<input id=\"tour_package_calculator_update_option\" name=\"tour_package_calculator_update_option\" type=\"submit\" class=\"button action\" value=\"Submit\">
				<input name=\"tour_package_calculator_reset_option\" type=\"submit\" class=\"button action\" value=\"Reset\">
			</form>
			<script type=\"text/javascript\">
				tour_package_calculator_to_inputs()
				function tour_package_calculator_to_inputs () {
					const form = jQuery(`#tour_package_calculator_form`)
					const json = JSON.parse(jQuery(`#tour_package_calculator_updated_option`).val())
					for (let select of json) {

						const name = select.name
						let placeholder = form.find(`#`+name)
						if (1 > placeholder.length) {
							jQuery(`<div class=\"tour_package_calculator_config_placeholder\" id=\"`+name+`\"><b>`+select.label+`</b></div><br>`).insertBefore(`#tour_package_calculator_update_option`)
							placeholder = form.find(`#`+name)
						} else placeholder.html(``)

						for (let option of select.options) {
							placeholder.append(`<br><span class=\"tour_package_calculator_config_option\">Text: <input class=\"text\" value=\"`+option.text+`\"> Price: <input class=\"value\" value=\"`+option.value+`\"></span>`)
						}
					}
				}

				function tour_package_calculator_to_json () {
					const json = JSON.parse(jQuery(`#tour_package_calculator_updated_option`).val())
					jQuery(`.tour_package_calculator_config_placeholder`).each(function() {
						const name = jQuery(this).attr(`id`)
						let options = []
						jQuery(this).find(`.tour_package_calculator_config_option`).each(function () {
							options.push({
								text: jQuery(this).find(`.text`).val(),
								value: jQuery(this).find(`.value`).val()
							})
						})
						for (let i in json) {
							if (json[i].name == name) json[i].options = options
						}
					})
					jQuery(`#tour_package_calculator_updated_option`).val(JSON.stringify(json))
					tour_package_calculator_to_inputs()
				}
				jQuery(`#tour_package_calculator_form`).submit(function () {
					tour_package_calculator_to_json()
					return true
				})
			</script>
		";
	});
});

function tour_package_calculator_option($option_value = null)
{
	global $wpdb;
	$option_table = "{$wpdb->prefix}options";
	$option_name = "tour-package-calculator-dropdowns";

	$option = $wpdb->get_var("SELECT option_value FROM {$option_table} WHERE option_name = '{$option_name}'");

	if (is_null($option_value)) return $option;
	else if (!$option) $wpdb->insert($option_table, [
		'option_name' => $option_name,
		'option_value' => $option_value
	]);
	else $wpdb->update($option_table, ['option_value' => $option_value], ['option_name' => $option_name]);

	return tour_package_calculator_option();
}

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
		],
		'note' => ''
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
		],
		'note' => ''
	],
	[
		'name' => 'add_ons',
		'label' => 'ADD ONS',
		'options' => [
			['text' => 'SINGLET', 'value' => 69],
			['text' => 'CHILD (12 & UNDER)', 'value' => 899],
			['text' => 'INFANT (24 MONTHS & UNDER)', 'value' => 199],
		],
		'note' => ''
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

	$html .= "<label><b>ADD ONS</b></label>";
	foreach ($dropdowns[2]['options'] as $add_on) {
		$html .= "<br>
			<select style=\"width: 10%; display: inline; margin-right: 10px\" onchange=\"javascript:tour_package_calculator();\" class=\"tour_package_calculator_add_ons\" data-price=\"{$add_on['value']}\">
				<option value=\"0\">0</option>
				<option value=\"1\">1</option>
				<option value=\"2\">2</option>
			</select>
			{$add_on['text']}<br>
		";
	}
	$html .= "<br>";

	$html .= "
		<link rel=\"stylesheet\" href=\"https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css\">
		<link rel=\"stylesheet\" href=\"/resources/demos/style.css\">
		<script src=\"https://code.jquery.com/jquery-3.7.1.js\"></script>
		<script src=\"https://code.jquery.com/ui/1.13.3/jquery-ui.js\"></script>
	";

	$html .= "<label><b>FIRST PAYMENT DATE</b></label> <input type=\"text\" id=\"tour-package-calc-first-payment-date\" onchange=\"javascript:tour_package_calculator();\" ><br>";

	$html .= "<br><label><b>FINAL PAYMENT DATE</b></label> <input type=\"text\" id=\"tour-package-calc-final-payment-date\" onchange=\"javascript:tour_package_calculator();\" ><br>";

	$html .= "<br><b>YOUR PACKAGE PAYMENT OPTIONS:</b>";
	$html .= "<br><b>UPFRONT</b> $51.95 DEPOSIT + ONE FINAL PAYMENT OF $<span id=\"tour-package-calc-upfront\">0</span>";
	$html .= "<br><b>WEEKLY</b> $51.95 DEPOSIT + <span class=\"tour-package-calc-weeks\"></span> WEEKLY INSTALMENTS OF $<span id=\"tour-package-calc-weekly\">0</span>";
	$html .= "<br><b>MONTHLY</b> $51.95  DEPOSIT + <span class=\"tour-package-calc-months\"></span> MONTHLY INSTALMENTS OF $<span id=\"tour-package-calc-monthly\">0</span>";

	$html .= "<br><br><i>The above instalments include the 2.8% credit card fee, and 9% admin fee for weekly or monthly instalments.</i>";

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
				let extension = parseFloat(document.getElementById(`extension`).value)
				if (-1 < jQuery(`#package_selection option:selected`).text().toLowerCase().indexOf(`couple`)) extension *= 2

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
		$mmq = asw('mmq', null);
		$ktl = asw('ktl', null);
		if (isset($_POST['tour_package_calculator_update_option'])) {
			$stored = tour_package_calculator_option(str_replace('\"', '"', $_POST['tour_package_calculator_updated_option']));
			$mmq = asw('mmq', $_POST['mmq']);
			$ktl = asw('ktl', $_POST['ktl']);
		} else if (isset($_POST['tour_package_calculator_reset_option'])) {
			$stored = tour_package_calculator_option(json_encode(TOUR_PACKAGE_CALC_DEFAULT_DROPDOWNS));
			$mmq = asw('mmq', '');
			$ktl = asw('ktl', '');
		}

		if (!$stored) $stored = tour_package_calculator_option();
		if (!$stored) $stored = tour_package_calculator_option(json_encode(TOUR_PACKAGE_CALC_DEFAULT_DROPDOWNS));

		echo "
			<br>
			<br>
			<form method=\"POST\" id=\"tour_package_calculator_form\">
				<textarea style=\"display: none\" id=\"tour_package_calculator_updated_option\" name=\"tour_package_calculator_updated_option\" cols=\"100\" rows=\"5\">{$stored}</textarea>

				<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css\">
				<style>.trumbowyg-editor{background: white; min-height: 275px !important}</style>
				<script type=\"text/javascript\" src=\"https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js\"></script>
				<textarea name=\"mmq\" class=\"tour-package-calculator-wysiwyg\">" . stripslashes($mmq) . "</textarea>
				<br>
				<br id=\"celeng\" >
				<textarea name=\"ktl\" class=\"tour-package-calculator-wysiwyg\">" . stripslashes($ktl) . "</textarea>
				<br>

				<input id=\"tour_package_calculator_update_option\" name=\"tour_package_calculator_update_option\" type=\"submit\" class=\"button action\" value=\"Submit\">
				<input name=\"tour_package_calculator_reset_option\" type=\"submit\" class=\"button action\" value=\"Reset\">
			</form>
			<script type=\"text/javascript\">
				jQuery(`.tour-package-calculator-wysiwyg`).trumbowyg()
				tour_package_calculator_to_inputs()
				function tour_package_calculator_to_inputs () {
					const form = jQuery(`#tour_package_calculator_form`)
					const json = JSON.parse(jQuery(`#tour_package_calculator_updated_option`).val())
					for (let select of json) {

						const name = select.name
						let placeholder = form.find(`#`+name)
						if (1 > placeholder.length) {
							jQuery(`
								<table class=\"tour_package_calculator_config_placeholder\" id=\"`+name+`\">
									<thead>
										<tr>
											<th style=\"text-align: left\"><b>`+select.label+`</b></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table><br>
							`).insertBefore(`#celeng`)
							placeholder = form.find(`#`+name)
						} else placeholder.html(``)

						for (let option of select.options) {
							placeholder.find(`tbody`).append(`
								<tr>
									<td class=\"tour_package_calculator_config_option\">
										Text: <input class=\"text\" value=\"`+option.text+`\"> Price: <input class=\"value\" value=\"`+option.value+`\">
									</td>
								</tr>
							`)
						}

						const rowspan = select.options.length
						const rows = rowspan + 2
						select.note = select.note.replaceAll(`\\\\n`, `\n`)
						placeholder.find(`tbody`).find(`tr`).eq(0).append(`
							<td rowspan=\"`+rowspan+`\">
								<textarea style=\"white-space: pre-wrap;\" rows=\"`+rows+`\">`+select.note+`</textarea>
							</td>
						`)

					}
				}

				function tour_package_calculator_to_json () {
					const json = JSON.parse(jQuery(`#tour_package_calculator_updated_option`).val())
					jQuery(`.tour_package_calculator_config_placeholder`).each(function() {
						const name = jQuery(this).attr(`id`)
						const note = jQuery(this).find(`textarea`).val()
						let options = []
						jQuery(this).find(`.tour_package_calculator_config_option`).each(function () {
							options.push({
								text: jQuery(this).find(`.text`).val(),
								value: jQuery(this).find(`.value`).val()
							})
						})
						for (let i in json) {
							if (json[i].name == name) {
								json[i].options = options
								json[i].note = note
							}
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

function asw($option_name, $option_value = null)
{
	global $wpdb;
	$option_table = "{$wpdb->prefix}options";

	$stored = $wpdb->get_var("SELECT option_value FROM {$option_table} WHERE option_name = {$option_name}");
	if (!$stored) {
		$wpdb->insert($option_table, [
			'option_name' => $option_name,
			'option_value' => ''
		]);
		$stored = '';
	}
	if (is_null($option_value)) $option_value = $stored;
	else $wpdb->update($option_table, ['option_value' => $option_value], ['option_name' => $option_name]);

	return $option_value;
}

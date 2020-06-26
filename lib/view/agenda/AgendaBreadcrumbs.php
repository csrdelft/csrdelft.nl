<?php

namespace CsrDelft\view\agenda;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/07/2019
 */
class AgendaBreadcrumbs {
	public static function getBreadcrumbs2($maand, $jaar) {
		return '<ol class="breadcrumb">'
			. '<li class="breadcrumb-item"><a href="/"><i class="fa fa-home"></i></a></li>'
			. '<li class="breadcrumb-item"><a href="/agenda" title="Agenda">Agenda</a></li>'
			. '<li class="breadcrumb-item">' . static::getDropDownYear($maand, $jaar) . '</li>'
			. '<li class="breadcrumb-item">' . static::getDropDownMonth($maand, $jaar) . '</li>'
			. '</ol>';
	}

	private static function getDropDownYear($maandHuidig, $jaarHuidig) {
		$dropdown = '<select onchange="location.href=this.value;">';
		$jaarMin = $jaarHuidig - 5;
		$jaarMax = $jaarHuidig + 5;
		for ($jaar = $jaarMin; $jaar <= $jaarMax; $jaar++) {
			$dropdown .= '<option value="/agenda/' . $jaar . '/' . $maandHuidig . '"';
			if ($jaar == $jaarHuidig) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . $jaar . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

	private static function getDropDownMonth($maandHuidig, $jaarHuidig) {
		$dropdown = '<select onchange="location.href=this.value;">';
		for ($maand = 1; $maand <= 12; $maand++) {
			$dropdown .= '<option value="/agenda/' . $jaarHuidig . '/' . $maand . '"';
			if ($maand == $maandHuidig) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . strftime('%B', strtotime($jaarHuidig . '-' . $maand . '-01')) . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}
}

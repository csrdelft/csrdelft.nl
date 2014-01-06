<?php
// Copyright (C) 1999, 2000, 2001, 2013 Lars Magne Ingebrigtsen
//
// Chart is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2, or (at your option)
// any later version.
//
// Chart is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Chart; see the file COPYING.  If not, write to the
// Free Software Foundation, Inc., 59 Temple Place - Suite 330,
// Boston, MA 02111-1307, USA.

require('rgb.php');

// The following variables should be edited to fit your 
// installation.

// If debugging is switched on, caching is switched off.
$chart_debug = false;

// The directory where Chart will store cached images. 
// Make sure this exists.
$chart_cache_directory = TMP_PATH;

// The default is to generate PNG images.  If you set
// this to false, GIF images will be generated instead.
// $chart_use_png = false;
$chart_use_png = true;

// If you want to use Type1 fonts, PHP has to be told
// where the IsoLatin1.enc file is.  Such a file is included
// in the Chart distribution.
$type1_font_encoding = LIB_PATH."/chart-0.8/IsoLatin1.enc";

// If your PHP is compiled with gd2, set this variable to true.
$gd2 = true;

function mod ($n, $m) {
  $n1 = (int)($n*1000);
  $m1 = (int)($m*1000);
  return ($n1 % $m1);
}

function alog ($number, $correction) {
  // It's unclear why the correction is applied -- it's to avoid
  // having a value between -1 and 1, for some reason or other.
  // For now, set to 0 and see what happens.
  $correction = 0;
  if ($number < 0)
    return -log(abs($number - $correction));
  else
    return log($number + $correction);
}

class chart {
  var $background_color = "white";
  var $background_from_color = 0, $background_to_color = 0;
  var $x_size, $y_size;
  var $output_x_size = false, $output_y_size;
  var $plots = array();
  var $image;
  var $left_margin = 30, $right_margin = 10, 
    $top_margin = 20, $bottom_margin = 21;
  var $margin_color = "white";
  var $border_color = "black", $border_width = 1;
  var $title_text = array(), $title_where = array(), $title_color = array(),
    $title_auto_y = 0;
  var $legends = array(), $legend_background_color = "white", 
    $legend_margin = 8, $legend_border_color = "black";
  var $legend_placement = "r"; // left or right
  var $legend_text_alignment = "l";
  var $axes = "xy", $axes_color = "black";
  var $grid_color = array(230, 230, 230), $grid_position = 0;
  var $tick_distance = 25;
  var $x_ticks = false, $x_ticks_format;
  var $scale = "linear";
  var $cache = false;
  var $x_label = false, $y_label = false;
  var $y_format = false;
  var $font = 2, $font_type = "internal", $font_name = 2, $font_size = 10;
  var $y_min = array(false), $y_max = array(false),
    $x_min = array(false), $x_max = array(false);
  var $frame = false;
  var $expired = false;
  var $marked_grid_point = false, $marked_grid_color = false;
  var $lockfd = -1, $lockfile = 0;
  var $tick_whole_x = false;
  var $months = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
  var $cleanup_after_plotting = true;
  var $do_draw_y_axis = true, $do_draw_x_axis = true;
  var $logarithmic_p = false;
  var $final_callback = false;
  var $do_draw_grid = true;
  var $output_data = false;
  var $jsonpart = false;
  var $data_ymin, $data_ymax, $data_height, $yoff;

  function mark_grid ($point = 0, $color = "red") {
    $this->marked_grid_point = $point;
    $this->marked_grid_color = $color;
  }

  function y_point ($yoff, $height, $ymin, $ymax, $y) {
    if ($this->logarithmic_p) {
      if ($ymax - $ymin > 2) 
	$c = 1;

      if ($y == 0)
	return $yoff + $height + (alog($ymin, $c) / 
				  (alog($ymax, $c) - alog($ymin, $c))) *
				    $height;
      else
	return $yoff + $height - ((alog($y, $c)*1.0 - alog($ymin, $c)) / 
				  (alog($ymax, $c) - alog($ymin, $c)) * 
				  $height);
    } else
      return $yoff + $height - (($y*1.0 - $ymin) / 
				($ymax - $ymin) * $height);
  }

  function set_grid_color ($color = false, $grid_under = true) {
      if ($color)
	$this->grid_color = $color;
      if ($grid_under)
	$this->grid_position = 0;
      else
	$this->grid_position = "last";
  }

  function set_output_size ($width, $height) {
    $this->output_x_size = $width;
    $this->output_y_size = $height;
  }

  function set_expired ($expired) {
    $this->expired = $expired;
  }

  function set_margins ($left = 30, $right = 10, 
			$top = 20, $bottom = 23) {
    $this->left_margin = $left;
    $this->right_margin = $right;
    $this->top_margin = $top;
    $this->bottom_margin = $bottom;
  }

  function set_tick_distance ($distance) {
    $this->tick_distance = $distance;
  }

  function set_labels ($x = false, $y = false) {
    $this->x_label = $x;
    $this->y_label = $y;
  }

  function set_extrema ($y_min = array(), $y_max = array(),
			$x_min = array(), $x_max = array()) {
    if (! is_array($y_min))
      $this->y_min = $y_min;
    if (! is_array($y_max))
      $this->y_max = $y_max;
    if (! is_array($x_min))
      $this->x_min = $x_min;
    if (! is_array($x_max))
      $this->x_max = $x_max;
  }

  function output_json () {
    $file = $this->cache . ".data";
    if (file_exists($file)) {
      $fp = fopen($file, "r");
      if ($fp) {
	header("Content-type: text/plain");
	echo "jsonCallback({ \"callback\": $this->jsonpart, ";
	fpassthru($fp);
      }
      exit;
    }
  }

  function chart ($x, $y, $cache = false, $jsonpart = false) {
    global $chart_debug;
    error_reporting(1);
    if ($jsonpart && $cache) {
      $this->set_cache_file_name($cache);
      $this->jsonpart = $jsonpart;
      if (! $chart_debug)
	$this->output_json();
    }
    // If this image has already been cached, then we just spew
    // it out and exit.
    if ($cache)
      $this->get_cache($cache);
    // If not, we initialize this object and allow execution to continue.
    $this->x_size = $x;
    $this->y_size = $y;
  }

  function set_cache_file_name ($file) {
    global $chart_cache_directory;
    $file = $chart_cache_directory . "/" . $file;
    // There probably is a security problem hereabouts.  Just
    // transforming all ".."'s into "__" and "//"'s into "/_" will 
    // probably help, though.
    while (ereg("[.][.]", $file)) 
      $file = ereg_replace("[.][.]", "__", $file);
    while (ereg("//", $file)) 
      $file = ereg_replace("//", "/_", $file);
    $this->cache = $file;
  }

  function get_cache ($file) {
    global $chart_debug, $chart_use_png;
    $this->set_cache_file_name($file);
    $file = $this->cache;
    if (! $chart_debug) {
	while (true) {
	    if (file_exists($file)) {
		// The chart is already in the cache, so we just serve
		// it out.
		if ($file = fopen($file, "rb")) {
		    $this->headers();
		    fpassthru($file);
		    exit;
		} 
	    } else {
		// The idea here is to obtain a lock on the file to be
		// written before starting to write it.  That way we
		// can ensure that no chart is ever generated more
		// than once, which can be very important if the chart
		// is CPU intensive.
		$this->make_directory(dirname($file));
		$this->lockfile = "$file.lock";
		$lockfd = fopen($this->lockfile, "a");
		$tries = 0;
		while (! flock($lockfd, 2+4) && $tries++ < 20 &&
		       ! file_exists($file)) {
		    sleep(1);
		}
		// If the file now exists, we serve it out and exit.
		if (file_exists($file) && ($file = fopen($file, "rb"))) {
		  fclose($lockfd);
		  if ($this->jsonpart) {
		    $this->output_json();
		    exit;
		  }
		  $this->headers();
		  fpassthru($file);
		  exit;
		}
		if ($tries >= 20) {
		    // We tried more than 20 seconds, so we delete the
		    // lock file, and try again.
		    fclose($lockfd);
		    if (file_exists($this->lockfile))
		      unlink($this->lockfile);
		} else {
		    // We got the lock, so we break from the loop and 
		    // return, and generate the chart ourselves.
		    $this->lockfd = $lockfd;
		    break;
		}
	    }
	}
    }
    return false;
  }
  
  function put_cache ($image) {
    global $chart_use_png;
    $file = $this->cache;
    if (file_exists($file))
      unlink($file);
    $this->make_directory(dirname($file));
    // Writing to a tmp file, and then renaming the tmp file to the
    // real file gives us some atomicity on most sensible file systems.
    // That is, the real file will never exist in a half-written state.
    if ($chart_use_png)
      imagepng($image, "$file.tmp");
    else
      imagegif($image, "$file.tmp");
    if (file_exists("$file.tmp")) 
      rename("$file.tmp", "$file");
    imagedestroy($image);
    
    // Remove the lock file.
    if ($this->lockfd != -1) {
	fclose($this->lockfd);
	unlink($this->lockfile);
    }

    if ($this->jsonpart) {
      $this->output_json();
      exit;
    }

    if ($file = fopen($file, "rb")) {
      $this->headers();
      fpassthru($file);
      exit;
    } 
    return true;
  }
  
  function make_directory ($file) {
    while (! (file_exists($file))) {
      $dirs[] = $file;
      $file = dirname($file);
    }
    for ($i = sizeof($dirs)-1; $i>=0; $i--) {
      if (strlen(basename($dirs[$i])) > 256) {
	echo "Too long file name";
	exit;
      } 
      if (! file_exists($dirs[$i]))
	@mkdir($dirs[$i], 0777);
    }
  }

  function set_border ($color = "black", $width = 1) {
    $this->border_color = $color;
    $this->border_width = $width;
  }

  function set_background_color ($color, $margin_color = false) {
    $this->background_color = $color;
    if ($margin_color)
      $this->margin_color = $margin_color;
  }

  function set_x_ticks ($ticks, $format = "date") {
    $this->x_ticks = $ticks;
    $this->x_ticks_format = $format;
  }

  function set_frame ($frame = true) {
    $this->frame = $frame;
  }

  function set_font ($font, $type = 0, $size = false) {
    $this->font_name = $font;
    $this->font_type = $type;
    if ($size)
      $this->font_size = $size;
  }

  function set_title ($title, $color = "black", $where = "center") {
    $this->title_text[] = $title;
    $this->title_where[] = $where;
    $this->title_color[] = $color;
  }

  function add_legend($string, $color = "black") {
    $this->legends[] = array($string, $color);
  }

  function set_axes ($which = "xy", $color = "black") {
    $this->axes = $which;
    $this->axes_color = $color;
  }

  function &plot ($c1, $c2 = false, $color = false, $style = false,
		 $to_color = false, $param = false,
		 $texts = false) {
    if ($c2 && $this->x_ticks_format == "date_2d") {
	/* Convert list of dates to list of days-since-19700101 in
	   order to get monotone base 10 numbers.*/
	$c2b = array();
	foreach ($c2 as $date) {
	    $c2b[] = $this->datadatetotime($date)/(24*60*60);
	}
	$c2 = $c2b;
    }

    $plot = new plot($c1, $c2);
    if ($color)
      $plot->set_color($color);
    if ($to_color)
      $plot->set_gradient_color($to_color, $param);
    if ($style)
      $plot->set_style($style);
    if ($texts)
      $plot->set_texts($texts);
    if ($param)
      $plot->set_param($param);
    $this->plots[] = &$plot;
    return $plot;
  }

  function splot ($plot) {
    $this->plots[] = &$plot;
  }

  function stroke ($callback = false) {
    global $chart_use_png, $type1_font_encoding;
    $xs = $this->x_size;
    $ys = $this->y_size;

    // Load the font for this chart.
    if ($this->font_type == "type1") {
	$this->font = imagepsloadfont($this->font_name);
	imagepsencodefont($this->font, $type1_font_encoding);
    } elseif ($this->font_type == "ttf") {
	$this->font = imagettfloadfont($this->font_name);
    } else {
	$this->font = $this->font_name;
    }

    if ($xs == 0 || $ys == 0) {
      printf("Invalid X or Y sizes: (%s, %s)", $xs, $ys);
      exit;
    }
    $im = imagecreate($xs, $ys);
    if (! $im)
      exit;
    $this->image = $im;
    
    if ($this->background_from_color) {
      $cs = rgb_allocate_colors($im, $this->background_from_color,
				$this->background_to_color,
				10, 100);
      if ($cs) {
	foreach ($cs as $c)
	  $colors[] = $c;
      }
      for ($i = 0; $i < $ys; $i++) {
	$color = $colors[$i / $ys * sizeof($colors)];
	imageline($im, 0, $i, $xs, $i, $color);
      }
    } else {
      $bgcolor = $this->allocate_color($this->background_color);
      imagefilledrectangle($im, 0, 0, $xs, $ys, $bgcolor);
    }

    list ($xmin, $xmax) = $this->get_extrema(2);

    if (is_array($this->y_min) || is_array($this->y_max)) {
      list ($ymin, $ymax) = $this->get_extrema(1);
      // If we're doing a logarithmic plot that just about touches 0, then 
      // it looks really ugly if we extend the chart below 0.  So don't
      // do that, then.
      if ($this->logarithmic_p && $ymin < 1)
	$grace = 0;
      else 
	$grace = ($ymax-$ymin)*0.01;
      $ymin -= $grace;
      $ymax += $grace;
    }

    if (! is_array($this->y_min))
      $ymin = $this->y_min;
    if (! is_array($this->y_max))
      $ymax = $this->y_max;
    if (! is_array($this->x_min))
      $xmin = $this->x_min;
    if (! is_array($this->x_max))
      $xmax = $this->x_max;

    if ($ymax == $ymin) {
      $ymax *= 1.01;
      $ymin *= 0.99;
    }
    if ($xmax == $xmin) 
      $xmax++;
    if ($ymax == $ymin) 
      $ymax++;

    $xoff = $this->left_margin;
    $yoff = $this->top_margin;
    $width = $xs - $this->left_margin - $this->right_margin;
    $height = $ys - $this->top_margin - $this->bottom_margin;

    $axes_color = $this->allocate_color($this->axes_color);

    if (! $this->cleanup_after_plotting) {
      $margin = $this->allocate_color($this->margin_color);
      imagefilledrectangle($im, 0, 0, $xs, $this->top_margin-1, $margin);
      imagefilledrectangle($im, $xs-$this->right_margin+1, $this->top_margin-1,
			   $xs, $ys, $margin);
      imagefilledrectangle($im, 0, $ys-$this->bottom_margin+1, $xs, $ys, 
			   $margin);
      imagefilledrectangle($im, 0, 0, $this->left_margin-1, $ys, $margin);
    }
    
    // Go through all the plots and stroke them.
    if ($callback != false) {
      if ($this->grid_position == 0)
	$this->draw_grid($xmin, $xmax, $ymin, $ymax);
      $callback($im, $xmin, $xmax, $ymin, $ymax,
		$xoff, $yoff, $width, $height);
    } else {
      for ($i = 0; $i < sizeof($this->plots); $i++) {
	if ($this->grid_position == $i)
	  $this->draw_grid($xmin, $xmax, $ymin, $ymax);
	$plot = &$this->plots[$i];
	$plot->stroke($im, $xmin, $xmax, $ymin, $ymax,
		      $xoff, $yoff, $width, $height, &$this);
      }
    }

    if (! strcmp($this->grid_position, "last"))
      $this->draw_grid($xmin, $xmax, $ymin, $ymax);

    // The plotting may have plotted outside of the allocated
    // "framed" area (if autoscaling is not in use), so we
    // blank out the surrounding area.
    if ($this->cleanup_after_plotting) {
      $margin = $this->allocate_color($this->margin_color);
      imagefilledrectangle($im, 0, 0, $xs, $this->top_margin-1, $margin);
      imagefilledrectangle($im, $xs-$this->right_margin+1, $this->top_margin-1,
			   $xs, $ys, $margin);
      imagefilledrectangle($im, 0, $ys-$this->bottom_margin+1, $xs, $ys, 
			   $margin);
      imagefilledrectangle($im, 0, 0, $this->left_margin-1, $ys, $margin);
    }
    
    if (! $this->frame) {
      if ($this->border_color) {
	imageline($im, $this->left_margin, $this->top_margin, 
		  $this->left_margin, $ys-$this->bottom_margin+3, $axes_color);
	imageline($im, $this->left_margin-3, $ys-$this->bottom_margin,
		  $xs-$this->right_margin, $ys-$this->bottom_margin,
		  $axes_color);
      }
    } else {
      imagerectangle($im, $this->left_margin, $this->top_margin, 
		     $xs-$this->right_margin, $ys-$this->bottom_margin, 
		     $this->allocate_color($this->border_color));
    }

    // Put the text onto the axes.
    if ($this->do_draw_y_axis)
      $this->draw_y_axis($im, $ymin, $ymax, $xs, $ys, $height, $yoff, true,
			 $axes_color);
    if ($this->do_draw_x_axis)
      $this->draw_x_axis($im, $xmin, $xmax, $xs, $ys, $width, $xoff, true,
			 $axes_color);

    $title_color = $this->allocate_color("black");

    // Draw the labels, if any.
    if ($this->y_label) {
      if ($this->font_type == "type1") {
	imagepstext ($im, $this->y_label, $this->font, $this->font_size, 
		     $this->allocate_color($title_color),
		     $this->allocate_color("white"),
		     15, (int)($ys/2+$this->string_pixels($this->y_label)/2),
		     0, 0, 90, 16);
      } else {
	imagestringup($im, $this->font, 5,
		      $ys/2+$this->string_pixels($this->y_label)/2,
		      $this->y_label, $title_color);
      }
    }
    if ($this->x_label) 
      imagestring($im, $this->font,
		  $xs/2-$this->string_pixels($this->x_label)/2,
		  $ys-20, $this->x_label, $title_color);

    // Draw the boorder.
    if ($this->border_color) 
      imagerectangle($im, 0, 0, $xs-1, $ys-1, 
		     $this->allocate_color($this->border_color));

    // Draw the title.
    $tx = "noval";
    for ($i=0; $i<sizeof($this->title_text); $i++) {
      if ($this->font_type == "type1") {
	if ($tx == "noval") {
	  if (!strcmp($this->title_where[$i], "center")) {
	    list ($llx, $lly, $urx, $ury) = imagepsbbox($this->title_text[$i],
							$this->font, 
							$this->font_size);
	    $tx = $xs/2 - ($urx-$llx)/2;
	    if ($this->title_auto_y)
	      $ty = $ury-$lly + 2;
	    else
	      $ty = 15;
	  } else 
	    $tx = 0;
	}

	imagepstext ($im, $this->title_text[$i], $this->font, 
		     $this->font_size, 
		     $this->allocate_color($this->title_color[$i]),
		     $this->allocate_color("white"),
		     (int)$tx, (int)$ty,
		     0, 0, 0, 16);
      } elseif ($this->font_type == "internal") {
	if (!strcmp($this->title_where[$i], "center")) 
	  $tx = $xs/2 - $this->string_pixels($this->title_text[$i])/2;
	else 
	  $tx = 0;
	
	imagestring($im, $this->font, $tx, 5, $this->title_text[$i], 
		    $this->allocate_color($this->title_color[$i]));
      }
    }

    // Draw the legend.
    if (sizeof($this->legends) != 0) {
      $maxlength = 0;
      foreach ($this->legends as $legend) {
	$length = $this->real_string_pixels($legend[0]);
	if ($length > $maxlength)
	  $maxlength = $length;
      }

      if ($this->legend_placement == "r") {
	$x = (int)($this->x_size - $this->right_margin - $maxlength - 20);
	$y = (int)($this->top_margin + 20);
      } else {
	$x = (int)($this->left_margin + 40);
	$y = (int)($this->top_margin + 20);
      }
      $lmargin = $this->legend_margin;
      // Draw a box behind the legend.
      if ($this->legend_background_color) {
	imagefilledrectangle($im, $x-$lmargin, $y-$lmargin,
			     $x+$lmargin+$maxlength, 
			     $y+$lmargin+
			     (($this->font_size+2)*sizeof($this->legends)),
			     $this->allocate_color($this->legend_background_color));
      }
      if ($this->legend_border_color) {
	imagerectangle($im, $x-$lmargin, $y-$lmargin,
		       $x+$lmargin+$maxlength, 
		       $y+$lmargin+
		       (($this->font_size+2)*sizeof($this->legends)),
		       $this->allocate_color($this->legend_border_color));
      }
      foreach ($this->legends as $legend) {
	if ($this->legend_text_alignment == "l")
	  $this->draw_text($legend[0], $legend[1], $x, $y);
	else 
	  $this->draw_text($legend[0], $legend[1], 
			   $x + $maxlength -
			   $this->real_string_pixels($legend[0]), 
			   $y);
	$y += $this->font_size+2;
      }
    }

    // Rescale the image before outputting, if requested.
    if ($this->output_x_size) {
      global $gd2;
      $owidth = $this->output_x_size;
      $oheight = $this->output_x_size;
      $om = imagecreate($owidth, $oheight);
      if (! $om)
	exit;
      if ($gd2)
	imagecopyresampled($om, $im, 0, 0, 0, 0,
			   $owidth, $oheight, $xs, $ys);
      else
	imagecopyresized($om, $im, 0, 0, 0, 0,
			 $owidth, $oheight, $xs, $ys);
      $im = $om;
    }

    // Allow the user to do a final modification.
    if ($this->final_callback) {
      $call = $this->final_callback;
      $call($im, $xmin, $xmax, $ymin, $ymax,
	    $xoff, $yoff, $width, $height, &$this);
      // The callback may have replaced the image.
      $im = $this->image;
    }

    if ($this->output_data && $this->cache)
      $this->output_data_file();

    // This statement usually doesn't return.
    if ($this->cache) 
      $this->put_cache($im);

    $this->headers();
    if ($chart_use_png)
      imagepng($im);
    else
      imagegif($im);

    imagedestroy($im);
    return true;
  }

  function output_data_file () {
    $first = true;
    $file = $this->cache . ".data";
    if (file_exists($file))
      unlink($file);
    $this->make_directory(dirname($file));
    $fp = fopen($file, "w");
    if (! $fp)
      exit;
    fwrite($fp, "\"ymin\": $this->ymin, \"ymax\": $this->ymax, \"height\": $this->height, \"yoff\": $this->yoff, ");
    fwrite($fp, "\"values\": [ ");
    $prev_x = -1;
    if ($this->output_data[1]) {
      foreach ($this->output_data[1] as $x => $elem)
	$additional[] = $elem[0];
    }
    $index = 0;
    foreach ($this->output_data[0] as $x => $elem) {
      if ($x - $prev_x < 2)
	continue;
      $prev_x = $x;
      if (! $first)
	fwrite($fp, ",");
      else
	$first = false;
      $value = $elem[0];
      if (sprintf("%.2f", $value) == $value)
	$value = sprintf("%.2f", $value);
      if ($additional)
	$extra = ",\"" . $additional[$index] . "\"";
      fwrite($fp, "[" . $x . ",\"" . $value . "\",\"" . 
	     date("d.m.Y", $this->datadatetotime($elem[1])) .
	     "\"" . $extra . "]");
      $index++;
    }
    fwrite($fp, "]});");
    fclose($fp);
  }

  function headers () {
    global $chart_use_png;
    if ($this->expired) {
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
      header("Cache-Control: no-cache, must-revalidate");
      header("Pragma: no-cache");
    }
    if ($chart_use_png)
      header("Content-type: image/png");
    else
      header("Content-type: image/gif");
  }

  function datadatetotime ($datatime) {
    if (ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $datatime, $regs)) { 
      return mktime (1, 0, 0,
		     $regs[2], $regs[3], $regs[1]);
    }
    return 0;
  }

  function shortnorwegiandate ($time) {
    return date("d.m.y", $time);
  }

  function shortnorwegianmdate ($time) {
    $monthnames = array("januar", "februar", "mars", "april", "mai", "juni",
			"juli", "august", "september", "oktober", "november",
			"desember");
    if (date("d", $time) == 1) {
#      return substr($monthnames[date("m", $time)-1], 0, 3) . " " . date("Y", $time);
      return $monthnames[date("m", $time)-1];
    } else {
      return date("d.m.y", $time);
    }
  }

  function secondtosecond($s) {
    return substr($s, 0, 2)*60*60+substr($s, 2, 2)*60+substr($s, 4, 2);
  }

  function month_length ($month, $year) {
    $length = $this->months[$month-1];
    // Special rules for February.
    if ($month == 2) {
      if (!($year % 4) && ($year % 100 || !($year % 400)))
	$length = 29;
    }
    return $length;
  }

  function draw_grid ($xmin, $xmax, $ymin, $ymax) {
    // Draw the grid and the axes.
    $im = $this->image;
    $xs = $this->x_size;
    $ys = $this->y_size;
    $xoff = $this->left_margin;
    $yoff = $this->top_margin;
    $width = $xs - $this->left_margin - $this->right_margin;
    $height = $ys - $this->top_margin - $this->bottom_margin;
    $axes_color = $this->allocate_color($this->axes_color);
    if ($this->do_draw_grid) 
      $this->draw_y_axis($im, $ymin, $ymax, $xs, $ys, $height, $yoff, false,
			 $axes_color);
    $this->draw_marked_grid($im, $ymin, $ymax, $xs, $ys, $height, $yoff);
    if ($this->do_draw_grid) 
      $this->draw_x_axis($im, $xmin, $xmax, $xs, $ys, $width, $xoff, false,
			 $axes_color);
  }

  function draw_x_axis ($im, $xmin, $xmax, $xs, $ys, $width,
			$xoff, $do_text, $axes_color) {
    if ($this->do_draw_x_axis == false && $do_text)
      return;
    $grid_color = $this->allocate_color($this->grid_color);
    $do_tick_texts = false;
    $thinning_factor = 70;
    $number_unsuitable = 0;
    $used_ticks_counter = 0;
    if (!(strcmp($this->axes, "x")) || !(strcmp($this->axes, "xy"))) {
      if ($this->x_ticks_format == "time" ||
	  $this->x_ticks_format == "seconds" ) {
	$do_tick_texts = true;
	$thinning_factor = 40;
	if ($this->x_ticks_format == "time") {
	    $start = $this->secondtosecond($this->x_ticks[0]);
	    $end = $this->secondtosecond($this->x_ticks[sizeof($this->x_ticks)-1]);
	} else {
	    $start = $this->x_ticks[0];
	    $end = $this->x_ticks[sizeof($this->x_ticks)-1];
	}
	$xmax = sizeof($this->x_ticks)-1;
	$xmin = 0;
	$duration = $end-$start;
	if ($duration == 0)
	  return;
	$scale = ($xmax-$xmin)/$duration;
	if ($duration < 10*60) {
	  $step = 60;
	} elseif ($duration < 30*60) {
	  $step = 60*5;
	} elseif ($duration < 2*60*60) {
	  $step = 60*10;
	} elseif ($duration < 4*60*60) {
	  $step = 60*30;
	} else {
	  $step = 3600;
	  if ($duration < 10*60*60)
	    $this->tick_whole_x = true;
	}

	// If the start/end positions are near "pretty"
	// numbers, then we round up/down.
	if ((! $start%$step) || (($start%$step) / ($end-$start)) > 0.01) 
	  $kstart = $start;
	else
	  $kstart = $start-($start%$step);

	if ((! $end%$step) || ($step-$end%$step) / ($end-$kstart) > 0.01)
	  $kend = $end;
	else
	  $kend = $end-$end%$step+$step;

	// See if we can start at an hour, if possible.
	if ($step == 3600 && $kstart%3600 != 0 && $kstart+3600 < $end)
	  $kstart += 3600-$kstart%3600;

	for ($hour = $kstart; $hour <= $kend; $hour += $step) {
	  $ticks[] = (($hour-$start)*$scale);
	  if ($step == 3600 && $hour%3600 == 0) 
	    $tick_texts[] = (int)($hour/3600);
	  else 
	    $tick_texts[] = sprintf("%02d:%02d", 
				    (int)($hour/3600), ($hour%3600/60));
	}
      } else if ($this->x_ticks_format == "price") {
	$do_tick_texts = true;
	$start = (int)($this->x_ticks[0] * 10000);
	$end = (int)($this->x_ticks[sizeof($this->x_ticks)-1] * 10000);
	$xmax = sizeof($this->x_ticks)-1;
	$xmin = 0;
	$range = $end-$start;
	if ($range == 0)
	  return;
	$scale = ($xmax-$xmin)/$range;
	if ($range < 10000)
	  $step = 1000;
	elseif ($range < 10 * 10000)
	  $step = 5000;
	else
	  $step = 10000;

	// If the start/end positions are near "pretty"
	// numbers, then we round up/down.
	if ((! $start%$step) || (($start%$step) / ($end-$start)) > 10000) 
	  $kstart = $start;
	else
	  $kstart = $start-($start%$step);

	for ($price = $kstart; $price <= $end; $price += $step) {
	  $ticks[] = ($price-$start)*$scale;
	  $tick_texts[] = sprintf("%.2f", $price / 10000);
	}
      } elseif ($this->x_ticks_format == "date" ||
		$this->x_ticks_format == "date_2d" ||
		$this->x_ticks_format == "sdate" ||
		$this->x_ticks_format == "sparse_date") {
	$do_tick_texts = true;
	$thinning_factor = 60;
	$start = $this->datadatetotime($this->x_ticks[0])/(24*60*60);
	$end = $this->datadatetotime($this->x_ticks[sizeof($this->x_ticks)-1])/
	    (24*60*60);
	$duration = $end-$start;
	$gdate = getdate($start*(24*60*60));
	$month = $gdate["mon"];
	$mday = $gdate["mday"];
	$wday = $gdate["wday"];
	$year = $gdate["year"];
	$mlength = $this->month_length($month, $year);
	$first = true;
	for ($i = $start; $i < $end; $i++) {
	  if ($mday == 1) 
	    $firsts[] = $i;
	  if ($mday == 1 || $mday == 15) {
	    if ($first) {
	      $first = false;
	      if ($mday == 15)
		$number_unsuitable = 1;
	    }
	    $mids[] = $i;
	  } if ($wday == 1) 
	    $mondays[] = $i;
	  if ($month == 1 && $mday == 1)
	    $first_januarys[] = $i;
	  if ($mday++ > $mlength-1) {
	    if ($month++ > 12-1) {
	      $month = 1;
	      $year++;
	    }
	    $mlength = $this->month_length($month, $year);
	    $mday = 1;
	  }
	  if ($wday++ > 7-1)
	    $wday = 1;
	}

	$wdformat = false;

	if ($duration > 0) {
	  if ($this->x_ticks_format == "sparse_date")
	    $scale = ($xmax-$xmin+1)/sizeof($this->x_ticks);
	  else
	    $scale = ($xmax-$xmin)/$duration;
	} else
	  $scale = 1;

	if ($duration < 24) {
	  for ($i = $start; $i<$end; $i++) 
	    $dates[] = $i;
	} elseif ($duration < 62) {
	  $dates = $mondays;
	  $wdformat = true;
	} elseif ($duration < 31*6) {
	  $dates = $mids;
	} elseif ($duration < 365*2) {
	  $dates = $firsts;
	} else {
	  $dates = $first_januarys;
	}

	for ($i = 0; $i<sizeof($dates); $i++) {
	  if ($this->x_ticks_format == "date_2d") {
	    /* Get scale right when using an array of dates as second arg
	       arg to $this->plot() */
	    $ticks[] = (($dates[$i])*$scale);
	  } else if ($this->x_ticks_format == "sparse_date") {
	    /* The idea here is that we want to pick pleasing dates,
	       but we also want to have them distributed non-evenly.
	       That is, skip dates that aren't in the data set. */
	    while (($tick_date = 
		    $this->datadatetotime($this->x_ticks[$used_ticks_counter])/(24*60*60)) != 0 &&
		   $tick_date <= $dates[$i]) {
	      $used_ticks_counter++;
	    }
	    $ticks[] = ($used_ticks_counter - 1) * $scale;
	  } else {
	    $ticks[] = (($dates[$i]-$start)*$scale);
	  }
	  if (! $wdformat) 
	    $tick_texts[] = $this->shortnorwegiandate($dates[$i]*24*60*60);
	  else {
	    if ($this->x_ticks_format == "sdate") 
	      $tick_texts[] = date("d.m", $dates[$i]*24*60*60);
	    else
	      $tick_texts[] = "Ma " . 
		$this->shortnorwegiandate($dates[$i]*24*60*60);
	  }
	}
      } else {
	$ticks = $this->get_ticks($xmin, $xmax, $xs);
      }

      if (! $this->tick_whole_x) {
	if ($do_tick_texts)
	  $thinning_factor = $this->string_pixels($tick_texts[0]) + 6;

        $step = ceil(sizeof($ticks)*1.0 / ($width/$thinning_factor));
      } else
        $step = 1;

      $ticklength = sizeof($ticks);
      for ($i = 0; $i < $ticklength; $i += 1) {
	$x = $ticks[$i];
	$xt = $xoff + ($x - $xmin) / ($xmax - $xmin) * $width;
	if ($do_text && $xt >= $xoff) {
	  if ((($i+$number_unsuitable) % $step) == 0) {
	    if ($do_tick_texts) {
	      $text = $tick_texts[$i];
	    } elseif ($this->x_ticks) {
	      if (!strcmp($this->x_ticks_format, "date")) {
		$text = $this->shortnorwegiandate
		  ($this->datadatetotime($this->x_ticks[$x]));
	      }	elseif (!strcmp($this->x_ticks_format, "sdate")) {
		$text = 
		  date("d.m", ($this->datadatetotime($this->x_ticks[$x])));
	      } elseif (!strcmp($this->x_ticks_format, "time")) {
		$text = $this->x_ticks["$x"];
	        $text = substr($text, 0, 2) . ":" . substr($text, 2, 2);
	      } elseif (!strcmp($this->x_ticks_format, "ctime")) {
		$dtext = $text = date("m/d", $x);
		if (isset($odtext) && $text != $odtext) 
		  $text .= " " . date("G:i", $x); 
		else 
		  $text= date("G:i", $x);
		$odtext=$dtext;
	      } elseif (!strcmp($this->x_ticks_format, "cdate")) {
		$text= date("m/d", $x);
	      } elseif (!strcmp($this->x_ticks_format, "text")) {
                $text = $this->x_ticks["$x"];
              }
	    } elseif ($this->x_ticks_format == "none") {
	      $text = "";
	    } else {
	      $text = $x;
	    }
	    if ($this->font_type == "type1") {
	      imagepstext($im, $text, $this->font, $this->font_size, 
			  $axes_color,
			  $this->allocate_color("white"),
			  (int)($xt-(strlen($text)*$this->font_size/4)+0),
			  (int)($ys-$this->bottom_margin+$this->font_size + 5),
			  0, 0, 0, 16);
	    } elseif ($this->font_type == "internal") {
	      imagestring($im, $this->font, $xt-(strlen($text)*6/2),
			  $ys-$this->bottom_margin+5, $text, $axes_color);
	    }
	    imageline($im, $xt, $ys-$this->bottom_margin, 
		      $xt, $ys-$this->bottom_margin+3, $axes_color);
	  } else {
	    imageline($im, $xt, $ys-$this->bottom_margin, 
		      $xt, $ys-$this->bottom_margin+1, $axes_color);
	  }
	} else {
	  if ($xt > $this->left_margin)
	    imageline($im, $xt, $this->top_margin, 
		      $xt, $ys-$this->bottom_margin-1, $grid_color);
	}
      }
    }      
  }

  function pleasing_numbers ($number, $series = 0, $minimum = 0) {
    $one = 0.001;
    $two = 0.002;
    $five = 0.005;
    while (true) {
      if ($number < $one && ($series == 0 || $series == 1) && $one >= $minimum)
	return array($one, 1);
      $one *= 10;
      if ($number < $two && ($series == 0 || $series == 2) && $two >= $minimum)
	return array($two, 2);
      $two *= 10;
      if ($number < $five && ($series == 0 || $series == 5) &&
	  $five >= $minimum)
	return array($five, 5);
      $five *= 10;
    }
  }

  function draw_text ($string, $color, $x, $y) {
    if ($this->font_type == "type1") {
      imagepstext($this->image, $string, $this->font, $this->font_size, 
		  $this->allocate_color($color),
		  $this->allocate_color($this->background_color),
		  $x, $y+$this->font_size-2, 
		  0, 0, 0, 16);
    } elseif ($this->font_type == "internal") {
      imagestring($this->image, $this->font, $x, $y, $string, 
		  $this->allocate_color($color));
    }
  }

  function draw_y_axis ($im, $ymin, $ymax, $xs, $ys,
			$height, $yoff, $do_text, $axes_color) {
    if ($this->do_draw_y_axis == false && $do_text)
      return;
    if (!(strcmp($this->axes, "y") || strcmp($this->axes, "xy"))) 
      return;

    $grid_color = $this->allocate_color($this->grid_color);
    // Compute the Y axis.
    $ticks = $this->get_ticks($ymin, $ymax, $height);
    $length = sizeof($ticks);
    $whole = true;
    $ideal = $height/$this->font_size + 1;
    if ($ideal >= $length) {
      $factor = .1;
      $valfactor = .1;
    } else {
      list($factor, $series) =
	$this->pleasing_numbers(ceil($length/$ideal));
      /* If we get a too big factor here, we decrease it. */
      if ($length / $factor < 2) 
	list($factor, $series) =
	  $this->pleasing_numbers(ceil($length/$ideal) / 2);
      
      list($valfactor) = $this->pleasing_numbers(($ymax-$ymin)/100, $series);
    }
    $spacing = abs($ticks[1] - $ticks[0]);
    for ($i = 0; $i < $length*2; $i++) {
      $y = $ticks[0] + $spacing*$i;
      if (! mod($y, $factor)) {
	$offset = mod($i, $factor)/1000;
	break;
      }
    }
    
    $iy = -1;
    $print_real = false;
    
    for ($i = 0; $i < $length; $i += 1) {
      $y = $ticks[$i];
      $yt = $this->y_point($yoff, $height, $ymin, $ymax, $y);
      
      if ($do_text) {
	if ($i >= $offset) 
	  $iy++;
	
	if ((! ((int)($iy*1000)%($factor*1000))) && ($iy > -1)) {
	  
	  if (ceil($spacing*100) == 10 || ceil($spacing*100) == 20)
	    $yst = sprintf("%.1f", $y);
	  else if ($spacing < 1 && ! mod($spacing*10, 1))
	    $yst = sprintf("%.1f", $y);
	  elseif (abs($spacing) < 0.01) 
	    $yst = sprintf("%.3f", $y);
	  elseif (abs($spacing) < 1) 
	    $yst = sprintf("%.2f", $y);
	  elseif (!($spacing % 1000000000)) 
	    $yst = sprintf("%dG",  $y / 1000000000);
	  elseif (!($spacing % 1000000)) 
	    $yst = sprintf("%dM",  $y / 1000000);
	  elseif (!($spacing % 1000))
	    $yst = sprintf("%dk",  $y / 1000);
	  elseif (! $whole)
	    $yst = sprintf("%.1f", $y);
	  else 
	    $yst = $y;
	  
	  if (strlen($yst) > 5) 
	    $yst = (int) $yst;
	  
	  if ($this->y_format == "percent")
	    $yst .= "%";
	  
	  if (($y*10)%10)
	    $whole = false;
	  
	  if ($this->font_type == "type1") {
	    list ($llx, $lly, $urx, $ury) = 
	      imagepsbbox("$yst", $this->font, $this->font_size);
	    // This is a filter to ween out any single pixel
	    // differences in text width.
	    $ww = ($urx-$llx); 
	    if ($prev_ww != $ww && abs($prev_ww-$ww) < 3)
	      $ww = $prev_ww;
	    else
	      $prev_ww = $ww;
	    imagepstext ($im, "$yst", $this->font, $this->font_size, 
			 $axes_color,
			 $this->allocate_color("white"),
			 (int)($this->left_margin-6-$ww), 
			 (int)($yt+4), 
			 0, 0, 0, 16);
	  } elseif ($this->font_type == "internal") {
	    imagestring($im, $this->font,
			$this->left_margin-3-strlen($yst)*6, $yt-7, $yst,
			$axes_color);
	  }
	  imageline($im, $this->left_margin-3, $yt, 
		    $this->left_margin, $yt, $axes_color);
	} else {
	  imageline($im, $this->left_margin-1, $yt, 
		    $this->left_margin, $yt, $axes_color);
	}
      } else {
	// Draw the grid line.
	imageline ($im, $this->left_margin+1, $yt, 
		   $xs-$this->right_margin, $yt, $grid_color);
      }
    }
  }

  function draw_marked_grid ($im, $ymin, $ymax, $xs, $ys, $height, $yoff) {
    if ($this->marked_grid_color) {
      $y = $this->marked_grid_point;
      $yt = $this->y_point($yoff, $height, $ymin, $ymax, $y);
      imageline ($im, $this->left_margin+1, $yt, 
		 $xs-$this->right_margin, $yt, 
		 $this->allocate_color($this->marked_grid_color));
    }
  }

  function real_string_pixels ($string) {
    if ($this->font_type == "type1") {
      list ($llx, $lly, $urx, $ury) = 
	imagepsbbox($string, $this->font, $this->font_size);
      return $urx-$llx;
    } else
      return strlen($string)*6;
  }


  function string_pixels ($string) {
    if ($this->font_type == "type1")
      return strlen($string) * ($this->font_size - 5.5);
    else
      return strlen($string)*6;
  }

  function get_ticks ($min, $max, $height, $whole = false) {
    $factor = 1000;
    $diff = abs($max-$min);
    list ($even) = $this->pleasing_numbers($diff/$height*10);
    if ($whole) 
      $even = max(1, $even);

    if ($min < 0) 
      $start = floor($min*$factor) + $even*$factor-(floor($min*$factor)%($even*$factor))
	- $even*$factor;
    elseif ($min == 0)
      $start = 0;
    else {
      // This is to work around yet another PHP floating point bug.
      $m = sprintf("%.10f", $min);
      $m = ($m*$factor);
      $e = ($even*$factor);
      $start = floor($m + $e-((($m * $factor) % ($e * $factor)) / $factor));
    }

    for ($elem = $start, $i = 0;
	 $elem < $max*$factor; $elem += floor($even*$factor), $i++) {
      $ticks[$i] = $elem/$factor;
      if ($i > 1000)
	return $ticks;
    }

    return $ticks;
  }

  function set_scale ($type = "linear") {
    $this->scale = $type;
  }

  function allocate_color($color) {
    return rgb_allocate($this->image, $color);
  }

  function get_extrema ($dim) {
    for ($i = 0; $i < sizeof($this->plots); $i++) {
      $plot = $this->plots[$i];
      if (($plot->style == "fill" || $plot->style == "fillgradient")
	  && $dim == 2)
	list ($mi, $ma) = $plot->get_extrema(3);
      else
	list ($mi, $ma) = $plot->get_extrema($dim);

      if (! isset($max))
	$max = $ma;
      if (! isset($min)) 
	$min = $mi;
      if ($ma > $max)
	$max = $ma;
      if ($mi < $min)
	$min = $mi;

      if (($plot->style == "fill" || $plot->style == "fillgradient")
	  && $dim == 1) {
	list ($mi, $ma) = $plot->get_extrema(2, true);
	if ($ma > $max)
	  $max = $ma;
	if ($mi < $min)
	  $min = $mi;
      }

    }
    return array($min, $max);
  }

}

class plot {
  var $coords;
  var $color = "black", $to_color = false, $param = 0;
  var $style = "lines";
  var $dimension = 1;
  var $texts = false;
  var $line_width = 1;
  var $output_data = false;

  function plot ($c1, $c2) {
    $this->coords[] = $c1;
    $this->coords[] = $c2;
    if ($c2 == 0) {
      $this->dimension = 1;
    } else {
      $this->dimension = 2;
    }
    return true;
  }

  function set_color ($color) {
    $this->color = $color;
    return true;
  }

  function set_texts ($texts) {
    $this->texts = $texts;
    return true;
  }

  function set_gradient_color ($to_color) {
    $this->to_color = $to_color;
  }

  function set_param ($param) {
    $this->param = $param;
  }

  function get_color () {
    return $this->color;
  }

  function set_style ($style) {
    $this->style = $style;
  }

  function set_dimension ($dim) {
    $this->dimension = $dim;
  }

  function get_extrema ($dim, $force_true = false) {
    if ($dim > $this->dimension ||
	(!$force_true && $dim == 2 && ($this->style == "fill" ||
				       $this->style == "fillgradient")))
      return array(0, sizeof($this->coords[0])-1);

    $arr = $this->coords[$dim-1];
    for ($j = 0; $j < sizeof($arr); $j++) {
      if ((! is_string($arr[$j])) || (strcmp($arr[$j], "noplot"))) {
	if (! isset($max))
	  $max = $arr[$j];
	if (! isset($min)) 
	  $min = $arr[$j];
	if ($arr[$j] > $max)
	  $max = $arr[$j];
	if ($arr[$j] < $min)
	  $min = $arr[$j];
      }
    }
    return array($min, $max);
  }

  function stroke ($im, $xmin, $xmax, $ymin, $ymax, $xoff, $yoff,
		   $width, $height, &$chart) {
    $color = rgb_allocate($im, $this->color);
    $style = $this->style;
    $param = $this->param;
    $ycoords = $this->coords[0];
    $end = sizeof($ycoords);
    $output_data = array();

    if (!strcmp($style, "points"))
      $style = 1;
    elseif (!strcmp($style, "lines"))
      $style = 2;
    elseif (!strcmp($style, "impulse"))
      $style = 3;
    elseif (!strcmp($style, "circle")) {
      $style = 4;
      if ($param) {
	if (is_array($param)) {
	  $circle_size = $param[0];
	  $position_style = $param[1];
	} else {
	  $circle_size = $param;
	}
      } else
	$circle_size = 10;
    } elseif (!strcmp($style, "cross")) {
      $style = 5;
      if ($param)
	$cross_size = $param/2;
      else
	$cross_size = 5;
    } elseif (!strcmp($style, "fill")) {
      $style = 6;
      $this->dimension = 1;
    } elseif (!strcmp($style, "square"))
	  $style = 7;
    elseif (!strcmp($style, "filled-square"))
      $style = 15;
    elseif (!strcmp($style, "triangle")) {
      if ($this->to_color)
	$dcolor = $this->to_color;
      else
	$dcolor = $this->color;
      $dcolor = rgb_allocate($im, $dcolor);
      $style = 9;
    } elseif (!strcmp($style, "box")) {
      if ($this->to_color)
	$to_color = $this->to_color;
      else
	$to_color = $this->color;
      $tcolors = rgb_allocate_colors($im, $this->color, $to_color, 3);
      $style = 10;
    } elseif (!strcmp($style, "gradient") ||
	      !strcmp($style, "fillgradient")) {
      // Calculate the gradient.
      if (!strcmp($style, "gradient"))
	$style = 8;
      else {
	$style = 12;
	$this->dimension = 1;
      }

      $gradient_style = $this->param&1;
      $gradient_updown = $this->param&2;
      $gradient_direction = $this->param&4;
      $gradient_horizontal = $this->param&8;
      if ($gradient_horizontal)
	$h = $width+2;
      else
	$h = $height+2;
      if (ereg(",", $this->color)) {
	$gcolors = explode(",", $this->color);
	for ($i = 0; $i < sizeof($gcolors) - 1; $i++)
	  $gacolors[] = array($gcolors[$i], $gcolors[$i+1]);
      } else {
	$gacolors[] = array($this->color, $this->to_color);
      }
      $h = (int)($h / sizeof($gacolors));
      $colors = array();
      foreach ($gacolors as $tfcolors) {
	if ($gradient_direction == 0) {
	  list ($from, $to) = $tfcolors;
	} else {
	  list ($to, $from) = $tfcolors;
	}
	$cs = rgb_allocate_colors($im, $from, $to, $h, 200/sizeof($gacolors));
	if ($cs) {
	  foreach ($cs as $c)
	    $colors[] = $c;
	}
      }
    } elseif (!strcmp($style, "text")) {
      $style = 11;
      $textnum = 0;
      $position_style = $param;
      $circle_size = 10;
    } elseif (!strcmp($style, "origo-impulse")) {
      $style = 12;
    } elseif (!strcmp($style, "candlestick"))
      $style = 13;
    elseif (!strcmp($style, "range"))
      $style = 14;
    elseif (!strcmp($style, "none"))
      $style = 16;
    
    if ($end == 1 && $position_style == "last") {
      $y = $ycoords[0];
      $xt = $xoff + $width;
      $yt = $chart->y_point($yoff, $height, $ymin, $ymax, $y);
      if ($style == 11) {
	imagepstext($im, 
		    $this->texts[0],
		    $chart->font,
		    $chart->font_size,
		    $chart->allocate_color("red"),
		    $chart->allocate_color("white"),
		    (int)($xt-$chart->real_string_pixels($this->texts[0])),
		    (int)$yt,
		    0, 0, 0, 16);
      } else {
	imagearc($im, $xt, $yt, $circle_size, $circle_size, 0, 360, $color);
      }
      return($color);
    } 

    for ($i = 0; $i < $end; $i++) {
      $y = $ycoords[$i];

      if ((! is_array($y) && ((! is_string($y)) || (strcmp($y, "noplot"))))) {
	if ($this->dimension == 1) 
	  $x = $i;
	else 
	  $x = $this->coords[1][$i];
	
	$xt = $xoff + ($x - $xmin) / ($xmax - $xmin) * $width;
	$yt = $chart->y_point($yoff, $height, $ymin, $ymax, $y);

	if ($this->output_data)
	  $output_data[$xt] = array($y, $chart->x_ticks[$i]);
	
	if (! isset($pxt))
	  $pxt = $xt;
	if (! isset($pyt))
	  $pyt = $yt;

	if ($style == 1) 
	  imageline($im, $xt, $yt, $xt, $yt, $color);
	elseif ($style == 2) {
	  if ($this->line_width > 1) {
	    $this->thick_line($im, $pxt, $pyt, $xt, $yt, $color, 
			      $this->line_width);
	  } else {
	    imageline($im, $pxt, $pyt, $xt, $yt, $color);
	  }
	} elseif ($style == 3) {
	  imageline($im, $xt, $yoff+$height, $xt, $yt, $color);
	} elseif ($style == 12) {
	  imageline($im, $xt, 
		    $chart->y_point($yoff, $height, $ymin, $ymax, 0),
		    $xt, $yt, $color);
	} elseif ($style == 4) {
	  imagearc($im, $xt, $yt, $circle_size, $circle_size, 0, 360, $color);
	} elseif ($style == 5) {
	  imageline($im, $xt-$cross_size, $yt-$cross_size,
		    $xt+$cross_size, $yt+$cross_size, $color);
	  imageline($im, $xt+$cross_size, $yt-$cross_size,
		    $xt-$cross_size, $yt+$cross_size, $color);
	} elseif ($style == 6) {
	  // Fill
	  if (! isset($poyt))
	    $poyt = $oyt;
	  $oyt = $chart->y_point($yoff, $height, $ymin, $ymax,
				 $this->coords[1][$i]);
	  for ($j = $pxt; $j <= $xt; $j++) 
	    imageline($im, $j, $oyt, $j, $yt, $color);
	  $poyt = $oyt;
	} elseif ($style == 7) {
	  imageline($im, $pxt, $pyt, $pxt, $yt, $color);
	  imageline($im, $pxt, $yt, $xt, $yt, $color);
	} elseif ($style == 15) {
	  imagefilledrectangle($im, $pxt, $yt, $xt, $yoff+$height, $color);
	} elseif ($style == 8) {
	  // gradient
	
	  // We plot down from the value to the bottom of the chart.
	  // There might be several pixels width of stuff to be plotted,
	  // so we first calculate the gradient of the top of the chart
	  // between the two points.  So the top of the "gradient"
	  // chart will resemble the "lines" chart, not the "square"
	  // chart.
	
	  if ($xt == $pxt) {
	    $b = 0;
	  } else {
	    $b = ($yt - $pyt) / ($xt - $pxt);
	  }
	  $a = $yt - $b * $xt;
	
	  for ($x = $pxt; $x <= $xt; $x++) {
	    $firsty = $a + $b * $x;
	    if ($gradient_updown == 0) {
	      for ($y = $a + $b * $x; $y < $yoff+$height; $y++) {
		if ($gradient_style && ! $gradient_horizontal)
		  $coff = $y-$firsty;
		elseif (! $gradient_style && ! $gradient_horizontal)
		  $coff = $y-$yoff;
		elseif ($gradient_style && $gradient_horizontal)
		  $coff = $x-$firstx;
		elseif (! $gradient_style && $gradient_horizontal)
		  $coff = $x-$xoff;
		imagesetpixel($im, $x, $y, $colors[$coff]);
	      }
	    } else {
	      for ($y = $a + $b * $x; $y > $yoff; $y--) {
		if ($gradient_style && ! $gradient_horizontal)
		  $coff = $firsty-$y;
		elseif (! $gradient_style && ! $gradient_horizontal)
		  $coff = $y-$yoff;
		elseif ($gradient_style && $gradient_horizontal)
		  $coff = $firstx-$x;
		elseif (! $gradient_style && $gradient_horizontal)
		  $coff = $x-$xoff;
		imagesetpixel($im, $x, $y, $colors[$coff]);
	      }
	    }
	  }
	} elseif ($style == 12) {
	  // fillgradient
	  
	  if (! isset($poyt))
	    $poyt = $oyt;
	  $oyt = $chart->y_point($yoff, $height, $ymin, $ymax,
				 $this->coords[1][$i]);
	  for ($x = $pxt; $x <= $xt; $x++) {
	    if ($oyt < $yt) {
	      $miny = $oyt;
	      $maxy = $yt;
	    } else {
	      $miny = $yt;
	      $maxy = $oyt;
	    }

	    $firsty = $oyt;
	    if ($gradient_updown == 0) {
	      for ($y = $miny; $y < $maxy; $y++) {
		if ($gradient_style == 1) {
		  imagesetpixel($im, $x, $y, $colors[$y-$firsty]);
		} else {
		  imagesetpixel($im, $x, $y, $colors[$y-$yoff]);
		}
	      }
	    } else {
	      for ($y = $maxy; $y > $miny; $y--) {
		if ($gradient_style == 1) {
		  imagesetpixel($im, $x, $y, $colors[$firsty-$y]);
		} else {
		  imagesetpixel($im, $x, $y, $colors[$y-$yoff]);
		}
	      }
	    }
	  }
	  $poyt = $oyt;

	} elseif ($style == 9) {
	  // Triangle
	  imageline($im, $xt-3, $yt+3, $xt+3, $yt+3, $dcolor);
	  imageline($im, $xt-3, $yt+2, $xt+3, $yt+2, $color);
	  imageline($im, $xt-2, $yt+1, $xt+2, $yt+1, $color);
	  imageline($im, $xt-2, $yt, $xt+2, $yt, $color);
	  imageline($im, $xt-1, $yt-1, $xt+1, $yt-1, $color);
	  imageline($im, $xt-1, $yt-2, $xt+1, $yt-2, $color);
	  imagesetpixel($im,$xt,$yt-3,$color);
	  imagesetpixel($im,$xt,$yt-4,$color);
	  imageline($im, $xt+4, $yt+3, $xt+1, $yt-4, $dcolor);
	} elseif ($style == 10) {
	  // Box
	  imageline($im, $xt-2, $yt-2, $xt+2, $yt-2, $tcolors[0]);
	  imageline($im, $xt-2, $yt-1, $xt-2, $yt+2, $tcolors[0]);
	  imageline($im, $xt-1, $yt-1, $xt+1, $yt-1, $tcolors[1]);
	  imageline($im, $xt-1, $yt  , $xt+1, $yt  , $tcolors[1]);
	  imageline($im, $xt-1, $yt+1, $xt+1, $yt+1, $tcolors[1]);
	  imageline($im, $xt-1, $yt+2, $xt+2, $yt+2, $tcolors[2]);
	  imageline($im, $xt+2, $yt-1, $xt+2, $yt+1, $tcolors[2]);
	} elseif ($style == 11) {
	  imagestring($im, $this->font, $xt, $yt, $this->texts[$textnum++],
		      $color);
	} elseif ($style == 16) {
	  // "None" style -- do nothing.  Called for side effect only.
	}
	
	$pxt = $xt;
	$pyt = $yt;
      } else if (is_array($y) && ($style == 13 || $style == 14)) {
	// Candlesticks and high/low.
	if ($this->dimension == 1) 
	  $x = $i;
	else 
	  $x = $this->coords[1][$i];
	
	$xt = $xoff + ($x - $xmin) / ($xmax - $xmin) * $width;

	list ($open, $high, $low, $close) = $y;

	// The high/low line.
	$ht = $chart->y_point($yoff, $height, $ymin, $ymax, $high);
	$lt = $chart->y_point($yoff, $height, $ymin, $ymax, $low);
	imageline($im, $xt, $ht, $xt, $lt, $color);

	if ($style == 13) {
	  // The box.
	  $ot = $chart->y_point($yoff, $height, $ymin, $ymax, $open);
	  $ct = $chart->y_point($yoff, $height, $ymin, $ymax, $close);
	  if ($close > $open) {
	    imageline($im, $xt, $ct, $xt, $ot,
		      $chart->allocate_color("white"));
	    imagerectangle($im, $xt-2, $ct, $xt+2, $ot, $color);
	  } else {
	    imagefilledrectangle($im, $xt-2, $ot, $xt+2, $ct, $color);
	  }
	}
      }
      
    }
    if ($output_data) {
      $chart->output_data[] = &$output_data;
      $chart->ymin = $ymin;
      $chart->ymax = $ymax;
      $chart->yoff = $yoff;
      $chart->height = $height;
    }
    return($color);
  }

  function thick_line ($im, $start_x, $start_y, $end_x, $end_y, $color, 
		       $thickness) {
    if ($end_x - $start_x == 0)
      $b = 0;
    else
      $b = ($end_y - $start_y) / ($end_x - $start_x);
    $a = $end_y - ($b * $end_x);
    if (abs($start_x - $end_x) > abs($start_y - $end_y)) {
      $start_x = ceil($start_x);
      for ($x = $start_x; $x <= $end_x; $x++) {
	$y = $b * $x + $a;
	imagefilledellipse($im, $x, $y, $thickness, $thickness, $color);
      }
    } else {
      $start = ceil(min($start_y, $end_y));
      $end = max($start_y, $end_y);
      for ($y = $start; $y <= $end; $y++) {
	$x = ($y - $a) / $b;
	imagefilledellipse($im, $x, $y, $thickness, $thickness, $color);
      }
    }
  }

}

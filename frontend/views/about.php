<?php
// rights check
if(!isset($currentUser)) die();
?>
<style>
/* about-only style definitions */
.center {
	text-align: center;
}
.bold {
	font-weight: bold;
}
#vendor-logo {
	width: 280px;
	margin-top: -40px;
}
</style>
<div class='contentbox small'>
	<img id='logo' src='img/logo.png'>
</div>
<div class='contentbox small'>
	<div class='about subtitle center'>
		<img id='vendor-logo' src='img/vendor-logo.png' alt='Georg Sieber Logo'>
		<p class='bold'>
			<?php echo LANG['version']; ?> <?php echo VERSION; ?>
		</p>
		<p>
			<?php echo LANG['copyright']; ?>
		</p>
		<p>
			<a href='https://georg-sieber.de'>https://georg-sieber.de</a>
			<br>
			<a href='mailto:it@georg-sieber.de'>it@georg-sieber.de</a>
		</p>
	</div>
</div>
<div class='contentbox small'>
	<h2><?php echo LANG['3rd_party_components']; ?></h2>
	<img src='img/herz.svg' class='right autosize' title='Dienstplan mit Herz'>
	<ul>
		<li><a target='_blank' href='http://www.fpdf.org/'>FPDF</a>, <a target='_blank' href='../lib/fpdf/license.txt'>FPDF License</a></li>
		<li><a target='_blank' href='http://phplot.sourceforge.net/'>PHPlot</a> by Afan Ottenheimer, <a target='_blank' href='../lib/phplot/COPYING'>LGPL</a></li>
		<li><a target='_blank' href='https://material.io/tools/icons'>Material Icons</a>, <a target='_blank' href='https://www.apache.org/licenses/LICENSE-2.0.html'>Apache License 2.0</a></li>
		<li><a target='_blank' href='https://github.com/SamHerbert/SVG-Loaders'>SVG Loaders</a> by Sam Herbert, <a target='_blank' href='https://github.com/SamHerbert/SVG-Loaders/blob/master/LICENSE.md'>MIT License</a></li>
		<li>Background Image from <a target='_blank' href='http://trianglify.io'>trianglify.io</a></li>
	</ul>
</div>

<?php
/**
 * Metadata for configuration manager plugin
 * Additions for the Avatar Plugin
 *
 * @author    Esther Brunner <wikidesign@gmail.com>
 */

$meta['namespace'] = array('string');
$meta['size']      = array('multichoice', '_choices' => array(20, 40, 80, 120));
$meta['rating']    = array('multichoice', '_choices' => array('X', 'R', 'PG', 'G'));
$meta['default']   = array('multichoice', '_choices' => array('404', 'mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'blank'));

//Setup VIM: ex: et ts=2 enc=utf-8 :

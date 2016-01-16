<?php
/**
 * Avatar Plugin: displays avatar images with syntax {{avatar>email@domain.com}}
 * Optionally you can add a title attribute: {{avatar>email@domain.com|My Name}}
 *
 * For registered users the plugin looks first for a local avatar named username.jpg
 * in user namespace. If none found or for unregistered guests, the avatar from
 * Gravatar.com is taken when available. The MonsterID by Andreas Gohr serves as fallback.
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Esther Brunner <wikidesign@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_avatar extends DokuWiki_Syntax_Plugin {

  function getType() { return 'substition'; }
  function getSort() { return 315; }

  function connectTo($mode) {
    $this->Lexer->addSpecialPattern("{{(?:gr|)avatar>.+?}}",$mode,'plugin_avatar');
  }

  function handle($match, $state, $pos, Doku_Handler $handler) {
    list($syntax, $match) = explode('>', substr($match, 0, -2), 2); // strip markup
    list($user, $title) = explode('|', $match, 2); // split title from mail / username

    // Check alignment
    $ralign = (bool)preg_match('/^ /', $user);
    $lalign = (bool)preg_match('/ $/', $user);
    if ($lalign & $ralign) $align = 'center';
    else if ($ralign)      $align = 'right';
    else if ($lalign)      $align = 'left';
    else                   $align = NULL;

    //split into src and size parameter (using the very last questionmark)
    list($user, $param) = explode('?', trim($user), 2);
    if (preg_match('/^s/', $param))       $size = 20;
    else if (preg_match('/^m/', $param))  $size = 40;
    else if (preg_match('/^l/', $param))  $size = 80;
    else if (preg_match('/^xl/', $param)) $size = 120;
    else $size = NULL;

    return array($user, $title, $align, $size);
  }

  function render($mode, Doku_Renderer $renderer, $data) {
    if ($mode == 'xhtml') {
      if ($my =& plugin_load('helper', 'avatar'))
        $renderer->doc .= '<span class="vcard">'.
          $my->getXHTML($data[0], $data[1], $data[2], $data[3]).
          '</span>';
      return true;
    }
    return false;
  }
}
// vim:ts=4:sw=4:et:enc=utf-8:

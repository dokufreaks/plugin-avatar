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

  function getInfo(){
    return array(
      'author' => 'Esther Brunner',
      'email'  => 'wikidesign@gmail.com',
      'date'   => '2007-02-20',
      'name'   => 'Avatar Plugin',
      'desc'   => 'Displays Avatar images',
      'url'    => 'http://www.wikidesign.ch/en/plugin/avatar/start',
    );
  }

  function getType(){ return 'substition'; }
  function getSort(){ return 315; }
  
  function connectTo($mode) {
    $this->Lexer->addSpecialPattern("{{(?:gr|)avatar>.+?}}",$mode,'plugin_avatar');
  }
  
  function handle($match, $state, $pos, &$handler){
    list($syntax, $match) = explode('>', substr($match, 0, -2), 2); // strip markup
    list($user, $title) = explode('|', $match, 2); // split title from mail / username
    
    // Check alignment
    $ralign = (bool)preg_match('/^ /', $user);
    $lalign = (bool)preg_match('/ $/', $user);
    if ($lalign & $ralign) $align = 'center';
    else if ($ralign)      $align = 'right';
    else if ($lalign)      $align = 'left';
    else                   $align = null;
    
    //split into src and size parameter (using the very last questionmark)
    list($user, $param) = explode('?', $user, 2);
    if (preg_match('/^s/', $param))       $size = 20;
    else if (preg_match('/^m/', $param))  $size = 40;
    else if (preg_match('/^l/', $param))  $size = 80;
    else if (preg_match('/^xl/', $param)) $size = 120;
    else $size = null;
    
    $src = $this->_getAvatarURL($user, $title, $size);
    
    return array($src, $title, $align, $size);
  } 
 
  function render($mode, &$renderer, $data){
    // a string to be added to the gravatar url
    // see http://gravatar.com/implement.php#section_1_1
  
    if ($mode == 'xhtml'){
      
      list($src, $title, $align, $size) = $data;
      $title = ($title ? hsc($data[1]) : obfuscate($mail));
      
      // output with vcard photo microformat
      $renderer->doc .= '<span class="vcard">'.
        '<img src="'.$src.'" class="media'.$align.' photo fn"'.
        ' title="'.hsc($title).'" alt="'.hsc($title).'"'.
        ' width="'.$size.'" height="'.$size.'" />'.
        '</span>';
      return true;
    }
    return false;
  }
  
  /**
   * Main function to determine the avatar to use
   */
  function _getAvatarURL($user, &$title, &$size){
    global $auth;
    
    if (!$size || !is_int($size)) $size = $this->getConf('size');
    
    // check first if a local image for the given user exists
    $userinfo = $auth->getUserData($user);
    if (is_array($userinfo)){
      if (($userinfo['name']) && (!$title)) $title = $userinfo['name'];
      $avatar = $this->getConf('namespace').':'.$user;
      $formats = array('.png', '.jpg', '.gif');
      foreach ($formats as $format){
        $img = mediaFN($avatar.$format);
        if (!@file_exists($img)) continue;
        $src = ml($avatar.$format, array('w' => $size, 'h' => $size));
        break;
      }
      if (!$src) $mail = $userinfo['mail'];
    } else {
      $mail = $user;
    }
    
    if (!$src){
      $seed = md5($user);
    
      // we take the monster ID as default
      $default = ml(DOKU_URL.'lib/plugins/avatar/monsterid.php?'.
        'seed='.$seed.
        '&size='.$size.
        '&.png', 'cache=recache', true, '&');
      
      // do not pass invalid or empty emails to gravatar site...
      if (isvalidemail($mail) && ($size <= 80)){
        $src = ml('http://www.gravatar.com/avatar.php?'.
          'gravatar_id='.$seed.
          '&default='.urlencode(DOKU_URL.$default).
          '&size='.$size.
          '&rating='.$this->getConf('rating').
          '&.jpg', 'cache=recache');
      
      // show only default image if invalid or empty email given
      } else {
        $src = $default;
      }
    }
    
    if (!$title) $title = obfuscate($mail);
    
    return $src;
  }
       
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
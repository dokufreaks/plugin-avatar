<?php
/**
 * @license   GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author    Andreas Gohr <andi@splitbrain.org>
 */

build_monster($_REQUEST['seed'], $_REQUEST['size']);

/**
 * Generates a monster for the given seed
 * GDlib is required!
 */
function build_monster($seed='',$size='') {
    // create 16 byte hash from seed
    $hash = md5($seed);

    // calculate part values from seed
    $parts = array(
        'legs' => _get_monster_part(substr($hash, 0, 2), 1, 5),
        'hair' => _get_monster_part(substr($hash, 2, 2), 1, 5),
        'arms' => _get_monster_part(substr($hash, 4, 2), 1, 5),
        'body' => _get_monster_part(substr($hash, 6, 2), 1, 15),
        'eyes' => _get_monster_part(substr($hash, 8, 2), 1, 15),
        'mouth'=> _get_monster_part(substr($hash, 10, 2), 1, 10),
    );

    // create background
    $monster = @imagecreatetruecolor(120, 120)
        or die("GD image create failed");
    $white   = imagecolorallocate($monster, 255, 255, 255);
    imagefill($monster,0,0,$white);

    // add parts
    foreach($parts as $part => $num) {
        $file = dirname(__FILE__).'/parts/'.$part.'_'.$num.'.png';

        $im = @imagecreatefrompng($file);
        if(!$im) die('Failed to load '.$file);
        imageSaveAlpha($im, true);
        imagecopy($monster,$im,0,0,0,0,120,120);
        imagedestroy($im);

        // color the body
        if($part == 'body') {
            $r = _get_monster_part(substr($hash, 0, 4), 20, 235);
            $g = _get_monster_part(substr($hash, 4, 4), 20, 235);
            $b = _get_monster_part(substr($hash, 8, 4), 20, 235);
            $color = imagecolorallocate($monster, $r, $g, $b);
            imagefill($monster,60,60,$color);
        }
    }

    // restore random seed
    if($seed) srand();

    // resize if needed, then output
    if($size && $size < 400) {
        $out = @imagecreatetruecolor($size,$size)
            or die("GD image create failed");
        imagecopyresampled($out,$monster,0,0,0,0,$size,$size,120,120);
        header ("Content-type: image/png");
        imagepng($out);
        imagedestroy($out);
        imagedestroy($monster);
    }else{
        header ("Content-type: image/png");
        imagepng($monster);
        imagedestroy($monster);
    }
    
}

function _get_monster_part($seed, $lower = 0, $upper = 255) {
    return hexdec($seed) % ($upper - $lower) + $lower;
}
// vim:ts=4:sw=4:et:enc=utf-8:

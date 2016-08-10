<?php

final class PhabricatorRemarkupFigletBlockInterpreter
  extends PhutilRemarkupBlockInterpreter {

  public function getInterpreterName() {
    return 'figlet';
  }

  /**
   * @phutil-external-symbol class Text_Figlet
   */
  public function markupContent($content, array $argv) {
    $map = self::getFigletMap();

    $font = idx($argv, 'font');
    $font = phutil_utf8_strtolower($font);
    if (empty($map[$font])) {
      $font = 'standard';
    }

    require_once '/usr/share/pear/Text/Figlet.php';

    $figlet = new Text_Figlet();
    $figlet->loadFont($map[$font]);

    $result = $figlet->lineEcho($content);

    $engine = $this->getEngine();

    if ($engine->isTextMode()) {
      return $result;
    }

    if ($engine->isHTMLMailMode()) {
      return phutil_tag('pre', array(), $result);
    }

    return phutil_tag(
      'div',
      array(
        'class' => 'PhabricatorMonospaced remarkup-figlet',
      ),
      $result);
  }

  private static function getFigletMap() {
    $dirs = array(
      '/usr/share/figlet/',
      '/usr/share/pear-data/Text_Figlet/fonts/',
    );

    $map = array();
    foreach ($dirs as $dir) {
      foreach (Filesystem::listDirectory($dir, false) as $file) {
        if (preg_match('/\.flf\z/', $file)) {
          $name = phutil_utf8_strtolower($file);
          $name = preg_replace('/\.flf\z/', '', $name);
          $map[$name] = $dir.$file;
        }
      }
    }

    return $map;
  }

}

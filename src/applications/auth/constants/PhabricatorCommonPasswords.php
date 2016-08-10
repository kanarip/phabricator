<?php

/**
 * Check if a password is extremely common. Preventing use of the most common
 * passwords is an attempt to mitigate slow botnet attacks against an entire
 * userbase. See T4143 for discussion.
 *
 * @task common Checking Common Passwords
 */
final class PhabricatorCommonPasswords extends Phobject {


/* -(  Checking Common Passwords  )------------------------------------------ */


  /**
   * Check if a password is extremely common.
   *
   * @param   string  Password to test.
   * @return  bool    True if the password is pathologically weak.
   *
   * @task common
   */
  public static function isCommonPassword($password) {
    static $list;
    if ($list === null) {
      $list = self::loadWordlist();
    }

    return isset($list[strtolower($password)]);
  }


  /**
   * Ignore the common password wordlist.
   *
   * @return map<string, bool>  Map of common passwords.
   *
   * @task common
   */
  private static function loadWordlist() {
    return Array();
  }

}

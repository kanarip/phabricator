<?php

final class PhabricatorMailSetupCheck extends PhabricatorSetupCheck {

  public function getDefaultGroup() {
    return self::GROUP_OTHER;
  }

  protected function executeChecks() {
    if (PhabricatorEnv::getEnvConfig('cluster.mailers')) {
      return;
    }

    $adapter = PhabricatorEnv::getEnvConfig('metamta.mail-adapter');

    switch ($adapter) {
      case 'PhabricatorMailImplementationPHPMailerLiteAdapter':
        if (!Filesystem::pathExists('/usr/bin/sendmail') &&
            !Filesystem::pathExists('/usr/sbin/sendmail')) {
          $message = pht(
            'Mail is configured to send via sendmail, but this system has '.
            'no sendmail binary. Install sendmail or choose a different '.
            'mail adapter.');

          $this->newIssue('config.metamta.mail-adapter')
            ->setShortName(pht('Missing Sendmail'))
            ->setName(pht('No Sendmail Binary Found'))
            ->setMessage($message)
            ->addRelatedPhabricatorConfig('metamta.mail-adapter');
        }
        break;
    }

  }
}

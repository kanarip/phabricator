<?php

final class PhabricatorConfigVersionController
  extends PhabricatorConfigController {

  public function handleRequest(AphrontRequest $request) {
    $viewer = $request->getViewer();

    $title = pht('Version Information');
    $versions = $this->renderModuleStatus($viewer);

    $nav = $this->buildSideNavView();
    $nav->selectFilter('version/');
    $header = $this->buildHeaderView($title);

    $view = $this->buildConfigBoxView(
      pht('Installed Versions'),
      $versions);

    $crumbs = $this->buildApplicationCrumbs()
      ->addTextCrumb($title)
      ->setBorder(true);

    $content = id(new PHUITwoColumnView())
      ->setHeader($header)
      ->setNavigation($nav)
      ->setFixed(true)
      ->setMainColumn($view);

    return $this->newPage()
      ->setTitle($title)
      ->setCrumbs($crumbs)
      ->appendChild($content);

  }

  public function renderModuleStatus($viewer) {
    $versions = $this->loadVersions($viewer);

    $version_property_list = id(new PHUIPropertyListView());
    foreach ($versions as $name => $info) {
      $version = $info['version'];

      if ($info['branchpoint']) {
        $display = pht(
          '%s (branched from %s on %s)',
          $version,
          $info['branchpoint'],
          $info['upstream']);
      } else {
        $display = $version;
      }

      $version_property_list->addProperty($name, $display);
    }

    $phabricator_root = dirname(phutil_get_library_root('phabricator'));
    $version_path = $phabricator_root.'/conf/local/VERSION';
    if (Filesystem::pathExists($version_path)) {
      $version_from_file = Filesystem::readFile($version_path);
      $version_property_list->addProperty(
        pht('Local Version'),
        $version_from_file);
    }

    $binaries = PhutilBinaryAnalyzer::getAllBinaries();
    foreach ($binaries as $binary) {
      if (!$binary->isBinaryAvailable()) {
        $binary_info = pht('Not Available');
      } else {
        $version = $binary->getBinaryVersion();
        $path = $binary->getBinaryPath();
        if ($path === null && $version === null) {
          $binary_info = pht('-');
        } else if ($path === null) {
          $binary_info = $version;
        } else if ($version === null) {
          $binary_info = pht('- at %s', $path);
        } else {
          $binary_info = pht('%s at %s', $version, $path);
        }
      }

      $version_property_list->addProperty(
        $binary->getBinaryName(),
        $binary_info);
    }

    return $version_property_list;
  }

  private function loadVersions(PhabricatorUser $viewer) {
    $specs = array(
      'phabricator',
      'arcanist',
      'phutil',
    );

    $pkg_specs = array(
      'phabricator' => 'phabricator',
      'arcanist' => 'arcanist',
      'phutil' => 'libphutil',
      'sprint' => 'phabricator-extension-sprint',
      'security' => 'phabricator-extension-security',
    );

    $all_libraries = PhutilBootloader::getInstance()->getAllLibraries();
    // This puts the core libraries at the top:
    $other_libraries = array_diff($all_libraries, $specs);
    $specs = array_merge($specs, $other_libraries);

    $log_futures = array();
    $remote_futures = array();

    foreach ($specs as $lib) {
      if (array_key_exists($lib, $pkg_specs)) {
        $lib = $pkg_specs[$lib];
      }

      $results[$lib] =
        id(new ExecFuture("rpm --query --queryformat=%%{VERSION} $lib"));
    }

    foreach ($results as $key => $future) {
        list($ret, $version) = $future->resolve();

        if ($ret > 0) {
            $results[$key] = 'Unknown';
        } else {
            $results[$key] = $version;
        }
    }

    return $results;
  }

}

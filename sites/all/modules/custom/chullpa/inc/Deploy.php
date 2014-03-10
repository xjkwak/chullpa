<?php

define ('MIN_PROJECT_NAME', 2);
define ('DEFAULT_PROJECT_FOLDER', '/home/dries/projects/');
define ('ALIASES_PATH', '/home/dries/.drush/');
define ('ALIASES_FILE', '/home/dries/.drush/aliases.drushrc.php');

class Deploy {

  public function deployDrupal($args) {
    //$name = $argv[1];
    //exec('mkdir -p ~/projects/lost5');
    //exec('drush dl');
    print_r($args);
    $nameProject = self::getProjectName($args);
    if (self::isValid($nameProject)) {
      self::deployProject($nameProject);
    }
  }

  public function deployProject($name) {
    $folder = self::createMainFolder($name);
    self::downloadDrupal($folder);
    self::createAlias($folder, $name);
    self::installDrupal($folder, $name);
    self::createSymbolicLink($folder,$name);
  }

  public function createAlias($folder, $name) {
    chdir($folder . '/drupal');
    exec('drush sa @self --full --with-optional >> '.ALIASES_FILE);
    chdir(ALIASES_PATH);
    exec('sed -i s/self/' . $name . '/g aliases.drushrc.php');
  }

  public function downloadDrupal($path) {
    exec('drush dl --destination='.$path.' --drupal-project-rename=drupal');
  }

  public function createSymbolicLink($folder, $name) {
    $origin = $folder . '/drupal';
    $destiny = '/var/www/html/drupal/' . $name;
    exec('ln -s ' . $origin . ' ' . $destiny);
    print 'Your new installation is available in http://108.174.62.233/drupal/' . $name;
  }

  public function installDrupal($folder, $name) {
    chdir($folder . '/drupal');
    exec('drush si --account-pass="717717" --db-url=mysql://drupal:drupal@localhost/' . $name .' -y');
  }

  public function createMainFolder($name) {
    $folder = DEFAULT_PROJECT_FOLDER.$name.'/web';
    exec('mkdir -p '.$folder);
    return $folder;
  }
 
  public function isValid($nameProject) {
    $response = TRUE;
    if (strlen($nameProject) < MIN_PROJECT_NAME) {
      $response = FALSE;
    }
    else {
      $projects = self::getListProjectsName();
      if (in_array($nameProject, $projects)) {
        $response = FALSE;
      }
    }
    return $response;
  }


  public function getListProjectsName() {
    $path = DEFAULT_PROJECT_FOLDER;
    $results = scandir($path);
    return $results;
  }

  public function getProjectName($args) {
    $nameProject = '';
    if (isset($args[1]) > 0) {
      $nameProject = $args[1];
    }
    return $nameProject;
  }

}

//Deploy::deployDrupal($argv);
//Deploy::getListProjectsName();

?>

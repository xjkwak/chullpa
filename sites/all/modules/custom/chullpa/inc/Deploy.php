<?php

define ('MIN_PROJECT_NAME', 2);
define ('DEFAULT_HOME_USER', '/home/dries/');
define ('DEFAULT_PROJECT_FOLDER', DEFAULT_HOME_USER . 'projects/');
define ('ALIASES_PATH', DEFAULT_HOME_USER . '.drush/');
define ('ALIASES_FILE', ALIASES_PATH . 'aliases.drushrc.php');
define ('DEFAULT_DRUPAL_INSTANCE', DEFAULT_HOME_USER . 'drupal-7.26/');
define ('DEFAULT_DRUPAL_INSTANCE_TAR', DEFAULT_HOME_USER . 'drupal-7.26.tar.gz');

class Deploy {

  public function deployDrupal($args) {
    print_r($args);
    $nameProject = self::getProjectName($args);
    if (self::isValid($nameProject)) {
      $modules = self::getModulesToInstall($args);
      self::deployProject($nameProject, $modules);
    }
  }

  public function deployProject($name, $modules) {
    $folder = self::createMainFolder($name);
    self::downloadDrupal($folder, $name);
    //self::copyDrupal($folder, $name);
    //self::createAlias($folder, $name);
    self::installDrupal($folder, $name);
    self::installModules($name, $modules);
    $modules = 'toolbar overlay';
    self::uninstallModules($name, $modules);
    self::updateHtaccess($folder, $name);
  }

  public function deleteProject($name) {
    exec('chmod -R 777 ' . DEFAULT_PROJECT_FOLDER . $name);
    exec('rm -rf ' . DEFAULT_PROJECT_FOLDER . $name);
    exec('mysql -udrupal -pdrupal -e "drop database ' . $name . ';"');
  }

  public function getModulesToInstall($args) {
    return "admin_menu views devel module_filter";

  }
  

  public function installModules($name, $modules) {
    chdir(DEFAULT_PROJECT_FOLDER . $name);
    exec('drush dl admin_menu ctools views devel pathauto && drush en admin_menu_toolbar views_ui devel pathauto -y ');
  }

  public function uninstallModules($name, $modules) {
    chdir(DEFAULT_PROJECT_FOLDER . $name);
    exec('drush dis ' . $modules . ' -y');
  }

  public function updateHtaccess($folder, $name) {
    chdir($folder . $name);
    exec('sed -i s/"# RewriteBase"/"  RewriteBase"/g .htaccess');
  }

  public function downloadDrupal($path, $name) {
    exec('drush dl --destination=' . $path . ' --drupal-project-rename=' . $name);
  } 

  public function copyDrupal($path, $name) {
    exec('cp -r ' . DEFAULT_DRUPAL_INSTANCE_TAR . ' ' . DEFAULT_PROJECT_FOLDER);
    chdir(DEFAULT_PROJECT_FOLDER);
    exec('tar xvfz drupal-7.26.tar.gz && mv drupal-7.26 '.$name);
  } 

  public function installDrupal($folder, $name) {
    chdir($folder . $name);
    exec('drush si --account-pass="717717" --site-name="'.$name.'" --db-url=mysql://drupal:drupal@localhost/' . $name .' -y');
  }

  public function createMainFolder($name) {
    $folder = DEFAULT_PROJECT_FOLDER;
    //exec('mkdir -p '.$folder);
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

  public function execute() {
   $output = array();
   exec('bash +x /home/dries/public_html/sites/all/modules/custom/chullpa/inc/deploy.sh', $output);
   return $output;
  }

}

?>

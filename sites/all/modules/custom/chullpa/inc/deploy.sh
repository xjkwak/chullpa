#! /bin/bash

if [[ $# -lt 1 ]]; then
  echo 'Please, provide the following parameters: [name_project] '
  echo "For example: "
  echo "             $0 potrero"
  exit 1
fi

#TODO: Validate the name of the project (e.g. It cannot start with a number)

PROJECTNAME=$1

cd /home/dries/projects
drush dl --drupal-project-rename=$PROJECTNAME
cd /home/dries/projects/$PROJECTNAME
sed -i s/"# RewriteBase"/"  RewriteBase"/g .htaccess
drush si --db-url=mysql://drupal:drupal@localhost/$PROJECTNAME --account-pass="717717" --site-name="$PROJECTNAME" -y

mkdir -p /home/dries/projects/$PROJECTNAME/sites/all/modules/contrib
drush dl backup_migrate admin_menu views ctools pathauto devel date module_filter
drush en views_ui -y
drush en admin_menu_toolbar -y
drush en pathauto -y
drush en devel_generate -y
drush en module_filter -y
drush dis overlay toolbar -y
echo "Instalado"

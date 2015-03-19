<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');

$currentDir = dirname( __FILE__ );
$projectDir = '/Users/dweidner/Sites/danielweidner';

require_once( $currentDir . DIRECTORY_SEPARATOR . 'testcase.php' );
require_once( $projectDir . DIRECTORY_SEPARATOR . 'kirby' . DIRECTORY_SEPARATOR . 'bootstrap.php' );

class Page extends PageAbstract {}
class Pages extends PagesAbstract {}
class Children extends ChildrenAbstract {}
class Content extends ContentAbstract {}
class Field extends FieldAbstract {}
class File extends FileAbstract {}
class Files extends FilesAbstract {}
class Kirbytext extends KirbytextAbstract {}
class Kirbytag extends KirbytagAbstract {}
class Role extends RoleAbstract {}
class Roles extends RolesAbstract {}
class Site extends SiteAbstract {}
class Users extends UsersAbstract {}
class User extends UserAbstract {}

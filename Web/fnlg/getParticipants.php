<?php
  define('ROOT_DIR', dirname(__FILE__) . '/../../');

require_once(ROOT_DIR . 'config/config.php');
require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Authorization/namespace.php');
require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'lib/Config/namespace.php');
require_once(ROOT_DIR . 'lib/Server/namespace.php');
require_once(ROOT_DIR . 'lib/Application/User/namespace.php');
require_once(ROOT_DIR . 'lib/Fnlg/FnlgUserAvailabilityHelper.php');


$resourceId = $_GET["resourceId"];
$startTimeString = $_GET["startTime"];

// If we're editing a reservation, this is the reference number.
$referenceNumber = $_GET["referenceNumber"];

$repository = new UserRepository();

$resourceRepository = new ResourceRepository();
$resource = $resourceRepository->LoadById($resourceId);
$ministryName = $resource->GetContact();

$availabilityHelper = new FnlgUserAvailabilityHelper($startTimeString, $referenceNumber);

$pageNumber = 1;
$pageSize = 255;
$sortField = null;
$sortDirection = null;
$filter = new SqlFilterNull();
$filter->_And(new SqlFilterEquals(new SqlFilterColumn(TableNames::USERS_ALIAS, ColumnNames::ORGANIZATION), $ministryName));
$accountStatus = AccountStatus::ACTIVE;

$userList = $repository->GetList($pageNumber, $pageSize, $sortField, $sortDirection, $filter, $accountStatus)->Results();

foreach ($userList as $user)
{
   $user->isBusy = $availabilityHelper->isUserBusy($user);
}

echo(json_encode($userList));

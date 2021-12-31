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

$supportResources = $_POST["supportResources"];
$ownerUser = $_POST["owner"];
$startTimeString = $_POST["startTime"];
$referenceNumber = $_POST["referenceNumber"];

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
$filter->_And(new SqlFilterIn(new SqlFilterColumn(TableNames::USERS_ALIAS, ColumnNames::USERNAME), $supportResources));
$accountStatus = AccountStatus::ACTIVE;

$userList = $repository->GetList($pageNumber, $pageSize, $sortField, $sortDirection, $filter, $accountStatus)->Results();

$owner = null;
if ($ownerUser)
{
   Log::Debug("Trying to get owner: |$ownerUser|");
   $ownerFilter = new SqlFilterNull();
   $ownerFilter->_And(new SqlFilterEquals(new SqlFilterColumn(TableNames::USERS_ALIAS, ColumnNames::USERNAME), $ownerUser));
   $ownerList = $repository->GetList($pageNumber, $pageSize, $sortField, $sortDirection, $ownerFilter, $accountStatus)->Results();
   if ($ownerList && count($ownerList) > 0)
   {
      $owner = $ownerList[0];
      $owner->isBusy = $availabilityHelper->isUserBusy($owner);
      Log::Debug("Got owner: ".json_encode($owner));
   }
}

foreach ($userList as $user)
{
   $user->isBusy = $availabilityHelper->isUserBusy($user);
}

echo(json_encode(["owner"=>$owner, "additionalParticipants"=>$userList]));
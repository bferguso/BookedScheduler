<?php
  define('ROOT_DIR', dirname(__FILE__) . '/../../');

require_once(ROOT_DIR . 'config/config.php');
require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'lib/Application/Authorization/namespace.php');
require_once(ROOT_DIR . 'lib/Common/namespace.php');
require_once(ROOT_DIR . 'lib/Config/namespace.php');
require_once(ROOT_DIR . 'lib/Server/namespace.php');
require_once(ROOT_DIR . 'lib/Application/User/namespace.php');
require_once(ROOT_DIR . 'lib/Fnlg/FnlgApiCaller.php');
require_once(ROOT_DIR . 'lib/Fnlg/FnlgUserAvailabilityHelper.php');

   $resourceId = $_GET["resourceId"];
   $showAll = $_GET["showAll"];
   $existingReservation = $_GET["referenceNumber"];
   $startTimeString = $_GET["startTime"];

   $caller = new FnlgApiCaller();
   if ($existingReservation)
   {
      $meetings = $caller->getScheduledRequest($existingReservation);
   }
   else
   {
      $ministryName = null;
      if ($showAll === "false")
      {
         $resourceRepository = new ResourceRepository();
         $resource = $resourceRepository->LoadById($resourceId);
         $ministryName = $resource->GetContact();
      }
      else
      {
         $ministryName = "all";
      }
      $meetings = $caller->getUnscheduledRequests($ministryName);
   }

   $availabilityHelper = new FnlgUserAvailabilityHelper($startTimeString, $existingReservation);
   foreach ($meetings as $meeting)
   {
      $meeting->firstNationIsBusy = $availabilityHelper->isFirstNationBusy($meeting->contact->first_nation_id);
   }

   echo json_encode($meetings);
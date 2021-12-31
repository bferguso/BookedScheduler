<?php
require_once(ROOT_DIR . 'Domain/Access/namespace.php');

class FnlgCalenderDataGenerator
{

   public function getReservationsByResource()
   {
      $timezone = Configuration::Instance()->GetDefaultTimezone();
      $startTime = new Date("2018-11-29T00:00", $timezone);
      $endTime = new Date("2018-11-30T23:59", $timezone);

      $scheduleRepository = new ScheduleRepository();
      $schedule = $scheduleRepository->GetAll()[0];
      $layout = $scheduleRepository->GetLayout($schedule, new ScheduleLayoutFactory($timezone));

      Log::Debug("Layout: ".var_export($layout, true));

      $reservationViewRepostiory = new ReservationViewRepository();
      $existingReservations =  $reservationViewRepostiory->GetReservations($startTime, $endTime,
         ReservationViewRepository::ALL_USERS,
         ReservationUserLevel::OWNER,
         ReservationViewRepository::ALL_SCHEDULES,
         ReservationViewRepository::ALL_RESOURCES);

   }

}
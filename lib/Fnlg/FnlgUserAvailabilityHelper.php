<?php

require_once(ROOT_DIR . 'Domain/Access/namespace.php');

class FnlgUserAvailabilityHelper
{
   protected $busyParticipantIds = [];
   protected $busyFirstNationIds = [];

   protected $customAttributes = null;

   /**
    * FnlgUserAvailabilityHelper constructor.
    * @param $startDateTimeString Date/Time string of the time slot to validate
    * @param $reservationReferenceNumber string Current reservation number if validating an existing reservation
    */
   public function __construct($startDateTimeString, $reservationReferenceNumber)
   {

      $timezone = Configuration::Instance()->GetDefaultTimezone();
      $startTime = new Date($startDateTimeString, $timezone);

      // Need to add one minute to the start time otherwise it catches the previous meeting too
      $startTime = $startTime->AddMinutes(1);

      $reservationViewRepostiory = new ReservationViewRepository();

      $existingReservations =  $reservationViewRepostiory->GetReservations($startTime, $startTime,
         ReservationViewRepository::ALL_USERS,
         ReservationUserLevel::OWNER,
         ReservationViewRepository::ALL_SCHEDULES,
         ReservationViewRepository::ALL_RESOURCES);

      $ownerAttendingAttributeId = $this->getCustomAttributeId('fnlg.custom.owner.attending.label');
      $firstNationIdAttributeId = $this->getCustomAttributeId('fnlg.custom.first_nation.id.label');

      $busyParticipantNames = [];
      foreach ($existingReservations as $reservation)
      {
         // Only add participants that are busy in other reservations if we're editing a reservation
         if (!$reservationReferenceNumber || $reservationReferenceNumber !== $reservation->ReferenceNumber)
         {
            $this->busyParticipantIds = array_merge($this->busyParticipantIds, $reservation->ParticipantIds);
            $busyParticipantNames = array_merge($busyParticipantNames, $reservation->ParticipantNames);

            // Add the First Nation ID to the busy list
            $firstNationId = $reservation->Attributes->Get($firstNationIdAttributeId);
            array_push($this->busyFirstNationIds, $firstNationId);

            if ($reservation->Attributes->Get($ownerAttendingAttributeId))
            {
               array_push($this->busyParticipantIds, $reservation->UserId);
            }
         }
      }
   }

   /**
    * Checks to see if the $user has been scheduled in another meeting.
    * @param $user The user to check
    * @return bool True if the user is scheduled in another meeting, otherwise false.
    */
   public function isUserBusy($user)
   {
      return  in_array($user->Id, $this->busyParticipantIds);
   }

   public function isFirstNationBusy($firstNationId)
   {
      return  in_array($firstNationId, $this->busyFirstNationIds);
   }

   private function getCustomAttributeId($configurationKey)
   {
      $customAttributeLabel = Configuration::Instance()->GetKey($configurationKey);
      $customAttributes = $this->getCustomAttributes();

      foreach ($customAttributes as $customAttribute)
      {
         if ($customAttribute->Label() === $customAttributeLabel)
         {
            return $customAttribute->Id();
         }
      }
   }

   private function getCustomAttributes()
   {
      if ($this->customAttributes == null)
      {
         $attRepo = new AttributeRepository();
         $this->customAttributes = $attRepo->GetByCategory(CustomAttributeCategory::RESERVATION);
      }
      return $this->customAttributes;
   }
}
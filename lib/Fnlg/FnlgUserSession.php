<?php
/**
 * Created by IntelliJ IDEA.
 * User: brett
 * Date: 2018-06-25
 * Time: 3:11 PM
 */

class FnlgUserSession extends UserSession
{
   public $Username;
   public $Organization;
   public $eventDates;

   /**
    * FnlgUserSession constructor.
    */
   public function __construct($id)
   {
      parent::__construct($id);
   }

   public function setParentValues($userSession)
   {
      $this->FirstName = $userSession->FirstName;
      $this->LastName = $userSession->LastName;
      $this->Email = $userSession->Email;
      $this->Timezone = $userSession->Timezone;
      $this->HomepageId = $userSession->HomepageId;
      $this->IsAdmin = $userSession->IsAdmin;
      $this->IsGroupAdmin = $userSession->IsGroupAdmin;
      $this->IsResourceAdmin = $userSession->IsResourceAdmin;
      $this->IsScheduleAdmin = $userSession->IsScheduleAdmin;
      $this->LanguageCode = $userSession->LanguageCode;
      $this->PublicId = $userSession->PublicId;
      $this->LoginTime = $userSession->LoginTime;
      $this->ScheduleId = $userSession->ScheduleId;
      $this->Groups = $userSession->Groups;
      $this->AdminGroups = $userSession->AdminGroups;
      $this->CSRFToken = $userSession->CSRFToken;
   }

}
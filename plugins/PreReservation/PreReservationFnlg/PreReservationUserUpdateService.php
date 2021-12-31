<?php
/**
Copyright 2012-2017 Nick Korbel

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
 */

class PreReservationUserUpdateService implements IReservationValidationService
{
	/**
	 * @var IReservationValidationService
	 */
	private $serviceToDecorate;

	public function __construct(IReservationValidationService $serviceToDecorate)
	{
		$this->serviceToDecorate = $serviceToDecorate;
	}

	public function Validate($series, $retryParameters = null)
	{
		$result = $this->serviceToDecorate->Validate($series, $retryParameters);

		// don't bother validating this rule if others have failed
		if (!$result->CanBeSaved())
		{
			return $result;
		}

		return $this->EvaluateCustomRule($series);
	}

	private function EvaluateCustomRule(ReservationSeries $series)
	{
      /*
	   Log::Debug("In EvaluateCustomRule");
      $configFile = Configuration::Instance()->File('PreReservationFnlg');
      $ownerAttendingLabel = $configFile->GetKey('custom.owner.attending.label');
      
	   $ownerIsAttending = false;
      $attRepo = new AttributeRepository();
      $customAttributes = $attRepo->GetByCategory(CustomAttributeCategory::RESERVATION);
      $ownerUserId = $series->UserId();
      $participants = $series->CurrentInstance()->Participants();
      Log::Debug("Instance before modification: ".var_export($series->CurrentInstance(), true));
      Log::Debug("Participants: ".json_encode($participants). " Owner: {$ownerUserId}");
      $key = array_search($ownerUserId, $participants);

      //$key = array_search(999, $participants);
      Log::Debug("Got key : |".$key."|");
      if ($key !== null && $key >= 0 && $key < count($participants))
      {
         Log::Debug("Unsetting key: |".$key."|");
         unset($participants[$key]);
         Log::Debug("Participants: ".json_encode($participants). " Owner: {$ownerUserId}");
         $participants = array_values($participants);
         Log::Debug("Participants (after array_values): ".json_encode($participants). " Owner: {$ownerUserId}");
         $series->CurrentInstance()->WithParticipants($participants);
         Log::Debug("Adding Instance: ".var_export($series->CurrentInstance(), true));
         //Log::Debug("Number of changes: ".$changedCount);
         Log::Debug("Participants in the object after: ".json_encode($series->CurrentInstance()->Participants()));
         //$series->AddAttributeValue(new AttributeValue($this->getAttributeIdByName($customAttributes, $ownerAttendingLabel), true));
         $ownerIsAttending = true;
      }

      $series->AddAttributeValue(new AttributeValue($this->getAttributeIdByName($customAttributes, $ownerAttendingLabel), $ownerIsAttending));
      */

      return new ReservationValidationResult();
	}

   /**
    * @param $customAttributes CustomAttribute[]
    * @param $attributeName string
    * @return int
    */
  private function getAttributeIdByName( $customAttributes, $attributeName)
  {

     for ($i = 0; $i < count($customAttributes); $i++)
     {
        if ($customAttributes[$i]->Label() === $attributeName)
        {
           return $customAttributes[$i]->Id();
        }
     }
  }
}
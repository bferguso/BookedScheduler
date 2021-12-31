<?php
/**
Copyright 2014-2017 Nick Korbel

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

require_once(ROOT_DIR . 'lib/Fnlg/FnlgApiCaller.php');

class PostReservationFnlg implements IPostReservationFactory
{
    /**
     * @var PostReservationFactory
     */
    private $factoryToDecorate;

    public function __construct(PostReservationFactory $factoryToDecorate)
    {
        $this->factoryToDecorate = $factoryToDecorate;
       require_once(dirname(__FILE__) . '/PostReservationFnlg.config.php');

       Configuration::Instance()->Register(
          dirname(__FILE__) . '/PostReservationFnlg.config.php',
          'PostReservationFnlg');
    }

	/**
	 * @param UserSession $userSession
	 * @return IReservationNotificationService
	 */
	public function CreatePostAddService(UserSession $userSession)
	{
		// custom logic to be executed
		$base = $this->factoryToDecorate->CreatePostAddService($userSession);
		return new PostReservationCreatedFnlg($base);
	}

	/**
	 * @param UserSession $userSession
	 * @return IReservationNotificationService
	 */
	public function CreatePostUpdateService(UserSession $userSession)
	{
		$base = $this->factoryToDecorate->CreatePostUpdateService($userSession);
		return new PostReservationUpdateFnlg($base);
	}

	/**
	 * @param UserSession $userSession
	 * @return IReservationNotificationService
	 */
	public function CreatePostDeleteService(UserSession $userSession)
	{
		// showing how to not add custom behavior during the post deletion stage
		$base = $this->factoryToDecorate->CreatePostAddService($userSession);
      return new PostReservationDeletedFnlg($base);
	}

	/**
	 * @param UserSession $userSession
	 * @return IReservationNotificationService
	 */
	public function CreatePostApproveService(UserSession $userSession)
	{
		// showing how to not add custom behavior during the post approval stage
		return $this->factoryToDecorate->CreatePostAddService($userSession);
	}

	/**
	 * @param UserSession $userSession
	 * @return IReservationNotificationService
	 */
	public function CreatePostCheckinService(UserSession $userSession)
	{
		return $this->factoryToDecorate->CreatePostCheckinService($userSession);
	}

	/**
	 * @param UserSession $userSession
	 * @return IReservationNotificationService
	 */
	public function CreatePostCheckoutService(UserSession $userSession)
	{
		return $this->factoryToDecorate->CreatePostCheckoutService($userSession);
	}
}

class PostReservationCreatedFnlg implements IReservationNotificationService
{
	/**
	 * @var IReservationNotificationService
	 */
	private $base;

	public function __construct(IReservationNotificationService $base)
	{
		$this->base = $base;
	}

	/**
	 * @param $reservationSeries ReservationSeries|ExistingReservationSeries
	 * @return void
	 */
	public function Notify($reservationSeries)
	{
      $configFile = Configuration::Instance()->File('PostReservationFnlg');
      $fnlgRequestIdLabel = $configFile->GetKey('custom.fnlg.request_id.label');
		// implement any custom post reservation created logic here

      $fnlgMeetingId = AttributeResolver::getAttributeValueByName($reservationSeries->AttributeValues(), $fnlgRequestIdLabel);
      $referenceNumber = $reservationSeries->CurrentInstance()->ReferenceNumber();
      $caller = new FnlgApiCaller();

      $resourceName = $reservationSeries->Resource()->GetName();
      if (strpos($resourceName, "Virtual") === false)
      {
         $result = $caller->markRequestAsScheduled($referenceNumber, $fnlgMeetingId);
      }

		// then let the main application continue
		$this->base->Notify($reservationSeries);
	}

}

class PostReservationDeletedFnlg implements IReservationNotificationService
{
   /**
    * @var IReservationNotificationService
    */
   private $base;

   public function __construct(IReservationNotificationService $base)
   {
      $this->base = $base;
   }

   /**
    * @param $reservationSeries ReservationSeries|ExistingReservationSeries
    * @return void
    */
   public function Notify($reservationSeries)
   {
      // implement any custom post reservation created logic here

      $fnlgMeetingId = AttributeResolver::getAttributeValueByName($reservationSeries->AttributeValues(), "fnlgRequestId");
      $caller = new FnlgApiCaller();
      $resourceName = $reservationSeries->Resource()->GetName();

      if (strpos($resourceName, "Virtual") === false)
      {
         $result = $caller->markRequestAsUnscheduled($fnlgMeetingId);
      }

      // then let the main application continue
      $this->base->Notify($reservationSeries);
   }

}

class PostReservationUpdateFnlg implements IReservationNotificationService
{
	/**
	 * @var IReservationNotificationService
	 */
	private $base;

	public function __construct(IReservationNotificationService $base)
	{
		$this->base = $base;
	}

	/**
	 * @param $reservationSeries ReservationSeries|ExistingReservationSeries
	 * @return void
	 */
	public function Notify($reservationSeries)
	{
		// implement any custom post reservation updated logic here

		// do not call the base Notify method if you want to completely override the base behavior
	}
}

class AttributeResolver
{
   /**
    * @param $customAttributes CustomAttribute[]
    * @param $attributeName string
    * @return int
    */
   public static function getAttributeValueByName($resourceAttributes, $attributeName)
   {
      $attRepo = new AttributeRepository();
      $customAttributes = $attRepo->GetByCategory(CustomAttributeCategory::RESERVATION);
      $attributeId = null;

      for ($i = 0; $i < count($customAttributes); $i++)
      {
         if ($customAttributes[$i]->Label() === $attributeName)
         {
            $attributeId =  $customAttributes[$i]->Id();
            break;
         }
      }

      if ($attributeId)
      {
         Log::Debug("Got attribute ID: ".$attributeId);
         return $resourceAttributes[strval($attributeId)]->Value;
      }
   }
}

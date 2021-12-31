<?php

class FnlgConfigKeys
{
   # Base URL for the FNLG Admin application
   const BASE_URL = 'fnlg.base.url';
   const UNAUTHORIZED_URI = 'fnlg.unauthorized.uri';

   # Laravel API callbacks

   # This requires the username to be appended to the end of the URL
   const API_SYSTEM_USERNAME = 'fnlg.api.username';
   const API_LOCAL_IP_ADDRESSES = 'fnlg.api.local.ip.addresses';

   const API_GET_USER = 'fnlg.api.get.user';
   const API_GET_EVENT_DATES = 'fnlg.api.get.event.dates';
   const API_GET_REQUEST_BY_REFERENCE_NO = 'fnlg.api.get.request.by.reference_no';
   const API_GET_UNSCHEDULED_REQUESTS = 'fnlg.api.get.unscheduled.requests';
   const API_MARK_REQUEST_SCHEDULED = 'fnlg.api.mark.request.scheduled';
   const API_MARK_REQUEST_UNSCHEDULED = 'fnlg.api.mark.request.unscheduled';

   const PARAM_MEETING_REQUEST_ID = 'fnlg.param.meeting.request.id';
   const PARAM_BOOKED_REFERENCE_NUMBER = 'fnlg.param.booked.reference.number';

   // SiteMinder header keys
   const SM_USERNAME_KEY = 'fnlg.sm.username.key';
   const SM_EMAIL_KEY = 'fnlg.sm.email.key';
   const SM_DISPLAY_NAME_KEY = 'fnlg.sm.display.name.key';
   const SM_REALM_KEY = 'fnlg.sm.realm.key';
   const SM_USER_TYPE_KEY = 'fnlg.sm.user.type.key';

}
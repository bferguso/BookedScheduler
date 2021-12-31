<!--li id='navFNLGSystemAdmin' class='dropdown'><a href='/admin' class='dropdown-toggle' data-toggle='dropdown'> Test: {ServiceLocator::GetServer()->GetUserSession()->UserId} <b class='caret'></b></a>
</li-->
{if (ServiceLocator::GetServer()->GetUserSession()->IsAdmin)}
<li id='navFNLGSystemAdmin' class='dropdown'><a href='/admin' class='dropdown-toggle' data-toggle='dropdown'> System Admin <b class='caret'></b></a>
    <ul class='dropdown-menu'>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/controlCodes'>Control codes</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/firstNations'>First Nation Names</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/ministries'>Ministries</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/ministryUser'>Ministry Users</a></li>
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/pageContentList">Page Content</a></li>
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/registrationTypes">Registration Types</a></li>
    </ul>
</li>
{/if}
<li id='navFNLGRequestAdmin' class='dropdown'><a href='/admin' class='dropdown-toggle' data-toggle='dropdown'>Request Admin <b
                class='caret'></b></a>
    <ul class='dropdown-menu'>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/requestList'>Review Requests</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/supportRequests'>Review Support Requests</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/booked/Web/schedule.php?sds={ServiceLocator::GetServer()->GetUserSession()->eventDates}'>Schedule Requests</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/ministryUserStats'>Ministry Attendee Statistics</a></li>
        <li><hr style="margin-top: 2px; margin-bottom: 2px;"></li>
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/request" target="newMeetings">Add New Meetings</a></li>
    </ul>
</li>
{if (ServiceLocator::GetServer()->GetUserSession()->IsAdmin)}
<li id='navFNLGRegAdmin' class='dropdown'><a href='/admin' class='dropdown-toggle' data-toggle='dropdown'>Registration Admin <b
                class='caret'></b></a>
    <ul class='dropdown-menu'>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/registrationList'>Review Registrations</a></li>
        <li><a href='{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/firstNationStats'>Registrations Statistics</a></li>
        <li><hr style="margin-top: 2px; margin-bottom: 2px;"></li>
        <!-- This is a bit of a hack - there is no "all" type so it returns all registrant types -->
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/type/all" target="newRegistrants">Register Delegates</a></li>
    </ul>
</li>
<li id='navFNLGAdminReports' class='dropdown'><a href='/admin' class='dropdown-toggle' data-toggle='dropdown'>Schedule Reports<b class='caret'></b></a>
    <ul class='dropdown-menu'>
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/roomScheduleReport" target="roomReportWindow">Schedule Report By Room</a></li>
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/userScheduleReport" target="userReportWindow">Schedule Report By User</a></li>
        <li><a href="{Configuration::Instance()->GetKey("fnlg.context.url")}/admin/firstNationScheduleReport" target="firstNationReportWindow">Schedule Report By First Nation</a></li>
    </ul>
</li>
{/if}

<script type="application/javascript">
    $(function () {
        var link = $("#navBookings a");
        link.attr("href", link.attr("href")+"?sds={ServiceLocator::GetServer()->GetUserSession()->eventDates}");
        $("#navDashboard").hide();
        $("#navReportsDropdown").hide();
    });
</script>

var meetingList;
// The baseUrl is the URL to the /Web directory (inclusive).
var baseUrl = window.location.href.replace(/\/Web\/.*/, "\/Web") ;

// Indicates whether the Larave participant list is ready in the page.
var gotMinistryParticipants = false;

var getUnscheduledMeetings = function() {
    var returnValue = null;
    var resourceId = $("#primaryResourceId").val();
    var showAll = $("#showAllMinistriesCheckbox").is(":checked") ? "true" : "false";
    var referenceNumber = $("#referenceNumber").val();
    var startTime = $("#BeginDate").val()+"T"+$("#BeginPeriod").val();
    console.log("Reference number: "+referenceNumber);

    if (referenceNumber)
    {
        $('#meetingRequestSelect').hide();
        $('#showAllMinistriesLabel').hide();
        $('#showAllMinistriesCheckbox').hide();
    }
    else
    {
        $('#meetingRequestSelect').show();
        $('#showAllMinistriesLabel').show();
        $('#showAllMinistriesCheckbox').show();
    }
    console.log("Show all?: "+showAll);
    console.log("baseUrl: "+baseUrl);
    $.ajax({
        url: baseUrl+"/fnlg/getRequests.php?resourceId="+resourceId+"&showAll="+showAll+"&referenceNumber="+referenceNumber+"&startTime="+startTime
    })
        .done(function (data) {
            returnValue = jQuery.parseJSON(data);
            if (returnValue && returnValue.length > 0)
            {
                $('#fnlgReservationInfo').show();
                $('.btnCreate').each(function() { $(this).prop("disabled", false); });
                $("#noMeetingsText").hide();

                var selectList = $('#meetingRequestSelect');
                selectList.find('option').remove();
                setMeetingListField(returnValue);
                $(returnValue).each(function (row) {
                    var option = $("<option></option>")
                            .attr("value", $(returnValue)[row]["meeting_request_id"])
                            .text("#" + $(returnValue)[row]["meeting_request_id"] + ": " + $(returnValue)[row]["reference_no"]
                                + ") [" + $(returnValue)[row]["contact"]["first_nation"]["first_nation_name"] + "] "
                                + $(returnValue)[row]["contact"]["contact_first_name"]
                                + " " + $(returnValue)[row]["contact"]["contact_last_name"]
                            );
                    if ($(returnValue)[row]["firstNationIsBusy"])
                    {
                        option.addClass("busy");
                    }
                    else
                    {
                        option.addClass("free");
                    }
                    if (referenceNumber && $(returnValue)[row]["booked_reference_no"] === referenceNumber)
                    {
                        console.log("Setting meeting as selected "+ $(returnValue)[row]["meeting_request_id"]);
                        option.attr('selected', true);
                        if (option.hasClass("busy"))
                        {
                            selectList.addClass("busy");
                        }
                    }
                    selectList.append(option);
                });
                updateFnlgFields();
            }
            else {
                $('#meetingRequestSelect').hide();
                $('#fnlgReservationInfo').hide();
                $('.btnCreate').each(function() { $(this).prop("disabled", true); });
                $("#noMeetingsText").show();
            }

            ensureRequestIdUpdated();
            setupOwnerAttending();
        })
        .fail(function(jqXHR, textStatus, errorThrown)
        {
            alert("Error!:"+textStatus+":"+errorThrown+"::"+jqXHR.status+":"+jqXHR.statusText+":  "+jqXHR.responseXML);
        });
};

var setMeetingListField = function(data)
{
    meetingList = data;
};

var updateFnlgFields = function()
{
    var reservationTitleInput = $("#reservationTitle");
    var maxLength = reservationTitleInput.attr("maxLength");
    var requestSelect = $("#meetingRequestSelect");
    var option = requestSelect.find("option:selected");
    if (option.hasClass("busy"))
    {
        requestSelect.addClass("busy");
        reservationTitleInput.addClass("busy");
    }
    else
    {
        requestSelect.removeClass("busy");
        reservationTitleInput.removeClass("busy");
    }
    var meetingRequestId = option.attr("value");
    console.log(jQuery.type(meetingRequestId));
    console.log("Meeting request ID: "+meetingRequestId);
    $(meetingList).each(function(row) {
        console.log(jQuery.type(meetingList[row]["meeting_request_id"]));
        if (Number(meetingRequestId) === meetingList[row]["meeting_request_id"]) {
            /* $("#fnlgContact").text(meetingList[row]["contact"]["contact_title"] +" " +
                meetingList[row]["contact"]["contact_first_name"] + " " +
                meetingList[row]["contact"]["contact_last_name"]); */
            $("#fnlgContact").text(meetingList[row]["contact"]["contact_first_name"] + " " +
                meetingList[row]["contact"]["contact_last_name"]);
            $("#fnlgNation").text(meetingList[row]["contact"]["first_nation"]["first_nation_name"]);
            $("#fnlgTopic").text(meetingList[row]["discussion_topics"]);
            reservationTitleInput.val(("#"+meetingList[row]["meeting_request_id"]+": "+meetingList[row]["contact"]["first_nation"]["first_nation_name"]).substr(0,maxLength));
            $("#description").val("Topic: "+meetingList[row]["discussion_topics"]+ (meetingList[row]["request_details"] == null ? "" : "\n\n"+meetingList[row]["request_details"]));

            setHiddenInputValue(meetingList[row]["meeting_request_id"], "fnlgRequestId");
            setHiddenInputValue(meetingList[row]["contact"]["first_nation_id"], "fnlgFirstNationId");

            setAdditionalParticipants(meetingList[row]["support_requests"]);
            return false;
        }
    });
};

/**
 * Function to ensure that the hidden requestId form field has been set . The Custom Attributes (where the
 * requestId field has been set) is loaded after the document has been loaded so not available immediately. This
 * tries function every second for 5 seconds until the field has been set properly.
 *
 * @param numberOfTries current try number (for recursive call)
 */
var ensureRequestIdUpdated = function (numOfTries) {
    var numberOfTries = ((typeof numOfTries === 'undefined') ? 0 : numOfTries);
    var meetingRequestId = $("#meetingRequestSelect").find("option:selected").attr("value");
    var maxTries = 5;
    var requestIdSet = false;
    var firstNationIdSet = false;

    $(meetingList).each(function (row) {
        if (Number(meetingRequestId) === meetingList[row]["meeting_request_id"]) {
            requestIdSet = setHiddenInputValue(meetingList[row]["meeting_request_id"], "fnlgRequestId");
            firstNationIdSet = setHiddenInputValue(meetingList[row]["contact"]["first_nation_id"], "fnlgFirstNationId");
            console.log("Request ID set? "+requestIdSet + " First Nation ID set? "+firstNationIdSet);
            if (!requestIdSet || !firstNationIdSet) {
                if (numberOfTries < maxTries) {
                    console.log("Set failed. Trying again (retry " + numberOfTries+") time: "+new Date());
                    setTimeout(ensureRequestIdUpdated, 1000, numberOfTries + 1);
                }
                else
                {
                    console.log("Failed. Max tries exceeded.");
                }
            }
            else {
                console.log("Success!");
            }
        }
    });
};

var addParticipantToSelect = function (userid, username, additionalClasses) {
    var option = $("<option></option>")
        .attr("value", userid)
        .attr("data-value", userid)
        .text(username);
    if (additionalClasses !== null) {
        option.addClass(additionalClasses);
    }
    $('#participant_list')
        .append(option);
    console.log("Added Id:" + userid + " Name:" + username);
};

/**
 * Gets additional participants that have been indicated as support resources for the meeting
 * @param supportRequests - list of support requests that have been made for the currently selected meeting request
 */
var setAdditionalParticipants = function (supportRequests) {
    var additionalParticipants = [];
    var ownerParticipant = null;
    var startTime = $("#BeginDate").val()+"T"+$("#BeginPeriod").val();
    var referenceNumber = $("#referenceNumber").val();

    // Remove any pre-existing options set to owner before we repopulate
    $("#participant_list option").removeClass("owner");

    $(supportRequests).each(function (row, data) {
        if (supportRequests[row]["support_role"] === "Owner")
        {
            ownerParticipant = supportRequests[row]["requested_user"]["username"];
        }
        else if (supportRequests[row]["request_status"] === "Accepted")
        {
            //console.log("Adding user: "+JSON.stringify(supportRequests[row]));
            additionalParticipants.push(supportRequests[row]["requested_user"]["username"]);
        }
    });
    //console.log("Owner: "+ownerParticipant);
    //console.log("Additional: "+JSON.stringify(additionalParticipants));

    $.ajax({
        url: baseUrl+"/fnlg/getAdditionalParticipants.php",
        type: "POST",
        data: {
            owner: ownerParticipant,
            supportResources: additionalParticipants,
            startTime: startTime,
            referenceNumber: referenceNumber
        }
    })
        .success(function (data) {
            console.log("Got additional participants "+data);
            returnValue = jQuery.parseJSON(data);
            var owner = returnValue["owner"];
            var additionalParticipants = returnValue["additionalParticipants"];
            //console.log("Owner: "+JSON.stringify(owner));
            //console.log("Additional Participants: "+JSON.stringify(additionalParticipants));
            var participantSelect = $('#participant_list');
            participantSelect.find(".additional").remove();
            $("#participantList").find(".additional").remove();

            var ownerOption;
            var ownerParticipating;

            if (owner !== null) {
                ownerOption = $('#participant_list option[value="' + owner.Id + '"]').first();
                ownerParticipating = $("#participantList").find("[data-userid='" + owner.Id + "']");
            }


            if (owner !== null) {
                var ownerClass = owner.isBusy ? "owner busy" : "owner";
                if (typeof ownerOption === 'undefined' || ownerOption.length === 0) {
                    if (ownerParticipating.length === 0)
                    {
                        ownerOption = $("<option class='"+ownerClass+"'></option>")
                            .attr("value", owner.Id)
                            .attr("data-value", owner.Id)
                            .text(owner.First + " " + owner.Last + " (" + owner.Position + ", " + owner.Organization + ")");
                        participantSelect.append(ownerOption);
                        //console.log("Added participant "+owner.Id);
                    }
                    else
                    {
                        ownerParticipating.parent().addClass(ownerClass);
                    }
                }
                else {
                    //console.log("Already had owner " + ownerOption.text());
                    ownerOption.addClass(ownerClass);
                }
            }

            $(additionalParticipants).each(function (row) {
                //console.log("Processing participant "+additionalParticipants[row].Id);
                var additionalOption = $('#participant_list option[value="'+additionalParticipants[row].Id+'"]').first();
                var alreadyParticipating = $("#participantList").find("[data-userid='" + additionalParticipants[row].Id + "']");
                var additionalClass = additionalParticipants[row].isBusy ? "additional busy" : "additional";
                if (typeof additionalOption === 'undefined' || additionalOption.length === 0 &&
                    (typeof alreadyParticipating === 'undefined' || alreadyParticipating.length === 0) ) {
                    // It doesn't exist anywhere.
                    console.log("Additional user doesn't exist anywhere. Adding to select list");
                    additionalOption = $("<option class='"+additionalClass+"'></option>")
                        .attr("value", additionalParticipants[row].Id)
                        .attr("data-value", additionalParticipants[row].Id)
                        .text(additionalParticipants[row].First + " " + additionalParticipants[row].Last + " ("
                            + additionalParticipants[row].Position +", "+ additionalParticipants[row].Organization+ ")");
                    participantSelect.append(additionalOption);
                    //console.log("Added participant "+additionalParticipants[row].Id);
                }
                else if(typeof additionalOption !== 'undefined' && additionalOption.length > 0)
                {
                    console.log("Adding class to pre-existing select option ");
                    additionalOption.addClass(additionalClass);
                }
                else
                {
                    //console.log("Adding class and remove callback to pre-existing item in participating list");
                    alreadyParticipating.parent().addClass(additionalClass);
                    //console.log("Adding remove callback to "+$(additionalParticipants)[row]['Id']);
                    alreadyParticipating.siblings(".remove").each(function() {
                        $(this).on('click', function (event) {
                            removeParticipantFromAttendeeList($(event.target).parent());
                        });
                    });
                }
            });

            participantSelect.trigger("chosen:updated");

        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            alert("Error!:" + textStatus + ":" + errorThrown + "::" + jqXHR.status + ":" + jqXHR.statusText + ":  " + jqXHR.responseXML);
        });
};

/**
 * Gets the list of all possible participants for the ministry.
 */
var getParticipantList = function () {
    $("#reservationParticipation").empty();

    $("#reservationParticipation").append("<label for='particpant_list'>Participants "
        +"<i class='infoicon glyphicon glyphicon-info-sign' data-toggle='tooltip' data-placement='right' title='<p>Select participant(s): </p><ul class=\"tooltipList\">"
        +"<li><span style=\"color: red\">red</span>: has another a meeting in that timeslot;</li>"
        +"<li><span style=\"font-weight: bold\">bold</span>: is Owner of the meeting;</li>"
        +"<li><span style=\"font-style: italic\">italic</span>: is additional support resource.</li>"
        +"</ul>'></i>"
        +"</label>"
        +"<select name='participant_list' id='participant_list'></select>"
        +"<div class='participationButtons'><button id='addParticipant' type='button' class='btn inline'>"
        +"<i class='fa fa-user'></i>Add</button></div><div id='participantList'></div>");
    //<div class="user"><a href="#" class="remove"><span class="fa fa-remove"></span></a> <a href="#" class="bindableUser" data-userid="89" user-details-bound="1">George Chow (Minister.TRD@gov.bc.ca2)</a><input type="hidden" class="id" name="participantList[]" value="89"></div>
    var returnValue = null;
    var resourceId = $("#primaryResourceId").val();
    var startTime = $("#BeginDate").val()+"T"+$("#BeginPeriod").val();
    var referenceNumber = $("#referenceNumber").val();

    console.log("Start time: "+startTime);

    $.ajax({
        url: baseUrl+"/fnlg/getParticipants.php?resourceId=" + resourceId+"&startTime="+startTime+"&referenceNumber="+referenceNumber,
    })
        .done(function (data) {
            returnValue = jQuery.parseJSON(data);
            $(returnValue).each(function (row) {
                    var inParticipatingList = $("#participantList").find("[data-userid='" + $(returnValue)[row]['Id'] + "']");
                    var isBusy = $(returnValue)[row]['isBusy'];
                    console.log("$(returnValue)[row]['isBusy']: "+$(returnValue)[row]['isBusy']);
                    console.log("Is busy? "+isBusy);
                    if (inParticipatingList.length === 0) {
                        addParticipantToSelect($(returnValue)[row]["Id"], $(returnValue)[row]["First"] + " " + $(returnValue)[row]["Last"]
                            + " (" + $(returnValue)[row]["Position"] + ")", isBusy ? "busy" : "");
                    }
                    else
                    {
                        console.log("Adding remove callback to "+$(returnValue)[row]['Id']);
                        inParticipatingList.siblings(".remove").each(function() {
                            $(this).on('click', function (event) {
                                removeParticipantFromAttendeeList($(event.target).parent());
                            });

                        });
                        if (isBusy)
                        {
                            inParticipatingList.parent().addClass("busy");
                        }
                    }
                }
            );
            // Tell the page that the Laravel participants list is ready.
            gotMinistryParticipants = true;

            $("#participant_list").chosen({
                search_contains: true
            });
            $("#addParticipant").on("click", function () {
                var option = $('#participant_list').find(":selected");
                addParticipantToAttendeeList(option);
            });

        })
        .fail(function(jqXHR, textStatus, errorThrown)
        {
            alert("Error!:" + textStatus + ":" + errorThrown + "::" + jqXHR.status + ":" + jqXHR.statusText + ":  " + jqXHR.responseXML);
        });
};

var removeParticipantFromAttendeeList = function(targetElement)
{
    var dataElement = $(targetElement).siblings(".bindableUser");
    console.log(targetElement);
    console.log(dataElement);
    console.log($(targetElement).siblings().length);
    var userid = $(dataElement).attr('data-userid');
    var username = $(dataElement).text();
    var meetingCreator = $("#userName").attr("data-userid");
    var divClasses = dataElement.parent().attr("class").replace("user","");
    if (meetingCreator === userid)
    {
        setOwnerIsAttending(false);
    }
    console.log("Caught event!! UserID: "+userid+" Username: "+username + " Classes: "+divClasses);
    addParticipantToSelect(userid, username, divClasses);
    $("#participant_list").trigger("chosen:updated");
};

var addParticipantToAttendeeList = function(option)
{
    var username = option.text();
    var bookedid = option.attr("data-value");
    var optionClasses = option.attr('class');
    var meetingCreator = $("#userName").attr("data-userid");

    option.remove();

    $('#participant_list').trigger("chosen:updated");

    if (bookedid == null && username !== null && username !== '') {
        alert("Error - no booked id");
    }
    else if (bookedid == null && (username == null || username === '')) {
        alert("Please select Participant to add.");
    }
    else {
        var div = $("<div></div>").addClass("user");
        if (typeof optionClasses !== 'undefined' && optionClasses.length > 0)
        {
            div.addClass(optionClasses);
        }
        var remove = $("<a></a>") .attr("href","#")
            .addClass("remove").append(
                $("<span>&nbsp</span>").addClass("fa fa-remove")
            );
        var userRef = $("<a></a>")
            .attr("href","#")
            .attr("data-userid",bookedid)
            .attr("user-details-bound","1")
            .addClass("bindableUser").text(username);
        div.append(remove).append(userRef);
        if (meetingCreator === bookedid) {
            setOwnerIsAttending(true);
        }
        else {
            var input = $("<input/>")
                .attr("type","hidden")
                .attr("name","participantList[]")
                .attr("value",bookedid)
                .addClass("id");
            div.append(input);
        }

        $("#participantList").append(div);

        $("#participantList [data-userid='" + bookedid + "']").siblings(".remove").each(function() {
            console.log("Trying to find: "+$(this).attr("class"));
            $(this).on('click', function (event) {
                removeParticipantFromAttendeeList($(event.target).parent());
            });

        });
    }
};

var setHiddenInputValue = function(requestId, labelText)
{
    var success = false;
    $("label").each( function() {
        if ($(this).text() === labelText)
        {
            $("#"+$(this).attr("for")).val(requestId);
            console.log("Request ID Set to "+requestId);
            success = true;
            return false; // Break out of each loop
        }
    });
    return success;
};

var setOwnerIsAttending = function(isAttending)
{
    var success = false;
    $("label").each( function() {
        if ($(this).text().replace(/\s/g, '') === "ownerIsAttending")
        {
            $("#"+$(this).attr("for")).prop("checked", isAttending);
            console.log("Set owner is attending flag to "+isAttending);
            success = true;
            return false; // Break out of each loop
        }
    });
    return success;
};

var setupOwnerAttending = function(numberOfTries)
{
    var maxTries = 5;
    var success = false;
    var thisTry = (typeof numberOfTries === 'undefined') ? 0 : numberOfTries;

    // Need to wait until we've fetched the ministry participants - meeting creator will never be in the
    // list by default.
    if (gotMinistryParticipants)
    {
        $("label").each( function() {
            if ($(this).text().replace(/\s/g, '') === "ownerIsAttending")
            {
                var isAttending = $("#"+$(this).attr("for")).is(":checked");
                if (isAttending)
                {
                    userid = $("#userName").attr("data-userid");
                    var option = $('#participant_list').find("option[data-value='"+userid+"']");
                    //console.log("setupOwnerAttending - Got option: "+JSON.stringify(option));
                    addParticipantToAttendeeList(option);
                    console.log("Meeting scheduler is attending");
                }
                else
                {
                    console.log("Meeting scheduler is NOT attending");
                }
                success = true;
                return false; // Break out of each loop
            }
        });
    }

    if (!success && (thisTry < maxTries)) {
        console.log((!gotMinistryParticipants ? "Participants not ready. Waiting for iteration " : "Didn't find element. Trying again with iteration ")+(thisTry+1));
        setTimeout(setupOwnerAttending, 1000, thisTry + 1);
    }
};


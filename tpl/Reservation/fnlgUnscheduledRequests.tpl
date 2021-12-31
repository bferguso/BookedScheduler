<style>
    .additional {
        font-style: italic;
    }
    .owner {
        font-weight: bold;
    }
    .free {
        color: black;
    }
    .busy {
        color: red;
    }
    div.busy {
        border: dotted red thin;
    }
    .chosen-container.chosen-container-single {
        width: 100% !important;
    }
    .customAttributes
    {
        display: none;
    }

    .infoicon {
        color: #2a62bc;
    }

    .infoicon:hover {
        color: #2c86e7;
        opacity: 0.7;
    }

    .tooltip > .tooltip-inner {
        white-space: pre-wrap;
        color: #000000;
        background-color: #eeeeee;
        text-align: left;
        max-width: 300px;
        min-width: 300px;
        opacity: 0.95;
    }
    ul.tooltipList {
        list-style: none;
        padding-left: 10px;
    }
</style>

<div class="col-xs-9 meetingRequestSelect">
        <div class="form-group">
            <label for="meetingRequestSelect">Meeting request
                <i class="infoicon glyphicon glyphicon-info-sign" data-toggle="tooltip" title="<p>Select meeting to schedule</p><p>Meetings in <span style='color: red'>red</span> indicate the First Nation already has a meeting in that timeslot.</p>"></i></label>
            </label><br/>
            <select id="meetingRequestSelect"></select>
            <span id="noMeetingsText" style="display: none">You have no meetings ready to schedule</span>
        </div>
</div>
<div class="col-xs-3">
{if $CanViewAdmin}
    <div class="form-group">
        <label id="showAllMinistriesLabel" for="showAllMinistriesCheckbox">All Ministries?</label><br/>
        <input type="checkbox" id="showAllMinistriesCheckbox" value="all" onclick="getUnscheduledMeetings();"/>
    </div>
{/if}
</div>
<div class="col-xs-12 reservationDescription" id="fnlgReservationInfo">
    <div class="form-group">
        <label>Contact:</label>
        <span id="fnlgContact"></span>
    </div>
    <div class="form-group">
        <label>First Nation Band / Tribal Council:</label>
        <span id="fnlgNation"></span>
    </div>
    <div class="form-group">
        <label>Topic to be discussed:</label>
        <span id="fnlgTopic"></span>
    </div>
</div>

<link rel="stylesheet" href="{Configuration::Instance()->GetKey('script.url')}/css/chosen.css">
<script type="text/javascript" src="{Configuration::Instance()->GetKey('script.url')}/scripts/chosen.jquery.js"></script>
<script type="text/javascript" src="{Configuration::Instance()->GetKey('script.url')}/scripts/fnlgReservation.js"></script>

<script type="application/javascript">
    $(function () {
        /* The list of all meetings that need to be scheduled */
        getUnscheduledMeetings();
        updateFnlgFields();
        getParticipantList();

        $("#meetingRequestSelect").change( function() {
            updateFnlgFields();
        });

        {literal}
        $('[data-toggle="tooltip"]').tooltip({html:true});
        {/literal}
    });
</script>


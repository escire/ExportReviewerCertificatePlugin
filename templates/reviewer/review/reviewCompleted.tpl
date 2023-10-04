{**
* templates/reviewer/review/reviewCompleted.tpl
*
* Copyright (c) 2014-2021 Simon Fraser University
* Copyright (c) 2003-2021 John Willinsky
* Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
*
* Show the review completed page.
*
*}

<h2>{translate key="reviewer.complete"}</h2>
<br />
<div class="separator"></div>
<p>{translate key="reviewer.complete.whatNext"}</p>
<!-- Display queries grid -->
{capture assign="queriesGridUrl"}{url router=$smarty.const.ROUTE_COMPONENT component="grid.queries.QueriesGridHandler"
op="fetchGrid" submissionId=$submission->getId() stageId=$reviewAssignment->getStageId() escape=false}{/capture}
{load_url_in_div id="queriesGridComplete" url=$queriesGridUrl}

<style>
    .export-certificate-form-container {
        position: relative;
        display: block;
    }

    .export-certificate-form-container>.instructions {
        text-align: justify;
    }

    .export-certificate-form-container>.column {
        display: inline-block;
        position: relative;
        padding: 10px 15px 10px 0px;
        min-width: 300px;
        width: 33%;
        max-width: 33%;
        vertical-align: top;
    }

    .export-certificate-form-container .form-group {
        margin-bottom: 10px;
        width: 100%;
    }

    .export-certificate-form-container .form-group input,
    .export-certificate-form-container .form-group select {
        display: block;
        min-height: 38px;
        max-height: 38px;
        border: solid 1px #006798;
        border-radius: 0px;
        width: 100%;
        padding: 5px 10px;
    }

    .export-certificate-form-container .form-group label {
        font-size: 12px;
    }

    .export-certificate-form-container .form-group label span {
        color: red
    }

    #institution_container{
        display: none;
    }
</style>

<br>
<hr>

<div class="export-certificate-form-container">
    <div class="instructions">{translate key="plugins.generic.exportReviewerCertificate.reviewer.instruction"}</div>
    <div class="separator"></div>
    <div class="column">
        <div class="form-group">
            <label><span>*</span> {translate key="plugins.generic.exportReviewerCertificate.reviewer.genre_label"}</label>
            <select id="reviewer_gender">
                <option value="" selected>{translate key="plugins.generic.exportReviewerCertificate.reviewer.selectOption"}</option>
                <option value="female">{translate key="plugins.generic.exportReviewerCertificate.reviewer.femaleOption"}</option>
                <option value="male">{translate key="plugins.generic.exportReviewerCertificate.reviewer.maleOption"}</option>
            </select>
        </div>
    </div>
    <div class="column">
        <div class="form-group">
            <label>{translate key="plugins.generic.exportReviewerCertificate.reviewer.title_label"}</label>
            <input type="text" id="reviewer_title" placeholder="C | Dr | Dra | LI | MC | MRT" />
        </div>
    </div>
    <div class="column">
        <div class="form-group">
            <label><span>*</span> {translate key="plugins.generic.exportReviewerCertificate.reviewer.institution_type_label"}</label>
            <select id="reviewer_institution_type">
                <option value="" selected>{translate key="plugins.generic.exportReviewerCertificate.reviewer.selectOption"}</option>
                <option value="independent">{translate key="plugins.generic.exportReviewerCertificate.reviewer.independentTypeOption"}</option>
                <option value="institution">{translate key="plugins.generic.exportReviewerCertificate.reviewer.institutionTypeOption"}</option>
            </select>
        </div>
        <div id="institution_container" class="form-group">
            <label><span>*</span> {translate key="plugins.generic.exportReviewerCertificate.reviewer.institution_label"}</label>
            <input id="reviewer_institution" type="text" placeholder="{translate key='plugins.generic.exportReviewerCertificate.reviewer.institutionTypeOption'}" />
        </div>
    </div>
</div>

<div class="pkp_controllers_grid ">
    <div class="actions">
        <a href="{url page=" reviewer" op="download" submission=$submission->getId()}"
            target="_BLANK"
            title="{translate key="plugins.generic.exportReviewerCertificate.reviewer.button_title" }">{translate
            key="plugins.generic.exportReviewerCertificate.reviewer.button_label"}</a>
    </div>
</div>

<script>
    $(function(){
        $('.actions').hide();
    });
    function showButton(){
        $('.actions').hide();
        let activeButton = true;
        if($('#reviewer_gender option:selected').val() == ""){
            activeButton = false;
        }
        if($('#reviewer_institution_type option:selected').val() == ""){
            activeButton = false;
        }
        if($('#reviewer_institution_type :selected').val() == 'institution' && $('#reviewer_institution').val() == ""){
            activeButton = false;
        }
        if(activeButton){
            $('.actions').show();
        }
    }
    $(document).on('change','#reviewer_gender',function(){
        showButton();
    });
    $(document).on('change','#reviewer_institution_type',function(){
        if($(this,' :selected').val() == 'institution'){
            $('#institution_container').show();
            $('#reviewer_institution').val('');
        }
        if($(this,' :selected').val() == 'independent'){
            $('#institution_container').hide();
            $('#reviewer_institution').val('');
        }
        showButton();
    });
    $(document).on('keyup','#reviewer_institution',function(){
        showButton();
    });
    $('.actions a').on('click',function(){
        let href = $(this).attr('href');
        let params = "&reviewer_gender="+$('#reviewer_gender option:selected').val();
        params += "&reviewer_title="+($("#reviewer_title").val() != "" ? $("#reviewer_title").val() : "C. ");
        params += "&reviewer_institution_type="+$('#reviewer_institution_type :selected').val()
        params += "&reviewer_institution="+($('#reviewer_institution_type :selected').val() == 'institution' ? $('#reviewer_institution').val() : "")
        $(this).attr('href',href+params);
    });
</script>
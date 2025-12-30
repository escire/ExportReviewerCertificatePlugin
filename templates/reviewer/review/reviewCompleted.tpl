{* 
    @file plugins/generic/exportReviewerCertificate/templates/reviewer/review/reviewCompleted.tpl

    Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.

    @brief File to overwrite the reviewerCompleted template to add new input elements to interact with step 4 reviewer review process.

    @author epsomsegura
    @email segurajaramilloepsom@gmail.com
    @github https://github.com/epsomsegura
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

    #institution_container {
        display: none;
        line-height: 1.1;
        text-align: justify;
    }
</style>

<br>
<hr>

<div class="export-certificate-form-container">
    <div class="instructions">{translate key="plugins.generic.exportReviewerCertificate.reviewer.instruction"}</div>
    <div class="separator"></div>

    {if $certificateDownloaded}
        <div class="pkp_notification"
            style="margin: 10px 0; padding: 10px; background: #f5f5f5; border-left: 4px solid #22d322;">
            <strong>{translate key="plugins.generic.exportReviewerCertificate.certificate.download"}</strong>
            <p style="margin: 5px 0 0 0;">
                {translate key="plugins.generic.exportReviewerCertificate.certificate.alreadyDownloaded"}</p>
        </div>
    {else}
        <div class="column">
            <div class="form-group">
                <label>{translate key="plugins.generic.exportReviewerCertificate.reviewer.title_label"}</label>
                <input type="text" id="reviewer_title" placeholder="C | Dr | Dra | LI | MC | MRT" />
            </div>
        </div>
    {/if}
</div>

{if !$certificateDownloaded}
    <div class="pkp_controllers_grid ">
        <div class="actions">
            <a href="{url page="reviewer" op="download" submission=$submission->getId()}" target="_BLANK"
                title="{translate key="plugins.generic.exportReviewerCertificate.reviewer.button_title" }">{translate
                        key="plugins.generic.exportReviewerCertificate.reviewer.button_label"}</a>
        </div>
    </div>
{/if}

{if !$certificateDownloaded}
    <script>
        $('.actions a').on('click', function() {
            let href = $(this).attr('href');
            let params = "";
            params += "&reviewer_title=" + ($("#reviewer_title").val() != "" ? $("#reviewer_title").val() : "C. ");
            $(this).attr('href', href + params);
        });
    </script>
{/if}
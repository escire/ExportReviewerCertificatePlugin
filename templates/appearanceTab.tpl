{* 
    @file plugins/generic/exportReviewerCertificate/templates/appearanceTab.tpl

    Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.

    @brief File to build the html element and its interaction with the assigned form

    @author epsomsegura
    @email segurajaramilloepsom@gmail.com
    @github https://github.com/epsomsegura
*}

<tab id="exportjournalcertificate" label="{translate key="plugins.generic.exportReviewerCertificate.tabname"}">
    <pkp-form v-bind="components.{$smarty.const.FORM_EXPORT_REVIEWER_CERTIFICATE}" @set="set" />
</tab>
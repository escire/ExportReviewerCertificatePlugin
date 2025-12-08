<?php

/**
 * @file plugins/generic/exportReviewerCertificate/api/v1/index.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Handle API requests for export reviewer certificate settings operations.
 * 
 * This file is automatically copied to api/v1/_exportReviewerCertificateSettings/index.php
 * during plugin installation.
 */

return new \PKP\handler\APIHandler(new \APP\plugins\generic\exportReviewerCertificate\api\v1\ExportReviewerCertificateController());

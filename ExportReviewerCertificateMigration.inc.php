<?php

/**
 * @file plugins/generic/exportReviewerCertificate/ExportReviewerCertificatePlugin.inc.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ExportReviewerCertificateMigration
 * @brief Main class plugin
 * 
 * @owner: eScire 
 * @co_authors: eScire, Epsom Enrique Segura Jaramillo, Araceli Hernández Morales y Joel Torres Hernández
 * @email: contacto@escire.lat
 * @github: https://github.com/escire-ojs-plugins/exportReviewerCertificate
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class ExportReviewerCertificateMigration  extends Migration
{
    public function up(): void
    {
        Capsule::schema()->create('review_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('submission_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Capsule::schema()->drop('review_certificates');
    }
}
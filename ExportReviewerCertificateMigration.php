<?php

namespace APP\plugins\generic\exportReviewerCertificate;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExportReviewerCertificateMigration  extends Migration
{
    public function up(): void
    {
        Schema::create('review_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('submission_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('review_certificates');
    }
}

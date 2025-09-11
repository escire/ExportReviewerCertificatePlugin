<?php
/**
  * @file plugins\generic\exportReviewerCertificate\repositories\ReviewerCertificateRepository.php
  *
  * Copyright (c) 2025 Oscar Chacón - eScire
  * oscar@escire.lat
  *
  * @class ReviewerCertificateRepository
  *
  */
namespace APP\plugins\generic\exportReviewerCertificate\repositories;

use Illuminate\Support\Facades\DB;

class ReviewerCertificateRepository
{
	public function registerReviewerCertificate($userId, $submissionId): bool
	{
		$sql = "INSERT INTO
			review_certificates(user_id, submission_id, created_at, updated_at)
			VALUES
			(?, ?, ?, ?)";
		$now = date('Y-m-d H:i:s');
		$params = array_merge([$userId], [$submissionId], [$now], [$now]);
		DB::insert($sql, $params);
		return true;
	}

	public function getReviewerCertificate($userId, $submissionId): ?array
	{
		$sql = "SELECT *
			FROM review_certificates
			WHERE
				user_id = ? AND
				submission_id = ?";
		$params = array_merge([$userId], [$submissionId]);
		$result = DB::select($sql, $params);

		$returner = null;
		if (!empty($result)) {
			$returner = (array) $result[0];
		}
		unset($result);
		return $returner;
	}
}

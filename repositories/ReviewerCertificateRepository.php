<?php
/**
  * @file plugins\generic\exportReviewerCertificate\repositories\ReviewerCertificateRepository.php
  *
  * Copyright (c) 2025 Oscar Chacón - eScire
  * oscar@escire.lat
  * Copyright (c) 2025 Gustavo Casiano - eScire
  * gustavo@escire.lat
  *
  * @class ReviewerCertificateRepository
  *
  */
namespace APP\plugins\generic\exportReviewerCertificate\repositories;

use Illuminate\Support\Facades\DB;

class ReviewerCertificateRepository
{
	/**
	 * Register a reviewer certificate
	 * Checks if it already exists before inserting to avoid duplicates
	 * @param int $userId User ID (reviewer)
	 * @param int $submissionId Submission ID
	 * @return bool True if created successfully or already exists
	 */
	public function registerReviewerCertificate($userId, $submissionId): bool
	{
		if ($this->exists($userId, $submissionId)) {
			return true;
		}

		$sql = "INSERT INTO
			review_certificates(user_id, submission_id, created_at, updated_at)
			VALUES
			(?, ?, ?, ?)";
		$now = date('Y-m-d H:i:s');
		$params = [$userId, $submissionId, $now, $now];
		
		DB::insert($sql, $params);
		
		return $this->exists($userId, $submissionId);
	}

	/**
	 * Get reviewer certificate by user and submission
	 * @param int $userId User ID (reviewer)
	 * @param int $submissionId Submission ID
	 * @return array|null Array with data or null if not exists
	 */
	public function getReviewerCertificate($userId, $submissionId): ?array
	{
		$sql = "SELECT *
			FROM review_certificates
			WHERE
				user_id = ? AND
				submission_id = ?";
		$params = [$userId, $submissionId];
		$result = DB::select($sql, $params);

		$returner = null;
		if (!empty($result)) {
			$returner = (array) $result[0];
		}
		unset($result);
		return $returner;
	}

	/**
	 * Check if certificate exists for user and submission
	 * @param int $userId User ID
	 * @param int $submissionId Submission ID
	 * @return bool True if exists
	 */
	public function exists($userId, $submissionId): bool
	{
		$sql = "SELECT COUNT(*) as count
			FROM review_certificates
			WHERE
				user_id = ? AND
				submission_id = ?";
		$params = [$userId, $submissionId];
		$result = DB::select($sql, $params);

		return !empty($result) && $result[0]->count > 0;
	}

	/**
	 * Get certificate by ID
	 * @param int $certificateId Certificate ID
	 * @return array|null Array with data or null if not exists
	 */
	public function getById($certificateId): ?array
	{
		$sql = "SELECT *
			FROM review_certificates
			WHERE id = ?";
		$params = [$certificateId];
		$result = DB::select($sql, $params);

		$returner = null;
		if (!empty($result)) {
			$returner = (array) $result[0];
		}
		unset($result);
		return $returner;
	}

	/**
	 * Get all certificates for a user
	 * @param int $userId User ID
	 * @return array Array of certificates
	 */
	public function getByUserId($userId): array
	{
		$sql = "SELECT *
			FROM review_certificates
			WHERE user_id = ?
			ORDER BY created_at DESC";
		$params = [$userId];
		$result = DB::select($sql, $params);

		$certificates = [];
		if (!empty($result)) {
			foreach ($result as $row) {
				$certificates[] = (array) $row;
			}
		}
		return $certificates;
	}

	/**
	 * Get all certificates for a submission
	 * @param int $submissionId Submission ID
	 * @return array Array of certificates
	 */
	public function getBySubmissionId($submissionId): array
	{
		$sql = "SELECT *
			FROM review_certificates
			WHERE submission_id = ?
			ORDER BY created_at DESC";
		$params = [$submissionId];
		$result = DB::select($sql, $params);

		$certificates = [];
		if (!empty($result)) {
			foreach ($result as $row) {
				$certificates[] = (array) $row;
			}
		}
		return $certificates;
	}

	/**
	 * Count certificates by user
	 * @param int $userId User ID
	 * @return int Certificate count
	 */
	public function countByUserId($userId): int
	{
		$sql = "SELECT COUNT(*) as count
			FROM review_certificates
			WHERE user_id = ?";
		$params = [$userId];
		$result = DB::select($sql, $params);

		return !empty($result) ? (int) $result[0]->count : 0;
	}

	/**
	 * Count certificates by submission
	 * @param int $submissionId Submission ID
	 * @return int Certificate count
	 */
	public function countBySubmissionId($submissionId): int
	{
		$sql = "SELECT COUNT(*) as count
			FROM review_certificates
			WHERE submission_id = ?";
		$params = [$submissionId];
		$result = DB::select($sql, $params);

		return !empty($result) ? (int) $result[0]->count : 0;
	}

	/**
	 * Delete certificate by user and submission
	 * @param int $userId User ID
	 * @param int $submissionId Submission ID
	 * @return bool True if deleted successfully
	 */
	public function deleteByUserAndSubmission($userId, $submissionId): bool
	{
		$sql = "DELETE FROM review_certificates
			WHERE user_id = ? AND submission_id = ?";
		$params = [$userId, $submissionId];
		DB::delete($sql, $params);
		return true;
	}

	/**
	 * Delete certificate by ID
	 * @param int $certificateId Certificate ID
	 * @return bool True if deleted successfully
	 */
	public function deleteById($certificateId): bool
	{
		$sql = "DELETE FROM review_certificates WHERE id = ?";
		$params = [$certificateId];
		DB::delete($sql, $params);
		return true;
	}

	/**
	 * Count all certificates
	 * @return int Total certificates
	 */
	public function countAll(): int
	{
		$sql = "SELECT COUNT(*) as count FROM review_certificates";
		$result = DB::select($sql);

		return !empty($result) ? (int) $result[0]->count : 0;
	}
}

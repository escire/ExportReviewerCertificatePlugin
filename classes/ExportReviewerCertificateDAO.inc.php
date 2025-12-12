<?php
/**
  * @file plugins\generic\exportReviewerCertificate\repositories\ReviewerCertificateRepository.php
  *
  * Copyright (c) 2025 Gustavo Casiano - eScire
  * gustavo@escire.lat
  *
  * @class ExportReviewerCertificateDAO
  *
  */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.exportReviewerCertificate.classes.ExportReviewerCertificate');

use Illuminate\Support\Facades\DB;

/**
 * @class ExportReviewerCertificateDAO
 * @brief Clase para operaciones de base de datos de certificados de revisores
 */
class ExportReviewerCertificateDAO extends DAO
{
	/**
	 * Crear un nuevo registro de certificado de revisor
	 * @param int $userId ID del usuario (revisor)
	 * @param int $submissionId ID del submission (artículo)
	 * @return ExportReviewerCertificate|null Objeto del certificado creado o null si falla
	 */
	public function insert($userId, $submissionId): ?ExportReviewerCertificate
	{
		// Verificar si ya existe un certificado
		$existing = $this->getByUserAndSubmission($userId, $submissionId);
		if ($existing) {
			// Ya existe, devolver el existente
			return $existing;
		}

		$sql = "INSERT INTO review_certificates 
				(user_id, submission_id, created_at, updated_at)
				VALUES (?, ?, ?, ?)";
		$now = date('Y-m-d H:i:s');
		$params = [$userId, $submissionId, $now, $now];
		
		$this->update($sql, $params);
		
		// Obtener el ID insertado (usando método heredado de DAO)
		$certificateId = $this->_getInsertId();
		
		// Retornar el objeto creado
		return $this->getById($certificateId);
	}

	/**
	 * Registrar un certificado de revisor (alias de insert)
	 * @param int $userId ID del usuario (revisor)
	 * @param int $submissionId ID del submission (artículo)
	 * @return bool True si se creó exitosamente
	 */
	public function registerReviewerCertificate($userId, $submissionId): bool
	{
		$result = $this->insert($userId, $submissionId);
		return !is_null($result);
	}

	/**
	 * Obtener un certificado por su ID
	 * @param int $certificateId ID del certificado
	 * @return ExportReviewerCertificate|null
	 */
	public function getById($certificateId): ?ExportReviewerCertificate
	{
		$result = $this->retrieve(
			'SELECT * FROM review_certificates WHERE id = ?',
			[(int) $certificateId]
		);
		
		$row = $result->current();
		return $row ? $this->_fromRow((array) $row) : null;
	}

	/**
	 * Obtener un certificado por usuario y submission
	 * @param int $userId ID del usuario (revisor)
	 * @param int $submissionId ID del submission
	 * @return ExportReviewerCertificate|null
	 */
	public function getByUserAndSubmission($userId, $submissionId): ?ExportReviewerCertificate
	{
		$result = $this->retrieve(
			'SELECT * FROM review_certificates WHERE user_id = ? AND submission_id = ?',
			[(int) $userId, (int) $submissionId]
		);
		
		$row = $result->current();
		return $row ? $this->_fromRow((array) $row) : null;
	}

	/**
	 * Obtener certificado de revisor
	 * @param int $userId ID del usuario
	 * @param int $submissionId ID del submission
	 * @return array|null Array con los datos o null
	 */
	public function getReviewerCertificate($userId, $submissionId): ?array
	{
		$certificate = $this->getByUserAndSubmission($userId, $submissionId);
		return $certificate ? $certificate->toArray() : null;
	}

	/**
	 * Obtener todos los certificados de un usuario
	 * @param int $userId ID del usuario
	 * @return array Array de objetos ExportReviewerCertificate
	 */
	public function getByUserId($userId): array
	{
		$result = $this->retrieve(
			'SELECT * FROM review_certificates WHERE user_id = ? ORDER BY created_at DESC',
			[(int) $userId]
		);
		
		$certificates = [];
		foreach ($result as $row) {
			$certificates[] = $this->_fromRow((array) $row);
		}
		return $certificates;
	}

	/**
	 * Obtener todos los certificados de un submission
	 * @param int $submissionId ID del submission
	 * @return array Array de objetos ExportReviewerCertificate
	 */
	public function getBySubmissionId($submissionId): array
	{
		$result = $this->retrieve(
			'SELECT * FROM review_certificates WHERE submission_id = ? ORDER BY created_at DESC',
			[(int) $submissionId]
		);
		
		$certificates = [];
		foreach ($result as $row) {
			$certificates[] = $this->_fromRow((array) $row);
		}
		return $certificates;
	}

	/**
	 * Actualizar un certificado
	 * @param ExportReviewerCertificate $certificate Objeto del certificado
	 * @return bool True si se actualizó exitosamente
	 */
	public function updateObject($certificate): bool
	{
		$sql = "UPDATE review_certificates 
				SET user_id = ?, 
					submission_id = ?, 
					updated_at = ?
				WHERE id = ?";
		
		$now = date('Y-m-d H:i:s');
		$params = [
			$certificate->getUserId(),
			$certificate->getSubmissionId(),
			$now,
			$certificate->getId()
		];
		
		$this->update($sql, $params);
		return true;
	}

	/**
	 * Eliminar un certificado por ID
	 * @param int $certificateId ID del certificado
	 * @return bool True si se eliminó exitosamente
	 */
	public function deleteById($certificateId): bool
	{
		$sql = "DELETE FROM review_certificates WHERE id = ?";
		$this->update($sql, [$certificateId]);
		return true;
	}

	/**
	 * Eliminar un certificado por usuario y submission
	 * @param int $userId ID del usuario
	 * @param int $submissionId ID del submission
	 * @return bool True si se eliminó exitosamente
	 */
	public function deleteByUserAndSubmission($userId, $submissionId): bool
	{
		$sql = "DELETE FROM review_certificates 
				WHERE user_id = ? AND submission_id = ?";
		$this->update($sql, [$userId, $submissionId]);
		return true;
	}

	/**
	 * Verificar si existe un certificado para un usuario y submission
	 * @param int $userId ID del usuario
	 * @param int $submissionId ID del submission
	 * @return bool True si existe
	 */
	public function exists($userId, $submissionId): bool
	{
		$certificate = $this->getByUserAndSubmission($userId, $submissionId);
		return !is_null($certificate);
	}

	/**
	 * Contar certificados por usuario
	 * @param int $userId ID del usuario
	 * @return int Cantidad de certificados
	 */
	public function countByUserId($userId): int
	{
		$result = $this->retrieve(
			'SELECT COUNT(*) as count FROM review_certificates WHERE user_id = ?',
			[(int) $userId]
		);
		
		$row = $result->current();
		return $row ? (int) $row->count : 0;
	}

	/**
	 * Contar certificados por submission
	 * @param int $submissionId ID del submission
	 * @return int Cantidad de certificados
	 */
	public function countBySubmissionId($submissionId): int
	{
		$result = $this->retrieve(
			'SELECT COUNT(*) as count FROM review_certificates WHERE submission_id = ?',
			[(int) $submissionId]
		);
		
		$row = $result->current();
		return $row ? (int) $row->count : 0;
	}

	/**
	 * Obtener todos los certificados con paginación
	 * @param int $limit Límite de registros
	 * @param int $offset Desplazamiento
	 * @return array Array de objetos ExportReviewerCertificate
	 */
	public function getAll($limit = null, $offset = 0): array
	{
		$params = [];
		$sql = 'SELECT * FROM review_certificates ORDER BY created_at DESC';
		
		if ($limit !== null) {
			$sql .= ' LIMIT ? OFFSET ?';
			$params = [(int) $limit, (int) $offset];
		}
		
		$result = $this->retrieve($sql, $params);
		
		$certificates = [];
		foreach ($result as $row) {
			$certificates[] = $this->_fromRow((array) $row);
		}
		return $certificates;
	}

	/**
	 * Contar todos los certificados
	 * @return int Total de certificados
	 */
	public function countAll(): int
	{
		$result = $this->retrieve('SELECT COUNT(*) as count FROM review_certificates');
		
		$row = $result->current();
		return $row ? (int) $row->count : 0;
	}

	/**
	 * Convertir una fila de base de datos a un objeto ExportReviewerCertificate
	 * @param array $row Fila de la base de datos
	 * @return ExportReviewerCertificate
	 */
	private function _fromRow($row): ExportReviewerCertificate
	{
		$certificate = new ExportReviewerCertificate();
		$certificate->setId((int) $row['id']);
		$certificate->setUserId((int) $row['user_id']);
		$certificate->setSubmissionId((int) $row['submission_id']);
		$certificate->setCreatedAt($row['created_at']);
		$certificate->setUpdatedAt($row['updated_at']);
		return $certificate;
	}

	/**
	 * Obtener el nombre de la tabla
	 * @return string
	 */
	public function getTableName(): string
	{
		return 'review_certificates';
	}
}
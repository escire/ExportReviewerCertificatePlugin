<?php
/**
  * @file plugins\generic\exportReviewerCertificate\classes\ExportReviewerCertificate.inc.php
  *
  * Copyright (c) 2025 Gustavo Casiano - eScire
  * gustavo@escire.lat
  *
  * @class ExportReviewerCertificate
  *
  */

import('lib.pkp.classes.core.DataObject');

/**
 * @class ExportReviewerCertificate
 * @brief Clase que representa un certificado de revisor en la base de datos
 */
class ExportReviewerCertificate extends DataObject
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    //
    // Getters y Setters
    //

    /**
     * Obtener el ID del certificado
     * @return int
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Establecer el ID del certificado
     * @param int $id
     */
    public function setId($id)
    {
        $this->setData('id', $id);
    }

    /**
     * Obtener el ID del usuario (revisor)
     * @return int
     */
    public function getUserId()
    {
        return $this->getData('user_id');
    }

    /**
     * Establecer el ID del usuario (revisor)
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->setData('user_id', $userId);
    }

    /**
     * Obtener el ID del submission (artículo)
     * @return int
     */
    public function getSubmissionId()
    {
        return $this->getData('submission_id');
    }

    /**
     * Establecer el ID del submission (artículo)
     * @param int $submissionId
     */
    public function setSubmissionId($submissionId)
    {
        $this->setData('submission_id', $submissionId);
    }

    /**
     * Obtener la fecha de creación
     * @return string Fecha en formato Y-m-d H:i:s
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * Establecer la fecha de creación
     * @param string $createdAt Fecha en formato Y-m-d H:i:s
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
    }

    /**
     * Obtener la fecha de última actualización
     * @return string Fecha en formato Y-m-d H:i:s
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * Establecer la fecha de última actualización
     * @param string $updatedAt Fecha en formato Y-m-d H:i:s
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData('updated_at', $updatedAt);
    }

    /**
     * Verificar si el certificado ya existe para este revisor y submission
     * @return bool
     */
    public function exists()
    {
        return !is_null($this->getId());
    }

    /**
     * Obtener todos los datos del certificado como array
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'submission_id' => $this->getSubmissionId(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }
}


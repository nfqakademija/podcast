<?php


namespace App\Interfaces;

interface MailableEntity
{
    public function getEmail(): ?string;

    public function getConfirmationToken(): ?string;
}

<?php


namespace App\Interfaces;


interface Confirmable
{
    public function getEmail(): ?string;

    public function getConfirmationToken(): ?string;
}
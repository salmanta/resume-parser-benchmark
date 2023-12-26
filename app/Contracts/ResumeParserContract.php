<?php

namespace App\Contracts;

interface ResumeParserContract
{
    public function getName(): string;
    public function parse(string $filePath): array;
}

<?php

interface CommandInterface
{
    public function getName(): string;
    public function execute(array $args): void;
}
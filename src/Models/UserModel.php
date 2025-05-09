<?php

namespace App\Models;

class UserModel {
    private ?int $id;
    private string $name;
    private float $credit;

    public function __construct(string $name, float $credit, ?int $id = null) {
        $this->id = $id;
        $this->name = $name;
        $this->credit = $credit;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getCredit(): float {
        return $this->credit;
    }

    public function setCredit(float $credit): void {
        $this->credit = $credit;
    }
}
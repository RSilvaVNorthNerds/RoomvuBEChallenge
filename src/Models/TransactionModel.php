<?php
    namespace App\Models;

    class TransactionModel {
        private ?int $id;
        private int $user_id;
        private float $amount;
        private string $date;
        private ?string $vanished_at;

        public function __construct(int $user_id, float $amount, string $date, ?int $id = null, ?string $vanished_at = null) {
            $this->id = $id;
            $this->user_id = $user_id;
            $this->amount = $amount;
            $this->date = $date;
            $this->vanished_at = $vanished_at;
        }

        public function getId(): ?int {
            return $this->id;
        }

        public function getUserId(): int {
            return $this->user_id;
        }

        public function getAmount(): float {
            return $this->amount;
        }

        public function getDate(): string {
            return $this->date;
        }

        public function getVanishedAt(): ?string {
            return $this->vanished_at;
        }

        public function setVanishedAt(string $vanished_at): void {
            $this->vanished_at = $vanished_at;
        }
    }


<?php
declare(strict_types=1);

final class Ledger
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function cents(string $value): int
    {
        $value = trim($value);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException(
                'Amount must be a positive number with up to 2 decimal places.'
            );
        }

        $parts = explode('.', $value, 2);
        $whole = $parts[0];
        $fraction = $parts[1] ?? '';

        $fraction = str_pad($fraction, 2, '0');

        $cents = ((int) $whole * 100) + (int) $fraction;

        if ($cents <= 0) {
            throw new InvalidArgumentException(
                'Amount must be greater than zero.'
            );
        }

        return $cents;
    }

    private function entryCents(string $value): int
    {
        $value = trim($value);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException(
                'Journal amount must be a valid number.'
            );
        }

        $parts = explode('.', $value, 2);
        $whole = $parts[0];
        $fraction = $parts[1] ?? '';

        $fraction = str_pad($fraction, 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public function post(array $data, int $userId): array
    {
        $amountCents = $this->cents((string) ($data['amount'] ?? ''));

        if (count($data['entries'] ?? []) < 2) {
            throw new InvalidArgumentException(
                'At least two journal entries are required.'
            );
        }

        $debit = 0;
        $credit = 0;

        foreach ($data['entries'] as $entry) {
            $d = $this->entryCents((string) ($entry['debit'] ?? '0'));
            $c = $this->entryCents((string) ($entry['credit'] ?? '0'));

            if (($d > 0 && $c > 0) || ($d === 0 && $c === 0)) {
                throw new InvalidArgumentException(
                    'Each journal line must contain either debit or credit.'
                );
            }

            $debit += $d;
            $credit += $c;
        }

        if ($debit !== $credit || $debit !== $amountCents) {
            throw new InvalidArgumentException(
                'Journal is unbalanced or does not match the transaction amount.'
            );
        }

        if (
            (int) $data['entries'][0]['account_id'] ===
            (int) $data['entries'][1]['account_id']
        ) {
            throw new InvalidArgumentException(
                'Debit and credit accounts must be different.'
            );
        }

        $currency = strtoupper((string) ($data['currency'] ?? 'USD'));

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException(
                'Currency must be a 3-letter ISO code.'
            );
        }

        $key = trim((string) ($data['idempotency_key'] ?? ''));

        if ($key === '') {
            throw new InvalidArgumentException(
                'Idempotency key is required.'
            );
        }

        $this->pdo->beginTransaction();

        try {
            $existing = $this->pdo->prepare(
                'SELECT id, transaction_reference
                 FROM transactions
                 WHERE idempotency_key = ?
                 FOR UPDATE'
            );

            $existing->execute([$key]);

            $row = $existing->fetch();

            if ($row) {
                $this->pdo->commit();

                return [
                    'id' => $row['id'],
                    'reference' => $row['transaction_reference'],
                    'duplicate' => true
                ];
            }

            $ref = 'TXN-' .
                date('YmdHis') .
                '-' .
                strtoupper(bin2hex(random_bytes(3)));

            $st = $this->pdo->prepare(
                'INSERT INTO transactions
                (
                    transaction_reference,
                    customer_id,
                    funding_source_id,
                    transaction_type,
                    amount,
                    currency,
                    status,
                    description,
                    idempotency_key,
                    created_by
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $st->execute([
                $ref,
                !empty($data['customer_id'])
                    ? (int) $data['customer_id']
                    : null,
                !empty($data['funding_source_id'])
                    ? (int) $data['funding_source_id']
                    : null,
                trim((string) $data['transaction_type']),
                $this->money($amountCents),
                $currency,
                'posted',
                $data['description'] ?? null,
                $key,
                $userId
            ]);

            $txId = (int) $this->pdo->lastInsertId();

            $je = $this->pdo->prepare(
                'INSERT INTO journal_entries
                (
                    transaction_id,
                    account_id,
                    debit,
                    credit,
                    description
                )
                VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($data['entries'] as $entry) {
                $je->execute([
                    $txId,
                    (int) $entry['account_id'],
                    $entry['debit'] ?: '0.00',
                    $entry['credit'] ?: '0.00',
                    $entry['description'] ?? null
                ]);
            }

            $audit = $this->pdo->prepare(
                'INSERT INTO audit_logs
                (
                    user_id,
                    action,
                    entity_type,
                    entity_id,
                    new_values,
                    ip_address,
                    user_agent
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $audit->execute([
                $userId,
                'POST',
                'transaction',
                $txId,
                json_encode([
                    'reference' => $ref,
                    'amount' => $this->money($amountCents),
                    'currency' => $currency
                ]),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            $this->pdo->commit();

            return [
                'id' => $txId,
                'reference' => $ref,
                'duplicate' => false
            ];

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function reverse(int $id, int $userId): array
    {
        $this->pdo->beginTransaction();

        try {
            $st = $this->pdo->prepare(
                'SELECT *
                 FROM transactions
                 WHERE id = ?
                 FOR UPDATE'
            );

            $st->execute([$id]);
            $tx = $st->fetch();

            if (!$tx) {
                throw new RuntimeException(
                    'Transaction not found.'
                );
            }

            if ($tx['status'] !== 'posted') {
                throw new RuntimeException(
                    'Only posted transactions can be reversed.'
                );
            }

            $lines = $this->pdo->prepare(
                'SELECT *
                 FROM journal_entries
                 WHERE transaction_id = ?'
            );

            $lines->execute([$id]);
            $entries = $lines->fetchAll();

            if (!$entries) {
                throw new RuntimeException(
                    'Transaction has no journal entries.'
                );
            }

            $ref = 'REV-' . $tx['transaction_reference'];

            $ins = $this->pdo->prepare(
                'INSERT INTO transactions
                (
                    transaction_reference,
                    customer_id,
                    funding_source_id,
                    transaction_type,
                    amount,
                    currency,
                    status,
                    description,
                    idempotency_key,
                    created_by,
                    reversal_of
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $ins->execute([
                $ref,
                $tx['customer_id'],
                $tx['funding_source_id'],
                'reversal',
                $tx['amount'],
                $tx['currency'],
                'posted',
                'Reversal of ' . $tx['transaction_reference'],
                'reverse-' . $id . '-' . bin2hex(random_bytes(4)),
                $userId,
                $id
            ]);

            $rid = (int) $this->pdo->lastInsertId();

            $je = $this->pdo->prepare(
                'INSERT INTO journal_entries
                (
                    transaction_id,
                    account_id,
                    debit,
                    credit,
                    description
                )
                VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($entries as $line) {
                $je->execute([
                    $rid,
                    (int) $line['account_id'],
                    $line['credit'],
                    $line['debit'],
                    'Reversal of ' . $tx['transaction_reference']
                ]);
            }

            $update = $this->pdo->prepare(
                'UPDATE transactions
                 SET status = ?
                 WHERE id = ?'
            );

            $update->execute([
                'reversed',
                $id
            ]);

            $audit = $this->pdo->prepare(
                'INSERT INTO audit_logs
                (
                    user_id,
                    action,
                    entity_type,
                    entity_id,
                    new_values,
                    ip_address,
                    user_agent
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $audit->execute([
                $userId,
                'REVERSE',
                'transaction',
                $id,
                json_encode([
                    'reversal_id' => $rid,
                    'reference' => $ref
                ]),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            $this->pdo->commit();

            return [
                'id' => $rid,
                'reference' => $ref
            ];

        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}
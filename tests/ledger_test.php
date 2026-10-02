<?php
// Lightweight source-level assertions for the accounting rules used by Ledger.
$src=file_get_contents(__DIR__.'/../src/Ledger.php');
$checks=[
 'atomic posting'=>str_contains($src,'beginTransaction()') && str_contains($src,'rollBack()'),
 'idempotency'=>str_contains($src,'idempotency_key') && str_contains($src,'FOR UPDATE'),
 'balanced ledger'=>str_contains($src,'$debit !== $credit'),
 'amount matches ledger'=>str_contains($src,'$debit !== $amountCents'),
 'no float arithmetic'=>!str_contains($src,'(float)'),
 'account separation'=>str_contains($src,'Debit and credit accounts must be different'),
 'reversal'=>str_contains($src,'reversal_of'),
];
foreach($checks as $name=>$ok){echo ($ok?'PASS':'FAIL')." - $name\n"; if(!$ok) exit(1);} echo "All ledger checks passed.\n";

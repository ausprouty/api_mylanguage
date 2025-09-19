<?php

$once    = in_array('--once', $argv, true);
$langArg = array_values(array_filter($argv, fn($a)=>str_starts_with($a,'--lang=')))[0] ?? null;
$client  = array_values(array_filter($argv, fn($a)=>str_starts_with($a,'--client=')))[0] ?? null;
$type    = array_values(array_filter($argv, fn($a)=>str_starts_with($a,'--type=')))[0] ?? null;
$subject = array_values(array_filter($argv, fn($a)=>str_starts_with($a,'--subject=')))[0] ?? null;
$variant = array_values(array_filter($argv, fn($a)=>str_starts_with($a,'--variant=')))[0] ?? null;

$filters = [];
if ($langArg) $filters[] = "targetLanguageCodeIso = " . $db->quote(substr($langArg, 7));
if ($client)  $filters[] = "clientCode = " . $db->quote(substr($client, 9));
if ($type)    $filters[] = "resourceType = " . $db->quote(substr($type, 7));
if ($subject) $filters[] = "subject = " . $db->quote(substr($subject, 10));
if ($variant) $filters[] = "variant = " . $db->quote(substr($variant, 10));

$where = "status='queued' AND runAfter<=NOW()";
if ($filters) $where .= " AND " . implode(' AND ', $filters);

<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Recipient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RecipientsImport;

class RecipientService
{
    /**
     * Import recipients from CSV/Excel file.
     */
    public function importFromFile(UploadedFile $file, Campaign $campaign): array
    {
        $extension = $file->getClientOriginalExtension();
        $rows = [];

        if (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($file);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->parseExcel($file);
        } else {
            return ['success' => false, 'message' => 'Unsupported file format. Use CSV or Excel.', 'imported' => 0, 'skipped' => 0, 'errors' => []];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $existingEmails = $campaign->recipients()->pluck('email')->toArray();

        foreach ($rows as $index => $row) {
            $email = trim($row['email'] ?? '');
            $name = trim($row['name'] ?? $row['nama'] ?? '');

            if (empty($email)) {
                $skipped++;
                $errors[] = "Row " . ($index + 2) . ": Email is empty";
                continue;
            }

            // Validate email format
            $validation = $this->validateEmail($email);
            if (!$validation['valid']) {
                $skipped++;
                $errors[] = "Row " . ($index + 2) . ": {$email} - {$validation['message']}";
                continue;
            }

            // Check duplicates
            if (in_array(strtolower($email), array_map('strtolower', $existingEmails))) {
                $skipped++;
                $errors[] = "Row " . ($index + 2) . ": {$email} - Duplicate email";
                continue;
            }

            // Extract custom fields (everything except email and name/nama)
            $customFields = array_filter($row, function ($key) {
                return !in_array(strtolower($key), ['email', 'name', 'nama']);
            }, ARRAY_FILTER_USE_KEY);

            Recipient::create([
                'campaign_id' => $campaign->id,
                'email' => strtolower($email),
                'name' => $name,
                'custom_fields' => !empty($customFields) ? $customFields : null,
                'status' => 'pending',
            ]);

            $existingEmails[] = strtolower($email);
            $imported++;
        }

        return [
            'success' => true,
            'message' => "Imported {$imported} recipients. Skipped {$skipped}.",
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Parse CSV file.
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) return [];

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        // Normalize headers
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, $data);
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Parse Excel file.
     */
    protected function parseExcel(UploadedFile $file): array
    {
        $rows = [];
        try {
            $data = Excel::toArray(null, $file);
            if (empty($data) || empty($data[0])) return [];

            $sheet = $data[0];
            $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $sheet[0]);

            for ($i = 1; $i < count($sheet); $i++) {
                if (count($sheet[$i]) === count($headers)) {
                    $rows[] = array_combine($headers, $sheet[$i]);
                }
            }
        } catch (\Exception $e) {
            // Fall back empty
        }
        return $rows;
    }

    /**
     * Validate email address.
     */
    public function validateEmail(string $email): array
    {
        // Basic format check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Invalid email format'];
        }

        // Disposable email check
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $disposableDomains = config('bulkemail.disposable_domains', []);

        if (in_array($domain, $disposableDomains)) {
            return ['valid' => false, 'message' => 'Disposable email not allowed'];
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Get available merge tags from a campaign's recipients.
     */
    public function getAvailableMergeTags(Campaign $campaign): array
    {
        $tags = ['nama', 'email'];

        $firstRecipient = $campaign->recipients()->first();
        if ($firstRecipient && is_array($firstRecipient->custom_fields)) {
            $tags = array_merge($tags, array_keys($firstRecipient->custom_fields));
        }

        return $tags;
    }
}

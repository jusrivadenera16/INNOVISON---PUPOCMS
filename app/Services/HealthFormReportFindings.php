<?php

namespace App\Services;

use App\Models\HealthProfile;

class HealthFormReportFindings
{
    public function text(HealthProfile $profile): string
    {
        $parts = [];
        $add = function ($value, string $prefix = '') use (&$parts) {
            foreach ($this->clean($value) as $text) {
                $key = mb_strtolower(rtrim($prefix.$text, '. '));
                if (!isset($parts[$key])) $parts[$key] = $prefix.$text;
            }
        };
        $add($profile->medical_history);
        $add($profile->other_illness);
        if (strtolower((string) $profile->has_disability) === 'yes') {
            $add($profile->disability_type ?: 'Reported disability (not specified)', 'Disability: ');
        }
        $add($profile->food_allergies, 'Food allergy: ');
        $add($profile->medicine_allergies, 'Medicine allergy: ');
        $add($profile->other_med_allergies, 'Medicine allergy: ');

        // Condition-specific fields plus relevant clauses from legacy review notes.
        // Never export workflow status, encoding/pending notes, or the physician field.
        foreach (['medical_condition_remarks', 'med_cert_findings_details', 'xray_findings_details', 'med_assessment_remarks', 'assessment_remarks'] as $field) {
            $add($profile->{$field});
        }
        if (strtolower((string) $profile->has_illness) === 'yes' && !$this->clean($profile->medical_history) && !$this->clean($profile->other_illness)) {
            $add('Reported illness (not specified)');
        }
        $hasFindings = collect([$profile->med_cert_findings, $profile->xray_findings])
            ->contains(fn ($value) => in_array(strtolower(trim((string) $value)), ['with findings', 'abnormal'], true));
        if ($parts) return implode('; ', array_values($parts));
        if ($hasFindings) return 'With findings (details not specified)';
        $normal = collect([$profile->med_cert_findings, $profile->xray_findings])
            ->contains(fn ($value) => in_array(strtolower(trim((string) $value)), ['no findings / normal', 'no findings', 'normal'], true));

        return strtolower((string) $profile->has_illness) === 'no' || $normal
            ? 'No condition or abnormal findings reported.' : 'Not specified';
    }

    private function clean($value): array
    {
        if (is_array($value)) return array_merge([], ...array_map(fn ($item) => $this->clean($item), array_values($value)));
        $value = strip_tags((string) $value);
        $clauses = preg_split('/\R|\||;|(?:^|\s)[\-\x{2022}]\s+|(?<!Dr\.)(?<!dr\.)(?<!DR\.)(?<=[.!?])\s+(?=[A-Z])/u', $value) ?: [];
        $result = [];
        foreach ($clauses as $clause) {
            $clause = trim(preg_replace('/\s+/u', ' ', $clause), " \t\n\r\0\x0B.-");
            // Remove certificate/signature tails even when appended to a diagnosis.
            $clause = trim(preg_replace('/\b(?:(?:MC|medical (?:certificate|clearance))\s+(?:was\s+)?issued\b|(?:dr\.?|doctor|physician)\s+).*$/iu', '', $clause));
            if ($clause === '' || preg_match('/^(?:PE\b|physical exam(?:ination)?\b|LMP\b|no subjective complaints\b|(?:MC|CXR|chest x[ -]?ray)(?:\s+and\s+(?:MC|CXR))?\s+(?:normal|noted)\b|not yet conducted\b|completed\s*\/\s*passed\b)/i', $clause)) continue;
            if (preg_match('/^(?:(?:past )?medical history\s*(?:\/\s*surgical history)?\s*:\s*)?(?:none(?: reported)?|no(?: known)? (?:illness|allergies|medical condition|findings)(?: reported)?|normal|unremarkable|no findings\s*\/\s*normal|n\/?a|not specified|\[\])\.?$/i', $clause)) continue;
            $result[] = rtrim($clause, '. ');
        }
        return $result;
    }
}

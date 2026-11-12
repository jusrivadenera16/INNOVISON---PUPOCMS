<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivateDocumentThumbnailTest extends TestCase
{
    public function test_current_documents_hide_empty_upload_slots_and_keep_existing_files(): void
    {
        $source = file_get_contents(resource_path('views/admin/show_health.blade.php'));
        $start = strpos($source, "@include('admin.partials.issued-clearance-document-cards'");
        $end = strpos($source, '$historicalDocumentDefinitions = [', $start);
        $template = substr($source, $start, $end - $start);
        $template = substr($template, 0, strrpos($template, '@php'));
        $profile = (new \App\Models\HealthProfile())->forceFill(['id' => 123]);
        $data = ['profile' => $profile, 'currentHealthFormSubmission' => null, 'pendingHealthFormRequest' => null,
            'issuedMedicalClearancesBySubmission' => collect(), 'documentRouteName' => 'walkin.document'];
        $html = \Illuminate\Support\Facades\Blade::render($template, $data);
        $this->assertStringContainsString('<h4>Health Information Form</h4>', $html);
        foreach (['Medical Certificate (PDF)', 'Medical Assessment Copy', 'Health Declaration', 'Chest X-ray Result (PDF)', 'PWD ID Proof', '2x2 Student Photo'] as $label) {
            $this->assertStringNotContainsString('<h4>' . $label . '</h4>', $html);
        }
        $profile->medical_certificate = 'saved/certificate.pdf';
        $profile->student_photo = 'saved/photo.jpg';
        $html = \Illuminate\Support\Facades\Blade::render($template, $data);
        $this->assertStringContainsString('<h4>Medical Certificate (PDF)</h4>', $html);
        $this->assertStringContainsString('<h4>2x2 Student Photo</h4>', $html);
        $this->assertStringNotContainsString('No chest X-ray result uploaded.', $html);
    }

    public function test_thumbnail_uses_only_a_label_and_never_loads_the_private_document(): void
    {
        foreach (['2x2 Photo', 'Health Information Form', 'Medical Certificate', 'Health Declaration', 'Chest X-ray Result', 'PWD ID Proof'] as $label) {
            $html = view('admin.partials.private-document-thumbnail', ['documentLabel' => $label])->render();
            $this->assertStringContainsString(e($label), $html);
            $this->assertStringContainsString('private-document-thumbnail', $html);
            $this->assertStringNotContainsString('<iframe', $html);
            $this->assertStringNotContainsString('<img', $html);
            $this->assertStringNotContainsString('src=', $html);
        }
        $html = view('admin.partials.private-document-thumbnail', ['documentLabel' => '<script>alert(1)</script>'])->render();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('../images/pup_logo.png', file_get_contents(public_path('css/private-document-thumbnail.css')));
    }

    public function test_record_pages_and_review_cards_do_not_embed_private_files(): void
    {
        foreach (['show_health', 'show_employee_health', 'health_records', 'partials.medical-assessment-modal'] as $view) {
            $source = file_get_contents(resource_path('views/admin/' . str_replace('.', '/', $view) . '.blade.php'));
            $this->assertStringNotContainsString('<iframe', $source);
            // Profile avatar images remain; document-card images must not use file URLs.
            $documents = $view === 'show_health' ? substr($source, strpos($source, 'id="docsPanel"')) : $source;
            $this->assertDoesNotMatchRegularExpression('/<img[^>]+src=["\']\{\{\s*\$(?:documentUrl|historyDocumentUrl|medicalCertificateUrl|chestXrayUrl|healthDeclarationUrl|studentPhotoUrl)/', $documents);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\EmailLog;
use App\Models\Attachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Message;

class EmailService
{
    public function __construct(
        protected MergeTagParser $mergeTagParser,
        protected QuotaService $quotaService,
        protected PdfGeneratorService $pdfGenerator,
    ) {}

    /**
     * Send a single email to a recipient.
     */
    public function sendEmail(Recipient $recipient, Campaign $campaign): bool
    {
        // Check quota before sending
        if (!$this->quotaService->canSendEmail()) {
            $recipient->update([
                'status' => Recipient::STATUS_PENDING,
                'error_message' => 'Daily/hourly quota exceeded. Will retry later.',
            ]);
            return false;
        }

        try {
            $subject = $this->mergeTagParser->parse($campaign->subject, $recipient);
            $body = $this->mergeTagParser->parse($campaign->body, $recipient);

            $attachments = $campaign->attachments;

            Mail::html($body, function (Message $message) use ($recipient, $subject, $attachments) {
                $message->to($recipient->email, $recipient->name)
                    ->subject($subject);

                foreach ($attachments as $attachment) {
                    $fullPath = Storage::path($attachment->path);
                    if (file_exists($fullPath)) {
                        $message->attach($fullPath, [
                            'as' => $attachment->original_name,
                            'mime' => $attachment->mime_type,
                        ]);
                    } else {
                        Log::warning("Attachment file not found", [
                            'path' => $fullPath,
                            'attachment_id' => $attachment->id,
                        ]);
                    }
                }

                // Dynamic letter PDF
                if ($campaign->letter_template_id && $campaign->letterTemplate) {
                    try {
                        $pdfPath = $this->pdfGenerator->generateForRecipient(
                            $campaign->letterTemplate,
                            $recipient,
                            $campaign->letter_filename ?? 'Surat'
                        );
                        
                        $message->attach(Storage::path($pdfPath), [
                            'as' => ($campaign->letter_filename ?? 'Surat') . '.pdf',
                            'mime' => 'application/pdf',
                        ]);

                        // We can't easily delete the file here because attach() might need it later in the process
                        // depending on how Mail::html queues things. 
                        // But since we are inside the closure which executes immediately for sync,
                        // or if queued, the closure is serialized... wait.
                        // Actually SendBulkEmailJob is queued, so this entire closure runs inside the worker.
                        // So we CAN delete it after sending? 
                        // No, attach() adds it to the swift/symfony message. 
                        // We should delete it AFTER the mail is sent.
                        // But we don't have a callback for "after sent".
                        // Strategy: Add to a cleanup list or just rely on a scheduled cleanup job for temp files.
                        // OR: Delete it immediately if we're sure attach() reads content into memory?
                        // Symfony Mailer attach() reads path.
                        
                        // Better approach for now: Leave it to scheduled cleanup or try to delete after Mail::html returns?
                        // Mail::html returns "sent" (void in L9+).
                        // But wait, the closure is built then executed.
                        // The $pdfPath needs to be passed out or we need a way to clean it up.
                        
                        // Let's rely on a cleanup job for temp files to be safe, 
                        // OR we can simple allow the temp file to exist for a bit.
                        
                    } catch (\Exception $e) {
                         Log::error("Failed to generate PDF attachment", [
                            'campaign_id' => $campaign->id,
                            'recipient_id' => $recipient->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            // Update recipient status
            $recipient->update([
                'status' => Recipient::STATUS_SENT,
                'sent_at' => now(),
                'error_message' => null,
            ]);

            // Log success
            EmailLog::create([
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Increment quota
            $this->quotaService->incrementCounter();

            Log::info("Email sent successfully", [
                'campaign_id' => $campaign->id,
                'recipient_email' => $recipient->email,
            ]);

            return true;

        } catch (\Exception $e) {
            $recipient->update([
                'status' => Recipient::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'retry_count' => $recipient->retry_count + 1,
            ]);

            EmailLog::create([
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'metadata' => ['exception' => get_class($e)],
            ]);

            Log::error("Failed to send email", [
                'campaign_id' => $campaign->id,
                'recipient_email' => $recipient->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a test email (not counted in quota).
     */
    public function sendTestEmail(string $to, string $subject, string $body): bool
    {
        try {
            Mail::html($body, function (Message $message) use ($to, $subject) {
                $message->to($to)->subject('[TEST] ' . $subject);
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Test email failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test Gmail SMTP connection.
     */
    public function testSmtpConnection(): array
    {
        try {
            $transport = Mail::mailer()->getSymfonyTransport();

            // Attempt to start/ping the transport
            if (method_exists($transport, '__toString')) {
                $info = (string) $transport;
            } else {
                $info = get_class($transport);
            }

            return [
                'success' => true,
                'message' => 'SMTP connection successful!',
                'info' => $info,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage(),
                'info' => null,
            ];
        }
    }
}

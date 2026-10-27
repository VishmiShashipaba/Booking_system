<?php
// config/mailer.php
// Self-contained SMTP Mailer for Prasanna Channel Center
// Uses Gmail SMTP with App Password (no Composer / PHPMailer required)

class Mailer
{
    // ── Gmail SMTP Settings ──────────────────────────────────────────────
    private static string $host     = 'smtp.gmail.com';
    private static int    $port     = 465;                       // SSL
    private static string $username = 'sewminirasangika0@gmail.com';
    private static string $password = 'acbo sagt xxpr ugtm';    // App Password
    private static string $fromName = 'Prasanna Channel Center';
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Send an HTML email.
     *
     * @param string $to       Recipient email address
     * @param string $subject  Email subject
     * @param string $body     HTML body content
     * @return bool            True on success, false on failure
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $timeout = 20;
        $socket  = @fsockopen('ssl://' . self::$host, self::$port, $errno, $errstr, $timeout);

        if (!$socket) {
            error_log("Mailer: Connection failed – $errstr ($errno)");
            return false;
        }

        // Helper: read one SMTP response (may be multi-line)
        $read = function () use ($socket): string {
            $out = '';
            while ($line = fgets($socket, 515)) {
                $out .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;  // single/last line
            }
            return $out;
        };

        // Helper: send a command and assert the expected numeric code
        $cmd = function (string $command, string $expectedCode) use ($socket, $read): bool {
            fwrite($socket, $command . "\r\n");
            $response = $read();
            if (substr($response, 0, 3) !== $expectedCode) {
                error_log("Mailer SMTP error (expected $expectedCode): " . trim($response));
                return false;
            }
            return true;
        };

        // Greeting
        $greeting = $read();
        if (substr($greeting, 0, 3) !== '220') {
            fclose($socket);
            error_log("Mailer: Bad greeting – " . trim($greeting));
            return false;
        }

        // SMTP handshake
        if (!$cmd("EHLO smtp.gmail.com",             '250')) { fclose($socket); return false; }
        if (!$cmd("AUTH LOGIN",                       '334')) { fclose($socket); return false; }
        if (!$cmd(base64_encode(self::$username),     '334')) { fclose($socket); return false; }
        if (!$cmd(base64_encode(self::$password),     '235')) { fclose($socket); return false; }
        if (!$cmd("MAIL FROM:<" . self::$username . ">", '250')) { fclose($socket); return false; }
        if (!$cmd("RCPT TO:<$to>",                   '250')) { fclose($socket); return false; }
        if (!$cmd("DATA",                             '354')) { fclose($socket); return false; }

        // Build RFC-2822 message
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $messageId      = '<' . uniqid('', true) . '@prasannachannel.lk>';
        $headers = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: " . self::$fromName . " <" . self::$username . ">",
            "To: <$to>",
            "Subject: $encodedSubject",
            "Date: " . date('r'),
            "Message-ID: $messageId",
        ]);

        fwrite($socket, $headers . "\r\n\r\n" . $body . "\r\n.\r\n");

        $dataResponse = $read();
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        if (substr($dataResponse, 0, 3) !== '250') {
            error_log("Mailer: DATA command failed – " . trim($dataResponse));
            return false;
        }

        return true;
    }

    // ── Convenience Template Methods ─────────────────────────────────────

    /**
     * Send appointment confirmation email to patient.
     *
     * @param string $patientEmail
     * @param string $patientName
     * @param string $appointmentNumber  e.g. A-00012
     * @param string $doctorName         e.g. Prasanna Jayamaha
     * @param string $specialization     e.g. Consultant Cardiologist
     * @param string $appointmentDate    e.g. 2026-07-20
     * @param string $dayOfWeek          e.g. Monday
     * @param string $startTime          e.g. 09:00:00
     * @param string $endTime            e.g. 12:00:00
     * @param int    $queueNumber
     * @param string $paymentMethod      Cash | Card
     * @param string $paymentStatus      Pending | Paid
     * @param float  $totalAmount        LKR total
     */
    public static function sendAppointmentConfirmation(
        string $patientEmail,
        string $patientName,
        string $appointmentNumber,
        string $doctorName,
        string $specialization,
        string $appointmentDate,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        int    $queueNumber,
        string $paymentMethod,
        string $paymentStatus,
        float  $totalAmount
    ): bool {
        $subject = "✅ Appointment Confirmed – $appointmentNumber | Prasanna Channel Center";

        // Format date nicely: "Monday, 20 July 2026"
        $formattedDate = date('l, d F Y', strtotime($appointmentDate));

        // Format times to 12-hour: "09:00 AM – 12:00 PM"
        $timeSlot = date('h:i A', strtotime($startTime)) . ' – ' . date('h:i A', strtotime($endTime));

        // Payment status colour
        $statusColor = ($paymentStatus === 'Paid') ? '#16a34a' : '#d97706';
        $statusIcon  = ($paymentStatus === 'Paid') ? '✅' : '⏳';

        $body = self::wrapLayout("
            <!-- Hero banner -->
            <div style='background:linear-gradient(135deg,#1d4ed8,#2563eb);border-radius:8px;padding:24px 28px;margin-bottom:28px;text-align:center;'>
                <div style='font-size:2.8rem;margin-bottom:6px;'>✅</div>
                <h2 style='margin:0;color:#ffffff;font-size:1.4rem;letter-spacing:0.3px;'>Appointment Confirmed!</h2>
                <p style='margin:6px 0 0;color:#bfdbfe;font-size:0.93rem;'>Your booking reference: <strong style='color:#fff;'>$appointmentNumber</strong></p>
            </div>

            <p style='margin:0 0 6px;'>Dear <strong>" . htmlspecialchars($patientName) . "</strong>,</p>
            <p style='margin:0 0 24px;color:#475569;'>Your appointment at <strong>Prasanna Channel Center</strong> has been confirmed. Please keep this email handy when you visit the clinic.</p>

            <!-- ── Appointment Details Table ── -->
            <table style='width:100%;border-collapse:collapse;margin-bottom:24px;font-size:0.94rem;'>
                <!-- Section header -->
                <tr>
                    <td colspan='2' style='background:#1d4ed8;color:#fff;padding:9px 14px;font-weight:700;font-size:0.85rem;letter-spacing:0.5px;border-radius:4px 4px 0 0;'>
                        🗓️ &nbsp;APPOINTMENT DETAILS
                    </td>
                </tr>
                <tr style='background:#eff6ff;'>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;width:42%;font-weight:600;color:#1e3a8a;'>Token / Appointment No.</td>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;'><strong style='font-size:1.05em;color:#1d4ed8;'>$appointmentNumber</strong></td>
                </tr>
                <tr>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;font-weight:600;color:#1e3a8a;'>Queue Number</td>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;'><span style='display:inline-block;background:#1d4ed8;color:#fff;border-radius:50%;width:36px;height:36px;line-height:36px;text-align:center;font-weight:700;font-size:1.05em;'>$queueNumber</span></td>
                </tr>
                <tr style='background:#eff6ff;'>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;font-weight:600;color:#1e3a8a;'>Date</td>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;'>" . htmlspecialchars($formattedDate) . "</td>
                </tr>
                <tr>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;font-weight:600;color:#1e3a8a;'>Session Time</td>
                    <td style='padding:11px 14px;border:1px solid #bfdbfe;'>⏰ " . htmlspecialchars($timeSlot) . "</td>
                </tr>

                <!-- Section header -->
                <tr>
                    <td colspan='2' style='background:#0f766e;color:#fff;padding:9px 14px;font-weight:700;font-size:0.85rem;letter-spacing:0.5px;'>
                        👨‍⚕️ &nbsp;DOCTOR INFORMATION
                    </td>
                </tr>
                <tr style='background:#f0fdfa;'>
                    <td style='padding:11px 14px;border:1px solid #99f6e4;font-weight:600;color:#134e4a;'>Doctor&apos;s Name</td>
                    <td style='padding:11px 14px;border:1px solid #99f6e4;'><strong>Dr. " . htmlspecialchars($doctorName) . "</strong></td>
                </tr>
                <tr>
                    <td style='padding:11px 14px;border:1px solid #99f6e4;font-weight:600;color:#134e4a;'>Specialization</td>
                    <td style='padding:11px 14px;border:1px solid #99f6e4;'>" . htmlspecialchars($specialization) . "</td>
                </tr>

                <!-- Section header -->
                <tr>
                    <td colspan='2' style='background:#6d28d9;color:#fff;padding:9px 14px;font-weight:700;font-size:0.85rem;letter-spacing:0.5px;'>
                        📍 &nbsp;VENUE
                    </td>
                </tr>
                <tr style='background:#faf5ff;'>
                    <td style='padding:11px 14px;border:1px solid #ddd6fe;font-weight:600;color:#4c1d95;'>Clinic Name</td>
                    <td style='padding:11px 14px;border:1px solid #ddd6fe;'><strong>Prasanna Channel Center</strong></td>
                </tr>
                <tr>
                    <td style='padding:11px 14px;border:1px solid #ddd6fe;font-weight:600;color:#4c1d95;'>Address</td>
                    <td style='padding:11px 14px;border:1px solid #ddd6fe;'>No. 120, Padiyatalawa Road,<br>Mahiyanganaya, Sri Lanka</td>
                </tr>
                <tr style='background:#faf5ff;'>
                    <td style='padding:11px 14px;border:1px solid #ddd6fe;font-weight:600;color:#4c1d95;'>Inquiry Hotline</td>
                    <td style='padding:11px 14px;border:1px solid #ddd6fe;'>📞 +94 55 123 4567 &nbsp;|&nbsp; +94 55 765 4321</td>
                </tr>

                <!-- Section header -->
                <tr>
                    <td colspan='2' style='background:#b45309;color:#fff;padding:9px 14px;font-weight:700;font-size:0.85rem;letter-spacing:0.5px;'>
                        💳 &nbsp;PAYMENT INFORMATION
                    </td>
                </tr>
                <tr style='background:#fffbeb;'>
                    <td style='padding:11px 14px;border:1px solid #fde68a;font-weight:600;color:#78350f;'>Total Fee</td>
                    <td style='padding:11px 14px;border:1px solid #fde68a;'><strong>LKR " . number_format($totalAmount, 2) . "</strong></td>
                </tr>
                <tr>
                    <td style='padding:11px 14px;border:1px solid #fde68a;font-weight:600;color:#78350f;'>Payment Method</td>
                    <td style='padding:11px 14px;border:1px solid #fde68a;'>" . htmlspecialchars($paymentMethod) . "</td>
                </tr>
                <tr style='background:#fffbeb;'>
                    <td style='padding:11px 14px;border:1px solid #fde68a;font-weight:600;color:#78350f;'>Payment Status</td>
                    <td style='padding:11px 14px;border:1px solid #fde68a;'>
                        <span style='color:$statusColor;font-weight:700;'>$statusIcon " . htmlspecialchars($paymentStatus) . "</span>
                        " . ($paymentStatus === 'Pending' ? "<br><small style='color:#92400e;'>Please settle at the reception desk upon arrival.</small>" : '') . "
                    </td>
                </tr>
            </table>

            <!-- Important notice -->
            <div style='background:#fef9c3;border-left:4px solid #ca8a04;padding:14px 18px;border-radius:0 6px 6px 0;margin-bottom:20px;'>
                <strong style='color:#92400e;'>⚠️ Important Reminders</strong>
                <ul style='margin:8px 0 0;padding-left:20px;color:#78350f;font-size:0.92rem;'>
                    <li>Please arrive <strong>15 minutes before</strong> your scheduled session time.</li>
                    <li>Bring your <strong>National ID (NIC)</strong> or this <strong>Appointment Number</strong>.</li>
                    <li>For Cash payments, please have the exact amount ready at reception.</li>
                    <li>To cancel or reschedule, log in to your patient portal at least <strong>24 hours</strong> in advance.</li>
                </ul>
            </div>

            <p style='color:#64748b;font-size:0.88em;text-align:center;'>
                Need help? Call us on <strong>+94 55 123 4567</strong> during clinic hours<br>
                (Mon–Fri: 8:00 AM – 9:00 PM &nbsp;|&nbsp; Sat–Sun: 8:00 AM – 6:00 PM)
            </p>
        ");

        return self::send($patientEmail, $subject, $body);
    }


    /**
     * Send appointment cancellation notification to patient.
     */
    public static function sendCancellationNotice(
        string $patientEmail,
        string $patientName,
        string $appointmentNumber,
        string $doctorName,
        string $appointmentDate
    ): bool {
        $subject = "Appointment Cancelled – $appointmentNumber | Prasanna Channel Center";

        $body = self::wrapLayout("
            <h2 style='color:#dc2626;'>❌ Appointment Cancelled</h2>
            <p>Dear <strong>" . htmlspecialchars($patientName) . "</strong>,</p>
            <p>Your appointment <strong>" . htmlspecialchars($appointmentNumber) . "</strong> with <strong>Dr. " . htmlspecialchars($doctorName) . "</strong>
               scheduled on <strong>" . htmlspecialchars($appointmentDate) . "</strong> has been <span style='color:#dc2626;font-weight:600;'>cancelled</span>.</p>
            <p>If this was done in error, please contact us or re-book through your patient dashboard.</p>
            <p style='color:#64748b;font-size:0.9em;'>We apologise for any inconvenience caused.</p>
        ");

        return self::send($patientEmail, $subject, $body);
    }

    /**
     * Send welcome / registration confirmation email to new patient.
     */
    public static function sendWelcomeEmail(
        string $patientEmail,
        string $patientName,
        string $patientNumber,
        string $username
    ): bool {
        $subject = "Welcome to Prasanna Channel Center – Account Created";

        $body = self::wrapLayout("
            <h2 style='color:#16a34a;'>🎉 Welcome, " . htmlspecialchars($patientName) . "!</h2>
            <p>Your patient account has been created successfully at <strong>Prasanna Channel Center</strong>.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
                <tr style='background:#f0fdf4;'>
                    <td style='padding:10px 14px;border:1px solid #bbf7d0;font-weight:600;'>Patient Number</td>
                    <td style='padding:10px 14px;border:1px solid #bbf7d0;'>" . htmlspecialchars($patientNumber) . "</td>
                </tr>
                <tr>
                    <td style='padding:10px 14px;border:1px solid #bbf7d0;font-weight:600;'>Username</td>
                    <td style='padding:10px 14px;border:1px solid #bbf7d0;'>" . htmlspecialchars($username) . "</td>
                </tr>
                <tr style='background:#f0fdf4;'>
                    <td style='padding:10px 14px;border:1px solid #bbf7d0;font-weight:600;'>Email</td>
                    <td style='padding:10px 14px;border:1px solid #bbf7d0;'>" . htmlspecialchars($patientEmail) . "</td>
                </tr>
            </table>
            <p>You can now log in and book doctor appointments online 24/7.</p>
            <p style='color:#64748b;font-size:0.9em;'>If you did not register on this platform, please ignore this email.</p>
        ");

        return self::send($patientEmail, $subject, $body);
    }

    // ── Private HTML wrapper ──────────────────────────────────────────────
    private static function wrapLayout(string $content): string
    {
        return "<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f1f5f9;padding:30px 0;'>
    <tr><td align='center'>
      <table width='620' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
        <!-- Header -->
        <tr>
          <td style='background:linear-gradient(135deg,#1d4ed8,#2563eb);padding:28px 32px;text-align:center;'>
            <h1 style='margin:0;color:#ffffff;font-size:1.5rem;letter-spacing:0.5px;'>🏥 Prasanna Channel Center</h1>
            <p style='margin:6px 0 0;color:#bfdbfe;font-size:0.9rem;'>Mahiyanganaya, Sri Lanka</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style='padding:32px 36px;color:#1e293b;font-size:0.97rem;line-height:1.7;'>
            $content
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style='background:#f8fafc;padding:18px 36px;border-top:1px solid #e2e8f0;text-align:center;color:#94a3b8;font-size:0.82rem;'>
            <p style='margin:0;'>📍 No. 120, Padiyatalawa Road, Mahiyanganaya, Sri Lanka</p>
            <p style='margin:4px 0 0;'>📞 +94 55 123 4567 &nbsp;|&nbsp; ✉️ sewminirasangika0@gmail.com</p>
            <p style='margin:8px 0 0;'>This is an automated message. Please do not reply directly to this email.</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";
    }
}
?>

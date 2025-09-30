<?php
require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';

class MailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new \PHPMailer\PHPMailer\PHPMailer();
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'soagk1tdtu@gmail.com';
        $this->mailer->Password = 'wveg zevy kdya rxbv'; 
        $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port = 587;
        $this->mailer->setFrom('soagk1tdtu@gmail.com', 'iBanking TDTU');
        $this->mailer->isHTML(true);
    }

    public function sendOtpEmail($email, $otp) {
        $this->mailer->clearAllRecipients();
        $this->mailer->addAddress($email);
        $this->mailer->Subject = 'Your OTP Code for Payment';
        $this->mailer->Body = $this->getOtpEmailTemplate($otp);
        return $this->mailer->send();
    }

    public function sendConfirmationEmail($email, $transaction, $user, $student, $fee) {
        $this->mailer->clearAllRecipients();
        $this->mailer->addAddress($email);
        $this->mailer->Subject = 'Payment Confirmation - iBanking TDTU';
        $this->mailer->Body = $this->getConfirmationEmailTemplate($transaction, $user, $student, $fee);

        return $this->mailer->send();
    }

    private function getOtpEmailTemplate($otp) {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>OTP Code</title>
        </head>
        <body style="margin:0; padding:0; font-family: Arial, sans-serif; background:#f4f6f9;">
            <table align="center" width="100%" style="max-width:600px; background:#ffffff; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); margin:20px auto;">
                <tr>
                    <td style="background:#004aad; padding:20px; text-align:center; border-radius:10px 10px 0 0;">
                        <h1 style="color:#ffffff; margin:0; font-size:22px;">iBanking TDTU</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px; text-align:center;">
                        <h2 style="color:#333; font-size:20px; margin-bottom:15px;">One-Time Password (OTP)</h2>
                        <p style="color:#555; font-size:15px; margin-bottom:20px;">Please use the OTP code below to verify your payment:</p>
                        <div style="display:inline-block; padding:15px 30px; background:#f0f4ff; border:1px dashed #004aad; border-radius:6px;">
                            <span style="font-size:28px; font-weight:bold; color:#004aad; letter-spacing:3px;">' . htmlspecialchars($otp) . '</span>
                        </div>
                        <p style="color:#777; font-size:13px; margin-top:25px;">This code is valid for <b>5 minutes</b>. Do not share it with anyone.</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f4f6f9; text-align:center; padding:15px; font-size:12px; color:#777; border-radius:0 0 10px 10px;">
                        © 2025 iBanking TDTU. All rights reserved.
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }

    private function getConfirmationEmailTemplate($transaction, $user, $student, $fee) {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Payment Confirmation</title>
        </head>
        <body style="margin:0; padding:0; font-family: Arial, sans-serif; background:#f4f6f9;">
            <table align="center" width="100%" style="max-width:650px; background:#ffffff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); margin:25px auto; overflow:hidden;">
                <!-- Header -->
                <tr>
                    <td style="background:#004aad; padding:25px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:24px;">iBanking TDTU</h1>
                        <p style="color:#cce0ff; margin:5px 0 0; font-size:14px;">Online Tuition Payment Service</p>
                    </td>
                </tr>
                
                <!-- Body -->
                <tr>
                    <td style="padding:30px;">
                        <h2 style="color:#28a745; text-align:center; font-size:22px; margin-bottom:15px;">Payment Successful</h2>
                        <p style="color:#555; text-align:center; font-size:15px; margin-bottom:25px;">
                            Thank you <strong>' . htmlspecialchars($user['username']) . '</strong> for completing your payment.<br>
                            Here are your transaction details:
                        </p>

                        <table width="100%" cellpadding="12" cellspacing="0" style="border-collapse:collapse; font-size:14px; color:#333; border:1px solid #eee;">
                            <tr style="background:#f9f9f9;">
                                <td style="width:35%; font-weight:bold; border:1px solid #eee;">Transaction ID</td>
                                <td style="border:1px solid #eee;">' . htmlspecialchars($transaction['transaction_id']) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; border:1px solid #eee;">Payer</td>
                                <td style="border:1px solid #eee;">' . htmlspecialchars($user['username']) . ' (' . htmlspecialchars($user['email']) . ')</td>
                            </tr>
                            <tr style="background:#f9f9f9;">
                                <td style="font-weight:bold; border:1px solid #eee;">Student</td>
                                <td style="border:1px solid #eee;">' . htmlspecialchars($student['full_name']) . ' (ID: ' . htmlspecialchars($student['student_id']) . ')</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; border:1px solid #eee;">Semester</td>
                                <td style="border:1px solid #eee;">' . htmlspecialchars($fee['semester']) . '</td>
                            </tr>
                            <tr style="background:#f9f9f9;">
                                <td style="font-weight:bold; border:1px solid #eee;">School Year</td>
                                <td style="border:1px solid #eee;">' . htmlspecialchars($fee['school_year']) . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; border:1px solid #eee;">Amount</td>
                                <td style="border:1px solid #eee; color:#004aad; font-weight:bold;">' . number_format($transaction['amount'], 0, ',', '.') . '₫</td>
                            </tr>
                            <tr style="background:#f9f9f9;">
                                <td style="font-weight:bold; border:1px solid #eee;">Date</td>
                                <td style="border:1px solid #eee;">' . (new DateTime($transaction['created_at']))->format('d/m/Y H:i:s') . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold; border:1px solid #eee;">Note</td>
                                <td style="border:1px solid #eee;">' . htmlspecialchars($transaction['note']) . '</td>
                            </tr>
                        </table>

                        <p style="text-align:center; color:#777; font-size:13px; margin-top:25px;">
                            If you have any questions, please contact 
                            <a href="mailto:support@ibankingtdtu.com" style="color:#004aad; text-decoration:none;">support@ibankingtdtu.com</a>.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f4f6f9; text-align:center; padding:15px; font-size:12px; color:#777;">
                        © 2025 iBanking TDTU. All rights reserved.
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }
}

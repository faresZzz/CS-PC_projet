<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';

/**
 * A simple wrapper for PHPMailer
 * uses IsMail()
 * handles alt text
 * @version 1.0.0
 * @author Phenix Info <arthur@phenixinfo.fr>
 */
class MailSender{

    /**
     * Sends an email
     * @param array|string recipients
     * @param string subject
     * @param string body HTML contents of the email
     * @param array options associative array containing any of the following:
     *                  images (name => file),
     *                  cc (sequential array),
     *                  attachments (name => file or sequential array)
     * @return bool success
     */
    public static function sendMail($recipients, $subject, $body, $options = []){

        global $f3;

        date_default_timezone_set('Etc/UTC');

        if(is_string($recipients)){
            $recipients = [$recipients];
        }

        $mail = new PHPMailer();

        try {
            //Server settings
            $mail->SMTPDebug = false;                                   // Enable verbose debug output
            $mail->isMail();                                            //
            //$mail->SMTPKeepAlive = true;                              // Prevent the SMTP session from being closed after each message
            $mail->Host       = $f3->get('MAIL.host');                  // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = $f3->get('MAIL.email');                 // SMTP username
            $mail->Password   = $f3->get('MAIL.password');              // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            $mail->CharSet    = 'UTF-8';                                // Encodage caractères spéciaux

            //Recipients
            $mail->setFrom($f3->get('MAIL.email'), $f3->get('MAIL.name'));
            foreach($recipients as $recipent){
                $mail->addAddress(trim($recipent));
            }

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;


            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], ['\r\n'], $body));

            //options
            if($options['images']){
                foreach($options['images'] as $key => $image){
                    $mail->addEmbeddedImage($image, $key);
                }
            }
            if($options['cc']){
                foreach($options['cc'] as $cc){
                    $mail->addCC($cc);
                }
            }
            if($options['attachments']){
                foreach($options['attachments'] as $filename => $attachment){
                    if(is_string($filename)){
                        $mail->addAttachment($attachment, $filename);
                    }
                    else{
                        $mail->addAttachment($attachment);
                    }
                }
            }

            if(!$mail->send()) {
                        (new \Log('logs/mail.log'))->write("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                        return false;
            }
            else{
                        (new \Log('logs/mail.log'))->write("Message send. Mailer Info : " . implode(", ", $recipients));
                        return true;
            }
        } catch (Exception $e) {
            (new \Log('logs/mail.log'))->write("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }


    }
}

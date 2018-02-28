<?php
declare(strict_types = 1);

namespace smtp;

use app;
use req;
use DomainException;
use Throwable;

/**
 * Send mail through SMTP server
 *
 * @throws DomainException
 */
function mail(string $from, string $to, string $subj, string $text, array $attach = []): bool
{
    $cfg = app\cfg('smtp');
    $host = req\data('host');

    try {
        $client = stream_socket_client($cfg['dsn'], $errno, $errstr, $cfg['timeout']);

        if (!is_resource($client)) {
            throw new DomainException(app\i18n('Could not send message'));
        }
    } catch (Throwable $e) {
        app\log($e);
        return false;
    }

    receive($client);
    send($client, 'HELO ' . $host, [250]);

    if ($cfg['tls']) {
        send($client, 'STARTTLS', [220]);
        stream_socket_enable_crypto($client, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    }

    if ($cfg['auth'] === 'plain') {
        send($client, 'AUTH PLAIN', [334]);
        send($client, base64_encode("\0" . $cfg['user'] . "\0" . $cfg['password']), [235]);
    } elseif ($cfg['auth'] === 'plain') {
        send($client, 'AUTH LOGIN', [334]);
        send($client, base64_encode($cfg['user']), [334]);
        send($client, base64_encode($cfg['password']), [235]);
    }

    send($client, 'MAIL FROM:<' . $from . '>', [250]);
    send($client, 'RCPT TO:<' . $to . '>', [250, 251]);
    send($client, 'DATA', [354]);

    $nl = "\r\n";
    $mail = 'From: <' . $from . '>' . $nl;
    $mail .= 'To: <' . $to . '>' . $nl;
    $mail .= 'Date: ' . date('r') . $nl;
    $mail .= 'Subject: ' . $subj . $nl;

    if ($attach) {
        $boundary = md5(uniqid((string) time()));
        $mail .= 'MIME-Version: 1.0' . $nl;
        $mail .= 'Content-Type: multipart/mixed; charset="utf-8"; boundary="' . $boundary . '"' . $nl . $nl;
        $mail .= 'This is a multipart message in MIME format.' . $nl . $nl;
        $mail .= '--' . $boundary . $nl;
        $mail .= 'Content-Type: text/plain; charset="utf-8"' . $nl;
        $mail .= 'Content-Transfer-Encoding: 8bit' . $nl . $nl;
        $mail .= $text . $nl;

        foreach ($attach as $file => $type) {
            if (!is_file($file)) {
                continue;
            }

            $name = basename($file);
            $mail .= '--' . $boundary . $nl;
            $mail .= 'Content-Type: ' . $type . '; name="' . $name . '"' . $nl;
            $mail .= 'Content-Disposition: attachment; filename="' . $name . '"' . $nl;
            $mail .= 'Content-Transfer-Encoding: base64' . $nl . $nl;
            $mail .= chunk_split(base64_encode(file_get_contents($file)));
        }

        $mail .= '--' . $boundary . '--' . $nl;
    } else {
        $mail .= 'Content-Type: text/plain; charset="utf-8"' . $nl . $nl;
        $mail .= $text . $nl;
    }

    $mail .= '.';
    send($client, $mail, [250]);
    send($client, 'QUIT', [221]);
    fclose($client);

    return true;
}

/**
 * Receives message from server
 */
function receive($client): ?string
{
    return fgets($client, 515) ?: null;
}

/**
 * Sends message to server
 *
 * @throws DomainException
 */
function send($client, string $msg, array $status): void
{
    fputs($client, $msg . "\r\n");
    $data = receive($client);

    if (!$data || !preg_match('#^([1-5][0-9][0-9])(?:.*)$#', $data, $match) || !in_array((int) $match[1], $status)) {
        throw new DomainException(app\i18n('Unexpected response from server: %s', $data));
    }
}

<?php
declare(strict_types=1);

namespace smtp;

use app;
use DomainException;
use str;

/**
 * Send mail through SMTP server
 *
 * @throws DomainException
 */
function mail(string $from, string $to, string $subj, string $text, array $attach = [], string $replyTo = null): void
{
    $cfg = app\cfg('smtp');
    $host = app\data('request', 'host');

    if (!$client = stream_socket_client($cfg['dsn'], timeout: $cfg['timeout'])) {
        throw new DomainException(app\i18n('Could not send message'));
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
    } elseif ($cfg['auth'] === 'login') {
        send($client, 'AUTH LOGIN', [334]);
        send($client, base64_encode($cfg['user']), [334]);
        send($client, base64_encode($cfg['password']), [235]);
    }

    send($client, 'MAIL FROM:<' . $from . '>', [250]);
    send($client, 'RCPT TO:<' . $to . '>', [250, 251]);
    send($client, 'DATA', [354]);

    $mail = 'From: <' . $from . '>' . APP['crlf'];
    $mail .= 'To: <' . $to . '>' . APP['crlf'];

    if ($replyTo) {
        $mail .= 'Reply-To: <' . $replyTo . '>' . APP['crlf'];
    }

    $mail .= 'Date: ' . date('r') . APP['crlf'];
    $mail .= 'Subject: ' . $subj . APP['crlf'];

    if ($attach) {
        $boundary = str\uniq();
        $mail .= 'MIME-Version: 1.0' . APP['crlf'];
        $mail .= 'Content-Type: multipart/mixed; charset="utf-8"; boundary="' . $boundary . '"' . APP['crlf'];
        $mail .= APP['crlf'];
        $mail .= 'This is a multipart message in MIME format.' . APP['crlf'];
        $mail .= APP['crlf'];
        $mail .= '--' . $boundary . APP['crlf'];
        $mail .= 'Content-Type: text/plain; charset="utf-8"' . APP['crlf'];
        $mail .= 'Content-Transfer-Encoding: 8bit' . APP['crlf'];
        $mail .= APP['crlf'];
        $mail .= $text . APP['crlf'];

        foreach ($attach as $file) {
            if (empty($file['name']) || empty($file['path']) || empty($file['type']) || !is_file($file['path'])) {
                continue;
            }

            $mail .= '--' . $boundary . APP['crlf'];
            $mail .= 'Content-Type: ' . $file['type'] . '; name="' . $file['name'] . '"' . APP['crlf'];
            $mail .= 'Content-Disposition: attachment; filename="' . $file['name'] . '"' . APP['crlf'];
            $mail .= 'Content-Transfer-Encoding: base64' . APP['crlf'];
            $mail .= APP['crlf'];
            $mail .= chunk_split(base64_encode(file_get_contents($file['path'])));
        }

        $mail .= '--' . $boundary . '--' . APP['crlf'];
    } else {
        $mail .= 'Content-Type: text/plain; charset="utf-8"' . APP['crlf'];
        $mail .= APP['crlf'];
        $mail .= $text . APP['crlf'];
    }

    $mail .= '.';
    send($client, $mail, [250]);
    send($client, 'QUIT', [221]);
    fclose($client);
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
    fputs($client, $msg . APP['crlf']);
    $data = receive($client);

    if (!$data || !preg_match('#^([1-5][0-9][0-9])(?:.*)$#', $data, $match) || !in_array((int)$match[1], $status)) {
        throw new DomainException(app\i18n('Unexpected response from server: %s', $data));
    }
}

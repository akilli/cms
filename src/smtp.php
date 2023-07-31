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
function mail(string $to, string $subj, string $text, array $attach = [], string $replyTo = null): void
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

    send($client, 'MAIL FROM:<' . $cfg['from'] . '>', [250]);
    send($client, 'RCPT TO:<' . $to . '>', [250, 251]);
    send($client, 'DATA', [354]);

    $mail = 'from: <' . $cfg['from'] . '>' . APP['eol']['crlf'];
    $mail .= 'to: <' . $to . '>' . APP['eol']['crlf'];

    if ($replyTo) {
        $mail .= 'reply-to: <' . $replyTo . '>' . APP['eol']['crlf'];
    }

    $mail .= 'date: ' . date('r') . APP['eol']['crlf'];
    $mail .= 'subject: ' . $subj . APP['eol']['crlf'];

    if ($attach) {
        $boundary = str\uniq();
        $mail .= 'mime-version: 1.0' . APP['eol']['crlf'];
        $mail .= 'content-type: ' . APP['type']['multipart'] . '; boundary="' . $boundary . '"' . APP['eol']['crlf'];
        $mail .= APP['eol']['crlf'];
        $mail .= 'This is a multipart message in MIME format.' . APP['eol']['crlf'];
        $mail .= APP['eol']['crlf'];
        $mail .= '--' . $boundary . APP['eol']['crlf'];
        $mail .= 'content-type: ' . APP['type']['text'] . APP['eol']['crlf'];
        $mail .= 'content-transfer-encoding: 8bit' . APP['eol']['crlf'];
        $mail .= APP['eol']['crlf'];
        $mail .= $text . APP['eol']['crlf'];

        foreach ($attach as $file) {
            if (empty($file['name']) || empty($file['path']) || empty($file['type']) || !is_file($file['path'])) {
                continue;
            }

            $mail .= '--' . $boundary . APP['eol']['crlf'];
            $mail .= 'content-type: ' . $file['type'] . '; name="' . $file['name'] . '"' . APP['eol']['crlf'];
            $mail .= 'content-disposition: attachment; filename="' . $file['name'] . '"' . APP['eol']['crlf'];
            $mail .= 'content-transfer-encoding: base64' . APP['eol']['crlf'];
            $mail .= APP['eol']['crlf'];
            $mail .= chunk_split(base64_encode(file_get_contents($file['path'])));
        }

        $mail .= '--' . $boundary . '--' . APP['eol']['crlf'];
    } else {
        $mail .= 'content-type: ' . APP['type']['text'] . APP['eol']['crlf'];
        $mail .= APP['eol']['crlf'];
        $mail .= $text . APP['eol']['crlf'];
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
    fputs($client, $msg . APP['eol']['crlf']);
    $data = receive($client);

    if (!$data || !preg_match('#^([1-5][0-9][0-9])(?:.*)$#', $data, $match) || !in_array((int)$match[1], $status)) {
        throw new DomainException(app\i18n('Unexpected response from server: %s', (string)$data));
    }
}
